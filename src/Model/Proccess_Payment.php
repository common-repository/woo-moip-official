<?php
namespace Woocommerce\Moip\Model;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Exception;

// Moip SDK
use Moip\Resource\Orders;
use Moip\Resource\Payment;

use Woocommerce\Moip\Core;
use Woocommerce\Moip\Helper\Utils;
use Woocommerce\Moip\Model\Order;
use Woocommerce\Moip\Model\Setting;

use WC_Order;

class Proccess_Payment
{
	protected $wc_order;

	protected $order;

	protected $sdk;

	protected $setting;

	public function __construct( $order_id, Order $order )
	{
		if ( ! is_numeric( $order_id ) ) {
			throw new Exception( __( 'Invalid order id', 'woo-moip-official' ) );
		}

		$this->setting      = Setting::get_instance();
		$this->order_paid   = $this->setting->tools_order_paid_status;
		$this->order_cancel = $this->setting->field_order_cancel_status;
		$this->order_id     = $order_id;

		$this->_set_orders( $order_id, $order );
		$this->set_sdk();
	}

	private function _set_orders( $order_id, $order )
	{
		$this->wc_order = wc_get_order( $order_id );
		$this->order    = $order;
	}

	public function set_sdk()
	{
		$this->sdk = Moip_SDK::get_instance();
	}

	public function get_order()
	{
		return $this->wc_order;
	}

	public function payment_authorized( $response )
	{
		$payment_id = $response->resource->payment->id;
		$this->_set_payment_meta( $response );

		if ( !$this->order_paid ) {
			$this->wc_order->update_status( 'processing', __( 'Moip: Payment authorized. '.$payment_id, 'woo-moip-official' ) );
			update_post_meta( $this->order_id, '_moip_order_authorized', 'true' );
		}

		if ( $this->order_paid == 1 ) {
			$this->wc_order->update_status( 'processing', __( 'Moip: Payment authorized. '.$payment_id, 'woo-moip-official' ) );
			update_post_meta( $this->order_id, '_moip_order_authorized', 'true' );
		}

		if ( $this->order_paid == 2 ) {
			$this->wc_order->update_status( 'completed', __( 'Moip: Payment authorized. '.$payment_id, 'woo-moip-official' ) );
			update_post_meta( $this->order_id, '_moip_order_authorized', 'true' );
		}

	}

	public function payment_waiting( $response )
	{
		$payment_id = $response->resource->payment->id;
		$authorized = get_post_meta( $this->order_id, '_moip_order_authorized', true );
		$this->_set_payment_meta( $response );

		if ( $authorized === 'true' ) {
			return;
		}

		$this->wc_order->add_order_note(__( 'Moip: Payment waiting. '.$payment_id, 'woo-moip-official' ) );
	}

	public function payment_in_analysis( $response )
	{
		$payment_id = $response->resource->payment->id;
		$authorized = get_post_meta( $this->order_id, '_moip_order_authorized', true );
		$this->_set_payment_meta( $response );

		if ( $authorized === 'true' ) {
			return;
		}

		$this->wc_order->add_order_note(__( 'Moip: Payment is under analysis. '.$payment_id, 'woo-moip-official' ) );
	}

	public function payment_cancelled( $response )
	{
		$payment_id = $response->resource->payment->id;
		$cancelled  = $response->resource->payment->cancellationDetails->description;
		$wc_status  = $this->wc_order->get_status();
		$this->_set_payment_meta( $response );

		if ( !$cancelled ) {
			$cancelled = __( 'Contact Moip to learn more.', 'woo-moip-official' );
		}

		if ( !$this->order_cancel ) {
			if ( $wc_status != 'cancelled' ) {
				$this->wc_order->update_status( 'cancelled', __( 'Moip: Payment canceled. '.$payment_id, 'woo-moip-official' ) );
				$this->wc_order->add_order_note(__( 'Moip: Payment canceled, reason: '.$cancelled, 'woo-moip-official' ) );
			}
		}

		if ( $this->order_cancel == 1 ) {
			if ( $wc_status != 'cancelled' ) {
				$this->wc_order->update_status( 'cancelled', __( 'Moip: Payment canceled. '.$payment_id, 'woo-moip-official' ) );
				$this->wc_order->add_order_note(__( 'Moip: Payment canceled, reason: '.$cancelled, 'woo-moip-official' ) );
			}
		}

		if ( $this->order_cancel == 2 ) {
			if ( $wc_status != 'failed' ) {
				$this->wc_order->update_status( 'failed', __( 'Moip: Payment canceled. '.$payment_id, 'woo-moip-official' ) );
				$this->wc_order->add_order_note(__( 'Moip: Payment canceled, reason: '.$cancelled, 'woo-moip-official' ) );
			}
		}
	}

	public function payment_refunded( $response )
	{
		$payment_id = $response->resource->payment->id;
		$wc_status  = $this->wc_order->get_status();
		$this->_set_payment_meta( $response );

		if ( $wc_status != 'refunded' ) {
			$this->wc_order->update_status( 'refunded', __( 'Moip: Payment refunded. '.$payment_id, 'woo-moip-official' ) );
		}
	}

	private function _get_moip_order( $response )
	{
		$orders = new Orders( $this->sdk->moip );

		return $orders->get( $response->resource->order->id );
	}

	private function _get_moip_payment( $response )
	{
		$payment = new Payment( $this->sdk->moip );

		return $payment->get( $response->resource->payment->id );
	}

	private function _set_payment_meta( $response )
	{
		if ( $this->order->payment_id !== $response->resource->payment->id ) {
			$this->order->payment_id = $response->resource->payment->id;
		}
	}
}
