<?php
namespace Woocommerce\Moip\Model;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Moip\Core;
use Woocommerce\Moip\Model\Setting;
use Woocommerce\Moip\Helper\Utils;

class Payment_Split
{
    private function _get_app_header()
    {
        $settings    = Setting::get_instance();
        $moip_token  = $settings->wirecard_manual_token;
        $moip_key    = $settings->wirecard_manual_key;
        $credentials = Utils::encoded( $moip_token.':'.$moip_key );

        if ( empty( $moip_token ) && empty( $moip_key ) ) {
            ob_start();
			printf('<div id="message" class="error"><p>%s</p></div>',
			__( 'Moip: Before generating the APP you need to save the settings!', 'woo-moip-official' ) );

            return '';
        }

        $args = [
            'authorization: Basic '. $credentials,
            'content-type: application/json'
        ];

		return $args;
    }

    public function set_moip_split_body()
    {
        $settings          = Setting::get_instance();
        $split_name        = __( 'Moip Brazil Official Split', 'woo-moip-official' );
        $split_description = __( 'Moip Brazil Official Payment Split', 'woo-moip-official' );
        $get_name          = $settings->wirecard_split_name;
        $get_description   = $settings->wirecard_split_description;

        if ( $get_name ) {
            $split_name = $get_name;
        }

        if ( $get_description ) {
            $split_description = $get_description;
        }

        $args = [
            'name'        => $split_name,
            'description' => $split_description,
            'site'        => site_url(),
            'redirectUri' => rest_url( Core::SPLIT_REST_ROUTE.'/callback' )
        ];

		return $args;
    }

    public function create_moip_split_app()
    {
        $notification_url = Utils::get_url_endpoint() . '/v2/channels/';
        $header           = $this->_get_app_header();
        $body             = json_encode( $this->set_moip_split_body() );
        $curl             = curl_init();

        if ( empty( $header ) ) {
            return;
        }

        $options = [
            CURLOPT_URL            => $notification_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => $header
        ];

        curl_setopt_array( $curl, $options );

        $response      = curl_exec( $curl );
        $err           = curl_error( $curl );
        $response_data = json_decode( $response );

        curl_close( $curl );

        if ( $err ) {
          echo "cURL Error #:" . $err;
        }

        update_option( 'wirecard_split_app_id', Utils::encoded( $response_data->id ) ) ;
        update_option( 'wirecard_split_accesstoken', Utils::encoded( $response_data->accessToken ) );
        update_option( 'wirecard_split_secret', Utils::encoded( $response_data->secret ) );
        update_option( 'wirecard_split_siteurl', Utils::encoded( $response_data->website ) );
        update_option( 'wirecard_split_redirecturl', Utils::encoded( $response_data->redirectUri ) );
    }
}
