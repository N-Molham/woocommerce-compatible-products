<?php
/**
 * Created by PhpStorm.
 * User: nabeel
 * Date: 5/18/16
 * Time: 6:18 PM
 */

// products query args holder
$query_args      = [];
$popover_content = '';
$panel_data      = array_map( function ( $product )
{
	unset( $product['wc_object'] );

	return $product;
}, $compatible_products );
?>
<div class="row">
	<div class="col-md-6 col-sm-6 col-xs-6 wc-cp-need-fittings"><?php _e( 'Do you need fittings?', 'woocommerce' ) ?></div>
	<div class="col-md-6 col-sm-4 col-xs-4">
		<label>
			<input class="wc-cp-need-compatible" type="checkbox" value="yes" />
			<?php _e( 'Yes', 'wooocommerce' ); ?>
		</label>
	</div>
</div>

<div class="panel panel-primary wc-cp-products-list hidden" data-products="<?php echo esc_attr( json_encode( $panel_data ) ); ?>">
	<div class="panel-heading"></div>
	<div class="panel-body">
		<ul class="list-group">
			<?php foreach ( $compatible_products as $product ) : ?>
				<?php
				// parse product args
				list( $base, $query ) = explode( '?', $product['add_to_cart_link'], 2 );
				wp_parse_str( urldecode( $query ), $query_args );

				if ( !isset( $query_args['add-to-cart'] ) )
				{
					// skip products that can't be added to the cart
					continue;
				}

				// replace add to cart arg
				$query_args['action']       = 'add_compatible_product_to_assembly';
				$query_args['product_id']   = $query_args['add-to-cart'];
				$query_args['assembly_key'] = $assembly_config['key'];
				$query_args['security']     = wp_create_nonce( 'wc_cp_add_to_assembly' );
				unset( $query_args['add-to-cart'] );

				// popover content
				$popover_content = [ $product['image'] ];

				if ( wp_is_mobile() )
				{
					// add product link to popover is mobile view
					$popover_content[] = '<a href="' . esc_url( $product['product_link'] ) . '" target="_blank" class="button btn btn-block wc-cp-product-link">' . __( 'Read More', 'woocommerce' ) . '</a>';
				}

				?>
				<li class="list-group-item compatible-product">
					<div class="row">
						<div class="col-md-6 col-sm-8 col-xs-12">
							<a href="<?php echo wp_is_mobile() ? 'javascript:void(0);' : esc_url( $product['product_link'] ); ?>" target="_blank" class="compatible-product-link"
							   data-toggle="popover" data-html="true" data-placement="top" data-trigger="<?php echo wp_is_mobile() ? 'focus' : 'hover' ?>"
							   data-content="<?php echo esc_attr( implode( '', $popover_content ) ); ?>"><?php echo $product['text']; ?></a>
						</div>
						<div class="col-md-3 col-sm-4 col-xs-8 align-right"><?php echo $product['price_formatted']; ?></div>
						<div class="col-md-3 col-sm-12 col-xs-4 align-right">
							<a href="javascript:void(0)" class="button compatible-product-add-to-cart-link" data-args="<?php echo esc_attr( json_encode( $query_args ) ); ?>"
							   data-product="<?php echo isset( $query_args['variation_id'] ) ? $query_args['variation_id'] : $query_args['product_id']; ?>"
							   data-loading-text="<?php _e( 'Adding...', 'woocommerce' ); ?>" data-added-text="<?php _e( 'Remove', 'woocommerce' ); ?>"><?php
								_e( 'Add', 'woocommerce' ); ?></a>
						</div>
					</div>
				</li>
				<?php $query_args = []; ?>
			<?php endforeach; ?>
		</ul>
	</div>
</div>

<div class="panel panel-primary wc-cp-assembly-config hidden" data-config="<?php echo esc_attr( wp_json_encode( $assembly_config ) ) ?>">
	<div class="panel-heading"><?php _e( 'Review your assembly configuration', 'woocommerce' ); ?></div>
	<div class="panel-body">
		<table class="table">
			<thead>
			<tr>
				<th width="15%"><?php _e( 'Qty', WC_CP_DOMAIN ); ?></th>
				<th><?php _e( 'Product', WC_CP_DOMAIN ); ?></th>
				<th><?php _e( 'Price', WC_CP_DOMAIN ); ?></th>
			</tr>
			</thead>
			<tbody class="wc-cp-config-container"></tbody>
		</table>
	</div>
</div>

<input type="hidden" name="wc_cp_assembly_config_key" value="<?php echo esc_attr( $assembly_config['key'] ); ?>" />