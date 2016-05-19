<?php
/**
 * Created by PhpStorm.
 * User: nabeel
 * Date: 5/18/16
 * Time: 6:18 PM
 */

// products query args holder
$query_args      = [ ];
$popover_content = '';
?>
<div class="row">
	<div class="col-md-4 col-sm-4"><?php _e( 'Do you need fittings?', 'woocommerce' ) ?></div>
	<div class="col-md-4 col-sm-4">
		<label>
			<input class="wc-cp-need-compatible" type="checkbox" value="yes" />
			<?php _e( 'Yes', 'wooocommerce' ); ?>
		</label>
	</div>
</div>

<div class="panel panel-primary wc-cp-products-list">
	<div class="panel-heading"><?php _e( 'Compatible Products', 'woocommerce' ); ?></div>
	<div class="panel-body">
		<ul class="list-group">
			<?php foreach ( $compatible_products as $product ) : ?>
				<?php
				// parse product args
				parse_str( parse_url( $product['add_to_cart_link'], PHP_URL_QUERY ), $query_args );

				if ( !isset( $query_args['add-to-cart'] ) )
				{
					// skip products that can't be added to the cart
					continue;
				}

				// replace add to cart arg
				$query_args['action']     = 'add_compatible_product_to_cart';
				$query_args['product_id'] = $query_args['add-to-cart'];
				$query_args['security']   = wp_create_nonce( 'wc_cp_add_to_cart' );
				unset( $query_args['add-to-cart'] );

				// popover content
				$popover_content = [
					$product['image'],
					'<div class="wc-cp-product-price align-center">' . $product['price_formatted'] . '</div>',
					'<a href="'. esc_url( $product['product_link'] ) .'" target="_blank" class="button btn btn-block wc-cp-product-link">'. __( 'Read More', 'woocommerce' ) .'</a>',
				];
				?>
				<li class="list-group-item compatible-product">
					<div class="row">
						<div class="col-md-9">
							<a href="javascript:void(0);" target="_blank" class="compatible-product-link"
							   data-toggle="popover" data-html="true" data-placement="top" data-trigger="focus"
							   data-content="<?php echo esc_attr( implode( '', $popover_content ) ); ?>">
								<?php echo $product['text']; ?>
							</a>
						</div>
						<div class="col-md-3 align-right">
							<a href="<?php echo esc_url( $product['add_to_cart_link'] ) ?>" class="button compatible-product-add-to-cart-link"
							   data-args="<?php echo esc_attr( json_encode( $query_args ) ); ?>"
							   data-loading-text="<?php _e( 'Adding...', 'woocommerce' ); ?>" data-added-text="<?php _e( 'Added', 'woocommerce' ); ?>">
								<?php _e( 'Add to cart', 'woocommerce' ); ?>
							</a>
						</div>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
