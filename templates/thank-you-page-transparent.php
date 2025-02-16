<?php
if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Moip\Model\Order;
use Woocommerce\Moip\Model\Setting;
use Woocommerce\Moip\View\Checkouts as Checkouts_View;
use Woocommerce\Moip\View\Moip_Emails;

$model        = new Order( $order_id );
$setting      = Setting::get_instance();
$payment_link = $model->get_link_by_type( $model->payment_type );

?>

<div class="moip-woocommerce-message">

	<p class="thank-you-description">
		<?php
			if ( ! is_null( $payment_link ) ) {
				echo Checkouts_View::billet_message( $model );

				if ( $setting->is_send_billet_email() ) {
					Moip_Emails::send_moip_billet_email( $order_id );
				}

			} else {
			    echo Checkouts_View::credit_cart_message( $model );
			}
		?>
	</p>

</div>
