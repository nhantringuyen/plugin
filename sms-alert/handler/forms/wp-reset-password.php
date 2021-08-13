<?php
	class WPResetPasswordForm extends FormInterface
	{
		private $formSessionVar = FormSessionVars::WP_DEFAULT_LOST_PWD;
		private $phoneNumberKey;

		function handleForm()
		{	
			$this->phoneNumberKey = 'billing_phone';
			add_action( 'retrieve_password', array($this,'startSmsalertResetPasswordProcess'), 10, 1 );
			$this->routeData();
		}
		
		function routeData()
		{
			if (!empty($_REQUEST['option']) && $_REQUEST['option']=="smsalert-change-password-form") 
			{
				$this->_handle_smsalert_changed_pwd($_POST);
			} 
		}
				
		public static function isFormEnabled() 
		{
			return (smsalert_get_option('reset_password', 'smsalert_general')=="on") ? true : false;
		}
		
		function _handle_smsalert_changed_pwd($post_data)
		{
			SmsAlertUtility::checkSession();
			$error='';
			$new_password = !empty($post_data['smsalert_user_newpwd']) ? $post_data['smsalert_user_newpwd'] : '' ;
			$confirm_password = !empty($post_data['smsalert_user_cnfpwd']) ? $post_data['smsalert_user_cnfpwd'] : '';
			
			if ($new_password=='') {
				$error = 'Please enter your password.';
			}
			if ($new_password !== $confirm_password ){
				$error ='Passwords do not match.';
			}
			if(!empty($error))
			{
				smsalertAskForResetPassword($_SESSION['user_login'],$_SESSION['phone_number_mo'], $error, 'phone',false);
				
			}
			$user = get_user_by( 'login', $_SESSION['user_login'] );
			reset_password( $user, $new_password );
			$this->unsetOTPSessionVariables();
			wp_redirect( add_query_arg( 'password-reset', 'true', wc_get_page_permalink( 'myaccount' ) ) );
			exit;
		}
		
		function startSmsalertResetPasswordProcess($user_login)
		{
			SmsAlertUtility::checkSession();	
			$user = get_user_by( 'login', $user_login );
			$phone_number = get_user_meta($user->data->ID, $this->phoneNumberKey,true);
			if(isset($_REQUEST['wc_reset_password']))
			{
				SmsAlertUtility::initialize_transaction($this->formSessionVar);
				if($phone_number!='')
				{
					$this->fetchPhoneAndStartVerification($user->data->user_login,$this->phoneNumberKey,NULL,NULL,$phone_number);
				}
			}
			return $user;
		} 

		function fetchPhoneAndStartVerification($user,$key,$username,$password,$phone_number)
		{
			if((array_key_exists($this->formSessionVar,$_SESSION) && strcasecmp($_SESSION[$this->formSessionVar],'validated')==0)) return;
			smsalert_site_challenge_otp($user,$username,null,$phone_number,"phone",$password,SmsAlertUtility::currentPageUrl(),false);
		}

		function handle_failed_verification($user_login,$user_email,$phone_number)
		{
			SmsAlertUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar])) return;

			if(isset($_SESSION[$this->formSessionVar])){	
				$_SESSION[$this->formSessionVar] = 'verification_failed';
				//wp_send_json( SmsAlertUtility::_create_json_response(SMSAlertMessages::INVALID_OTP,'error'));
				smsalert_site_otp_validation_form($user_login,$user_email,$phone_number,SMSAlertMessages::INVALID_OTP,"phone",FALSE);
			}
		}

		function handle_post_verification($redirect_to,$user_login,$user_email,$password,$phone_number,$extra_data)
		{
			SmsAlertUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar])) return;
			smsalertAskForResetPassword($_SESSION['user_login'],$_SESSION['phone_number_mo'], "Please change Your password", 'phone',false);
		}

		public function unsetOTPSessionVariables()
		{
			unset($_SESSION[$this->formSessionVar]);
		}

		public function is_ajax_form_in_play($isAjax)
		{
			SmsAlertUtility::checkSession();
			return isset($_SESSION[$this->formSessionVar]) ? FALSE : $isAjax;
		}

		function handleFormOptions()
	    {
			
	    }
	}
	new WPResetPasswordForm;