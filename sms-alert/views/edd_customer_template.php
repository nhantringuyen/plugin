<!-- accordion -->	
<div class="cvt-accordion">
	<div class="accordion-section">
	<?php 
	 foreach($edd_order_statuses as $ks => $vs)
	 {
		  $current_val = (is_array($edd_order_statuses) && array_key_exists($vs, $edd_order_statuses)) ? $edd_order_statuses[$vs] : $vs;
		 ?>		
		<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_cust_<?php echo $ks; ?>"><input type="checkbox" name="smsalert_edd_general[edd_order_status_<?php echo $vs; ?>]" id="smsalert_edd_general[edd_order_status_<?php echo $vs; ?>]" class="notify_box" 
		<?php echo ((smsalert_get_option( 'edd_order_status_'.$vs, 'smsalert_edd_general', 'on')=='on')?"checked='checked'":''); ?>/><label><?php _e( 'when Order is '.ucwords(str_replace('-', ' ', $vs )), SmsAlertConstants::TEXT_DOMAIN ) ?></label>
		<span class="expand_btn"></span>
		</a>		 
		<div id="accordion_cust_<?php echo $ks; ?>" class="cvt-accordion-body-content">
			<table class="form-table">
				<tr valign="top">
				<td><div class="smsalert_tokens"><?php echo SmsAlertEdd::getEDDVariables(); ?></div>
				<textarea name="smsalert_edd_message[edd_sms_body_<?php echo $vs; ?>]" id="smsalert_edd_message[edd_sms_body_<?php
				echo $vs; ?>]" <?php echo(($current_val==$vs)?'' : "readonly='readonly'"); ?>><?php 	
		
					echo smsalert_get_option('edd_sms_body_'.$vs, 'smsalert_edd_message', defined('SmsAlertMessages::DEFAULT_EDD_BUYER_SMS_'.str_replace('-', '_', strtoupper($vs))) ? constant('SmsAlertMessages::DEFAULT_EDD_BUYER_SMS_'.str_replace('-', '_', strtoupper($vs))) : SmsAlertMessages::DEFAULT_EDD_BUYER_SMS_STATUS_CHANGED); ?></textarea>
				</td>
				</tr>
			</table>
		</div>
		 <?php
	 }
	 ?>	
	</div>
</div>
<!--end accordion-->	