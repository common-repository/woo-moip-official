<?php
namespace Woocommerce\Moip\Model;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

//Moip SDK
use Moip\Moip;
use Moip\Auth\OAuth;
use Moip\Resource\Orders;
use Moip\Resource\Payment;
use Moip\Resource\Customer as Moip_Customer;
use Moip\Resource\Holder as Moip_Holder;

// Objects Native
use Exception;
use DateTime;

// WooCommerce
use WC_Order;
use WC_Order_Item_Fee;

// Config
use Woocommerce\Moip\Core;

// Model
use Woocommerce\Moip\Model\Setting;
use Woocommerce\Moip\Model\Order;
use Woocommerce\Moip\Model\Customer;

// Helper
use Woocommerce\Moip\Helper\Utils;

//Exceptions
use Woocommerce\Moip\Exceptions\Parse_Exception;

class Moip_SDK
{
	private static $_instance = null;

	public $setting;

	public $customer;

	public $default_date_string = '31-12-1800';

	public $moip = false;

	private function __construct()
	{
		$this->_set_customer();
		$this->_set_setting();
		$this->_set_moip();
	}

	private function _set_customer()
	{
		$this->customer = new Customer( get_current_user_id() );
	}

	private function _set_setting()
	{
		$this->setting = Setting::get_instance();
	}

	public function is_valid()
	{
		return ( $this->moip instanceof Moip );
	}

	public function get_endpoint()
	{
		return $this->setting->is_sandbox() ? Moip::ENDPOINT_SANDBOX : Moip::ENDPOINT_PRODUCTION;
	}

	private function _set_moip()
	{
		$authorize_data = $this->setting->authorize_data;

		if ( $authorize_data && $authorize_data->accessToken ) {
			try {
				$oauth      = new OAuth( $authorize_data->accessToken );
				$this->moip = new Moip( $oauth, $this->get_endpoint() );

				$http_sess = $this->moip->getSession();

				$http_sess->options['timeout']         = 60.0;
				$http_sess->options['connect_timeout'] = 60.0;

				$this->moip->setSession( $http_sess );

			} catch ( Exception $e ) {
				error_log( $e->__toString() );
			}
		}
	}

	public function create_customer_by_order( WC_Order $wc_order )
	{
		if ( ! $this->is_valid() ) {
			return null;
		}

		$order_id      = $wc_order->get_id();
		$model         = new Order( $order_id );
		$moip_customer = $this->moip->customers();
		$person_type   = intval( $this->setting->field_person_type );
		$persontype    = $model->billing_persontype;

		if ( class_exists( 'Extra_Checkout_Fields_For_Brazil' ) ) {
			$settings = get_option( 'wcbcf_settings' );

			if ( intval( $settings['person_type'] ) === 2 ) {
				$persontype = 1;
			}

			if ( intval( $settings['person_type'] ) === 3 ) {
				$persontype = 2;
			}
		}

		try {

			if ( $person_type == 0 ) {

				if ( $persontype == 1 ) {
					$document      = $model->billing_cpf;
					$fullname      = $model->billing_first_name." ".$model->billing_last_name;
					$document_type = 'CPF';
				}

				if ( $persontype == 2 ) {
					$document      = $model->billing_cnpj;
					$fullname      = $model->billing_company;
					$document_type = 'CNPJ';
				}
			}

			if ( $person_type == 1 ) {
				$document      = $model->billing_cpf;
				$fullname      = $model->billing_first_name." ".$model->billing_last_name;
				$document_type = 'CPF';
			}

			if ( $person_type == 2 ) {
				$document      = $model->billing_cnpj;
				$fullname      = $model->billing_company;
				$document_type = 'CNPJ';
			}

			$moip_customer->setOwnId( $this->setting->get_customer_id() );
			$moip_customer->setFullname( $fullname );
			$moip_customer->setEmail( $model->billing_email );
			$moip_customer->setBirthDate( Utils::convert_date( $this->default_date_string ) );
			$moip_customer->setTaxDocument( Utils::format_document( $document ), $document_type );

			if ( $phone = Utils::format_phone_number( $model->billing_phone ) ) {
				$moip_customer->setPhone( $phone[0], $phone[1] );
			}

			$moip_customer->addAddress(
				Moip_Customer::ADDRESS_SHIPPING,
				$model->shipping_address_1,
				$model->shipping_number,
				$model->shipping_neighborhood,
				$model->shipping_city,
				$model->shipping_state,
				preg_replace( '/[^\d]+/', '', $model->shipping_postcode ),
				$model->shipping_address_2
			);

			$moip_customer->create();

			return $moip_customer;

		} catch ( Exception $e ) {
			error_log( $e->__toString() );
			if ( $this->setting->is_enabled_logs() ) {
				$this->setting->log()->add( 'moip-brazil-official-error', 'MOIP CREATE CUSTOMER ERROR: ' . $e->__toString() );
			}
			$this->_show_cancel_button( $wc_order );
		    return null;
		}
	}

