/**
 * Created by nabeel on 5/18/16.
 */
!function(a,b,c){"use strict";function d(a){function b(a){return a.replace(new RegExp(c(wc_compatible_products_params.woocommerce_price_decimal_sep,"/")+"0+$"),"")}function c(a,b){return(a+"").replace(new RegExp("[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\"+(b||"")+"-]","g"),"\\$&")}function d(a,b,c,d){a=(a+"").replace(/[^0-9+\-Ee.]/g,"");var e=isFinite(+a)?+a:0,f=isFinite(+b)?Math.abs(b):0,g="undefined"==typeof d?",":d,h="undefined"==typeof c?".":c,i="",j=function(a,b){var c=Math.pow(10,b);return""+Math.round(a*c)/c};return i=(f?j(e,f):""+Math.round(e)).split("."),i[0].length>3&&(i[0]=i[0].replace(/\B(?=(?:\d{3})+(?!\d))/g,g)),(i[1]||"").length<f&&(i[1]=i[1]||"",i[1]+=new Array(f-i[1].length+1).join("0")),i.join(h)}var e="",f=wc_compatible_products_params.woocommerce_price_num_decimals,g=wc_compatible_products_params.woocommerce_currency_pos,h=wc_compatible_products_params.woocommerce_currency_symbol;switch(a=d(a,f,wc_compatible_products_params.woocommerce_price_decimal_sep,wc_compatible_products_params.woocommerce_price_thousand_sep),"yes"===wc_compatible_products_params.woocommerce_price_trim_zeros&&f>0&&(a=b(a)),g){case"left":e='<span class="amount">'+h+a+"</span>";break;case"right":e='<span class="amount">'+a+h+"</span>";break;case"left_space":e='<span class="amount">'+h+"&nbsp;"+a+"</span>";break;case"right_space":e='<span class="amount">'+a+"&nbsp;"+h+"</span>"}return e}function e(a,b,c,d){a=(a+"").replace(/[^0-9+\-Ee.]/g,"");var e,f=isFinite(+a)?+a:0,g=isFinite(+b)?Math.abs(b):0,h="undefined"==typeof d?",":d,i="undefined"==typeof c?".":c,j=function(a,b){var c=Math.pow(10,b);return""+(Math.round(a*c)/c).toFixed(b)};return e=(g?j(f,g):""+Math.round(f)).split("."),e[0].length>3&&(e[0]=e[0].replace(/\B(?=(?:\d{3})+(?!\d))/g,h)),(e[1]||"").length<g&&(e[1]=e[1]||"",e[1]+=new Array(g-e[1].length+1).join("0")),e.join(i)}a(function(){/**
		 * Setup assembly configuration table item object
		 *
		 * @param {Object} item_data
		 * @param {Boolean} main_product
		 *
		 * @returns {{qty: number, name: string, price: string}}
		 */
function f(a,b){b=b||!1;var c={data_obj:a,raw_qty:0,qty:"",name:"-",price:"",raw_price:0,total:0,is_assembly:!0};if(b){var f=wc_price_calculator_params.product_measurement_unit?wc_price_calculator_params.product_measurement_unit:"";
// fetch name
if(""===f&&wc_price_calculator_params.product_price_unit&&(f=wc_price_calculator_params.product_price_unit),
// item quantity
c.raw_qty=o.val(),c.raw_qty=parseFloat(c.raw_qty>0?c.raw_qty:"0"),0===c.raw_qty&&s&&(
// use current configuration quantity
c.raw_qty=s.quantity,o.val(c.raw_qty),
// trigger calculator change
g.trigger("wc-measurement-price-calculator-update")),
// item price
c.price=n.find(".product_price .amount").text(),c.raw_price=parseFloat(c.price.replace(wc_compatible_products_params.woocommerce_currency_symbol,"")),c.total=c.raw_price,
// append measure unit formatted
c.qty=c.raw_qty.toString()+" "+f,
// item price formatted
c.price='<span class="amount">'+c.price+"</span>",
// is assembly item or not
c.is_assembly=!1,"product_variations"in a)
//noinspection JSDuplicatedDeclaration
for(var h=0,i=a.product_variations.length;i>h;h++){var k=a.product_variations[h];if(k.variation_id.toString()===j.val()){
// set product name
c.name=k.variation_name;break}}}else
// other parts ( fittings )
//noinspection JSDuplicatedDeclaration
for(var h=0,i=t.length;i>h;h++){var l=t[h];"variation_id"in a&&"variation_id"in l&&a.variation_id!==l.variation_id||l.product_id===a.product_id&&(
// setup item data
c.raw_qty=a.quantity,c.qty=e(a.quantity),c.total=l.price*a.quantity,c.price=d(c.total),c.name=l.text)}
// clear escaped tags
return c.name=c.name.replace(/&lt;.+&gt;/,"").replace(/\s+/g," "),c}var g=a(".variations_form");if(!(g.length<1)){
// vars
var h=a(b),i=g.data(),j=g.find("input[name=variation_id]"),k=g.find(".variations"),l=g.find(".wc-cp-assemblies-subtotal-amount"),m=a("#measuring-instructions-button").removeClass("hidden"),n=a("#price_calculator"),o=n.find("#length_needed"),p=location.search.indexOf("wc-cp-need-fittings=yes")>-1,q=location.search.indexOf("wc_cp_edit_assembly=yes")>-1,r=null,s=null,t=null;wc_compatible_products_params.is_assembly_product_page&&(p=!0),
// Update assembly configuration
g.on("wc-cp-update-assembly-config",function(a,b){
// items holder
var e=[],h=0;
// Assembly configuration
if(
// clear any previous selections
g.find(".wc-cp-products-list").trigger("wc-cp-change-state"),s&&"parts"in s&&s.parts.length){
// sort parts by box order
var j=s.parts.sort(function(a,b){return a.box_order>b.box_order?1:b.box_order>a.box_order?-1:0});for(p=0,q=j.length;q>p;p++){
// vars
var k=j[p],m=k.variation_id?k.variation_id:k.product_id,n=b;
// find related add-to-assembly button
c!==b&&m==b.data("product")||(
// query it instead
n=g.find('.compatible-product-add-to-cart-link[data-product="'+m+'"]:not(.disabled):first()')),n.length&&
// trigger box state change
n.closest(".wc-cp-products-list").trigger("wc-cp-change-state",[n]),
// get item setup
e.push(f(k))}}
// get main product item setup0
e.push(f(i,!0));for(var o=[],p=0,q=e.length;q>p;p++){var r=e[p];r.is_assembly&&(
// append remove button to name
r.name+='&nbsp;&nbsp;<a href="javascript:void(0)" class="wc-cp-remove-assembly" data-pid="'+r.data_obj.product_id+'" data-vid="'+r.data_obj.variation_id+'"><i class="fa fa-times"></i></a>'),
// build row data
o.push('<tr><td class="qty">'+r.qty+'</td><td class="name">'+r.name+'</td><td class="price">'+r.price+"</td></tr>"),h+=r.total}g.find(".wc-cp-config-container").html(o.join("")),
// assembly subtotal amount
l.html(d(h))}),
// when price calculator change
g.on("wc-measurement-price-calculator-update",function(){s&&a.post(wc_add_to_cart_params.ajax_url,{action:"update_assembly_amount",amount:o.val(),assembly_key:s.key,security:wc_compatible_products_params.assembly_quantity_nonce},function(a){"success"in a&&(a.success?
// trigger assembly configuration update
g.trigger("wc-cp-update-assembly-config"):alert(a.data))},"json")}),
// move button to new location
a('<tr><td colspan="2"></td></tr>').insertAfter(o.closest("tr")).find("td").append(m),
// when show compatible products checkbox change
g.on("change wc-cp-change",".wc-cp-need-compatible",function(a){
// assembly panels
var b=g.find(".wc-cp-products-list, .wc-cp-assembly-config");a.target.checked||p?b.removeClass("hidden"):b.addClass("hidden");var c=b.filter(".wc-cp-products-list");c.length<2&&(
// set first box title
c.attr("data-order",1).find(".panel-heading").text(wc_compatible_products_params.labels.assembly_box_1),
// clone it after it
c.clone().attr("data-order",2).insertAfter(c).find(".panel-heading").text(wc_compatible_products_params.labels.assembly_box_2)),r&&r.remove();
// move specifications panel location after the attributes table
var d=g.find(".panel-specifications");r=d.clone().insertAfter(k),d.remove()}).trigger("wc-cp-change"),
// when variation changes
g.on("woocommerce_variation_has_changed",function(){
// initialize popovers
g.find(".compatible-product-link").popover(),t=g.find(".wc-cp-products-list").data("products"),h.trigger("vc_reload"),s=g.find(".wc-cp-assembly-config").data("config"),s&&(o.val(s.quantity),g.trigger("wc-measurement-price-calculator-update"),s.parts&&s.parts.length&&(p=!0)),p&&(g.find(".wc-cp-need-compatible").prop("checked",!0),p=!1),g.find(".wc-cp-need-compatible").trigger("wc-cp-change"),g.trigger("wc-cp-update-assembly-config"),q&&(k.addClass("hidden"),g.find(":input:submit").addClass("update-assembly").text(wc_compatible_products_params.labels.edit_assembly).parent().append('<input type="hidden" name="wc_cp_update_assembly" value="'+s.key+'" />'))}),
// add product to cart click
g.on("click",".compatible-product-add-to-cart-link",function(b){
// skip disabled buttons
if(b.preventDefault(),!(b.currentTarget.className.indexOf("disabled")>-1)){
// start loading
var c=a(this).button("loading"),d=c.data("args");
// set quantity
d.quantity=1,d.box_order=c.closest(".wc-cp-products-list").attr("data-order"),
// send AJAX request
a.post(wc_add_to_cart_params.ajax_url,d,function(a){"object"==typeof a?
// json response
a.success?(s=a.data,g.trigger("wc-cp-update-assembly-config",[c])):(c.button("reset"),console.log(a.data)):(c.button("reset"),console.log(a))},"json")}}),
// add product to cart click
g.on("click",".compatible-product-remove-from-assembly-link",function(b){
// skip disabled buttons
if(b.preventDefault(),!(b.currentTarget.className.indexOf("hidden")>-1)){
// start loading
var c=a(this).button("loading"),d=c.data("args"),e=d.variation_id?"data-vid="+d.variation_id:"data-pid="+d.product_id;
// remove mark class
c.siblings(".compatible-product-add-to-cart-link").removeClass("product-added"),
// trigger remove event
g.find(".wc-cp-remove-assembly["+e+"]").trigger("wc-cp-remove")}}),
// Assembly configuration item remove button clicked
g.on("click wc-cp-remove",".wc-cp-remove-assembly",function(){var b=a(this),c=b.data();
// disable button
b.prop("disabled",!0),
// additional props
c.action="remove_compatible_product_from_assembly",c.security=wc_compatible_products_params.assembly_remove_nonce,c.assembly_key=s.key,a.post(wc_add_to_cart_params.ajax_url,c,function(a){"success"in a?a.success?(s=a.data,g.trigger("wc-cp-update-assembly-config")):alert(a.data):console.log(a)},"json").always(function(){
// re-enable button
b.prop("disabled",!1)})}),
// compatible products boxes state change
g.on("wc-cp-change-state",".wc-cp-products-list",function(b,c){
// this panel
var d=a(this),e=d.find(".compatible-product-add-to-cart-link").button("reset").removeClass("disabled product-added");
// hide any remove buttons
d.find(".compatible-product-remove-from-assembly-link").addClass("hidden").button("reset"),c&&(
// switch label
c.button("added").addClass("product-added"),
// show up the related remove button
c.siblings(".compatible-product-remove-from-assembly-link").removeClass("hidden"),
// disable all other products buttons on this list
e.not(".product-added").addClass("disabled"))});
// get any notices printed
var u=a(".woocommerce-message");u.length&&setTimeout(function(){u.animate_scroll_to(180)},100)}}),
// window scroll animation
a.fn.animate_scroll_to=function(b,c){
// check target
// viewport
// check target
// check offset
// check speed
// scroll viewport
return window.$viewport||(window.$viewport=a("body, html")),this.length?(b=isNaN(b)?0:parseFloat(b),c=isNaN(c)?500:parseFloat(c),$viewport.animate({scrollTop:this.offset().top-b},c),this):void 0}}(jQuery,window);