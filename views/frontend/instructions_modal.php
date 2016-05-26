<?php
/**
 * Created by PhpStorm.
 * User: nabeel
 * Date: 5/26/16
 * Time: 9:15 PM
 */
?>
<div class="modal fade" id="measuring-instructions-modal" tabindex="-1" role="dialog" aria-labelledby="measuring-instructions-label">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="<?php esc_attr_e( 'Close', 'woocommerce' ); ?>">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title" id="measuring-instructions-label"><?php _e( 'Measuring Instructions', 'woocommerce' ); ?></h4>
			</div><!-- .modal-header -->
			<div class="modal-body"><?php echo $instructions; ?></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php _e( 'Close', 'woocommerce' ); ?></button>
			</div>
		</div><!-- .modal-content -->
	</div><!-- .modal-dialog -->
</div><!-- .modal -->
