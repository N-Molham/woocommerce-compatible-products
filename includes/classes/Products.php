<?php namespace WooCommerce\Compatible_Products;

use WC_Price_Calculator_Measurement;
use WC_Price_Calculator_Product;
use WC_Price_Calculator_Settings;
use WC_Product;
use WC_Product_Composite;

/**
 * Products logic
 *
 * @package WooCommerce\Compatible_Products
 */
class Products extends Component
{
	/**
	 * Compatible products field id and name
	 *
	 * @var string
	 */
	protected $compatible_products_key = '_wc_cp_products';

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init()
	{
		parent::init();
	}

	/**
	 * Get product compatible products IDs
	 *
	 * @param int $product_id
	 *
	 * @return array
	 */
	public function get_product_compatible_products( $product_id )
	{
		return array_filter( get_post_meta( $product_id, $this->compatible_products_key ) );
	}

	/**
	 * Get compatible products meta key
	 *
	 * @return string
	 */
	public function get_compatible_products_key()
	{
		return $this->compatible_products_key;
	}
}
