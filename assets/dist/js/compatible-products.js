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
function c(a,b){b=b||!1;var c={data_obj:a,qty:0,name:"-",price:"",is_assembly:!0};if(b){var f=wc_price_calculator_params.product_measurement_unit?wc_price_calculator_params.product_measurement_unit:"";
// fetch name
if(""===f&&wc_price_calculator_params.product_price_unit&&(f=wc_price_calculator_params.product_price_unit),
// item quantity
c.qty=j.val(),c.qty=parseFloat(c.qty>0?c.qty:"0"),0===c.qty&&l&&(
// use current configuration quantity
c.qty=l.quantity,j.val(c.qty),
// trigger calculator change
e.trigger("wc-measurement-price-calculator-update")),
// append measure unit
c.qty=c.qty.toString()+" "+f,
// item price
c.price='<span class="amount">'+i.find(".product_price .amount").text()+"</span>",
// is assembly item or not
c.is_assembly=!1,"product_variations"in a)for(var h=0,k=a.product_variations.length;k>h;h++){var n=a.product_variations[h];if(n.variation_id.toString()===g.val()){
// set product name
c.name=n.variation_name;break}}}else
// other parts ( fittings )
for(var h=0,k=m.length;k>h;h++){var o=m[h];"variation_id"in a&&"variation_id"in o&&a.variation_id!==o.variation_id||o.product_id===a.product_id&&(
// setup item data
c.qty=a.quantity,c.price=d(o.price*a.quantity),c.name=o.text)}return c}var e=a(".variations_form");if(!(e.length<1)){
// vars
var f=e.data(),g=e.find("input[name=variation_id]"),h=a("#measuring-instructions-button").removeClass("hidden"),i=a("#price_calculator"),j=i.find("#length_needed"),k=location.search.indexOf("wc-cp-need-fittings=yes")>-1,l=null,m=null;
// Update assembly configuration
e.on("wc-cp-update-assembly-config",function(){
// items holder
var a=[];if(null!==l){
// Assembly configuration
if(l&&"parts"in l){var b=l.parts;for(g=0,h=b.length;h>g;g++)a.push(c(b[g]))}
// main product item
a.push(c(f,!0));for(var d=[],g=0,h=a.length;h>g;g++){var i=a[g];i.is_assembly&&(
// append remove button to name
i.name+='&nbsp;&nbsp;<a href="javascript:void(0)" class="wc-cp-remove-assembly" data-pid="'+i.data_obj.product_id+'" data-vid="'+i.data_obj.variation_id+'"><i class="fa fa-times"></i></a>'),d.push('<tr><td class="qty">'+i.qty+'</td><td class="name">'+i.name+'</td><td class="price">'+i.price+"</td></tr>")}e.find(".wc-cp-config-container").html(d.join(""))}}),/* Assembly configuration item remove button clicked*/
e.on("click",".wc-cp-remove-assembly",function(){var b=a(this),c=b.data();
// disable button
b.prop("disabled",!0),
// additional props
c.action="remove_compatible_product_from_assembly",c.security=wc_compatible_products_params.assembly_remove_nonce,c.assembly_key=l.key,a.post(wc_add_to_cart_params.ajax_url,c,function(a){"success"in a?a.success?(l=a.data,e.trigger("wc-cp-update-assembly-config")):alert(a.data):console.log(a)},"json").always(function(){
// re-enable button
b.prop("disabled",!1)})}),
// when price calculator change
e.on("wc-measurement-price-calculator-update",function(){
// trigger assembly configuration update
e.trigger("wc-cp-update-assembly-config")}),
// move button to new location
a('<tr><td colspan="2"></td></tr>').insertAfter(j.closest("tr")).find("td").append(h),
// when show compatible products checkbox change
e.on("change wc-cp-change",".wc-cp-need-compatible",function(a){var b=e.find(".wc-cp-products-list, .wc-cp-assembly-config");a.target.checked||k?b.removeClass("hidden"):b.addClass("hidden")}).trigger("wc-cp-change"),
// when variation changes
e.on("woocommerce_variation_has_changed",function(){if(
// show compatible products by default
k&&(e.find(".wc-cp-need-compatible").prop("checked",!0),k=!1),
// trigger compatible checkbox checked change
e.find(".wc-cp-need-compatible").trigger("wc-cp-change"),
// initialize popovers
e.find(".compatible-product-link").popover(),m=e.find(".wc-cp-products-list").data("products"),l=e.find(".wc-cp-assembly-config").data("config"),b.history&&b.history.replaceState){
// change the URL with the assembly key
var a=location.search;-1==a.indexOf("wc_cp_assembly_key")&&(a+=-1==a.indexOf("?")?"?":"&",a+="wc_cp_assembly_key="+l.key,history.replaceState(null,null,a))}
// trigger assembly configuration update
e.trigger("wc-cp-update-assembly-config")}),
// add product to cart click
e.on("click",".compatible-product-add-to-cart-link",function(b){b.preventDefault();
// start loading
var c=a(this).button("loading"),d=c.data("args");
// set quantity
$qty_input=e.find('input[name="wc_cp_quantity['+d.variation_id+']"]'),1!==$qty_input.length?d.quantity=1:d.quantity=parseInt($qty_input.val()),
// send AJAX request
a.post(wc_add_to_cart_params.ajax_url,d,function(a){"object"==typeof a?
// json response
a.success?(
// success
c.button("added"),l=a.data,e.trigger("wc-cp-update-assembly-config")):(c.button("reset"),alert(a.data)):(c.button("reset"),console.log(a))},"json")})}})}(jQuery,window);