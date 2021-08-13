 <div class="cvt-accordion">
				 <div class="accordion-section">			      
					<?php 
					 foreach($edd_order_statuses as $ks => $vs)
					 {
						?>		
						<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_<?php echo $ks; ?>"><input type="checkbox" name="smsalert_edd_general[edd_admin_notification_<?php echo $vs; ?>]" id="smsalert_edd_general[edd_admin_notification_<?php echo $vs; ?>]" class="notify_box" <?php echo ((smsalert_get_option( 'edd_admin_notification_'.$vs, 'smsalert_edd_general', 'on')=='on')?"checked='checked'":''); ?>/><label><?php _e('when Order is '.ucwords(str_replace('-', ' ', $vs )), SmsAlertConstants::TEXT_DOMAIN ) ?></label>
						<span class="expand_btn"></span>
						</a>		 
						<div id="accordion_<?php echo $ks; ?>" class="cvt-accordion-body-content">
							<table class="form-table">
								<tr valign="top">
								<td><div class="smsalert_tokens"><?php echo SmsAlertEdd::getEDDVariables(); ?></div>
								<textarea name="smsalert_edd_message[edd_admin_sms_body_<?php echo $vs; ?>]" id="smsalert_message[admin_sms_body_<?php echo $vs; ?>]" <?php echo((smsalert_get_option( 'edd_admin_notification_'.$vs, 'smsalert_edd_general', 'on')=='on')?'' : "readonly='readonly'"); ?>><?php 
						  echo smsalert_get_option('edd_admin_sms_body_'.$vs, 'smsalert_edd_message', defined('SmsAlertMessages::DEFAULT_EDD_ADMIN_SMS_'.str_replace('-', '_', strtoupper($vs))) ? constant('SmsAlertMessages::DEFAULT_EDD_ADMIN_SMS_'.str_replace('-', '_', strtoupper($vs))) : SmsAlertMessages::DEFAULT_EDD_ADMIN_SMS_STATUS_CHANGED); 
								
								?></textarea>
								</td>
								</tr>
							</table>
						</div>
						 <?php
					 }
					 ?>	
				</div>
		   </div>