/**
 * Created by nabeel on 5/18/16.
 */
!function(a,b,c){function d(a){function b(a){return a.replace(new RegExp(c(wc_compatible_products_params.woocommerce_price_decimal_sep,"/")+"0+$"),"")}function c(a,b){return(a+"").replace(new RegExp("[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\"+(b||"")+"-]","g"),"\\$&")}function d(a,b,c,d){a=(a+"").replace(/[^0-9+\-Ee.]/g,"");var e=isFinite(+a)?+a:0,f=isFinite(+b)?Math.abs(b):0,g="undefined"==typeof d?",":d,h="undefined"==typeof c?".":c,i="",j=function(a,b){var c=Math.pow(10,b);return""+Math.round(a*c)/c};return i=(f?j(e,f):""+Math.round(e)).split("."),i[0].length>3&&(i[0]=i[0].replace(/\B(?=(?:\d{3})+(?!\d))/g,g)),(i[1]||"").length<f&&(i[1]=i[1]||"",i[1]+=new Array(f-i[1].length+1).join("0")),i.join(h)}var e="",f=wc_compatible_products_params.woocommerce_price_num_decimals,g=wc_compatible_products_params.woocommerce_currency_pos,h=wc_compatible_products_params.woocommerce_currency_symbol;switch(a=d(a,f,wc_compatible_products_params.woocommerce_price_decimal_sep,wc_compatible_products_params.woocommerce_price_thousand_sep),"yes"===wc_compatible_products_params.woocommerce_price_trim_zeros&&f>0&&(a=b(a)),g){case"left":e='<span class="amount">'+h+a+"</span>";break;case"right":e='<span class="amount">'+a+h+"</span>";break;case"left_space":e='<span class="amount">'+h+"&nbsp;"+a+"</span>";break;case"right_space":e='<span class="amount">'+a+"&nbsp;"+h+"</span>"}return e}a(function(){/**
		 * Setup assembly configuration table item object
		 *
		 * @param {Object} item_data
		 * @param {Boolean} main_product
		 *
		 * @returns {{qty: number, name: string, price: string}}
		 */
function b(a,b){b=b||!1;var e={data_obj:a,qty:0,name:"-",price:"",is_assembly:!0};if(b){var g=wc_price_calculator_params.product_measurement_unit?wc_price_calculator_params.product_measurement_unit:"";
// fetch name
if(""===g&&wc_price_calculator_params.product_price_unit&&(g=wc_price_calculator_params.product_price_unit),
// item quantity
e.qty=i.val(),e.qty=parseFloat(e.qty>0?e.qty:"0"),0===e.qty&&k&&(
// use current configuration quantity
e.qty=k.quantity,i.val(e.qty),
// trigger calculator change
c.trigger("wc-measurement-price-calculator-update")),
// append measure unit
e.qty=e.qty.toString()+" "+g,
// item price
e.price='<span class="amount">'+h.find(".product_price .amount").text()+"</span>",
// is assembly item or not
e.is_assembly=!1,"product_variations"in a)for(var j=0,m=a.product_variations.length;m>j;j++){var n=a.product_variations[j];if(n.variation_id.toString()===f.val()){
// set product name
e.name=n.variation_name;break}}}else
// other parts ( fittings )
for(var j=0,m=l.length;m>j;j++){var o=l[j];"variation_id"in a&&"variation_id"in o&&a.variation_id!==o.variation_id||o.product_id===a.product_id&&(
// setup item data
e.qty=a.quantity,e.price=d(o.price*a.quantity),e.name=o.text)}return e}var c=a(".variations_form");if(!(c.length<1)){
// vars
var e=c.data(),f=c.find("input[name=variation_id]"),g=a("#measuring-instructions-button").removeClass("hidden"),h=a("#price_calculator"),i=h.find("#length_needed"),j=location.search.indexOf("wc-cp-need-fittings=yes")>-1,k=null,l=null;
// Update assembly configuration
c.on("wc-cp-update-assembly-config",function(){
// items holder
var a=[];
// Assembly configuration
if(k&&"parts"in k){var d=k.parts;for(g=0,h=d.length;h>g;g++)a.push(b(d[g]))}
// main product item
a.push(b(e,!0));for(var f=[],g=0,h=a.length;h>g;g++){var i=a[g];i.is_assembly&&(
// append remove button to name
i.name+='&nbsp;&nbsp;<a href="javascript:void(0)" class="wc-cp-remove-assembly" data-pid="'+i.data_obj.product_id+'" data-vid="'+i.data_obj.variation_id+'"><i class="fa fa-times"></i></a>'),f.push('<tr><td class="qty">'+i.qty+'</td><td class="name">'+i.name+'</td><td class="price">'+i.price+"</td></tr>")}c.find(".wc-cp-config-container").html(f.join(""))}),/* Assembly configuration item remove button clicked*/
c.on("click",".wc-cp-remove-assembly",function(){var b=a(this),d=b.data();
// disable button
b.prop("disabled",!0),
// additional props
d.action="remove_compatible_product_from_assembly",d.security=wc_compatible_products_params.assembly_remove_nonce,d.assembly_key=k.key,a.post(wc_add_to_cart_params.ajax_url,d,function(a){"success"in a?a.success?(k=a.data,c.trigger("wc-cp-update-assembly-config")):alert(a.data):console.log(a)},"json").always(function(){
// re-enable button
b.prop("disabled",!1)})}),
// when price calculator change
c.on("wc-measurement-price-calculator-update",function(){k&&a.post(wc_add_to_cart_params.ajax_url,{action:"update_assembly_amount",amount:i.val(),assembly_key:k.key,security:wc_compatible_products_params.assembly_quantity_nonce},function(a){a.success?
// trigger assembly configuration update
c.trigger("wc-cp-update-assembly-config"):alert(a.data)},"json")}),
// move button to new location
a('<tr><td colspan="2"></td></tr>').insertAfter(i.closest("tr")).find("td").append(g),
// when show compatible products checkbox change
c.on("change wc-cp-change",".wc-cp-need-compatible",function(a){var b=c.find(".wc-cp-products-list, .wc-cp-assembly-config");a.target.checked||j?b.removeClass("hidden"):b.addClass("hidden")}).trigger("wc-cp-change"),
// when variation changes
c.on("woocommerce_variation_has_changed",function(){
// show compatible products by default
j&&(c.find(".wc-cp-need-compatible").prop("checked",!0),j=!1),
// trigger compatible checkbox checked change
c.find(".wc-cp-need-compatible").trigger("wc-cp-change"),
// initialize popovers
c.find(".compatible-product-link").popover(),l=c.find(".wc-cp-products-list").data("products"),k=c.find(".wc-cp-assembly-config").data("config"),i.val(k.quantity),c.trigger("wc-cp-update-assembly-config")}),
// add product to cart click
c.on("click",".compatible-product-add-to-cart-link",function(b){b.preventDefault();
// start loading
var d=a(this).button("loading"),e=d.data("args"),f=c.find('input[name="wc_cp_quantity['+e.variation_id+']"]');1!==f.length?e.quantity=1:e.quantity=parseInt(f.val()),
// send AJAX request
a.post(wc_add_to_cart_params.ajax_url,e,function(a){"object"==typeof a?
// json response
a.success?(
// success
d.button("added"),k=a.data,f.val(1),c.trigger("wc-cp-update-assembly-config")):(d.button("reset"),alert(a.data)):(d.button("reset"),console.log(a))},"json")})}})}(jQuery,window);