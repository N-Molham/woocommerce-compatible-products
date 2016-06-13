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

		// vars
		var $instructions_btn  = $( '#measuring-instructions-button' ).removeClass( 'hidden' ),
		    $needed_length     = $( '#length_needed' ),
		    init_need_fittings = location.search.indexOf( 'wc-cp-need-fittings=yes' ) > -1;

		// move button to new location
		$( '<tr><td colspan="2"></td></tr>' ).insertAfter( $needed_length.closest( 'tr' ) ).find( 'td' ).append( $instructions_btn );

		// when show compatible products checkbox change
		$variations_form.on( 'change wc-cp-change', '.wc-cp-need-compatible', function ( e ) {
			var $panels = $variations_form.find( '.wc-cp-products-list, .wc-cp-assembly-config' );

			if ( e.target.checked || init_need_fittings ) {
				$panels.removeClass( 'hidden' );
			} else {
				$panels.addClass( 'hidden' );
			}
		} ).trigger( 'wc-cp-change' );

		// when variation changes
		$variations_form.on( 'woocommerce_variation_has_changed', function () {
			// show compatible products by default
			if ( init_need_fittings ) {
				$variations_form.find( '.wc-cp-need-compatible' ).prop( 'checked', true );
				init_need_fittings = false;
			}

			// trigger compatible checkbox checked change
			$variations_form.find( '.wc-cp-need-compatible' ).trigger( 'wc-cp-change' );

			// initialize popovers
			$variations_form.find( '.compatible-product-link' ).popover();
		} );

		// add product to cart click
		$variations_form.on( 'click', '.compatible-product-add-to-cart-link', function ( e ) {
			e.preventDefault();

			// start loading
			var $this        = $( this ).button( 'loading' ),
			    request_data = $this.data( 'args' );

			// set quantity
			$qty_input = $variations_form.find( 'input[name="wc_cp_quantity[' + request_data.variation_id + ']"]' );
			if ( $qty_input.length !== 1 ) {
				request_data.quantity = 1;
			} else {
				request_data.quantity = parseInt( $qty_input.val() );
			}

			// send AJAX request
			$.post( wc_add_to_cart_params.ajax_url, request_data, function ( response ) {
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