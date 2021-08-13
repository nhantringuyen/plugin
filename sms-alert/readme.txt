=== SMS Alert Order Notifications - WooCommerce ===

Contributors: cozyvision1
Tags: order notification, order SMS, SMS order, notification, order notification, seller notification, sms, SMSAlert, sms alert, sms india, transactional sms, woocommerce sms, woocommerce order sms, woocommerce order notification, woocommerce sms, sms integration, sms plugin, SMSAlert – WooCommerce, sms notification, two-step-verification, otp, mobile verification, verification, mobile, phone, sms, one time, password, sms verification
Requires at least: 3.5
Tested up to: 5.2.4
Stable tag: trunk
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin for sending SMS notification after placing orders using WooCommerce

== Description ==

This is a WooCommerce add-on. By Using this plugin admin and buyer can get notification about their order via sms using SMS Alert.

The WooCommerce Order SMS Notification plugin for WordPress is very useful, when you want to get notified via SMS after placing an order. Buyer and seller both can get SMS notification after an order is placed. SMS notification options can be customized in the admin panel very easily.

https://youtu.be/nSoXZBWEG5k

= SMSAlert - WooCommerce (Key Features) =

> + OTP for order confirmation(with option to enable OTP only for COD orders)
> + OTP verification for registration
> + Login with OTP
> + Reset password with OTP
> + OTP verification for login(option to enable OTP only for selected roles)
> + SMS to Customer and Admin on new user registration/signup
> + Admin/Post Author can get Order SMS notifications
> + Buyer can get order sms notifications supports custom template
> + Sending Order Details ( order no, order status, order items and order amount ) in SMS text
> + Different SMS template corresponding to different Order Status
> + Directly contact with buyer via SMS through order notes, and custom sms available on order detail page
> + All order status supported(Pending, On Hold, Completed, Cancelled)
> + Block multiple user registration with same mobile number
> + Supports wordpress Multisite
> + Custom Low Balance Alert
> + Option to disable sending OTP to a particular after n resends
> + Daily SMS Balance on Email
> + Sync Customers to Group on [www.smsalert.co.in](https://www.smsalert.co.in)
> + Auto Shorten URL

= Compatibility =

👉 [Sequential Order Numbers Pro](https://woocommerce.com/products/sequential-order-numbers-pro/)
👉 [WooCommerce Order Status Manager](https://woocommerce.com/products/woocommerce-order-status-manager/)
👉 [Admin Custom Order Fields](https://woocommerce.com/products/admin-custom-order-fields/)
👉 [Shipment Tracking](https://woocommerce.com/products/shipment-tracking/)
👉 [Advanced Shipment Tracking for WooCommerce](https://wordpress.org/plugins/woo-advanced-shipment-tracking/)
👉 [Aftership - WooCommerce Tracking](https://wordpress.org/plugins/aftership-woocommerce-tracking/)
👉 [Ultimate Member](https://wordpress.org/plugins/ultimate-member/)
👉 [Pie Register](https://wordpress.org/plugins/pie-register/)
👉 [WP-Members Membership Plugin](https://wordpress.org/plugins/wp-members/)
👉 [Dokan Multivendor Marketplace](https://wordpress.org/plugins/dokan-lite/)
👉 [WC Marketplace](https://wordpress.org/plugins/dc-woocommerce-multi-vendor/)
👉 [WooCommerce PDF Invoices & Packing Slips](https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/) to send invoice on SMS
👉 [Claim GST for Woocommerce](https://wordpress.org/plugins/claim-gst/) for Input tax credit

= Integrations =

👨 [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) to send notification to customer and admins, and verify mobile number through OTP
👨 [Gravity Forms](https://www.gravityforms.com/) to send notification to customer and admins
👨 [Returns and Warranty Requests](https://woocommerce.com/products/warranty-requests/) to send RMA status update to customer
👨 [Easy Digital Downloads](https://wordpress.org/plugins/easy-digital-downloads/) to send notification to customer
👨 [Affiliates Manager](https://wordpress.org/plugins/affiliates-manager/) to send notification to Affiliates and admin
👨 [WooCommerce Bookings](https://woocommerce.com/products/woocommerce-bookings/) to send booking confirmation to customers and admin
👨 [LearnPress – WordPress LMS Plugin](https://wordpress.org/plugins/learnpress/) to send notifications to student and admin

== Installation ==

= New Install via FTP =

1. Unzip the package SMS Alert.zip on you computer.
2. Upload the unzipped folder the /wp-content/plugins/ directory.
3. Activate the plugin through the Plugins menu in WordPress.
4. Goto Woocommerce settings and configure your SMS Alert.

= New Install via the WordPress uploader =

1. Click Plugins > Add New inside of your WordPress install.
2. Click Upload and select the package SMS Alert.zip
3. Activate the plugin through the Plugins menu in WordPress.
4. Goto Woocommerce settings and configure your SMS Alert.

= Updating via the FTP =

1. If you've made any custom changes to the core you'll need to merge those changes into the new package.
2. Unzip the package SMS Alert.zip on you computer.
3. Replace the current folder in your plugins folder with the new unzipped folder from your computer.

== Frequently Asked Questions ==

= Can i integrate my own sms gateway? =

There is no provision to integrate any other SMS Gateway, we only support [SMS Alert](http://www.smsalert.co.in/) SMS Gateway.

= How do i change Sender id? =

You can request the sender id after login to your [SMS Alert](http://www.smsalert.co.in/) account, from manage sender id.

Sender id is only available for transactional account.

= I signed up for a demo account, but not received any test sms =

As per TRAI Guidelines promotional sms can be sent only from 9 am to 9 pm, please test during this period only, also check if your number is not registered in NDNC registry.

If still you face any issues, please [contact](https://wordpress.org/support/plugin/sms-alert) our support team.

= I am unable to login to my wordpress admin =

This can happen in two cases like you do not have sms credits in your sms alert account, or your admin profile has some other number registered, for both cases you can rename the plugin directory in your wordpress plugin directory via FTP, to disable the plugin

= Which all countries do you support sms? =

We support sms to below countries:

* Argentina
* Australia
* Canada
* France
* Germany
* Greece
* India
* Italy
* Nigeria
* Netherlands
* Saudi Arabia
* Singapore
* Spain
* Ukraine
* United Kingdom
* USA

= Can i send sms to multiple countries from one smsalert account? =

Yes, you can send sms to multiple countries, by default your account is configured to send SMS to only one country, you can request to allow additional countries for your account through email on support@cozyvision.com.

= How can i use my custom variables in sms templates? =

The plugin supports custom order post meta, if your post meta key is '_my_custom_key', then you can access it in sms templates as [my_custom_key]

= Can i extend the functionality of this plugin? =

You can use our hook to send sms from any plugin, please refer to the below example to send sms.

do_action('sa_send_sms','918010551055','This is a demo sms.');

= Can you customise the plugin for me? =

Please use wordpress [support forum](https://wordpress.org/support/plugin/sms-alert) for new feature request, our development team may consider it in future updates. Please note we do not have any plans to develop any integrations for any paid plugins, if still you need it someone like you must sponser the update :-)

== Screenshots ==

1. OTP popup - Login, Registration, Checkout, Contact Form 7.
2. Login with OTP.
3. General Settings - Login with your www.smsalert.co.in username and password.
4. Customer Templates - Set sms templates for every order status, these will be sent to the customers.
5. Admin SMS Templates - Set sms templates that admin will receive, set admin mobile number from advanced settings.
6. Advanced Settings - Enable or disable daily balance alert, low balance alert, admin mobile number, and many other advanced options.
7. Custom SMS on Order detail page - You can send custom personalised sms to the customer directly from order detail page from your admin panel, this is very useful in case you wih to update customer in case of any unplanned event, like delay in delivery, order disputes and claims, etc.
8. Returns and Warranty Requests - Send SMS to customer and admin when a new warranty request is placed, or warranty request status changes.
9. Gravity Forms - Send sms to customer and admin, whenever the form is submitted.
10. Contact Form 7 - Visitor & Admin Message, SMS OTP Verification.
11. Easy Digital Downloads - Notification to Customer and Admin on various order status's.
12. Woocommerce Bookings - Customer Templates
13. Woocommerce Bookings - Admin Templates

== Changelog ==

= 1.0 =
* Initial version released

= 2.2 =
* fixed bug: admin number not saving
* can have different sms content at different order status

= 2.7 =
* plugin moved to woocommerce settings tab
* dashboard widget for sms credits
* fixed bug: & symbol not working in sms
* Block multiple user registration with same mobile number

= 2.7.1 =
* order cancelled status sms option added
* compatibility with custom order numbers

= 2.7.2 =
* removed php depricated function call
* contact form 7 support

= 2.8.0 =
* OTP for user / admin login
* SMS Template selection for custom sms

= 2.8.1 =
* bug fix - unable to enter otp as page keeps on loading

= 2.8.2 =
* added feature to send sms to post author

= 2.8.3 =
* minor bug fixes and added OTP length support

= 2.8.4 =
* php warning fix when wordpress is in debug mode

= 2.8.5 =
* added ability to verify order on specific payment option
* added ability to verify order only for guest checkout

= 2.8.6 =
* bug fix can not verify sms alert account

= 2.8.7 =
* bug fix error with razorpay and paytm gateway

= 2.8.8 =
* bug fix, woocommerce checkout displaying two buttons to place order and verify
* compatibility fix with Woocommerce smart COD and Woocommerce COD advanced

= 2.9.1 =
* resend OTP will be enabled after 15 sec
* design changes for admin

= 2.9.2 =
* Support for Custom Order Status
* Sync Customers to group on www.smsalert.co.in
* Low balance and daily balance alert on email
* PHP 7 compatibility issue fix on selected payment gateway

= 2.9.3 =
* notification to admin for template mismatch error

= 2.9.4 =
* compatibility fix for woocommerce advaned COD(moved plugin settings to seperate page)
* added option to customise OTP resend timer and checkout button text
* added support for woocommerce shipment tracking plugin
* code cleaning

= 2.9.5 =
* Added version check support for woocommerce
* added country based mobile number validation(first step towards international sms support)
* added support for dynamic order variables
* custom sms now supports variables
* order id now does not prepend order#
* php error fix

= 2.9.7 =
* Integration with Easy Digital Downloads
* mobile number now accepts with or without 0 or country code
* Login with OTP

= 2.9.8 =
* Integration with Gravity Forms
* Added new variable for woocommerce [item_name_qty] for item quantity with product name

= 2.9.9 =
* added custom regex validator for each country
* login issue fix for older wordpress versions
* added multi lingual support

= 2.9.10 =
* compatibility fix for woocommerce latest release

= 2.9.11 =
* bug fix: sms not going to admin when multiple numbers are added
* Integration with ultimate membership plugin

= 2.9.12 =
* bug fix: OTP popup responsive for mobile devices
* bug fix: resend OTP: validate option was being shown even in case of error in sending OTP
* bug fix: woocommerce shipping tracking plugin was not getting detected
* bug fix: two billing phone number on same page
* bug fix: checkout option was not shown when OTP for selected gateway was checked but no gateway was selected
* bug fix: Call to undefined function sync_customers() in error logs

= 2.9.13 =
* bug fix: undefined variable default_message
* bug fix: Undefined index: ENTER_PHONE_CODE  
* bug fix: disable low balance alert
* bug fix: OTP popup was not visible for some themes
* bug fix: input type text not found in checkout page when my account page title was changed
* compatibility updated for wordpress v-4.9.6
* compatibility updated for woocommerce v-3.4.0

= 2.9.14 =
* added support for ultimate member signup support
* added support for WP Members
* added OTP support for Contact form 7
* minor bug fixes

= 2.9.15 =
* added support for Pie Register

= 2.9.16 =
* Bug Fix: OTP Timer issue - Internet Explorer
* Bug Fix: Dokan Vendor Registration OTP issue
* Added Support for Affiliates Manager
* Added hook support to extend this plugin's functionality and usage

= 2.9.17 =
* Bug Fix: order status variable not working in custom sms and order note
* Bug Fix: PHP warning messages fixed
* added javascript validation for Woocommerce checkout
* added support for WC Marketplace

= 2.9.18 =
* Bug Fix: Checkout validate OTP not working for stores selling in specific country
* Bug Fix: PHP warning messages fixed
* Bug Fix: Added option to disable checkout form validation before sending OTP

= 2.9.19 =
* Added support for WooCommerce Bookings plugin
* code cleaning and optimisation


= 2.9.20 =
* compatibility updated for wordpress v-5.0
* compatibility updated for woocommerce v-3.5.1

= 2.9.21 =
* bug fixes for ultimate member integration
* added option to set custom templates for Affiliates Manager
* bug fixes for Affiliates Manager
* Removed curl for better wordpress compatibility

= 2.9.22 =
* added option to send SMS after registration
* bug fixes for javascript
* compatability check with latest wordpress and woocommerce releases

= 2.9.23 =
* checkbox was getting untick in settings

= 2.9.24 =
* remove leading 0 for countries where phone pattern is not handled
* bugfix sync customers to group
* bugfix login with mobile number and password

= 2.9.25 =
* better OTP session handling for older PHP versions
* bugfix post registration user was redirected to My account page, ignoring other plugins
* better handling of country wise phone number pattern
* bugfix copy paste OTP not working on mobile device

= 2.9.26 =
* bugfix WC Marketplace file type on registration page support
* bypass OTP screen on login if user has already logged in with OTP
* option to enable OTP, only for selected role
* Integration with Aftership
* Reset password with OTP
* Option to disable sending OTP to a particular after n retries
* bugfix OTP was resent on checkout if the popup was closed and reopened, ignoring timer
* bugfix OTP field copy paste not working
* code clean up

= 2.9.27 =
* Compatibility fix for older wordpress versions

= 2.9.28 =
* Integration with Woocommerce pdf invoices & packaging slip
* Added option to auto shorten URL
* Compatibility fix for Ultimate member
* Bugfix: Ask user phone number if there is no phone number updated in his account

= 2.9.29 =
* code cleanup
* make password field blank when user clicks on login with OTP
* Bugfix: Duplicate mobile number error not visible
* Integration with Claim GST Plugin

= 3.0.0 =
* code cleanup
* Integration with Learnpress

= 3.1.0 =
* Integration with Advanced Shipment Tracking for WooCommerce
* Support for stores selling globally
* Bugfix: double sms going for WooCommerce Bookings

= 3.2.0 =
* Simplified Contact form 7 integration

= 3.2.1 =
* Compatibility Fix: OTP timer not working on IE 11
* Bugfix: Woocommerce Booking, sms not sent when user is not registered

== Support ==

Since this plugin is dependent on www.smsalert.co.in, we provide 24X7 email support for this plugin via support@cozyvision.com. For new feature requests please use wordpress [support forum](https://wordpress.org/support/plugin/sms-alert).

== Translations ==

* English - default
* Hindi