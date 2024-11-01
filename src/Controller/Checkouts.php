<?php
namespace Woocommerce\Moip\Controller;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Moip\Core;
use Woocommerce\Moip\Helper\Utils;
use Woocommerce\Moip\Model\Checkout;
use Woocommerce\Moip\Model\Moip_SDK;
use Woocommerce\Moip\Model\Order;
use Woocommerce\Moip\Model\Setting;
use Woocommerce\Moip\View\Checkouts as Checkouts_View;

// WooCommerce
use WC_Order;

class Checkouts
{
	public function __construct()
	{
		$this->settings = Setting::get_instance();

		if ( class_exists( 'Extra_Checkout_Fields_For_Brazil' ) ) {
			add_action( 'woocommerce_checkout_process', array( $this, 'validate_cc_fields' ) );
		}

		add_action( 'wp_ajax_RmSLgKecpN', array( $this, 'process_checkout_moip' ) );
		add_action( 'wp_ajax_nopriv_RmSLgKecpN', array( $this, 'process_checkout_moip' ) );
		add_action( 'wp_ajax_N7yAgMU7JJ', array( $this, 'process_checkout_default' ) );
		add_action( 'wp_ajax_nopriv_N7yAgMU7JJ', array( $this, 'process_checkout_default' ) );
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'moip_billet_discount' ), 30 );
		add_action( 'woocommerce_review_order_after_order_total', array( $this, 'custom_cc_order_total') );
		add_action( 'woocommerce_checkout_create_order', array( $this, 'change_total_on_checking' ), 20, 1 );

		if ( ! class_exists( 'Extra_Checkout_Fields_For_Brazil' ) ) {
			add_filter( 'woocommerce_billing_fields', array( $this, 'wbo_billing_checkout_field' ), 20 );
			add_filter( 'woocommerce_shipping_fields', array( $this, 'wbo_shipping_checkout_field' ), 20 );
			add_filter( 'woocommerce_localisation_address_formats', array( $this, 'wbo_address_formats' ) );
			add_filter( 'woocommerce_formatted_address_replacements', array( $this, 'wbo_address_replacements' ), 10, 2 );
			add_filter( 'woocommerce_order_formatted_billing_address', array( $this, 'wbo_update_formatted_billing_address' ), 10, 2 );
        	add_filter( 'woocommerce_order_formatted_shipping_address', array( $this, 'wbo_update_formatted_shipping_address' ), 10, 2 );
            add_action( 'woocommerce_checkout_process', array( $this, 'valid_checkout_fields' ) );
		}
	}

	public function change_total_on_checking( $wc_order )
	{
		if ( ! $this->settings->is_active_installments() ) {
    		return;
		}

		$total           = $wc_order->get_total();
		$fields          = Utils::post( 'moip_fields', false );
		$installments    = $fields['installments'];
		$payment_method  = $fields['payment_method'];

		if ( $payment_method != 'payCreditCard' || $installments == 1 ) {
			return;
    	}

    	if ( ! isset( $this->settings->installments['interest'][ $installments ] ) ) {
			return;
		}

		if ( empty( $this->settings->installments['interest'][ $installments ] ) ) {
			return;
		}

		$per_installment = str_replace( ',', '.', $this->settings->installments['interest'][ $installments ] );
		$interest        = (  $per_installment / 100 ) * $total;
		$order_total     = ( $total + $interest );

		$wc_order->set_total( $order_total );
	}

	public function custom_cc_order_total()
	{
		return Checkouts_View::render_cc_title_discount();
	}

	public function moip_billet_discount()
	{
		if ( $this->settings->is_active_render_discount() && $this->settings->is_active_billet_banking() ) {
			return Checkouts_View::render_moip_billet_discount();
		}
	}

	public function process_checkout_moip()
	{
		if ( ! Utils::is_request_ajax() ) {
			exit( 0 );
		}

		if ( ! Utils::verify_nonce_post( 'security', 'checkout' ) ) {
			wp_send_json_error( __( 'Invalid nonce', 'woo-moip-official' ) );
		}

		$return_url = Utils::post( 'returnUrl', '', 'esc_url' );
		$order_id   = Utils::post( 'order', 0, 'intval' );

		if ( empty( $return_url ) || ! $order_id ) {
			wp_send_json_error();
		}

		$model = new Order( $order_id );

		$model->payment_type = Utils::post( 'paymentType' );

		wp_send_json_success( array( 'redirectUrl' => $return_url ) );
	}

	public function process_checkout_default()
	{
		if ( ! Utils::is_request_ajax() ) {
			exit( 0 );
		}

		$order_id = Utils::post( 'order', 0, 'intval' );

		if ( ! $order_id ) {
			wp_send_json_error( __( 'Invalid order', 'woo-moip-official' ) );
		}

		if ( $this->settings->is_enabled_logs() ) {
			$this->settings->log()->add( 'moip-brazil-official', 'WC ORDER CREATED: ' . $order_id );
		}

		$checkout       = new Checkout( $order_id );
		$order          = $checkout->get_order();
		$fields         = $checkout->prepare_fields( $_POST['fields'] );
		$sdk            = Moip_SDK::get_instance();
		$payment_method = Utils::get_value_by( $fields, 'payment_method' );

		if ( empty( $fields ) ) {
			wp_send_json_error( __( 'Empty fields', 'woo-moip-official' ) );
		}

		$moip_order = $order->ct_cache;

		if ( empty( $moip_order ) ) {

			$moip_order = $sdk->create_order( new WC_Order( $order_id ), $fields );

			if ( ! $moip_order['order'] ) {
				wp_send_json_error( __( 'Could not create order. Try again.', 'woo-moip-official' ) );
			}

			$order->ct_cache = $moip_order;
		}

		if ( $this->settings->is_enabled_logs() ) {
			$this->settings->log()->add( 'moip-brazil-official', 'MOIP ORDER CREATED: ' . print_r( $moip_order, true ) );
		}

		$created_payment = $sdk->create_payment( $moip_order['order'], $moip_order['customer'], $fields, false );

		if ( is_string( $created_payment ) ) {

			if ( $this->settings->is_enabled_logs() ) {
				$this->settings->log()->add( 'moip-brazil-official', 'MOIP PAYMENT ERROR: ' . $created_payment );
			}

			wp_send_json_error( $created_payment );
		}

		$data = $created_payment->jsonSerialize();

		if ( $this->settings->is_enabled_logs() ) {
			$this->settings->log()->add( 'moip-brazil-official', 'MOIP PAYMENT CREATED: ' . print_r( $data, true ) );
		}

		$order->payment_id     = $data->id;
		$order->payment_type   = $fields['payment_method'];
		$order->payment_status = $data->status;
		$order->payment_links  = $data->_links;

		if ( $payment_method == 'payBoleto' ) {
			$order->payment_billet_linecode = $data->fundingInstrument->boleto->lineCode;
		}

		if ( $payment_method == 'payCreditCard' ) {
			$order->installments = intval( $fields['installments'] );

			if ( $storage_card = Utils::get_value_by( $fields, 'store_credit_card' ) ) {
				$sdk->customer->credit_card_last_numbers = $data->fundingInstrument->creditCard->last4;
				$sdk->customer->credit_card_brand        = $data->fundingInstrument->creditCard->brand;
			}
		}

		wp_send_json_success( $created_payment );
	}

	public static function process_checkout_transparent( $wc_order )
	{
		if ( ! method_exists( $wc_order, 'get_id' ) ) {
			wc_add_notice( __( 'Invalid order', 'woo-moip-official' ), 'error' );
			return false;
		}

		$settings = Setting::get_instance();
		$order_id = $wc_order->get_id();
		$fields   = Utils::post( 'moip_fields', false );

		if ( empty( $fields ) ) {
			wc_add_notice( __( 'Empty fields', 'woo-moip-official' ), 'error' );
			return false;
		}

		$checkout       = new Checkout( $order_id );
		$model_order    = new Order( $order_id );
		$order          = $checkout->get_order();
		$sdk            = Moip_SDK::get_instance();
		$payment_method = Utils::get_value_by( $fields, 'payment_method' );
		$moip_order     = $order->ct_cache;

		if ( empty( $moip_order ) ) {
			$moip_order = $sdk->create_order( $wc_order, $fields );

			if ( ! $moip_order['order'] ) {
				wc_add_notice( __( 'Could not create order. Try again.', 'woo-moip-official' ), 'error' );
				return false;
			}

			$order->ct_cache = $moip_order;
		}

		$model_order->payment_on_hold();

		$created_payment = $sdk->create_payment( $moip_order['order'], $moip_order['customer'], $fields, $wc_order );

		if ( is_string( $created_payment ) ) {

			wc_add_notice( __( 'Moip: Sorry! We were unable to create your order, please try again later!', 'woo-moip-official' ), 'error' );

			$wc_order->add_order_note(__( 'Moip: It was not possible to create the order on Moip.'.$created_payment, 'woo-moip-official' ) );

			return false;
		}

		$data = $created_payment->jsonSerialize();

		if ( $settings->is_enabled_logs() ) {
			$settings->log()->add( 'moip-brazil-official', 'WOOCOMMERCE ORDER ID: ' . $order_id );
			$settings->log()->add( 'moip-brazil-official', 'MOIP ORDER CREATED: ' . json_encode( $moip_order ) );
			$settings->log()->add( 'moip-brazil-official', 'MOIP PAYMENT CREATED: ' . json_encode( $data ) );
		}

		$order->payment_id     = $data->id;
		$order->payment_type   = $fields['payment_method'];
		$order->payment_status = $data->status;
		$order->payment_links  = $data->_links;

		if ( $payment_method == 'payBoleto' ) {
			$order->payment_billet_linecode = $data->fundingInstrument->boleto->lineCode;
			add_post_meta( $order_id, '_wbo_billet_linecode', $order->payment_billet_linecode, true );
			add_post_meta( $order_id, '_wbo_billet_link', $order->payment_links->payBoleto->redirectHref . '/print', true );
		}

		if ( $payment_method == 'payCreditCard' ) {
			$order->installments = (int) $fields['installments'];
			add_post_meta( $order_id, '_wbo_creditcard_brand', $data->fundingInstrument->creditCard->brand, true );

            if ( $settings->is_enabled_cpf_holder() ) {
                add_post_meta( $order_id, '_wbo_creditcard_cpf_number', $data->fundingInstrument->creditCard->holder->taxDocument->number, true );
			}

			if ( $settings->is_enabled_birth_holder() ) {
                add_post_meta( $order_id, '_wbo_creditcard_birth_number', $data->fundingInstrument->creditCard->holder->birthdate, true );
			}

			if ( $storage_card = Utils::get_value_by( $fields, 'store_credit_card' ) ) {
				$sdk->customer->credit_card_last_numbers = $data->fundingInstrument->creditCard->last4;
				$sdk->customer->credit_card_brand        = $data->fundingInstrument->creditCard->brand;
			}
		}

		add_post_meta( $order_id, '_wbo_payment_method', $payment_method, true );

		return $created_payment;
	}

	/**
	 * Checkout billing fields.
	 *
	 * @param  array $fields Default fields.
	 *
	 * @return array
	 */
	public function wbo_billing_checkout_field( $fields )
	{
		$custom_fields = array();
		$person_type   = intval( $this->settings->field_person_type );

		if ( $person_type === 0 ) {
			$class = 'wbo-person-all';
		}

		if ( $person_type === 1 ) {
			$class = 'wbo-billing-cpf';
		}

		if ( $person_type === 2 ) {
			$class = 'wbo-billing-cnpj';
		}

		if ( $person_type === 0 ) {
			$custom_fields['billing_persontype'] = array(
				'type'     => 'select',
				'label'    => __( 'Person type', 'woo-moip-official' ),
				'class'    => array( 'form-row-wide', 'person-type-field', $class ),
				'required' => true,
				'options'  => array(
					'0' => __( 'Select an option', 'woo-moip-official' ),
					'1' => __( 'Individuals', 'woo-moip-official' ),
					'2' => __( 'Legal Person', 'woo-moip-official' ),
				),
				'priority' => 22,
			);
		}

		if ( $person_type === 0 || $person_type === 1 ) {
			$custom_fields['billing_cpf'] = array(
				'label'       => __( 'CPF', 'woo-moip-official' ),
				'placeholder' => _x( 'CPF', 'placeholder', 'woo-moip-official' ),
				'class'       => array( 'form-row-wide', 'person-type-field', $class ),
				'required'    => false,
				'type'        => 'tel',
				'priority'    => 23,
			);
		}

		if ( $person_type === 0 || $person_type === 2 ) {
			$custom_fields['billing_cnpj'] = array(
				'label'       => __( 'CNPJ', 'woo-moip-official' ),
				'placeholder' => _x( 'CNPJ', 'placeholder', 'woo-moip-official' ),
				'class'       => array( 'form-row-wide', 'person-type-field', $class ),
				'required'    => false,
				'type'        => 'tel',
				'priority'    => 24,
			);

			$custom_fields['billing_company'] = array(
				'label'       => __( 'Company Name', 'woo-moip-official' ),
				'placeholder' => _x( 'Company Name', 'placeholder', 'woo-moip-official' ),
				'class'       => array( 'form-row', 'address-field', $class ),
				'clear'       => true,
				'required'    => false,
				'priority'    => 30,
			);

		}

		if ( $person_type === 0 || $person_type === 1 || $person_type === 2 ) {
			$custom_fields['billing_number'] = array(
				'label'       => __( 'Number', 'woo-moip-official' ),
				'placeholder' => _x( 'Number', 'placeholder', 'woo-moip-official' ),
				'class'       => array( 'form-row-first', 'address-field' ),
				'clear'       => true,
				'required'    => true,
				'priority'    => 55,
			);

			$custom_fields['billing_neighborhood'] = array(
				'label'       => __( 'Neighborhood', 'woo-moip-official' ),
				'placeholder' => _x( 'Neighborhood', 'placeholder', 'woo-moip-official' ),
				'class'       => array( 'form-row-last', 'address-field' ),
				'clear'       => true,
				'required'    => true,
				'priority'    => 56,
			);
		}

		$fields = wp_parse_args( $custom_fields, $fields );

		return apply_filters( Core::tag_name( 'billing_checkout_fields' ), $fields );
    }

	/**
	 * Checkout shipping fields.
	 *
	 * @param  array $fields Default fields.
	 *
	 * @return array
	 */
	public function wbo_shipping_checkout_field( $fields ) {
		$custom_fields = [];

		$custom_fields['shipping_number'] = [
			'label'       => __( 'Número', 'wc-ame-digital' ),
			'placeholder' => __( 'Número', 'wc-ame-digital' ),
			'class'       => [ 'form-row-first', 'address-field' ],
			'clear'       => true,
			'required'    => true,
			'priority'    => 55,
		];

		$custom_fields['shipping_neighborhood'] = [
			'label'       => __( 'Bairro', 'wc-ame-digital' ),
			'placeholder' => __( 'Bairro', 'wc-ame-digital' ),
			'class'       => [ 'form-row-last', 'address-field' ],
			'clear'       => true,
			'required'    => true,
			'priority'    => 56,
		];

		$fields = wp_parse_args( $custom_fields, $fields );

		return apply_filters( Core::tag_name( 'shipping_checkout_fields' ), $fields );
	}

	public function wbo_address_formats( $formats )
    {
		$formats['BR'] = "{name}\n{address_1}, {number}\n{address_2}\n{neighborhood}\n{city}\n{state}\n{postcode}\n{country}";

		return $formats;
	}

	public function wbo_address_replacements( $replacements, $args )
    {
		$args = wp_parse_args(
			$args,
			[
				'number'       => '',
				'neighborhood' => '',
			]
		);

		$replacements['{number}']       = $args['number'];
		$replacements['{neighborhood}'] = $args['neighborhood'];

		return $replacements;
	}

	public function wbo_update_formatted_billing_address( $address, $wc_order )
    {
        // WooCommerce 3.0 or later.
		if ( method_exists( $wc_order, 'get_meta' ) ) {
			$address['number']       = $wc_order->get_meta( '_billing_number' );
			$address['neighborhood'] = $wc_order->get_meta( '_billing_neighborhood' );
		} else {
			$address['number']       = $wc_order->billing_number;
			$address['neighborhood'] = $wc_order->billing_neighborhood;
		}

		return $address;
    }

    public function wbo_update_formatted_shipping_address( $address, $wc_order )
    {
        if ( ! is_array( $address ) ) {
			return $address;
		}

		// WooCommerce 3.0 or later.
		if ( method_exists( $wc_order, 'get_meta' ) ) {
			$address['number']       = $wc_order->get_meta( '_shipping_number' );
			$address['neighborhood'] = $wc_order->get_meta( '_shipping_neighborhood' );
		} else {
			$address['number']       = $wc_order->shipping_number;
			$address['neighborhood'] = $wc_order->shipping_neighborhood;
		}

		return $address;
    }

	public function valid_checkout_fields()
	{
		if ( apply_filters( Core::tag_name( 'disable_checkout_fields_validation' ), false ) ) {
			return;
		}

		$person_type           = intval( $this->settings->field_person_type );
		$fields                = Utils::post( 'moip_fields', false );
		$payment_method        = 'woo-moip-official';
		$chosen_payment_method = WC()->session->get( 'chosen_payment_method' );

		if ( $person_type === 0 ) {
			$person_type = Utils::post( 'billing_persontype', 0, 'intval' );
		}

		if ( $chosen_payment_method == $payment_method && $fields['payment_method'] === 'payCreditCard' ) {
			return Checkout::wbo_validate_credit_card();
		}

		$cpf   = Utils::post( 'billing_cpf' );
		$cnpj  = Utils::post( 'billing_cnpj' );

		if ( $person_type === 1 ) {
			$this->_check_single_field( 'cpf', $cpf );
		}

		if ( $person_type === 2 ) {
			$this->_check_single_field( 'cnpj', $cnpj );
		}
	}

	public function validate_cc_fields()
	{
		$fields                = Utils::post( 'moip_fields', false );
		$payment_method        = 'woo-moip-official';
		$chosen_payment_method = WC()->session->get( 'chosen_payment_method' );

		if ( $chosen_payment_method == $payment_method && $fields['payment_method'] === 'payCreditCard' ) {
			return Checkout::wbo_validate_credit_card();
		}
	}

	private function _check_single_field( $type, $value )
	{
		$name     = strtoupper( $type );
		$callback = 'is_' . $type;

		if ( empty( $value ) ) {
			wc_add_notice(
				sprintf( '<strong>%s</strong> %s.',
					__( $name, 'woo-moip-official' ),
					__( 'is a required field', 'woo-moip-official' )
				),
				'error'
			);
		}

		if ( ! Utils::$callback( $value ) ) {
			wc_add_notice(
				sprintf( '<strong>%s</strong> %s.',
					__( $name, 'woo-moip-official' ),
					__( 'is not valid', 'woo-moip-official' )
				),
				'error'
			);
		}
	}
}
