/**
 * Created by nabeel on 5/18/16.
 */
(function ( $, w, undefined ) {
	$( function () {
		var $variations_form = $( '.variations_form' );
		if ( $variations_form.length < 1 ) {
			// skip as the variation form not found
			return;
		}

		// when user selects a variation
		$variations_form.on( 'woocommerce_variation_has_changed', function () {
			$variations_form.find( '.compatible-product-link' ).popover();
		} );
	} );
})( jQuery, window );