/**
 * Created by nabeel on 5/18/16.
 */
(function ( $, win, undefined ) {
	$( function () {
		var $variations_form = $( '.variations_form' );
		if ( $variations_form.length < 1 ) {
			// skip as the variation form not found
			return;
		}

		// vars
		var product_data        = $variations_form.data(),
		    $variation_id       = $variations_form.find( 'input[name=variation_id]' ),
		    $instructions_btn   = $( '#measuring-instructions-button' ).removeClass( 'hidden' ),
		    $price_calculator   = $( '#price_calculator' ),
		    $calculated_amount  = $price_calculator.find( '#length_needed' ),
		    init_need_fittings  = location.search.indexOf( 'wc-cp-need-fittings=yes' ) > -1,
		    current_config      = null,
		    compatible_products = null;

		// Update assembly configuration
		$variations_form.on( 'wc-cp-update-assembly-config', function () {
			// items holder
			var config_items = [];

			// Assembly configuration
			if ( current_config && 'parts' in current_config ) {
				var parts = current_config.parts;
				for ( i = 0, len = parts.length; i < len; i++ ) {
					config_items.push( setup_assembly_config_item( parts[ i ] ) );
				}
			}

			// main product item
			config_items.push( setup_assembly_config_item( product_data, true ) );

			// populate config table
			var rows = [];
			for ( var i = 0, len = config_items.length; i < len; i++ ) {
				var config_item = config_items[ i ];

				if ( config_item.is_assembly ) {
					// append remove button to name
					config_item.name += '&nbsp;&nbsp;<a href="javascript:void(0)" class="wc-cp-remove-assembly" ' +
						'data-pid="' + config_item.data_obj.product_id + '" data-vid="' + config_item.data_obj.variation_id + '">' +
						'<i class="fa fa-times"></i></a>';
				}

				rows.push( '<tr><td class="qty">' + config_item.qty + '</td>' +
					'<td class="name">' + config_item.name + '</td>' +
					'<td class="price">' + config_item.price + '</td></tr>' );
			}
			$variations_form.find( '.wc-cp-config-container' ).html( rows.join( '' ) );
		} );

		/* Assembly configuration item remove button clicked*/
		$variations_form.on( 'click', '.wc-cp-remove-assembly', function () {
			var $this        = $( this ),
			    request_data = $this.data();

			// disable button
			$this.prop( 'disabled', true );

			// additional props
			request_data.action       = 'remove_compatible_product_from_assembly';
			request_data.security     = wc_compatible_products_params.assembly_remove_nonce;
			request_data.assembly_key = current_config.key;

			$.post( wc_add_to_cart_params.ajax_url, request_data, function ( response ) {
				if ( 'success' in response ) {
					if ( response.success ) {
						// update the configuration object
						current_config = response.data;

						// trigger assembly configuration update
						$variations_form.trigger( 'wc-cp-update-assembly-config' );
					} else {
						alert( response.data );
					}
				} else {
					console.log( response );
				}
			}, 'json' ).always( function () {
				// re-enable button
				$this.prop( 'disabled', false );
			} );
		} );

		// when price calculator change
		$variations_form.on( 'wc-measurement-price-calculator-update', function () {
			if ( null !== current_config ) {
				$.post( wc_add_to_cart_params.ajax_url, {
					action      : 'update_assembly_amount',
					amount      : $calculated_amount.val(),
					assembly_key: current_config.key,
					security    : wc_compatible_products_params.assembly_quantity_nonce
				}, function ( response ) {
					if ( response.success ) {
						// trigger assembly configuration update
						$variations_form.trigger( 'wc-cp-update-assembly-config' );
					} else {
						alert( response.data );
					}
				}, 'json' );
			}
		} );

		// move button to new location
		$( '<tr><td colspan="2"></td></tr>' ).insertAfter( $calculated_amount.closest( 'tr' ) ).find( 'td' ).append( $instructions_btn );

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

			// set the new compatible products list
			compatible_products = $variations_form.find( '.wc-cp-products-list' ).data( 'products' );

			// set the current assembly configuration
			current_config = $variations_form.find( '.wc-cp-assembly-config' ).data( 'config' );

			if ( win.history && win.history.replaceState ) {
				// change the URL with the assembly key
				var search_replace = location.search;
				if ( search_replace.indexOf( 'wc_cp_assembly_key' ) == -1 ) {
					search_replace += search_replace.indexOf( '?' ) == -1 ? '?' : '&';
					search_replace += 'wc_cp_assembly_key=' + current_config.key;
					history.replaceState( null, null, search_replace );
				}
			}

			// trigger assembly configuration update
			$variations_form.trigger( 'wc-cp-update-assembly-config' );
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

						// update the current config with the updated info
						current_config = response.data;

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

		/**
		 * Setup assembly configuration table item object
		 *
		 * @param {Object} item_data
		 * @param {Boolean} main_product
		 *
		 * @returns {{qty: number, name: string, price: string}}
		 */
		function setup_assembly_config_item( item_data, main_product ) {
			main_product = main_product || false;

			var part_item = {
				data_obj   : item_data,
				qty        : 0,
				name       : '-',
				price      : '',
				is_assembly: true // is assembly part or not
			};

			if ( main_product ) {
				var qty_unit = wc_price_calculator_params.product_measurement_unit ? wc_price_calculator_params.product_measurement_unit : '';
				if ( '' === qty_unit && wc_price_calculator_params.product_price_unit ) {
					qty_unit = wc_price_calculator_params.product_price_unit;
				}

				// item quantity
				part_item.qty = $calculated_amount.val();
				part_item.qty = parseFloat( part_item.qty > 0 ? part_item.qty : '0' );
				if ( 0 === part_item.qty && current_config ) {
					// use current configuration quantity
					part_item.qty = current_config.quantity;
					$calculated_amount.val( part_item.qty );

					// trigger calculator change
					$variations_form.trigger( 'wc-measurement-price-calculator-update' );
				}

				// append measure unit
				part_item.qty = part_item.qty.toString() + ' ' + qty_unit;

				// item price
				part_item.price = '<span class="amount">' + $price_calculator.find( '.product_price .amount' ).text() + '</span>';

				// is assembly item or not
				part_item.is_assembly = false;

				// fetch name
				if ( 'product_variations' in item_data ) {
					for ( var i = 0, len = item_data.product_variations.length; i < len; i++ ) {
						var variation = item_data.product_variations[ i ];
						if ( variation.variation_id.toString() === $variation_id.val() ) {
							// set product name
							part_item.name = variation.variation_name;
							break;
						}
					}
				}
			} else {
				// other parts ( fittings )
				for ( var i = 0, len = compatible_products.length; i < len; i++ ) {
					var cp = compatible_products[ i ];

					if ( 'variation_id' in item_data && 'variation_id' in cp && item_data.variation_id !== cp.variation_id ) {
						// skip variation product if not found
						continue;
					} else if ( cp.product_id !== item_data.product_id ) {
						// skip normal product if not found
						continue;
					}

					// setup item data
					part_item.qty   = item_data.quantity;
					part_item.price = wc_format_price( cp.price * item_data.quantity );
					part_item.name  = cp.text;
				}
			}

			return part_item;
		}
	} );

	function wc_format_price( price ) {
		var formatted_price = '',
		    num_decimals    = wc_compatible_products_params.woocommerce_price_num_decimals,
		    currency_pos    = wc_compatible_products_params.woocommerce_currency_pos,
		    currency_symbol = wc_compatible_products_params.woocommerce_currency_symbol;

		function wc_price_decimals_sep( price ) {
			return price.replace( new RegExp( d( wc_compatible_products_params.woocommerce_price_decimal_sep, "/" ) + "0+$" ), "" );
		}

		function d( a, b ) {
			return (a + "").replace( new RegExp( "[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\" + (b || "") + "-]", "g" ), "\\$&" )
		}

		function c( a, b, c, d ) {
			a     = (a + "").replace( /[^0-9+\-Ee.]/g, "" );
			var e = isFinite( +a ) ? +a : 0, f = isFinite( +b ) ? Math.abs( b ) : 0, g = "undefined" == typeof d ? "," : d, h = "undefined" == typeof c ? "." : c, i = "", j = function ( a, b ) {
				var c = Math.pow( 10, b );
				return "" + Math.round( a * c ) / c
			};
			return i = (f ? j( e, f ) : "" + Math.round( e )).split( "." ), i[ 0 ].length > 3 && (i[ 0 ] = i[ 0 ].replace( /\B(?=(?:\d{3})+(?!\d))/g, g )), (i[ 1 ] || "").length < f && (i[ 1 ] = i[ 1 ] || "", i[ 1 ] += new Array( f - i[ 1 ].length + 1 ).join( "0" )), i.join( h );
		}

		switch ( price = c( price, num_decimals, wc_compatible_products_params.woocommerce_price_decimal_sep, wc_compatible_products_params.woocommerce_price_thousand_sep ), "yes" === wc_compatible_products_params.woocommerce_price_trim_zeros && num_decimals > 0 && (price = wc_price_decimals_sep( price )), currency_pos ) {
			case 'left':
				formatted_price = '<span class="amount">' + currency_symbol + price + "</span>";
				break;
			case 'right':
				formatted_price = '<span class="amount">' + price + currency_symbol + "</span>";
				break;
			case 'left_space':
				formatted_price = '<span class="amount">' + currency_symbol + "&nbsp;" + price + "</span>";
				break;
			case 'right_space':
				formatted_price = '<span class="amount">' + price + "&nbsp;" + currency_symbol + "</span>"
		}
		return formatted_price;
	}

})( jQuery, window );