	public function create_order( WC_Order $wc_order, $fields = false )
	{
		$moip_customer = $this->create_customer_by_order( $wc_order );

		if ( is_null( $moip_customer ) ) {
			return null;
		}

		try {
			$moip_order = $this->moip->orders();
			$order_id   = intval( $wc_order->get_id() );

		    $moip_order->setOwnId( $this->setting->get_transation_id( $order_id ) );

		    foreach ( $wc_order->get_items() as $item ) {
				$product = wc_get_product( $item['product_id'] );
				$qty     = absint( $item['qty'] );
				$title   = sanitize_title( $item['name'] ) . ' x ' . $qty;
				$price   = Utils::format_order_price( $wc_order->get_item_total( $item, false ) );

				//Quando produto possuir valor zerado.
				if ( $price == 0 ) {
					$price = (int)01;
					update_post_meta( $order_id, '_moip_bundle_product_price', $qty );
				}

		    	$moip_order->addItem( $title, $qty, $product->get_sku(), $price );
			}

			$this->_set_installments_interest( $moip_order, $fields, $wc_order );

			if ( $this->setting->is_active_render_discount() && ! $this->setting->is_checkout_moip() ) {
				$this->_set_moip_shipping( $moip_order, $wc_order );
			} else {
				$this->_set_shipping( $moip_order, $wc_order );
			}

			//$this->_set_discount( $moip_order, $wc_order );

			$moip_order->setCustomer( $moip_customer );
	        // Add Filter Marketplace
			$moip_order = apply_filters( 'apiki_moip_create_order', $moip_order, $wc_order );
			$moip_order->create();

			$response = $moip_order->jsonSerialize();

			$order                = new Order( $order_id );
			$order->payment_links = $response->_links->checkout;
			$order->resource_id   = $response->id;

			unset( $order );

	        return [
				'order'    => $moip_order,
				'customer' => $moip_customer,
				'response' => $response,
			];

		} catch ( Exception $e ) {
			if ( $this->setting->is_enabled_logs() ) {
				$this->setting->log()->add( 'moip-brazil-official-error', 'MOIP CREATE ORDER ERROR: ' . $e->__toString() );
			}
			$this->_show_cancel_button( $wc_order );
			return null;
		}
	}

	public function create_payment( $moip_order, $moip_customer, $fields, $wc_order )
	{
		try {
			$payment        = $moip_order->payments();
			$payment_method = $fields['payment_method'];

		    if ( ! method_exists( $this, $payment_method ) ) {
		    	wp_send_json_error( __( 'Payment method not found!', 'woo-moip-official' ) );
		    }

		    $this->{$payment_method}( $payment, $moip_customer, $fields );

		    $payment->execute();

		    return $payment;

		} catch ( Exception $e ) {
			if ( $this->setting->is_enabled_logs() ) {
				$this->setting->log()->add( 'moip-brazil-official-error', 'MOIP CREATE PAYMENT ERROR: ' . $e->__toString() );
			}
			$parse_exception = new Parse_Exception( $e );
			$wc_order->add_order_note(__( 'Moip: <p>'.$parse_exception->get_errors().'</p>', 'woo-moip-official' ) );

		    return $parse_exception->get_errors();
		}
	}

	public function get_order( $resource_id )
	{
		$orders = new Orders( $this->moip );

		return $orders->get( $resource_id );
	}

