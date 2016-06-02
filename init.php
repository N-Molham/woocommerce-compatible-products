<?php namespace WooCommerce\Compatible_Products;

/**
 * Plugin Name: WooCommerce Compatible Products and Measurements
 * Description: Integrates WooCommerce Measurement Price Calculator plugin with compatible products
 * Version: 1.3.4
 * Author: Nabeel Molham
 * Author URI: http://nabeel.molham.me/
 * Text Domain: wc-compatible-products
 * Domain Path: /languages
 * License: GNU General Public License, version 3, http://www.gnu.org/licenses/gpl-3.0.en.html
 */

if ( !defined( 'WPINC' ) )
{
	// Exit if accessed directly
	die();
}

/**
 * Constants
 */

// plugin master file
define( 'WC_CP_MAIN_FILE', __FILE__ );

// plugin DIR
define( 'WC_CP_DIR', plugin_dir_path( WC_CP_MAIN_FILE ) );

// plugin URI
define( 'WC_CP_URI', plugin_dir_url( WC_CP_MAIN_FILE ) );

// localization text Domain
define( 'WC_CP_DOMAIN', 'wc-compatible-products' );

require_once WC_CP_DIR . 'includes/classes/Singular.php';
require_once WC_CP_DIR . 'includes/helpers.php';
require_once WC_CP_DIR . 'includes/functions.php';

/**
 * Plugin main component
 *
 * @package WooCommerce\Compatible_Products
 */
class Plugin extends Singular
{
	/**
	 * Plugin version
	 *
	 * @var string
	 */
	var $version;

	/**
	 * Backend
	 *
	 * @var Backend
	 */
	var $backend;

	/**
	 * Backend
	 *
	 * @var Frontend
	 */
	var $frontend;

	/**
	 * Backend
	 *
	 * @var Ajax_Handler
	 */
	var $ajax;

	/**
	 * Products
	 *
	 * @var Products
	 */
	var $products;

	/**
	 * Plugin header info
	 *
	 * @var array
	 */
	var $plugin_data;

	/**
	 * Initialization
	 *
	 * @return void
	 */
	protected function init()
	{
		// load language files
		add_action( 'plugins_loaded', [ &$this, 'load_language' ] );

		// plugin data
		$this->plugin_data = get_plugin_data( WC_CP_MAIN_FILE );
		$this->version     = $this->plugin_data['Version'];

		// autoloader register
		spl_autoload_register( [ &$this, 'autoloader' ] );

		// Plugins dependencies check
		if (
			Helpers::is_plugin_inactive( 'woocommerce/woocommerce.php' ) ||
			Helpers::is_plugin_inactive( 'woocommerce-measurement-price-calculator/woocommerce-measurement-price-calculator.php' )
		)
		{
			// do nothing as the required plugin(s) not installed/active
			return;
		}

		// modules
		$this->products = Products::get_instance();
		$this->ajax     = Ajax_Handler::get_instance();
		$this->backend  = Backend::get_instance();
		$this->frontend = Frontend::get_instance();

		// plugin loaded hook
		do_action_ref_array( 'wc_cp_loaded', [ &$this ] );
	}

	/**
	 * Get Cookie saved data
	 *
	 * @return array|bool
	 */
	public function get_cookie_data()
	{
		if ( !isset( $_COOKIE['wc-cp-amounts'] ) )
		{
			// skip
			return false;
		}

		// load cookie info
		$new_amounts = json_decode( wp_kses_stripslashes( $_COOKIE['wc-cp-amounts'] ), true );
		if ( !is_array( $new_amounts ) )
		{
			// skip
			return false;
		}

		return $new_amounts;
	}

	/**
	 * Load view template
	 *
	 * @param string $view_name
	 * @param array  $args ( optional )
	 *
	 * @return void
	 */
	public function load_view( $view_name, $args = null )
	{
		// build view file path
		$__view_name     = $view_name;
		$__template_path = WC_CP_DIR . 'views/' . $__view_name . '.php';
		if ( !file_exists( $__template_path ) )
		{
			// file not found!
			wp_die( sprintf( __( 'Template <code>%s</code> File not found, calculated path: <code>%s</code>', WC_CP_DOMAIN ), $__view_name, $__template_path ) );
		}

		// clear vars
		unset( $view_name );

		if ( !empty( $args ) )
		{
			// extract passed args into variables
			extract( $args, EXTR_OVERWRITE );
		}

		/**
		 * Before loading template hook
		 *
		 * @param string $__template_path
		 * @param string $__view_name
		 */
		do_action_ref_array( 'wc_cp_load_template_before', [ &$__template_path, $__view_name, $args ] );

		/**
		 * Loading template file path filter
		 *
		 * @param string $__template_path
		 * @param string $__view_name
		 *
		 * @return string
		 */
		require apply_filters( 'wc_cp_load_template_path', $__template_path, $__view_name, $args );

		/**
		 * After loading template hook
		 *
		 * @param string $__template_path
		 * @param string $__view_name
		 */
		do_action( 'wc_cp_load_template_after', $__template_path, $__view_name, $args );
	}

	/**
	 * Language file loading
	 *
	 * @return void
	 */
	public function load_language()
	{
		load_plugin_textdomain( WC_CP_DOMAIN, false, dirname( plugin_basename( WC_CP_MAIN_FILE ) ) . '/languages' );
	}

	/**
	 * System classes loader
	 *
	 * @param $class_name
	 *
	 * @return void
	 */
	public function autoloader( $class_name )
	{
		if ( strpos( $class_name, __NAMESPACE__ ) === false )
		{
			// skip non related classes
			return;
		}

		$class_path = WC_CP_DIR . 'includes' . DIRECTORY_SEPARATOR . 'classes' . str_replace( [
				__NAMESPACE__,
				'\\',
			], [ '', DIRECTORY_SEPARATOR ], $class_name ) . '.php';

		if ( file_exists( $class_path ) )
		{
			// load class file if found
			require_once $class_path;
		}
	}
}

// boot up the system
wc_compatible_products();