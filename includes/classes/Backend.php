<?php namespace WooCommerce\Compatible_Products;

use WP_Post;

/**
 * Backend logic
 *
 * @package WooCommerce\Compatible_Products
 */
class Backend extends Component
{
	/**
	 * Compatible products mete key
	 *
	 * @var string
	 */
	protected $compatible_meta_key = '';

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init()
	{
		parent::init();

		$this->compatible_meta_key = wc_compatible_products()->products->get_compatible_products_key();

		// Product variation extra attributes action
		add_action( 'woocommerce_product_after_variable_attributes', [
			&$this,
			'product_variation_compatible_products_field',
		], 10, 3 );

		// Save product variation data
		add_action( 'woocommerce_save_product_variation', [ &$this, 'save_product_variation_compatible_data' ] );

		// TODO move data to main variation list
		// add_filter( 'woocommerce_available_variation', 'load_variation_settings_fields' );

		// dashboard scripts
		add_action( 'admin_enqueue_scripts', [ &$this, 'enqueue_scripts' ] );
	}

	/**
	 * Save the selected meta boxes
	 *
	 * @param int $variation_id
	 *
	 * @return void
	 */
	public function save_product_variation_compatible_data( $variation_id )
	{
		$selected_products = filter_input( INPUT_POST, $this->compatible_meta_key, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY );
		if ( !is_array( $selected_products ) || !isset( $selected_products[ $variation_id ] ) )
		{
			// skip invalid data
			return;
		}

		// clear old values
		delete_post_meta( $variation_id, $this->compatible_meta_key );

		foreach ( explode( ',', $selected_products[ $variation_id ] ) as $product_id )
		{
			// save meta value(s)
			add_post_meta( $variation_id, $this->compatible_meta_key, $product_id );
		}
	}

	/**
	 * Add product variation compatible products field
	 *
	 * @param int     $variation_index
	 * @param array   $variation_data
	 * @param WP_Post $variation
	 *
	 * @return void
	 */
	public function product_variation_compatible_products_field( $variation_index, $variation_data, $variation )
	{
		// vars
		$products_ids  = wc_compatible_products()->products->get_product_compatible_products( $variation->ID );
		$products_list = [ ];
		foreach ( $products_ids as $product_id )
		{
			$products_list[] = [
				'id'   => $product_id,
				'text' => wc_get_product( $product_id )->get_formatted_name(),
			];
		}

		wc_cp_view( 'admin/product_data/compatibles_field', [
			'variation_id'      => $variation->ID,
			'field_name'        => $this->compatible_meta_key,
			'field_value'       => $products_ids,
			'initial_selection' => $products_list,
		] );
	}

	/**
	 * Load JS assets
	 *
	 * @return void
	 */
	public function enqueue_scripts()
	{
		if ( 'product' !== get_current_screen()->id )
		{
			// skip non-related view
			return;
		}

		$base_dir = WC_CP_URI . Helpers::enqueue_base_dir();

		// main js file
		wp_enqueue_script( 'wc-cp-compatible-products', $base_dir . 'js/admin-products.js', [
			'jquery',
			'select2',
		], wc_cp_version(), true );

		// localization
		wp_localize_script( 'wc-cp-compatible-products', 'wc_cp_compatible_products', [
			'i18n'         => [
				'placeholder' => __( 'Search for a product/variation', WC_CP_DOMAIN ),
			],
			'search_nonce' => wp_create_nonce( 'search-products' ),
		] );
	}
}
