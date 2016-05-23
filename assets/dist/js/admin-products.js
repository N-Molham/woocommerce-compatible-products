/**
 * Created by nabeel on 5/17/16.
 */
!function(a,b,c){a(function(){var b=a("#woocommerce-product-data");if(!(b.length<1)){
// vars
var c=a("#variable_product_options");
// when product variation view loads
b.on("woocommerce_variations_loaded",function(){
// compatible lists initialization
c.find(".wc-cp-dropdown").select2({placeholder:wc_cp_compatible_products.i18n.placeholder,minimumInputLength:3,multiple:!0,ajax:{url:ajaxurl,cache:!0,dataType:"json",quietMillis:250,data:function(a){return{action:"search_compatible_products",term:a,security:wc_cp_compatible_products.search_nonce,wc_cp_request:!0}},results:function(a){var b=[];console.log(a);
// build the result set
for(var c in a)a.hasOwnProperty(c)&&b.push({id:c,text:a[c]});
// return the final results
return{results:b}}},initSelection:function(b,c){c(a(b).data("initial"))},escapeMarkup:function(a){
// don't escape HTML
return a}})})}})}(jQuery,window);