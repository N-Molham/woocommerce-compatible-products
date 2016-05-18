<?php
/**
 * Created by PhpStorm.
 * User: nabeel
 * Date: 5/18/16
 * Time: 6:18 PM
 */
?>

<p class="wc-cp-need-fittings"><?php _e( 'Do you need fittings?', 'woocommerce' ) ?></p>
<p class="wc-cp-need-assembly"><?php _e( 'Do you need assembly?', 'woocommerce' ) ?></p>

<div class="panel panel-primary">
	<div class="panel-heading"><?php _e( 'Compatible Products', 'woocommerce' ); ?></div>
	<div class="panel-body">
		<ul class="list-group">
			<?php foreach ( $compatible_products as $product ) : ?>
				<li class="list-group-item compatible-product">
					<div class="row">
						<div class="col-md-9">
							<a href="javascript:void(0);" class="compatible-product-link quickview" data-id="<?php echo esc_attr( $product['id'] ); ?>">
								<?php echo $product['text']; ?>
							</a>
						</div>
						<div class="col-md-3 align-right">
							<a href="<?php echo esc_url( $product['add_to_cart_link'] ) ?>" class="button compatible-product-add-to-cart-link">
								<?php _e( 'Add to cart', 'woocommerce' ) ?>
							</a>
						</div>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
