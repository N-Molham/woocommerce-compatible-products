<?php namespace WooCommerce\Compatible_Products;

use WC_Cart;
use WC_Product;
use WC_Session;

/**
 * Products logic
 *
 * @package WooCommerce\Compatible_Products
 */
class Products extends Component
{
	/**
	 * Assembly configurations session store key name
	 *
	 * @var string
	 */
	protected $assembly_configs_session_key = 'wc_cp_assembly_configs';

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
	 * List of registered assembly configurations
	 *
	 * @var array
	 */
	protected $assembly_configurations;

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
		add_action( 'woocommerce_init', [ &$this, 'setup_assembly_configurations' ] );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_GET['wc_cp_request'] ) )
		{
			// filter searched products result
			add_filter( 'woocommerce_json_search_found_products', [ &$this, 'json_replace_ids_with_skus' ] );
		}
	}

	/**
	 * Setup registered assembly configurations information
	 *
	 * @return void
	 */
	public function setup_assembly_configurations()
	{
		// default value
		$this->assembly_configurations = [ ];
		if ( array_key_exists( $this->assembly_configs_session_key, $_SESSION ) )
		{
			// set the current value
			$this->assembly_configurations = $_SESSION[ $this->assembly_configs_session_key ];
		}
	}

	/**
	 * Replace IDs with SKUs in searched products result
	 *
	 * @param array $products
	 *
	 * @return array
	 */
	public function json_replace_ids_with_skus( $products )
	{
		$allowed_categories = array_map( 'intval', get_option( 'wc_cp_category_filter', [ ] ) );

		foreach ( $products as $product_id => $product_name )
		{
			if ( null !== $allowed_categories )
			{
				$parent_product_id = wp_get_post_parent_id( $product_id );

				// get product categories
				$product_cats = wp_get_post_terms( $parent_product_id ? $parent_product_id : $product_id, 'product_cat', [ 'fields' => 'ids' ] );
				if ( 0 === count( $product_cats ) || 0 === count( array_intersect( $product_cats, $allowed_categories ) ) )
				{
					// product not allowed, remove from list
					unset( $products[ $product_id ] );
					continue;
				}
			}

			// replace product ID with SKU
			$products[ $this->get_product_sku_by_id( $product_id ) ] = $product_name;
			unset( $products[ $product_id ] );
		}

		return $products;
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
			if ( !isset( $cart_item['variation_id'] ) || $cart_item['variation_id'] < 1 )
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
	 * Check if cart has assembly fees or not
	 *
	 * @param WC_Cart $cart
	 *
	 * @return bool
	 */
	public function cart_has_assembly_fee( &$cart )
	{
		$fees = $cart->get_fees();
		if ( empty( $fees ) )
		{
			// cart has no fees at all
			return false;
		}

		$assembly_fees = $this->calculate_assembly_fee( $cart );
		if ( false === $assembly_fees )
		{
			// skip as forced to ignore the assembly fees
			return false;
		}

		foreach ( $fees as $fee )
		{
			if ( 'assembly-fees' === $fee->id && $assembly_fees === $fee->amount )
			{
				// found it
				return true;
			}
		}

		return false;
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
	 * Get measuring instructions set by admin
	 *
	 * @return string|boolean
	 */
	public function get_measuring_instructions()
	{
		return get_option( 'wc_cp_measure_instructions', false );
	}

	/**
	 * Set product compatible products SKUs
	 *
	 * @param int          $variation_id
	 * @param array|string $compatible_products
	 *
	 * @return void
	 */
	public function set_product_compatible_products( $variation_id, $compatible_products )
	{
		// array or string
		$compatible_products = is_array( $compatible_products ) ? implode( '|', $compatible_products ) : str_replace( ',', '|', $compatible_products );

		update_post_meta( $variation_id, $this->compatible_products_key, $compatible_products );
	}

	/**
	 * Get product compatible products SKUs
	 *
	 * @param int $variation_id
	 *
	 * @return array
	 */
	public function get_product_compatible_products( $variation_id )
	{
		return array_filter( explode( '|', get_post_meta( $variation_id, $this->compatible_products_key, true ) ) );
	}

	/**
	 * Get product SKU by ID.
	 *
	 * @param int $product_id
	 *
	 * @return string
	 */
	function get_product_sku_by_id( $product_id )
	{
		return get_post_meta( $product_id, '_sku', true );
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
		$products_skus = $this->get_product_compatible_products( $variation_id );
		$products_list = [ ];
		$product_info  = [ ];
		$product       = null;

		foreach ( $products_skus as $compatible_sku )
		{
			// get product ID from SKU
			$compatible_id = wc_get_product_id_by_sku( $compatible_sku );

			if ( 0 === $compatible_id )
			{
				// skip as sku not related to any product
				continue;
			}

			// query product info
			$product = wc_get_product( $compatible_id );

			if ( false === $product )
			{
				// skip as product not found
				continue;
			}

			// basic info
			$product_info = [
				'id'   => $product->get_sku(),
				'text' => $full_info ? $product->get_title() : $product->get_formatted_name(),
			];

			if ( $full_info )
			{
				// detailed information
				$product_info['product_id']       = $product->id;
				$product_info['sku']              = $product->get_sku();
				$product_info['price']            = $product->get_price();
				$product_info['price_formatted']  = $product->get_price_html();
				$product_info['image']            = $product->get_image();
				$product_info['image_src']        = get_the_post_thumbnail_url( $product->get_image_id(), 'shop_thumbnail' );
				$product_info['product_link']     = $product->get_permalink();
				$product_info['add_to_cart_link'] = $product->add_to_cart_url();
				$product_info['stock_quantity']   = $product->get_stock_quantity();
				$product_info['wc_object']        = $product;
				$product_info['is_variation']     = $this->is_product_variation( $product );

				if ( $product_info['is_variation'] )
				{
					// variation product information
					$product_info['variation_id'] = $product->variation_id;
				}
			}

			$products_list[] = $product_info;
		}
		unset( $product_info, $compatible_id, $compatible_sku );

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

	/**
	 * Check if given product is a another product variation
	 *
	 * @param WC_Product $product
	 *
	 * @return bool
	 */
	public function is_product_variation( $product )
	{
		return 'variation' === $product->get_type() || 'WC_Product_Variation' === get_class( $product );
	}

	/**
	 * Get assembly configuration by it's key
	 *
	 * @param string|null $assembly_key
	 * @param int|null    $product_id
	 *
	 * @return array|bool false if assembly not set
	 */
	public function get_assembly_configuration( $assembly_key = null, $product_id = null )
	{
		// all registered configs
		$configs       = $this->get_assembly_configurations();
		$target_config = null;

		if ( null !== $assembly_key )
		{
			if ( !array_key_exists( $assembly_key, $configs ) )
			{
				// not found
				return false;
			}

			// get configuration by key
			$target_config = $configs[ $assembly_key ];
		}

		if ( null !== $product_id )
		{
			// get configuration by main product id
			foreach ( $configs as $config_key => $config_info )
			{
				if ( !is_array( $config_info ) || !isset( $config_info['product_id'] ) )
				{
					// skip empty configs
					continue;
				}

				if ( $product_id === $config_info['product_id'] )
				{
					// config found
					$assembly_key  = $config_key;
					$target_config = $config_info;
					break;
				}
			}
			unset( $config_key, $config_info );

			if ( null === $target_config )
			{
				// not found
				return false;
			}
		}

		if ( null === $target_config )
		{
			// get the last generated key with no configs yet
			$configs_reversed = array_reverse( $configs );
			foreach ( $configs_reversed as $config_key => $config_data )
			{
				if ( !array_key_exists( 'parts', $config_data ) )
				{
					// use this one as it's the last generated one with no data attached yet
					$assembly_key  = $config_key;
					$target_config = $config_data;
					break;
				}
			}
		}

		if ( null !== $target_config )
		{
			if ( !isset( $target_config['quantity'] ) )
			{
				// default main product quantity
				$target_config['quantity'] = 1;
			}

			// config found
			return array_merge( [ 'key' => $assembly_key ], $target_config );
		}

		// no configuration found
		return false;
	}

	/**
	 * Generate new configuration key
	 *
	 * @param int $product_id
	 *
	 * @return string
	 */
	public function create_new_assembly_configuration( $product_id )
	{
		if ( null === $this->assembly_configurations )
		{
			// initialize configs if not yet
			$this->setup_assembly_configurations();
		}

		// vars
		$assembly_key     = '';
		$configs          = $this->get_assembly_configurations();
		$configs_reversed = array_reverse( $configs );

		// get the last generated key with no configs yet
		foreach ( $configs_reversed as $config_key => $config_data )
		{
			if ( null === $config_data )
			{
				// use this one as it's the last generated one with no data attached yet
				$assembly_key = $config_key;
				break;
			}
		}

		if ( '' === $assembly_key )
		{
			// generate new one
			$assembly_key = md5( uniqid() );
		}

		$new_config = [
			'product_id' => $product_id,
			'quantity'   => 1,
		];

		$this->save_assembly_configuration( $assembly_key, $new_config );

		return array_merge( [ 'key' => $assembly_key ], $new_config );
	}

	/**
	 * Get available/registered configurations
	 *
	 * @return array
	 */
	public function &get_assembly_configurations()
	{
		if ( null === $this->assembly_configurations )
		{
			// initialize configs if not yet
			$this->setup_assembly_configurations();
		}

		return $this->assembly_configurations;
	}

	/**
	 * Save assembly configuration
	 *
	 * @param string     $assembly_key
	 * @param array|null $assembly_config
	 *
	 * @return void
	 */
	public function save_assembly_configuration( $assembly_key, $assembly_config = null )
	{
		if ( null === $this->assembly_configurations )
		{
			// initialize configs if not yet
			$this->setup_assembly_configurations();
		}

		$this->assembly_configurations[ $assembly_key ] = $assembly_config;

		$_SESSION[ $this->assembly_configs_session_key ] = $this->assembly_configurations;
	}

	/**
	 * Update/add item to assembly configuration
	 *
	 * @param array $assembly_config
	 * @param array $new_part
	 *
	 * @return array
	 */
	public function add_assembly_configuration_item( $assembly_config, $new_part )
	{
		if ( is_string( $assembly_config ) )
		{
			// get assembly info
			$assembly_config = $this->get_assembly_configuration( $assembly_config );
		}

		if ( !isset( $assembly_config['parts'] ) )
		{
			// parts container
			$assembly_config['parts'] = [ ];
		}

		$duplicated = false;
		foreach ( $assembly_config['parts'] as $part_index => &$part_info )
		{
			// look for item to update instead of adding as new
			if ( $part_info['product_id'] === $new_part['product_id'] && $part_info['variation_id'] === $new_part['variation_id'] )
			{
				$duplicated = true;
				$new_part['quantity'] += $part_info['quantity'];
				$part_info = $new_part;
				break;
			}
		}

		if ( false === $duplicated )
		{
			// add new item
			$assembly_config['parts'][] = $new_part;
		}

		// save update
		$this->save_assembly_configuration( $assembly_config['key'], $assembly_config );

		// return the updated one
		return $assembly_config;
	}

	/**
	 * Remove item from assembly configuration
	 *
	 * @param array $assembly_config
	 * @param int   $product_id
	 * @param int   $variation_id
	 *
	 * @return array
	 */
	public function remove_assembly_configuration_item( $assembly_config, $product_id, $variation_id )
	{
		if ( is_string( $assembly_config ) )
		{
			// get assembly info
			$assembly_config = $this->get_assembly_configuration( $assembly_config );
		}

		if ( !isset( $assembly_config['parts'] ) )
		{
			// parts container
			$assembly_config['parts'] = [ ];
		}

		foreach ( $assembly_config['parts'] as $part_index => $part_info )
		{
			// look for item to update instead of adding as new
			if ( $part_info['product_id'] === $product_id && $part_info['variation_id'] === $variation_id )
			{
				unset( $assembly_config['parts'][ $part_index ] );
				break;
			}
		}

		//
		$assembly_config['parts'] = array_values( $assembly_config['parts'] );

		// save update
		$this->save_assembly_configuration( $assembly_config['key'], $assembly_config );

		// return the updated one
		return $assembly_config;
	}

	/**
	 * Update assembly configuration main item quantity
	 *
	 * @param array $assembly_config
	 * @param float $quantity
	 *
	 * @return array
	 */
	public function update_assembly_configuration_quantity( $assembly_config, $quantity )
	{
		if ( is_string( $assembly_config ) )
		{
			// get assembly info
			$assembly_config = $this->get_assembly_configuration( $assembly_config );
		}

		$assembly_config['quantity'] = $quantity;

		// save update
		$this->save_assembly_configuration( $assembly_config['key'], $assembly_config );

		// return the updated one
		return $assembly_config;
	}
}
