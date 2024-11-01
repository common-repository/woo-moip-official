<?php
namespace Woocommerce\Moip\Model;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Exception;

use Woocommerce\Moip\Core;
use Woocommerce\Moip\Model\Moip_SDK;
use Woocommerce\Moip\Model\Webhook;
use Woocommerce\Moip\Model\Setting;

class Custom_Gateway
{
	public $settings;

	public function __construct()
	{
		$this->settings = Setting::get_instance();
		$this->moip_sdk = Moip_SDK::get_instance();
	}

	public function supported_currency() {
		return ( get_woocommerce_currency() === 'BRL' );
	}

	public function get_installment_options()
	{
		return [
			2  => 2,
			3  => 3,
			4  => 4,
			5  => 5,
			6  => 6,
			7  => 7,
			8  => 8,
			9  => 9,
			10 => 10,
			11 => 11,
			12 => 12,
		];
	}

	public function get_persontype_options()
	{
		return [
			0 => __( 'Individuals and Legal Person', 'woo-moip-official' ),
			1 => __( 'Individual only', 'woo-moip-official' ),
			2 => __( 'Legal Person only', 'woo-moip-official' ),
		];
	}

	public function set_webhook_notification()
	{
		if ( empty( $this->settings->webhook_id ) ) {
			return;
		}

		try {
			$webhook  = new Webhook( $this->moip_sdk->moip );
			$response = $webhook->create();

			if ( $response->token ) {
				$this->settings->set( 'webhook_token', $response->token );
				$this->settings->set( 'webhook_id', $response->id );
			}

			unset( $webhook );

		} catch ( Exception $e ) {
			error_log( $e->__toString() );
		}

	}

	public function delete_webhook_notification()
	{
		try {
			$model          = new Webhook( $this->moip_sdk->moip );
			$notifications  = $model->get();
			$response       = json_decode( json_encode( $notifications ), true );

			foreach ( $response as $notification ) {
				$model->delete( $notification['id'] );
			}

			unset($model);

		} catch ( Exception $e ) {
			error_log( $e->__toString() );
		}
	}

	public function get_status_paid_options()
	{
		return [
			1 => __( 'Processing', 'woo-moip-official' ),
			2 => __( 'Completed', 'woo-moip-official' ),
		];
	}

	public function get_status_cancel_options()
	{
		return [
			1 => __( 'Canceled', 'woo-moip-official' ),
			2 => __( 'Failed', 'woo-moip-official' ),
		];
	}
}
