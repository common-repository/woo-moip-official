<?php
namespace Woocommerce\Moip\View;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Moip\Core;
use Woocommerce\Moip\Helper\Utils;
use Woocommerce\Moip\Model\Setting;

class Moip_Emails
{
    public static function send_moip_billet_email( $order_id )
    {
        $settings      = Setting::get_instance();
        $from_name     = get_option( 'blogname' );
        $from_email    = get_option( 'admin_email' );
        $subject_name  = __( 'Moip Billet', 'woo-moip-official' );
        $order_subject = __( 'Order #', 'woo-moip-official' );

        add_filter( 'wp_mail_content_type', function() {
            return 'text/html';
        } );

        if ( $settings->wirecard_billet_from_name ) {
            $from_name = $settings->wirecard_billet_from_name;
        }

        if ( $settings->wirecard_billet_from_email ) {
            $from_email = $settings->wirecard_billet_from_email;
        }

        if ( $settings->wirecard_billet_subject ) {
            $subject_name = $settings->wirecard_billet_subject;
        }

        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: '.$from_name.' <'.$from_email.'>';
        $subject   = $subject_name . '['.$order_subject.$order_id.']';
        $email     = get_post_meta( $order_id, '_billing_email', true );

        ob_start();
           Utils::template_include( 'templates/emails/billet',
                compact( 'order_id' )
            );
            $message = ob_get_contents();
        ob_end_clean();

        wp_mail( $email, $subject, $message, $headers );
    }
}
