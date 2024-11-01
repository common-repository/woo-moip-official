<?php
namespace Woocommerce\Moip\Controller;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

// Moip SDK
use Moip\Auth\Connect;

use Woocommerce\Moip\Core;
use Woocommerce\Moip\Helper\Utils;
use Woocommerce\Moip\Model\Setting;

use WP_REST_Request;
use WP_REST_Response;

class Marketplaces_Dokan
{
	public function __construct()
	{
        $this->app_id     = Utils::decoded( get_option('wirecard_split_app_id') );
        $this->app_secret = Utils::decoded( get_option('wirecard_split_secret') );
        $this->app_nonce  = get_option('wirecard_split_nonce');
        $this->app_user   = Utils::decoded( get_option('wirecard_split_user') );
        $setting          = Setting::get_instance();
        $marketplace      = $setting->marketplace_options_type;

        if ( $setting->is_moip_payment_split() && $marketplace == 'dokan_marketplace' ) {
            add_filter( 'dokan_query_var_filter', [ $this, 'wbo_dokan_load_document_menu' ] );
            add_filter( 'dokan_get_dashboard_nav', [ $this, 'wbo_dokan_add_help_menu' ] );
            add_action( 'dokan_load_custom_template', [ $this, 'wbo_dokan_load_template' ] );
            add_action( 'rest_api_init', [ $this, 'moip_split_register_route' ] );
        }
    }

    // BEGIN DOKAN
    public function wbo_dokan_load_document_menu( $dokan_menus )
    {
        $dokan_menus['moip-account'] = 'moip-account';

        return $dokan_menus;
    }

    public function wbo_dokan_add_help_menu( $urls )
    {
        $urls['moip-account'] = array(
            'title' => __( 'Moip Account', 'woo-moip-official'),
            'icon'  => '<i class="fa fa-address-card-o"></i>',
            'url'   => dokan_get_navigation_url( 'moip-account' ),
            'pos'   => 51
        );

        return $urls;
    }

    public function wbo_dokan_load_template( $dokan_menus )
    {
        if ( isset( $dokan_menus['moip-account'] ) ) {
            require_once( Core::get_file_path( 'moip-dokan.php', 'templates/marketplaces/' ) );
        }
    }

    public function moip_split_register_route()
	{
		register_rest_route(
			Core::SPLIT_REST_ROUTE,
			'/callback',
			[
				'methods'             => 'GET',
                'callback'            => [ $this, 'parse_moip_callback' ],
                'permission_callback' => '__return_true'
            ]
		);
    }

    public function parse_moip_callback( WP_REST_Request $request )
	{
		$nonce = $request->get_param( 'wboid' );
		$code  = $request->get_param( 'code' );
		$user  = $this->app_user;

		if ( ! $nonce || ! $code || ! $user ) {
            return new WP_REST_Response( array( 'message' => 'Param invalid.' ), 400 );
		}
		$redirect_uri    = rest_url( Core::SPLIT_REST_ROUTE.'/callback?wboid=' .  wp_create_nonce( 'wp_rest' ) );
		$client_id       = $this->app_id;
		$client_secret   = $this->app_secret;
		$scope           = false;
		$connect         = new Connect( $redirect_uri, $client_id, $scope, Utils::get_url_endpoint_connect() );
		$dokan_dashboard = dokan_get_navigation_url( 'moip-account' );

		$connect->setClientSecret( $client_secret );
		$connect->setCode( $code );
		$auth           = $connect->authorize();
		$moip_client_id = $auth->moipAccount->id;

		if ( Utils::verify_moip_account( $moip_client_id ) ) {
            wp_redirect( $dokan_dashboard . '&return=same_account' );
			exit( 0 );
        }

        if ( ! Utils::verify_moip_account( $moip_client_id ) ) {
            delete_user_meta( $user, '_wirecard_account_id' );
            delete_user_meta( $user, '_wirecard_access_token' );
            add_user_meta( $user, '_wirecard_account_id', $auth->moipAccount->id, true );
            add_user_meta( $user, '_wirecard_access_token', $auth->accessToken, true );
            wp_redirect( $dokan_dashboard );
			exit( 0 );
        }
    }
    // END DOKAN
}
