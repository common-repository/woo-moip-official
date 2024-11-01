<?php
namespace Woocommerce\Moip\View;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Moip\Core;
use Woocommerce\Moip\Helper\Utils;
use Woocommerce\Moip\Model\Order;
use Woocommerce\Moip\Model\Setting;

class Checkouts
{
	protected static function message_before()
	{
		return __( 'Your order has been sent to Moip.', 'woo-moip-official' ) . '<br />';
	}

	protected static function message_after_cc()
	{
		return __( 'Your request will be processed as soon as your credit card company confirms payment.', 'woo-moip-official' ) . '<br />';
	}

	protected static function message_after_billet()
	{
		return __( 'Your request will be processed as soon as we receive confirmation of your billet payment.', 'woo-moip-official' );
	}

	protected static function message_after()
	{
		return __( 'If you have any questions regarding the transaction, please contact us or the Moip.', 'woo-moip-official' );
	}

	public static function handle_messages( Order $order )
	{
		switch ( $order->payment_type ) {

			case 'payBoleto' :
				return self::billet_message( $order );

			case 'payCreditCard' :
				return self::credit_cart_message( $order );

			case 'payOnlineBankDebitItau' :
				return self::debit_message( $order );
		}
	}

	public static function credit_cart_message( $order )
	{
		$card_brand        = get_post_meta( $order->ID, '_wbo_creditcard_brand', true );
		$card_installments = $order->installments;
		$message           = '';

		$message .= sprintf(
			__( 'You just made a payment in %s with credit card %s.', 'woo-moip-official' ),
			'<strong>' . $card_installments . 'x</strong>',
			'<strong>' . $card_brand . '</strong>'
		) . '<br />';

		$message .= self::message_after_cc();

		$message .= self::message_after();

		return $message;
	}

	public static function debit_message( $order )
	{
		$message = self::message_before();

		$message .= __( 'If you have not made ​​the payment, please click the button below to pay.', 'woo-moip-official' ) . '<br />';

		$message .= sprintf(
			'<a href="%s" target="_blank" class="payment-link">%s</a></br>',
			$order->payment_links->payOnlineBankDebitItau->redirectHref,
			__( 'Pay now', 'woo-moip-official' )
		);

		$message .= self::message_after();

		return $message;
	}

	public static function billet_message( $order )
	{
		$message = self::message_before();

		if ( ! empty( $order->payment_billet_linecode ) ) {
			$message .= sprintf(
				'<span>%s <span id="linecode">%s</span></span><br/>',
				__( 'Line Code: ', 'woo-moip-official' ),
				$order->payment_billet_linecode
			);
		}

		$message .= sprintf(
			'<button id="clipboard-linecode-btn"
			         class="clipboard-btn"
			         data-success-text="%s"
			         data-clipboard-target="#linecode">
			    %s
			 </button><br/>',
			__( 'Copied!', 'woo-moip-official' ),
			__( 'Copy barcode', 'woo-moip-official' )
		);

		$message .= __( 'To print the billet click the button below.', 'woo-moip-official' ) . '<br />';

		$message .= self::message_after_billet();

		$message .= sprintf(
			'<a href="%s" target="_blank" class="payment-link">%s</a><br/>',
			$order->payment_links->payBoleto->redirectHref . '/print',
			__( 'Print', 'woo-moip-official' )
		);

		return $message;
	}

