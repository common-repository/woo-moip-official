<?php
namespace Woocommerce\Moip;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Moip\Helper\Utils;
use Woocommerce\Moip\Model\Setting;

class Core
{
	private static $_instance = null;

	const SLUG               = 'woo-moip-official';
	const TEXTDOMAIN         = 'woo-moip-official';
	const PREFIX             = 'moip';
	const LOCALIZE_SCRIPT_ID = 'MOIPGlobalVars';
	const SPLIT_REST_ROUTE   = 'moip-brazil-official-split/v1';
	const WEBHOOK_REST_ROUTE = 'moip-brazil-official-webhooks/v1';
	const ACC_SANDBOX_URL    = 'https://bem-vindo-sandbox.wirecard.com.br/';
	const ACC_PRODUCTION_URL = 'https://bem-vindo.wirecard.com.br/';

	private function __construct()
	{
		add_action( 'init', [ __CLASS__, 'load_textdomain' ] );
		add_action( 'admin_init', [ __CLASS__, 'redirect_on_activate' ] );

		self::initialize();
		self::admin_enqueue_scripts();
		self::front_enqueue_scripts();
	}

	public static function load_textdomain()
	{
		load_plugin_textdomain( self::TEXTDOMAIN, false, self::plugin_rel_path( 'languages' ) );
	}

	public static function redirect_on_activate()
	{
		global $pagenow;

		if ( $pagenow !== 'plugins.php' || Utils::get( 'activate-multi' ) ) {
			return;
		}

		if ( ! get_option( APIKI_MOIP_OPTION_ACTIVATE, false ) ) {
			return;
		}

		delete_option( APIKI_MOIP_OPTION_ACTIVATE );

		wp_redirect( self::get_page_link() );

		exit(1);
	}

	public static function initialize()
	{
		$controllers = [
			'Settings',
			'Customers',
            'Checkouts',
			'Marketplaces_Dokan',
			'Marketplaces_Wcfm',
			'Payment_Splits',
			'Webhooks',
			'Moip_Connects',
			'Orders'
		];

		self::load_controllers( $controllers );
	}

	public static function load_controllers( $controllers )
	{
		foreach ( $controllers as $controller ) {
			$class = sprintf( __NAMESPACE__ . '\Controller\%s', $controller );
			new $class();
		}
	}

	public static function get_localize_script_args( $args = [] )
	{
		$defaults = [
			'ajaxUrl'    => Utils::get_admin_url( 'admin-ajax.php' ),
			'WPLANG'     => get_locale(),
			'spinnerUrl' => Utils::get_spinner_url(),
			'prefix'     => self::PREFIX,
		];

		return array_merge( $defaults, $args );
	}

