<?php
if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Moip\Core;
use Woocommerce\Moip\Model\Order;

$order        = new Order( $order_id );
$parent_id    = $wc_order->get_parent_id();
$cpf_holder   = '';
$birth_holder = '';

if ( $parent_id > 0 ) {
	$order_id        = $parent_id;
	$order           = new Order( $order_id );
}

$moip_pay_method = get_post_meta( $order_id, '_moip_payment_type', true );
$card_brand      = get_post_meta( $order_id, '_wbo_creditcard_brand', true );
$moip_pay_method = get_post_meta( $order_id, '_moip_payment_type', true );

?>

<div class="clear"></div>
<div id="wirecard-order-container">
<h3><?php esc_html_e( 'Moip Brasil Oficial', 'woo-moip-official' ); ?></h3>
<?php
	//Credit Card
	if ( $moip_pay_method == 'payCreditCard' ) :
		if ( $settings->is_enabled_cpf_holder() ) {
			$get_cpf_holder = preg_replace( "/\D/", '', get_post_meta( $order_id, '_wbo_creditcard_cpf_number', true ) );
			$cpf_holder     = preg_replace( "/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $get_cpf_holder );
		}

		if ( $settings->is_enabled_birth_holder() ) {
			$get_birth_holder = date_create( get_post_meta( $order_id, '_wbo_creditcard_birth_number', true ) );
			$birth_holder     = date_format( $get_birth_holder, 'd/m/Y' );
		} ?>
		<p>
		<strong><?php esc_html_e( 'Payment by', 'woo-moip-official' ); ?>: </strong><span style="color:red"><?php esc_html_e( 'Credit Card', 'woo-moip-official' ); ?></span><br>
		<strong><?php esc_html_e( 'Brand', 'woo-moip-official' ); ?>: </strong><span><?php echo esc_attr( $card_brand ); ?></span><br>
		<strong><?php esc_html_e( 'Installments', 'woo-moip-official' ); ?>: </strong><span><?php echo esc_attr( $order->installments ); ?></span><br>
		<strong><?php esc_html_e( 'Moip Order ID', 'woo-moip-official' ); ?>: </strong><span><?php echo esc_attr( $order->resource_id ); ?></span><br>

		<?php if ( $cpf_holder ) : ?>
			<strong><?php esc_html_e( 'CPF Number', 'woo-moip-official' ); ?>: </strong><span><?php echo esc_attr( $cpf_holder ); ?></span><br>
		<?php endif; ?>

		<?php if ( $birth_holder ) : ?>
			<strong><?php esc_html_e( 'Date Birth', 'woo-moip-official' ); ?>: </strong><span><?php echo esc_attr( $birth_holder ); ?></span><br>
		<?php endif; ?>

		</p>
<?php
	endif;
	//Billet
	if ( $moip_pay_method == 'payBoleto' ) : ?>
		<p>
		<strong><?php esc_html_e( 'Payment by', 'woo-moip-official' ); ?>: </strong><span style="color:red"><?php esc_html_e( 'Bank Billet', 'woo-moip-official' ); ?></span><br>
		<strong><?php esc_html_e( 'Billet Link', 'woo-moip-official' ); ?>: </strong>
		<a href="<?php echo esc_attr( $order->payment_links->payBoleto->printHref ); ?>" target="_blank" class="admin-payment-link"><?php esc_html_e( 'Print', 'woo-moip-official' ); ?></a><br>
		<strong><?php esc_html_e( 'Moip Order ID', 'woo-moip-official' ); ?>: </strong>
		<span><?php echo esc_attr( $order->resource_id ); ?></span><br>
		</p>
<?php
	endif; ?>
</div>
