<?php
namespace Woocommerce\Moip\Fields\Billet;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Moip\Core;
use Woocommerce\Moip\Model\Setting;

class Config
{
	public static function not_found()
	{
		return [
			'title' => __( 'Ativar opção de boleto bancário!', 'woo-moip-official' ),
			'type'  => 'title',
			'description' => sprintf(
				__( 'To View the logs click the link: %s' , 'woo-moip-official' ),
				self::get_general_url()
			),
		];
	}

	public static function get_general_url()
	{
		$url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=woo-moip-official&wirecard-tab=wbo-general' );

		return '<a href="' . esc_url( $url ) . '">' . __( 'General', 'woo-moip-official' ) . '</a>';
	}

	public static function section_billet_settings()
	{
		return [
			'title' => __( 'Billet Settings', 'woo-moip-official' ),
			'type'  => 'title',
		];
	}

	public static function render_billet_description()
    {
    	return [
			'title'             => __( 'Description Billet Checkout', 'woo-moip-official' ),
			'description'       => __( 'Add description in billet option in transparent checkout. If leave blank will show the default text.', 'woo-moip-official' ),
			'type'              => 'textarea',
			'placeholder'       => __( 'Default text: The order will be confirmed only after confirmation of payment.', 'woo-moip-official' ),
			'css'               => 'height: 100px',
			'custom_attributes' => [
				'data-field' => 'billet-discount',
			],
		];
	}

	public static function field_billet_payee_name()
	{
		return [
			'title'             => __( 'Payee Name', 'woo-moip-official' ),
			'description'       => __( 'Type to change the payee name on the boleto, default Invoice Name.', 'woo-moip-official' ),
			'desc_tip'          => true,
			'default'           => '',
			'custom_attributes' => [
				'data-field' => 'billet',
				'maxlength'  => 13
			],
		];
	}

	public static function field_billet_deadline_days()
	{
		return [
			'title'             => __( 'Number of Days', 'woo-moip-official' ),
			'description'       => __( 'Days of expiry of the billet after printed.', 'woo-moip-official' ),
			'desc_tip'          => true,
			'placeholder'       => 5,
			'default'           => 5,
			'custom_attributes' => [
				'data-field' => 'billet',
			],
		];
	}

	public static function field_billet_instructions( $line )
	{
		$instructions = [
			1 => [
				'title'       => __( 'Instruction Line 1', 'woo-moip-official' ),
				'type'        => 'text',
				'description' => __( 'First line instruction for the billet.', 'woo-moip-official' ),
				'desc_tip'    => true,
			],
			2 => [
				'title'       => __( 'Instruction Line 2', 'woo-moip-official' ),
				'type'        => 'text',
				'description' => __( 'Second line instruction for the billet.', 'woo-moip-official' ),
				'desc_tip'    => true,
			],
			3 => [
				'title'       => __( 'Instruction Line 3', 'woo-moip-official' ),
				'type'        => 'text',
				'description' => __( 'Third line instruction for the billet.', 'woo-moip-official' ),
				'desc_tip'    => true,
			],
		];

		return $instructions[ $line ];
	}

	public static function field_billet_logo_url()
	{
		return [
			'title'       => __( 'Custom Logo URL', 'woo-moip-official' ),
			'type'        => 'text',
			'description' => __( 'URL of the logo image to be shown on the billet.', 'woo-moip-official' ),
			'desc_tip'    => true,
			'default'     => ''
		];
	}

	public static function section_discount()
	{
		return [
			'title' => __( 'Discount Settings', 'woo-moip-official' ),
			'type'  => 'title',
		];
	}

	public static function field_enabled_discount()
	{
		return [
			'title'       => __( 'Moip Discount', 'woo-moip-official' ),
			'type'        => 'checkbox',
			'label'       => __( 'Enable Billet Discount', 'woo-moip-official' ),
			'default'     => 'no',
			'description'       => __( 'Enabling the discount will apply to billet.', 'woo-moip-official' ),
			'desc_tip'          => true,
			'custom_attributes' => [
				'data-field'  => 'enable-discount-field',
			],
		];
	}

	public static function moip_discount_type() {
		return [
			'type'        => 'select',
			'title'       => __( 'Discount type', 'woo-moip-official' ),
			'description' => __( 'Choose where the discount coupon will be applied. About the total or subtotal value. By default it will be subtotal.', 'woo-moip-official' ),
			'desc_tip'    => true,
			'default'     => 'moip_discount_subtotal',
			'options'     => [
				'moip_discount_subtotal' => __( 'Discount on subtotal', 'woo-moip-official' ),
				'moip_discount_total'    => __( 'Discount on total', 'woo-moip-official' ),
			],
			'custom_attributes' => [
				'data-field'  => 'wbo-billet-discount',
			],
		];
	}

	public static function moip_discount_name()
    {
    	return [
			'title'             => __( 'Discount Name', 'woo-moip-official' ),
			'description'       => __( 'Enter the name of the discount (Default name if blank: Moip Discount).', 'woo-moip-official' ),
			'desc_tip'          => true,
			'type'              => 'text',
			'placeholder'       => __( 'Moip Discount', 'woo-moip-official' ),
			'custom_attributes' => [
				'data-field'  => 'wbo-billet-discount',
			],
		];
	}

