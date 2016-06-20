<?php namespace WooCommerce\Compatible_Products;

use WC_AJAX;

/**
 * AJAX handler
 *
 * @package WooCommerce\Compatible_Products
 */
class Ajax_Handler extends Component
{
	/**
	 * List of public action which don't require a logged in user
	 *
	 * @var array
	 */
	protected $public_actions = [
		'add_compatible_product_to_cart'          => 'both',
		'add_compatible_product_to_assembly'      => 'both',
		'remove_compatible_product_from_assembly' => 'both',
	];

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init()
	{
		parent::init();

		/**
		 * Filters AJAX public actions
		 *
		 * @param array $public_actions
		 *
		 * @return array
		 */
		$this->public_actions = apply_filters( 'wc_cp_ajax_public_actions', $this->public_actions );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
		{
			$action = filter_var( isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '', FILTER_SANITIZE_STRING );
			if ( method_exists( $this, $action ) )
			{
				$privilege_action = true;
				if ( isset( $this->public_actions[ $action ] ) )
				{
					// hook into non privilege action
					add_action( 'wp_ajax_nopriv_' . $action, [ &$this, $action ] );

					// if action will trigger for privilege users also or not
					$privilege_action = 'both' === $this->public_actions[ $action ];
				}

				if ( $privilege_action )
				{
					// hook into privilege action
					add_action( 'wp_ajax_' . $action, [ &$this, $action ] );
				}
			}
		}
	}

	/**
	 * Update assembly main product item quantity
	 *
	 * @return void
	 */
	public function update_assembly_amount()
	{
		// security check
		check_ajax_referer( 'wc_cp_update_assembly_main_product_quantity', 'security' );

		// request args
		$quantity     = abs( filter_input( INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT ) );
		$assembly_key = sanitize_key( filter_input( INPUT_POST, 'assembly_key', FILTER_SANITIZE_STRING ) );

		// assembly configuration
		$assembly_config = wc_cp_products()->get_assembly_configuration( $assembly_key );
		if ( false === $assembly_config )
		{
			// invalid config
			$this->error( __( 'Invalid target assembly configuration!', WC_CP_DOMAIN ) );
		}

		// update configuration and return the new version
		$this->success( wc_cp_products()->update_assembly_configuration_quantity( $assembly_config, $quantity ) );
	}

	/**
	 * Remove assembly item from configuration
	 *
	 * @return void
	 */
	public function remove_compatible_product_from_assembly()
	{
		// security check
		check_ajax_referer( 'wc_cp_remove_assembly_item', 'security' );

		// request args
		$product_id   = absint( filter_input( INPUT_POST, 'pid', FILTER_SANITIZE_NUMBER_INT ) );
		$variation_id = absint( filter_input( INPUT_POST, 'vid', FILTER_SANITIZE_NUMBER_INT ) );
		$assembly_key = sanitize_key( filter_input( INPUT_POST, 'assembly_key', FILTER_SANITIZE_STRING ) );

		// assembly configuration
		$assembly_config = wc_cp_products()->get_assembly_configuration( $assembly_key );
		if ( false === $assembly_config )
		{
			// invalid config
			$this->error( __( 'Invalid target assembly configuration!', WC_CP_DOMAIN ) );
		}

		// update configuration and return the new version
		$this->success( wc_cp_products()->remove_assembly_configuration_item( $assembly_config, $product_id, $variation_id ) );
	}

	/**
	 * Add compatible product into cart
	 *
	 * @return
	 */
	public function add_compatible_product_to_assembly()
	{
		// security check
		check_ajax_referer( 'wc_cp_add_to_assembly', 'security' );

		// request args
		$product_id   = absint( filter_input( INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT ) );
		$variation_id = absint( filter_input( INPUT_POST, 'variation_id', FILTER_SANITIZE_NUMBER_INT ) );
		$quantity     = absint( filter_input( INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT ) );
		$assembly_key = sanitize_key( filter_input( INPUT_POST, 'assembly_key', FILTER_SANITIZE_STRING ) );

		// assembly configuration
		$assembly_config = wc_cp_products()->get_assembly_configuration( $assembly_key );
		if ( false === $assembly_config )
		{
			// invalid config
			$this->error( __( 'Invalid target assembly configuration!', WC_CP_DOMAIN ) );
		}

		if ( 0 === $quantity )
		{
			// fallback
			$quantity = 1;
		}

		// product attributes
		$attributes = [ ];
		foreach ( $_POST as $arg_name => $arg_value )
		{
			if ( 0 === strpos( $arg_name, 'attribute_' ) )
			{
				$attributes[ $arg_name ] = $arg_value;
			}
		}

		if ( empty( $product_id ) || empty( $variation_id ) )
		{
			// invalid product
			$this->error( __( 'Unknown product!', WC_CP_DOMAIN ) );
		}

		// product validation
		$product = wc_get_product( $variation_id ? $variation_id : $product_id );
		if ( false === $product || ( wc_cp_products()->is_product_variation( $product ) && $product_id !== $product->id ) )
		{
			// invalid product information
			$this->error( __( 'Invalid product!', WC_CP_DOMAIN ) );
		}

		if ( false === $product->has_enough_stock( $quantity ) )
		{
			// stock is not enough to cover it
			$this->error( __( 'Stock os not enough!', WC_CP_DOMAIN ) );
		}

		// save changes
		$assembly_config = wc_cp_products()->add_assembly_configuration_item( $assembly_config, compact( 'product_id', 'variation_id', 'quantity', 'attributes' ) );

		// success response
		$this->success( $assembly_config );
	}

	/**
	 * Compatible products search handler
	 *
	 * @return void
	 */
	public function search_compatible_products()
	{
		WC_AJAX::json_search_products_and_variations();
	}

	/**
	 * AJAX Debug response
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $data
	 *
	 * @return void
	 */
	public function debug( $data )
	{
		// return dump
		$this->error( $data );
	}

	/**
	 * AJAX Debug response ( dump )
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $args
	 *
	 * @return void
	 */
	public function dump( $args )
	{
		// return dump
		$this->error( print_r( func_num_args() === 1 ? $args : func_get_args(), true ) );
	}

	/**
	 * AJAX Error response
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $data
	 *
	 * @return void
	 */
	public function error( $data )
	{
		wp_send_json_error( $data );
	}

	/**
	 * AJAX success response
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $data
	 *
	 * @return void
	 */
	public function success( $data )
	{
		wp_send_json_success( $data );
	}

	/**
	 * AJAX JSON Response
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $response
	 *
	 * @return void
	 */
	public function response( $response )
	{
		// send response
		wp_send_json( $response );
	}
}
