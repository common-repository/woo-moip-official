<?php
namespace Woocommerce\Moip\Fields\Payment_Split;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Moip\Core;
use Woocommerce\Moip\Helper\Utils;
use Woocommerce\Moip\Model\Custom_Gateway;
use Woocommerce\Moip\Model\Setting;

//Views
use Woocommerce\Moip\View;

class Config
{
    public static function section_payment_split()
	{
		return [
			'title'       => __( '', 'woo-moip-official' ),
			'type'        => 'title',
			'description' => sprintf(
				'%s',
				self::split_message_soon()
			),
		];
    }

    public static function split_message_soon()
	{
		$model      = new Custom_Gateway();
		$authorized = strtoupper( $model->settings->authorize_mode );
		$message    = __( 'Is authorized via <strong>'.$authorized.'</strong>!', 'woo-moip-official' );

		if ( empty( $authorized ) ) {
			$message    = __( 'NOT AUTHORIZED :(', 'woo-moip-official' );
		}

        return $message;
    }

    public static function moip_checkbox_payment_split()
	{
		return [
			'title'             => __( 'Payment Split', 'woo-moip-official' ),
			'type'              => 'checkbox',
			'label'             => __( 'Enable payment split', 'woo-moip-official' ),
			'default'           => 'no',
			'description'       => __( 'After generating the app you need to enable split to work.', 'woo-moip-official' ),
			'desc_tip'          => false,
			'custom_attributes' => [
				'data-field' => 'render-wirecard-payment-split',
			],
		];
	}

	public static function split_shipping_method()
	{
		return [
			'type'              => 'select',
			'title'             => __( 'Shipping Method', 'woo-moip-official' ),
			'class'             => 'wc-enhanced-select',
			'default'           => 'split_shipping_vendor',
			'description'       => __( 'Choose who will pay the split payment shipping fee. By default it will be the Vendor.', 'woo-moip-official' ),
			'desc_tip'          => false,
			'custom_attributes' => [
				'data-element' => 'split-shipping-method',
				'data-action'  => 'split-shipping-method-type',
			],
			'options' => [
				'split_shipping_admin'  => __( 'Administrator', 'woo-moip-official' ),
				'split_shipping_vendor' => __( 'Vendor', 'woo-moip-official' ),
			],
		];
	}

	public static function marketplace_options_type()
	{
		$marketplace_default = 'empty_marketplace';
		/*Dokan Lite Class*/
		if ( class_exists( 'WeDevs_Dokan' ) ){
			$marketplace_default = 'dokan_marketplace';
		}
		/*WCFM Class*/
		if ( class_exists( 'WCFMmp' ) ){
			$marketplace_default = 'wcfm_marketplace';
		}

		return [
			'type'              => 'select',
			'title'             => __( 'Marketplace Type', 'woo-moip-official' ),
			'class'             => 'wc-enhanced-select',
			'default'           => $marketplace_default,
			'description'       => __( 'Select the marketplace according to the installed plugin, Dokan or WCFM.' , 'woo-moip-official' ),
			'custom_attributes' => [
				'data-element'  => 'marketplace',
				'data-action'   => 'marketplace-type',
			],
			'options' => [
				'empty_marketplace' => __( 'Select your Marketplace', 'woo-moip-official' ),
				'dokan_marketplace' => __( 'Dokan', 'woo-moip-official' ),
				'wcfm_marketplace'  => __( 'WCFM', 'woo-moip-official' ),
			],
		];
	}

    public static function moip_split_name()
	{
		return [
			'title'             => __( 'Moip Split Name', 'woo-moip-official' ),
			'type'              => 'text',
			'desc_tip'          => false,
			'default'           => '',
			'placeholder'       => __( 'Moip Brazil Official Split', 'woo-moip-official' ),
			'description'       => __( 'Enter the name of the payment split for your store. Default: Moip Brazil Official Split' , 'woo-moip-official' ),
			'custom_attributes' => [
				'maxlength' => 40
			],
		];
	}

    public static function moip_split_description()
	{
		return [
			'title'             => __( 'Moip Split Description', 'woo-moip-official' ),
			'type'              => 'text',
			'desc_tip'          => false,
			'default'           => '',
			'placeholder'       => __( 'Moip Brazil Official Payment Split', 'woo-moip-official' ),
			'description'       => __( 'Enter the description of the payment split for your store. Default: Moip Brazil Official Payment Split' , 'woo-moip-official' ),
			'custom_attributes' => [
				'maxlength' => 50
			],
		];
	}

	public static function moip_manual_app_credentials()
	{
		return [
			'title'       => __( 'APP credentials', 'woo-moip-official' ),
			'type'        => 'title',
			'desc_tip'    => false
		];
	}

	public static function moip_manual_token_account()
	{
		return [
			'title'       => __( 'Moip Token', 'woo-moip-official' ),
			'type'        => 'text',
			'desc_tip'    => false,
			'default'     => '',
			'custom_attributes' => [
				'data-action'    => 'wirecard-manual-token',
				'data-element'   => 'validate',
				'data-error-msg' => __( 'This field is required.', 'woo-moip-official' ),
			],
			'description' => sprintf(
				__( 'Copy and paste moip token, where to find: %s' , 'woo-moip-official' ),
				self::get_moip_manual_token()
			)
		];
	}

