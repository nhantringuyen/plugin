<div class="smsalert_wrapper">
					<strong><?php _e( $smsalert_helper, SmsAlertConstants::TEXT_DOMAIN ); ?></strong>	
					<table class="form-table">
						<tr valign="top">
							
							<th scrope="row"><?php _e('SMS Alert Username',SmsAlertConstants::TEXT_DOMAIN); ?>
								<span class="tooltip" data-title="Enter SMSAlert Username"><span class="dashicons dashicons-info"></span></span>
							</th>
							<td style="vertical-align: top;">
								<?php if($islogged){echo $smsalert_name;}?>
								<input type="text" name="smsalert_gateway[smsalert_name]" id="smsalert_gateway[smsalert_name]" value="<?php echo $smsalert_name; ?>" data-id="smsalert_name" class="<?php echo $hidden?>">
								<input type="hidden" name="action" value="save_sms_alert_settings" />
								<span class="<?php echo $hidden?>"><?php _e( 'your <b>SMS Alert</b> user name', 'smsalert' ); ?></span>
							</td>
						</tr>

						<tr valign="top">
							<th scrope="row"><?php _e( 'SMS Alert Password', SmsAlertConstants::TEXT_DOMAIN ) ?>
								<span class="tooltip" data-title="Enter SMSAlert Password"><span class="dashicons dashicons-info"></span></span>
							</th>
							<td >
								<?php if($islogged){echo '*****';}?>
								<input type="text" name="smsalert_gateway[smsalert_password]" id="smsalert_gateway[smsalert_password]" value="<?php echo $smsalert_password; ?>" data-id="smsalert_password" class="<?php echo $hidden?>">
								<span class="<?php echo $hidden?>"><?php _e( 'your <b>SMS Alert</b> password', SmsAlertConstants::TEXT_DOMAIN ); ?></span>
							</td>
						</tr>
						<?php do_action('verify_senderid_button')?>
						<tr valign="top">
							<th scrope="row">
							<?php _e( 'SMS Alert Sender Id', SmsAlertConstants::TEXT_DOMAIN ) ?>
							<span class="tooltip" data-title="Only available for transactional route"><span class="dashicons dashicons-info"></span></span>
							</th>
							<td >
								<?php if($islogged){?>
									<?php echo $smsalert_api;?>
									<input type="hidden" value="<?php echo $smsalert_api;?>" name="smsalert_gateway[smsalert_api]" id="smsalert_gateway[smsalert_api]">
								<?php }else{?>
								
								<select name="smsalert_gateway[smsalert_api]" id="smsalert_gateway[smsalert_api]" disabled>
									<option value="SELECT">SELECT</option>
								</select>
								<span class="<?php echo $hidden?>"><?php _e( 'display name for SMS\'s to be sent', SmsAlertConstants::TEXT_DOMAIN ); ?></span>
								<?php } ?>
								
							</td>
						</tr>
						<tr valign="top">
							<th scrope="row">
							</th>
							<td >
								<?php if($islogged){?>
								<a href="#" class="button-primary" onclick="logout(); return false;"><?php echo _e( 'Logout', SmsAlertConstants::TEXT_DOMAIN );?></a>
								<?php }?>
							</td>
						</tr>
					</table>
				</div>
				