<?php
	require_once 'forms/woocommerce/wc-checkout.php';
	require_once 'forms/woocommerce/wc-registration.php';
	require_once 'forms/wp-login.php';
	require_once 'forms/ultimate-member.php';
	require_once 'forms/cf7.php';
	require_once 'forms/wp-member.php';
	require_once 'forms/pie-registration.php';
	require_once 'forms/affilate-manager.php';
	require_once 'forms/wp-reset-password.php';
	require_once 'forms/learnpress-registration.php';
	
	add_action(	'init', 'smsalert_customer_validation_handle_form' , 1 );
	add_action( 'smsalert_validate_otp', '_handle_validation_form_action' , 1, 2);

	function smsalert_site_challenge_otp($user_login, $user_email, $errors, $phone_number=null,$otp_type,$password="",$extra_data=null,$from_both=false)
	{
		SmsAlertUtility::checkSession();
		$_SESSION['current_url'] 	= SmsAlertUtility::currentPageUrl();
		$_SESSION['user_email'] 	= $user_email;
		$_SESSION['user_login'] 	= $user_login;
		$_SESSION['user_password'] 	= $password;
		$_SESSION['phone_number_mo']= $phone_number;
		$_SESSION['extra_data'] 	= $extra_data;
		_handle_otp_action($user_login,$user_email,$phone_number,$otp_type,$from_both);
	}

	function _handle_verification_resend_otp_action($otp_type,$from_both)
	{
		SmsAlertUtility::checkSession();
		$user_email 	= $_SESSION['user_email'];
		$user_login 	= $_SESSION['user_login'];
		$password 		= $_SESSION['user_password'];
		$phone_number 	= $_SESSION['phone_number_mo'];
		$extra_data 	= $_SESSION['extra_data'];
		_handle_otp_action($user_login,$user_email,$phone_number,$otp_type,$from_both);
	}

	function _handle_otp_action($user_login,$user_email,$phone_number,$otp_type,$form)
	{
		global $phoneLogic;
		$phoneLogic->_handle_logic($user_login,$user_email,$phone_number,$otp_type,$form);
	}

	function _handle_validation_goBack_action()
	{
		SmsAlertUtility::checkSession();
		$url = isset($_SESSION['current_url'])? $_SESSION['current_url'] : '';
		session_unset();
		wp_redirect($url);
		exit();
	}
	
	function _handle_validation_form_action($requestVariable='smsalert_customer_validation_otp_token',$from_both=false)
	{
		SmsAlertUtility::checkSession();
		$_REQUEST		= smsalert_sanitize_array($_REQUEST);
		$user_login 	= !SmsAlertUtility::isBlank($_SESSION['user_login']) ? $_SESSION['user_login'] 						 	: null;
		$user_email 	= !SmsAlertUtility::isBlank($_SESSION['user_email']) ? $_SESSION['user_email'] 							: null;
		$phone_number 	= (array_key_exists('billing_phone',$_REQUEST) && !SmsAlertUtility::isBlank($_REQUEST['billing_phone']))? $_REQUEST['billing_phone'] 											: null;
		$phone_number 	= array_key_exists('phone_number_mo', $_SESSION) && !SmsAlertUtility::isBlank($_SESSION['phone_number_mo']) ? $_SESSION['phone_number_mo'] : $phone_number;
		$password 		= !SmsAlertUtility::isBlank($_SESSION['user_password']) 					? $_SESSION['user_password'] 						: null;
		$extra_data 	= !SmsAlertUtility::isBlank($_SESSION['extra_data']) 						? $_SESSION['extra_data'] 							: null;
		//$txID 			= !SmsAlertUtility::isBlank($_SESSION['mo_customer_validation_site_txID'])? $_SESSION['mo_customer_validation_site_txID' ] 	: null;
		$requestVariable = (array_key_exists('phone',$_REQUEST) && !array_key_exists('smsalert_customer_validation_otp_token',$_REQUEST))?$_REQUEST['phone']:'smsalert_customer_validation_otp_token';
		
		$requestVariable = array_key_exists('order_verify',$_REQUEST)?'order_verify':$requestVariable;
		
		$otp_token 		= !SmsAlertUtility::isBlank($_REQUEST[$requestVariable])? $_REQUEST[$requestVariable] : null;
	
		$content = json_decode(SmsAlertcURLOTP::validate_otp_token($phone_number, $otp_token),true);
		if($content['status']=='success' && isset($content['description']['desc']) && strcasecmp($content['description']['desc'], 'Code Matched successfully.') == 0) {
			_handle_success_validated($user_login,$user_email,$password,$phone_number,$extra_data);
		}else{
			_handle_error_validated($user_login,$user_email,$phone_number);
		}
	}

	function _handle_success_validated($user_login,$user_email,$password,$phone_number,$extra_data)
	{		
		$redirect_to = array_key_exists('redirect_to', $_POST) ? $_POST['redirect_to'] : '';
		do_action('otp_verification_successful',$redirect_to,$user_login,$user_email,$password,$phone_number,$extra_data);
	}

	function _handle_error_validated($user_login,$user_email,$phone_number)
	{	
		do_action('otp_verification_failed',$user_login,$user_email,$phone_number);
	}
	
	function _handle_validate_otp_choice_form($postdata)
	{
		SmsAlertUtility::checkSession();
		if($postdata['mo_customer_validation_otp_choice'] == 'user_email_verification')
			smsalert_site_challenge_otp($_SESSION['user_login'],$_SESSION['user_email'],null,$_SESSION['phone_number_mo'],"email",$_SESSION['user_password'],$_SESSION['extra_data'],true);
		else 
			smsalert_site_challenge_otp($_SESSION['user_login'],$_SESSION['user_email'],null,$_SESSION['phone_number_mo'],"phone",$_SESSION['user_password'],$_SESSION['extra_data'],true);
	}

	function _handle_mo_ajax_phone_validate($getdata)
	{
		SmsAlertUtility::checkSession();
		$_SESSION[FormSessionVars::AJAX_FORM] = trim($getdata['billing_phone']);
		smsalert_site_challenge_otp($_SESSION['user_login'],null,null, trim($data['billing_phone']),"phone",$_SESSION['user_password'],null, null);
	}
	
	function _handle_mo_ajax_form_validate_action()
	{
		SmsAlertUtility::checkSession();
		if(isset($_SESSION[FormSessionVars::WC_SOCIAL_LOGIN]))
		{
			_handle_validation_form_action();
			if($_SESSION[FormSessionVars::WC_SOCIAL_LOGIN]=='validated')
				wp_send_json( SmsAlertUtility::_create_json_response('successfully validated','success') );
			else
				wp_send_json( SmsAlertUtility::_create_json_response(SmsAlertMessages::INVALID_OTP,'error'));
		}
	}

	function _handle_mo_create_user_wc_action($postdata)
	{
		SmsAlertUtility::checkSession();
		if(isset($_SESSION[FormSessionVars::WC_SOCIAL_LOGIN]) && $_SESSION[FormSessionVars::WC_SOCIAL_LOGIN]=='validated')
			create_new_wc_social_customer($postdata);
	}

	function smsalert_customer_validation_handle_form()
	{
		if(array_key_exists('option', $_REQUEST) && $_REQUEST['option'])
		{
			switch (trim($_REQUEST['option'])) 
			{
				case "validation_goBack":
					_handle_validation_goBack_action();								break;
				case "smsalert-ajax-otp-generate":
					_handle_mo_ajax_phone_validate($_GET);							break;
				case "smsalert-ajax-otp-validate":
					_handle_mo_ajax_form_validate_action($_GET);					break;
				case "smsalert_ajax_form_validate":
					_handle_mo_create_user_wc_action($_POST);						break;
				case "smsalert-validate-otp-form":
					$from_both = $_POST['from_both']=='true' ? true : false;
					_handle_validation_form_action();	break;
				case "verification_resend_otp_phone":
					$from_both = $_POST['from_both']=='true' ? true : false;
					_handle_verification_resend_otp_action("phone",trim($_REQUEST['option'])); 	break;
				case "verification_resend_otp_email":
					$from_both = $_POST['from_both']=='true' ? true : false;
					_handle_verification_resend_otp_action("email",trim($_REQUEST['option']));		break;
				case "verification_resend_otp_both":
					$from_both = $_POST['from_both']=='true' ? true : false;
					_handle_verification_resend_otp_action("both",trim($_REQUEST['option']));		break;
				case "smsalert-validate-otp-choice-form":
					_handle_validate_otp_choice_form($_POST);						break;
																break;
			}
		}
	}
?>