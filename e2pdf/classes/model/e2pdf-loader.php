<?php

/**
 * E2pdf Loader Helper
 * 
 * @copyright  Copyright 2017 https://e2pdf.com
 * @license    GPL v2
 * @version    1
 * @link       https://e2pdf.com
 * @since      0.00.01
 */
if (!defined('ABSPATH')) {
    die('Access denied.');
}

class Model_E2pdf_Loader extends Model_E2pdf_Model {

    private $errors = array();
    private $e2pdf_admin_pages = array(
        'toplevel_page_e2pdf',
        'e2pdf_page_e2pdf-templates',
        'e2pdf_page_e2pdf-settings',
        'e2pdf_page_e2pdf-license',
        'e2pdf_page_e2pdf-debug'
    );

    /*
     * On init
     * Create instance of Main Helper
     */

    function __construct() {
        parent::__construct();
    }

    /**
     * Main loader of actions / filters / hooks
     */
    public function load() {
        $this->load_translation();
        $this->load_actions();
        $this->load_filters();
        $this->load_extensions();
        $this->load_hooks();
        $this->load_ajax();
        $this->load_shortcodes();
    }

    /**
     * Load translation
     */
    public function load_translation() {
        load_plugin_textdomain('e2pdf', false, '/e2pdf/languages/');
    }

    /**
     * Load ajax
     */
    public function load_ajax() {
        if (is_admin()) {
            add_action('wp_ajax_e2pdf_save_form', array(new Controller_E2pdf_Templates(), 'ajax_save_form'));
            add_action('wp_ajax_e2pdf_auto', array(new Controller_E2pdf_Templates(), 'ajax_auto'));
            add_action('wp_ajax_e2pdf_upload', array(new Controller_E2pdf_Templates(), 'ajax_upload'));
            add_action('wp_ajax_e2pdf_reupload', array(new Controller_E2pdf_Templates(), 'ajax_reupload'));
            add_action('wp_ajax_e2pdf_extension', array(new Controller_E2pdf_Templates(), 'ajax_extension'));
            add_action('wp_ajax_e2pdf_activate_template', array(new Controller_E2pdf_Templates(), 'ajax_activate_template'));
            add_action('wp_ajax_e2pdf_deactivate_template', array(new Controller_E2pdf_Templates(), 'ajax_deactivate_template'));
            add_action('wp_ajax_e2pdf_visual_mapper', array(new Controller_E2pdf_Templates(), 'ajax_visual_mapper'));
            add_action('wp_ajax_e2pdf_get_styles', array(new Controller_E2pdf_Templates(), 'ajax_get_styles'));
            add_action('wp_ajax_e2pdf_license_key', array(new Controller_E2pdf_License(), 'ajax_change_license_key'));
            add_action('wp_ajax_e2pdf_templates', array(new Controller_E2pdf(), 'ajax_templates'));
            add_action('wp_ajax_e2pdf_dataset', array(new Controller_E2pdf(), 'ajax_dataset'));
            add_action('wp_ajax_e2pdf_delete_item', array(new Controller_E2pdf(), 'ajax_delete_item'));
            add_action('wp_ajax_e2pdf_delete_items', array(new Controller_E2pdf(), 'ajax_delete_items'));
            add_action('wp_ajax_e2pdf_delete_font', array(new Controller_E2pdf_Settings(), 'ajax_delete_font'));
            add_action('wp_ajax_e2pdf_email', array(new Controller_E2pdf_Settings(), 'ajax_email'));
        }
    }

    /**
     * Load actions
     */
    public function load_actions() {
        if (is_admin()) {
            add_action('wpmu_new_blog', array(&$this, 'activate_new_network'), 10, 6);
            add_action('admin_menu', array(&$this, 'admin_menu'));
            add_action('admin_init', array(&$this, 'admin_settings'));
            add_action('admin_enqueue_scripts', array(&$this, 'admin_js'));
            add_action('admin_enqueue_scripts', array(&$this, 'admin_css'));
            add_action('current_screen', array(&$this, 'admin_functions'));
            add_action('plugins_loaded', array(&$this, 'after_load'));
        }
        add_action('wp_enqueue_scripts', array(&$this, 'frontend_js'));
        add_action('wp', array(Helper_E2pdf_View::instance(), 'render_frontend_page'));
        add_action('wp_loaded', array(&$this, 'wp_loaded'));
    }

    public function load_filters() {
        
    }

    public function after_load() {
        if (get_option('e2pdf_version') !== $this->helper->get('version')) {
            $this->activate();
        }
    }

    /**
     * Load extensions and its action/filters
     */
    public function load_extensions() {

        $model_e2pdf_extension = new Model_E2pdf_Extension();
        $extensions = $model_e2pdf_extension->extensions();
        if (!empty($extensions)) {
            foreach ($extensions as $extension => $extension_name) {
                $model_e2pdf_extension->load($extension);
                $model_e2pdf_extension->load_actions();
                $model_e2pdf_extension->load_filters();
                $model_e2pdf_extension->load_shortcodes();
            }
        }
    }

