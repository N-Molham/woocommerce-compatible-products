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
		var product_data          = $variations_form.data(),
		    $variations           = $variations_form.find( '.variations' ),
		    $instructions_btn     = $( '#measuring-instructions-button' ).removeClass( 'hidden' ),
		    $needed_length        = $( '#length_needed' ),
		    $price_calculator     = $( '#price_calculator' ),
		    $calculated_amount    = $price_calculator.find( '#length_needed' ),
		    init_need_fittings    = location.search.indexOf( 'wc-cp-need-fittings=yes' ) > -1,
		    current_configuration = null;

		// Update assembly configuration
		$variations_form.on( 'wc-cp-update-assembly-config', function () {
			// items holder
			var config_items = [];

			// main product item
			config_items.push( {
				qty  : $calculated_amount.val(),
				name : '',
				price: $price_calculator.find( '.total_price .amount' ).text()
			} );

			console.log( current_configuration );
		} );

		// when price calculator change
		$variations_form.on( 'wc-measurement-price-calculator-update', function () {
			// trigger assembly configuration update
			$variations_form.trigger( 'wc-cp-update-assembly-config' );
		} );

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
					if ( response.success ) {
						// success
						$this.button( 'added' );

						current_configuration = response.data;

						// trigger assembly configuration update
						$variations_form.trigger( 'wc-cp-update-assembly-config' );
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

		function append_config_item( name, qty, price ) {
			console.log( arguments );
		}
	} );
})( jQuery, window );