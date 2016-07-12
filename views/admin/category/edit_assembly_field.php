<?php
/**
 * Created by PhpStorm.
 * User: Nabeel
 * Date: 12-Jul-16
 * Time: 6:57 PM
 */
?>
<tr class="form-field">
	<th scope="row" valign="top">
		<label><?php _e( 'Assembly Category', WC_CP_DOMAIN ); ?></label>
	</th>
	<td>
		<fieldset>
			<label>
				<input type="radio" name="wc_cp_assembly_category" value="1"<?php checked( $is_checked, true ); ?> /> <?php _e( 'Yes', WC_CP_DOMAIN ); ?>
			</label>
			<label>
				<input type="radio" name="wc_cp_assembly_category" value="0"<?php checked( $is_checked, false ); ?> /> <?php _e( 'No', WC_CP_DOMAIN ); ?>
			</label>
		</fieldset>
	</td>
</tr>
