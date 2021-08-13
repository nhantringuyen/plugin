=== E2Pdf - Export To Pdf Tool for WordPress ===
Contributors: rasmarcus, oleksandrz
Donate link: https://e2pdf.com/
Tags: e2pdf, pro2pdf, pdf, create, edit, export, save, generation, pdftk, formidable, caldera, divi, forminator, forms, pdf viewer, create pdf, export pdf, save pdf, formidable pdf, caldera pdf, divi pdf, forminator pdf, forms pdf, wordpress pdf, pdf editor, export to pdf, export data, gravity, gravity pdf
Requires at least: 4.0
Tested up to: 5.3
Requires PHP: 5.3
Stable tag: 1.09.06
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create PDFs, then map web form data and display a link on a post/page/form or preview the merged PDF on a PC or mobile device with a simple shortcode.

== Description ==

= E2Pdf is the next generation PDF tool for Wordpress. =

This plugin includes:

* a PDF Document Viewer - Allow visitors to view static or dynamic PDF documents in Wordpress.
* a PDF Document Editor - Create/Edit new and existing PDF documents without leaving Wordpress.
* a PDF Forms Editor - Create/Edit new, existing, and auto-generated PDF Forms from the Dashboard.
* a PDF Data Injector - Merge data from Wordpress pages, posts, or web forms into PDF forms.
* a Generous Affiliate Program - 90-day cookies. 20% commission paid lifetime for all new payments.

= Learn all about E2Pdf =

