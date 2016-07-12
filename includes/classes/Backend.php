<?php namespace WooCommerce\Compatible_Products;

use WC_Product;
use WC_Product_Variation;
use WP_Post;
use WP_Term;

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
	 * Default assembly price percentage to use
	 *
	 * @var float
	 */
	public $default_assembly_percentage = 30;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init()
	{
		parent::init();

		$this->compatible_meta_key = wc_compatible_products()->products->get_compatible_products_key();

		// Product meta data processing for save
		add_action( 'woocommerce_process_product_meta', [ &$this, 'save_product_assembly_percentage' ] );

		// Product information general information tab fields
		add_action( 'woocommerce_product_options_general_product_data', [
			&$this,
			'product_general_assembly_percentage_field',
		] );

		// Product variation extra attributes action
		add_action( 'woocommerce_product_after_variable_attributes', [
			&$this,
			'product_variation_assembly_percentage_field',
		], 10, 3 );
		add_action( 'woocommerce_product_after_variable_attributes', [
			&$this,
			'product_variation_compatible_products_field',
		], 12, 3 );

		// Save product variation data
		add_action( 'woocommerce_save_product_variation', [ &$this, 'save_product_variation_compatible_data' ] );
		add_action( 'woocommerce_save_product_variation', [
			&$this,
			'save_product_variation_assembly_percentage_data',
		] );

		// dashboard scripts
		add_action( 'admin_enqueue_scripts', [ &$this, 'enqueue_scripts' ] );

		// WooCommerce general settings filter
		add_filter( 'woocommerce_products_general_settings', [ &$this, 'add_search_categories_filter' ] );

		// WooCommerce product category form fields action hook
		add_action( 'product_cat_add_form_fields', [ &$this, 'add_assembly_category_field' ], PHP_INT_MAX );
		add_action( 'product_cat_edit_form_fields', [ &$this, 'edit_assembly_category_field' ], PHP_INT_MAX );

		// Save WooCommerce product category data
		add_action( 'create_product_cat', [ &$this, 'save_assembly_category_data' ] );
		add_action( 'edit_product_cat', [ &$this, 'save_assembly_category_data' ] );
	}

	/**
	 * Save assembly category data
	 *
	 * @param int $term_id
	 *
	 * @return void
	 */
	public function save_assembly_category_data( $term_id )
	{
		update_term_meta( $term_id, 'wc_cp_assembly_category', filter_input( INPUT_POST, 'wc_cp_assembly_category', FILTER_SANITIZE_NUMBER_INT ) );
	}

	/**
	 * Add assembly category field UI
	 *
	 * @return void
	 */
	public function add_assembly_category_field()
	{
		wc_cp_view( 'admin/category/add_assembly_field', [ 'is_checked' => false ] );
	}

	/**
	 * Edit assembly category field UI
	 *
	 * @param WP_Term $term
	 *
	 * @return void
	 */
	public function edit_assembly_category_field( $term )
	{
		wc_cp_view( 'admin/category/edit_assembly_field', [ 'is_checked' => (bool) get_term_meta( $term->term_id, 'wc_cp_assembly_category', true ) ] );
	}

	/**
	 * Save the selected meta boxes
	 *
	 * @param int $variation_id
	 *
	 * @return void
	 */
	public function save_product_variation_assembly_percentage_data( $variation_id )
	{
		$percentages = filter_input( INPUT_POST, 'wc_cp_variation_assembly_percentage', FILTER_VALIDATE_FLOAT, FILTER_REQUIRE_ARRAY );
		if ( empty( $percentages ) || !isset( $percentages[ $variation_id ] ) )
		{
			// skip
			return;
		}

		update_post_meta( $variation_id, '_wc_cp_assembly_percentage', abs( $percentages[ $variation_id ] ) );
	}

	/**
	 * Add product variation assembly percentage field
	 *
	 * @param int     $variation_index
	 * @param array   $variation_data
	 * @param WP_Post $variation
	 *
	 * @return void
	 */
	public function product_variation_assembly_percentage_field( $variation_index, $variation_data, $variation )
	{
		// default and product percentages
		$default_assembly_percentage   = wc_cp_products()->get_global_assembly_percentage();
		$variation_assembly_percentage = wc_cp_products()->get_product_assembly_percentage( $variation );
		if ( 0 == $variation_assembly_percentage )
		{
			// switch to empty string to display the placeholder
			$variation_assembly_percentage = '';
		}

		wc_cp_view( 'admin/product_data/assembly_percentage_field', compact( 'default_assembly_percentage', 'variation_assembly_percentage', 'variation' ) );
	}

	/**
	 * Save product's general assembly percentage
	 *
	 * @param int $product_id
	 *
	 * @return void
	 */
	public function save_product_assembly_percentage( $product_id )
	{
		update_post_meta( $product_id, '_wc_cp_assembly_percentage', abs( floatval( filter_input( INPUT_POST, 'wc_cp_assembly_percentage', FILTER_SANITIZE_NUMBER_FLOAT ) ) ) );
	}

	/**
	 * Display product's general assembly percentage
	 *
	 * @return void
	 */
	public function product_general_assembly_percentage_field()
	{
		// current product
		$product = wc_get_product();

		// default and product percentages
		$default_assembly_percentage = wc_cp_products()->get_global_assembly_percentage();
		$product_assembly_percentage = wc_cp_products()->get_product_assembly_percentage( $product );
		if ( 0 == $product_assembly_percentage )
		{
			// switch to empty string to display the placeholder
			$product_assembly_percentage = '';
		}

		woocommerce_wp_text_input( [
			'label'             => __( 'Assembly Price Percentage (%)', WC_CP_DOMAIN ),
			'id'                => 'wc_cp_assembly_percentage',
			'name'              => 'wc_cp_assembly_percentage',
			'value'             => $product_assembly_percentage,
			'placeholder'       => $default_assembly_percentage,
			'description'       => __( 'Percentage used as default for the product pricing as general and as the default it\'s variations.', WC_CP_DOMAIN ),
			'desc_tip'          => true,
			'type'              => 'number',
			'custom_attributes' => [
				'step' => 0.5,
				'min'  => 0,
			],
		] );
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
			'title'             => __( 'Assembly Price Percentage (%)', 'woocommerce' ),
			'desc'              => '',
			'id'                => 'wc_cp_assembly_percentage',
			'css'               => 'width:65px;',
			'class'             => 'code',
			'type'              => 'number',
			'custom_attributes' => [
				'min'  => 0,
				'step' => 0.5,
			],
			'default'           => $this->default_assembly_percentage,
		];

		$settings[] = [
			'title'    => __( 'Product button with assembly label', WC_CP_DOMAIN ),
			'desc'     => __( 'The label of the product page button with assembly fees added', WC_CP_DOMAIN ),
			'desc_tip' => true,
			'id'       => 'wc_cp_product_assembly_btn_label',
			'css'      => '',
			'class'    => 'regular-text',
			'type'     => 'text',
			'default'  => 'Select Options with Assembly',
		];

		$settings[] = [
			'type' => 'sectionend',
			'id'   => 'wc_cp_options',
		];

		return $settings;
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
		if ( !is_array( $selected_products ) || !array_key_exists( $variation_id, $selected_products ) )
		{
			// skip invalid data
			return;
		}

		wc_cp_products()->set_product_compatible_products( $variation_id, $selected_products[ $variation_id ] );
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
