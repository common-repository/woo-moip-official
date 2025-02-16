<?php
namespace Woocommerce\Moip\Controller;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

//Native PHP
use Exception;

//Models
use Woocommerce\Moip\Core;
use Woocommerce\Moip\Helper\Utils;
use Woocommerce\Moip\Model\Moip_Connect;
use Woocommerce\Moip\Model\Moip_SDK;
use Woocommerce\Moip\Model\Webhook;
use Woocommerce\Moip\Model\Setting;

//Views
use Woocommerce\Moip\View;

class Moip_Connects
{
	public function __construct() {
		$this->setting  = Setting::get_instance();
		$this->moip_sdk = Moip_SDK::get_instance();

		add_action( 'wp_ajax_GAb1rv70dV', array( $this, 'authorize_request' ) );
		add_action( 'admin_footer', array( $this, 'form_authorize' ) );
		add_action( 'woocommerce_api_' . Moip_Connect::AUTHORIZE_APP_API, array( $this, 'authorize_api' ) );
	}

	public function authorize_request() {
		if ( ! Utils::is_request_ajax() ) {
			exit(0);
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( __( 'You are not allowed to do this!', 'woo-moip-official' ) );
		}

		$mode = Utils::post( 'mode', false );

		if ( ! $mode ) {
			wp_send_json_error( __( 'Mode not defined!', 'woo-moip-official' ) );
		}

		$this->set_authorize_mode( $mode );

		$this->_request_authorize_url( $mode );
	}

	private function _request_authorize_url( $mode ) {
		$model 	  = new Moip_Connect();
        $response = $model->get_authorize_url( $mode );

		if ( isset( $response->authorize_url ) ) {
			wp_send_json_success( $response->authorize_url );
		}

		if ( isset( $response->error ) ) {
			wp_send_json_error( $response->error );
		}

		if ( is_string( $response ) && ! empty( $response ) ) {
			wp_send_json_error( $response );
		}

		wp_send_json_error( __( 'There was an error processing the request.', 'woo-moip-official' ) );
	}

	public function form_authorize() {
		if ( Utils::is_settings_page() ) {
			View\Moip_Connects::render_form_authorize();
		}
	}

	public function authorize_api() {
		if ( Utils::server( 'REQUEST_METHOD' ) !== 'POST' ) {
			$this->delete_token_notification();
			$this->set_token_notification();
			$this->_redirect_dashboard();
		}

		$setting  = Setting::get_instance();
		$raw_data = Utils::get_json_post_data();

		if ( ! isset( $raw_data->token ) || $raw_data->token !== $setting->hash_token ) {
			$this->_redirect_dashboard();
		}

		if ( empty( $raw_data->auth_data ) || empty( $raw_data->public_key ) ) {
			$this->_redirect_dashboard();
		}

		$setting->set( 'authorize_data', $raw_data->auth_data );
		$setting->set( 'public_key', $raw_data->public_key );
		$setting->set( 'authorize_account', $raw_data->auth_data->moipAccount->id );

		exit(1);
	}

	public function set_token_notification() {
		if ( empty( $this->setting->webhook_id ) ) {
			return;
		}

		try {
			$webhook  = new Webhook( $this->moip_sdk->moip );
			$response = $webhook->create();

			if ( $response->token ) {
				$this->setting->set( 'webhook_token', $response->token );
				$this->setting->set( 'webhook_id', $response->id );
			}

			unset( $webhook );

		} catch( Exception $e ) {
			//error_log( $e->__toString() );
		}
	}

	public function delete_token_notification() {
		if ( empty( $this->setting->webhook_id ) ) {
			return;
		}

		try {
			$model          = new Webhook( $this->moip_sdk->moip );
			$notifications  = $model->get();
			$response       = json_decode( json_encode( $notifications ), true );

			foreach ( $response as $notification ) {
				$model->delete( $notification['id'] );
			}

			unset($model);

		} catch( Exception $e ) {
			//error_log( $e->__toString() );
		}

	}

	private function _redirect_dashboard() {
		wp_redirect( Core::get_page_link() );
		exit(0);
	}

    public function set_authorize_mode( $mode ) {
        $setting = Setting::get_instance();

        $setting->set( 'authorize_mode', $mode );
    }

	public function get_authorized_url() {
		$model = new Moip_Connect();

		return $model->get_authorized_url( $this->mode );
	}
}