	public static function render_installments( $total )
	{
		$setting = Setting::get_instance();

		if ( ! $setting->is_active_credit_card() ) {
			return;
		}

		if ( ! $setting->is_active_installments() ) {
			$text  = sprintf( __( '%s (%s)', 'woo-moip-official' ),
				__( 'At sight', 'woo-moip-official' ),
				wc_price( $total )
			);

			printf( '<option value="%1$s">%2$s</option>', 1, $text );

			return;
		}

		$min_installments = str_replace( ',', '.', $setting->installments_minimum );
		$max_installments = intval( $setting->installments_maximum );

		for ( $times = 1; $times <= $max_installments; $times++ ) {
			$amount  = $total;

			if ( isset( $setting->installments['interest'][ $times ] ) ) {
				$per_installment = (float) str_replace( ',', '.', $setting->installments['interest'][$times] );
				$amount         += ( $per_installment / 100 ) * $amount;
			}

			$price = ceil( $amount / $times * 100 ) / 100;

			if ( !empty( $setting->installments['interest'][ $times ] ) ) {
				$interest_name  = __( 'with interest', 'woo-moip-official' );
				$interest_value = wc_price( $price * $times );
			} else {
				$interest_name  = __( 'without interest', 'woo-moip-official' );
				$interest_value = wc_price( $amount );
			}

			if ( $times == 1 && empty( $setting->installments['interest'][ $times ] ) ) {
				$text  = sprintf( __( '%s (%s)', 'woo-moip-official' ),
					__( 'At sight', 'woo-moip-official' ),
					wc_price( $price )
				);

				printf( '<option value="%1$s">%2$s</option>', $times, $text );

			} else {
				if ( $min_installments <= $price ) {
					$text  = sprintf( __( '%dx of %s (%s %s)', 'woo-moip-official' ),
						$times,
						wc_price( $price ),
						$interest_value,
						$interest_name
					);

					printf( '<option value="%1$s">%2$s</option>', $times, $text );
				}
			}
		}
	}

	public static function render_moip_billet_discount()
	{

		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		$setting         = Setting::get_instance();
    	$amount          = (float) str_replace( ',', '.', $setting->wirecard_discount_number );
    	$discount_name   = $setting->wirecard_discount_name;
		$discount_type   = $setting->get_moip_discount_type();
		$payment_method  = 'woo-moip-official';
		$fields          = Utils::post( 'moip_fields', false );
		$payments_method = Utils::get_value_by( $fields, 'payment_method' );

		if ( $payments_method == 'payCreditCard'
		|| $setting->is_checkout_moip()
		|| $setting->is_checkout_default()
		|| is_cart() ) {
			return;
		}

    	if ( empty( $discount_name ) ) {
    		$discount_name = __( 'Moip Discount', 'woo-moip-official' );
    	}

		if ( ! $amount ) {
			return;
		}

    	$chosen_payment_method = WC()->session->get( 'chosen_payment_method' );
	    $wirecard_fee          = ( $discount_type / 100 ) * $amount;

		if ( $payment_method == $chosen_payment_method ) {
		    if ( $wirecard_fee > 0 ) {
		        WC()->cart->add_fee( $discount_name, -$wirecard_fee, true );
		    }
    	}

	}

	public static function render_billet_description()
	{
		$setting = Setting::get_instance();
		$message = __( 'The order will be confirmed only after confirmation of payment.', 'woo-moip-official' );

		if ( $setting->render_billet_description && $setting->is_active_billet_banking() ) {
			$message = $setting->render_billet_description;
		}

		return apply_filters( 'wcd_render_billet_description', $message );
	}

	public static function render_cc_title_discount()
	{
		if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;

		$setting         = Setting::get_instance();
    	$amount          = (float) str_replace( ',', '.', $setting->wirecard_discount_number );
    	$discount_name   = $setting->wirecard_discount_name;
		$discount_type   = $setting->get_moip_discount_type();
		$payment_method  = 'woo-moip-official';
		$payments_method = '';
		$message         = '';

		if ( ! $setting->is_active_render_discount() ) {
			return;
		}

		if ( $payments_method == 'payCreditCard' || $setting->is_checkout_moip()
		 || $setting->is_checkout_default() || ! $setting->is_active_billet_banking()
		 || ! $setting->is_active_credit_card() || is_cart() ) {
			return;
		}

		if ( ! $amount ) {
			return;
		}

    	$chosen_payment_method = WC()->session->get( 'chosen_payment_method' );
		$wirecard_fee          = ( $discount_type / 100 ) * $amount;
		$credit_card_total     = WC()->cart->total + $wirecard_fee;

		if ( $payment_method == $chosen_payment_method ) {
			printf(
				'<tr class="order-total-cc">
					<th>%s</th>
					<td><strong>%s</strong></td>
				</tr>',
				__( 'Total no Cartão de Crédito', 'woo-moip-official' ),
				wc_price( $credit_card_total )
			);
    	}
	}

}
