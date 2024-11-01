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

class Marketplaces_Wcfm
{
	public function __construct()
	{
        $this->app_id     = Utils::decoded( get_option( 'wirecard_split_app_id' ) );
        $this->app_secret = Utils::decoded( get_option( 'wirecard_split_secret' ) );
        $this->app_nonce  = get_option( 'wirecard_split_nonce' );
        $this->app_user   = Utils::decoded( get_option( 'wirecard_split_user' ) );
        $this->store_page = get_option( 'wcfm_page_options' );
        $setting          = Setting::get_instance();
        $marketplace      = $setting->marketplace_options_type;

        if ( $setting->is_moip_payment_split() && $marketplace == 'wcfm_marketplace' ) {
            add_filter( 'wcfm_menus', [ $this, 'wbo_wcfm_add_menu' ] );
            add_action( 'before_wcfm_load_views', [ $this, 'wbo_wcfm_load_views' ], 30 );
            add_filter( 'wcfm_query_vars', [ $this, 'wbo_wcfm_refund_query_vars' ], 20 );
            add_action( 'rest_api_init', [ $this, 'moip_split_register_route' ] );
        }
    }

    public function wbo_wcfm_add_menu( $wcfm_menus )
    {
        $wcfm_menus['wcfm-moip-brazil-official'] = [
            'label'    => __( 'Moip Settings', 'woo-moip-official' ),
            'url'      => $this->wbo_wcfm_moip_url(),
            'icon'     => 'fa-id-card',
            'priority' => 71
        ];

        return $wcfm_menus;
    }

    public function wbo_wcfm_moip_url()
    {
        global $WCFM;

        $wbo_page              = get_wcfm_page();
        $wbo_get_wcfm_moip_url = wcfm_get_endpoint_url( 'wbo-moip', '', $wbo_page );

        return apply_filters( 'wcfm_moip_brazil_official', $wbo_get_wcfm_moip_url );
    }

    public function wbo_wcfm_load_views( $end_point )
    {
        switch( $end_point ) {
            case 'wbo-moip':
                require_once( Core::get_file_path( 'moip-wcfm.php', 'templates/marketplaces/' ) );
            break;
        }
    }

    public function wbo_wcfm_refund_query_vars( $fields )
    {
        $wcfm_modified_endpoints = (array) get_option( 'wcfm_endpoints' );

        $fields['wbo-moip'] = ! empty( $wcfm_modified_endpoints['wbo-moip'] ) ? $wcfm_modified_endpoints['wbo-moip'] : 'wbo-moip';

        return $fields;
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
		$nonce    = $request->get_param( 'wboid' );
		$code     = $request->get_param( 'code' );
        $user     = $this->app_user;
        $store_id = $this->store_page['wc_frontend_manager_page_id'];

        if ( isset( $store_id ) ) {
            $page       = get_post( $store_id );
            $store_slug = $page->post_name;
        }

		if ( ! $nonce || ! $code || ! $user ) {
            return new WP_REST_Response( [ 'message' => 'Param invalid.' ], 400 );
		}
		$redirect_uri  = rest_url( Core::SPLIT_REST_ROUTE.'/callback?wboid=' .  wp_create_nonce( 'wp_rest' ) );
        $client_id     = $this->app_id;
		$client_secret = $this->app_secret;
		$scope         = false;
		$connect       = new Connect( $redirect_uri, $client_id, $scope, Utils::get_url_endpoint_connect() );

		$connect->setClientSecret( $client_secret );
		$connect->setCode( $code );
		$auth           = $connect->authorize();
		$moip_client_id = $auth->moipAccount->id;

		if ( Utils::verify_moip_account( $moip_client_id ) ) {
            wp_redirect( site_url( $store_slug.'/wbo-moip/&return=same_account' ) );
			exit( 0 );
        }

        if ( ! Utils::verify_moip_account( $moip_client_id ) ) {
            delete_user_meta( $user, '_wirecard_account_id' );
            delete_user_meta( $user, '_wirecard_access_token' );
            add_user_meta( $user, '_wirecard_account_id', $auth->moipAccount->id, true );
            add_user_meta( $user, '_wirecard_access_token', $auth->accessToken, true );
            wp_redirect( site_url( $store_slug.'/wbo-moip' ) );
			exit( 0 );
        }
    }
}
