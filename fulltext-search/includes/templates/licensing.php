<?php


?>
			<form method="post" id="wpftsi_form4">
				<?php wp_nonce_field( 'wpfts_options', 'wpfts_options-nonce' ); ?>
				<div id="poststuff">
	
					<div id="post-body" class="metabox-holder columns-2">
					
						<!-- Main Content -->
						<div id="postbox-container-1" class="postbox-container">
							<?php do_meta_boxes('wpfts-options', 'side', array()); ?>
						</div>
	
						<div id="postbox-container-2" class="postbox-container">
							<?php do_meta_boxes('wpfts-options', 'normal4', array()); ?>
						</div>

						<div>
							<button type="button" class="button-primary wpfts_submit4" name="update_options"><?php echo __('Save Changes', 'wpfts_lang'); ?></button>
						</div>
					</div>
				</div><!--#poststuff-->
			</form>
	
			<div class="wrap">
	
				<?php if(isset($_POST['wpfts-updater-nonce'])) : ?>
				<div class="updated">
					<p><?php _e('License key saved!', 'wpfts_lang'); ?></p>
				</div>
				<?php endif; ?>
	
				<form action="" method="post">
	
				</form>
			</div>
<?php
