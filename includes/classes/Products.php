<?php namespace WooCommerce\Compatible_Products;

use WC_Cart;
use WC_Price_Calculator_Measurement;
use WC_Price_Calculator_Product;
use WC_Price_Calculator_Settings;
use WC_Product;
use WC_Product_Composite;
use WC_Session;

/**
 * Products logic
 *
 * @package WooCommerce\Compatible_Products
 */
class Products extends Component
{
	/**
	 * Compatible products assembly fees percentage
	 *
	 * @var float
	 */
	protected $assembly_fees_percentage = 0.3;

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

		// WooCommerce order created and processed
		add_action( 'woocommerce_checkout_order_processed', [ &$this, 'clear_assembly_fees_session' ] );

		// WooCommerce cart fees calculation hook
		add_action( 'woocommerce_cart_calculate_fees', [ &$this, 'add_assembly_fees_to_cart' ] );

		// WooCommerce initialized
		add_action( 'woocommerce_init', [ &$this, 'mark_cart_to_have_assembly_fees' ] );
		add_action( 'woocommerce_init', [ &$this, 'remove_assembly_fees_from_cart' ] );
	}

	/**
	 * remove cart assembly fees
	 *
	 * @return void
	 */
	public function remove_assembly_fees_from_cart()
	{
		if ( 'yes' === filter_input( INPUT_GET, 'wc_cp_remove_fee', FILTER_SANITIZE_STRING ) && true === WC()->session->get( 'wc_cp_add_assembly_fees' ) )
		{
			// update session
			unset( WC()->session->wc_cp_add_assembly_fees );

			// reload cart page
			wp_redirect( remove_query_arg( 'wc_cp_remove_fee' ) );
			die();
		}
	}

	/**
	 * Setup cart to have assembly fees added
	 *
	 * @return void
	 */
	public function mark_cart_to_have_assembly_fees()
	{
		if ( 'yes' === filter_input( INPUT_GET, 'wc_cp_add_assembly', FILTER_SANITIZE_STRING ) && true === WC()->session->get( 'wc_cp_in_cart' ) )
		{
			// update session
			WC()->session->set( 'wc_cp_add_assembly_fees', true );

			// reload page
			wp_redirect( remove_query_arg( 'wc_cp_add_assembly' ) );
			die();
		}
	}

	/**
	 * Add assembly fees to the cart
	 *
	 * @param WC_Cart $cart
	 *
	 * @return void
	 */
	public function add_assembly_fees_to_cart( $cart )
	{
		if ( null === WC()->session->get( 'wc_cp_add_assembly_fees' ) )
		{
			// skip as no need to add the fees
			return;
		}

		$cart->add_fee( 'Assembly Fees', $this->calculate_assembly_fee( $cart ), true );
	}

	/**
	 * Clear session from unwanted keys
	 *
	 * @return void
	 */
	public function clear_assembly_fees_session()
	{
		unset( WC()->session->wc_cp_in_cart );
		unset( WC()->session->wc_cp_add_assembly_fees );
	}

	/**
	 * Setup assembly fees for the cart
	 *
	 * @param WC_Cart    $cart
	 * @param WC_Session $session
	 *
	 * @return float|false
	 */
	public function setup_cart_assembly_fees( &$cart, &$session )
	{
		// holders
		$cart_has_cp = false;

		if ( true === $session->get( 'wc_cp_add_assembly_fees' ) )
		{
			// fees already marked to be added
			return false;
		}

		if ( $cart->is_empty() )
		{
			// skip as the cart is empty
			return false;
		}

		// look into the cart for product variation with compatible products
		foreach ( $cart->get_cart() as $cart_item_key => $cart_item )
		{
			if ( !isset( $cart_item['variation_id'] ) )
			{
				// skip as the item is not a product variation
				continue;
			}

			$cp_list = $this->get_product_compatible_products( $cart_item['variation_id'] );
			if ( count( $cp_list ) > 0 )
			{
				// break out as the target found
				$cart_has_cp = true;
				break;
			}
		}

		if ( false === $cart_has_cp )
		{
			// cart has no compatible product(s)
			return false;
		}

		if ( null === $session->get( 'wc_cp_in_cart' ) )
		{
			// mark session for handling later
			$session->set( 'wc_cp_in_cart', true );
		}

		$assembly_fees = wc_cp_products()->calculate_assembly_fee( $cart );
		if ( false === $assembly_fees )
		{
			// skip as forced to ignore the assembly fees
			return false;
		}

		return $assembly_fees;
	}

	/**
	 * Calculate compatible products assembly fees
	 *
	 * @param WC_Cart $cart
	 *
	 * @return float|false
	 */
	public function calculate_assembly_fee( &$cart )
	{
		/**
		 * Filter the calculated assembly fees
		 *
		 * @param float   $assembly_fees
		 * @param WC_Cart $cart
		 * @param float   $assembly_fees_percentage
		 *
		 * @return float|false
		 */
		return apply_filters_ref_array( 'wc_cp_assembly_fees', [
			$cart->subtotal_ex_tax * $this->assembly_fees_percentage,
			&$cart,
			$this->assembly_fees_percentage,
		] );
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


	/**
	 * Get compatible products assembly fees percentage
	 *
	 * @return float
	 */
	public function get_assembly_fees_percentage()
	{
		return $this->assembly_fees_percentage;
	}
}
