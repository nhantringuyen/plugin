 <div class="cvt-accordion">
				 <div class="accordion-section">			      
					<?php 
					 foreach($wpam_statuses as $ks => $vs)
					 {
						?>		
						<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_<?php echo $ks; ?>"><input type="checkbox" name="smsalert_wpam_general[wpam_admin_notification_<?php echo $vs; ?>]" id="smsalert_wpam_general[wpam_admin_notification_<?php echo $vs; ?>]" class="notify_box" <?php echo ((smsalert_get_option( 'wpam_admin_notification_'.$vs, 'smsalert_wpam_general', 'on')=='on')?"checked='checked'":''); ?>/><label><?php _e('when Affiliate is '.ucwords(str_replace('-', ' ', $vs )), SmsAlertConstants::TEXT_DOMAIN ) ?></label>
						<span class="expand_btn"></span>
						</a>		 
						<div id="accordion_<?php echo $ks; ?>" class="cvt-accordion-body-content">
							<table class="form-table">
								<tr valign="top">
								<td><div class="smsalert_tokens"><?php echo AffiliateManagerForm::getWPAMvariables('affiliate'); ?></div>
								<textarea name="smsalert_wpam_message[wpam_admin_sms_body_<?php echo $vs; ?>]" id="smsalert_message[admin_sms_body_<?php echo $vs; ?>]" <?php echo((smsalert_get_option( 'wpam_admin_notification_'.$vs, 'smsalert_wpam_general', 'on')=='on')?'' : "readonly='readonly'"); ?>><?php 
						  echo smsalert_get_option('wpam_admin_sms_body_'.$vs, 'smsalert_wpam_message', defined('SmsAlertMessages::DEFAULT_WPAM_ADMIN_SMS_'.str_replace('-', '_', strtoupper($vs))) ? constant('SmsAlertMessages::DEFAULT_WPAM_ADMIN_SMS_'.str_replace('-', '_', strtoupper($vs))) : SmsAlertMessages::DEFAULT_WPAM_ADMIN_SMS_STATUS_CHANGED); 
								
								?></textarea>
								</td>
								</tr>
							</table>
						</div>
						 <?php
					 }
					 ?>	
				</div>
				
				
				<!--transaction status-->
				  <div class="accordion-section">			      
					<?php 
					 foreach($wpam_transaction as $ks => $vs)
					 {
						 
						?>		
						<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_<?php echo $ks; ?>"><input type="checkbox" name="smsalert_wpam_general[wpam_admin_notification_<?php echo $vs; ?>]" id="smsalert_wpam_general[wpam_admin_notification_<?php echo $vs; ?>]" class="notify_box" <?php echo ((smsalert_get_option( 'wpam_admin_notification_'.$vs, 'smsalert_wpam_general', 'on')=='on')?"checked='checked'":''); ?>/><label><?php _e('when Transaction is '.ucwords(str_replace('-', ' ', $vs )), SmsAlertConstants::TEXT_DOMAIN ) ?></label>
						<span class="expand_btn"></span>
						</a>		 
						<div id="accordion_<?php echo $ks; ?>" class="cvt-accordion-body-content">
							<table class="form-table">
								<tr valign="top">
								<td><div class="smsalert_tokens"><?php echo AffiliateManagerForm::getWPAMvariables('transaction'); ?></div>
								<textarea name="smsalert_wpam_message[wpam_admin_sms_body_<?php echo $vs; ?>]" id="smsalert_message[admin_sms_body_<?php echo $vs; ?>]" <?php echo((smsalert_get_option( 'wpam_admin_notification_'.$vs, 'smsalert_wpam_general', 'on')=='on')?'' : "readonly='readonly'"); ?>><?php 
						  echo smsalert_get_option('wpam_admin_sms_body_'.$vs, 'smsalert_wpam_message', defined('SmsAlertMessages::DEFAULT_WPAM_ADMIN_SMS_'.str_replace('-', '_', strtoupper($vs))) ? constant('SmsAlertMessages::DEFAULT_WPAM_ADMIN_SMS_'.str_replace('-', '_', strtoupper($vs))) : SmsAlertMessages::DEFAULT_WPAM_ADMIN_SMS_TRANS_STATUS_CHANGED); 
								
								?></textarea>
								</td>
								</tr>
							</table>
						</div>
						 <?php
					 }
					 ?>	
				</div>
				<!--/transaction status-->
				
				
				
		   </div>