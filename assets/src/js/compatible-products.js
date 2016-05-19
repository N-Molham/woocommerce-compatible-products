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

		// add product to cart click
		$variations_form.on( 'click', '.compatible-product-add-to-cart-link', function ( e ) {
			e.preventDefault();

			// start loading
			var $this = $( this ).button( 'loading' );

			// send AJAX request
			$.post( wc_add_to_cart_params.ajax_url, $this.data( 'args' ), function ( response ) {
				if ( typeof response === 'object' ) {
					// json response
					if ( 'fragments' in response ) {
						// success
						$this.button( 'added' );

						// update mini cart data
						for ( var css_selector in response.fragments ) {
							if ( response.fragments.hasOwnProperty( css_selector ) ) {
								// query the fragment position
								var $selector_element = $( css_selector );
								if ( $selector_element.length ) {
									// replace with the new information
									$selector_element.replaceWith( response.fragments[ css_selector ] );
								}
							}
						}
					} else {
						// error
						$this.button( 'reset' );
						alert( response.data );
					}
				} else {
					// unknown response format
					$this.button( 'reset' );
					console.log( response );
				}
			}, 'json' );
		} );
	} );
})( jQuery, window );