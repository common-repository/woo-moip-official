<?php
namespace Woocommerce\Moip\Model;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

// Moip SDK
use Moip\Auth\Connect;

use Woocommerce\Moip\Core;
use Woocommerce\Moip\Helper\Utils;

class Marketplace
{
    public function __construct()
	{
		$this->app_id      = Utils::decoded( get_option( 'wirecard_split_app_id' ) );
		$this->app_secret  = Utils::decoded( get_option( 'wirecard_split_secret' ) );
    }

    public function get_url_connect()
	{
		$user = wp_get_current_user();
		
		update_option( 'wirecard_split_user', base64_encode( $user->ID ) );

		$redirect_uri = rest_url( Core::SPLIT_REST_ROUTE.'/callback?wboid=' .  wp_create_nonce( 'wp_rest' ) );
		$client_id    = $this->app_id;
		$scope        = false;
		$connect      = new Connect( $redirect_uri, $client_id, $scope, Utils::get_url_endpoint_connect() );

		$connect->setScope( Connect::MANAGE_ACCOUNT_INFO );
		$connect->setScope( Connect::TRANSFER_FUNDS );
		$connect->setScope( Connect::RETRIEVE_FINANCIAL_INFO );

		return $connect->getAuthUrl();
	}
}