	public static function get_moip_manual_token()
	{
		return '<a href="' . Utils::get_url_endpoint_connect() . '">'
			. __( 'My account &gt; Settings &gt; Access Keys', 'woo-moip-official' ) . '</a> '
			. __( 'In Authentication Key copy TOKEN.', 'woo-moip-official' );
	}

	public static function moip_manual_key_account()
	{
		return [
			'title'       => __( 'Moip Key', 'woo-moip-official' ),
			'type'        => 'text',
			'desc_tip'    => false,
			'default'     => '',
			'custom_attributes' => [
				'data-action'    => 'wirecard-manual-key',
				'data-element'   => 'validate',
				'data-error-msg' => __( 'This field is required.', 'woo-moip-official' ),
			],
			'description' => sprintf(
				__( 'Copy and paste moip key, where to find: %s' , 'woo-moip-official' ),
				self::get_moip_manual_key()
			)
		];
	}

	public static function get_moip_manual_key()
	{
		return '<a href="' . Utils::get_url_endpoint_connect() . '">'
			. __( 'My account &gt; Settings &gt; Access Keys', 'woo-moip-official' ) . '</a> '
			. __( 'In Authentication Key click: [click here to show key] copy KEY.', 'woo-moip-official' );
    }

    public static function section_moip_app()
	{
		return [
			'title' => __( 'Moip APP', 'woo-moip-official' ),
            'type'  => 'title',
            'description' => sprintf(
				'%s',
				self::generate_moip_app()
			),
		];
    }

	public static function generate_moip_app()
	{
		$button_notification = View\Payment_Splits::render_button_moip_split();

		return $button_notification;
    }

    public static function section_app_info()
	{
		return [
			'title'       => __( 'App Info', 'woo-moip-official' ),
			'type'        => 'title',
			'description' => self::get_app_info()
		];
	}

	public static function get_app_info()
	{
		$model      = new Custom_Gateway();
		$all_token  = '';

		if ( $model->settings->authorize_data ) {
			$all_token .= View\Payment_Splits::render_info_moip_app( $model->settings );
		}

		return $all_token;
	}

	public static function section_title_marketplace()
	{
		return [
			'title'       => __( '', 'woo-moip-official' ),
			'type'        => 'title',
			'description' => sprintf(
				'%s',
				self::marketplace_message_soon()
			),
		];
    }

    public static function marketplace_message_soon()
	{
		$dokan_plugin = 'https://wordpress.org/plugins/dokan-lite/';
		$wcfm_plugin  = 'https://wordpress.org/plugins/wc-multivendor-marketplace/';

        return sprintf(
            '%s
            <a href="%s">%s</a> %s <a href="%s">%s</a> %s',
            __( 'To work with the payment split, you need to install and activate the', 'woo-moip-official' ),
            esc_url( $dokan_plugin ),
			__( 'Dokan Lite', 'woo-moip-official' ),
			__( 'or', 'woo-moip-official' ),
			esc_url( $wcfm_plugin ),
			__( 'WCFM Marketplace', 'woo-moip-official' ),
            __( 'marketplace plugin.', 'woo-moip-official' )
        );
	}

	public static function section_title_payment_type()
	{
		return [
			'title'       => __( '', 'woo-moip-official' ),
			'type'        => 'title',
			'description' => sprintf(
				'%s',
				self::payment_type_message_soon()
			),
		];
    }

    public static function payment_type_message_soon()
	{
		$url_plugin = Utils::get_admin_url( 'admin.php' ) . '?page=wc-settings&tab=checkout&section=woo-moip-official&wirecard-tab=wbo-general';

        return sprintf(
            '%s
            <a href="%s">%s</a>',
            __( 'To work with the payment split you need to change the payment type to Transparent Checkout, ', 'woo-moip-official' ),
            esc_url( $url_plugin ),
            __( 'CHANGE NOW', 'woo-moip-official' )
        );
    }

	public static function get_fields()
	{
		$setting = Setting::get_instance();

		/*Dokan Lite Class and WCFM Class*/
		if ( !class_exists( 'WeDevs_Dokan' ) && !class_exists( 'WCFMmp' ) ){
			return [ 'section_title_marketplace' => self::section_title_marketplace() ];
		}

		/*Checkout Transparente*/
		if ( !$setting->is_checkout_transparent() ) {
			return [ 'section_title_payment_type' => self::section_title_payment_type() ];
		}

		return [
			'section_payment_split'       => self::section_payment_split(),
            'wirecard_payment_split'      => self::moip_checkbox_payment_split(),
			'marketplace_options_type'    => self::marketplace_options_type(),
			'split_shipping_method'       => self::split_shipping_method(),
            'wirecard_split_name'         => self::moip_split_name(),
			'wirecard_split_description'  => self::moip_split_description(),
			'wirecard_manual_credentials' => self::moip_manual_app_credentials(),
			'wirecard_manual_token'       => self::moip_manual_token_account(),
			'wirecard_manual_key'         => self::moip_manual_key_account(),
			'section_wirecard_app'        => self::section_moip_app(),
			'section_app_info'            => self::section_app_info()
		];
	}
}
