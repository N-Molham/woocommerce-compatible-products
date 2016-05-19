/**
 * Created by nabeel on 5/18/16.
 */
!function(a,b,c){a(function(){var b=a(".variations_form");b.length<1||
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
c.button("reset"),console.log(b)},"json")})})}(jQuery,window);