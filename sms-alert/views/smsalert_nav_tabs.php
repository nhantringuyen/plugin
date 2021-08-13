<ul>
			   <li tab_type="logo" onclick="return false;" >
				<img src="https://www.smsalert.co.in/logo/www.smsalert.co.in.png" width="150px;" />
              </li>
              <li tab_type="global" onclick="SMSAlert_change_nav(this, 'SMSAlert_nav_global_box')" class="SMSAlert_active">
                <a href="#general"><span class="dashicons-before dashicons-admin-generic"></span> <?php echo _e( 'General Settings', SmsAlertConstants::TEXT_DOMAIN );?> </a>
              </li>
			<?php 
			  if ($hasWoocommerce|| $hasWPmembers || $hasUltimate || $hasWPAM) 
			  {
			 ?>
				   <li tab_type="css" onclick="SMSAlert_change_nav(this, 'SMSAlert_nav_css_box')">
					<a href="#customertemplates"><span class="dashicons-before dashicons-admin-users"></span> <?php echo _e( 'Customer Templates', SmsAlertConstants::TEXT_DOMAIN );?></a>
				  </li>
				  <?php	
			  }
			  
			  if ($hasWoocommerce) 
			  {
			  ?>
				  <li tab_type="admintemplates" onclick="SMSAlert_change_nav(this, 'SMSAlert_nav_admintemplates_box')" >
					<a href="#admintemplates"><span class="dashicons-before dashicons-list-view"></span> <?php echo _e( 'Admin Templates', SmsAlertConstants::TEXT_DOMAIN );?></a>
					</li>
			<?php
				}			
			  if (is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' )) 
			  {
			?>
				  <li tab_type="eddcsttemplates" onclick="SMSAlert_change_nav(this, 'SMSAlert_nav_eddcsttemplates_box')" >
					<a href="#eddcsttemplates"><span class="dashicons-before dashicons-admin-users"></span> <?php echo _e( 'EDD Cust. Templates', SmsAlertConstants::TEXT_DOMAIN );?></a>
				  </li>
				  
				  <li tab_type="eddadmintemplates" onclick="SMSAlert_change_nav(this, 'SMSAlert_nav_eddadmintemplates_box')" >
					<a href="#eddadmintemplates"><span class="dashicons-before dashicons-list-view"></span> <?php echo _e( 'EDD Admin Templates', SmsAlertConstants::TEXT_DOMAIN );?></a>
				  </li>
			 <?php	
			  }
			  if (is_plugin_active('woocommerce-bookings/woocommerce-bookings.php' )) 
			  {
				 
			?>
				  <li tab_type="wcbkcsttemplates" onclick="SMSAlert_change_nav(this, 'SMSAlert_nav_wcbkcsttemplates_box')" >
					<a href="#wcbkcsttemplates"><span class="dashicons-before dashicons-admin-users"></span> <?php echo _e( 'Booking Cust. Temp', SmsAlertConstants::TEXT_DOMAIN );?></a>
				  </li>
				  
				  <li tab_type="wcbkadmintemplates" onclick="SMSAlert_change_nav(this, 'SMSAlert_nav_wcbkadmintemplates_box')" >
					<a href="#wcbkadmintemplates"><span class="dashicons-before dashicons-list-view"></span> <?php echo _e( 'Booking Admin Temp', SmsAlertConstants::TEXT_DOMAIN );?></a>
				  </li>
			 <?php	
			  }
			  if (is_plugin_active('affiliates-manager/boot-strap.php' )) //affiliate manager
			  {
			  ?>
				  <li tab_type="wpamcsttemplates" onclick="SMSAlert_change_nav(this, 'SMSAlert_nav_wpamcsttemplates_box')" >
					<a href="#wpamcsttemplates"><span class="dashicons-before dashicons-admin-users"></span> <?php echo _e( 'Affiliate Cust. Temp', SmsAlertConstants::TEXT_DOMAIN );?></a>
				  </li>
				  
				  <li tab_type="wpamadmintemplates" onclick="SMSAlert_change_nav(this, 'SMSAlert_nav_wpamadmintemplates_box')" >
					<a href="#wpamadmintemplates"><span class="dashicons-before dashicons-list-view"></span> <?php echo _e( 'Affiliate Admin Temp', SmsAlertConstants::TEXT_DOMAIN );?></a>
				  </li>
			 <?php	
			  }
			  if (is_plugin_active('learnpress/learnpress.php' )) //learnpress
			 {
			 ?>
			 <li tab_type="lpresscsttemplates" onclick="SMSAlert_change_nav(this, 'SMSAlert_nav_lpresscsttemplates_box')" >
			<a href="#lpresscsttemplates"><span class="dashicons-before dashicons-admin-users"></span> <?php echo _e( 'LPress Cust. Temp', SmsAlertConstants::TEXT_DOMAIN );?></a>
			 </li>
			 
			 <li tab_type="lpressadmintemplates" onclick="SMSAlert_change_nav(this, 'SMSAlert_nav_lpressadmintemplates_box')" >
			<a href="#lpressadmintemplates"><span class="dashicons-before dashicons-list-view"></span> <?php echo _e( 'LPress Admin Temp', SmsAlertConstants::TEXT_DOMAIN );?></a>
			 </li>
			<?php
			 }
			?> 
			   <li tab_type="callbacks" onclick="SMSAlert_change_nav(this, 'SMSAlert_nav_callbacks_box')" >
				<a href="#otp"><span class="dashicons-before dashicons-admin-settings"></span> <?php echo _e( 'Advanced Settings', SmsAlertConstants::TEXT_DOMAIN );?></a>
              </li>
			   <li tab_type="credits" onclick="SMSAlert_change_nav(this, 'SMSAlert_nav_credits_box')" class="<?php echo $credit_show?>">
				<a href="#credits"><span class="dashicons-before dashicons-admin-comments"></span> <?php echo _e( 'SMS Credits', SmsAlertConstants::TEXT_DOMAIN );?></a>
              </li>
			   <li tab_type="support" onclick="SMSAlert_change_nav(this, 'SMSAlert_nav_support_box')" >
				<a href="#support"><span class="dashicons-before dashicons-editor-help"></span> <?php echo _e( 'Support', SmsAlertConstants::TEXT_DOMAIN );?></a>
              </li>
            </ul>