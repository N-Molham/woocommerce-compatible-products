<?php namespace WooCommerce\Compatible_Products;

use WC_Cart;

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

		// WooCommerce cart fee output filter
		add_filter( 'woocommerce_cart_totals_fee_html', [ &$this, 'add_assembly_fee_remove_button' ], 10, 2 );

		// before loading cart template
		add_action( 'woocommerce_check_cart_items', [ &$this, 'add_assembly_notice' ] );

		// load JS asset(s)
		add_action( 'wp_enqueue_scripts', [ &$this, 'enqueues' ] );
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

		// add the offer notice
		wc_add_notice( sprintf( __( 'Do you need assembly? Fees: <b>%s</b> <a href="%s" class="button"><i class="fa fa-plus-circle"></i> Yes</a> ', 'woocommerce' ),
			wc_price( $assembly_fees ),
			add_query_arg( 'wc_cp_add_assembly', 'yes' ) ), 'notice' );
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
