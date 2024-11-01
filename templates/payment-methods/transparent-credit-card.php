<?php
if ( ! function_exists( 'add_action' ) ) {
	exit(0);
}

use Woocommerce\Moip\Core;
use Woocommerce\Moip\Model\Setting;
use Woocommerce\Moip\View\Checkouts as Checkouts_View;

if ( ! $model->settings->is_active_credit_card() ) {
	return;
}

$brand            = strtolower( $customer->credit_card_brand );
$setting          = Setting::get_instance();
$min_installments = str_replace( ',', '.', $setting->installments_minimum );
$max_installments = intval( $setting->installments_maximum );
$message          = __( 'Installments quantity', 'woo-moip-official' );
$discount_type    = $setting->get_moip_discount_type();
$save_credit_card = $setting->is_save_credit_card();

/*Discount*/
$discount_amount  = (float) str_replace( ',', '.', $setting->wirecard_discount_number );
$wirecard_fee     = ( $discount_type / 100 ) * $discount_amount;

if ( $setting->is_active_render_discount() && $setting->is_active_billet_banking() ) {
	$cart_total = $cart_total + $wirecard_fee;
}

if ( $min_installments && $setting->is_active_installments() ) :

	$message = sprintf(
			'%s (%s %s)',
			__( 'Installments quantity', 'woo-moip-official' ),
			__( 'minimum installment R$', 'woo-moip-official' ),
			$min_installments
		);

endif;

?>

