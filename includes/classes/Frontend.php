<?php namespace WooCommerce\Compatible_Products;

use WC_Cart;
use WC_Order_Item_Meta;
use WC_Product;
use WC_Product_Variation;
use WP_Post;

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
	 * Whither current mode with assembly or not
	 *
	 * @var boolean
	 */
	protected $with_assembly;

	/**
	 * Is the current pointer within the variation form of not
	 *
	 * @var boolean
	 */
	protected $within_variation_form;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init()
	{
		parent::init();

		// vars
		$this->with_assembly = isset( $_REQUEST['wc_cp_with_need_assembly'] ) && 'yes' === sanitize_key( $_REQUEST['wc_cp_with_need_assembly'] );

		// WooCommerce cart fee output filter
		add_filter( 'woocommerce_cart_totals_fee_html', [ &$this, 'add_assembly_fee_remove_button' ], 10, 2 );

		// before loading cart template
		// add_action( 'woocommerce_check_cart_items', [ &$this, 'add_assembly_notice' ] );

		// load JS asset(s)
		add_action( 'wp_enqueue_scripts', [ &$this, 'enqueues' ] );

		// WooCommerce notices types
		// add_filter( 'woocommerce_notice_types', [ &$this, 'add_assembly_notice_type' ] );

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

		// WooCommerce product item extra data to save to add to the cart
		add_filter( 'woocommerce_add_cart_item_data', [ &$this, 'remove_updated_cart_item' ], 5, 3 );
		add_filter( 'woocommerce_add_cart_item_data', [ &$this, 'add_assembly_config_to_cart_item' ], 10, 3 );

		// WooCommerce product item data to be saved in the cart
		add_filter( 'woocommerce_add_cart_item', [ &$this, 'set_assembly_config_price_in_cart' ], 50 );

		add_action( 'woocommerce_before_calculate_totals', [ &$this, 'set_assembly_config_total_price_in_cart' ] );

		// WooCommerce cart item extra data and price
		add_filter( 'woocommerce_get_item_data', [ &$this, 'list_assembly_config_list_to_cart_item' ], 20, 2 );
		add_filter( 'woocommerce_cart_item_price', [ &$this, 'assembly_configuration_cart_item_price' ], 20, 3 );

		// WooCommerce order items add meta
		add_action( 'woocommerce_add_order_item_meta', [
			&$this,
			'assembly_configuration_add_order_item_meta',
		], 10, 2 );

		// WooCommerce formatted order order metas
		add_filter( 'woocommerce_order_items_meta_get_formatted', [
			&$this,
			'assembly_configuration_order_item_meta',
		], 10, 2 );

		if ( $this->with_assembly )
		{
			// WooCommerce product price
			add_filter( 'woocommerce_get_price', [ &$this, 'get_product_price_with_assembly_percentage' ], 10, 2 );

			// WooCommerce after product's add to cart button
			add_action( 'woocommerce_after_add_to_cart_button', [ &$this, 'assembly_percentage_hidden_input_mark' ] );
		}

		// WooCommerce add to cart link
		add_filter( 'woocommerce_loop_add_to_cart_link', [
			&$this,
			'product_page_assembly_button_ui',
		], PHP_INT_MAX, 2 );

		// WP single/product title filter
		add_filter( 'the_title', [ &$this, 'assembly_product_page_title' ], 10, 2 );

		// WC product attribute label filter
		add_filter( 'woocommerce_attribute_label', [ &$this, 'assembly_product_page_attribute_label' ] );

		// WC variation form before/after action hooks
		add_action( 'woocommerce_before_variations_form', [ &$this, 'set_within_assembly_mark' ] );
		add_action( 'woocommerce_after_variations_form', [ &$this, 'unset_within_assembly_mark' ] );

		// WP body tag css classes filter
		add_filter( 'body_class', [ &$this, 'assembly_product_page_css_classes' ] );

		// WC Measurements price label
		add_filter( 'wc_measurement_price_calculator_label', [
			&$this,
			'assembly_product_page_measurement_label',
		] );

		// WC before template part is loaded
		add_action( 'woocommerce_before_template_part', [ &$this, 'assembly_product_page_quantity_label' ], 10, 4 );

		// WC before template part is loaded
		add_action( 'woocommerce_after_template_part', [
			&$this,
			'assembly_product_page_quantities_total',
		], PHP_INT_MAX, 4 );

		// WC before single product page layout
		add_action( 'woocommerce_before_single_product', [ &$this, 'unhook_notices_print' ], 0 );

		// WC product meta data ends hook
		add_action( 'woocommerce_product_meta_end', [ &$this, 'print_wc_notices' ] );

		// WC product added to cart message filter
		add_filter( 'wc_add_to_cart_message', [ &$this, 'add_continue_shopping_link' ], 10, 2 );

		// WP post permalink filter
		add_filter( 'post_type_link', [ &$this, 'product_link_with_assembly_option' ], 40, 2 );
	}

	/**
	 * Add assembly option to product link
	 *
	 * @param string  $permalink
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public function product_link_with_assembly_option( $permalink, $post )
	{
		if ( false === $this->is_assembly_product_page( $post->ID ) )
		{
			// skip
			return $permalink;
		}

		return add_query_arg( 'wc_cp_with_need_assembly', 'yes', $permalink );
	}

	/**
	 * Add "Continue Shopping" link to the added-to-cart message
	 *
	 * @param string $message
	 * @param int    $product_id
	 *
	 * @return string
	 */
	public function add_continue_shopping_link( $message, $product_id = false )
	{
		if ( false === $this->is_assembly_product_page( $product_id ) )
		{
			// skip
			return $message;
		}

		$count = 1;

		return str_replace( '</a>', '</a> <a href="' . esc_url( get_option( 'wc_cp_continue_shopping_url', wc_get_page_permalink( 'shop' ) ) ) . '" class="button wc-continue-shopping wc-forward">' . __( 'Continue Shopping', WC_CP_DOMAIN ) . '</a>', $message, $count );
	}

	/**
	 * Print notices if assembly product
	 *
	 * @return void
	 */
	public function print_wc_notices()
	{
		if ( $this->is_assembly_product_page() )
		{
			// is assembly product so print the notices here
			echo '<div class="woocommerce-messages-container">';
			wc_print_notices();
			echo '</div>';
		}
	}

	/**
	 * Unhook notices print before assembly product page
	 *
	 * @return void
	 */
	public function unhook_notices_print()
	{
		if ( $this->is_assembly_product_page() )
		{
			// is assembly product so remove the print from here
			remove_action( 'woocommerce_before_single_product', 'wc_print_notices' );
		}
	}

	/**
	 * Assembly quantity label before main qty input
	 *
	 * @param string $template_name
	 * @param string $template_path
	 * @param string $located
	 * @param array  $args
	 *
	 * @return void
	 */
	public function assembly_product_page_quantity_label( $template_name, $template_path, $located, $args )
	{
		if ( 'global/quantity-input.php' !== $template_name || isset( $args['wc_cp_input'] ) || false === $this->is_assembly_product_page() )
		{
			// skip non-single assembly product page
			return;
		}

		echo '<span class="wc-cp-assembly-qty-label">', __( 'Quantity of Assemblies', WC_CP_DOMAIN ), '</span>';
	}

	/**
	 * Assembly quantity total after main qty input
	 *
	 * @param string $template_name
	 * @param string $template_path
	 * @param string $located
	 * @param array  $args
	 *
	 * @return void
	 */
	public function assembly_product_page_quantities_total( $template_name, $template_path, $located, $args )
	{
		if ( 'global/quantity-input.php' !== $template_name || isset( $args['wc_cp_input'] ) || false === $this->is_assembly_product_page() )
		{
			// skip non-single assembly product page
			return;
		}

		echo '<div class="wc-cp-assemblies-total">', __( 'Total (Freight not included)', WC_CP_DOMAIN ),
		'<span class="wc-cp-assemblies-subtotal-amount"></span></div><br/>';
	}

	/**
	 * Prepend assembly step before measurement label
	 *
	 * @param string $label
	 *
	 * @return string
	 */
	public function assembly_product_page_measurement_label( $label )
	{
		if ( false === $this->is_assembly_product_page() )
		{
			// skip non-single assembly product page
			return $label;
		}

		return '<strong class="assembly-step-label">' . __( 'Step 3.', WC_CP_DOMAIN ) . '</strong> ' . $label;
	}

	/**
	 * Assembly product page Unique css class
	 *
	 * @param array $classes
	 *
	 * @return array
	 */
	public function assembly_product_page_css_classes( $classes )
	{
		if ( false === $this->is_assembly_product_page() || in_array( 'single-product-assembly', $classes ) )
		{
			// skip non-single assembly product page
			return $classes;
		}

		// append assembly product body CSS class
		$classes[] = 'single-product-assembly';

		return $classes;
	}

	/**
	 * Prepend "STEP 2" before length attribute label
	 *
	 * @param string $label
	 *
	 * @return string
	 */
	public function assembly_product_page_attribute_label( $label )
	{
		if ( false === $this->is_assembly_product_page() || true !== $this->within_variation_form )
		{
			// skip non-single assembly product page
			return $label;
		}

		return '<span class="assembly-step-label">' . __( 'Step 2.', WC_CP_DOMAIN ) . '</span> ' . $label;
	}

	/**
	 * Mark the pointer as within variation form
	 *
	 * @return void
	 */
	public function set_within_assembly_mark()
	{
		$this->within_variation_form = true;
	}

	/**
	 * Un-mark the pointer as within variation form
	 *
	 * @return void
	 */
	public function unset_within_assembly_mark()
	{
		$this->within_variation_form = false;
	}

	/**
	 * Append "assembly" label to assmebly product title
	 *
	 * @param string $title
	 * @param int    $product_id
	 *
	 * @return string
	 */
	public function assembly_product_page_title( $title, $product_id )
	{
		if ( false === $this->is_assembly_product_page( $product_id ) )
		{
			// skip non-single assembly product page
			return $title;
		}

		return $title . ' ' . ( wc_cp_products()->is_assembly_product( wc_get_product( $product_id ) ) ? __( 'Assembly', WC_CP_DOMAIN ) : __( '- Bulk', WC_CP_DOMAIN ) );
	}

	/**
	 * Display product page assembly button after the loop "Add to Cart" button
	 *
	 * @param string     $add_to_cart_link
	 * @param WC_Product $product
	 *
	 * @return string
	 */
	public function product_page_assembly_button_ui( $add_to_cart_link, $product )
	{
		if ( false === wc_cp_products()->is_assembly_product( $product ) )
		{
			// skip unwanted category
			return $add_to_cart_link;
		}

		// vars
		$button_label = get_option( 'wc_cp_product_assembly_btn_label', __( 'Select Options with Assembly', WC_CP_DOMAIN ) );
		//$button_link  = add_query_arg( 'wc_cp_with_need_assembly', 'yes', $product->get_permalink() );
		$button_link = $product->get_permalink();

		$add_to_cart_link = wc_cp_view( 'frontend/assembly_product_page_button', compact( 'button_label', 'button_link' ), true );

		return $add_to_cart_link;
	}

	/**
	 * Add hidden input to assembly main product to use in cart
	 *
	 * @return void
	 */
	public function assembly_percentage_hidden_input_mark()
	{
		echo '<input type="hidden" name="wc_cp_with_need_assembly" value="yes" />';
	}

	/**
	 * Calculate product price with assembly percentage added
	 *
	 * @param float      $price
	 * @param WC_Product $product
	 *
	 * @return float
	 */
	public function get_product_price_with_assembly_percentage( $price, $product )
	{
		return wc_cp_products()->calculate_price_with_assembly( $product, $price );
	}

	/**
	 * Append assembly configuration order item meta
	 *
	 * @param array              $formatted_meta
	 * @param WC_Order_Item_Meta $order_item_meta
	 *
	 * @return array
	 */
	public function assembly_configuration_order_item_meta( $formatted_meta, $order_item_meta )
	{
		$assembly_config = isset( $order_item_meta->meta['wc_cp_assembly_config'] ) ? $order_item_meta->meta['wc_cp_assembly_config'] : null;
		if ( empty( $assembly_config ) )
		{
			// skip unrated item;
			return $formatted_meta;
		}

		// parse raw meta value
		$assembly_config = maybe_unserialize( array_shift( $assembly_config ) );
		$with_assembly   = (boolean) $order_item_meta->meta['wc_cp_with_need_assembly'][0];

		// parts list holder
		$assembly_parts = [];

		foreach ( $assembly_config['parts'] as $part_item )
		{
			$_product = wc_get_product( isset( $part_item['variation_id'] ) ? $part_item['variation_id'] : $part_item['product_id'] );
			if ( false === $_product )
			{
				// skip missing part linked product
				continue;
			}

			// product base name
			$product_name = $_product->get_sku() . ' &ndash; ' . $_product->get_title();
			if ( wc_cp_products()->is_product_variation( $_product ) )
			{
				// variation attribute(s)
				$product_name .= ' &ndash; ' . $_product->get_formatted_variation_attributes( true );
			}

			// product price
			if ( $with_assembly )
			{
				// with assembly fees
				$product_name .= ' &ndash; ' . wc_cp_products()->calculate_price_with_assembly( $_product );
			}
			else
			{
				// normal price
				$product_name .= ' &ndash; ' . $_product->get_price();
			}

			$assembly_parts[] = sprintf( '%s x <strong>%s</strong>', $product_name, $part_item['quantity'] );
		}

		// append parts list
		$formatted_meta[] = [
			'key'   => 'wc_cp_assembly_config',
			'label' => __( 'Assembly Configuration', WC_CP_DOMAIN ),
			'value' => '<ul class="assembly-configration"><li>' . implode( '</li><li>', $assembly_parts ) . '</li></ul>',
		];

		return $formatted_meta;
	}

	/**
	 * Add assembly configuration data to the order
	 *
	 * @param int   $item_id
	 * @param array $values
	 *
	 * @return void
	 */
	public function assembly_configuration_add_order_item_meta( $item_id, $values )
	{
		if ( !isset( $values['wc_cp_assembly_config'] ) || !isset( $values['wc_cp_assembly_config']['parts'] ) )
		{
			// return price unmodified as the item is not in an assembly
			return;
		}

		wc_add_order_item_meta( $item_id, 'wc_cp_assembly_config', $values['wc_cp_assembly_config'], true );
		wc_add_order_item_meta( $item_id, 'wc_cp_with_need_assembly', $values['wc_cp_with_need_assembly'], true );
	}

	/**
	 * Override main product subtotal price with the assembly total price
	 *
	 * @param string $subtotal_price
	 * @param array  $cart_item
	 * @param string $cart_item_key
	 *
	 * @return string
	 */
	public function assembly_configuration_cart_item_subtotal( $subtotal_price, $cart_item, $cart_item_key )
	{
		if ( !isset( $cart_item['wc_cp_assembly_config'] ) || !isset( $cart_item['wc_cp_assembly_config']['parts'] ) )
		{
			// return price unmodified as the item is not in an assembly
			return $subtotal_price;
		}

		return wc_price( $cart_item['line_subtotal'] + wc_cp_products()->calculate_assembly_configuration_parts_total( $cart_item['wc_cp_assembly_config'], $cart_item['quantity'] ) );
	}

	/**
	 * Override main product price with the assembly total price
	 *
	 * @param string $price
	 * @param array  $cart_item
	 * @param string $cart_item_key
	 *
	 * @return string
	 */
	public function assembly_configuration_cart_item_price( $price, $cart_item, $cart_item_key )
	{
		if ( !isset( $cart_item['wc_cp_assembly_config'] ) || !isset( $cart_item['wc_cp_assembly_config']['parts'] ) )
		{
			// return data unmodified as the item is not in an assembly
			return $price;
		}

		return wc_price( $cart_item['data']->get_price() );
	}

	/**
	 * List assembly configuration items inline to the part item
	 *
	 * @param array $item_data
	 * @param array $cart_item
	 *
	 * @return array
	 */
	public function list_assembly_config_list_to_cart_item( $item_data, $cart_item )
	{
		if ( !isset( $cart_item['wc_cp_assembly_config'] ) || !isset( $cart_item['wc_cp_assembly_config']['parts'] ) )
		{
			// return data unmodified as the item is not in an assembly
			return $item_data;
		}

		$with_assembly = false === empty( $cart_item['wc_cp_with_need_assembly'] );

		// parts list holder
		$assembly_parts = [];

		foreach ( $cart_item['wc_cp_assembly_config']['parts'] as $part_item )
		{
			$_product = wc_get_product( isset( $part_item['variation_id'] ) ? $part_item['variation_id'] : $part_item['product_id'] );
			if ( false === $_product )
			{
				// skip missing part linked product
				continue;
			}

			// product base name
			$product_name = $_product->get_sku() . ' &ndash; ' . $_product->get_title();
			if ( wc_cp_products()->is_product_variation( $_product ) )
			{
				// variation attribute(s)
				$product_name .= ' &ndash; ' . $_product->get_formatted_variation_attributes( true );
			}

			// product price
			if ( $with_assembly )
			{
				// with assembly fees
				$product_name .= ' &ndash; ' . wc_cp_products()->calculate_price_with_assembly( $_product );
			}
			else
			{
				// normal price
				$product_name .= ' &ndash; ' . $_product->get_price();
			}

			$assembly_parts[] = sprintf( '<li class="assembly-item">%s x <strong>%s</strong></li>', $product_name, $part_item['quantity'] );
		}

		// assembly edit URL based on main product link
		$edit_args = [
			'wc_cp_edit_assembly' => 'yes',
			'wc_cp_assembly_key'  => $cart_item['wc_cp_assembly_config']['key'],
		];

		if ( $with_assembly )
		{
			// with assembly fees also
			$edit_args['wc_cp_with_need_assembly'] = 'yes';
		}

		/**
		 * Filter assembly configuration edit link URL
		 *
		 * @param string $edit_url
		 * @param array  $cart_item
		 *
		 * @return string
		 */
		$edit_url = apply_filters( 'wc_cp_edit_assembly_link_url', add_query_arg( $edit_args, $cart_item['data']->get_permalink() ), $cart_item );

		// assembly edit link
		$assembly_parts[] = '<li class="assembly-edit-link"><a href="' . esc_url( $edit_url ) . '" class="button">' . __( 'Edit Assembly', WC_CP_DOMAIN ) . '</a></li>';

		// append parts list
		$item_data[] = [
			'key'   => __( 'Assembly Configuration', WC_CP_DOMAIN ),
			'value' => '<ul class="assembly-configuration">' . implode( '', $assembly_parts ) . '</ul>',
		];

		return $item_data;
	}

	/**
	 * Override/set cart item of assembly configuration price
	 *
	 * @param array $cart_item
	 *
	 * @return array
	 */
	public function set_assembly_config_price_in_cart( $cart_item )
	{
		if ( !isset( $cart_item['wc_cp_assembly_config'] ) || !isset( $cart_item['wc_cp_assembly_config']['parts'] ) )
		{
			// return data unmodified as the item is not in an assembly
			return $cart_item;
		}

		if ( !isset( $cart_item['wc_cp_with_need_assembly'] ) )
		{
			// mart item that need assembly to add the fee percentage
			$cart_item['wc_cp_with_need_assembly'] = $this->with_assembly;
		}

		// new price
		$new_price = (float) $cart_item['data']->get_price() + wc_cp_products()->calculate_assembly_configuration_parts_total( $cart_item['wc_cp_assembly_config'], 1, $cart_item['wc_cp_with_need_assembly'] );

		/**
		 * Filter assembly configuration price
		 *
		 * @param float $new_price
		 * @param array $cart_item
		 *
		 * @return float
		 */
		$new_price = (float) apply_filters( 'wc_cp_assembly_configuration_cart_price', $new_price, $cart_item );

		// save the new price
		$cart_item['data']->set_price( $new_price );

		return $cart_item;
	}

	/**
	 * Override/set cart of assembly configuration total price
	 *
	 * @param WC_Cart $cart
	 *
	 * @return void
	 */
	public function set_assembly_config_total_price_in_cart( $cart )
	{
		if ( $cart->is_empty() )
		{
			// skip as cart is empty
			return;
		}

		foreach ( $cart->cart_contents as $item_key => &$cart_item )
		{
			$cart_item = $this->set_assembly_config_price_in_cart( $cart_item );
		}
	}

	/**
	 * Add assembly configuration to cart item
	 *
	 * @param array $cart_item_data
	 * @param int   $product_id
	 * @param int   $variation_id
	 *
	 * @return array
	 */
	public function add_assembly_config_to_cart_item( $cart_item_data, $product_id, $variation_id )
	{
		$assembly_key    = sanitize_key( filter_input( INPUT_POST, 'wc_cp_assembly_config_key', FILTER_SANITIZE_STRING ) );
		$assembly_config = wc_cp_products()->get_assembly_configuration( $assembly_key );
		if ( false === $assembly_config )
		{
			// return unmodified data
			return $cart_item_data;
		}

		if ( $product_id === $assembly_config['product_id'] || $variation_id === $assembly_config['product_id'] )
		{
			// append configuration to product cart item
			$cart_item_data['wc_cp_assembly_config'] = wc_cp_products()->clone_assembly_configuration( $assembly_config );
		}

		return $cart_item_data;
	}

	/**
	 * Remove assembly configuration cart item which will be updated
	 *
	 * @param array $cart_item_data
	 * @param int   $product_id
	 * @param int   $variation_id
	 *
	 * @return array
	 */
	public function remove_updated_cart_item( $cart_item_data, $product_id, $variation_id )
	{
		$assembly_key = sanitize_key( (string) filter_input( INPUT_POST, 'wc_cp_update_assembly', FILTER_SANITIZE_STRING ) );
		if ( empty( $assembly_key ) || WC()->cart->is_empty() )
		{
			// skip item with no configuration or cart doesn't have any other items
			return $cart_item_data;
		}

		$cart = WC()->cart->get_cart();
		foreach ( $cart as $cart_item_key => $cart_item )
		{
			if ( !isset( $cart_item['wc_cp_assembly_config'] ) && $assembly_key !== $cart_item['wc_cp_assembly_config']['key'] )
			{
				// skip non-configuration item or miss-matched
				continue;
			}

			// remove old item so it will be replaced with other
			WC()->cart->remove_cart_item( $cart_item_key );
		}

		return $cart_item_data;
	}

	/**
	 * Add measuring instructions modal to product page
	 *
	 * @return void
	 */
	public function product_fittings_measuring_instructions_modal()
	{
		$product = wc_get_product();
		if ( ( false !== $product && $this->is_assembly_product_page( $product->id ) ) || is_checkout() )
		{
			// only if assembly product or checkout page
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

		// get assembly configuration
		$assembly_config = false;
		$assembly_key    = sanitize_key( filter_input( INPUT_GET, 'wc_cp_assembly_key', FILTER_SANITIZE_STRING ) );
		if ( '' !== $assembly_key )
		{
			// get by the key
			$assembly_config = wc_cp_products()->get_assembly_configuration( $assembly_key );
		}

		if ( false === $assembly_config || $variation_data['variation_id'] !== $assembly_config['product_id'] )
		{
			// get config for the current variation
			$assembly_config = wc_cp_products()->get_assembly_configuration( null, $variation_data['variation_id'] );
		}

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
			// append the new notice type
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

		// assets base directory URL
		$base_url = WC_CP_URI . Helpers::enqueue_base_url();

		// assets version
		$version_file   = WC_CP_DIR . 'assets/last_update';
		$assets_version = file_exists( $version_file ) && is_readable( $version_file ) ? sanitize_key( file_get_contents( $version_file ) ) : null;
		if ( empty( $assets_version ) )
		{
			// fallback to plugin version
			$assets_version = wc_compatible_products()->version;
		}

		// main CSS file
		wp_enqueue_style( 'wc-cp-compatible-products', $base_url . 'css/compatible-products.css', null, $assets_version );

		// load main JS file
		wp_enqueue_script( 'wc-cp-compatible-products', $base_url . 'js/compatible-products.js', [ 'jquery' ], $assets_version, true );
		wp_localize_script( 'wc-cp-compatible-products', 'wc_compatible_products_params', [
			'labels'                         => [
				'edit_assembly'  => __( 'Update Assembly', WC_CP_DOMAIN ),
				'assembly_box_1' => __( 'Step 4: Select Fitting #1', WC_CP_DOMAIN ),
				'assembly_box_2' => __( 'Step 5: Select Fitting #2', WC_CP_DOMAIN ),
			],
			'assembly_update_nonce'          => wp_create_nonce( 'wc_cp_cart_update_assembly' ),
			'assembly_remove_nonce'          => wp_create_nonce( 'wc_cp_remove_assembly_item' ),
			'assembly_quantity_nonce'        => wp_create_nonce( 'wc_cp_update_assembly_main_product_quantity' ),
			'woocommerce_currency_symbol'    => get_woocommerce_currency_symbol(),
			'woocommerce_price_num_decimals' => wc_get_price_decimals(),
			'woocommerce_currency_pos'       => get_option( 'woocommerce_currency_pos' ),
			'woocommerce_price_decimal_sep'  => stripslashes( get_option( 'woocommerce_price_decimal_sep' ) ),
			'woocommerce_price_thousand_sep' => stripslashes( get_option( 'woocommerce_price_thousand_sep' ) ),
			'woocommerce_price_trim_zeros'   => get_option( 'woocommerce_price_trim_zeros' ),
			'is_assembly_product_page'       => $this->is_assembly_product_page(),
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

	/**
	 * Check if current request for assembly product page
	 *
	 * @param bool|int|WC_Product $product
	 *
	 * @return bool
	 */
	public function is_assembly_product_page( $product = false )
	{
		$is_product = is_product();
		if ( !is_object( $product ) )
		{
			// current query product
			$product    = wc_get_product( $product );
			$is_product = true;
		}

		return false === is_admin() && $is_product && $product && wc_cp_products()->is_assembly_product( $product );
	}
}
