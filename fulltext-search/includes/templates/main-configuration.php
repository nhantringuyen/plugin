<?php

?>
			<form method="post" id="wpftsi_form">
				<?php wp_nonce_field( 'wpfts_options', 'wpfts_options-nonce' ); ?>
				<div id="poststuff">
	
					<div id="post-body" class="metabox-holder columns-2">
					
						<!-- Main Content -->
						<div id="postbox-container-1" class="postbox-container">
							<?php do_meta_boxes('wpfts-options', 'side', array()); ?>
						</div>
	
						<div id="postbox-container-2" class="postbox-container">
							<?php do_meta_boxes('wpfts-options', 'normal1', array()); ?>
						</div>
	
						<div>
							<button type="button" class="button-primary wpfts_submit" name="update_options"><?php echo __('Save Changes', 'wpfts_lang'); ?></button>
						</div>
					</div>
				</div><!--#poststuff-->
			</form>
<?php
