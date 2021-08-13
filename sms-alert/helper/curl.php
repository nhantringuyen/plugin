<?php
class SmsAlertcURLOTP
{	
	public static function sendtemplatemismatchemail($template)
	{
		$username = smsalert_get_option( 'smsalert_name', 'smsalert_gateway', '');
		$To_mail=smsalert_get_option( 'alert_email', 'smsalert_general', '');
		
		//Email template with content
		$params = array(
                'template' => nl2br($template),
                'username' => $username,
                'server_name' => $_SERVER['SERVER_NAME'],
                'admin_url' => admin_url(),
        );
		$emailcontent = get_smsalert_template('template/emails/mismatch_template.php',$params);
		wp_mail( $To_mail, '❗ ✱ SMS Alert ✱ Template Mismatch', $emailcontent,'content-type:text/html');
	}
	
	public static function checkPhoneNos($nos=NULL)
	{
		$country_code = smsalert_get_option( 'default_country_code', 'smsalert_general' );
		
		$nos = explode(',',$nos);
		$valid_no=array();
		if(is_array($nos))
		{			
			foreach($nos as $no){
				$no = ltrim(ltrim($no, '+'),'0'); //remove leading + and 0
				$no = (substr($no,0,strlen($country_code))!=$country_code) ? $country_code.$no : $no;
				$match = preg_match(SmsAlertConstants::getPhonePattern(),$no);
				if($match)
				{
					$valid_no[] = $no;
				}
			}
		}
		
		if(sizeof($valid_no)>0)
		{
			$nos = implode(',',$valid_no);
			return $nos;
		}
		else
		{
			return false;
		}
	}

	public static function sendsms($sms_data) 
	{

        $response = false;
        $username = smsalert_get_option( 'smsalert_name', 'smsalert_gateway' );
        $password = smsalert_get_option( 'smsalert_password', 'smsalert_gateway' );
        $senderid = smsalert_get_option( 'smsalert_api', 'smsalert_gateway' );
		$enable_short_url = smsalert_get_option( 'enable_short_url', 'smsalert_general');
		
        $phone = self::checkPhoneNos($sms_data['number']);
		if($phone===false)
		{
			$data=array();
			$data['status']= "error";
			$data['description']= "phone number not valid";
			return json_encode($data);
		}
        $text = htmlspecialchars_decode($sms_data['sms_body']);
        //bail out if nothing provided
        if ( empty( $username ) || empty( $password ) || empty( $senderid ) || empty( $text ) ) {
            return $response;
        }

		$url = base64_decode("aHR0cDovL3d3dy5zbXNhbGVydC5jby5pbi9hcGkvcHVzaC5qc29u");
		$fields = array('user'=>$username, 'pwd'=>$password, 'mobileno'=>$phone, 'sender'=>$senderid, 'text'=>$text);
		
		if($enable_short_url=='on'){$fields['shortenurl']=1;}
		$json = json_encode($fields);
		
		$response = self::callAPI($url, $fields, null);
	
		$response_arr=json_decode($response,true);
		
		if($response_arr['status']=='error') {
			$error = (is_array($response_arr['description'])) ? $response_arr['description']['desc'] : $response_arr['description'];
			if($error == "Invalid Template Match")
			{
				self::sendtemplatemismatchemail($text);
			}
		}
        return $response;
    }
	
	public static function smsalert_send_otp_token($form, $email='', $phone='')
	{
		$phone = self::checkPhoneNos($phone);
		$cookie_value = get_smsalert_cookie($phone);
		$max_otp_resend_allowed = smsalert_get_option( 'max_otp_resend_allowed', 'smsalert_general');
		if(get_smsalert_cookie($phone)>$max_otp_resend_allowed)
		{
			$data=array();
			$data['status']= "error";
			$data['description']['desc']= SmsAlertMessages::showMessage('MAX_OTP_LIMIT');
			return json_encode($data);
		}
		
		
		
		$response = false;
		$username = smsalert_get_option( 'smsalert_name', 'smsalert_gateway' );
        $password = smsalert_get_option( 'smsalert_password', 'smsalert_gateway' );
        $senderid = smsalert_get_option( 'smsalert_api', 'smsalert_gateway' );
		$template = smsalert_get_option( 'sms_otp_send', 'smsalert_message', SmsAlertMessages::DEFAULT_BUYER_OTP);
		if($phone===false)
		{
			$data=array();
			$data['status']= "error";
			$data['description']['desc']= "phone number not valid";
			return json_encode($data);
		}
		
		
        if ( empty( $username ) || empty( $password ) || empty( $senderid ) ) {
            return $response;
        }
		
		$url = base64_decode("aHR0cDovL3d3dy5zbXNhbGVydC5jby5pbi9hcGkvbXZlcmlmeS5qc29u==");

		$fields = array('user'=>$username, 'pwd'=>$password, 'mobileno'=>$phone, 'sender'=>$senderid, 'template'=>$template);
		$json = json_encode($fields);
		$response = self::callAPI($url, $fields, null);	
		$response_arr = (array)json_decode($response,true);
		if(array_key_exists('status',$response_arr) && $response_arr['status']=='error') {
			$error = (is_array($response_arr['description'])) ? $response_arr['description']['desc'] : $response_arr['description'];
			if($error == "Invalid Template Match")
			{
				self::sendtemplatemismatchemail($template);
			}
		}
		else
		{
			create_smsalert_cookie($phone,$cookie_value+1);
		}
		
		return $response;
	}
	
