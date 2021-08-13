<!-- accordion -->	
		   <div class="cvt-accordion">
			<div class="accordion-section">
				
			<?php 
			 foreach($order_statuses as $ks => $vs)
			 {
				$prefix = 'wc-';
				$vs = $ks;
				if (substr($vs, 0, strlen($prefix)) == $prefix) {
					$vs = substr($vs, strlen($prefix));
				}
				$current_val = (is_array($smsalert_notification_status) && array_key_exists($vs, $smsalert_notification_status)) ? $smsalert_notification_status[$vs] : $vs;
				
                
				 ?>		
				<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_cust_<?php echo $ks; ?>"><input type="checkbox" name="smsalert_general[order_status][<?php echo $vs; ?>]" id="smsalert_general[order_status][<?php echo $vs; ?>]" class="notify_box" <?php echo (($current_val==$vs)?"checked='checked'":''); ?> value="<?php echo $vs; ?>"/><label><?php _e( 'when Order is '.ucwords(str_replace('-', ' ', $vs )), SmsAlertConstants::TEXT_DOMAIN ) ?></label>
				<span class="expand_btn"></span>
				</a>		 
				<div id="accordion_cust_<?php echo $ks; ?>" class="cvt-accordion-body-content">
					<table class="form-table">
						<tr valign="top">
						<td><div class="smsalert_tokens"><?php echo $getvariables; ?></div>
						<textarea name="smsalert_message[sms_body_<?php echo $vs; ?>]" id="smsalert_message[sms_body_<?php
						echo $vs; ?>]" <?php echo(($current_val==$vs)?'' : "readonly='readonly'"); ?>><?php 	
				
							echo smsalert_get_option('sms_body_'.$vs, 'smsalert_message', defined('SmsAlertMessages::DEFAULT_BUYER_SMS_'.str_replace('-', '_', strtoupper($vs))) ? constant('SmsAlertMessages::DEFAULT_BUYER_SMS_'.str_replace('-', '_', strtoupper($vs))) : SmsAlertMessages::DEFAULT_BUYER_SMS_STATUS_CHANGED); ?></textarea>
						</td>
				        </tr>
					</table>
				</div>
				 <?php
			 }
			 ?>	
			 
			 
			 
			
				<?php if ($hasWoocommerce) {?>
					<!-- accordion --5-->
					<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_5">
					<input type="checkbox" name="smsalert_general[buyer_notification_notes]" id="smsalert_general[buyer_notification_notes]" class="notify_box" <?php echo (($smsalert_notification_notes=='on')?"checked='checked'":'')?>/>
					<label><?php _e( 'When a new note is added to order', SmsAlertConstants::TEXT_DOMAIN ) ?></label>
					<span class="expand_btn"></span>
					</a>
					<div id="accordion_5" class="cvt-accordion-body-content">
						<table class="form-table">
							<tr valign="top">
							<td>
							<div class="smsalert_tokens"><?php echo $getvariables; ?><a href="#" val="[note]">order note</a> </div>
							<textarea name="smsalert_message[sms_body_new_note]" id="smsalert_message[sms_body_new_note]"><?php echo $sms_body_new_note; ?></textarea>
							</td>
							</tr>
						</table>
					</div>
				<?php }?>
				<!-- accordion --6-->
				<?php
				//if any child is checked then select all check box will checked
				if($smsalert_notification_checkout_otp=='on' || $smsalert_notification_signup_otp=='on' || $smsalert_notification_login_otp=='on')
				{
					$selectallchecked = 'checked';
				}
				else{
					$selectallchecked = '';
				}
				
				?>
				
				<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_6"> <input type="checkbox" name="selectall" id="selectall" <?php echo $selectallchecked;?> > <label><?php _e( 'Select All OTP', SmsAlertConstants::TEXT_DOMAIN ) ?></label>
				<span class="expand_btn"></span>
				</a>
				<div id="accordion_6" class="cvt-accordion-body-content">
					<table class="form-table">
						<tr valign="top">
						<th scrope="row">
						  <?php if ($hasWoocommerce) {?>
						  <input type="checkbox" name="smsalert_general[buyer_checkout_otp]" id="smsalert_general[buyer_checkout_otp]" class="notify_box" <?php echo (($smsalert_notification_checkout_otp=='on')?"checked='checked'":'')?>/><?php _e( 'OTP for Checkout', SmsAlertConstants::TEXT_DOMAIN ) ?><br /><br />
						  <?php }?>
						  <?php if ($hasWoocommerce || $hasWPmembers || $hasUltimate || $hasWPAM || $hasLearnPress) {?>
							<input type="checkbox" name="smsalert_general[buyer_signup_otp]" id="smsalert_general[buyer_signup_otp]" class="notify_box" <?php echo (($smsalert_notification_signup_otp=='on')?"checked='checked'":'')?>/><?php _e( 'OTP for Registration', SmsAlertConstants::TEXT_DOMAIN ) ?><br /><br />
						  <?php }?>	
						  <?php if ($hasWoocommerce || $hasWPAM) {?>
							<input type="checkbox" name="smsalert_general[buyer_login_otp]" id="smsalert_general[buyer_login_otp]" class="notify_box" <?php echo (($smsalert_notification_login_otp=='on')?"checked='checked'":'')?>/><?php _e( 'OTP for Login', SmsAlertConstants::TEXT_DOMAIN ) ?><br /><br />
						 <?php }?>	
						<!--Login with OTP-->
						 <?php if ($hasWoocommerce || $hasWPAM) {?>
							<input type="checkbox" name="smsalert_general[login_with_otp]" id="smsalert_general[login_with_otp]" class="notify_box" <?php echo (($login_with_otp=='on')?"checked='checked'":'')?>/><?php _e( 'Login with OTP', SmsAlertConstants::TEXT_DOMAIN ) ?><br /><br />
						 <?php }?>	
						<!--/-Login with OTP-->
						<!--OTP FOR Reset Password-->
						 <?php if ($hasWoocommerce || $hasWPAM) {?>
							<input type="checkbox" name="smsalert_general[reset_password]" id="smsalert_general[reset_password]" class="notify_box" <?php echo (($enable_reset_password=='on')?"checked='checked'":'')?>/><?php _e( 'OTP For Reset Password', SmsAlertConstants::TEXT_DOMAIN ) ?>
						 <?php }?>	<br /><br />
						<!--/-OTP FOR Reset Password-->
						
						</th>
						<td>
						<div class="smsalert_tokens"><a href="#" val="[otp]">OTP</a> </div>
						<textarea name="smsalert_message[sms_otp_send]" id="smsalert_message[sms_otp_send]"><?php echo $sms_otp_send; ?></textarea>
							<span><?php _e('You can also define OTP length between 3-8', SmsAlertConstants::TEXT_DOMAIN); ?>, eg <code>[otp length="6"]</code></span>
						</td>
				        </tr>
					</table>
				</div>
				<!--user registration-->
				<?php if ($hasWoocommerce) {?>
					<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_7">
					<input type="checkbox" name="smsalert_general[registration_msg]" id="smsalert_general[registration_msg]" class="notify_box" <?php echo (($smsalert_notification_reg_msg=='on')?"checked='checked'":'')?>/>
					<label><?php _e( 'When a new user is registered', SmsAlertConstants::TEXT_DOMAIN ) ?></label>
					<span class="expand_btn"></span>
					</a>
					<div id="accordion_7" class="cvt-accordion-body-content">
						<table class="form-table">
							<tr valign="top">
							<td>
							<div class="smsalert_tokens"><a href="#" val="[username]">Username</a> | <a href="#" val="[store_name]">Store Name</a>| <a href="#" val="[email]">Email</a>| <a href="#" val="[billing_phone]">Billing Phone</a></div>
							<textarea name="smsalert_message[sms_body_registration_msg]" id="smsalert_message[sms_body_registration_msg]"><?php echo $sms_body_registration_msg; ?></textarea>
							</td>
							</tr>
						</table>
					</div>
				<?php }?>
				<!--/user registration-->
			</div>
		  </div>
		  <!--end accordion-->	