	public static function admin_enqueue_scripts()
	{
		if ( ! Utils::is_settings_page() ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'scripts_admin' ] );
	}

	public static function front_enqueue_scripts()
	{
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'scripts_front' ] );
	}

	public static function scripts_admin()
	{
		self::enqueue_scripts( 'admin' );
		self::enqueue_styles( 'admin' );
	}

	public static function scripts_front()
	{
		$setting        = Setting::get_instance();
		$is_marketplace = $setting->marketplace_options_type;

		if ( $is_marketplace == 'wcfm_marketplace' ) {
			self::enqueue_styles( 'front' );
		}

		if ( ! is_checkout() ) {
			return;
		}

		self::enqueue_styles( 'front' );

		if ( $setting->is_checkout_default() ) {
			self::enqueue_scripts(
				'front',
				[ 'jquery-payment' ],
				self::tc_script_localize_args()
			);

			return;
		}

		if ( $setting->is_checkout_transparent() ) {
			self::enqueue_scripts(
				'front',
				[ 'jquery-payment' ],
				self::tc_script_localize_args()
			);

			return;
		}

		self::enqueue_scripts( 'front' );
	}

	public static function tc_script_localize_args()
	{
		return [
			'messages' => [
				'processingTitle'              => __( 'Waiting...', 'woo-moip-official' ),
				'processingTextWaiting'        => __( 'The transaction is being processed...', 'woo-moip-official' ),
				'processingTextCompleted'      => __( 'Your transaction was processed successfully.', 'woo-moip-official' ),
				'failDefaultText'              => __( 'An error occurred while processing. <p>Try again.</p>', 'woo-moip-official' ),
				'failDefaultTextCreditCard'    => __( 'Invalid credit card. <p>Check all fields e try again.</p>', 'woo-moip-official' ),
				'failRequiredFieldsCreditCard' => __( 'Invalid credit card. <p>Complete all required fields.</p>', 'woo-moip-official' ),
			],
		];
	}

	public static function enqueue_scripts( $type, $deps = [], $localize_args = [] )
	{
		$setting = Setting::get_instance();
		$id      = "{$type}-script-" . self::SLUG;
		$footer  = false;

		if ( $setting->is_checkout_transparent() ) {
			$footer = true;
		}

		wp_enqueue_script(
			$id,
			self::plugins_url( "assets/javascripts/{$type}/built.js" ),
			array_merge( [ 'jquery' ], $deps ),
			self::filemtime( "assets/javascripts/{$type}/built.js" ),
			$footer
		);

		wp_localize_script(
			$id,
			self::LOCALIZE_SCRIPT_ID,
			self::get_localize_script_args( $localize_args )
		);
	}

	public static function enqueue_styles( $type )
	{
		wp_enqueue_style(
			"{$type}-style-" . self::SLUG,
			self::plugins_url( "assets/stylesheets/{$type}/style.css" ),
			[],
			self::filemtime( "assets/stylesheets/{$type}/style.css" )
		);
	}

	public static function get_name()
	{
		return __( 'Pagamento Moip for WooCommerce', 'woo-moip-official' );
	}

	public static function plugin_dir_path( $path = '' )
	{
		return plugin_dir_path( APIKI_MOIP_ROOT_FILE ) . $path;
	}

	public static function plugin_rel_path( $path )
	{
		return dirname( self::plugin_basename() ) . '/' . $path;
	}

	/**
	 * Plugin file root path
	 *
	 * @since 1.0
	 * @param String $file
	 * @return String
	 */
	public static function get_file_path( $file, $path = '' ) {
		return self::plugin_dir_path( $path ) . $file;
	}

	public static function plugins_url( $path )
	{
		return esc_url( plugins_url( $path, APIKI_MOIP_ROOT_FILE ) );
	}

	public static function filemtime( $path )
	{
		$file = self::plugin_dir_path( $path );

		return file_exists( $file ) ? filemtime( $file ) : APIKI_MOIP_VERSION;
	}

	public static function get_page_link()
	{
		return Utils::get_admin_url( 'admin.php' ) . '?page=wc-settings&tab=checkout&section=' . self::SLUG;
    }

    public static function support_link()
	{
		return esc_url( 'https://apiki.com/parceiros/moip/' );
    }

    public static function wc_wordpress_link()
	{
		return esc_url( 'https://wordpress.org/support/plugin/woo-moip-official/reviews/' );
	}

	public static function documentation_link()
	{
		return esc_url( 'https://blog.apiki.com/e-commerce/' );
    }

	public static function tag_name( $name = '' )
	{
		return sprintf( 'apiki_%s_%s', self::PREFIX, str_replace( '-', '_', $name ) );
	}

	/**
	 * Plugin base name
	 *
	 * @since 1.0
	 * @param String $filter
	 * @return String
	 */
	public static function plugin_basename( $filter = '' )
	{
		return $filter . plugin_basename( APIKI_MOIP_ROOT_FILE );
	}

	public static function get_webhook_url()
	{
		return sprintf( '%s/wc-api/%s/?token=%s', Utils::get_site_url(), self::get_webhook_name(), Setting::get_instance()->hash_token );
	}

	public static function get_webhook_name()
	{
		return Utils::add_prefix( '-webhook' );
	}

	public static function instance()
	{
		if ( is_null( self::$_instance ) ) :
			self::$_instance = new self;
		endif;
	}
}