	public static function validate_otp_token($mobileno,$otpToken)
	{
        $response = false;
		$username = smsalert_get_option( 'smsalert_name', 'smsalert_gateway' );
        $password = smsalert_get_option( 'smsalert_password', 'smsalert_gateway' );
        $senderid = smsalert_get_option( 'smsalert_api', 'smsalert_gateway' );
		$mobileno = self::checkPhoneNos($mobileno);
		if($mobileno===false)
		{
			$data=array();
			$data['status']= "error";
			$data['description']= "phone number not valid";
			return json_encode($data);
		}
		
        if ( empty( $username ) || empty( $password ) || empty( $senderid ) ) {
            return $response;
        }
		$url = base64_decode("aHR0cDovL3d3dy5zbXNhbGVydC5jby5pbi9hcGkvbXZlcmlmeS5qc29u");

		$fields = array('user'=>$username, 'pwd'=>$password, 'mobileno'=>$mobileno, 'code'=>$otpToken);
		
		$response    = self::callAPI($url, $fields, null);
		$content = json_decode($response,true);
		if(isset($content['description']['desc']) && strcasecmp($content['description']['desc'], 'Code Matched successfully.') == 0) {
			clear_smsalert_cookie($mobileno);
		}
		
		
		return $response;
	}
	
	public static function get_senderids( $username=NULL, $password = NULL)
    {
	   if ( empty( $username ) || empty( $password ) ) {
			return '';
       }
               
       $url = base64_decode("aHR0cDovL3d3dy5zbXNhbGVydC5jby5pbi9hcGkvc2VuZGVybGlzdC5qc29u");

		$fields = array('user'=>$username, 'pwd'=>$password);

		$response = self::callAPI($url, $fields, null);
		return $response;
    }
	
	public static function get_templates( $username=NULL, $password = NULL)
    {
	   if ( empty( $username ) || empty( $password ) ) {
			return '';
       }
       $url = base64_decode("aHR0cDovL3d3dy5zbXNhbGVydC5jby5pbi9hcGkvdGVtcGxhdGVsaXN0Lmpzb24=");

		$fields = array('user'=>$username, 'pwd'=>$password);

		$response = self::callAPI($url, $fields, null);
		return $response;
    }
	
	public static function get_credits()
    {
       $response = false;
	   $username = smsalert_get_option( 'smsalert_name', 'smsalert_gateway' );
       $password = smsalert_get_option( 'smsalert_password', 'smsalert_gateway' );
	   
	   if ( empty( $username ) || empty( $password ) ) {
			return $response;
       }
               
       $url = base64_decode("aHR0cDovL3d3dy5zbXNhbGVydC5jby5pbi9hcGkvY3JlZGl0c3RhdHVzLmpzb24=");

		$fields = array('user'=>$username, 'pwd'=>$password);
		$response    = self::callAPI($url, $fields, null);
		return $response;
	} 
	
	public static function group_list()
    {
       $username = smsalert_get_option( 'smsalert_name', 'smsalert_gateway' );
       $password = smsalert_get_option( 'smsalert_password', 'smsalert_gateway' );
	   
	   if ( empty( $username ) || empty( $password ) ) {
			return '';
       }
               
       $url = base64_decode("aHR0cDovL3d3dy5zbXNhbGVydC5jby5pbi9hcGkvZ3JvdXBsaXN0Lmpzb24=");

		$fields = array('user'=>$username, 'pwd'=>$password);

		$response    = self::callAPI($url, $fields, null);
		return $response;
    }

	public static function country_list()
    {
        $url = base64_decode("aHR0cDovL3d3dy5zbXNhbGVydC5jby5pbi9hcGkvY291bnRyeWxpc3QuanNvbg==");
		$response    = self::callAPI($url, null, null);
		return $response;
    }	
		
	public static function creategrp()
    {
       $username = smsalert_get_option( 'smsalert_name', 'smsalert_gateway' );
       $password = smsalert_get_option( 'smsalert_password', 'smsalert_gateway' );
	   
	   if ( empty( $username ) || empty( $password ) ) {
			return '';
       }
               
       $url = base64_decode("aHR0cDovL3d3dy5zbXNhbGVydC5jby5pbi9hcGkvY3JlYXRlZ3JvdXAuanNvbg==");

		$fields = array('user'=>$username, 'pwd'=>$password, 'name'=>$_SERVER['SERVER_NAME']);

		$response    = self::callAPI($url, $fields, null);
		return $response;
    } 	
	
	public static function create_contact($group_name=null,$name=null,$mob=null)
    {
       $username = smsalert_get_option( 'smsalert_name', 'smsalert_gateway' );
       $password = smsalert_get_option( 'smsalert_password', 'smsalert_gateway' );
	   
	   if ( empty( $username ) || empty( $password ) ) {
			return '';
       }
               
       $url = base64_decode("aHR0cDovL3d3dy5zbXNhbGVydC5jby5pbi9hcGkvY3JlYXRlY29udGFjdC5qc29u");

		$fields = array('user'=>$username, 'pwd'=>$password,'grpname'=>$group_name,'name'=>$name,'number'=>$mob);
		$response    = self::callAPI($url, $fields, null);
		return $response;
    } 
		
	public static function callAPI($url, $params, $headers = array("Content-Type: application/json"))
	{
		$extra_params = array('plugin'=>'woocommerce', 'website'=>$_SERVER['SERVER_NAME']);
		$params = (!is_null($params)) ? array_merge($params, $extra_params) : $extra_params;
		
		$args=array('body'=>$params);
		$response = wp_remote_post($url,$args);
		return wp_remote_retrieve_body($response);
	}
}