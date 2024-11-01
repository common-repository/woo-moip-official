<?php
namespace Woocommerce\Moip\Controller;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

//WooCommerce
use WC_Payment_Gateway;
use WC_Order;

use Woocommerce\Moip\Core;
use Woocommerce\Moip\Helper\Utils;
use Woocommerce\Moip\Model\Custom_Gateway;
use Woocommerce\Moip\Model\Order;
use Woocommerce\Moip\Model\Moip_SDK;
use Woocommerce\Moip\Fields;

class Custom_Gateways extends WC_Payment_Gateway
{
	/**
	 * @var Object
	 */
	public $model;

	/**
	 * @var Object
	 */
	public $moip_order;

	public function __construct()
	{
		$this->model  = new Custom_Gateway();

		$this->site_name          = get_bloginfo( 'name' );
        $this->prefix_name        = substr( wp_hash( $this->site_name ), 0, 4 );
        $this->site_permalinks    = get_option( 'permalink_structure' );
        $this->postname_url       = '/%postname%/';

		$this->id                 = Core::SLUG;
		$this->method_title       = __( 'Moip Brazil Official', 'woo-moip-official' );
		$this->method_description = __( 'Payment Gateway moip', 'woo-moip-official' );
		$this->has_fields         = true;
		//$this->icon               = Core::plugins_url( 'assets/images/icons/logo.png' );

		if ( $this->model->settings->is_checkout_transparent() ) {
			$this->order_button_text  = __( 'Proceed to payment', 'woo-moip-official' );
		}

		$this->_set_webhook();
		$this->init_form_fields();
		$this->init_settings();
		$this->_check_errors();

		$this->enabled     = $this->get_option( 'enabled', 'no' );
		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );

		if ( is_admin() ) {
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thank_you_page' ) );
		add_action( 'admin_notices', array( $this, 'display_errors' ) );

	}

	public function payment_fields()
	{
		if ( $description = $this->get_description() ) {

			if ( $this->model->settings->is_sandbox() ) {
				$description  = trim( $description );
				$description .= '<br><b>'.__( 'TEST MODE ENABLED!' ).'</b>';
			}

			echo wpautop( wptexturize( $description ) );
		}

		$cart_total    = $this->get_order_total();
		$cart_subtotal = WC()->cart->subtotal;

		$public_key    = $this->model->settings->public_key;
		$model         = $this->model;

		if ( $this->model->settings->is_checkout_transparent() ) {
			Utils::template_include( 'templates/checkout-transparent',
				compact( 'cart_total', 'public_key', 'model', 'cart_subtotal' )
			);
		}
	}

	/**
	 * Output the admin options table.
	 *
	 * @since 1.0
	 * @param null
	 * @return Void
	 */
	public function admin_options()
	{?>
		<div id="message" class="wirecard-message-updated notice">
		<?php
            printf(
                '<p>%s <strong>%s</strong>, %s. %s</p>
                <p class="submit">
				<a href="%s" class="button-primary" target="_blank">%s</a>
				<a href="%s" class="button-primary" target="_blank">%s</a>
                <a href="%s" class="button-secondary" target="_blank">%s</a>
				</p>
				<p><strong>%s: </strong>%s</p>',
                __( 'If you like', 'woo-moip-official' ),
                __( 'Moip Brazil Official', 'woo-moip-official' ),
                __( 'please leave us a ★★★★★ rating', 'woo-moip-official' ),
                __( 'A huge thanks in advance!', 'woo-moip-official' ),
                Core::support_link(),
				__( 'Apiki Support', 'woo-moip-official' ),
				Core::documentation_link(),
                __( 'Documentation', 'woo-moip-official' ),
                Core::wc_wordpress_link(),
				__( 'Leave us a rating', 'woo-moip-official' ),
				__( 'Version', 'woo-moip-official' ),
				APIKI_MOIP_VERSION
            );
		?>
		</div>
    <?php if ( $this->site_permalinks != $this->postname_url ) : ?>
        <div id="message" class="notice-warning notice">
        <?php
            printf(
                '<p><strong>%s: </strong>%s <strong>%s</strong>.</p>',
                __( 'Before Authorizing', 'woo-moip-official' ),
                __( 'Save the permalinks as', 'woo-moip-official' ),
                __( 'Post name', 'woo-moip-official' )
            );
        ?>
        </div>
    <?php endif; ?>

    <?php if ( strpos( $this->site_permalinks, 'index.php' ) ) : ?>
        <div id="message" class="notice-warning notice">
        <?php
            printf(
                '<p><strong>%s: </strong>%s <strong>%s</strong>.</p>',
                __( 'Before Authorizing', 'woo-moip-official' ),
                __( 'Remove permalinks from "index.php" and save as', 'woo-moip-official' ),
                __( 'Post name', 'woo-moip-official' )
            );
        ?>
        </div>
    <?php endif; ?>
	<?php
		printf(
			$this->build_submenu() . '<div %s><table class="form-table">%s</table></div>',
			Utils::get_component( 'settings' ),
			$this->generate_settings_html( $this->get_form_fields(), false )
		);
	}

