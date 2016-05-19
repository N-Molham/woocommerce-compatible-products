/**
 * Created by nabeel on 5/18/16.
 */
!function(a,b,c){a(function(){var b=a(".variations_form");b.length<1||(
// when show compatible products checkbox change
b.on("change wc-cp-change",".wc-cp-need-compatible",function(a){b.find(".wc-cp-products-list").css("display",a.target.checked?"block":"none")}).trigger("wc-cp-change"),
// when variation changes
b.on("woocommerce_variation_has_changed",function(){
// trigger compatible checkbox checked change
b.find(".wc-cp-need-compatible").trigger("wc-cp-change"),
// initialize popovers
b.find(".compatible-product-link").popover()}),
// add product to cart click
b.on("click",".compatible-product-add-to-cart-link",function(b){b.preventDefault();
// start loading
var c=a(this).button("loading");
// send AJAX request
a.post(wc_add_to_cart_params.ajax_url,c.data("args"),function(b){if("object"==typeof b)
// json response
if("fragments"in b){
// success
c.button("added");
// update mini cart data
for(var d in b.fragments)if(b.fragments.hasOwnProperty(d)){
// query the fragment position
var e=a(d);e.length&&
// replace with the new information
e.replaceWith(b.fragments[d])}}else
// error
c.button("reset"),alert(b.data);else
// unknown response format
c.button("reset"),console.log(b)},"json")}))})}(jQuery,window);