<?php namespace WooCommerce\Compatible_Products;

use WC_Product_Variation;

/**
 * Frontend logic
 *
 * @package WooCommerce\Compatible_Products
 */
class Frontend extends Component
{
	/**
	 * Assembly fees WC notice type
	 *
	 * @var string
	 */
	protected $assembly_notice_type = 'assembly';

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init()
	{
		parent::init();

		// WooCommerce cart fee output filter
		add_filter( 'woocommerce_cart_totals_fee_html', [ &$this, 'add_assembly_fee_remove_button' ], 10, 2 );

		// before loading cart template
		add_action( 'woocommerce_check_cart_items', [ &$this, 'add_assembly_notice' ] );

		// load JS asset(s)
		add_action( 'wp_enqueue_scripts', [ &$this, 'enqueues' ] );

		// WooCommerce notices types
		add_filter( 'woocommerce_notice_types', [ &$this, 'add_assembly_notice_type' ] );

		// WooCommerce before order submit button
		add_action( 'woocommerce_review_order_before_submit', [ &$this, 'order_assembly_fees_confirm_checkbox' ] );

		// WooCommerce before checkout process
		add_action( 'woocommerce_before_checkout_process', [ &$this, 'checkout_is_assembly_term_checked' ] );

		// WooCommerce before product's add to cart button
		add_action( 'woocommerce_before_add_to_cart_button', [
			&$this,
			'product_fittings_measuring_instructions_modal',
		] );

		add_action( 'wp_footer', [ &$this, 'product_fittings_measuring_instructions_modal' ], PHP_INT_MAX );

		// Product variations data filter
		add_filter( 'woocommerce_available_variation', [ &$this, 'append_compatible_products_to_variation_data' ] );
	}

	/**
	 * Add measuring instructions modal to product page
	 *
	 * @return void
	 */
	public function product_fittings_measuring_instructions_modal()
	{
		if ( is_product() || is_checkout() )
		{
			echo $this->get_measuring_instructions();
		}
	}

	/**
	 * Check if assembly specifications is checked
	 *
	 * @return void
	 */
	public function checkout_is_assembly_term_checked()
	{
		if ( false === wc_cp_products()->cart_has_assembly_fee( WC()->cart ) )
		{
			// skip as the cart doesn't have assembly fees applied
			return;
		}

		if ( 'on' !== filter_input( INPUT_POST, 'assembly' ) )
		{
			// add error message
			wc_add_notice( __( 'You must accept our assembly specifications.', 'woocommerce' ), 'error' );

			ob_start();
			wc_print_notices();

			wp_send_json( [
				'result'   => 'failure',
				'messages' => ob_get_clean(),
				'refresh'  => isset( WC()->session->refresh_totals ) ? 'true' : 'false',
				'reload'   => isset( WC()->session->reload_checkout ) ? 'true' : 'false',
			] );
		}
	}

	/**
	 * Add compatible products data to variation data
	 *
	 * @param array $variation_data
	 *
	 * @return array
	 */
	public function append_compatible_products_to_variation_data( $variation_data )
	{
		// product variation information
		/* @var $variation_product WC_Product_Variation */
		$variation_product = wc_get_product( $variation_data['variation_id'] );

		// variation name
		$variation_data['variation_name'] = $variation_product->get_title() . ' - ' . $variation_product->get_formatted_variation_attributes( true );

		// append list to variation data array
		$variation_data['_wc_cp_compatible_products'] = wc_cp_products()->get_product_compatible_products_list( $variation_data['variation_id'], true );

		// current assembly configuration
		$assembly_config = wc_cp_products()->get_assembly_configuration( null, $variation_data['variation_id'] );
		if ( false === $assembly_config )
		{
			// generate new one
			$assembly_config = wc_cp_products()->create_new_assembly_configuration( $variation_data['variation_id'] );
		}

		if ( isset( $variation_data['_wc_cp_compatible_products'][0] ) )
		{
			// append compatible products panel
			$variation_data['variation_description'] .= wc_cp_view( 'frontend/compatible_list', [
				'compatible_products' => $variation_data['_wc_cp_compatible_products'],
				'assembly_config'     => $assembly_config,
			], true );
		}

		return $variation_data;
	}

