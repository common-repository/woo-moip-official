<?php
namespace Woocommerce\Moip\Fields\Tools;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Moip\Core;
use Woocommerce\Moip\Model\Custom_Gateway;
use Woocommerce\Moip\Helper\Utils;

//Views
use Woocommerce\Moip\View;

class Config
{
    public static function section_tools()
	{
		return [
			'title' => __( 'Tools', 'woo-moip-official' ),
			'type'  => 'title',
		];
	}

	public static function field_order_paid_status()
	{
		$model = new Custom_Gateway();

		return [
			'type'        => 'select',
			'title'       => __( 'Paid Order', 'woo-moip-official' ),
			'description' => __( 'Status that the order should have after receiving confirmation of payment from Moip.', 'woo-moip-official' ),
			'desc_tip'    => false,
			'class'       => 'wc-enhanced-select',
			'default'     => 1,
			'options'     => $model->get_status_paid_options(),
			'custom_attributes' => [
				'data-action' => 'tools-order-status',
			],
		];
	}

	public static function field_order_cancel_status()
	{
		$model = new Custom_Gateway();

		return [
			'type'        => 'select',
			'title'       => __( 'Order Canceled', 'woo-moip-official' ),
			'description' => __( 'Order status after receiving confirmation of cancelation from Moip.', 'woo-moip-official' ),
			'desc_tip'    => false,
			'class'       => 'wc-enhanced-select',
			'default'     => 1,
			'options'     => $model->get_status_cancel_options(),
			'custom_attributes' => [
				'data-action' => 'tools-order-status',
			],
		];
	}

    public static function field_enabled_logs()
	{
		return [
			'title'       => __( 'Logs', 'woo-moip-official' ),
			'type'        => 'checkbox',
			'label'       => __( 'Enable', 'woo-moip-official' ),
			'default'     => 'no',
			'description' => sprintf(
				__( 'To View the logs click the link: %s' , 'woo-moip-official' ),
				self::moip_oficial_log_view()
			),
		];
    }

	public static function moip_oficial_log_view()
	{
		return '<a href="' . esc_url(
            admin_url( 'admin.php?page=wc-status&tab=logs&log_file=moip-brazil-official-'
            . sanitize_file_name( wp_hash( Core::SLUG ) ) . '.log' ) ) . '">'
            . __( 'System Status &gt; Logs', 'woo-moip-official' ) . '</a>';
	}

    public static function section_webhook()
	{
		return [
			'title'       => __( 'Webhooks', 'woo-moip-official' ),
			'type'        => 'title',
			'description' => sprintf(
				'%s',
				self::send_webhook()
			),
		];
	}

	public static function send_webhook()
	{
		$button_notification = View\Custom_Gateways::render_button_webhook_send();

		return $button_notification;
	}

	public static function get_fields()
	{
		return [
			'section_tools'                => self::section_tools(),
			'tools_order_paid_status'      => self::field_order_paid_status(),
			'field_order_cancel_status'    => self::field_order_cancel_status(),
			'enable_logs'                  => self::field_enabled_logs(),
			'section_webhook'              => self::section_webhook()
		];
	}
}