    /**
     * Load filters
     */
    public function load_shortcodes() {
        add_shortcode('e2pdf-download', array(new Model_E2pdf_Shortcode(), 'e2pdf_download'));
        add_shortcode('e2pdf-attachment', array(new Model_E2pdf_Shortcode(), 'e2pdf_attachment'));
        add_shortcode('e2pdf-adobesign', array(new Model_E2pdf_Shortcode(), 'e2pdf_adobesign'));
        add_shortcode('e2pdf-save', array(new Model_E2pdf_Shortcode(), 'e2pdf_save'));
        add_shortcode('e2pdf-view', array(new Model_E2pdf_Shortcode(), 'e2pdf_view'));
        add_shortcode('e2pdf-format-number', array(new Model_E2pdf_Shortcode(), 'e2pdf_format_number'));
        add_shortcode('e2pdf-format-date', array(new Model_E2pdf_Shortcode(), 'e2pdf_format_date'));
        add_shortcode('e2pdf-format-output', array(new Model_E2pdf_Shortcode(), 'e2pdf_format_output'));
        add_shortcode('e2pdf-user', array(new Model_E2pdf_Shortcode(), 'e2pdf_user'));
        add_shortcode('e2pdf-wp', array(new Model_E2pdf_Shortcode(), 'e2pdf_wp'));
        add_shortcode('e2pdf-content', array(new Model_E2pdf_Shortcode(), 'e2pdf_content'));
        add_shortcode('e2pdf-exclude', array(new Model_E2pdf_Shortcode(), 'e2pdf_exclude'));
        add_shortcode('e2pdf-filter', array(new Model_E2pdf_Shortcode(), 'e2pdf_filter'));
    }

    /**
     * Load hooks
     */
    public function load_hooks() {
        register_activation_hook($this->helper->get('plugin_file_path'), array(&$this, 'activate'));
        register_deactivation_hook($this->helper->get('plugin_file_path'), array(&$this, 'deactivate'));
        register_uninstall_hook($this->helper->get('plugin_file_path'), array('Model_E2pdf_Loader', 'uninstall'));
    }

    /**
     * Load admin menu
     */
    public function admin_menu() {
        ob_start();
        $caps = $this->helper->get_caps();
        if (current_user_can('manage_options')) {
            foreach ($caps as $cap_key => $cap) {
                $caps[$cap_key]['cap'] = 'manage_options';
            }
        }

        add_menu_page('e2pdf', 'E2Pdf', $caps['e2pdf']['cap'], 'e2pdf', array(Helper_E2pdf_View::instance(), 'render_page'), '', '26');
        add_submenu_page('e2pdf', __('Export', 'e2pdf'), __('Export', 'e2pdf'), $caps['e2pdf']['cap'], 'e2pdf', array(Helper_E2pdf_View::instance(), 'render_page'));
        add_submenu_page('e2pdf', __('Templates', 'e2pdf'), __('Templates', 'e2pdf'), $caps['e2pdf_templates']['cap'], 'e2pdf-templates', array(Helper_E2pdf_View::instance(), 'render_page'));
        add_submenu_page('e2pdf', __('Settings', 'e2pdf'), __('Settings', 'e2pdf'), $caps['e2pdf_settings']['cap'], 'e2pdf-settings', array(Helper_E2pdf_View::instance(), 'render_page'));
        add_submenu_page('e2pdf', __('License', 'e2pdf'), __('License', 'e2pdf'), $caps['e2pdf_license']['cap'], 'e2pdf-license', array(Helper_E2pdf_View::instance(), 'render_page'));
        if (get_option('e2pdf_debug') === '1') {
            add_submenu_page('e2pdf', __('Debug', 'e2pdf'), __('Debug', 'e2pdf'), $caps['e2pdf_debug']['cap'], 'e2pdf-debug', array(Helper_E2pdf_View::instance(), 'render_page'));
        }
    }

    /**
     * Setup settings page
     */
    public function admin_settings() {
        register_setting('e2pdf-settings', 'e2pdf_debug');
    }

    /**
     * Load admin css
     */
    public function admin_css($page) {
        if (get_option('e2pdf_debug') === '1') {
            $version = strtotime("now");
        } else {
            $version = $this->helper->get('version');
        }

        wp_register_style('e2pdf.backend', plugins_url('css/e2pdf.backend.css', $this->helper->get('plugin_file_path')), array(), $version);
        wp_enqueue_style('e2pdf.backend');
    }

    /**
     * Load admin javascript
     * 
     * @param string $page - Current page
     */
    public function admin_js($page) {

        if (!in_array($page, $this->e2pdf_admin_pages)) {
            return;
        }

        if (get_option('e2pdf_debug') === '1') {
            $version = strtotime("now");
        } else {
            $version = $this->helper->get('version');
        }

        wp_enqueue_script(
                'js/e2pdf.backend', plugins_url('js/e2pdf.backend.js', $this->helper->get('plugin_file_path')), array('jquery'), $version
        );

        $js_lang = $this->get_js('lang');
        wp_localize_script('js/e2pdf.backend', 'e2pdfLang', $js_lang);

        $js_size = $this->get_js('template_sizes');
        wp_localize_script('js/e2pdf.backend', 'e2pdfTemplateSizes', $js_size);

        $js_extension = $this->get_js('template_extensions');
        wp_localize_script('js/e2pdf.backend', 'e2pdfTemplateExtensions', $js_extension);

        $js_params = $this->get_js('params');
        wp_localize_script('js/e2pdf.backend', 'e2pdfParams', $js_params);
    }