	/**
	 * Add order assembly fees confirmation checkbox
	 *
	 * @return void
	 */
	public function order_assembly_fees_confirm_checkbox()
	{
		if ( false === wc_cp_products()->cart_has_assembly_fee( WC()->cart ) )
		{
			// skip as the cart doesn't have assembly fees applied
			return;
		}

		// echo $this->get_measuring_instructions();

		wc_cp_view( 'frontend/checkout/assembly_checkbox', [
			'assembly_is_checked' => 'on' === filter_input( INPUT_POST, 'assembly' ),
		] );
	}

	/**
	 * Add new notice type in WooCommerce
	 *
	 * @param array $notice_types
	 *
	 * @return array
	 */
	public function add_assembly_notice_type( $notice_types )
	{
		if ( !in_array( $this->assembly_notice_type, $notice_types ) )
		{
			$notice_types[] = $this->assembly_notice_type;
		}

		return $notice_types;
	}

	/**
	 * Add remove button to the assembly fee
	 *
	 * @param string $cart_totals_fee_html
	 * @param object $fee
	 *
	 * @return string
	 */
	public function add_assembly_fee_remove_button( $cart_totals_fee_html, $fee )
	{
		if ( 'assembly-fees' !== $fee->id )
		{
			// return original as not the target fee
			return $cart_totals_fee_html;
		}

		return $cart_totals_fee_html . ' <a href="' . esc_attr( add_query_arg( [
			'wc_cp_remove_fee' => 'yes',
			'wc-ajax'          => false,
		] ) ) . '" title="' . __( 'Remove', 'woocommerce' ) . '"><i class="fa fa-minus-circle"></i></a>';
	}

	/**
	 * Add assembly notification message if the cart has compatible products
	 *
	 * @return
	 */
	public function add_assembly_notice()
	{
		$assembly_fees = wc_cp_products()->setup_cart_assembly_fees( WC()->cart, WC()->session );
		if ( false === $assembly_fees )
		{
			// skip as forced to ignore or not applicable
			return;
		}

		$notice_content = get_option( 'wc_cp_assembly_notice', false );
		if ( false === $notice_content )
		{
			// skip as the content is not set
			return;
		}

		// append final cost and confirm button
		$notice_content .= sprintf( __( '<b>%s</b> <a href="%s" class="button"><i class="fa fa-plus-circle"></i> Yes</a> ', 'woocommerce' ),
			wc_price( $assembly_fees ),
			add_query_arg( 'wc_cp_add_assembly', 'yes' )
		);

		// add the offer notice
		wc_add_notice( $notice_content, $this->assembly_notice_type );
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
		wp_localize_script( 'wc-cp-compatible-products', 'wc_compatible_products_params', [
			'woocommerce_currency_symbol'    => get_woocommerce_currency_symbol(),
			'woocommerce_price_num_decimals' => wc_get_price_decimals(),
			'woocommerce_currency_pos'       => get_option( 'woocommerce_currency_pos' ),
			'woocommerce_price_decimal_sep'  => stripslashes( get_option( 'woocommerce_price_decimal_sep' ) ),
			'woocommerce_price_thousand_sep' => stripslashes( get_option( 'woocommerce_price_thousand_sep' ) ),
			'woocommerce_price_trim_zeros'   => get_option( 'woocommerce_price_trim_zeros' ),
		] );
	}

	/**
	 * Get measuring instructions modal box
	 *
	 * @return string
	 */
	public function get_measuring_instructions()
	{
		ob_start();

		wc_cp_view( 'frontend/instructions_modal', [
			'instructions' => wc_cp_products()->get_measuring_instructions(),
		] );

		return ob_get_clean();
	}
}
