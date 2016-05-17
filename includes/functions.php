<?php
/**
 * Created by Nabeel
 * Date: 2016-01-22
 * Time: 2:38 AM
 *
 * @package WooCommerce\Compatible_Products
 */

use WooCommerce\Compatible_Products\Plugin;
use WooCommerce\Compatible_Products\Products;

if ( !function_exists( 'wc_compatible_products' ) ):
	/**
	 * Get plugin instance
	 *
	 * @return Plugin
	 */
	function wc_compatible_products()
	{
		return Plugin::get_instance();
	}
endif;

if ( !function_exists( 'wc_cp_view' ) ):
	/**
	 * Load view
	 *
	 * @param string  $view_name
	 * @param array   $args
	 * @param boolean $return
	 *
	 * @return void
	 */
	function wc_cp_view( $view_name, $args = null, $return = false )
	{
		if ( $return )
		{
			// start buffer
			ob_start();
		}

		wc_compatible_products()->load_view( $view_name, $args );

		if ( $return )
		{
			// get buffer flush
			return ob_get_clean();
		}
	}
endif;

if ( !function_exists( 'wc_cp_version' ) ):
	/**
	 * Get plugin version
	 *
	 * @return string
	 */
	function wc_cp_version()
	{
		return wc_compatible_products()->version;
	}
endif;