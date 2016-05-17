/**
 * Created by nabeel on 5/17/16.
 */
(function ( $, w, undefined ) {
	$( function () {
		var $product_data = $( '#woocommerce-product-data' );
		if ( $product_data.length < 1 ) {
			// skip unrelated js file loading
			return;
		}

		// vars
		var $variable_options = $( '#variable_product_options' );

		// when product variation view loads
		$product_data.on( 'woocommerce_variations_loaded', function () {
			// compatible lists initialization
			$variable_options.find( '.wc-cp-dropdown' ).select2( {
				placeholder       : wc_cp_compatible_products.i18n.placeholder,
				minimumInputLength: 3,
				multiple          : true,
				ajax              : {
					url        : ajaxurl,
					cache      : true,
					dataType   : 'json',
					quietMillis: 250,
					data       : function ( term ) {
						return {
							action  : 'search_compatible_products',
							term    : term,
							security: wc_cp_compatible_products.search_nonce
						};
					},
					results    : function ( data ) {
						var parsed_results = [];

						// build the result set
						for ( var pid in data ) {
							if ( data.hasOwnProperty( pid ) ) {
								parsed_results.push( {
									id  : pid,
									text: data[ pid ]
								} );
							}
						}

						// return the final results
						return {
							results: parsed_results
						};
					}
				},
				initSelection     : function ( element, callback ) {
					callback( $( element ).data( 'initial' ) );
				},
				escapeMarkup      : function ( item_text ) {
					// don't escape HTML
					return item_text;
				}
			} );
		} );
	} );
})( jQuery, window );