    /**
     * Load admin javascript
     * 
     * @param string $page - Current page
     */
    public function frontend_js() {

        wp_register_script(
                'js/e2pdf.frontend', plugins_url('js/e2pdf.frontend.js', $this->helper->get('plugin_file_path')), array('jquery'), $this->helper->get('version')
        );
        wp_enqueue_script(
                'js/e2pdf.frontend'
        );
    }

    public function get_js($type) {

        $data = array();

        switch ($type) {
            case 'lang':
                $data = array(
                    'Delete' => __('Delete', 'e2pdf'),
                    'Properties' => __('Properties', 'e2pdf'),
                    'License Key' => __('License Key', 'e2pdf'),
                    'Submit' => __('Submit', 'e2pdf'),
                    'Are you sure want to remove page?' => __('Are you sure want to remove page?', 'e2pdf'),
                    'Are you sure want to remove font?' => __('Are you sure want to remove font?', 'e2pdf'),
                    'Empty PDF' => __('Empty PDF', 'e2pdf'),
                    'Upload PDF' => __('Upload PDF', 'e2pdf'),
                    'Auto PDF' => __('Auto PDF', 'e2pdf'),
                    'Create PDF' => __('Create PDF', 'e2pdf'),
                    'Extension' => __('Extension', 'e2pdf'),
                    'Size' => __('Size', 'e2pdf'),
                    'Properties' => __('Properties', 'e2pdf'),
                    'Name' => __('Name', 'e2pdf'),
                    'Enter link here' => __('Enter link here', 'e2pdf'),
                    'Z-index' => __('Z-index', 'e2pdf'),
                    'Border' => __('Border', 'e2pdf'),
                    'Background' => __('Background', 'e2pdf'),
                    'Left' => __('Left', 'e2pdf'),
                    'Right' => __('Right', 'e2pdf'),
                    'Top' => __('Top', 'e2pdf'),
                    'Center' => __('Center', 'e2pdf'),
                    'Bottom' => __('Bottom', 'e2pdf'),
                    'Justify' => __('Justify', 'e2pdf'),
                    'Color' => __('Color', 'e2pdf'),
                    'Border Color' => __('Border Color', 'e2pdf'),
                    'Line Height' => __('Line Height', 'e2pdf'),
                    'Padding' => __('Padding', 'e2pdf'),
                    'Width' => __('Width', 'e2pdf'),
                    'Height' => __('Height', 'e2pdf'),
                    'Text Color' => __('Text Color', 'e2pdf'),
                    'Value' => __('Value', 'e2pdf'),
                    'Font' => __('Font', 'e2pdf'),
                    'Options' => __('Options', 'e2pdf'),
                    'Option' => __('Option', 'e2pdf'),
                    'Group' => __('Group', 'e2pdf'),
                    'Group Name' => __('Group Name', 'e2pdf'),
                    'Group Value' => __('Group Value', 'e2pdf'),
                    'Selector' => __('Selector', 'e2pdf'),
                    'Check' => __('Check', 'e2pdf'),
                    'Circle' => __('Circle', 'e2pdf'),
                    'Cross' => __('Cross', 'e2pdf'),
                    'Diamond' => __('Diamond', 'e2pdf'),
                    'Square' => __('Square', 'e2pdf'),
                    'Star' => __('Star', 'e2pdf'),
                    'Type' => __('Type', 'e2pdf'),
                    'Scale' => __('Scale', 'e2pdf'),
                    'Width&Height' => __('Width&Height', 'e2pdf'),
                    'Width' => __('Width', 'e2pdf'),
                    'Height' => __('Height', 'e2pdf'),
                    'Choose Image' => __('Choose Image', 'e2pdf'),
                    'PDF Options' => __('PDF Options', 'e2pdf'),
                    'PDF Upload' => __('PDF Upload', 'e2pdf'),
                    'Global Actions' => __('Global Actions', 'e2pdf'),
                    'Item' => __('Item', 'e2pdf'),
                    'Position' => __('Position', 'e2pdf'),
                    'Copy' => __('Copy', 'e2pdf'),
                    'Cut' => __('Cut', 'e2pdf'),
                    'Paste' => __('Paste', 'e2pdf'),
                    'Paste in Place' => __('Paste in Place', 'e2pdf'),
                    'Apply' => __('Apply', 'e2pdf'),
                    'Dynamic Height' => __('Dynamic Height', 'e2pdf'),
                    'Text Align' => __('Text Align', 'e2pdf'),
                    'Read-Only' => __('Read-Only', 'e2pdf'),
                    'Multiline' => __('Multiline', 'e2pdf'),
                    'Required' => __('Required', 'e2pdf'),
                    'Settings' => __('Settings', 'e2pdf'),
                    'Relative' => __('Relative', 'e2pdf'),
                    'Page Size' => __('Page Size', 'e2pdf'),
                    'Custom' => __('Custom', 'e2pdf'),
                    'Size Preset' => __('Size Preset', 'e2pdf'),
                    'Page Options' => __('Page Options', 'e2pdf'),
                    'Select' => __('Select', 'e2pdf'),
                    'Saved Template will be overwritten! Are you sure want to continue?' => __('Saved Template will be overwritten! Are you sure want to continue?', 'e2pdf'),
                    'All pages will be removed! Are you sure want to continue?' => __('All pages will be removed! Are you sure want to continue?', 'e2pdf'),
                    'Adding new pages not available in "Uploaded PDF"' => __('Adding new pages not available in "Uploaded PDF"', 'e2pdf'),
                    'Dataset will be removed! Are you sure want to continue?' => __('Dataset will be removed! Are you sure want to continue?', 'e2pdf'),
                    'All datasets will be removed! Are you sure want to continue?' => __('All datasets will be removed! Are you sure want to continue?', 'e2pdf'),
                    'WARNING: Template has changes after last save! Changes will be lost!' => __('WARNING: Template has changes after last save! Changes will be lost!', 'e2pdf'),
                    'Element will be removed! Are you sure want to continue?' => __('Element will be removed! Are you sure want to continue?', 'e2pdf'),
                    'Elements will be removed! Are you sure want to continue?' => __('Elements will be removed! Are you sure want to continue?', 'e2pdf'),
                    'Action will be removed! Are you sure want to continue?' => __('Action will be removed! Are you sure want to continue?', 'e2pdf'),
                    'Condition will be removed! Are you sure want to continue?' => __('Condition will be removed! Are you sure want to continue?', 'e2pdf'),
                    'All Field Values will be overwritten! Are you sure want to continue?' => __('All Field Values will be overwritten! Are you sure want to continue?', 'e2pdf'),
                    'Website will be forced to use "FREE" License Key! Are you sure want to continue?' => __('Website will be forced to use "FREE" License Key! Are you sure want to continue?', 'e2pdf'),
                    'Not Available in Revision Edit Mode' => __('Not Available in Revision Edit Mode', 'e2pdf'),
                    'WYSIWYG Editor is disabled for this HTML Object' => __('WYSIWYG Editor is disabled for this HTML Object', 'e2pdf'),
                    'WYSIWYG can be applied only to HTML Object' => __('WYSIWYG can be applied only to HTML Object', 'e2pdf'),
                    'RTL' => __('RTL', 'e2pdf'),
                    'Hide' => __('Hide', 'e2pdf'),
                    'Show' => __('Show', 'e2pdf'),
                    'Password' => __('Password', 'e2pdf'),
                    'Map Field' => __('Map Field', 'e2pdf'),
                    'Parent' => __('Parent', 'e2pdf'),
                    '--- Select ---' => __('--- Select ---', 'e2pdf'),
                    'Activated' => __('Activated', 'e2pdf'),
                    'Not Activated' => __('Not Activated', 'e2pdf'),
                    'Page ID' => __('Page ID', 'e2pdf'),
                    'New Position' => __('New Position', 'e2pdf'),
                    'Render New Fields' => __('Render New Fields', 'e2pdf'),
                    'Flush Elements' => __('Flush Elements', 'e2pdf'),
                    'Keep dimension' => __('Keep dimension', 'e2pdf'),
                    'ID' => __('ID', 'e2pdf'),
                    'Only 1 page allowed with "FREE" license type' => __('Only 1 page allowed with "FREE" license type', 'e2pdf'),
                    'Last condition can\'t be removed' => __('Last condition can\'t be removed', 'e2pdf'),
                    'Confirmation code' => __('Confirmation code', 'e2pdf'),
                    'Code' => __('Code', 'e2pdf'),
                    'Visual Mapper' => __('Visual Mapper', 'e2pdf'),
                    'Auto' => __('Auto', 'e2pdf'),
                    'Actions' => __('Actions', 'e2pdf'),
                    'Save' => __('Save', 'e2pdf'),
                    'Resize' => __('Resize', 'e2pdf'),
                    'Horizontal align' => __('Horizontal align', 'e2pdf'),
                    'Vertical align' => __('Vertical align', 'e2pdf'),
                    'Middle' => __('Middle', 'e2pdf'),
                    'Apply If' => __('Apply If', 'e2pdf'),
                    'Action' => __('Action', 'e2pdf'),
                    'Property' => __('Property', 'e2pdf'),
                    'If' => __('If', 'e2pdf'),
                    'Condition' => __('Condition', 'e2pdf'),
                    'Change' => __('Change', 'e2pdf'),
                    'Any' => __('Any', 'e2pdf'),
                    'All' => __('All', 'e2pdf'),
                    'Order' => __('Order', 'e2pdf'),
                    'E-signature' => __('E-signature', 'e2pdf'),
                    'Contact' => __('Contact', 'e2pdf'),
                    'Location' => __('Location', 'e2pdf'),
                    'Reason' => __('Reason', 'e2pdf'),
                    'Placeholder' => __('Placeholder', 'e2pdf'),
                    'Length' => __('Length', 'e2pdf'),
                    'Comb' => __('Comb', 'e2pdf'),
                    'None' => __('None', 'e2pdf'),
                    'Highlight' => __('Highlight', 'e2pdf'),
                    'Invert' => __('Invert', 'e2pdf'),
                    'Outline' => __('Outline', 'e2pdf'),
                    'Push' => __('Push', 'e2pdf'),
                    'Title' => __('Title', 'e2pdf'),
                    'Status' => __('Status', 'e2pdf'),
                    'Add Action' => __('Add Action', 'e2pdf'),
                    'Shortcodes' => __('Shortcodes', 'e2pdf'),
                    'Labels' => __('Labels', 'e2pdf'),
                    'Field Values' => __('Field Values', 'e2pdf'),
                    'Field Names' => __('Field Names', 'e2pdf'),
                    'Field Name' => __('Field Name', 'e2pdf'),
                    'As Field Name' => __('As Field Name', 'e2pdf'),
                    'Confirm' => __('Confirm', 'e2pdf'),
                    'Cancel' => __('Cancel', 'e2pdf'),
                    'Hide (If Empty)' => __('Hide (If Empty)', 'e2pdf'),
                    'Hide Page (If Empty)' => __('Hide Page (If Empty)', 'e2pdf'),
                    'Preg Replace' => __('Preg Replace', 'e2pdf'),
                    'Pattern' => __('Pattern', 'e2pdf'),
                    'Replacement' => __('Replacement', 'e2pdf'),
                    'Replace Value' => __('Replace Value', 'e2pdf'),
                    'Auto-Close' => __('Auto-Close', 'e2pdf'),
                    'E2Pdf License Key' => __('E2Pdf License Key', 'e2pdf'),
                    'New Lines to BR' => __('New Lines to BR', 'e2pdf'),
                    'Disable WYSIWYG Editor' => __('Disable WYSIWYG Editor', 'e2pdf'),
                    'Enabling WYSIWYG can affect "HTML" Source' => __('Enabling WYSIWYG can affect "HTML" Source', 'e2pdf'),
                    'Hidden Fields' => __('Hidden Fields', 'e2pdf'),
                    'Access By URL' => __('Access By URL', 'e2pdf'),
                    'Error Message' => __('Error Message', 'e2pdf')
                );
                break;
            case 'params':
                $data = array(
                    'nonce' => wp_create_nonce('e2pdf_ajax'),
                    'plugins_url' => plugins_url('', $this->helper->get('plugin_file_path')),
                    'upload_url' => $this->helper->get_upload_url(),
                    'license_type' => $this->helper->get('license')->get('type')
                );
                break;
            case 'template_sizes':
                $controller_e2pdf_templates = new Controller_E2pdf_Templates();
                $data = $controller_e2pdf_templates->get_sizes_list();
                break;
            case 'template_extensions':
                $model_e2pdf_extension = new Model_E2pdf_Extension();
                $data = $model_e2pdf_extension->extensions();
                break;
            default:
                break;
        }

        return $data;
    }

