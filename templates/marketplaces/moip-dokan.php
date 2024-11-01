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

if ( $settings->authorize_mode == 'sandbox' ) {
    $status = __( 'A loja está autorizada como <strong>SANDBOX</strong>.', 'woo-moip-official' );
}

if ( $settings->authorize_mode == 'production' ) {
    $status = __( 'A loja está autorizada como <strong>PRODUCTION</strong>.', 'woo-moip-official' );
}
?>

<?php do_action( 'dokan_dashboard_wrap_start' ); ?>
    <div class="dokan-dashboard-wrap">
        <?php do_action( 'dokan_dashboard_content_before' ); ?>
        <div class="dokan-dashboard-content dokan-wirecard-content">
            <article class="dokan-wirecard-area">
                <header class="dokan-dashboard-header">
                    <h1 class="entry-title"><?php echo __( 'Moip Account', 'woo-moip-official' ); ?></h1>
                </header>
            </article>

            <?php if ( $admin_user ) : ?>
                <p class="description">
                    <?php echo __( 'Are you the site administrator! Your linked account is the same as the Moip Brazil Official plugin.', 'woo-moip-official' ); ?>
                </p>
            <?php endif; ?>

            <?php if ( !$admin_user && strlen( $wirecard_user_id ) > 0 ) : ?>
            <div class="dokan-wirecard-container">
                <p class="description">
                    <?php echo $status; ?>
                </p>
                <p class="description">
                    <?php echo __( 'Your account is already linked to Wirecard. If you need you can authorize again by clicking the button below.', 'woo-moip-official' ); ?>
                </p>
                <a href="<?php echo esc_url( $model->get_url_connect() ); ?>">
                    <button type="button" class="reauthorize-button">
                        <?php echo __( 'Authorize Again', 'woo-moip-official' ); ?>
                    </button>
                </a>
                <p class="description ">
                    <?php echo __( 'Click the button to sign in to your Moip account again if necessary.', 'woo-moip-official' ); ?>
                </p>
            </div>
            <?php endif; ?>

            <?php if ( !$admin_user && strlen( $wirecard_user_id ) == 0 ) : ?>
                <div class="dokan-wirecard-container">
                    <div class="dokan-page-help">
                        <?php echo __( 'Before authorizing you must have a Moip account.', 'woo-moip-official' ); ?>
                    </div>
                    <a href="<?php echo esc_url( $model->get_url_connect() ); ?>">
                        <button type="button" class="authorize-button">
                            <?php echo __( 'Authorize', 'woo-moip-official' ); ?>
                        </button>
                    </a>
                    <p class="description ">
                        <?php echo __( 'Click the button to sign in to your Moip account.', 'woo-moip-official' ); ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
        <?php do_action( 'dokan_dashboard_content_after' ); ?>
    </div>
<?php do_action( 'dokan_dashboard_wrap_end' );