    public function payCreditCard( Payment $payment, Moip_Customer $moip_customer, array $fields )
    {
		$hash = $this->_get_payment_hash( $fields );

		if ( !$hash ) {
			return;
		}

		if ( $this->setting->is_checkout_transparent() ) {
            $moip_customer->setFullname( "{$fields['card_holder']}" );
        }

        if ( $this->setting->is_checkout_transparent()
        && $this->setting->is_active_credit_card()
        && $this->setting->is_enabled_cpf_holder() ) {
            $moip_customer->setTaxDocument( "{$fields['cpf_holder']}", 'CPF' );
		}

		if ( $this->setting->is_checkout_transparent()
        && $this->setting->is_active_credit_card()
        && $this->setting->is_enabled_birth_holder() ) {
            $moip_customer->setBirthDate( Utils::convert_date("{$fields['birth_holder']}"));
		}

		if ( $this->setting->is_checkout_transparent()
        && $this->setting->is_active_credit_card()
        && $this->setting->is_enabled_phone_holder() ) {
			$phone_holder = "{$fields['phone_holder']}";
			$full_phone   = preg_replace( '/\D+/', '', $phone_holder );
			$ddd_phone    = substr( $full_phone, 0, 2 );
			$phone        = substr( $full_phone, 2 );
			$moip_customer->setPhone( $ddd_phone, $phone, 55 );
		}

    	if ( $hash ) {
			$payment->setCreditCardHash( $hash, $moip_customer );
	    	$this->_save_credit_card_hash( $hash, $fields );
    	}

    	$payment->setInstallmentCount( $fields['installments'] );
    	$payment->setStatementDescriptor( $this->setting->invoice_name );
    }

    public function payOnlineBankDebitItau( Payment $payment, $moip_customer = null, $fields = null )
    {
    	$expiration_date = new DateTime();

    	$expiration_date->modify( '+1 day' );
    	$payment->setOnlineBankDebit( 341, $expiration_date->format( 'Y-m-d' ), null );
    }

    public function payBoleto( Payment $payment, $moip_customer = null, $fields = null )
    {
		$expiration_date = new DateTime();
		$payee_name      = $this->setting->invoice_name;

		if ( $this->setting->billet_payee_name ) {
			$payee_name = $this->setting->billet_payee_name;
		}

    	if ( $days = (int)$this->setting->billet_deadline_days ) {
    		$expiration_date->modify( "+{$days} day" );
    	}

    	$payment->setBoleto(
    		$expiration_date->format( 'Y-m-d' ),
    		$this->setting->billet_logo,
    		[
    			$this->setting->billet_instruction_line1,
    			$this->setting->billet_instruction_line2,
    			$this->setting->billet_instruction_line3
			]
		);

		$payment->setStatementDescriptor( $payee_name );
    }

    private function _show_cancel_button( WC_Order $wc_order )
	{
		if ( ! Utils::is_request_ajax() ) {
			printf(
				'<a class="button cancel" href="%s">%s</a>',
				esc_url( $wc_order->get_cancel_order_url() ),
				__( 'An error occurred while processing. Click to try again.', 'woo-moip-official' )
			);
		}
	}

    private function _get_payment_hash( array $fields )
    {
		if ( ! Utils::post( 'encrypt', 0, 'intval' ) ) {
			return false;
		}

		$field_hash = Utils::get_value_by( $fields, 'hash' );

		return $field_hash ? $field_hash : $this->customer->credit_card_hash;
    }

    private function _save_credit_card_hash( $hash, $fields )
    {
		$saved = Utils::get_value_by( $fields, 'store_credit_card' );

		if ( $hash && $saved === '1' ) {
			$this->customer->stored_credit_card = '1';
			$this->customer->credit_card_hash   = $hash;
		}
    }

    private function _set_installments_interest( $moip_order, $fields, $wc_order )
    {
   		if ( ! $this->setting->is_active_installments() ) {
    		return;
    	}

    	if ( ! $fields ) {
    		return;
    	}

		$fields       = Utils::post( 'moip_fields', false );
		$installments = $fields['installments'];
		$total        = $wc_order->get_total();

    	if ( $fields['payment_method'] != 'payCreditCard' ) {
    		return;
    	}

    	if ( ! isset( $this->setting->installments['interest'][ $installments ] ) ) {
    		return;
		}

		if ( empty( $this->setting->installments['interest'][ $installments ] ) ) {
    		return;
    	}

		$tax_total      = $wc_order->get_total_tax();
		$shipping_total = $wc_order->get_shipping_total();
		$subtotal       = $wc_order->get_subtotal();

		$wc_total = $tax_total + $subtotal + $shipping_total;

		$per_installment = (float) str_replace( ',', '.', $this->setting->installments['interest'][ $installments ] );
		$interest = round( (  $per_installment / 100 ) * $wc_total, 2 );


		$moip_order->setAddition( Utils::format_order_price( $interest ) );

		$item_fee = new WC_Order_Item_Fee();

		$item_fee->set_name( __( 'Installment interest', 'woo-moip-official' ) );
		$item_fee->set_amount( $interest );
		$item_fee->set_tax_class( '' );
		$item_fee->set_tax_status( 'taxable' );
		$item_fee->set_total( $interest );

		// Add Fee item to the order.
		$wc_order->add_item( $item_fee );

		$wc_order->calculate_totals();
    }