	public static function moip_discount_number()
    {
    	return [
			'title'             => __( 'Discount Amount', 'woo-moip-official' ),
			'description'       => __( 'Enter the discount amount for the bank slip (Example: 10%).', 'woo-moip-official' ),
			'desc_tip'          => true,
			'type'              => 'text',
			'placeholder'       => '0,00',
			'custom_attributes' => [
				'data-mask'         => '##0,00%',
				'data-mask-reverse' => true,
				'data-field'        => 'wbo-billet-discount',
			],
		];
	}

	public static function section_email()
	{
		return [
			'title' => __( 'Email Settings', 'woo-moip-official' ),
			'type'  => 'title',
		];
	}

	public static function send_moip_billet_email()
	{
		return [
			'title'       => __( 'Send Billet to Email', 'woo-moip-official' ),
			'type'        => 'checkbox',
			'label'       => __( 'Enable billet sending for email', 'woo-moip-official' ),
			'default'     => 'no',
			'description' => __( 'Enabling the billet link will be sent to the buyer email.', 'woo-moip-official' ),
			'desc_tip'    => true,
			'custom_attributes' => [
				'data-field'  => 'send-email-field',
			],
		];
	}

	public static function moip_billet_from_name()
    {
    	return [
			'title'             => __( 'From Name', 'woo-moip-official' ),
			'description'       => __( 'Enter the from name (Default name if blank: Site Name).', 'woo-moip-official' ),
			'desc_tip'          => true,
			'type'              => 'text',
			'placeholder'       => get_option( 'blogname' ),
			'custom_attributes' => [
				'data-field'  => 'wbo-email-field',
			],
		];
	}

	public static function moip_billet_from_email()
    {
    	return [
			'title'             => __( 'From Email', 'woo-moip-official' ),
			'description'       => __( 'Enter the from email (Default name if blank: Admin Email ).', 'woo-moip-official' ),
			'desc_tip'          => true,
			'type'              => 'text',
			'placeholder'       => get_option( 'admin_email' ),
			'custom_attributes' => [
				'data-field'  => 'wbo-email-field',
			],
		];
	}

	public static function moip_billet_subject()
    {
    	return array(
			'title'             => __( 'Subject', 'woo-moip-official' ),
			'description'       => __( 'Enter the name of the subject (Default name if blank: Moip Billet).', 'woo-moip-official' ),
			'desc_tip'          => true,
			'type'              => 'text',
			'placeholder'       => __( 'Moip Billet [Order #ID]', 'woo-moip-official' ),
			'custom_attributes' => [
				'data-field'  => 'wbo-email-field',
			],
		);
	}

	public static function get_fields()
	{
		$settings = Setting::get_instance();
		$values   = [
			'not_found'  => self::not_found(),
		];

		if ( $settings->is_checkout_default() ) {

			$values = [
				'section_billet_settings'    => self::section_billet_settings(),
				'render_billet_description'  => self::render_billet_description(),
				'billet_payee_name'          => self::field_billet_payee_name(),
				'billet_deadline_days'       => self::field_billet_deadline_days(),
				'billet_instruction_line1'   => self::field_billet_instructions( 1 ),
				'billet_instruction_line2'   => self::field_billet_instructions( 2 ),
				'billet_instruction_line3'   => self::field_billet_instructions( 3 ),
				'billet_logo'                => self::field_billet_logo_url()
			];

			return $values;
		}

		if ( $settings->is_checkout_moip() ) {

			$values = [
				'section_billet_settings'    => self::section_billet_settings(),
				'render_billet_description'  => self::render_billet_description(),
				'billet_payee_name'          => self::field_billet_payee_name(),
				'billet_deadline_days'       => self::field_billet_deadline_days(),
				'billet_instruction_line1'   => self::field_billet_instructions( 1 ),
				'billet_instruction_line2'   => self::field_billet_instructions( 2 ),
				'billet_instruction_line3'   => self::field_billet_instructions( 3 ),
				'billet_logo'                => self::field_billet_logo_url()
			];

			return $values;
		}

		if ( $settings->is_active_billet_banking() ) {

			$values = [
				'section_billet_settings'    => self::section_billet_settings(),
				'render_billet_description'  => self::render_billet_description(),
				'billet_payee_name'          => self::field_billet_payee_name(),
				'billet_deadline_days'       => self::field_billet_deadline_days(),
				'billet_instruction_line1'   => self::field_billet_instructions( 1 ),
				'billet_instruction_line2'   => self::field_billet_instructions( 2 ),
				'billet_instruction_line3'   => self::field_billet_instructions( 3 ),
				'billet_logo'                => self::field_billet_logo_url(),
				'section_discount'           => self::section_discount(),
				'field_enabled_discount'     => self::field_enabled_discount(),
				'moip_discount_type'         => self::moip_discount_type(),
				'wirecard_discount_name'     => self::moip_discount_name(),
				'wirecard_discount_number'   => self::moip_discount_number(),
				'section_email'              => self::section_email(),
				'send_wirecard_billet_email' => self::send_moip_billet_email(),
				'wirecard_billet_from_name'  => self::moip_billet_from_name(),
				'wirecard_billet_from_email' => self::moip_billet_from_email(),
				'wirecard_billet_subject'    => self::moip_billet_subject()
			];

		}

		return $values;
	}
}
