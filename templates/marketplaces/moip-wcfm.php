<?php
if ( ! function_exists( 'add_action' ) ) {
	exit(0);
}

use Woocommerce\Moip\Core;
use Woocommerce\Moip\Model\Setting;
use Woocommerce\Moip\Model\Marketplace;

$model            = new Marketplace();
$settings         = Setting::get_instance();
$current_user     = wp_get_current_user();
$admin_user       = current_user_can( 'administrator' );
$wirecard_user_id = get_user_meta( $current_user->ID, '_wirecard_account_id', true );
$status           = __( '<strong>A loja ainda não está autorizada!<strong>', 'woo-moip-official' );
$url              = '#';

if ( $settings->authorize_mode == 'sandbox' ) {
	$status = __( 'A loja está autorizada como <strong>SANDBOX</strong>.', 'woo-moip-official' );
	$url    = Core::ACC_SANDBOX_URL;
}

if ( $settings->authorize_mode == 'production' ) {
	$status = __( 'A loja está autorizada como <strong>PRODUCTION</strong>.', 'woo-moip-official' );
	$url    = Core::ACC_PRODUCTION_URL;
}

?>
<div class="collapse wcfm-collapse" id="wcfm_wirecard_settings">
  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-retweet"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Moip Settings', 'woo-moip-official' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
	  <div id="wcfm_page_load"></div>

	  <div class="wcfm-container wcfm-top-element-container">
			<h2><?php _e( 'Moip Settings - Payment Split', 'woo-moip-official' ) ?></h2>
			<div class="wcfm-clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
			<div class="wcfm-container">
			<?php if ( $admin_user ) : ?>
                <p class="description">
                    <?php echo __( 'You are the site administrator you cannot link as a shopkeeper to perform tests!', 'woo-moip-official' ); ?>
                </p>
            <?php endif; ?>

            <?php if ( !$admin_user && strlen( $wirecard_user_id ) > 0 ) : ?>
            <div class="wcfm-wirecard-container">
                <p class="description">
                    <?php echo esc_attr( $status ); ?>
                </p>
                <p class="description">
                    <?php echo __( 'Your account is already linked to Moip. If you need you can authorize again by clicking the button below.', Core::TEXTDOMAIN ); ?>
                </p>
                <a class="wbo_reauthorize_client text_tip" href="<?php echo esc_url( $model->get_url_connect() ); ?>">
                    <button type="button" class="reauthorize-button" style="border: solid 1px #333333;border-radius: 3px;">
					<span style="margin-right:7px;" class="wcfmfa fa-user-plus"></span>
                        <?php echo __( 'Authorize Again', 'woo-moip-official' ); ?>
                    </button>
                </a>
                <p class="description ">
                    <?php echo __( 'Click the button to sign in to your Moip account again if necessary.', 'woo-moip-official' ); ?>
                </p>
            </div>
            <?php endif; ?>

            <?php if ( !$admin_user && strlen( $wirecard_user_id ) == 0 ) : ?>
                <div class="wcfm-wirecard-container">
					<p class="description">
						<?php echo esc_attr( $status ); ?>
					</p>
                    <div class="wcfm-page-help" style="margin-bottom:20px;">
                        <?php _e( 'Before authorizing you must have a Moip account.', 'woo-moip-official' ); ?>
						<?php
							echo sprintf(
								'<a href="%s" target="__blank">%s</a>',
								esc_url( $url ),
								__( 'Create an account', 'woo-moip-official' )
							);
						?>
                    </div>
					<a class="wbo_authorize_client text_tip" href="<?php echo esc_url( $model->get_url_connect() ); ?>">
                        <button type="button" class="authorize-button" style="border: solid 1px #333333;border-radius: 3px;">
							<span style="margin-right:7px;" class="wcfmfa fa-user-plus"></span>
                            <?php echo __( 'Authorize', 'woo-moip-official' ); ?>
                        </button>
                    </a>
                    <p class="description ">
                        <?php echo __( 'Click the button to sign in to your Moip account.', 'woo-moip-official' ); ?>
                    </p>
                </div>
            <?php endif; ?>
			</div>
			<div class="wcfm-clearfix"></div>
	</div>
</div>
