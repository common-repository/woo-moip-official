<?php
namespace Woocommerce\Moip\Controller;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Moip\Helper\Utils;
use Woocommerce\Moip\Model\Setting;

use WC_Order;
use stdClass;
use WC_Order_Item_Shipping;
use WC_Order_Item_Product;

class Payment_Splits
{
    public function __construct()
	{
        $this->setting     = Setting::get_instance();
        $this->marketplace = $this->setting->marketplace_options_type;

        if ( $this->setting->is_moip_payment_split() ) {
            add_filter( 'apiki_moip_create_order', array( $this, 'add_split_payment' ), 10, 2 );
        }
    }

    public function add_split_payment( $moip_order, WC_Order $order )
	{
        $sellers     = $this->get_sellers( $order );
        $sellers_arr = [];

        foreach ( $sellers as $seller ) {
            $positions      = $this->get_position_products_vendor( $seller, $order );
            $value_shipping = 0;
            $count          = 0;
            $percentage     = 0;
            $seller_obj     = new stdClass();

            foreach ( $order->get_items('shipping') as $ship ) {
                if ( in_array( $count, $positions ) ) {
                    $value_shipping += $ship->get_total();
                }
                $count++;
            }

            $count          = 0;
            $value_total    = 0;
            $seller_obj->id = $seller;

            if ( $this->marketplace == 'dokan_marketplace' ) {
                $percentage = $this->get_dokan_percentage_comission();
            }

            $seller_obj->value_shipping = $value_shipping;
            $seller_obj->moip_id        = $this->get_moip_id( $seller, false );

            foreach ( $order->get_items() as $item ) {
                if ( in_array( $count, $positions ) ) {
                    $value_total += $item->get_total();
                }
                $count++;
            }

            $seller_obj->valor_product = $value_total;

            if ( $this->marketplace == 'wcfm_marketplace' ) {
                $percentage                 = $this->get_wcfm_percentage_comission( $seller_obj->id );
                $wcfm_shipping_total        = $this->_wbo_wcfm_get_shipping( $order );
                $wcfm_subtotal_total        = $this->_wbo_wcfm_get_subtotal( $order );
                $seller_obj->value_shipping = $wcfm_shipping_total[$seller_obj->id];
                $seller_obj->valor_product  = $wcfm_subtotal_total[$seller_obj->id];
            }

            $seller_obj->percentage = $percentage;

            array_push( $sellers_arr, $seller_obj );
        }

        foreach ( $sellers_arr as $seller_arr ) {
            $percentage_vendor       = ( 100 - $seller_arr->percentage) / 100;
            $shipping                = isset( $seller_arr->shipping ) ? $seller_arr->shipping : 0;
            $value                   = ( $seller_arr->valor_product * $percentage_vendor ) + $shipping;
            $seller_arr->value_total = $value + $seller_arr->value_shipping;

            if ( $this->setting->is_shipping_admin() ) {
                $seller_arr->value_total = $seller_arr->valor_product * $percentage_vendor;
            }

            $moip_order->addReceiver(
                $seller_arr->moip_id,
                'SECONDARY',
                Utils::format_order_price( $seller_arr->value_total ),
                null,
                false //feePayor
            );
        }

        return $moip_order;
    }

    private function get_dokan_percentage_comission()
    {
        $options = get_option( 'dokan_selling', array() );

        return $options['admin_percentage'];
    }

    private function get_wcfm_percentage_comission( $user_id )
    {
        $options_user     = get_user_meta( $user_id, 'wcfmmp_profile_settings', true );
        $options_defult   = get_option('wcfm_commission_options');
        $commissions_user = $options_user['commission'];
        $commissions      = $commissions_user['commission_percent'];
        $commission_mode  = $commissions_user['commission_mode'];

        if ( $commission_mode === 'global' ) {
            $commissions = $options_defult['commission_percent'];
        }

        if ( $commission_mode === 'percent' ) {
            if ( empty( $commissions ) || $commissions <= 0 ) {
                $commissions = $options_defult['commission_percent'];
            }
        }

        return $commissions;

    }

    private function get_sellers( WC_Order $order )
    {
        $sellers = [];

        foreach ( $order->get_items() as $item ) {
            $seller_id      =  get_post_field( 'post_author', $item->get_product_id() );
            $vendor_moip_id = get_user_meta( $seller_id, '_wirecard_account_id', true );

            if( ! in_array( $seller_id ,$sellers ) && $vendor_moip_id <> '' )
                array_push( $sellers, $seller_id );
        }
        return $sellers;
    }

    private function get_position_products_vendor( $vendor, WC_Order $order )
    {
        $positions = [];
        $pos       = 0;

        foreach ( $order->get_items() as $item ){
            if ( $vendor == get_post_field( 'post_author', $item->get_product_id() ) ) {
                array_push( $positions, $pos );
            }
           $pos++;
        }
        return $positions;
    }

    private function  get_moip_id( $product_id, $is_product = true )
    {
        $vendor_id = get_post_field( 'post_author', $product_id );

        if ( ! $is_product )
            $vendor_id      = $product_id;
            $vendor         = get_userdata( $vendor_id );
            $vendor_moip_id = get_user_meta( $vendor->ID, '_wirecard_account_id', true);

        return $vendor_moip_id;
    }

    private function _wbo_wcfm_get_shipping( $order ) {
		global $WCFM, $WCFMmp;

        $vendor_wcfm_shipping = array();

		if (!$order ) {
			return $vendor_wcfm_shipping;
        }

        $shipping_items = $order->get_items( 'shipping' );

		foreach ( $shipping_items as $shipping_item_id => $shipping_item ) {
			$order_item_shipping = new WC_Order_Item_Shipping( $shipping_item_id );
            $shipping_vendor_id  = $order_item_shipping->get_meta( 'vendor_id', true );

			if ( $shipping_vendor_id > 0 ) {
				$shipping_item_total = $order_item_shipping->get_total() + $order_item_shipping->get_total_tax();

				if ( isset( $vendor_wcfm_shipping[$shipping_vendor_id] ) ) {
                    $vendor_wcfm_shipping[$shipping_vendor_id] = $vendor_wcfm_shipping[$shipping_vendor_id] + $shipping_item_total;
                } else {
                    $vendor_wcfm_shipping[$shipping_vendor_id] = $shipping_item_total;
                }
			}
		}

		return $vendor_wcfm_shipping;
    }

    private function _wbo_wcfm_get_subtotal( $order ) {
		global $WCFM, $WCFMmp;

        $vendor_wcfm_subtotal = array();

		if ( !$order ) {
			return $vendor_wcfm_subtotal;
		}

        $items = $order->get_items( 'line_item' );

		foreach ( $items as $order_item_id => $item ) {
			$line_item  = new WC_Order_Item_Product( $item );
			$product_id = $line_item->get_product_id();

            if ( $product_id ) {
                $vendor_id = wcfm_get_vendor_id_by_post( $product_id );

				if( $vendor_id ) {
					$line_item_total = $line_item->get_total() + $line_item->get_total_tax();

					if ( isset( $vendor_wcfm_subtotal[$vendor_id] ) ) {
                        $vendor_wcfm_subtotal[$vendor_id] = $vendor_wcfm_subtotal[$vendor_id] + $line_item_total;
                    } else {
                        $vendor_wcfm_subtotal[$vendor_id] = $line_item_total;
                    }
				}
			}
		}

		return $vendor_wcfm_subtotal;
	}
}