* [FAQ](https://e2pdf.com/support/faq)
* [Help Desk](https://e2pdf.com/support/desk)
* [Documenation](https://e2pdf.com/support/docs)
* [YouTube](https://www.youtube.com/e2pdf)

= PDF DOCUMENT VIEWER: [e2pdf-view] =

* Allows users to view and print PDF documents without leaving your site.
* Preview dynamically created PDF documents prior to downloading, emailing, or purchasing.

= PDF DOCUMENT EDITOR: Built-in =

* Create a PDF from a blank document.
* Upload and edit existing PDF documents.
* Add/Edit text and images.
* Auto-generate PDF documents based on a Wordpress page or post.

= PDF FORMS EDITOR: Built-in =

* Create PDF forms from a blank document.
* Upload and edit existing PDF documents or forms, no need for third-party software.
* Auto-generate PDF forms based on a Wordpress page, post, or web form.
* Use actions and conditions to create dynamic PDF documents.

= PDF DATA INJECTOR: Remotely Generated¹ =

* Map Wordpress pages or post to PDF fields.
* Map web forms to PDF form fields.
* Map signature² fields to PDF form fields.
* Map images² to PDF form fields.

= EMAIL PDF OPTIONS =

* Send as email attachment.
* Send a link in email body to download PDF documents and forms. 

= SAVE DYNAMIC PDF TO SERVER =

* Save form filled PDF documents to static or dynamic folders on your server.

== EXTENSIONS: 3rd Party Integrations ==

* [Wordpress](https://e2pdf.com/extensions/wordpress)
* [Forminator](https://e2pdf.com/extensions/forminator)
* [Formidable Forms](https://e2pdf.com/extensions/formidable)
* [Gravity Forms](https://e2pdf.com/extensions/gravity)
* [Caldera Forms](https://e2pdf.com/extensions/caldera)
* [Divi Contact Forms](https://e2pdf.com/extensions/divi)

== APIs: 3rd Party Integrations ==

* Adobe Sign REST API

= IN DEVELOPMENT³ =

* WooCommerce
* Contact Form 7
* Ninja Forms

== Terms of Service ==

By continuing to use our plugin you are agreeing to our [Terms of Service](https://e2pdf.com/terms-of-service).

== Additional Information, Definition and Explaination ==

¹ Remotely Generated: Due to the complex nature of the PDF file format, dynamic PDF documents are generating remotely with the E2Pdf API at E2Pdf.com. 
PRIVACY POLICY: We do not collect or store any web form submitted user private data that is sent to the API.

² Selected extension must include the signature field or image field.

³ In Development: Extensions to be added to E2Pdf. [Click here](https://e2pdf.com/support/desk) to request an integration, or the status of an integration.

== HISTORY ==

E2Pdf is the new and highly improved iteration of the [Formidable PRO2PDF plugin](https://wordpress.org/plugins/formidablepro-2-pdf/). Originally designed and coded in 2013 out of a need to print dynamic PDF documents from WordPress forms, PRO2PDF provided the automation necessary for a small insurance broker to produce far more business with the same number of employees.

Today, the E2Pdf plugin and Wordpress extension provide the entire WordPress community with a cost free method of creating dynamic PDF documents – without programming or coding – with one simple shortcode. More information can be found at [E2Pdf.com](https://e2pdf.com)

[youtube https://www.youtube.com/watch?v=BFu78n9-tcM]

== Installation ==

1. Go to your "Plugins" -> "Add New" page in your WordPress admin dashboard
2. Search for "E2Pdf"
3. Click the "Install Now" button
4. Activate the plugin through the "Plugins" menu
5. Create a new Template, activate and use one of the shortcodes available to add PDF to needed page/form and you're done!

== Frequently Asked Questions ==
= Support for Multisite installation =

Yes, plugin supports Network Activation.

= How to add new Fonts? =

To upload new fonts go to E2Pdf -> Settings -> Fonts. After successfull upload font will appear in the "Font Dropdown" in templates.

= Font inside fields for "Uploaded" PDF different =

At this moment, fields are fully recreated and controlled by E2Pdf. To use the original font, you must upload the font to E2Pdf and assign it to the PDF form field(s), or any other created objects from the PDF Builder.

= Plugin shows "License Key Not Valid" with FREE version =

Try to "Deactivate" and "Activate" plugin again.

== Screenshots ==

1. Export data to PDF from Admin Panel.
2. Templates list Page.
3. Creating new PDF Template.
4. Editing PDF Template.
5. PDF Template Object properties.
6. Settings Page.

== Changelog ==
= 1.09.06 =
*Release Date - 06 December 2019*

* Fix: RTL Support
* Add: Templates Bulk Actions: "Activation" and "Deactivation"

= 1.09.05 =
*Release Date - 21 November 2019*

* Fix: Hidden pages missed on "Preview" and "View" Template action
* Fix: "Divi Contact Forms" auto download
* Fix: WordPress 5.3 Styles
* Add: "Global Actions"
* Add: "single_page_mode", "hide", "background", "border" attributes for [e2pdf-view]
* Add: %%_wp_http_referer%%, %%e2pdf_entry_id%% shortcodes support for "Divi"
* Add: Database Debug information
* Add: id="current" for [e2pdf-user] shortcode
* Improvement: Extended "Debug" information
* Improvement: pdf.js update (v2.3.200)

= 1.09.04 =
*Release Date - 22 October 2019*

* Add: "responsive", "viewer" attributes for [e2pdf-view]
* Add: Support of shortcodes which contains [id] for "Formidable Forms"
* Fix: Missed fields for "Gravity Forms" inside "Visual Mapper"
* Fix: "Hide" action for pages fired incorrectly in some cases
* Fix: Incorrect render of values in some cases

= 1.09.03 =
*Release Date - 27 September 2019*

* Add: Option to disable WYSIWYG Editor for HTML Object
* Add: Option to disable Local Images Load
* Add: Option to change Images Load Timeout
* Add: "justify" option for HTML Object
* Add: Caldera Forms Conditional Fields support
* Add: "Visual Mapper" show hidden fields option
* Fix: "Visual Mapper" fails for "Caldera Forms" in some cases
* Improvement: Templates load
* Improvement: Translation
* Improvement: Errors and Message Notifications

= 1.09.02 =
*Release Date - 27 August 2019*

* Add: Template Revisions
* Fix: UI minor bug fixes

= 1.09.01 =
*Release Date - 17 August 2019*

* Fix: Compatibility with Divi 3.27
* Fix: Incorrect render of values in some cases
* Fix: Fields data were not saving in some cases
* Improvement: Optimization

= 1.08.09 =
*Release Date - 07 August 2019*

* Add: "Nl2br" option for "e2pdf-html"
* Add: Unlink paid License Key option
* Add: "attachment_image_url" attribute for [e2pdf-meta] shortcode support
* Add: "Events Manager" render shortcodes support
* Add: "Divi Builder" support
* Add: Extended 3rd party plugins support for WordPress extension
* Add: "Visual Mapper" meta keys support for WordPress extension
* Add: "nl2br" filter for [e2pdf-format-output] shortcode
* Add: Permission settings
* Add: Dynamic shortcode support for "WordPress" extension inside Widgets
* Add: "Hide (If Empty)" and "Hide Page (if Empty)" properties for HTML object
* Add: "Preg Replace" option for fields and objects
* Add: "Replace Value" and "Auto-Close" options for "Visual Mapper"
* Fix: "Visual Mapper" styles for Forminator
* Fix: Conflict with Elementor
* Fix: Conflict with SiteOrigin
* Fix: "Caldera Forms" incorrect support for checkbox with "Show Values" option
* Fix: "preg_replace" error
* Improvement: "Auto PDF"
* Improvement: UI
* Improvement: Optimization

= 1.07.11 =
*Release Date - 24 June 2019*

* Add: Serialized post meta fields support
* Add: "attachment_url", "path" attributes for WordPress [meta key="x"] shortcode
* Add: Post terms support
* Add: [terms key="x"] shortcode for WordPress posts/pages support
* Add: "pdf" attribute for [e2pdf-download] shortcode
* Add: "Popup Maker" support
* Add: "overwrite" attribute for [e2pdf-save] shortcode
* Add: "Entries" cache support
* Add: Cache support
* Add: "Auto Form" Gravity Forms support
* Add: "Close" button while creating new template
* Add: Gravity Forms support
* Add: "attachment" attribute for [e2pdf-save] shortcode
* Add: [e2pdf-arg] shortcode suppport
* Add: [post_thumbnail] shortcode for WordPress extension
* Fix: "Gravity Forms" does not render values inside mail notification in some cases
* Fix: Missing "Actions" and "Conditions" for pages while re-upload PDF
* Fix: "Visual Mapper" fails for "Caldera Forms" in some cases
* Fix: "Visual Mapper" not rendered correctly for "Gravity Forms" in some cases
* Fix: "Auto Form" Caldera Forms dropdown
* Fix: PHP warnings on signature field in some cases
* Fix: "Auto PDF" radio group names
* Fix: Some shortcodes not fired correctly
* Fix: Backward compatibility with WordPress 4.0
* Fix: "Auto PDF" option visible on extension change
* Fix: New pages do not respect global E2Pdf Template size option
* Fix: Incorrect file name while download in some cases
* Improvement: Better support for 3rd party WordPress plugins
* Improvement: Optimization
* Improvement: UI
* Improvement: Translation
* Improvement: Filters

= 1.06.02 =
*Release Date - 10 April 2019*

* Add: Custom post types support
* Add: [e2pdf-user] shortcode support
* Add: [e2pdf-wp] shortcode support
* Add: [e2pdf-content] shortcode support
* Add: Custom field names
* Add: "Auto Form from PDF" additional options
* Add: "Meta" Title, Subject, Author and Keywords PDF options
* Add: "e2pdf_extension_render_shortcodes_tags" filter
* Add: [e2pdf-view] shortcode additional attributes: page, zoom, nameddest, pagemode
* Fix: "Error" show if failed while creating "Template"
* Fix: WordPress Pages/Posts not showing all items
* Fix: Notice on "e2pdf-image" and "e2pdf-signature" render
* Fix: [e2pdf-exclude] not process shortcodes inside
* Fix: [e2pdf-download] incorrect button title in some cases
* Fix: "Attachments" missing in some cases due incorrect "PDF Name"
* Fix: "Auto Form from PDF" pre-built template radio/checkbox empty
* Fix: "Import" item replace shortcodes inside "Email" body and "Success Messages"
* Improvement: WordPress extension
* Improvement: pdf.js update (v2.0.943)
* Improvement: "Deactivate" template while moving to "Trash"
* Improvement: Translation

= 1.05.03 =
*Release Date - 23 February 2019*

* Add: "Auto Form" from pre-built E2Pdf Template
* Add: "Formidable Forms" Item import options
* Add: "Pagination" and "Screen" options for Templates list
* Fix: "Forminator" incorrect field IDs while "Auto Form"
* Fix: "Replace PDF" failed in some cases (Chrome 72.0.3626.109)
* Fix: "Replace PDF" css
* Fix: Caldera Forms "Auto Form" empty field values
* Fix: Backup failed in some cases
* Fix: "E2Pdf" css style affected other pages
* Improvement: Templates activation/deactivation action
* Improvement: Optimization
* Improvement: Translation

= 1.04.07 =
*Release Date - 11 February 2019*

* Add: Possibility to disable extensions
* Add: "Forminator" "Disable store submissions in my database" support
* Add: "Caldera Forms" Connected Forms support
* Add: "Download" and "View" links generation based on saved PDFs
* Add: Auto Form from PDF for "Formidable Forms", "Caldera Forms", "Forminator"
* Add: "highlight" property for e2pdf-link
* Add: "search", "replace", "ireplace" attributes for [e2pdf-format-output] shortcode
* Add: Changing "Page ID" for elements with "Actions" and "Conditions"
* Fix: "PHP" Warning on empty "LIKE" "NOT_LIKE" condition value
* Fix: "Forminator" textarea field type "Auto PDF"
* Fix: "Visual Mapper" for "Forminator PRO" 1.6.1
* Fix: "Formidable Forms" Signature field (2.0.1) compatibility
* Fix: Image render failed in some cases
* Fix: "Visual Mapper" not rendered correctly for "Forminator" in some cases
* Fix: "Incorrect" element position with "Auto PDF" and "Free" License Type in some cases
* Fix: "Filename" for [e2pdf-view] shortcode
* Fix: "Pages" and "Elements" possible overload issue
* Fix: "e2pdf-signature" failed on load in some cases
* Improvement: Translation
* Improvement: Templates list load time

= 1.03.07 =
*Release Date - 24 December 2018*

* Add: Additional checks for "Visual Mapper"
* Add: Formidable Forms "Repeatable" fields support for e2pdf-" shortcodes
* Add: Display element "Type" inside properties window
* Fix: "Formidable Forms" Visual Mapper error with address field in some cases
* Fix: Z-index issue in some cases
* Fix: "Mozilla Firefox" PDF re-upload new tab
* Fix: Incorrect page size after E2Pdf Template load in some cases
* Fix: "Divi" extension compatibility fix with 3.18.7
* Fix: "e2pdf-format-output" shortcode warning fix
* Fix: "e2pdf-" shortcodes incorrect render for image and signature field types
* Fix: PHP warnings on settings page
* Fix: "auto" and "inline" options failed on "false" state
* Fix: "frontend.js" missed for "admin" users
* Fix: Default value for border color
* Fix: "Border" on fields after editing PDF
* Fix: Incorrect "HTML" element text position
* Fix: Incorrect "HTML" size
* Fix: "Divi" delete item error in some cases
* Fix: "Actions" and "Conditions" replace shortcodes among import action
* Fix: "Upload PDF" item and extension not updated in some cases
* Fix: "Rectangle" minimum width
* Fix: "Visual Mapper" encoding issue in some cases
* Fix: "Visual Mapper" for "Formidable Forms" showed draft entry in some cases
* Fix: "Visual Mapper" memory leak
* Fix: "Visual Mapper" failed in some cases
* Improvement: "Visual Mapper" checks
* Improvement: "Image" load optimization
* Improvement: Text positions inside inputs
* Improvement: Auto PDF
* Improvement: UI
* Improvement: Translation

= 1.02.02 =
*Release Date - 02 December 2018*

* Add: "Adobe Sign" REST API support
* Fix: "Actions" not fired in some cases
* Fix: "Divi" duplicate replacement of value in some cases
* Fix: "Forminator" empty forms list while creating Template
* Fix: Notifications not showed in some cases
* Fix: Shortcode attributes not rendered correctly in some cases
* Improvement: Optimization

= 1.01.01 =
*Release Date - 26 October 2018*

* Add: "Forminator" support
* Add: Extensions unlock
* Fix: Minor bug fixes

= 1.00.13 =
*Release Date - 15 October 2018*

* Add: "Line Height" option for "textarea" field type
* Add: "Signature" field
* Add: "E-signature" field type
* Add: Typed "Signature" support for all extensions
* Add: "length" property for "input", "textarea" fields
* Add: "comb" (Combination of Characters) property for "input", "textarea" fields
* Add: Notification on failed PDF re-upload
* Add: Notification on failed PDF upload
* Add: "class" attribute for "e2pdf-download" and "e2pdf-view" shortcodes
* Add: Privacy Policy
* Fix: "Divi" item not found in some cases
* Fix: Typed "Signature" color fix
* Fix: Minor style fixes
* Fix: Checkbox value contains comma
* Fix: Signature text generation Formidable Forms
* Fix: Multiple repeat sections Formidable Forms
* Fix: Mozilla Firefox compatibility
* Improvement: "Visual Mapper"
* Improvement: Signature quality, options
* Improvement: Update Process

= 1.00.00 =
*Release Date - 20 August 2018*

* Initial Release

== Upgrade Notice ==

= 1.00.00 =

Initial Release
