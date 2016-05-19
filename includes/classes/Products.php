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
	 * @param int $variation_id
	 *
	 * @return array
	 */
	public function get_product_compatible_products( $variation_id )
	{
		return array_filter( get_post_meta( $variation_id, $this->compatible_products_key ) );
	}

	/**
	 * Get product compatible products list
	 *
	 * @param int  $variation_id
	 * @param bool $full_info
	 *
	 * @return array
	 */
	public function get_product_compatible_products_list( $variation_id, $full_info = false )
	{
		// vars
		$products_ids  = $this->get_product_compatible_products( $variation_id );
		$products_list = [ ];
		$product_info  = [ ];
		$product       = null;

		foreach ( $products_ids as $compatible_pid )
		{
			$product = wc_get_product( $compatible_pid );

			// basic info
			$product_info = [
				'id'   => $compatible_pid,
				'text' => $product->get_formatted_name(),
			];

			if ( $full_info )
			{
				// detailed information
				$product_info['product_id']       = $product->id;
				$product_info['price']            = $product->get_price();
				$product_info['price_formatted']  = $product->get_price_html();
				$product_info['image']            = $product->get_image();
				$product_info['image_src']        = get_the_post_thumbnail_url( $product->get_image_id(), 'shop_thumbnail' );
				$product_info['product_link']     = $product->get_permalink();
				$product_info['add_to_cart_link'] = $product->add_to_cart_url();
			}

			$products_list[] = $product_info;
		}
		unset( $product_info, $compatible_pid, $product );

		return $products_list;
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