    /**
     * Check requirenments before activation
     */
    public function requirenments() {
        if (version_compare(PHP_VERSION, '5.3.3', '<')) {
            throw new Exception(
            sprintf(__("E2Pdf requires PHP version 5.3.3 or later. Your PHP version is %s", 'e2pdf'), PHP_VERSION)
            );
        }
    }

    public function admin_functions() {
        $current_screen = get_current_screen();
        if (!$current_screen || !in_array($current_screen->id, $this->e2pdf_admin_pages)) {
            return;
        }

        if ($current_screen->id == 'e2pdf_page_e2pdf-templates' && !isset($_GET['action'])) {
            $screen_option = array(
                'label' => __('Templates per page', 'e2pdf') . ':',
                'default' => get_option('e2pdf_templates_screen_per_page') ? get_option('e2pdf_templates_screen_per_page') : '20',
                'option' => 'e2pdf_templates_screen_per_page'
            );
            add_screen_option('per_page', $screen_option);
        }

        $this->helper->set('license', new Model_E2pdf_License());
    }

    public function wp_loaded() {
        if (!get_transient('e2pdf_adobesign_refresh_token')) {
            new Model_E2pdf_AdobeSign();
        }
    }

    /**
     * On plugin activation
     */
    public function activate() {
        global $wpdb;

        try {
            $this->requirenments();
            if (is_multisite() && is_super_admin() && is_main_site()) {
                foreach ($wpdb->get_col("SELECT blog_id FROM $wpdb->blogs") as $blog_id) {
                    $this->activate_site($blog_id);
                }
            } else {
                $this->activate_site();
            }
        } catch (Exception $e) {
            echo "<div style='line-height: 70px;'>";
            echo $e->getMessage();
            echo "</div>";
            exit();
        }
    }

