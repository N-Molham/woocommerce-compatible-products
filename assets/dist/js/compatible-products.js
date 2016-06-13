/**
 * Created by nabeel on 5/18/16.
 */
!function(a,b,c){a(function(){var b=a(".variations_form");if(!(b.length<1)){
// vars
var c=a("#measuring-instructions-button").removeClass("hidden"),d=a("#length_needed"),e=location.search.indexOf("wc-cp-need-fittings=yes")>-1;
// move button to new location
a('<tr><td colspan="2"></td></tr>').insertAfter(d.closest("tr")).find("td").append(c),
// when show compatible products checkbox change
b.on("change wc-cp-change",".wc-cp-need-compatible",function(a){var c=b.find(".wc-cp-products-list, .wc-cp-assembly-config");a.target.checked||e?c.removeClass("hidden"):c.addClass("hidden")}).trigger("wc-cp-change"),
// when variation changes
b.on("woocommerce_variation_has_changed",function(){
// show compatible products by default
e&&(b.find(".wc-cp-need-compatible").prop("checked",!0),e=!1),
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
a.post(wc_add_to_cart_params.ajax_url,e,function(b){if("object"==typeof b)
// json response
if("fragments"in b){
// success
d.button("added");
// update mini cart data
for(var c in b.fragments)if(b.fragments.hasOwnProperty(c)){
// query the fragment position
var e=a(c);e.length&&
// replace with the new information
e.replaceWith(b.fragments[c])}}else
// error
d.button("reset"),alert(b.data);else
// unknown response format
d.button("reset"),console.log(b)},"json")})}})}(jQuery,window);