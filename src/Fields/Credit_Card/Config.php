<?php
namespace Woocommerce\Moip\Fields\Credit_Card;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Moip\Core;
use Woocommerce\Moip\Model\Setting;
use Woocommerce\Moip\Model\Custom_Gateway;

class Config
{
	public static function not_found()
	{
		return [
			'title' => __( 'Enable credit card option!', 'woo-moip-official' ),
			'type'  => 'title',
			'description' => sprintf(
				__( 'Click the link to go back and activate your credit card: %s' , 'woo-moip-official' ),
				self::get_general_url()
			),
		];
	}

	public static function not_found_cc()
	{
		return [
			'title' => __( 'Credit card setup is not yet available for the wirecard checkout!', 'woo-moip-official' ),
			'type'  => 'title'
		];
	}

	public static function get_general_url()
	{
		$url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=woo-moip-official&wirecard-tab=wbo-general' );

		return '<a href="' . esc_url( $url ) . '">' . __( 'General', 'woo-moip-official' ) . '</a>';
	}

	public static function section_installments()
	{
		return [
			'title' => __( 'Credit Card Settings', 'woo-moip-official' ),
			'type'  => 'title',
		];
    }

	public static function save_credit_card_option()
    {
    	return [
			'title'       => __( 'Save Credit Card', 'woo-moip-official' ),
			'description' => __( 'Show option to save the card number in transparent checkout.', 'woo-moip-official' ),
			'desc_tip'    => true,
			'type'        => 'checkbox',
			'label'       => __( 'Show Option', 'woo-moip-official' ),
			'default'     => 'no',
			'custom_attributes' => [
				'data-field' => 'credit-card-option',
			],
		];
    }

	public static function field_credit_card_installments( $field )
	{
		$model        = new Custom_Gateway();
		$installments = [];

		$installments['enabled'] = [
			'title'             => __( 'Installments settings', 'woo-moip-official' ),
			'type'              => 'checkbox',
			'label'             => __( 'Enable Installments settings', 'woo-moip-official' ),
			'default'           => 'no',
			'custom_attributes' => [
				'data-field' => 'installments',
			],
		];

		$installments['installment'] = [
			'title'       => __( 'Minimum installment', 'woo-moip-official' ),
			'type'        => 'text',
			'description' => __( 'Amount of the minimum installment to be applied to the card.', 'woo-moip-official' ),
			'desc_tip'    => true,
			'placeholder' => '0,00',
			'custom_attributes' => [
				'data-mask'         => '##0,00',
				'data-mask-reverse' => true,
			],
		];

		$installments['maximum'] = [
			'title'       => __( 'Maximum installments number', 'woo-moip-official' ),
			'type'        => 'select',
			'description' => __( 'Force a maximum number of installments for payment.', 'woo-moip-official' ),
			'desc_tip'    => true,
			'default'     => 12,
			'options'     => $model->get_installment_options(),
			'custom_attributes' => [
				'data-action' => 'installments-maximum',
			],
		];

		$installments['general'] = [
			'title'       => __( 'Interest per installment', 'woo-moip-official' ),
			'type'        => 'installments',
			'description' => __( 'Define interest for each installment.', 'woo-moip-official' ),
			'desc_tip'    => false,
		];

		$installments['interest'] = [
			'title'       => __( 'Interest', 'woo-moip-official' ),
			'type'        => 'text',
			'description' => __( 'Interest to be applied to the installment.', 'woo-moip-official' ),
			'desc_tip'    => true,
			'placeholder' => '0,00',
		];

		return $installments[ $field ];
	}

	public static function cc_additional_fields()
	{
		return [
			'title' => __( 'Additional Fields', 'woo-moip-official' ),
			'type'  => 'title',
		];
	}

	public static function field_moip_cpf_holder()
	{
		return [
			'title'       => __( 'CPF', 'woo-moip-official' ),
			'type'        => 'checkbox',
			'label'       => __( 'Enable CPF field', 'woo-moip-official' ),
			'default'     => 'no',
			'description' => __( 'Add CPF field to credit card tab for risk analysis.(for transparent checkout only).', 'woo-moip-official' ),
			'desc_tip'    => false,
			'custom_attributes' => [
				'data-field' => 'render-cpf-field',
			],
		];
	}

	public static function field_moip_birth_holder()
	{
		return [
			'title'       => __( 'Date Birth', 'woo-moip-official' ),
			'type'        => 'checkbox',
			'label'       => __( 'Enable date birth field', 'woo-moip-official' ),
			'default'     => 'no',
			'description' => __( 'Add date birth field to credit card tab for risk analysis.(for transparent checkout only).', 'woo-moip-official' ),
			'desc_tip'    => false,
			'custom_attributes' => [
				'data-field' => 'render-birth-field',
			],
		];
	}

	public static function field_moip_phone_holder()
	{
		return [
			'title'       => __( 'Phone', 'woo-moip-official' ),
			'type'        => 'checkbox',
			'label'       => __( 'Enable phone field', 'woo-moip-official' ),
			'default'     => 'no',
			'description' => __( 'Add phone field to credit card tab for risk analysis.(for transparent checkout only).', 'woo-moip-official' ),
			'desc_tip'    => false,
			'custom_attributes' => [
				'data-field' => 'render-phone-field',
			],
		];
	}

	public static function get_fields()
	{
		$settings = Setting::get_instance();
		$values   = [
			'not_found'  => self::not_found(),
		];

		if ( $settings->is_checkout_moip() ) {
			$values = [
				'not_found_cc'  => self::not_found_cc(),
			];

			return $values;
		}

		if ( $settings->is_active_credit_card() ) {

			$values = [
				'section_installments'  => self::section_installments(),
                //'save_credit_card'      => self::save_credit_card_option(),
				'installments_enabled'  => self::field_credit_card_installments( 'enabled' ),
				'installments_minimum'  => self::field_credit_card_installments( 'installment' ),
				'installments_maximum'  => self::field_credit_card_installments( 'maximum' ),
				'installments'          => self::field_credit_card_installments( 'general' ),
				'cc_additional_fields'  => self::cc_additional_fields(),
				'wirecard_cpf_holder'   => self::field_moip_cpf_holder(),
				'wirecard_birth_holder' => self::field_moip_birth_holder(),
				'wirecard_phone_holder' => self::field_moip_phone_holder()
			];

		}

		return $values;
	}
}
