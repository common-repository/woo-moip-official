<?php
namespace Woocommerce\Moip\Controller;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Moip\Core;
use Woocommerce\Moip\Model\Setting;
use Woocommerce\Moip\Helper\Utils;
use Woocommerce\Moip\Model\Order;

class Orders
{
    public function __construct()
    {
        $this->settings = Setting::get_instance();

        add_action( 'woocommerce_view_order', array( $this, 'add_billet_page' ), 20 );
        add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'billet_button_to_list' ), 10, 2 );
        add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_order_data_in_admin' ) );
    }

    public function add_billet_page( $order_id )
    {
        Utils::template_include( 'templates/orders/html-order-billet', [ 'order' => new Order( $order_id ) ] );
    }

    public function billet_button_to_list( $actions, $wc_order )
    {
        $order_id = $wc_order->get_id();
        $order    = new Order( $order_id );

        if ( $order->payment_links->payBoleto->redirectHref ) {
            $actions['moip'] = array(
                'url'  => $order->payment_links->payBoleto->redirectHref . '/print',
                'name' => __( 'Billet Moip', 'woo-moip-official' ),
            );
        }

        return $actions;
    }

    public function display_order_data_in_admin( $wc_order )
    {
        $order_id = $wc_order->get_id();
        $settings = $this->settings;

        Utils::template_include( 'templates/orders/html-order-billing',
            compact( 'wc_order', 'order_id', 'settings' )
        );
    }

}
