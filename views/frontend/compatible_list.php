<?php
/**
 * Created by PhpStorm.
 * User: nabeel
 * Date: 5/18/16
 * Time: 6:18 PM
 */

// products query args holder
$query_args = [ ];
?>
<p class="wc-cp-need-fittings"><?php _e( 'Do you need fittings?', 'woocommerce' ) ?></p>
<p class="wc-cp-need-assembly"><?php _e( 'Do you need assembly?', 'woocommerce' ) ?></p>

<div class="panel panel-primary">
	<div class="panel-heading"><?php _e( 'Compatible Products', 'woocommerce' ); ?></div>
	<div class="panel-body">
		<ul class="list-group">
			<?php foreach ( $compatible_products as $product ) : ?>
				<?php
				// parse product args
				parse_str( parse_url( $product['add_to_cart_link'], PHP_URL_QUERY ), $query_args );

				// replace add to cart arg
				$query_args['action']     = 'add_compatible_product_to_cart';
				$query_args['product_id'] = $query_args['add-to-cart'];
				$query_args['security']   = wp_create_nonce( 'wc_cp_add_to_cart' );
				unset( $query_args['add-to-cart'] );
				?>
				<li class="list-group-item compatible-product">
					<div class="row">
						<div class="col-md-9">
							<a href="javascript:void(0);" class="compatible-product-link quickview" data-id="<?php echo esc_attr( $product['id'] ); ?>">
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
