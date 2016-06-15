/**
 * Created by nabeel on 5/18/16.
 */
!function(a,b,c){a(function(){var b=a(".variations_form");if(!(b.length<1)){
// vars
var c=(b.data(),b.find(".variations"),a("#measuring-instructions-button").removeClass("hidden")),d=a("#length_needed"),e=a("#price_calculator"),f=e.find("#length_needed"),g=location.search.indexOf("wc-cp-need-fittings=yes")>-1,h=null;
// Update assembly configuration
b.on("wc-cp-update-assembly-config",function(){
// items holder
var a=[];
// main product item
a.push({qty:f.val(),name:"",price:e.find(".total_price .amount").text()}),console.log(h)}),
// when price calculator change
b.on("wc-measurement-price-calculator-update",function(){
// trigger assembly configuration update
b.trigger("wc-cp-update-assembly-config")}),
// move button to new location
a('<tr><td colspan="2"></td></tr>').insertAfter(d.closest("tr")).find("td").append(c),
// when show compatible products checkbox change
b.on("change wc-cp-change",".wc-cp-need-compatible",function(a){var c=b.find(".wc-cp-products-list, .wc-cp-assembly-config");a.target.checked||g?c.removeClass("hidden"):c.addClass("hidden")}).trigger("wc-cp-change"),
// when variation changes
b.on("woocommerce_variation_has_changed",function(){
// show compatible products by default
g&&(b.find(".wc-cp-need-compatible").prop("checked",!0),g=!1),
// trigger compatible checkbox checked change
b.find(".wc-cp-need-compatible").trigger("wc-cp-change"),
// initialize popovers
b.find(".compatible-product-link").popover()}),
// add product to cart click
b.on("click",".compatible-product-add-to-cart-link",function(c){c.preventDefault();
// start loading
var d=a(this).button("loading"),e=d.data("args");
// set quantity
$qty_input=b.find('input[name="wc_cp_quantity['+e.variation_id+']"]'),1!==$qty_input.length?e.quantity=1:e.quantity=parseInt($qty_input.val()),
// send AJAX request
a.post(wc_add_to_cart_params.ajax_url,e,function(a){"object"==typeof a?
// json response
a.success?(
// success
d.button("added"),h=a.data,b.trigger("wc-cp-update-assembly-config")):(d.button("reset"),alert(a.data)):(d.button("reset"),console.log(a))},"json")})}})}(jQuery,window);