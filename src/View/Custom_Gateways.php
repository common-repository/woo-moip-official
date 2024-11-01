<?php
namespace Woocommerce\Moip\View;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Moip\Core;
use Woocommerce\Moip\Helper\Utils;
use Woocommerce\Moip\Model\Custom_Gateway;

class Custom_Gateways
{
	public static function render_notification_webhook( $settings )
	{
		$args = [
			'access_token'     => $settings->authorize_data->accessToken,
			'url_callback'     => Core::get_webhook_url(),
			'id'               => $settings->webhook_id,
			'url_notification' => Utils::get_url_endpoint().'/v2/preferences/notifications'
		];

		ob_start();

		?>
		<table class='form-table wbo-webhooks-token'>
			<tr>
		 		<th><?php echo __( 'Moip Token:', 'woo-moip-official' ); ?></th>
		 		<td class='forminp'>
		 			<?php echo Utils::encrypt( $args ); ?>
		 		</td>
		 	</tr>
		</table>
		<?php

		return ob_get_clean();
	}

	public static function render_button_webhook_send()
	{
		$model = new Custom_Gateway();

		ob_start();
		?>
		<table class='form-table'>
			<tr valign="top">
		 		<th><?php echo __( 'Status', 'woo-moip-official' ); ?></th>
		 		<td class='forminp'>
		 			<form method="">
		 				<button class="webhook button" name="moip_send_webhook"><?php echo __( 'Update Notifications', 'woo-moip-official' ); ?></button>
		 			</form>
		 			<p class="description "><?php echo __( 'Update notifications only if the WooCommerce / Moip status is not working.', 'woo-moip-official' ); ?></p>
		 		</td>
		 	</tr>
		</table>

	<?php

		if ( isset( $_POST['moip_send_webhook'] ) ) {
			$model->delete_webhook_notification();
            $model->set_webhook_notification();
		}
		return ob_get_clean();
	}

}
