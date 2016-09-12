/**
 * Created by nabeel on 5/18/16.
 */
(function ( $, win, undefined ) {
	"use strict";

	$( function () {
		var $variations_form = $( '.variations_form' );
		if ( $variations_form.length < 1 ) {
			// skip as the variation form not found
			return;
		}

		// vars
		var $window               = $( win ),
		    product_data          = $variations_form.data(),
		    $variation_id         = $variations_form.find( 'input[name=variation_id]' ),
		    $variation_attributes = $variations_form.find( '.variations' ),
		    $assembly_subtotal    = $variations_form.find( '.wc-cp-assemblies-subtotal-amount' ),
		    $instructions_btn     = $( '#measuring-instructions-button' ).removeClass( 'hidden' ),
		    $price_calculator     = $( '#price_calculator' ),
		    $calculated_amount    = $price_calculator.find( '#length_needed' ),
		    init_need_fittings    = location.search.indexOf( 'wc-cp-need-fittings=yes' ) > -1,
		    edit_assembly_mod     = location.search.indexOf( 'wc_cp_edit_assembly=yes' ) > -1,
		    $current_specs_panel  = null,
		    current_config        = null,
		    compatible_products   = null;

		if ( wc_compatible_products_params.is_assembly_product_page ) {
			init_need_fittings = true;
		}

		// Update assembly configuration
		$variations_form.on( 'wc-cp-update-assembly-config', function ( e, $trigger_button ) {
			// items holder
			var config_items = [],
			    subtotal     = 0;

			// clear any previous selections
			$variations_form.find( '.wc-cp-products-list' ).trigger( 'wc-cp-change-state' );

			// Assembly configuration
			if ( current_config && 'parts' in current_config && current_config.parts.length ) {
				// sort parts by box order
				var parts = current_config.parts.sort( function ( item_a, item_b ) {
					if ( item_a.box_order > item_b.box_order ) {
						return 1;
					}

					if ( item_b.box_order > item_a.box_order ) {
						return -1;
					}

					return 0;
				} );

				for ( i = 0, len = parts.length; i < len; i++ ) {
					// vars
					var part_data            = parts[ i ],
					    part_pid             = part_data.variation_id ? part_data.variation_id : part_data.product_id,
					    $part_trigger_button = $trigger_button;

					// find related add-to-assembly button
					if ( undefined === $trigger_button || part_pid != $trigger_button.data( 'product' ) ) {
						// query it instead
						$part_trigger_button = $variations_form.find( '.compatible-product-add-to-cart-link[data-product="' + part_pid + '"]:not(.disabled):first()' );
					}

					if ( $part_trigger_button.length ) {
						// trigger box state change
						$part_trigger_button.closest( '.wc-cp-products-list' ).trigger( 'wc-cp-change-state', [ $part_trigger_button ] );
					}

					// get item setup
					config_items.push( setup_assembly_config_item( part_data ) );
				}
			}

			// get main product item setup0
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

				// build row data
				rows.push( '<tr><td class="qty">' + config_item.qty + '</td>' +
					'<td class="name">' + config_item.name + '</td>' +
					'<td class="price">' + config_item.price + '</td></tr>' );

				subtotal += config_item.total;
			}
			$variations_form.find( '.wc-cp-config-container' ).html( rows.join( '' ) );

			// assembly subtotal amount
			$assembly_subtotal.html( wc_format_price( subtotal ) );
		} );

		// when price calculator change
		$variations_form.on( 'wc-measurement-price-calculator-update', function () {
			if ( current_config ) {
				$.post( wc_add_to_cart_params.ajax_url, {
					action      : 'update_assembly_amount',
					amount      : $calculated_amount.val(),
					assembly_key: current_config.key,
					security    : wc_compatible_products_params.assembly_quantity_nonce
				}, function ( response ) {
					if ( 'success' in response ) {
						if ( response.success ) {
							// trigger assembly configuration update
							$variations_form.trigger( 'wc-cp-update-assembly-config' );
						} else {
							alert( response.data );
						}
					}
				}, 'json' );
			}
		} );

		// move button to new location
		$( '<tr><td colspan="2"></td></tr>' ).insertAfter( $calculated_amount.closest( 'tr' ) ).find( 'td' ).append( $instructions_btn );

		// when show compatible products checkbox change
		$variations_form.on( 'change wc-cp-change', '.wc-cp-need-compatible', function ( e ) {
			// assembly panels
			var $panels = $variations_form.find( '.wc-cp-products-list, .wc-cp-assembly-config' );

			if ( e.target.checked || init_need_fittings ) {
				$panels.removeClass( 'hidden' );
			} else {
				$panels.addClass( 'hidden' );
			}

			var $assembly_boxes = $panels.filter( '.wc-cp-products-list' );
			if ( $assembly_boxes.length < 2 ) {
				// set first box title
				$assembly_boxes.attr( 'data-order', 1 ).find( '.panel-heading' ).text( wc_compatible_products_params.labels.assembly_box_1 );

				// clone it after it
				$assembly_boxes.clone().attr( 'data-order', 2 ).insertAfter( $assembly_boxes )
				// and set the new title
				.find( '.panel-heading' ).text( wc_compatible_products_params.labels.assembly_box_2 );
			}

			if ( $current_specs_panel ) {
				$current_specs_panel.remove();
			}

			// move specifications panel location after the attributes table
			var $variation_specs_panel = $variations_form.find( '.panel-specifications' );
			$current_specs_panel       = $variation_specs_panel.clone().insertAfter( $variation_attributes );
			$variation_specs_panel.remove();
		} ).trigger( 'wc-cp-change' );

		// when variation changes
		$variations_form.on( 'woocommerce_variation_has_changed', function () {
			// initialize popovers
			$variations_form.find( '.compatible-product-link' ).popover();

			// set the new compatible products list
			compatible_products = $variations_form.find( '.wc-cp-products-list' ).data( 'products' );

			// force reload to initialize quantity input
			$window.trigger( 'vc_reload' );

			// set the current assembly configuration
			current_config = $variations_form.find( '.wc-cp-assembly-config' ).data( 'config' );
			if ( current_config ) {
				// set amount new value
				$calculated_amount.val( current_config.quantity );

				// calculator update
				$variations_form.trigger( 'wc-measurement-price-calculator-update' );

				if ( current_config.parts && current_config.parts.length ) {
					init_need_fittings = true;
				}

				// Update current assembly
				// update_query_string_param( 'wc_cp_assembly_key', current_config.key );
			}

			// show compatible products by default
			if ( init_need_fittings ) {
				$variations_form.find( '.wc-cp-need-compatible' ).prop( 'checked', true );
				init_need_fittings = false;
			}

			// trigger compatible checkbox checked change
			$variations_form.find( '.wc-cp-need-compatible' ).trigger( 'wc-cp-change' );

			// trigger assembly configuration update
			$variations_form.trigger( 'wc-cp-update-assembly-config' );

			// assembly mode on
			if ( edit_assembly_mod ) {
				// disable any validations changes for now
				$variation_attributes.addClass( 'hidden' );

				$variations_form
				// change add to cart button functionality
				.find( ':input:submit' ).addClass( 'update-assembly' ).text( wc_compatible_products_params.labels.edit_assembly )
				// append update mark
				.parent().append( '<input type="hidden" name="wc_cp_update_assembly" value="' + current_config.key + '" />' );
			}
		} );

		// add product to cart click
		$variations_form.on( 'click', '.compatible-product-add-to-cart-link', function ( e ) {
			e.preventDefault();

			// skip disabled buttons
			if ( e.currentTarget.className.indexOf( 'disabled' ) > -1 ) {
				return;
			}

			// start loading
			var $this        = $( this ).button( 'loading' ),
			    request_data = $this.data( 'args' );

			// set quantity
			request_data.quantity  = 1;
			request_data.box_order = $this.closest( '.wc-cp-products-list' ).attr( 'data-order' );

			// send AJAX request
			$.post( wc_add_to_cart_params.ajax_url, request_data, function ( response ) {
				if ( typeof response === 'object' ) {
					// json response
					if ( response.success ) {
						// update the current config with the updated info
						current_config = response.data;

						// trigger assembly configuration update
						$variations_form.trigger( 'wc-cp-update-assembly-config', [ $this ] );
					} else {
						// error
						$this.button( 'reset' );
						console.log( response.data );
					}
				} else {
					// unknown response format
					$this.button( 'reset' );
					console.log( response );
				}
			}, 'json' );
		} );

		// add product to cart click
		$variations_form.on( 'click', '.compatible-product-remove-from-assembly-link', function ( e ) {
			e.preventDefault();

			// skip disabled buttons
			if ( e.currentTarget.className.indexOf( 'hidden' ) > -1 ) {
				return;
			}

			// start loading
			var $this        = $( this ).button( 'loading' ),
			    request_data = $this.data( 'args' );

			// remove from assembly
			var selector_attr = request_data.variation_id ? 'data-vid=' + request_data.variation_id : 'data-pid=' + request_data.product_id;

			// remove mark class
			$this.siblings( '.compatible-product-add-to-cart-link' ).removeClass( 'product-added' );

			// trigger remove event
			$variations_form.find( '.wc-cp-remove-assembly[' + selector_attr + ']' ).trigger( 'wc-cp-remove' );
		} );

		// Assembly configuration item remove button clicked
		$variations_form.on( 'click wc-cp-remove', '.wc-cp-remove-assembly', function () {
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

		// compatible products boxes state change
		$variations_form.on( 'wc-cp-change-state', '.wc-cp-products-list', function ( e, $button ) {
			// this panel
			var $panel = $( this );

			// reset all panel button
			var $panel_buttons = $panel.find( '.compatible-product-add-to-cart-link' ).button( 'reset' ).removeClass( 'disabled product-added' );

			// hide any remove buttons
			$panel.find( '.compatible-product-remove-from-assembly-link' ).addClass( 'hidden' ).button( 'reset' );

			if ( $button ) {
				// switch label
				$button.button( 'added' )
				// mark as added
				.addClass( 'product-added' );

				// show up the related remove button
				$button.siblings( '.compatible-product-remove-from-assembly-link' ).removeClass( 'hidden' );

				// disable all other products buttons on this list
				$panel_buttons.not( '.product-added' ).addClass( 'disabled' );
			}
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
				raw_qty    : 0,
				qty        : '',
				name       : '-',
				price      : '',
				raw_price  : 0,
				total      : 0,
				is_assembly: true // is assembly part or not
			};

			if ( main_product ) {
				var qty_unit = wc_price_calculator_params.product_measurement_unit ? wc_price_calculator_params.product_measurement_unit : '';
				if ( '' === qty_unit && wc_price_calculator_params.product_price_unit ) {
					qty_unit = wc_price_calculator_params.product_price_unit;
				}

				// item quantity
				part_item.raw_qty = $calculated_amount.val();
				part_item.raw_qty = parseFloat( part_item.raw_qty > 0 ? part_item.raw_qty : '0' );
				if ( 0 === part_item.raw_qty && current_config ) {
					// use current configuration quantity
					part_item.raw_qty = current_config.quantity;
					$calculated_amount.val( part_item.raw_qty );

					// trigger calculator change
					$variations_form.trigger( 'wc-measurement-price-calculator-update' );
				}

				// item price
				part_item.price     = $price_calculator.find( '.product_price .amount' ).text();
				part_item.raw_price = parseFloat( part_item.price.replace( wc_compatible_products_params.woocommerce_currency_symbol, '' ) );
				part_item.total     = part_item.raw_price;

				// append measure unit formatted
				part_item.qty = part_item.raw_qty.toString() + ' ' + qty_unit;

				// item price formatted
				part_item.price = '<span class="amount">' + part_item.price + '</span>';

				// is assembly item or not
				part_item.is_assembly = false;

				// fetch name
				if ( 'product_variations' in item_data ) {
					//noinspection JSDuplicatedDeclaration
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
				//noinspection JSDuplicatedDeclaration
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
					part_item.raw_qty = item_data.quantity;
					part_item.qty     = number_format( item_data.quantity );
					part_item.total   = cp.price * item_data.quantity;
					part_item.price   = wc_format_price( part_item.total );
					part_item.name    = cp.text;
				}
			}

			// clear escaped tags
			part_item.name = part_item.name.replace( /&lt;.+&gt;/, '' ).replace( /\s+/g, ' ' );

			return part_item;
		}
	} );

	function update_query_string_param( key, value ) {
		var baseUrl        = [ location.protocol, '//', location.host, location.pathname ].join( '' ),
		    urlQueryString = document.location.search,
		    newParam       = key + '=' + value,
		    params         = '?' + newParam;

		// If the "search" string exists, then build params from it
		if ( urlQueryString ) {
			var keyRegex = new RegExp( '([\?&])' + key + '[^&]*' );
			// If param exists already, update it
			if ( urlQueryString.match( keyRegex ) !== null ) {
				params = urlQueryString.replace( keyRegex, "$1" + newParam );
			} else { // Otherwise, add it to end of query string
				params = urlQueryString + '&' + newParam;
			}
		}
		window.history.replaceState( null, null, baseUrl + params );
	}

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

	function number_format( number, decimals, decPoint, thousandsSep ) {

		number   = (number + '').replace( /[^0-9+\-Ee.]/g, '' );
		var n    = !isFinite( +number ) ? 0 : +number,
		    prec = !isFinite( +decimals ) ? 0 : Math.abs( decimals ),
		    sep  = (typeof thousandsSep === 'undefined') ? ',' : thousandsSep,
		    dec  = (typeof decPoint === 'undefined') ? '.' : decPoint,
		    s;

		var toFixedFix = function ( n, prec ) {
			var k = Math.pow( 10, prec );
			return '' + (Math.round( n * k ) / k)
				.toFixed( prec )
		};

		s = (prec ? toFixedFix( n, prec ) : '' + Math.round( n )).split( '.' );
		if ( s[ 0 ].length > 3 ) {
			s[ 0 ] = s[ 0 ].replace( /\B(?=(?:\d{3})+(?!\d))/g, sep );
		}
		if ( (s[ 1 ] || '').length < prec ) {
			s[ 1 ] = s[ 1 ] || '';
			s[ 1 ] += new Array( prec - s[ 1 ].length + 1 ).join( '0' );
		}

		return s.join( dec );
	}

})( jQuery, window );