    private function _set_shipping( $moip_order, WC_Order $wc_order )
    {
    	//Woo Tax Calculation
    	$wc_tax     = $wc_order->get_total_tax();
		$discount   = $wc_order->get_total_discount();
		$order_id   = intval( $wc_order->get_id() );
		$add_price  = get_post_meta( $order_id, '_moip_bundle_product_price', true );
		$shipping   = $wc_order->get_shipping_total();

    	if ( $wc_tax ) {
			$shipping = $wc_order->get_total() - $wc_order->get_subtotal() + $discount;
			//$moip_order->setAddition( Utils::format_order_price( $wc_tax ) );
    	} else {
			$shipping = $wc_order->get_shipping_total();
		}

		if ( !empty( $add_price ) ) {
			$this->set_discount_bundle_item( $moip_order, $wc_order );
		}

    	$moip_order->setShippingAmount( Utils::format_order_price( $shipping ) );

		//$this->setting->log()->add( 'wirecard-brazil-official-cc', 'WIRECARD CREATE SHIPPING2: ' . $shipping );
    }

    // private function _set_discount( $moip_order, WC_Order $wc_order )
    // {
	// 	if ( ! $discount = $wc_order->get_total_discount() ) {
    // 		return;
    // 	}

    // 	$moip_order->setDiscount( Utils::format_order_price( $discount ) );
    // }

    private function _set_moip_shipping( $moip_order, WC_Order $wc_order )
    {
		$wirecard_discount  = (float) str_replace( ',', '.', $this->setting->wirecard_discount_number );
		$discount_type      = $this->setting->get_moip_discount_type();
		$wirecard_fee       = ( $discount_type / 100 ) * $wirecard_discount;
		$fields             = Utils::post( 'moip_fields', false );
		$payments_method    = Utils::get_value_by( $fields, 'payment_method' );
		$wc_shipping        = $wc_order->get_shipping_total();
		$shipping           = $wc_shipping - $wirecard_fee;

		if ( $payments_method == 'payCreditCard' && !empty( $wirecard_fee ) ) {
			$this->_get_card_shipping( $moip_order, $wc_order );
    		return;
		}

		if ( $wc_shipping == 0 || $wc_shipping < $wirecard_fee ) {
			$this->_get_billet_shipping( $moip_order, $wc_order );
    		return;
		}

		if ( ! $wirecard_discount ) {
			$this->_set_shipping( $moip_order, $wc_order );
    		return;
    	}

    	$moip_order->setShippingAmount( Utils::format_order_price( $shipping ) );
	}

	private function _get_billet_shipping( $moip_order, WC_Order $wc_order )
    {
		$wirecard_discount  = (float) str_replace( ',', '.', $this->setting->wirecard_discount_number );
		$wirecard_fee       = ( WC()->cart->subtotal / 100 ) * $wirecard_discount;
		$wirecard_total     = $wirecard_fee - $wc_order->get_shipping_total();
		$fields             = Utils::post( 'moip_fields', false );
		$payments_method    = Utils::get_value_by( $fields, 'payment_method' );

		if ( $payments_method == 'payCreditCard' ) {
			$this->_get_card_shipping( $moip_order, $wc_order );
			return;
		}

    	$moip_order->setDiscount( Utils::format_order_price( $wirecard_total ) );
	}

	private function _get_card_shipping( $moip_order, WC_Order $wc_order )
    {
        $shipping = $wc_order->get_shipping_total();

		// //Quando o frete possuir valor zerado.
		// if ( $shipping == 0 ) {
		// 	$shipping = 0.01;
		// }

    	$moip_order->setShippingAmount( Utils::format_order_price( $shipping ) );
    }

	public static function get_instance()
	{
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	public function set_discount_bundle_item( $moip_order, WC_Order $wc_order )
	{
		$order_id   = intval( $wc_order->get_id() );
		$add_price  = get_post_meta( $order_id, '_moip_bundle_product_price', true );

		$moip_order->setDiscount( $add_price );
	}
}
