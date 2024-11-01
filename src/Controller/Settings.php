<?php
namespace Woocommerce\Moip\Controller;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Moip\Core;

class Settings
{
	public function __construct()
	{
		add_filter( Core::plugin_basename( 'plugin_action_links_' ), array( $this, 'plugin_link' ) );
		add_filter( 'plugin_row_meta', array( $this, 'support_links' ), 10, 4 );

		$this->gateway_load();
	}

	/**
	 * Add link settings page
	 *
	 * @since 1.0
	 * @param Array $links
	 * @return Array
	 */
	public function plugin_link( $links )
	{
		$links_settings = array( sprintf(
            '<a href="%s">%s</a>',
			Core::get_page_link(),
            __( 'Settings', Core::TEXTDOMAIN )
        ) );

        return array_merge( $links_settings, $links );
	}

	/**
	 * Add support link page
	 *
	 * @since 1.0
	 * @param Array $links
	 * @return Array
	 */
	public function support_links( $links_array, $plugin_file_name, $plugin_data, $status )
	{
		if ( $plugin_file_name === 'woo-moip-official/woo-moip-official.php' ) {
			$links_array[] = '<a href="'.Core::support_link().'"target="_blank">'.__( 'Apiki Support', 'woo-moip-official' ).'</a>';
			$links_array[] = '<a href="'.Core::documentation_link().'"target="_blank">'.__( 'Documentation', 'woo-moip-official' ).'</a>';
		}

		return $links_array;
	}

	public function gateway_load()
	{
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}

		add_filter( 'woocommerce_payment_gateways', array( $this, 'add_payment_gateway' ) );
	}

	public function add_payment_gateway( $methods )
	{
		$methods[] = __NAMESPACE__ . '\Custom_Gateways';

		return $methods;
	}
}