    public function activate_new_network($blog_id, $user_id, $domain, $path, $site_id, $meta) {
        if (is_plugin_active_for_network('e2pdf/e2pdf.php')) {
            $this->activate_site($blog_id);
        }
    }

    public function activate_site($blog_id = false) {
        global $wpdb;

        $db_prefix = $wpdb->prefix;

        $wp_upload_dir = wp_upload_dir();

        if ($blog_id) {
            switch_to_blog($blog_id);
            $wp_upload_dir = wp_upload_dir();
            $db_prefix = $wpdb->get_blog_prefix($blog_id);
            if (!is_main_site($blog_id)) {
                $this->helper->set('upload_dir', $wp_upload_dir['basedir'] . '/e2pdf/');
                $this->helper->set('tmp_dir', $this->helper->get('upload_dir') . 'tmp/');
                $this->helper->set('pdf_dir', $this->helper->get('upload_dir') . 'pdf/');
                $this->helper->set('fonts_dir', $this->helper->get('upload_dir') . 'fonts/');
                $this->helper->set('tpl_dir', $this->helper->get('upload_dir') . 'tpl/');
            }
        }

        $dirs = array(
            $this->helper->get('upload_dir'),
            $this->helper->get('tmp_dir'),
            $this->helper->get('pdf_dir'),
            $this->helper->get('fonts_dir'),
            $this->helper->get('tpl_dir'),
        );

        if (!is_main_site($blog_id)) {
            array_unshift($dirs, $wp_upload_dir['basedir']);
        }

        foreach ($dirs as $dir) {
            if ($this->helper->create_dir($dir)) {
                if ($dir == $this->helper->get('fonts_dir')) {
                    copy($this->helper->get('plugin_dir') . "data/fonts/NotoSans-Regular.ttf", $this->helper->get('fonts_dir') . "NotoSans-Regular.ttf");
                }
            } else {
                throw new Exception(
                sprintf(__("Can't create folder %s", 'e2pdf'), $dir)
                );
            }
        }

        $update = true;
        $options = Model_E2pdf_Options::get_options();
        foreach ($options as $option_key => $option_value) {
            if (get_option($option_key) === false) {
                if ($option_key === 'e2pdf_version') {
                    $update = false;
                }
                add_option($option_key, $option_value);
            }
        }

        $wpdb->query("CREATE TABLE IF NOT EXISTS `" . $db_prefix . "e2pdf_templates` (
        `ID` int(11) NOT NULL AUTO_INCREMENT,
        `uid` varchar(255) NOT NULL,
        `pdf` text,
        `title` text,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        `flatten` enum('0','1','2') NOT NULL DEFAULT '0',
        `compression` int(1) NOT NULL DEFAULT '-1',
        `appearance` enum('0','1') NOT NULL DEFAULT '0',
        `width` int(11) NOT NULL DEFAULT '0',
        `height` int(11) NOT NULL DEFAULT '0',
        `extension` varchar(255) NOT NULL,
        `item` varchar(255) NOT NULL,
        `format` enum('pdf') NOT NULL DEFAULT 'pdf',
        `dataset_title` text NOT NULL,
        `button_title` text NOT NULL,
        `inline` enum('0','1') NOT NULL DEFAULT '0',
        `auto` enum('0','1') NOT NULL DEFAULT '0',
        `name` text NOT NULL,
        `password` text NOT NULL,
        `meta_title` text NOT NULL,
        `meta_subject` text NOT NULL,
        `meta_author` text NOT NULL,
        `meta_keywords` text NOT NULL,
        `font` varchar(255) NOT NULL,
        `font_size` varchar(255) NOT NULL,
        `font_color` varchar(255) NOT NULL,
        `line_height` varchar(255) NOT NULL,
        `fonts` longtext NOT NULL,
        `trash` enum('0','1') NOT NULL DEFAULT '0',
        `activated` enum('0','1') NOT NULL DEFAULT '0',
        `locked` enum('0','1') NOT NULL DEFAULT '0',
        `author` int(11) NOT NULL,
        `actions` longtext NOT NULL,
            PRIMARY KEY (`ID`)
        ) CHARSET=utf8 COLLATE=utf8_general_ci");

        if (version_compare(get_option('e2pdf_version'), '0.01.54', '<')) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_templates` DROP COLUMN `blank`");
        }

        if (version_compare(get_option('e2pdf_version'), '1.06.00', '<')) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_templates` ADD COLUMN `meta_title` text NOT NULL AFTER password");
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_templates` ADD COLUMN `meta_subject` text NOT NULL AFTER meta_title");
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_templates` ADD COLUMN `meta_author` text NOT NULL AFTER meta_subject");
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_templates` ADD COLUMN `meta_keywords` text NOT NULL AFTER meta_author");
        }

        if (version_compare(get_option('e2pdf_version'), '1.09.05', '<')) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_templates` ADD COLUMN `actions` longtext NOT NULL AFTER author");
        }

