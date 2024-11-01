<?php
namespace Woocommerce\Moip\Model;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Moip\Core;
use Woocommerce\Moip\Helper\Utils;
use Woocommerce\Moip\Model\Setting;

class Checkout
{
	private $ID;

	public function __construct( $ID = false )
	{
		if ( false !== $ID ) {
			$this->ID = absint( $ID );
		}
	}

	public function get_order()
	{
		return new Order( $this->ID );
	}

	public function prepare_fields( $form_data )
	{
		if ( empty( $form_data ) ) {
			return false;
		}

		$fields = array();

		foreach ( $form_data as $data ) {

			if ( ! isset( $data['name'] ) || ! isset( $data['value'] ) ) {
				continue;
			}

			if ( empty( $data['value'] ) ) {
				continue;
			}

			$fields[ $data['name'] ] = Utils::rm_tags( $data['value'], true );

			if ( $data['name'] == 'card_number' ) {
				$fields[ $data['name'] ] = Utils::format_document( $data['value'] );
			}

			if ( $data['name'] == 'card_expiry' ) {
				$expiry_pieces               = explode( '/',  $data['value'] );
				$fields['card_expiry_month'] = trim( $expiry_pieces[0] );
				$card_year                   = date_create_from_format( 'y', trim( $expiry_pieces[1] ) );
				$fields['card_expiry_year']  = $card_year->format( 'Y' );
			}
		}

		return $fields;
	}

	public static function wbo_validate_credit_card()
	{
		$fields      = Utils::post( 'moip_fields', false );
		$setting     = Setting::get_instance();

		if ( $setting->is_checkout_transparent() ) {
			if ( ! $fields['card_holder'] ) {
				wc_add_notice( __( 'Moip: Card Holder Empty', 'woo-moip-official' ), 'error' );
			}

			if ( strlen( $fields['card_holder'] ) < 3 ) {
				wc_add_notice( __( 'Moip: Card Holder Invalid', 'woo-moip-official' ), 'error' );
			}

            if ( $setting->is_enabled_cpf_holder() ) {
                if ( ! $fields['cpf_holder'] ) {
                    wc_add_notice( __( 'Moip: CPF Number Empty', 'woo-moip-official' ), 'error' );
                }
			}

			if ( $setting->is_enabled_birth_holder() ) {
                if ( ! $fields['birth_holder'] ) {
                    wc_add_notice( __( 'Moip: Date birth Number Empty', 'woo-moip-official' ), 'error' );
                }
			}

			if ( $setting->is_enabled_phone_holder() ) {
                if ( ! $fields['phone_holder'] ) {
                    wc_add_notice( __( 'Moip: Phone Number Empty', 'woo-moip-official' ), 'error' );
                }
            }

			if ( ! $fields['installments'] ) {
				wc_add_notice( __( 'Moip: Card Installments Empty', 'woo-moip-official' ), 'error' );
			}

			if ( $fields['fail'] == 1 ) {
				wc_add_notice( __( 'Moip: Invalid credit card. Check all fields e try again.', 'woo-moip-official' ), 'error' );
			}
		}

	}
}
