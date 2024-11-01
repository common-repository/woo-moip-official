<?php
namespace Woocommerce\Moip\View;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Moip\Core;
use Woocommerce\Moip\Helper\Utils;
use Woocommerce\Moip\Model\Payment_Split;

class Payment_Splits
{
    public static function render_button_moip_split()
	{
		$split = new Payment_Split();

		ob_start();
		?>
		<table class='form-table'>
			<tr valign="top">
				<th><?php echo __( 'Split APP', 'woo-moip-official' ); ?></th>
				<td class='forminp'>
					<form method="">
						<button class="webhook button" id="wirecard-split-app" name="wirecard_split_app"><?php echo __( 'Generate APP', 'woo-moip-official' ); ?></button>
					</form>
					<p class="description "><?php echo __( '<strong>After filling in the information above and saving the changes, click generate token.<strong>', 'woo-moip-official' ); ?></p>
				</td>
			</tr>
		</table>
		<?php

		if ( isset( $_POST['wirecard_split_app'] ) ) {
			$split->create_moip_split_app();
        }

		return ob_get_clean();
    }

    public static function render_info_moip_app()
    {
        $app_id          = Utils::decoded( get_option( 'wirecard_split_app_id' ) );
        $app_accesstoken = Utils::decoded( get_option( 'wirecard_split_accesstoken' ) );
		$app_secret      = Utils::decoded( get_option( 'wirecard_split_secret' ) );
		$app_siteurl     = Utils::decoded( get_option( 'wirecard_split_siteurl' ) );
		$app_redirecturl = Utils::decoded( get_option( 'wirecard_split_redirecturl' ) );

        if ( empty( $app_id )
		&& empty( $app_accesstoken )
		&& empty( $app_secret ) ) {
            $app_id          = __( 'not registered', 'woo-moip-official' );
            $app_accesstoken = __( 'not registered', 'woo-moip-official' );
			$app_secret      = __( 'not registered', 'woo-moip-official' );
			$app_siteurl     = __( 'not registered', 'woo-moip-official' );
			$app_redirecturl = __( 'not registered', 'woo-moip-official' );
		}

		ob_start();

		?>
		<table class='form-table webhooks'>
			<tr>
		 		<th><?php _e( 'APP ID:', 'woo-moip-official' ); ?></th>
		 		<td class='forminp'>
		 			<?php echo esc_attr( $app_id ); ?>
		 		</td>
		 	</tr>
            <tr>
	  	 		<th><?php _e( 'AccessToken:', 'woo-moip-official' ); ?></th>
	  	 		<td class='forminp'>
	  	 			<?php echo esc_attr( $app_accesstoken ); ?>
	  	 		</td>
	  	 	</tr>
	  	 	<tr>
	  	 		<th><?php _e( 'Secret:', 'woo-moip-official' ); ?></th>
	  	 		<td class='forminp'>
	  	 			<?php echo esc_attr( $app_secret ); ?>
	  	 		</td>
			</tr>
			<tr>
	  	 		<th><?php _e( 'Site:', 'woo-moip-official' ); ?></th>
	  	 		<td class='forminp'>
	  	 			<?php echo esc_url( $app_siteurl ); ?>
	  	 		</td>
			</tr>
			<tr>
	  	 		<th><?php _e( 'Redirect url:', 'woo-moip-official' ); ?></th>
	  	 		<td class='forminp'>
	  	 			<?php echo esc_url( $app_redirecturl ); ?>
	  	 		</td>
	  	 	</tr>
		</table>
		<?php

		return ob_get_clean();
    }
}
