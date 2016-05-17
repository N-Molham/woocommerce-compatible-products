<?php
/**
 * Created by PhpStorm.
 * User: nabeel
 * Date: 5/17/16
 * Time: 5:41 PM
 */
?>
<p class="form-row form-row-full">
	<label><?php _e( 'Compatible Products', WC_CP_DOMAIN ); ?>:</label>
	<input type="hidden" class="select2 wc-cp-dropdown" name="<?php echo esc_attr( $field_name ); ?>[<?php echo $variation_id; ?>]"
	       value="<?php echo esc_attr( implode( ',', $field_value ) ); ?>" data-initial="<?php echo esc_attr( json_encode( $initial_selection ) ); ?>" />
</p>