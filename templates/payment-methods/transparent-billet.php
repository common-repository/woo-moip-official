<?php
if ( ! function_exists( 'add_action' ) ) {
	exit(0);
}

use Woocommerce\Moip\Core;
use Woocommerce\Moip\View\Checkouts as Checkouts_View;

$amount = (float) str_replace( ',', '.', $model->settings->wirecard_discount_number );

if ( ! $model->settings->is_active_billet_banking() ) {
	return;
}

?>

<div id="tab-billet" class="entry-content">
	<div class="tab-billet-container">
		<ul class="moip-tab-billet">
		<?php if ( $model->settings->is_active_render_discount() ) : ?>
			<div class="wbo-discount-container">
				<?php
					printf( '<img src="%1$s" alt="%2$s" title="%2$s" />',
						Core::plugins_url( 'assets/images/billet-discount.png' ),
						__( 'Bank Billet', 'woo-moip-official' )
					);
				?>
				<span>
					<strong><?php echo wc_price( WC()->cart->total ); ?></strong>
					<?php echo __( 'on the billet bank', 'woo-moip-official' ); ?>
					<?php 
						printf( '(<div id="wbo-discount-amount">%s %s</div>).',
							$amount . '%',
							__( 'Discount', 'woo-moip-official' )
						); 
					?>
				</span>
			</div>
		<?php endif; ?>
			<li>
				<label>
					<p><?php echo Checkouts_View::render_billet_description(); ?></p>
					<?php
						printf( '<img src="%1$s" alt="%2$s" title="%2$s" />',
							Core::plugins_url( 'assets/images/barcode.svg' ),
							__( 'Bank Billet', 'woo-moip-official' )
						);
					?>
				</label>
			</li>
		</ul>
	</div>
</div>