	public function build_submenu()
	{

		ob_start();

		echo '<ul class="subsubsub wirecard-submenu">';

		$sections = array(
			'wbo-general'       => __( 'General', 'woo-moip-official' ),
			'wbo-credit-card'   => __( 'Credit Card', 'woo-moip-official' ),
			'wbo-billet'        => __( 'Bank Billet', 'woo-moip-official' ),
            'wbo-payment-split' => __( 'Payment Split', 'woo-moip-official' ),
            'wbo-tools'         => __( 'Tools', 'woo-moip-official' )
		);

		$array_keys      = array_keys( $sections );
		$current_section = Utils::get( 'moip-tab', 'wbo-general' );

		foreach ( $sections as $id => $label ) {
			$link = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=woo-moip-official&moip-tab=' . sanitize_title( $id ) );
			$pipe = ( end( $array_keys ) === $id ? '' : '|' );
			echo '<li><a href="' . $link . '" class="' . ( $current_section === $id ? 'current' : '' ) . '">' . $label . '</a> ' . $pipe . ' </li>';
		}

		echo '</ul><br class="clear" />';

		$content = ob_get_contents();

		ob_end_clean();

		return $content;
	}

	/**
	 * Return the name of the option in the WP DB.
	 * @since 1.0
	 * @return string
	 */
	public function get_option_key()
	{
		return $this->model->settings->get_option_key();
	}

	public function is_available() {
		return ( $this->model->settings->is_enabled() && ! $this->get_errors() && $this->model->supported_currency() );
	}

	public function init_form_fields()
	{
		$current_section = Utils::get( 'moip-tab', 'wbo-general' );

		switch ( $current_section ) {
			case 'wbo-general':
				$fields = Fields\General\Config::get_fields();
				break;
			case 'wbo-credit-card':
				$fields = Fields\Credit_Card\Config::get_fields();
				break;
			case 'wbo-billet':
				$fields = Fields\Billet\Config::get_fields();
				break;
			case 'wbo-payment-split':
				$fields = Fields\Payment_Split\Config::get_fields();
				break;
			case 'wbo-tools':
				$fields = Fields\Tools\Config::get_fields();
				break;
			default:
				$fields = Fields\General\Config::get_fields();
				break;
		}

		$this->form_fields = $fields;
	}

	public function process_payment( $order_id )
	{
		$wc_order = new WC_Order( $order_id );

		if ( $this->model->settings->is_checkout_transparent() ) {
			return $this->_process_checkout_transparent( $wc_order, $order_id );
		}

		return array(
			'result'   => 'success',
			'redirect' => $wc_order->get_checkout_payment_url( true ),
		);
	}

