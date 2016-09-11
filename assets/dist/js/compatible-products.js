/**
 * Created by nabeel on 5/18/16.
 */
!function(a,b,c){function d(a){function b(a){return a.replace(new RegExp(c(wc_compatible_products_params.woocommerce_price_decimal_sep,"/")+"0+$"),"")}function c(a,b){return(a+"").replace(new RegExp("[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\"+(b||"")+"-]","g"),"\\$&")}function d(a,b,c,d){a=(a+"").replace(/[^0-9+\-Ee.]/g,"");var e=isFinite(+a)?+a:0,f=isFinite(+b)?Math.abs(b):0,g="undefined"==typeof d?",":d,h="undefined"==typeof c?".":c,i="",j=function(a,b){var c=Math.pow(10,b);return""+Math.round(a*c)/c};return i=(f?j(e,f):""+Math.round(e)).split("."),i[0].length>3&&(i[0]=i[0].replace(/\B(?=(?:\d{3})+(?!\d))/g,g)),(i[1]||"").length<f&&(i[1]=i[1]||"",i[1]+=new Array(f-i[1].length+1).join("0")),i.join(h)}var e="",f=wc_compatible_products_params.woocommerce_price_num_decimals,g=wc_compatible_products_params.woocommerce_currency_pos,h=wc_compatible_products_params.woocommerce_currency_symbol;switch(a=d(a,f,wc_compatible_products_params.woocommerce_price_decimal_sep,wc_compatible_products_params.woocommerce_price_thousand_sep),"yes"===wc_compatible_products_params.woocommerce_price_trim_zeros&&f>0&&(a=b(a)),g){case"left":e='<span class="amount">'+h+a+"</span>";break;case"right":e='<span class="amount">'+a+h+"</span>";break;case"left_space":e='<span class="amount">'+h+"&nbsp;"+a+"</span>";break;case"right_space":e='<span class="amount">'+a+"&nbsp;"+h+"</span>"}return e}function e(a,b,c,d){a=(a+"").replace(/[^0-9+\-Ee.]/g,"");var e=isFinite(+a)?+a:0,f=isFinite(+b)?Math.abs(b):0,g="undefined"==typeof d?",":d,h="undefined"==typeof c?".":c,i="",j=function(a,b){var c=Math.pow(10,b);return""+(Math.round(a*c)/c).toFixed(b)};return i=(f?j(e,f):""+Math.round(e)).split("."),i[0].length>3&&(i[0]=i[0].replace(/\B(?=(?:\d{3})+(?!\d))/g,g)),(i[1]||"").length<f&&(i[1]=i[1]||"",i[1]+=new Array(f-i[1].length+1).join("0")),i.join(h)}a(function(){/**
		 * Setup assembly configuration table item object
		 *
		 * @param {Object} item_data
		 * @param {Boolean} main_product
		 *
		 * @returns {{qty: number, name: string, price: string}}
		 */
function c(a,b){b=b||!1;var c={data_obj:a,raw_qty:0,qty:"",name:"-",price:"",raw_price:0,total:0,is_assembly:!0};if(b){var g=wc_price_calculator_params.product_measurement_unit?wc_price_calculator_params.product_measurement_unit:"";
// fetch name
if(""===g&&wc_price_calculator_params.product_price_unit&&(g=wc_price_calculator_params.product_price_unit),
// item quantity
c.raw_qty=n.val(),c.raw_qty=parseFloat(c.raw_qty>0?c.raw_qty:"0"),0===c.raw_qty&&r&&(
// use current configuration quantity
c.raw_qty=r.quantity,n.val(c.raw_qty),
// trigger calculator change
f.trigger("wc-measurement-price-calculator-update")),
// item price
c.price=m.find(".product_price .amount").text(),c.raw_price=parseFloat(c.price.replace(wc_compatible_products_params.woocommerce_currency_symbol,"")),c.total=c.raw_price,
// append measure unit formatted
c.qty=c.raw_qty.toString()+" "+g,
// item price formatted
c.price='<span class="amount">'+c.price+"</span>",
// is assembly item or not
c.is_assembly=!1,"product_variations"in a)for(var h=0,j=a.product_variations.length;j>h;h++){var k=a.product_variations[h];if(k.variation_id.toString()===i.val()){
// set product name
c.name=k.variation_name;break}}}else
// other parts ( fittings )
for(var h=0,j=s.length;j>h;h++){var l=s[h];"variation_id"in a&&"variation_id"in l&&a.variation_id!==l.variation_id||l.product_id===a.product_id&&(
// setup item data
c.raw_qty=a.quantity,c.qty=e(a.quantity),c.total=l.price*a.quantity,c.price=d(c.total),c.name=l.text)}
// clear escaped tags
return c.name=c.name.replace(/&lt;.+&gt;/,"").replace(/\s+/g," "),c}var f=a(".variations_form");if(!(f.length<1)){
// vars
var g=a(b),h=f.data(),i=f.find("input[name=variation_id]"),j=f.find(".variations"),k=f.find(".wc-cp-assemblies-subtotal-amount"),l=a("#measuring-instructions-button").removeClass("hidden"),m=a("#price_calculator"),n=m.find("#length_needed"),o=location.search.indexOf("wc-cp-need-fittings=yes")>-1,p=location.search.indexOf("wc_cp_edit_assembly=yes")>-1,q=null,r=null,s=null;wc_compatible_products_params.is_assembly_product_page&&(o=!0),
// Update assembly configuration
f.on("wc-cp-update-assembly-config",function(){
// items holder
var a=[],b=0;
// Assembly configuration
if(r&&"parts"in r){var e=r.parts;for(i=0,j=e.length;j>i;i++)a.push(c(e[i]))}
// main product item
a.push(c(h,!0));for(var g=[],i=0,j=a.length;j>i;i++){var l=a[i];l.is_assembly&&(
// append remove button to name
l.name+='&nbsp;&nbsp;<a href="javascript:void(0)" class="wc-cp-remove-assembly" data-pid="'+l.data_obj.product_id+'" data-vid="'+l.data_obj.variation_id+'"><i class="fa fa-times"></i></a>'),
// build row data
g.push('<tr><td class="qty">'+l.qty+'</td><td class="name">'+l.name+'</td><td class="price">'+l.price+"</td></tr>"),b+=l.total}f.find(".wc-cp-config-container").html(g.join("")),
// assembly subtotal amount
k.html(d(b))}),/* Assembly configuration item remove button clicked*/
f.on("click",".wc-cp-remove-assembly",function(){var b=a(this),c=b.data();
// disable button
b.prop("disabled",!0),
// additional props
c.action="remove_compatible_product_from_assembly",c.security=wc_compatible_products_params.assembly_remove_nonce,c.assembly_key=r.key,a.post(wc_add_to_cart_params.ajax_url,c,function(a){"success"in a?a.success?(r=a.data,f.trigger("wc-cp-update-assembly-config")):alert(a.data):console.log(a)},"json").always(function(){
// re-enable button
b.prop("disabled",!1)})}),
// when price calculator change
f.on("wc-measurement-price-calculator-update",function(){r&&a.post(wc_add_to_cart_params.ajax_url,{action:"update_assembly_amount",amount:n.val(),assembly_key:r.key,security:wc_compatible_products_params.assembly_quantity_nonce},function(a){"success"in a&&(a.success?
// trigger assembly configuration update
f.trigger("wc-cp-update-assembly-config"):alert(a.data))},"json")}),
// move button to new location
a('<tr><td colspan="2"></td></tr>').insertAfter(n.closest("tr")).find("td").append(l),
// when show compatible products checkbox change
f.on("change wc-cp-change",".wc-cp-need-compatible",function(a){
// assembly panels
var b=f.find(".wc-cp-products-list, .wc-cp-assembly-config");a.target.checked||o?b.removeClass("hidden"):b.addClass("hidden"),q&&q.remove();
// move specifications panel location after the attributes table
var c=f.find(".panel-specifications");q=c.clone().insertAfter(j),c.remove()}).trigger("wc-cp-change"),
// when variation changes
f.on("woocommerce_variation_has_changed",function(){
// initialize popovers
f.find(".compatible-product-link").popover(),s=f.find(".wc-cp-products-list").data("products"),g.trigger("vc_reload"),r=f.find(".wc-cp-assembly-config").data("config"),r&&(n.val(r.quantity),f.trigger("wc-measurement-price-calculator-update"),r.parts&&r.parts.length&&(o=!0)),o&&(f.find(".wc-cp-need-compatible").prop("checked",!0),o=!1),f.find(".wc-cp-need-compatible").trigger("wc-cp-change"),f.trigger("wc-cp-update-assembly-config"),p&&(j.addClass("hidden"),f.find(":input:submit").addClass("update-assembly").text(wc_compatible_products_params.edit_assembly_label).parent().append('<input type="hidden" name="wc_cp_update_assembly" value="'+r.key+'" />'))}),
// add product to cart click
f.on("click",".compatible-product-add-to-cart-link",function(b){b.preventDefault();
// start loading
var c=a(this).button("loading"),d=c.data("args"),e=f.find('input[name="wc_cp_quantity['+d.variation_id+']"]');1!==e.length?d.quantity=1:d.quantity=parseInt(e.val()),
// send AJAX request
a.post(wc_add_to_cart_params.ajax_url,d,function(a){"object"==typeof a?
// json response
a.success?(
// success
c.button("added"),r=a.data,e.val(1),f.trigger("wc-cp-update-assembly-config")):(c.button("reset"),alert(a.data)):c.button("reset")},"json")})}})}(jQuery,window);