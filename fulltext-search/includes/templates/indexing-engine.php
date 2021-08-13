<?php

?>
			<form method="post" id="wpftsi_form2">
				<?php wp_nonce_field( 'wpfts_options', 'wpfts_options-nonce' ); ?>
				<div id="poststuff">
	
					<div id="post-body" class="metabox-holder columns-2">
					
						<!-- Main Content -->
						<div id="postbox-container-1" class="postbox-container">
							<?php do_meta_boxes('wpfts-options', 'side', array()); ?>
						</div>
	
						<div id="postbox-container-2" class="postbox-container">
							<?php do_meta_boxes('wpfts-options', 'normal2', array()); ?>
						</div>
	
						<div>
							<button type="button" class="button-primary wpfts_submit2" name="update_options" data-confirm="<?php echo htmlspecialchars(__('Changing of Indexing Engine Settings will automatically upgrade the search index. This operation could take some time. Are you sure?', 'wpfts_lang')); ?>"><?php echo __('Save Changes and Upgrade Index', 'wpfts_lang'); ?></button>
						</div>
					</div>
				</div><!--#poststuff-->
			</form>
<?php
