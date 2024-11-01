<?php
namespace Woocommerce\Moip\Fields\General;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Moip\Model\Custom_Gateway;

//Views
use Woocommerce\Moip\View;

class Config
{
	public static function section_auth() {
		return [
			'title'       => __( 'APP authorization', 'woo-moip-official' ),
			'type'        => 'title',
			'description' => self::get_app_status(),
		];
	}

	public static function get_app_status() {
		$model = new Custom_Gateway();

		$message = sprintf(
			'%s<br><strong>Status: </strong><span class="app-not-authorized">%s</span>%s',
			__( 'To make your sales, you must authorize this application on Moip.', 'woo-moip-official' ),
			__( 'Not authorized', 'woo-moip-official' ),
			self::get_authorized_app_btn()
		);

		if ( $model->settings->authorize_data ) {
			$account_id = $model->settings->authorize_account;
			$text  = __( 'Account ID: ' );

			if ( !$account_id ) {
				$text = __( 'Unable to get account id.' );
			}

			$message = sprintf(
				'%s<br><strong>Status: </strong><span class="app-authorized">%s</span> %s <br>
				<strong>%s</strong><span>%s</span>%s',
				__( 'To make your sales, you must authorize this application on Moip.', 'woo-moip-official' ),
				__( 'Authorized', 'woo-moip-official' ),
				sprintf( 'via %s', strtoupper( $model->settings->authorize_mode ) ),
				$text,
				$account_id,
				self::get_authorized_app_btn()
			);
		}

		return $message;
	}

	public static function get_authorized_app_btn() {
		$model = new Custom_Gateway();

		$title = __( 'Authorize App', 'woo-moip-official' );
		$class = 'button-primary';

		if ( $model->settings->authorize_data ) {
			$title = __( 'New authorize', 'woo-moip-official' );
			$class = '';
		}

		return sprintf(
			'<p>
				<a href="#"
				   class="button %s"
				   id="oauth-app-btn">
					%s
				</a>
			</p>',
			$class,
			$title
		);
	}

	public static function section_settings() {
		return [
			'title' => __( 'Settings', 'woo-moip-official' ),
			'type'  => 'title',
		];
	}

	public static function section_payment_settings() {
		return [
			'title' => __( 'Payment settings', 'woo-moip-official' ),
			'type'  => 'title',
		];
	}

	public static function field_enabled() {
		return [
			'title'   => __( 'Enable', 'woo-moip-official' ),
			'type'    => 'checkbox',
			'label'   => __( 'Enable payment', 'woo-moip-official' ),
			'default' => 'no',
		];
	}

	public static function field_title() {
		return [
			'title'       => __( 'Title', 'woo-moip-official' ),
			'description' => __( 'This the title which the user sees during checkout.', 'woo-moip-official' ),
			'desc_tip'    => true,
			'default'     => __( 'Moip', 'woo-moip-official' ),
		];
	}

	public static function field_description() {
		return [
			'title'   => __( 'Description', 'woo-moip-official' ),
			'default' => __( 'Pay with Moip', 'woo-moip-official' ),
		];
	}

	public static function field_invoice_name() {
		return [
			'title'             => __( 'Invoice name', 'woo-moip-official' ),
			'desc_tip'          => true,
			'placeholder'       => __( 'Maximum of 13 characters', 'woo-moip-official' ),
			'description'       => __( 'It allows the shopkeeper to send a text of up to 13 characters that will be printed on the bearer\'s invoice, next to the shop identification, respecting the length of the flags.', 'woo-moip-official' ),
			'custom_attributes' => [
				'data-action'    => 'invoice-name',
				'data-element'   => 'validate',
				'maxlength'      => 13,
				'data-error-msg' => __( 'This field is required.', 'woo-moip-official' ),
			],
		];
	}

	public static function field_invoice_prefix() {
		return [
			'title'       => __( 'Invoice prefix', 'woo-moip-official' ),
			'default'     => 'WC-',
			'desc_tip'    => true,
			'description' => __( 'Enter a prefix for your invoice numbers. If you use your Wirecard account for multiple stores, make sure this prefix is unique because Wirecard will not allow orders with the same invoice number.', 'woo-moip-official' ),
		];
	}