	private function _process_checkout_transparent( $wc_order, $order_id )
	{
		$payment = Checkouts::process_checkout_transparent( $wc_order );
		$order   = new WC_Order( $order_id );

		if ( ! $payment ) {
			return array(
				'result'   => 'fail',
				'redirect' => '',
			);
		}

		WC()->cart->empty_cart();

		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $wc_order ),
		);
	}

	public function receipt_page( $order_id )
	{
		if ( $this->model->settings->is_checkout_default() ) {
			$this->checkout_default( $order_id );
		} else {
			$this->checkout_moip( $order_id );
		}
	}

	public function thank_you_page( $order_id )
	{
		$order = new WC_Order( $order_id );

		if ( $this->model->settings->is_checkout_transparent() ) {
			require_once Core::get_file_path( 'thank-you-page-transparent.php', 'templates/' );
			return;
		}

		require_once Core::get_file_path( 'thank-you-page.php', 'templates/' );
	}

	public function checkout_moip( $order_id )
	{
		$order         = new Order( $order_id );
		$wc_order      = new WC_Order( $order_id );
		$payment_links = $order->payment_links;

		if ( ! is_object( $payment_links ) ) {
			$moip_sdk         = Moip_SDK::get_instance();
			$this->moip_order = $moip_sdk->create_order( $wc_order );

			if ( ! $this->moip_order ) {
				return;
			}

			$payment_links = $this->moip_order['response']->_links->checkout;
		}

		require_once( Core::get_file_path( 'checkout-moip.php', 'templates/' ) );

		unset( $order );
		unset( $wc_order );
	}

	public function checkout_default( $order_id )
	{
		$wc_order = new WC_Order( $order_id );

		require_once( Core::get_file_path( 'checkout-default.php', 'templates/' ) );
	}

	private function _set_webhook() {
		Webhooks::set_token( $this );
	}

	private function _check_errors()
	{
		if ( ! $this->model->settings->authorize_data ) {
			$this->add_error( __( 'Application not authorized.', 'woo-moip-official' ) );
		}

		if ( ! $this->model->settings->invoice_name ) {
			$this->add_error( __( 'Invoice name is required.', 'woo-moip-official' ) );
		}

		if ( ! $this->model->settings->public_key ) {
			$this->add_error( __( 'Not informed public key.', 'woo-moip-official' ) );
		}

		return $this->errors;
	}

	public function display_errors()
	{
		if ( ! $this->get_errors() ) {
			return;
		}

		echo '<div id="woocommerce_errors" class="error notice is-dismissible">';

		printf(
			'<p><strong>%s:</strong> %s <a href="%s">%s</a></p>',
			Core::get_name(),
			__( 'You need to set up your Wirecard data to use the payment method.', 'woo-moip-official' ),
			Core::get_page_link(),
			__( 'Go to setup', 'woo-moip-official' )
		);

		echo '<ol>';

		foreach ( $this->get_errors() as $error ) {
			printf(
				'<li><strong>%s</strong></li> ',
				wp_kses_post( $error )
			);
		}

		echo '</ol>';
		echo '</div>';
	}

	/**
	 * Get HTML for descriptions.
	 *
	 * @param  array $data
	 * @return string
	 */
	public function get_description_html( $data )
	{
		if ( $data['desc_tip'] === true ) {
			return;
		} elseif ( ! empty( $data['desc_tip'] ) ) {
			$description = $data['description'];
		} elseif ( ! empty( $data['description'] ) ) {
			$description = $data['description'];
		} else {
			return;
		}

		return sprintf(
			'<p class="description %s">%s</p>',
			sanitize_html_class( Utils::get_value_by( $data, 'class_p' ) ),
			strip_tags( $description, '<a><span>' )
		);
	}

	public function generate_installments_html( $key, $data )
	{
		$field_key = $this->get_field_key( $key );
		$defaults  = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array(),
		);

		$data  = wp_parse_args( $data, $defaults );
		$value = (array) $this->get_option( $key, array() );

		ob_start();

		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<?php echo esc_html( $this->get_tooltip_html( $data ) ); ?>
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
			</th>
			<td class="forminp">
				<fieldset>
					<?php
						for ( $i = 1; $i <= 12; $i++ ) :
							$interest = isset( $value['interest'][ $i ] ) ? $value['interest'][ $i ] : '';
					?>
					<p data-installment="<?php echo esc_attr( $i ); ?>">
						<input class="small-input" type="text" value="<?php echo esc_attr( $i ); ?>"
							   <?php disabled( 1, true ); ?> />
						<input class="small-input" type="text"
							   placeholder="0,00"
							   data-mask="##0,00" data-mask-reverse="true"
							   name="<?php echo esc_attr( $field_key ); ?>[interest][<?php echo esc_attr( $i ); ?>]"
							   id="<?php echo esc_attr( $field_key ); ?>" value="<?php echo esc_attr( wc_format_localized_price( $interest ) ); ?>" />%
					</p>
					<?php endfor; ?>

					<?php echo esc_html( $this->get_description_html( $data ) ); ?>
				</fieldset>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}

	public function validate_installments_field( $key, $value )
	{
		return $value;
	}
}