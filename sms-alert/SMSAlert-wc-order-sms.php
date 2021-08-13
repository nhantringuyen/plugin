<?php
/*
 * Plugin Name: SMSAlert - WooCommerce
 * Plugin URI: https://wordpress.org/plugins/sms-alert/
 * Description: This is a WooCommerce add-on. By Using this plugin admin and buyer can get notification after placing order via sms using SMS Alert.
 * Version: 3.2.1
 * Author: Cozy Vision Technologies Pvt. Ltd. 
 * Author URI: https://www.smsalert.co.in
 * Text Domain: sms-alert
 * WC requires at least: 2.0.0
 * WC tested up to: 3.7.1
 * License: GPL2
 */

/**
 * 
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

// Lib Directory Path Constant
define( 'PLUGIN_LIB_PATH', dirname(__FILE__). '/lib' );
define( 'SMSALERT_PLUGIN_VERSION', plugin_get_version());

// Requere settings api
require_once PLUGIN_LIB_PATH. '/class.settings-api.php';

function plugin_get_version() {
	if ( ! function_exists( 'get_plugins' ) )
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	$plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
	$plugin_file = basename( ( __FILE__ ) );
	return $plugin_folder[$plugin_file]['Version'];

}

function smsalert_sanitize_array($arr) 
{
	  $result = array();
	  foreach ($arr as $key => $val)
	  {
		  $result[$key] = is_array($val) ? smsalert_sanitize_array($val) : sanitize_text_field($val);
	  }
	  return $result;
}

function create_smsalert_cookie($cookie_key,$cookie_value)
{
	ob_start();
	setcookie($cookie_key,$cookie_value, time()+(15 * 60));
	ob_get_clean();
}
	
function clear_smsalert_cookie($cookie_key)
{	
	if(isset($_COOKIE[$cookie_key])){
		unset($_COOKIE[$cookie_key]);
		setcookie( $cookie_key, '', time() - ( 15 * 60 ) );
	}
}

function get_smsalert_cookie($cookie_key)
{
	if(!isset($_COOKIE[$cookie_key])) {
	  return false;
	} else {
	  return $_COOKIE[$cookie_key];
	}
}
 
function smsalert_get_option( $option, $section, $default = '' ) {
    $options = get_option( $section );

    if ( isset( $options[$option] ) ) {
        return $options[$option];
    }
    return $default;
}

function get_smsalert_template($filepath,$datas)
{
		ob_start();
		extract($datas);
		include(plugin_dir_path( __DIR__ ).'sms-alert/'.$filepath);
		return ob_get_clean();
}

class smsalert_WC_Order_SMS {
	
    /**
     * Constructor for the smsalert_WC_Order_SMS class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses is_admin()
     * @uses add_action()
     */
    public function __construct() {
		
		// Instantiate necessary class
        $this->instantiate();
		
        // Localize our plugin
        add_action( 'init', array( $this, 'localization_setup' ) );
		add_action( 'init', array($this, 'register_hook_send_sms'));
        
		//added on 30-01-2019 for user registeration
		add_action('smsalert_after_update_new_user_phone', array( $this,  'smsalert_after_user_register'), 10, 2 );
		
        add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'buyer_notification_update_order_meta' ) );
        add_action( 'woocommerce_order_status_changed', array( $this, 'trigger_after_order_place' ), 10, 3 );
		if(class_exists('WooCommerce_Warranty')) {		
			add_action( 'admin_post_wc_warranty_settings_update', array($this, 'update_wc_warranty_settings'),5 );
			add_action( 'wp_ajax_warranty_update_request_fragment', array($this, 'on_rma_status_update'),0 );
			add_action( 'wc_warranty_created',  array($this, 'on_new_rma_request'),5);
		}
		
		if(class_exists('WPCF7')) {	
			if(smsalert_get_option('allow_query_sms', 'smsalert_general')!="off") {
				add_filter( 'wpcf7_editor_panels' , array($this, 'new_menu_sms_alert'),98);
				add_action( 'wpcf7_after_save', array( &$this, 'save_form' ) );
			}
		}
		
		if ( is_plugin_active( 'gravityforms-master/gravityforms.php' ) || is_plugin_active('gravityforms/gravityforms.php' )) 
		{
			require_once 'handler/forms/gravity-form.php';
			add_action( 'gform_after_submission', array( $this, 'do_gForm_processing' ), 10, 2 );
		}
		

		require_once 'helper/formlist.php';
		require_once 'views/common-elements.php';
		require_once 'handler/forms/FormInterface.php';
		require_once 'handler/smsalert_form_handler.php';
		
		if(is_admin())
		{
			
			add_action( 'add_meta_boxes', array($this, 'add_send_sms_meta_box') );
			add_action( 'wp_ajax_wc_sms_alert_sms_send_order_sms', array( $this,'send_custom_sms'));
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'woocommerce_new_customer_note', array($this, 'trigger_new_customer_note'), 10 );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta_link' ), 10, 4 );
			add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_action_links') );
		}

		/*code to notify for daily balance begins */
		add_action ('smsalert_balance_notify',array($this, 'background_task'));
	}
	
    /**
     * Instantiate necessary Class
     * @return void
     */
    function instantiate() {
		spl_autoload_register( array($this, 'smsalert_sms_autoload') );
        new smsalert_Setting_Options();
    }

	/**
	 * Autoload class files on demand
	 *
	 * @param string $class requested class name
	 */	
	function smsalert_sms_autoload( $class ) {

		require_once 'handler/smsalert_logic_interface.php';
		require_once 'handler/smsalert_phone_logic.php';
		require_once 'helper/sessionVars.php';
		require_once 'helper/utility.php';
		require_once 'helper/constants.php';
		require_once 'helper/messages.php';
		require_once 'helper/curl.php';
		
		if ( stripos( $class, 'smsalert_' ) !== false ) {

			$class_name = str_replace( array('smsalert_', '_'), array('', '-'), $class );
			$filename = dirname( __FILE__ ) . '/classes/' . strtolower( $class_name ) . '.php';

			if ( file_exists( $filename ) ) {
				require_once $filename;
			}
		}
	}
	
    /**
     * Initializes the SMSAlert_WC_Order_SMS() class
     *
     * Checks for an existing SMSAlert_WC_Order_SMS() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new SMSAlert_WC_Order_SMS();
        }
        return $instance;
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public static function localization_setup() {
		load_plugin_textdomain( 'sms-alert', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
		
	/*send sms on user registration dated 30-01-2019*/
	function smsalert_after_user_register($user_id,$billing_phone) 
	{
	  
	  $smsalert_reg_notify 			= smsalert_get_option( 'registration_msg', 'smsalert_general', 'off');
	  $sms_body_new_user 			= smsalert_get_option( 'sms_body_registration_msg', 'smsalert_message', SmsAlertMessages::DEFAULT_NEW_USER_REGISTER );	  
	  $smsalert_reg_admin_notify 	= smsalert_get_option( 'admin_registration_msg', 'smsalert_general', 'off');
	  $sms_admin_body_new_user 		= smsalert_get_option( 'sms_body_registration_admin_msg', 'smsalert_message', SmsAlertMessages::DEFAULT_ADMIN_NEW_USER_REGISTER );
	  $admin_phone_number     		= smsalert_get_option( 'sms_admin_phone', 'smsalert_message', '' );
	  $user 						= get_userdata($user_id);
	
	  /*let's send message to user on new registration*/
	  if($smsalert_reg_notify=='on' && $billing_phone!='')
	  {
			$search = array(
				'[username]',
				'[store_name]',
				'[email]',
				'[billing_phone]'
			);
			
			$replace = array(
				$user->user_login,
				get_bloginfo(),
				$user->user_email,
				$billing_phone
			);
			
			$sms_body_new_user 			= str_replace($search,$replace,$sms_body_new_user);
			$buyer_sms_data['number'] 	= $billing_phone;
			$buyer_sms_data['sms_body'] = $sms_body_new_user;
			$buyer_response 			= SmsAlertcURLOTP::sendsms( $buyer_sms_data );
	  }
	  
	  /*let's send message to admin on new registration*/
	  if($smsalert_reg_admin_notify=='on' && $admin_phone_number!='')
	  {
			$search=array(
				'[username]',
				'[store_name]',
				'[email]',
				'[billing_phone]'
			);
			
			$replace=array(
				$user->user_login,
				get_bloginfo(),
				$user->user_email,
				$billing_phone
			);
			
			$sms_admin_body_new_user = str_replace($search,$replace,$sms_admin_body_new_user);
			$admin_sms_data['number'] 	= $admin_phone_number;
			$admin_sms_data['sms_body'] = $sms_admin_body_new_user;
			$admin_response = SmsAlertcURLOTP::sendsms( $admin_sms_data );
	  }
	}
	
	

	function fn_sa_send_sms($number, $content)
	{
		$obj=array();
		$obj['number'] = $number;
		$obj['sms_body'] = $content;
		$response = SmsAlertcURLOTP::sendsms($obj);
		return $response;
	}

	function register_hook_send_sms()
	{
		add_action( 'sa_send_sms', array($this, 'fn_sa_send_sms'), 10, 2 ); 
	}
	
    public function admin_enqueue_scripts() {

        wp_enqueue_style( 'admin-smsalert-styles', plugins_url( 'css/admin.css', __FILE__ ), false, date( 'Ymd' ) );
        wp_enqueue_script( 'admin-smsalert-scripts', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), false, true );
        wp_enqueue_script( 'admin-smsalert-scriptss', plugins_url( 'js/chosen.jquery.min.js', __FILE__ ), array( 'jquery' ), false, false );

        wp_localize_script( 'admin-smsalert-scripts', 'smsalert', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' )
        ) );
		
    }

    public function plugin_row_meta_link( $plugin_meta, $plugin_file, $plugin_data, $status ) 
	{

        if( isset( $plugin_data['slug'] ) && ( $plugin_data['slug'] == 'sms-alert' ) && ! defined( 'smsalert_DIR' ) ) {
			$plugin_meta[] = '<a href="http://kb.smsalert.co.in/knowledgebase/woocommerce-sms-notifications/" target="_blank">Documentation</a>';
			$plugin_meta[] = '<a href="https://wordpress.org/support/plugin/sms-alert/reviews/#postform" target="_blank" class="wc-rating-link">★★★★★</a>';
        }
        return $plugin_meta;
    }
	
	function add_action_links ( $links ) {
		$links[] = sprintf('<a href="%s">Settings</a>', admin_url('admin.php?page=sms-alert') );
		return $links;
	}

	function add_send_sms_meta_box(){
			add_meta_box(
				'wc_sms_alert_send_sms_meta_box',
				'SMS Alert (Custom SMS)',
				array($this, 'display_send_sms_meta_box'),
				'shop_order',
				'side',
				'default'
			);
	}
	
	function display_send_sms_meta_box($data){
		global $woocommerce, $post;
		$order = new WC_Order($post->ID);
		$order_id = $post->ID;
		
		$username = smsalert_get_option( 'smsalert_name', 'smsalert_gateway' );
		$password = smsalert_get_option( 'smsalert_password', 'smsalert_gateway' );
		$result = SmsAlertcURLOTP::get_templates($username, $password);
		$templates = json_decode($result, true);
		?>
		<select name="smsalert_templates" id="smsalert_templates" style="width:87%;" onchange="return selecttemplate(this, '#wc_sms_alert_sms_order_message');">
		<option value="">Select Template</option>
		<?php
		if(array_key_exists('description', $templates) && (!array_key_exists('desc', $templates['description']))) {
		foreach($templates['description'] as $template) {
		?>
		<option value="<?php echo $template['Smstemplate']['template'] ?>"><?php echo $template['Smstemplate']['title'] ?></option>
		<?php } } ?>
		</select>
		<span class="woocommerce-help-tip" data-tip="You can add templates from your www.smsalert.co.in Dashboard"></span>
										<p><textarea type="text" name="wc_sms_alert_sms_order_message" id="wc_sms_alert_sms_order_message" class="input-text" style="width: 100%;" rows="4" value=""></textarea></p>
			<input type="hidden" class="wc_sms_alert_order_id" id="wc_sms_alert_order_id" value="<?php echo $order_id;?>" >
			<p><a class="button tips" id="wc_sms_alert_sms_order_send_message" data-tip="Send an SMS to the billing phone number for this order.">Send SMS</a>
			<span id="wc_sms_alert_sms_order_message_char_count" style="color: green; float: right; font-size: 16px;">0</span></p>
			<?php
	}
	
	//return only single credit i.e onlyone route(transactional credit)
	static function only_credit(){
		$trans_credit = 0;
		$credits = json_decode(SmsAlertcURLOTP::get_credits(),true);   //credit json
		if(is_array($credits['description']) && array_key_exists('routes', $credits['description']))
		{
			foreach($credits['description']['routes'] as $credit){
				if($credit['route']=='transactional'){
					$trans_credit = $credit['credits'];
				}
			}
		}
		return $trans_credit;
	 }
		
	static function run_on_activate()
	{
		if( !wp_next_scheduled( 'smsalert_balance_notify' ) )
		{
			wp_schedule_event( time(), 'hourly', 'smsalert_balance_notify');
		}
	}

	static function run_on_deactivate()
	{
		wp_clear_scheduled_hook('smsalert_balance_notify');
	}

	function background_task()
	{
		$low_bal_alert = smsalert_get_option( 'low_bal_alert', 'smsalert_general', 'off');
		$daily_bal_alert = smsalert_get_option( 'daily_bal_alert', 'smsalert_general', 'off');
		$user_authorize = new smsalert_Setting_Options();
		$islogged = $user_authorize->isUserAuthorised();
		$auto_sync = smsalert_get_option( 'auto_sync', 'smsalert_general', 'off');
		if($islogged == true) 
		{
			if($auto_sync == 'on')
			{
				self::sync_customers();
			}
		}
		if($low_bal_alert == 'on'){self::send_smsalert_balance();}
		if($daily_bal_alert == 'on'){self::daily_email_alert();}
		
	}
	static function sync_customers()
	{
		$group_name 	= smsalert_get_option( 'group_auto_sync', 'smsalert_general', '');
		$update_id 		= smsalert_get_option( 'last_sync_userId','smsalert_sync','');
		$update_id 		= ($update_id!='') ? $update_id : 3;
		
		global $wpdb;
		
		$sql 			= $wpdb->prepare(
		"SELECT ID FROM {$wpdb->users} WHERE {$wpdb->users}.ID > %d order by ID asc limit 10 ",
		$update_id
		);
		
		$uids = $wpdb->get_col( $sql );
		if(sizeof($uids)==0)
		{
			echo 'No New users found.';
		}
		else
		{
			$user_query = new WP_User_Query( array( 'include' => $uids  ,'orderby' => 'id', 'order' => 'ASC') );
			if ( $user_query->get_results()) {
				 foreach ( $user_query->get_results() as $user ) {
					 $number = get_user_meta($user->ID, 'billing_phone', true);
					 SmsAlertcURLOTP::create_contact($group_name, $user->display_name, $number);
					$last_sync_id = $user->ID;
				}
				
				update_option('smsalert_sync',array('last_sync_userId'=>$last_sync_id));//update last_sync_id
				
			 } else {
				 echo 'No users found.';
			 }
		}
	}
	
	static function send_smsalert_balance()
	{
		$date = date("Y-m-d");	
		$update_dateTime = smsalert_get_option( 'last_updated_lBal_alert','smsalert_background_task','');
		
		if($update_dateTime == $date)
		{
			return;
		}
		 
		$username = smsalert_get_option( 'smsalert_name', 'smsalert_gateway', '');  //smsalert auth username
		$low_bal_val = smsalert_get_option( 'low_bal_val', 'smsalert_general', '');  //from alert box
		$To_mail=smsalert_get_option( 'alert_email', 'smsalert_general', '');
		$trans_credit = self::only_credit();
		
		//Email template with content
		$params = array(
                'trans_credit' => $trans_credit,
                'username' => $username,
                'admin_url' => admin_url(),
        );
		$emailcontent = get_smsalert_template('template/emails/smsalert-low-bal.php',$params);
		update_option('smsalert_background_task',array('last_updated_lBal_alert'=>date('Y-m-d')));//update last time and date 
		if($trans_credit <= $low_bal_val)
		{
			 wp_mail( $To_mail, '❗ ✱ SMS Alert ✱ Low Balance Alert', $emailcontent,'content-type:text/html');
		}	
	}

	function daily_email_alert(){
		$username = smsalert_get_option( 'smsalert_name', 'smsalert_gateway', '');  //smsalert auth username
		$date = date("Y-m-d");	
		$To_mail=smsalert_get_option( 'alert_email', 'smsalert_general', '');
		$update_dateTime = smsalert_get_option( 'last_updated_dBal_alert','smsalert_background_dBal_task','');
		
		if($update_dateTime == $date)
		{
			return;
		}
		
		$daily_credits = self::only_credit();
		//email content
		$params = array(
                'daily_credits' => $daily_credits,
                'username' => $username,
                'date' => $date,
                'admin_url' => admin_url(),
        );
		$dailyemailcontent = get_smsalert_template('template/emails/daily_email_alert.php',$params);
		update_option('smsalert_background_dBal_task',array('last_updated_dBal_alert'=>date('Y-m-d')));//update last time and date 
		wp_mail($To_mail, '✱ SMS Alert ✱ Daily  Balance Alert ',$dailyemailcontent,'content-type:text/html');
	 }	
    /**
     * Update Order buyer notify meta in checkout page
     * @param  integer $order_id
     * @return void
     */
    function buyer_notification_update_order_meta( $order_id ) {
        if ( ! empty( $_POST['buyer_sms_notify'] ) ) {
            update_post_meta( $order_id, '_buyer_sms_notify', sanitize_text_field( $_POST['buyer_sms_notify'] ) );
        }
    }

    public  function trigger_after_order_place( $order_id, $old_status, $new_status ) {	
		
		$order = new WC_Order( $order_id );
		
        if( !$order_id ) {
            return;
        }
        $admin_sms_data = $buyer_sms_data = array();

        $order_status_settings  = smsalert_get_option( 'order_status', 'smsalert_general', array() );
        $admin_phone_number     = smsalert_get_option( 'sms_admin_phone', 'smsalert_message', '' );
		
        if( count( $order_status_settings ) < 0 ) {
            return;
        }
		
        if( in_array( $new_status, $order_status_settings ) ) 
		{
			$default_buyer_sms 			=  defined('SmsAlertMessages::DEFAULT_BUYER_SMS_'.str_replace(" ","_",strtoupper($new_status))) ? constant('SmsAlertMessages::DEFAULT_BUYER_SMS_'.str_replace(" ","_",strtoupper($new_status))) : SmsAlertMessages::DEFAULT_BUYER_SMS_STATUS_CHANGED;
			
			$buyer_sms_body 			= smsalert_get_option( 'sms_body_'.$new_status, 'smsalert_message', $default_buyer_sms);
			$buyer_sms_data['number'] 	= get_post_meta( $order_id, '_billing_phone', true );
			$buyer_sms_data['sms_body'] = $this->pharse_sms_body( $buyer_sms_body, $new_status, $order, '');

			$buyer_response = SmsAlertcURLOTP::sendsms( $buyer_sms_data );
			
			
			$response=json_decode($buyer_response, true);
			
			if( $response['status']=='success' ) {
				$order->add_order_note( __('SMS Send to buyer Successfully.', 'smsalert' ) );
			} else {
				if(isset($response['description']) && is_array($response['description']) && array_key_exists('desc', $response['description']))
				{
					$order->add_order_note( __($response['description']['desc'], 'smsalert' ) );
				}
				else
				{
					$order->add_order_note( __($response['description'], 'smsalert' ) );
				}
			}
		}
		
		if(smsalert_get_option( 'admin_notification_'.$new_status, 'smsalert_general', 'on' ) == 'on' && $admin_phone_number!='')
		{	
			//send sms to post author
			if(strpos($admin_phone_number,'post_author') !== false)
			{
				$order_items 		= $order->get_items();
				$first_item 		= current($order_items);		
				$prod_id 			= $first_item['product_id'];
				$product 			= wc_get_product( $prod_id );
				$author_no = get_the_author_meta('billing_phone', get_post($prod_id)->post_author);
				$admin_phone_number = str_replace('post_author',$author_no,$admin_phone_number);
			}
			$default_admin_sms = defined('SmsAlertMessages::DEFAULT_ADMIN_SMS_'.str_replace(" ","_",strtoupper($new_status))) ? constant('SmsAlertMessages::DEFAULT_ADMIN_SMS_'.str_replace(" ","_",strtoupper($new_status))) : SmsAlertMessages::DEFAULT_ADMIN_SMS_STATUS_CHANGED;
			
			$admin_sms_body  			= smsalert_get_option( 'admin_sms_body_'.$new_status, 'smsalert_message', $default_admin_sms );	
			$admin_sms_data['number']   = $admin_phone_number;
			$admin_sms_data['sms_body'] = $this->pharse_sms_body( $admin_sms_body, $new_status, $order, '');
			$admin_response             = SmsAlertcURLOTP::sendsms( $admin_sms_data );
			$response=json_decode($admin_response,true);
			if( $response['status']=='success' ) {
				$order->add_order_note( __( 'SMS Sent Successfully.', 'smsalert' ) );
			} else {
				if(is_array($response['description']) && array_key_exists('desc', $response['description']))
				{
					$order->add_order_note( __($response['description']['desc'], 'smsalert' ) );
				}
				else {
					$order->add_order_note( __($response['description'], 'smsalert' ) );
				}
			}
		}
    }
	
	function update_wc_warranty_settings($data)
	{
		$options = $_POST;
		if($options['tab'] == 'smsalert_warranty')
		{
			foreach($options as $name => $value)
			{
			   if(is_array($value))
			   {
				   foreach($value as $k => $v)
				   {
					   if(!is_array($v))
					   {
							$value[$k] = stripcslashes($v);
					   }
				   }
			   }
				update_option( $name, $value );
		    }
		}
	}
	
	function send_rma_status_sms($request_id,$status)
	{
		$wc_warranty_checkbox=smsalert_get_option('warranty_status_'.$status, 'smsalert_warranty','');
		$is_sms_enabled = ($wc_warranty_checkbox=='on')  ? true : false;
		if($is_sms_enabled)
		{
			$sms_content	= smsalert_get_option('sms_text_'.$status, 'smsalert_warranty','');
			$order_id 		= get_post_meta( $request_id, '_order_id', true );
			$rma_id 		= get_post_meta( $request_id, '_code', true );
			$order 			= wc_get_order( $order_id );
			global $wpdb;
			$products 		= $items = $wpdb->get_results( $wpdb->prepare(
							"SELECT *
							FROM {$wpdb->prefix}wc_warranty_products
							WHERE request_id = %d",
							$request_id
						), ARRAY_A );
						
			$item_name = '';						
			foreach ( $products as $product ) {

				if ( empty( $product['product_id'] ) && empty( $item['product_name'] ) ) {
					continue;
				}

				if ( $product['product_id'] == 0 ) {
					$item_name .= $item['product_name'].', ';
				} else {
					$item_name .= warranty_get_product_title( $product['product_id'] ).', ';
				}
			}
			$item_name 					= rtrim($item_name, ', ');
			$sms_content 				= str_replace( '[item_name]', $item_name, $sms_content );
			$buyer_sms_data				= array();
			$buyer_sms_data['number']   = get_post_meta( $order_id, '_billing_phone', true );
			$buyer_sms_data['sms_body'] = $this->pharse_sms_body($sms_content, $status, $order, '', $rma_id);
			$buyer_response 			= SmsAlertcURLOTP::sendsms( $buyer_sms_data );
		}
	}
	
	function on_new_rma_request($warranty_id)
	{
		$this->send_rma_status_sms($warranty_id,"new");
	}
	
	function on_rma_status_update()
	{
		$request_id = $_POST['request_id'];
		$status 	= $_POST['status'];
		
		$this->send_rma_status_sms($request_id,$status);
	}
	
	function trigger_new_customer_note( $data ) {
		
		if(smsalert_get_option('buyer_notification_notes', 'smsalert_general')=="on")
		{
			$order_id					= $data['order_id'];
			$order						= new WC_Order( $order_id ); 
			$buyer_sms_body         	= smsalert_get_option( 'sms_body_new_note', 'smsalert_message', SmsAlertMessages::DEFAULT_BUYER_NOTE );
			$buyer_sms_data 			= array();
			$buyer_sms_data['number']   = get_post_meta( $data['order_id'], '_billing_phone', true );
			$buyer_sms_data['sms_body'] = $this->pharse_sms_body( $buyer_sms_body, $order->get_status(), $order, $data['customer_note']);
			$buyer_response 			= SmsAlertcURLOTP::sendsms( $buyer_sms_data );
			$response					= json_decode($buyer_response,true);
			
			if( $response['status']	== 'success' ) {
				$order->add_order_note( __( 'Order note SMS Sent to buyer', 'smsalert' ) );
			} else {
				$order->add_order_note( __($response['description']['desc'], 'smsalert' ) );
			}
		}
	}
	
    public function pharse_sms_body( $content, $order_status, $order, $order_note, $rma_id = '' ) {
		
		$order_id			= is_callable( array( $order, 'get_id' ) ) ? $order->get_id() : $order->id;
		$order_variables	= get_post_custom($order_id); 
		$order_items 		= $order->get_items();
		$item_name			= implode(", ",array_map(function($o){return $o['name'];},$order_items));
		$item_name_with_qty	= implode(", ",array_map(function($o){return sprintf("%s [%u]", $o['name'], $o['qty']);},$order_items));
		$store_name 		= get_bloginfo();
		$tracking_number 	= '';
		$tracking_provider 	= '';
		$tracking_link 		= '';
		$aftrShp_tracking_number 	= '';
		$aftrShp_tracking_provider_name 	= '';
		
		if(
			(strpos($content, '[tracking_number]') 		!== false) || 
			(strpos($content, '[tracking_provider]') 	!== false) || 
			(strpos($content, '[tracking_link]') 		!== false)
		)//fetch from database only if tracking plugin is installed
		{			
			if(is_plugin_active( 'woocommerce-shipment-tracking/woocommerce-shipment-tracking.php')) 
			{
				$tracking_info = get_post_meta( $order_id, '_wc_shipment_tracking_items', true );
				if(sizeof($tracking_info) > 0)
				{
					$t_info = array_shift($tracking_info);
					$tracking_number 	= $t_info['tracking_number'];
					$tracking_provider 	= ($t_info['tracking_provider'] != '') ? $t_info['tracking_provider'] : $t_info['custom_tracking_provider'];
					$tracking_link 		= $t_info['custom_tracking_link'];
				}
			}
			elseif( is_plugin_active( 'woo-advanced-shipment-tracking/woocommerce-advanced-shipment-tracking.php' ))
			{
				$ast = new WC_Advanced_Shipment_Tracking_Actions;
				$tracking_items = $ast->get_tracking_items( $order_id,true );
				if (count($tracking_items)>0)
				{
					$t_info = array_shift($tracking_items);
					$tracking_number = $t_info['tracking_number'];
					$tracking_provider = $t_info['formatted_tracking_provider'];
					$tracking_link = $t_info['formatted_tracking_link'];
				}
			}
		}
		
		if(
			(strpos($content, '[aftership_tracking_number]') 		!== false) || 
			(strpos($content, '[aftership_tracking_provider_name]') 	!== false) 
		)//fetch from database only if tracking plugin is installed
		{	if(is_plugin_active( 'aftership-woocommerce-tracking/aftership.php')) 
			{		
				$aftrShp_tracking_number = get_post_meta( $order_id, '_aftership_tracking_number', true );
				$aftrShp_tracking_provider_name = get_post_meta( $order_id, '_aftership_tracking_provider_name', true );
			}
		}
		
		$find = array(
            '[order_id]',
            '[order_status]',
            '[rma_status]',
            '[first_name]',
            '[item_name]',
            '[item_name_qty]',
            '[order_amount]',
            '[note]',
            '[rma_number]',
            '[store_name]',
            '[tracking_number]',
            '[tracking_provider]',
            '[tracking_link]',
			'[aftership_tracking_number]',
            '[aftership_tracking_provider_name]',
            '[pdf_invoice_link]',
        );
        $replace = array(
            $order->get_order_number(),
            $order_status,
            $order_status,
            '[billing_first_name]',
            $item_name,
			$item_name_with_qty,
			$order->get_total(),
			$order_note,
			$rma_id,
			$store_name,
			$tracking_number,
			$tracking_provider,
			$tracking_link,
			$aftrShp_tracking_number,
			$aftrShp_tracking_provider_name,
			admin_url( "admin-ajax.php?action=generate_wpo_wcpdf&document_type=invoice&order_ids=" . $order_id."&order_key=".$order->get_order_key() )
        );
		
        $content = str_replace( $find, $replace, $content );
		foreach ($order_variables as &$value) {
			$value = $value[0];
		}
		unset($value);
		
		$order_variables = array_combine(
			array_map(function($key){ return '['.ltrim($key, '_').']'; }, array_keys($order_variables)),
			$order_variables
		);
        $content = str_replace( array_keys($order_variables), array_values($order_variables), $content );
        return $content;
    }
	
	/*
	* Function for sms alert integration with contact form 7.
	*/
	
	public function new_menu_sms_alert ($panels) {
		$panels['sms-alert-sms-panel'] = array(
				'title' => __('SMS Alert'),
				'callback' => array($this, 'add_panel_sms_alert')
		);
		return $panels;
	}
	
	public function add_panel_sms_alert($form) { 
		if ( wpcf7_admin_has_edit_cap() ) {
		  $options = get_option( 'smsalert_sms_c7_' . (method_exists($form, 'id') ? $form->id() : $form->id) );
		  if( empty( $options ) || !is_array( $options ) ) {
			$options 		= array( 'phoneno' => '', 'text' => '', 'visitorNumber' => '','visitorMessage' => '');
		  }
		  $options['form'] 	= $form;
          $data 			= $options; 
		  include(plugin_dir_path( __DIR__ ).'sms-alert/template/cf7-template.php'); 
		}
	}
	
  public function save_form( $form ) {
    update_option( 'smsalert_sms_c7_' . (method_exists($form, 'id') ? $form->id() : $form->id), $_POST['wpcf7smsalert-settings'] );
  } 
  
  public function get_cf7_tagS_To_String($value,$form){
		if(function_exists('wpcf7_mail_replace_tags')) {
			$return = wpcf7_mail_replace_tags($value); 
		} elseif(method_exists($form, 'replace_mail_tags')) {
			$return = $form->replace_mail_tags($value); 
		} else {
			return;
		}
		return $return;
	}
	
	public	function send_custom_sms($data) 
	{
		$order 							= new WC_Order($_POST['order_id']);
		$sms_body 						= $_POST['sms_body'];
		$buyer_sms_data 				= array();
		$buyer_sms_data['number']   	= get_post_meta( $_POST['order_id'], '_billing_phone', true );
		$buyer_sms_data['sms_body'] 	= $this->pharse_sms_body($sms_body, $order->get_status(), $order, '');
		$buyer_response 				= SmsAlertcURLOTP::sendsms( $buyer_sms_data );
		echo $buyer_response;
		exit();
	}
	
	/**gravity form submission frontend*/
	public function do_gForm_processing( $entry, $form ) 
	{	 
		$meta = RGFormsModel::get_form_meta( $entry['form_id'] );
	    $feeds = GFAPI::get_feeds(null,$entry['form_id'],'gravity-forms-sms-alert');
		$message = $cstmer_nos = $admin_nos = $admin_msg ='';
		foreach($feeds as $feed)
		{
			if(sizeof($feed)>0 && array_key_exists('meta',$feed))
			{
				$admin_msg = $feed['meta']['smsalert_gForm_admin_text'];	
				$admin_nos = $feed['meta']['smsalert_gForm_admin_nos'];
				$cstmer_nos_pattern = $feed['meta']['smsalert_gForm_cstmer_nos'];				
				$message =$feed['meta']['smsalert_gForm_cstmer_text'];	
			}
		}
		
		foreach($meta['fields'] as $meta_field)
		{
			if(is_object($meta_field)) 
			{
				$field_id = $meta_field->id;
				if(isset($entry[$field_id]))
				{
					$label = $meta_field->label;
					$search = '{'.$label.':'.$field_id.'}';
					$replace=$entry[$field_id];
					$message = str_replace($search,$replace,$message);
					$admin_msg = str_replace($search,$replace,$admin_msg);
					
					if($cstmer_nos_pattern==$search)
					{
						$cstmer_nos=$replace;
					}
				}				
			}
		}				
		if($cstmer_nos!='' && $message!='')
		{
			$buyer_sms_data['number']   = $cstmer_nos;
			$buyer_sms_data['sms_body'] = $message;
			$buyer_response = SmsAlertcURLOTP::sendsms( $buyer_sms_data );
		}
		if($admin_nos!='' && $admin_msg!='')
		{
			$admin_sms_data['number']   = $admin_nos;
			$admin_sms_data['sms_body'] = $admin_msg;
			$admin_response = SmsAlertcURLOTP::sendsms( $admin_sms_data );
		}
	}
	
	/**gravity form submission frontend ends*/
	
} // SMSAlert_WC_Order_SMS

/**
 * Loaded after all plugin initialize
 */
add_action( 'plugins_loaded', 'load_SMSAlert_wc_order_sms' );

function load_SMSAlert_wc_order_sms() {
    $smsalert = SMSAlert_WC_Order_SMS::init();
}

register_activation_hook( __FILE__, 	array('smsalert_WC_Order_SMS', 'run_on_activate'));
register_deactivation_hook( __FILE__, 	array('smsalert_WC_Order_SMS', 'run_on_deactivate'));
?>