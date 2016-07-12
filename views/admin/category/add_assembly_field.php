<?php
/**
 * Created by PhpStorm.
 * User: Nabeel
 * Date: 12-Jul-16
 * Time: 6:57 PM
 */
?>
<div class="form-field">
	<label><?php _e( 'Assembly Category', WC_CP_DOMAIN ); ?></label>
	<fieldset>
		<label>
			<input type="radio" name="wc_cp_assembly_category" value="1"<?php checked( $is_checked, true ); ?> /> <?php _e( 'Yes', WC_CP_DOMAIN ); ?>
		</label>
		<label>
			<input type="radio" name="wc_cp_assembly_category" value="0"<?php checked( $is_checked, false ); ?> /> <?php _e( 'No', WC_CP_DOMAIN ); ?>
		</label>
	</fieldset>
</div>