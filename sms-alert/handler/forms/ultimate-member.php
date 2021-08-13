<?php
	class UltimateMemberRegistrationForm extends FormInterface
	{
		
		private $formSessionVar = FormSessionVars::UM_DEFAULT_REG;
		private $phoneFormID 	= "input[name^='billing_phone']";
		

		function handleForm()
		{
			
			
			//add_action( 'um_submit_form_errors_hook_', array($this,'smsalert_um_phone_validation'), 99,1);
			
			//if (is_plugin_active( 'ultimate-member/ultimate-member.php' ) && !isset($_POST['smsalert_otp_token_submit'])) //>= UM version 2.0.17

			if (is_plugin_active( 'ultimate-member/ultimate-member.php' )) //>= UM version 2.0.17			
			{
				add_filter( 'um_add_user_frontend_submitted', array($this,'smsalert_um_user_registration'), 1,1);
			}
			else //< UM version 2.0.17 
			{
				add_action( 'um_before_new_user_register', array($this,'smsalert_um_user_registration'), 1,1);
			}
		}

		public static function isFormEnabled() 
		{
			return (smsalert_get_option('buyer_signup_otp', 'smsalert_general')=="on") ? true : false;
		}

			 
		function smsalert_um_user_registration($args)
		{
			
			SmsAlertUtility::checkSession();
			$errors = new WP_Error();
			
			if(isset($_SESSION['sa_um_mobile_verified']))
			{
				unset($_SESSION['sa_um_mobile_verified']);
				return $args;
			}
			
			SmsAlertUtility::initialize_transaction($this->formSessionVar);
			
			foreach ($args as $key => $value)
			{
				if($key=="user_login")
					$username = $value;
				elseif ($key=="user_email")
					$email = $value;
				elseif ($key=="user_password")
					$password = $value;
				elseif ($key == 'billing_phone')
					$phone_number = $value;
				else
					$extra_data[$key]=$value;
			}
			
			$this->startOtpTransaction($username,$email,$errors,$phone_number,$password,$extra_data);
			exit();
		}

		function startOtpTransaction($username,$email,$errors,$phone_number,$password,$extra_data)
		{
			smsalert_site_challenge_otp($username,$email,$errors,$phone_number,"phone",$password,$extra_data);
		}

		function handle_failed_verification($user_login,$user_email,$phone_number)
		{
			SmsAlertUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar])) return;
			smsalert_site_otp_validation_form($user_login,$user_email,$phone_number,SmsAlertUtility::_get_invalid_otp_method(),"phone",FALSE);
		}

		function handle_post_verification($redirect_to,$user_login,$user_email,$password,$phone_number,$extra_data)
		{
			SmsAlertUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar])) return;
			$_SESSION['sa_um_mobile_verified']=true;
		}

		public function unsetOTPSessionVariables()
		{
			unset($_SESSION[$this->txSessionId]);
			unset($_SESSION[$this->formSessionVar]);
		}

		public function is_ajax_form_in_play($isAjax)
		{
			SmsAlertUtility::checkSession();
			return isset($_SESSION[$this->formSessionVar]) ? FALSE : $isAjax;
		}

		public function getPhoneNumberSelector($selector)	
		{
			SmsAlertUtility::checkSession();
			if(self::isFormEnabled()) array_push($selector, $this->phoneFormID); 
			return $selector;
		}

		function handleFormOptions()
	    {
			
	    }
	}
	new UltimateMemberRegistrationForm;