<?php

?>
			<form method="post" id="wpftsi_form3">
				<?php wp_nonce_field( 'wpfts_options', 'wpfts_options-nonce' ); ?>
				<div id="poststuff">
	
					<div id="post-body" class="metabox-holder columns-2">
					
						<!-- Main Content -->
						<div id="postbox-container-1" class="postbox-container">
							<?php do_meta_boxes('wpfts-options', 'side', array()); ?>
						</div>
	
						<div id="postbox-container-2" class="postbox-container">
							<?php do_meta_boxes('wpfts-options', 'normal3', array()); ?>
						</div>
	
					</div>
				</div><!--#poststuff-->
			</form>
<?php
