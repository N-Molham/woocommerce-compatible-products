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
		'add_compatible_product_to_cart' => 'both',
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
	 * Add compatible product into cart
	 *
	 * @return
	 */
	public function add_compatible_product_to_cart()
	{
		// security check
		check_ajax_referer( 'wc_cp_add_to_cart', 'security' );

		// request args
		$product_id   = absint( filter_input( INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT ) );
		$variation_id = absint( filter_input( INPUT_POST, 'variation_id', FILTER_SANITIZE_NUMBER_INT ) );

		// product attributes
		$attributes = [ ];
		foreach ( $_POST as $arg_name => $arg_value )
		{
			if ( 0 === strpos( $arg_name, 'attribute_' ) )
			{
				$attributes[ $arg_name ] = $arg_value;
			}
		}

		if ( empty( $product_id ) || false === wc_get_product( $product_id ) )
		{
			// invalid product
			$this->error( __( 'Unknown product!', 'woocommerce' ) );
		}

		// add to cart
		$added_to_cart = WC()->cart->add_to_cart( $product_id, 1, $variation_id, $attributes );

		if ( $added_to_cart && wc_notice_count( 'error' ) === 0 )
		{
			// success
			WC_AJAX::get_refreshed_fragments();
		}

		// error response
		$this->error( implode( "\n", wc_get_notices( 'error' ) ) );
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
