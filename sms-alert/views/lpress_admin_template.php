 <div class="cvt-accordion">
				 <div class="accordion-section">			      
					<?php 
					 foreach($lpress_statuses as $ks => $vs)
					 {
						?>		
						<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_<?php echo $ks; ?>"><input type="checkbox" name="smsalert_lpress_general[lpress_admin_notification_<?php echo $vs; ?>]" id="smsalert_lpress_general[lpress_admin_notification_<?php echo $vs; ?>]" class="notify_box" <?php echo ((smsalert_get_option( 'lpress_admin_notification_'.$vs, 'smsalert_lpress_general', 'on')=='on')?"checked='checked'":''); ?>/><label><?php _e('when Order is '.ucwords(str_replace('-', ' ', $vs )), SmsAlertConstants::TEXT_DOMAIN ) ?></label>
						<span class="expand_btn"></span>
						</a>		 
						<div id="accordion_<?php echo $ks; ?>" class="cvt-accordion-body-content">
							<table class="form-table">
								<tr valign="top">
								<td><div class="smsalert_tokens"><?php echo SmsAlertLearnPress::getLPRESSvariables(); ?></div>
								<textarea name="smsalert_lpress_message[lpress_admin_sms_body_<?php echo $vs; ?>]" id="smsalert_lpress_message[lpress_admin_sms_body_<?php echo $vs; ?>]" <?php echo((smsalert_get_option( 'lpress_admin_notification_'.$vs, 'smsalert_lpress_general', 'on')=='on')?'' : "readonly='readonly'"); ?>><?php 
						  echo smsalert_get_option('lpress_admin_sms_body_'.$vs, 'smsalert_lpress_message', defined('SmsAlertMessages::DEFAULT_LPRESS_ADMIN_SMS_'.str_replace('-', '_', strtoupper($vs))) ? constant('SmsAlertMessages::DEFAULT_LPRESS_ADMIN_SMS_'.str_replace('-', '_', strtoupper($vs))) : SmsAlertMessages::DEFAULT_LPRESS_ADMIN_SMS_STATUS_CHANGED); 
								
								?></textarea>
								</td>
								</tr>
							</table>
						</div>
						 <?php
					 }
					 ?>	
					 
				<!--course enroll student-->
				<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_admin_course_enroll">
				<input type="checkbox" name="smsalert_lpress_general[admin_course_enroll]" id="smsalert_lpress_general[admin_course_enroll]" class="notify_box" <?php echo (($admin_notification_course_enroll=='on')?"checked='checked'":'')?>/>
				<label><?php _e( 'When a student enrolls course', SmsAlertConstants::TEXT_DOMAIN ) ?></label>
				<span class="expand_btn"></span>
				</a>
				<div id="accordion_admin_course_enroll" class="cvt-accordion-body-content">
					<table class="form-table">
						<tr valign="top">
						<td>
						<div class="smsalert_tokens"><?php echo SmsAlertLearnPress::getLPRESSvariables('courses'); ?></div>
						<textarea name="smsalert_lpress_message[sms_body_course_enroll_admin_msg]" id="smsalert_lpress_message[sms_body_course_enroll_admin_msg]"><?php echo $sms_body_course_enroll_admin_msg; ?></textarea>
						</td>
						</tr>
					</table>
				</div>
				<!--/course enroll student-->
				
				<!--course finished student-->
				<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_admin_course_finished">
				<input type="checkbox" name="smsalert_lpress_general[admin_course_finished]" id="smsalert_lpress_general[admin_course_finished]" class="notify_box" <?php echo (($student_notification_course_finished=='on')?"checked='checked'":'')?>/>
				<label><?php _e( 'When a student finishes course', SmsAlertConstants::TEXT_DOMAIN ) ?></label>
				<span class="expand_btn"></span>
				</a>
				<div id="accordion_admin_course_finished" class="cvt-accordion-body-content">
					<table class="form-table">
						<tr valign="top">
						<td>
						<div class="smsalert_tokens"><?php echo SmsAlertLearnPress::getLPRESSvariables('courses'); ?></div>
						<textarea name="smsalert_lpress_message[sms_body_course_finished_admin_msg]" id="smsalert_lpress_message[sms_body_course_finished_admin_msg]"><?php echo $sms_body_course_finished_admin_msg; ?></textarea>
						</td>
						</tr>
					</table>
				</div>
				<!--/course finished student-->				
				
				<!--become_a_teacher-->
					<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_admin_become_a_teacher">
					<input type="checkbox" name="smsalert_lpress_general[admin_become_teacher]" id="smsalert_lpress_general[admin_become_teacher]" class="notify_box" <?php echo (($admin_become_teacher=='on')?"checked='checked'":'')?>/>
					<label><?php _e( 'When new teacher created', SmsAlertConstants::TEXT_DOMAIN ) ?></label>
					<span class="expand_btn"></span>
					</a>
					<div id="accordion_admin_become_a_teacher" class="cvt-accordion-body-content">
						<table class="form-table">
							<tr valign="top">
							<td>
							<div class="smsalert_tokens"><?php echo SmsAlertLearnPress::getLPRESSvariables('teacher'); ?></div>
							<textarea name="smsalert_lpress_message[sms_body_admin_become_teacher_msg]" id="smsalert_lpress_message[sms_body_admin_become_teacher_msg]"><?php echo $sms_body_admin_become_teacher_msg; ?></textarea>
							</td>
							</tr>
						</table>
					</div>
				<!--/become_a_teacher-->
				
				</div>
				
		   </div>