<fieldset class="wc-credit-card-form wc-payment-form">
	<div class="wc-moip-store-cc-content wc-moip-hide-field"
		 data-element="stored-cc-info">

        <p class="form-row form-row-wide">
            <label>
                <?php _e( 'Registered credit card', 'woo-moip-official' ); ?>
            </label>

			<input class="input-text wc-moip-credit-card-form-card-number <?php echo esc_attr( $brand ); ?>"
				inputmode="numeric"
				type="tel"
				placeholder="•••• •••• •••• <?php echo esc_attr( $customer->credit_card_last_numbers ); ?>">
        </p>

        <p class="form-row form-row-first">

            <a href="javascript:void(0);"
               class="wc-moip-change-cc"
               data-type="new"
               data-action="change-cc">

            	<?php _e( 'Use new credit card', 'woo-moip-official' ); ?>
        	</a>

        </p>
	</div>

	<div class="tab-creditcard-container">
		<?php if ( $model->settings->is_active_render_discount() && $model->settings->is_active_billet_banking() ) : ?>
			<div class="wbo-discount-container">
				<?php
					printf( '<img src="%1$s" alt="%2$s" title="%2$s" />',
						Core::plugins_url( 'assets/images/credit-card-discount.png' ),
						__( 'Bank Billet', 'woo-moip-official' )
					);
				?>
				<span>
					<strong><?php echo wc_price( $cart_total ); ?></strong>
					<?php echo __( 'on the credit card', 'woo-moip-official' ); ?>
					<?php
						printf( '%s %s',
						__( 'in until', 'woo-moip-official' ),
							$max_installments . 'x.'
						);
					?>
				</span>
			</div>
		<?php endif; ?>

		<div class="wc-credit-card-info"
			data-element="fields-cc-data">
            <!--CARD HOLDER NAME-->
			<p class="form-row form-row-wide">

				<label for="card-holder"><?php _e( 'Card Holder Name', 'woo-moip-official' ); ?> <small>(<?php _e( 'as recorded on the card', 'woo-moip-official' ); ?>)</small> <span class="required">*</span></label>

				<input id="card-holder" data-element="card-holder"
					class="input-text wc-moip-credit-card-form-card-holder"
					type="text"
					minlength="3"
					name="moip_fields[card_holder]"
					placeholder="<?php _e( 'Name printed on card', 'woo-moip-official' ); ?>">
			</p>
            <!--CARD NUMBER-->
			<p class="form-row form-row-wide">

				<label for="card-number"><?php _e( 'Card number', 'woo-moip-official' ); ?> <span class="required">*</span></label>

				<input id="card-number" data-element="card-number"
					class="input-text wc-moip-credit-card-form-card-number wc-credit-card-form-card-number"
					inputmode="numeric"
					type="tel"
					placeholder="•••• •••• •••• ••••">
			</p>
            <!--CARD EXPIRY-->
			<p class="form-row form-row-first">

				<label for="card-expiry">
					<?php _e( 'Expiry (MM/YY)', 'woo-moip-official' ); ?>
					<span class="required">*</span>
				</label>

				<input id="card-expiry" data-element="card-expiry"
					class="input-text wc-credit-card-form-card-expiry"
					inputmode="numeric"
					type="tel"
					maxlength="7"
					placeholder="<?php _e( 'MM / YY', 'woo-moip-official' ); ?>">
			</p>
            <!--CARD CVC-->
			<p class="form-row form-row-last">

				<label for="card-cvc">
					<?php _e( 'Card code', 'woo-moip-official' ); ?> <span class="required">*</span>
				</label>

				<input id="card-cvc"
					data-element="card-cvc"
					class="input-text wc-credit-card-form-card-cvc"
					inputmode="numeric"
					type="tel"
					maxlength="4"
					placeholder="CVC"
					style="width:100px">
			</p>

			<p class="form-row form-row-wide">

				<a href="javascript:void(0);"
				class="wc-moip-change-cc wc-moip-hide-field"
				data-type="old"
				data-element="old-cc-info"
				data-action="change-cc">
					<?php _e( 'Use old credit card', 'woo-moip-official' ); ?>
				</a>

            </p>
		</div>
	</div>

	<p class="form-row form-row-first">

		<label for="installments">
			<?php echo esc_attr( $message ); ?><span class="required">*</span>
		</label>

		<select id="installments"
				data-action="select2"
				data-element="installments"
				name="moip_fields[installments]">

			<?php Checkouts_View::render_installments( $cart_total ); ?>
		</select>
	</p>

	<?php if ( $setting->is_enabled_cpf_holder() ) : ?>
		<!--CPF-->
		<p class="form-row form-row-wide">

			<label for="cpf-holder"><?php _e( 'CPF', 'woo-moip-official' ); ?> <small>(<?php _e( 'card owner', 'woo-moip-official' ); ?>)</small> <span class="required">*</span></label>

			<input id="cpf-holder" data-element="cpf-holder"
				class="input-text wc-moip-credit-card-form-cpf-holder"
				inputmode="numeric"
				type="tel"
				maxlength="14"
				placeholder="<?php _e( 'Card owner CPF', 'woo-moip-official' ); ?>"
				name="moip_fields[cpf_holder]">
		</p>
	<?php endif; ?>

	<?php if ( $setting->is_enabled_birth_holder() ) : ?>
		<!--DATE BIRTH-->
		<p class="form-row form-row-wide">

			<label for="birth-holder"><?php _e( 'Date birth', 'woo-moip-official' ); ?> <small>(<?php _e( 'card owner', 'woo-moip-official' ); ?>)</small> <span class="required">*</span></label>

			<input id="birth-holder" data-element="birth-holder"
				class="input-text wc-moip-credit-card-form-birth-holder"
				inputmode="numeric"
				type="tel"
				maxlength="14"
				placeholder="<?php _e( 'DD/MM/YYYY', 'woo-moip-official' ); ?>"
				name="moip_fields[birth_holder]">

		</p>
	<?php endif; ?>

	<?php if ( $setting->is_enabled_phone_holder() ) : ?>
		<!--PHONE-->
		<p class="form-row form-row-wide">

			<label for="phone-holder"><?php _e( 'Phone', 'woo-moip-official' ); ?> <small>(<?php _e( 'card owner', 'woo-moip-official' ); ?>)</small> <span class="required">*</span></label>

			<input id="phone-holder" data-element="phone-holder"
				class="input-text wc-moip-credit-card-form-phone-holder"
				inputmode="numeric"
				type="tel"
				maxlength="14"
				placeholder="<?php _e( '(XX) XXXX-XXXX', 'woo-moip-official' ); ?>"
				name="moip_fields[phone_holder]">
		</p>
	<?php endif; ?>

	<?php if ( $save_credit_card = false ) : ?>
		<p class="form-row form-row-first">
			<label for="store-credit-card">
				<input type="checkbox"
					id="store-credit-card"
					name="moip_fields[store_credit_card]"
					value="1"
					<?php checked( $customer->stored_credit_card, true ); ?>>

				<?php _e( 'Save this card for future purchases', 'woo-moip-official' ); ?>
			</label>
		</p>
	<?php endif; ?>
	<div class="clear">
		<input type="hidden" data-element="hash" name="moip_fields[hash]">
		<input type="hidden" data-element="fail" name="moip_fields[fail]">
		<input type="hidden" data-element="pKey" value="<?php esc_attr_e( base64_encode( $public_key ) ); ?>">
	</div>
</fieldset>
