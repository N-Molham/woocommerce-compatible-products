<?php
/**
 * Created by PhpStorm.
 * User: nabeel
 * Date: 7/3/16
 * Time: 4:48 PM
 */
?>
<p class="form-row form-row-full">
	<label for="wc_cp_assembly_percentage_<?php echo $variation->ID ?>">
		<?php _e( 'Assembly Price Percentage (%)', WC_CP_DOMAIN ); ?>:
	</label>
	<input type="number" value="<?php echo esc_attr( $variation_assembly_percentage ); ?>" min="0" step="0.5"
	       name="wc_cp_variation_assembly_percentage[<?php echo $variation->ID ?>]" id="wc_cp_assembly_percentage_<?php echo $variation->ID ?>" class="short"
	       placeholder="<?php echo esc_attr( $default_assembly_percentage ); ?>" />
</p>