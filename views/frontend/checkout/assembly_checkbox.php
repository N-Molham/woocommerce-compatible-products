<?php
/**
 * Created by PhpStorm.
 * User: nabeel
 * Date: 5/26/16
 * Time: 7:28 PM
 */
?>
<p class="form-row terms assembly">
	<label for="assembly" class="checkbox">
		<?php _e( 'I&rsquo;ve read and accept the <a href="javascript:void(0)" target="_blank" data-toggle="modal" data-target="#measuring-instructions-modal">assembly specifications</a>', 'woocommerce' ); ?>
		<span class="required">*</span>
	</label>
	<input type="checkbox" class="input-checkbox" name="assembly" <?php checked( $assembly_is_checked, true ); ?> id="assembly" />
</p>