	public static function field_payment_type() {
		return [
			'type'              => 'select',
			'title'             => __( 'Payment Type', 'woo-moip-official' ),
			'class'             => 'wc-enhanced-select',
			'default'           => 'default_checkout',
			'custom_attributes' => [
				'data-element'  => 'checkout',
				'data-action'   => 'checkout-type',
			],
			'options' => [
				//'default_checkout'     => __( 'Default Checkout', 'woo-moip-official' ),
				'transparent_checkout' => __( 'Transparent Checkout', 'woo-moip-official' ),
				'moip_checkout'        => __( 'Moip Checkout', 'woo-moip-official' ),
			],
		];
	}

	public static function field_public_key() {
		return [
			'title'             => __( 'Public Key', 'woo-moip-official' ),
			'type'              => 'textarea',
			'css'               => 'height: 200px',
			'custom_attributes' => [
				'data-element'   => 'validate',
				'data-field'     => 'public-key',
				'data-error-msg' => __( 'This field is required.', 'woo-moip-official' ),
			],
			'description' => __( 'Allows credit card data to be sent encrypted, generating more security in your transactions.OBS: You can not change this field manually.', 'woo-moip-official' )
		];
	}

	public static function field_billet_banking() {
		return [
			'title'   => __( 'Billet Banking', 'woo-moip-official' ),
			'type'    => 'checkbox',
			'label'   => __( 'Enable Billet Banking', 'woo-moip-official' ),
			'default' => 'yes',
			'custom_attributes' => [
				'data-field' => 'wbo-billet-banking',
			],
		];
	}

	public static function field_credit_card() {
		return [
			'title'   => __( 'Credit Card', 'woo-moip-official' ),
			'type'    => 'checkbox',
			'label'   => __( 'Enable Credit Card', 'woo-moip-official' ),
			'default' => 'yes',
			'custom_attributes' => [
				'data-field' => 'wbo-credit-card',
			],
		];
	}

	public static function field_banking_debit() {
		return [
			'title'   => __( 'Banking Debit', 'woo-moip-official' ),
			'type'    => 'checkbox',
			'label'   => __( 'Enable Banking Debit', 'woo-moip-official' ),
			'default' => 'yes',
			'custom_attributes' => [
				'data-field' => 'debit',
			],
		];
	}

	public static function section_tools() {
		return [
			'title' => __( 'Checkout', 'woo-moip-official' ),
			'type'  => 'title',
		];
	}

	public static function field_person_type() {
		$model = new Custom_Gateway();

		return [
			'type'              => 'select',
			'title'             => __( 'Person Type', 'woo-moip-official' ),
			'description'       => __( 'Choose the type of person (Individuals and/or Legal Person) to appear on the checkout page.', 'woo-moip-official' ),
			'desc_tip'          => true,
			'class'             => 'wc-enhanced-select',
			'default'           => 0,
			'options'           => $model->get_persontype_options(),
			'custom_attributes' => [
				'data-action' => 'checkout-person-type',
			],
		];
	}

	public static function section_notification() {
		return [
			'title'       => __( 'Notifications', 'woo-moip-official' ),
			'type'        => 'title',
			'description' => self::webhook_fields_notifications()
		];
	}

	public static function webhook_fields_notifications() {
		$model      = new Custom_Gateway();
		$all_token  = '';

		if ( $model->settings->authorize_data ) {
			$all_token    .= View\Custom_Gateways::render_notification_webhook( $model->settings );
		}

		return $all_token;
	}

	public static function get_fields() {
		return [
			'auth'                     => self::section_auth(),
			'section_settings'         => self::section_settings(),
			'enabled'                  => self::field_enabled(),
			'title'                    => self::field_title(),
			'description'              => self::field_description(),
			'invoice_name'             => self::field_invoice_name(),
			'invoice_prefix'           => self::field_invoice_prefix(),
			'section_payment_settings' => self::section_payment_settings(),
			'public_key'               => self::field_public_key(),
			'payment_api'              => self::field_payment_type(),
			'billet_banking'           => self::field_billet_banking(),
			'credit_card'              => self::field_credit_card(),
			'banking_debit'            => self::field_banking_debit(),
			'section_tools'            => self::section_tools(),
			'field_person_type'        => self::field_person_type(),
			'section_notification'     => self::section_notification()
		];
	}
}