        $wpdb->query("CREATE TABLE IF NOT EXISTS `" . $db_prefix . "e2pdf_entries` (
        `ID` int(11) NOT NULL AUTO_INCREMENT,
        `uid` varchar(255) NOT NULL,
        `entry` longtext,
        `pdf_num` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`ID`)
        ) CHARSET=utf8 COLLATE=utf8_general_ci");

        if (version_compare(get_option('e2pdf_version'), '0.01.33', '<')) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_entries` ADD COLUMN `pdf_num` int(11) NOT NULL DEFAULT '0'");
        }

        if (version_compare(get_option('e2pdf_version'), '1.09.03', '<') || !$update) {
            $wpdb->query("CREATE INDEX `uid` ON `" . $db_prefix . "e2pdf_entries` (`uid`); ");
        }

        $wpdb->query("CREATE TABLE IF NOT EXISTS `" . $db_prefix . "e2pdf_datasets` (
        `ID` int(11) NOT NULL AUTO_INCREMENT,
        `extension` varchar(255) NOT NULL,
        `item` varchar(255) NOT NULL,
        `entry` longtext,
            PRIMARY KEY (`ID`)
        ) CHARSET=utf8 COLLATE=utf8_general_ci");

        $wpdb->query("CREATE TABLE IF NOT EXISTS `" . $db_prefix . "e2pdf_pages` (
        `page_id` int(11) NOT NULL DEFAULT '0',
        `template_id` int(11) NOT NULL DEFAULT '0',
        `properties` longtext NOT NULL,
        `actions` longtext NOT NULL,
        `revision_id` int(11) NOT NULL DEFAULT '0'
        ) CHARSET=utf8 COLLATE=utf8_general_ci");

        if (version_compare(get_option('e2pdf_version'), '0.01.63', '<')) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_pages` ADD COLUMN `actions` longtext NOT NULL");
        }

        if (version_compare(get_option('e2pdf_version'), '1.09.02', '<')) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_pages` ADD COLUMN `revision_id` int(11) NOT NULL DEFAULT '0' AFTER actions");
        }

        if (version_compare(get_option('e2pdf_version'), '1.09.03', '<') || !$update) {
            $wpdb->query("CREATE INDEX `page_id` ON `" . $db_prefix . "e2pdf_pages` (`page_id`); ");
            $wpdb->query("CREATE INDEX `template_id` ON `" . $db_prefix . "e2pdf_pages` (`template_id`); ");
            $wpdb->query("CREATE INDEX `revision_id` ON `" . $db_prefix . "e2pdf_pages` (`revision_id`); ");
        }

        $wpdb->query("CREATE TABLE IF NOT EXISTS `" . $db_prefix . "e2pdf_elements` (
        `page_id` int(11) NOT NULL DEFAULT '0',
        `template_id` int(11) NOT NULL DEFAULT '0',
        `element_id` int(11) NOT NULL DEFAULT '0',
        `name` text NOT NULL,
        `type` varchar(255) NOT NULL,
        `top` int(11) NOT NULL DEFAULT '0',
        `left` int(11) NOT NULL DEFAULT '0',
        `width` int(11) NOT NULL DEFAULT '0',
        `height` int(11) NOT NULL DEFAULT '0',
        `value` longtext NOT NULL,
        `properties` longtext NOT NULL,
        `actions` longtext NOT NULL,
        `revision_id` int(11) NOT NULL DEFAULT '0'
        ) CHARSET=utf8 COLLATE=utf8_general_ci");

        if (version_compare(get_option('e2pdf_version'), '0.01.63', '<')) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_elements` ADD COLUMN `actions` longtext NOT NULL");
        }

        if (version_compare(get_option('e2pdf_version'), '1.04.00', '<')) {
            $wpdb->query("UPDATE `" . $db_prefix . "e2pdf_elements` ee INNER JOIN `" . $db_prefix . "e2pdf_pages` ep ON ee.page_id = ep.ID set ee.page_id = ep.page_id ");
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_pages` DROP COLUMN `ID`");
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_elements` DROP COLUMN `ID`");
        }

        if (version_compare(get_option('e2pdf_version'), '1.06.00', '<')) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_elements` ADD COLUMN `name` text NOT NULL AFTER element_id");
        }

        if (version_compare(get_option('e2pdf_version'), '1.09.02', '<')) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_elements` ADD COLUMN `revision_id` int(11) NOT NULL DEFAULT '0' AFTER actions");
        }

        if (version_compare(get_option('e2pdf_version'), '1.09.03', '<') || !$update) {
            $wpdb->query("CREATE INDEX `page_id` ON `" . $db_prefix . "e2pdf_elements` (`page_id`); ");
            $wpdb->query("CREATE INDEX `template_id` ON `" . $db_prefix . "e2pdf_elements` (`template_id`); ");
            $wpdb->query("CREATE INDEX `revision_id` ON `" . $db_prefix . "e2pdf_elements` (`revision_id`); ");
        }

        $wpdb->query("CREATE TABLE IF NOT EXISTS `" . $db_prefix . "e2pdf_revisions` (
        `revision_id` int(11) NOT NULL DEFAULT '0',
        `template_id` int(11) NOT NULL DEFAULT '0',
        `pdf` text,
        `title` text,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        `flatten` enum('0','1','2') NOT NULL DEFAULT '0',
        `compression` int(1) NOT NULL DEFAULT '-1',
        `appearance` enum('0','1') NOT NULL DEFAULT '0',
        `width` int(11) NOT NULL DEFAULT '0',
        `height` int(11) NOT NULL DEFAULT '0',
        `extension` varchar(255) NOT NULL,
        `item` varchar(255) NOT NULL,
        `format` enum('pdf') NOT NULL DEFAULT 'pdf',
        `dataset_title` text NOT NULL,
        `button_title` text NOT NULL,
        `inline` enum('0','1') NOT NULL DEFAULT '0',
        `auto` enum('0','1') NOT NULL DEFAULT '0',
        `name` text NOT NULL,
        `password` text NOT NULL,
        `meta_title` text NOT NULL,
        `meta_subject` text NOT NULL,
        `meta_author` text NOT NULL,
        `meta_keywords` text NOT NULL,
        `font` varchar(255) NOT NULL,
        `font_size` varchar(255) NOT NULL,
        `font_color` varchar(255) NOT NULL,
        `line_height` varchar(255) NOT NULL,
        `fonts` longtext NOT NULL,
        `author` int(11) NOT NULL,
        `actions` longtext NOT NULL
        ) CHARSET=utf8 COLLATE=utf8_general_ci");

        if (version_compare(get_option('e2pdf_version'), '1.09.03', '<') || !$update) {
            $wpdb->query("CREATE INDEX `revision_id` ON `" . $db_prefix . "e2pdf_revisions` (`revision_id`); ");
            $wpdb->query("CREATE INDEX `template_id` ON `" . $db_prefix . "e2pdf_revisions` (`template_id`); ");
        }

        if (version_compare(get_option('e2pdf_version'), '1.09.05', '<')) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_revisions` ADD COLUMN `actions` longtext NOT NULL AFTER author");
        }

        if (get_option('e2pdf_version') !== $this->helper->get('version')) {
            update_option('e2pdf_version', $this->helper->get('version'));
        }

        $model_e2pdf_api = new Model_E2pdf_Api();
        $model_e2pdf_api->set(array(
            'action' => 'common/activate'
        ));
        $model_e2pdf_api->request();

        $model_e2pdf_license = new Model_E2pdf_License();

        if ($blog_id) {
            restore_current_blog();
            $wp_upload_dir = wp_upload_dir();
            $this->helper->set('upload_dir', $wp_upload_dir['basedir'] . '/e2pdf/');
            $this->helper->set('tmp_dir', $this->helper->get('upload_dir') . 'tmp/');
            $this->helper->set('pdf_dir', $this->helper->get('upload_dir') . 'pdf/');
            $this->helper->set('fonts_dir', $this->helper->get('upload_dir') . 'fonts/');
            $this->helper->set('tpl_dir', $this->helper->get('upload_dir') . 'tpl/');
        }
    }

    /**
     * On plugin deactivation
     */
    public function deactivate() {
        
    }

    /**
     * On plugin uninstall
     */
    public static function uninstall() {
        global $wpdb;

        if (is_multisite() && is_super_admin() && is_main_site()) {
            foreach ($wpdb->get_col("SELECT blog_id FROM $wpdb->blogs") as $blog_id) {
                self::uninstall_site($blog_id);
            }
        } else {
            self::uninstall_site();
        }
    }

    public static function uninstall_site($blog_id = false) {
        global $wpdb;

        $db_prefix = $wpdb->prefix;

        $wp_upload_dir = wp_upload_dir();

        $helper_e2pdf_helper = Helper_E2pdf_Helper::instance();

        if ($blog_id) {
            switch_to_blog($blog_id);
            $wp_upload_dir = wp_upload_dir();
            $db_prefix = $wpdb->get_blog_prefix($blog_id);

            if (!is_main_site($blog_id)) {
                $helper_e2pdf_helper->set('upload_dir', $wp_upload_dir['basedir'] . '/e2pdf/');
            }
        }


        $model_e2pdf_api = new Model_E2pdf_Api();
        $model_e2pdf_api->set(array(
            'action' => 'common/uninstall'
        ));
        $model_e2pdf_api->request();

        $options = Model_E2pdf_Options::get_options();
        foreach ($options as $option_key => $option_value) {
            delete_option($option_key, $option_value);
        }

        $wpdb->query('DROP TABLE IF EXISTS `' . $db_prefix . 'e2pdf_templates' . '`');
        $wpdb->query('DROP TABLE IF EXISTS `' . $db_prefix . 'e2pdf_entries' . '`');
        $wpdb->query('DROP TABLE IF EXISTS `' . $db_prefix . 'e2pdf_datasets' . '`');
        $wpdb->query('DROP TABLE IF EXISTS `' . $db_prefix . 'e2pdf_pages' . '`');
        $wpdb->query('DROP TABLE IF EXISTS `' . $db_prefix . 'e2pdf_elements' . '`');
        $wpdb->query('DROP TABLE IF EXISTS `' . $db_prefix . 'e2pdf_revisions' . '`');

        $wpdb->query($wpdb->prepare('DELETE FROM `' . $db_prefix . 'options' . '` WHERE option_name LIKE %s OR option_name LIKE %s', '_transient_e2pdf_%', '_transient_e2pdf_%'));

        $helper_e2pdf_helper->delete_dir($helper_e2pdf_helper->get('upload_dir'));

        $caps = $helper_e2pdf_helper->get_caps();
        $roles = wp_roles()->get_names();
        foreach ($roles as $role_key => $sub_role) {
            $role = get_role($role_key);
            foreach ($caps as $cap_key => $cap) {
                $role->remove_cap($cap_key);
            }
        }

        if ($blog_id) {
            restore_current_blog();
            $wp_upload_dir = wp_upload_dir();
            $helper_e2pdf_helper->set('upload_dir', $wp_upload_dir['basedir'] . '/e2pdf/');
        }
    }

}
