<?php namespace WooCommerce\Compatible_Products;

use WC_Product;
use WC_Product_Variation;
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

		// Product variations data filter
		add_filter( 'woocommerce_available_variation', [ &$this, 'append_compatible_products_to_variation_data' ] );

		// dashboard scripts
		add_action( 'admin_enqueue_scripts', [ &$this, 'enqueue_scripts' ] );

		// WooCommerce general settings filter
		add_filter( 'woocommerce_products_general_settings', [ &$this, 'add_search_categories_filter' ] );
	}

	/**
	 * Add categories filter for JSON products search
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function add_search_categories_filter( $settings )
	{
		$settings[] = [
			'title' => __( 'Compatible Products Options', 'woocommerce' ),
			'type'  => 'title',
			'desc'  => '',
			'id'    => 'wc_cp_options',
		];

		$settings[] = [
			'title'    => __( 'Categories Filter', 'woocommerce' ),
			'desc'     => __( 'This controls which category(ies) the compatible products search in.', 'woocommerce' ),
			'id'       => 'wc_cp_category_filter',
			'css'      => 'min-width:350px;',
			'default'  => '',
			'type'     => 'multiselect',
			'class'    => 'wc-enhanced-select',
			'desc_tip' => true,
			'options'  => get_terms( 'product_cat', [ 'fields' => 'id=>name' ] ),
		];

		$settings[] = [
			'title' => __( 'Measuring Instructions', 'woocommerce' ),
			'desc'  => '',
			'id'    => 'wc_cp_measure_instructions',
			'css'   => 'min-width:350px; min-height: 220px;',
			'class' => 'code',
			'type'  => 'textarea',
		];

		$settings[] = [
			'title'   => __( 'Assembly Fees Notice', 'woocommerce' ),
			'desc'    => '',
			'id'      => 'wc_cp_assembly_notice',
			'css'     => 'min-width:350px; min-height: 220px;',
			'class'   => 'code',
			'type'    => 'textarea',
			'default' => __( 'Do you need assembly? Fees: ', 'woocommerce' ) . PHP_EOL,
		];

		$settings[] = [
			'type' => 'sectionend',
			'id'   => 'wc_cp_options',
		];

		return $settings;
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
		// append list to variation data array
		$variation_data['_wc_cp_compatible_products'] = wc_compatible_products()->products->get_product_compatible_products_list( $variation_data['variation_id'], true );

		if ( isset( $variation_data['_wc_cp_compatible_products'][0] ) )
		{
			// append compatible products panel
			$variation_data['variation_description'] .= wc_cp_view( 'frontend/compatible_list', [
				'compatible_products' => $variation_data['_wc_cp_compatible_products'],
			], true );
		}

		return $variation_data;
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
		wc_cp_view( 'admin/product_data/compatibles_field', [
			'variation_id'      => $variation->ID,
			'field_name'        => $this->compatible_meta_key,
			'field_value'       => wc_compatible_products()->products->get_product_compatible_products( $variation->ID ),
			'initial_selection' => wc_compatible_products()->products->get_product_compatible_products_list( $variation->ID ),
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
