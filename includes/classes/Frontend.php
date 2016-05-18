<?php namespace WooCommerce\Compatible_Products;

use WC_Price_Calculator_Measurement;
use WC_Price_Calculator_Product;
use WC_Price_Calculator_Settings;
use WC_Product;
use WC_Product_Composite;

/**
 * Frontend logic
 *
 * @package WooCommerce\Compatible_Products
 */
class Frontend extends Component
{
	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init()
	{
		parent::init();

		add_action( 'wp_enqueue_scripts', [ &$this, 'enqueues' ] );
	}

	/**
	 * Enqueue JS & CSS assets
	 *
	 * @return void
	 */
	public function enqueues()
	{
		if ( !is_product() )
		{
			// skip for non-product page
			return;
		}

		// load main JS file
		wp_enqueue_script( 'wc-cp-compatible-products', WC_CP_URI . Helpers::enqueue_base_dir() . 'js/compatible-products.js', [ 'jquery' ], wc_compatible_products()->version, true );
	}
}
