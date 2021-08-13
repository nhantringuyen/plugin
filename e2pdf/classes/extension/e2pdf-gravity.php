<?php

/**
 * E2pdf Gravity Forms Extension
 * 
 * @copyright  Copyright 2017 https://e2pdf.com
 * @license    GPL v2
 * @version    1
 * @link       https://e2pdf.com
 * @since      1.07.04
 */
if (!defined('ABSPATH')) {
    die('Access denied.');
}

class Extension_E2pdf_Gravity extends Model_E2pdf_Model {

    private $options;
    private $info = array(
        'key' => 'gravity',
        'title' => 'Gravity Forms'
    );

    function __construct() {
        parent::__construct();
    }

    /**
     * Get info about extension
     * 
     * @param string $key - Key to get assigned extension info value
     * 
     * @return array|string - Extension Key and Title or Assigned extension info value
     */
    public function info($key = false) {
        if ($key && isset($this->info[$key])) {
            return $this->info[$key];
        } else {
            return array(
                $this->info['key'] => $this->info['title']
            );
        }
    }

    /**
     * Check if needed plugin active
     * 
     * @return bool - Activated/Not Activated plugin
     */
    public function active() {

        if (!function_exists('is_plugin_active')) {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        if (is_plugin_active('gravityforms/gravityforms.php')) {
            return true;
        }
        return false;
    }

    /**
     * Set option
     * 
     * @param string $key - Key of option
     * @param string $value - Value of option
     * 
     * @return bool - Status of setting option
     */
    public function set($key, $value) {

        if (!isset($this->options)) {
            $this->options = new stdClass();
        }

        $this->options->$key = $value;
        return true;
    }

    /**
     * Get option by key
     * 
     * @param string $key - Key to get assigned option value
     * 
     * @return mixed
     */
    public function get($key) {
        if (isset($this->options->$key)) {
            $value = $this->options->$key;
            return $value;
        } elseif ($key == 'args') {
            return array();
        } else {
            return false;
        }
    }

    /**
     * Get items to work with
     * 
     * @return array() - List of available items
     */
    public function items() {

        $content = array();

        if (class_exists('GFFormsModel')) {
            $forms = GFFormsModel::get_forms(null, 'title');
            if ($forms) {
                foreach ($forms as $key => $value) {
                    $content[] = $this->item($value->id);
                }
            }
        }

        return $content;
    }

    /**
     * Get entries for export
     * 
     * @param int $item - Form ID
     * @param string $name - Entries names
     * 
     * @return array() - Entries list
     */
    public function datasets($item = false, $name = false) {

        $item = (int) $item;
        $datasets = array();

        if (class_exists('GFAPI') && $item) {
            $datasets_tmp = GFAPI::get_entries($item);
            if ($datasets_tmp) {
                foreach ($datasets_tmp as $key => $dataset) {
                    $this->set('item', $item);
                    $this->set('dataset', $dataset['id']);

                    $dataset_title = $this->render($name);
                    if (!$dataset_title) {
                        $dataset_title = $dataset['id'];
                    }
                    $datasets[] = array(
                        'key' => $dataset['id'],
                        'value' => $dataset_title
                    );
                }
            }
        }

        return $datasets;
    }

    /**
     * Get dataset
     * 
     * @param int $dataset - Dataset ID
     * 
     * @return object - Dataset
     */
    public function dataset($dataset = false) {

        $dataset = (int) $dataset;

        if (!$dataset) {
            return false;
        }

        $entry = GFFormsModel::get_entry($dataset);
        $item = $entry && isset($entry['form_id']) ? $entry['form_id'] : '0';

        $data = new stdClass();
        $data->url = $this->helper->get_url(array('page' => 'gf_entries', 'view' => 'entry', 'id' => $item, 'lid' => $dataset));

        return $data;
    }

    /**
     * Get item
     * 
     * @param int $item - Item ID
     * 
     * @return object - Item
     */
    public function item($item = false) {

        $item = (int) $item;
        if (!$item && $this->get('item')) {
            $item = $this->get('item');
        }

        $gravity_form = false;
        if (class_exists('GFFormsModel')) {
            $gravity_form = GFFormsModel::get_form_meta($item);
        }

        $form = new stdClass();
        if ($gravity_form) {
            $form->id = (string) $item;
            $form->url = $this->helper->get_url(array('page' => 'gf_edit_forms', 'id' => $item));
            $form->name = $gravity_form['title'];
        } else {
            $form->id = '';
            $form->url = 'javascript:void(0);';
            $form->name = '';
        }
        return $form;
    }

    /**
     * Render value according to content
     * 
     * @param string $value - Content
     * @param string $type - Type of rendering value
     * @param array $field - Field details
     * 
     * @return string - Fully rendered value
     */
    public function render($value, $field = array(), $convert_shortcodes = true) {

        $html = false;
        if (isset($field['type']) && $field['type'] == 'e2pdf-html') {
            $html = true;
        }

        $value = $this->render_shortcodes($value, $field);
        $value = $this->strip_shortcodes($value);
        $value = $this->convert_shortcodes($value, $convert_shortcodes, $html);

        if (isset($field['type']) && $field['type'] === 'e2pdf-checkbox' && isset($field['properties']['option'])) {
            $option = $this->render($field['properties']['option']);
            $options = explode(', ', $value);
            $option_options = explode(', ', $option);
            if (is_array($options) && is_array($option_options) && !array_diff($option_options, $options)) {
                return $option;
            } else {
                return "";
            }
        }

        return $value;
    }

    /**
     * Load actions for this extension
     */
    public function load_actions() {
        add_action('gform_after_email', array($this, 'action_gform_after_email'), 30, 1);
    }

    /**
     * Load filters for this extension
     */
    public function load_filters() {
        add_filter('gform_confirmation', array($this, 'filter_gform_confirmation'), 30, 4);
        add_filter('gform_notification', array($this, 'filter_gform_notification'), 30, 3);
    }

    /**
     * Render shortcodes which available in this extension
     * 
     * @param string $value - Content
     * @param string $type - Type of rendering value
     * @param array $field - Field details
     * 
     * @return string - Value with rendered shortcodes
     */
    public function render_shortcodes($value, $field = array()) {

        $dataset = $this->get('dataset');
        $item = $this->get('item');
        $user_id = $this->get('user_id');
        $args = $this->get('args');
        $template_id = $this->get('template_id') ? $this->get('template_id') : '0';
        $element_id = isset($field['element_id']) ? $field['element_id'] : '0';

        $form = false;
        $entry = false;

        if ($this->verify()) {

            $args = apply_filters('e2pdf_extension_render_shortcodes_args', $args, $element_id, $template_id, $item, $dataset);

            $form = GFFormsModel::get_form_meta($item);
            $entry = GFFormsModel::get_entry($dataset);

            if (false !== strpos($value, '[')) {

                $shortcode_tags = array(
                    'e2pdf-user',
                    'e2pdf-arg'
                );
                preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $value, $matches);
                $tagnames = array_intersect($shortcode_tags, $matches[1]);

                if (!empty($tagnames)) {

                    $pattern = $this->helper->load('shortcode')->get_shortcode_regex($tagnames);

                    preg_match_all("/$pattern/", $value, $shortcodes);

                    foreach ($shortcodes[0] as $key => $shortcode_value) {
                        $shortcode = array();
                        $shortcode[1] = $shortcodes[1][$key];
                        $shortcode[2] = $shortcodes[2][$key];
                        $shortcode[3] = $shortcodes[3][$key];
                        $shortcode[4] = $shortcodes[4][$key];
                        $shortcode[5] = $shortcodes[5][$key];
                        $shortcode[6] = $shortcodes[6][$key];

                        $atts = shortcode_parse_atts($shortcode[3]);

                        if ($shortcode['2'] == 'e2pdf-user') {
                            if (!isset($atts['id']) && $user_id) {
                                $shortcode[3] .= " id=\"" . $user_id . "\"";
                                $value = str_replace($shortcode_value, "[" . $shortcode['2'] . $shortcode['3'] . "]", $value);
                            }
                        } elseif ($shortcode['2'] == 'e2pdf-arg') {
                            if (isset($atts['key']) && isset($args[$atts['key']])) {
                                $sub_value = $this->strip_shortcodes($args[$atts['key']]);
                                $value = str_replace($shortcode_value, $sub_value, $value);
                            } else {
                                $value = str_replace($shortcode_value, '', $value);
                            }
                        }
                    }
                }

                $shortcode_tags = array(
                    'e2pdf-format-number',
                    'e2pdf-format-date',
                    'e2pdf-format-output',
                );
                $shortcode_tags = apply_filters('e2pdf_extension_render_shortcodes_tags', $shortcode_tags);
                preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $value, $matches);
                $tagnames = array_intersect($shortcode_tags, $matches[1]);

                if (!empty($tagnames)) {

                    $pattern = $this->helper->load('shortcode')->get_shortcode_regex($tagnames);

                    preg_match_all("/$pattern/", $value, $shortcodes);
                    foreach ($shortcodes[0] as $key => $shortcode_value) {
                        $shortcode = array();
                        $shortcode[1] = $shortcodes[1][$key];
                        $shortcode[2] = $shortcodes[2][$key];
                        $shortcode[3] = $shortcodes[3][$key];
                        $shortcode[4] = $shortcodes[4][$key];
                        $shortcode[5] = $shortcodes[5][$key];
                        $shortcode[6] = $shortcodes[6][$key];

                        if (!$shortcode['5']) {
                            $sub_value = '';
                        } elseif (isset($field['type']) && ($field['type'] === 'e2pdf-image' || $field['type'] === 'e2pdf-signature')) {
                            $sub_value = $this->render($shortcode['5'], array(), false);
                        } else {
                            $sub_value = $this->render($shortcode['5'], $field, false);
                        }
                        $value = str_replace($shortcode_value, "[" . $shortcode['2'] . $shortcode['3'] . "]" . $sub_value . "[/" . $shortcode['2'] . "]", $value);
                    }
                }
            }

            $value = do_shortcode($value);

            if (class_exists('GFCommon')) {
                add_filter('gform_merge_tag_filter', array($this, 'filter_gform_merge_tag_filter'), 30, 5);
                $value = GFCommon::replace_variables($value, $form, $entry, false, false, false, 'text');
                remove_filter('gform_merge_tag_filter', array($this, 'filter_gform_merge_tag_filter'), 30, 5);
            }

            if (isset($field['type']) && ($field['type'] === 'e2pdf-image' || $field['type'] === 'e2pdf-signature')) {
                $esig = isset($field['properties']['esig']) && $field['properties']['esig'] ? true : false;
                if ($esig) {
                    //process e-signature
                    $value = "";
                } else {

                    $value = $this->helper->load('properties')->apply($field, $value);

                    if (!$this->helper->load('image')->get_image($value)) {
                        $value = $this->strip_shortcodes($value);
                        if (
                                $value &&
                                trim($value) != "" &&
                                extension_loaded('gd') &&
                                function_exists('imagettftext')
                        ) {
                            if (isset($field['properties']['text_color']) && $field['properties']['text_color']) {
                                $penColour = $this->helper->load('convert')->to_hex_color($field['properties']['text_color']);
                            } else {
                                $penColour = array(0x14, 0x53, 0x94);
                            }

                            $default_options = array(
                                'imageSize' => array(isset($field['width']) ? $field['width'] : '400', isset($field['height']) ? $field['height'] : '150'),
                                'bgColour' => 'transparent',
                                'penColour' => $penColour
                            );

                            $options = array();
                            $options = apply_filters('e2pdf_image_sig_output_options', $options, $element_id, $template_id);
                            $options = array_merge($default_options, $options);

                            $model_e2pdf_font = new Model_E2pdf_Font();

                            $font = false;
                            if (isset($field['properties']['text_font']) && $field['properties']['text_font']) {
                                $font = $model_e2pdf_font->get_font_path($field['properties']['text_font']);
                            }

                            if (!$font) {
                                $font = $model_e2pdf_font->get_font_path('Noto Sans');
                            }

                            $size = 150;
                            if (isset($field['properties']['text_font_size']) && $field['properties']['text_font_size']) {
                                $size = $field['properties']['text_font_size'];
                            }

                            $model_e2pdf_signature = new Model_E2pdf_Signature();
                            $value = $model_e2pdf_signature->ttf_signature($value, $size, $font, $options);
                        } else {
                            $value = "";
                        }
                    }
                }
            } else {

                $value = $this->convert_shortcodes($value);
                $value = $this->helper->load('properties')->apply($field, $value);
            }
        }

        $value = apply_filters('e2pdf_extension_render_shortcodes_value', $value, $element_id, $template_id, $item, $dataset);

        return $value;
    }

    /**
     * Strip unused shortcodes
     * 
     * @param string $value - Content
     * 
     * @return string - Value with removed unused shortcodes
     */
    public function strip_shortcodes($value) {
        $value = preg_replace('~(?:\[/?)[^/\]]+/?\]~s', "", $value);
        $value = preg_replace('~(?:{/?)[^/}]+/?}~s', "", $value);
        return $value;
    }

    /**
     * Convert "shortcodes" inside value string
     * 
     * @param string $value - Value string
     * @param bool $to - Convert From/To
     * 
     * @return string - Converted value
     */
    public function convert_shortcodes($value, $to = false, $html = false) {
        if ($value) {
            if ($to) {
                $search = array('&#91;', '&#93;', '&#091;', '&#093;');
                $replace = array('[', ']', '[', ']');
                $value = str_replace($search, $replace, $value);
                if (!$html) {
                    $value = wp_specialchars_decode($value, ENT_QUOTES);
                }
            } else {
                $search = array('[', ']', '&#091;', '&#093;');
                $replace = array('&#91;', '&#93;', '&#91;', '&#93;');
                $value = str_replace($search, $replace, $value);
            }
        }
        return $value;
    }

    /**
     * Auto Generate of Template for this extension
     * 
     * @return array - List of elements
     */
    public function auto() {

        $item = $this->get('item');

        $response = array();
        $elements = array();
        $merged_tags = array();
        $form = false;

        if (class_exists('GFFormsModel')) {
            $form = GFFormsModel::get_form_meta($item);
        }

        if ($form) {
            if (class_exists('GFCommon')) {
                foreach ($form['fields'] as $field) {
                    $tags = GFCommon::get_field_merge_tags($field);
                    foreach ($tags as $tag) {
                        if (isset($tag['tag'])) {
                            if ($field->type == 'list') {
                                $field_id = preg_replace('/\{(?:.*)\:(.*)\:\}/', '${1}', $tag['tag']);
                            } else {
                                $field_id = preg_replace('/\{(?:.*)\:(.*)\}/', '${1}', $tag['tag']);
                            }
                            if ($field_id) {
                                $merged_tags[$field_id] = $tag['tag'];
                            }
                        }
                    }
                }
            }

            foreach ($form['fields'] as $field) {
                if ($field->type == 'product' || $field->type == 'shipping') {
                    if ($field->inputType == 'select') {
                        $field->type = 'select';
                    } elseif ($field->inputType == 'radio') {
                        $field->type = 'radio';
                    } elseif ($field->inputType == 'price') {
                        $field->type = 'text';
                    }
                }

                switch ($field->type) {
                    case 'text':
                    case 'number':
                    case 'date':
                    case 'time':
                    case 'phone':
                    case 'website':
                    case 'email':
                    case 'fileupload':
                    case 'post_title':
                    case 'post_excerpt':
                    case 'post_tags':
                    case 'post_custom_field':
                    case 'quantity':
                    case 'shipping':
                    case 'total':
                        $value = isset($merged_tags[$field->id]) ? $merged_tags[$field->id] : '';
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => $field->label,
                                'pass' => $field->enablePasswordInput ? '1' : '0'
                            )
                        ));

                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-input',
                            'properties' => array(
                                'top' => '5',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => $value,
                            )
                        ));
                        break;
                    case 'list':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => $field->label,
                            )
                        ));

                        if ($field->enableColumns) {

                            $width = number_format(floor((100 / count($field->choices)) * 100) / 100, 2);

                            foreach ($field->choices as $key => $choice) {

                                $field_id = (int) $key + 1;
                                $value = isset($merged_tags[$field->id]) ? $merged_tags[$field->id] : '';
                                if (substr($value, -1) == '}') {
                                    $value = substr($value, 0, -1) . '1_' . $field_id . '}';
                                }

                                $float = true;
                                if ($key == '0') {
                                    $float = false;
                                }
                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-html',
                                    'block' => true,
                                    'float' => $float,
                                    'properties' => array(
                                        'top' => '5',
                                        'left' => '20',
                                        'right' => '20',
                                        'width' => $width . '%',
                                        'height' => 'auto',
                                        'value' => $choice['text'],
                                    )
                                ));

                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-input',
                                    'properties' => array(
                                        'top' => '5',
                                        'width' => '100%',
                                        'height' => 'auto',
                                        'value' => $value,
                                    )
                                ));
                            }
                        } else {
                            $value = isset($merged_tags[$field->id]) ? $merged_tags[$field->id] : '';
                            if (substr($value, -1) == '}') {
                                $value = substr($value, 0, -1) . '1}';
                            }

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-input',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => $value,
                                )
                            ));
                        }
                        break;
                    case 'textarea':
                    case 'post_content':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => $field->label,
                            )
                        ));

                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-textarea',
                            'properties' => array(
                                'top' => '5',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => isset($merged_tags[$field->id]) ? $merged_tags[$field->id] : '',
                            )
                        ));
                        break;
                    case 'select':
                    case 'multiselect':
                    case 'post_category':
                    case 'option':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => $field->label,
                            )
                        ));

                        $options_tmp = array();
                        if (isset($field->choices) && is_array($field->choices)) {
                            foreach ($field->choices as $opt_key => $option) {
                                $options_tmp[] = isset($option['value']) ? $option['value'] : '';
                            }
                        }

                        $value = isset($merged_tags[$field->id]) ? $merged_tags[$field->id] : '';
                        if ($field->enableChoiceValue && $value) {
                            if (substr($value, -1) == '}') {
                                $value = substr($value, 0, -1) . ':value}';
                            }
                        }

                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-select',
                            'properties' => array(
                                'top' => '5',
                                'width' => '100%',
                                'height' => 'auto',
                                'options' => implode("\n", $options_tmp),
                                'value' => $value,
                                'height' => $field->type == 'multiselect' ? '80' : 'auto',
                                'multiline' => $field->type == 'multiselect' ? '1' : '0',
                            )
                        ));
                        break;
                    case 'checkbox':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => $field->label,
                            )
                        ));

                        if (isset($field->choices) && is_array($field->choices)) {

                            $value = isset($merged_tags[$field->id]) ? $merged_tags[$field->id] : '';
                            if ($field->enableChoiceValue && $value) {
                                if (substr($value, -1) == '}') {
                                    $value = substr($value, 0, -1) . ':value}';
                                }
                            }

                            foreach ($field->choices as $opt_key => $option) {
                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-checkbox',
                                    'properties' => array(
                                        'top' => '5',
                                        'width' => 'auto',
                                        'height' => 'auto',
                                        'value' => $value,
                                        'option' => isset($option['value']) ? $option['value'] : ''
                                    )
                                ));
                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-html',
                                    'float' => true,
                                    'properties' => array(
                                        'left' => '5',
                                        'width' => '100%',
                                        'height' => 'auto',
                                        'value' => isset($option['text']) ? $option['text'] : ''
                                    )
                                ));
                            }
                        }
                        break;
                    case 'radio':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => $field->label,
                            )
                        ));

                        if (isset($field->choices) && is_array($field->choices)) {

                            $value = isset($merged_tags[$field->id]) ? $merged_tags[$field->id] : '';
                            if ($field->enableChoiceValue && $value) {
                                if (substr($value, -1) == '}') {
                                    $value = substr($value, 0, -1) . ':value}';
                                }
                            }

                            foreach ($field->choices as $opt_key => $option) {
                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-radio',
                                    'properties' => array(
                                        'top' => '5',
                                        'width' => 'auto',
                                        'height' => 'auto',
                                        'value' => $value,
                                        'option' => isset($option['value']) ? $option['value'] : '',
                                        'group' => $value
                                    )
                                ));
                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-html',
                                    'float' => true,
                                    'properties' => array(
                                        'left' => '5',
                                        'width' => '100%',
                                        'height' => 'auto',
                                        'value' => isset($option['text']) ? $option['text'] : ''
                                    )
                                ));
                            }
                        }
                        break;
                    case 'name':
                        foreach ($field['inputs'] as $key => $input) {
                            if (isset($input->isHidden) && $input->isHidden) {
                                unset($field['inputs'][$key]);
                            }
                        }

                        $width = '100%';
                        if (count($field['inputs']) == '3') {
                            $width = '33.3%';
                        } else {
                            $width = 100 / count($field['inputs']) . '%';
                        }

                        foreach ($field['inputs'] as $key => $sub_field) {

                            $elements[] = $this->auto_field($sub_field, array(
                                'type' => 'e2pdf-html',
                                'block' => true,
                                'float' => $key == '0' ? false : true,
                                'properties' => array(
                                    'top' => '20',
                                    'left' => '20',
                                    'right' => '20',
                                    'width' => $width,
                                    'height' => 'auto',
                                    'value' => isset($sub_field['label']) && $sub_field['label'] ? $sub_field['label'] : '',
                                )
                            ));

                            if (isset($sub_field['choices']) && is_array($sub_field['choices'])) {

                                $options_tmp = array();
                                foreach ($sub_field['choices'] as $opt_key => $option) {
                                    $options_tmp[] = isset($option['value']) ? $option['value'] : '';
                                }

                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-select',
                                    'properties' => array(
                                        'top' => '5',
                                        'width' => '100%',
                                        'height' => 'auto',
                                        'options' => implode("\n", $options_tmp),
                                        'value' => isset($merged_tags[$sub_field['id']]) ? $merged_tags[$sub_field['id']] : '',
                                        'height' => 'auto',
                                        'multiline' => '0',
                                    )
                                ));
                            } else {
                                $elements[] = $this->auto_field($sub_field, array(
                                    'type' => 'e2pdf-input',
                                    'properties' => array(
                                        'top' => '5',
                                        'width' => '100%',
                                        'height' => 'auto',
                                        'value' => isset($merged_tags[$sub_field['id']]) ? $merged_tags[$sub_field['id']] : '',
                                    )
                                ));
                            }
                        }
                        break;
                    case 'address':
                        $index = 0;
                        foreach ($field['inputs'] as $key => $sub_field) {

                            if (isset($sub_field['isHidden']) && $sub_field['isHidden']) {
                                
                            } else {
                                $elements[] = $this->auto_field($sub_field, array(
                                    'type' => 'e2pdf-html',
                                    'block' => true,
                                    'float' => $index == '0' ? false : true,
                                    'properties' => array(
                                        'top' => '20',
                                        'left' => '20',
                                        'right' => '20',
                                        'width' => $key == '0' || $key == '1' ? '100%' : '50%',
                                        'height' => 'auto',
                                        'value' => isset($sub_field['label']) && $sub_field['label'] ? $sub_field['label'] : '',
                                    )
                                ));

                                $elements[] = $this->auto_field($sub_field, array(
                                    'type' => 'e2pdf-input',
                                    'properties' => array(
                                        'top' => '5',
                                        'width' => '100%',
                                        'height' => 'auto',
                                        'value' => isset($merged_tags[$sub_field['id']]) ? $merged_tags[$sub_field['id']] : '',
                                    )
                                ));
                                $index++;
                            }
                        }
                        break;
                    case 'consent':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => $field->label,
                            )
                        ));

                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-checkbox',
                            'properties' => array(
                                'top' => '5',
                                'width' => 'auto',
                                'height' => 'auto',
                                'value' => isset($merged_tags[$field->id . '.1']) ? $merged_tags[$field->id . '.1'] : '',
                                'option' => '1'
                            )
                        ));
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'float' => true,
                            'properties' => array(
                                'left' => '5',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => $field->checkboxLabel
                            )
                        ));
                        break;
                    case 'post_image':

                        $value = isset($merged_tags[$field->id]) ? $merged_tags[$field->id] : '';

                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => $field->label,
                            )
                        ));

                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-image',
                            'properties' => array(
                                'top' => '5',
                                'width' => '100',
                                'height' => '100',
                                'value' => $value,
                                'dimension' => '1'
                            )
                        ));

                        if ($field->displayTitle) {

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'block' => true,
                                'properties' => array(
                                    'top' => '20',
                                    'left' => '20',
                                    'right' => '20',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => __('Title', 'gravityforms'),
                                )
                            ));

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-input',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => $value && substr($value, -1) == '}' ? substr($value, 0, -1) . ':title}' : '',
                                    'dimension' => '1'
                                )
                            ));
                        }

                        if ($field->displayCaption) {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'block' => true,
                                'properties' => array(
                                    'top' => '20',
                                    'left' => '20',
                                    'right' => '20',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => __('Caption', 'gravityforms'),
                                )
                            ));

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-input',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => $value && substr($value, -1) == '}' ? substr($value, 0, -1) . ':caption}' : '',
                                    'dimension' => '1'
                                )
                            ));
                        }

                        if ($field->displayDescription) {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'block' => true,
                                'properties' => array(
                                    'top' => '20',
                                    'left' => '20',
                                    'right' => '20',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => __('Description', 'gravityforms'),
                                )
                            ));

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-input',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => $value && substr($value, -1) == '}' ? substr($value, 0, -1) . ':description}' : '',
                                    'dimension' => '1'
                                )
                            ));
                        }

                        break;
                    case 'html':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => $field->content,
                            )
                        ));
                        break;
                    case 'product':

                        if ($field->inputType != 'hiddenproduct') {

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'block' => true,
                                'properties' => array(
                                    'top' => '20',
                                    'left' => '20',
                                    'right' => '20',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => $field->label,
                                )
                            ));

                            if ($field->inputType == 'singleproduct' || $field->inputType == 'calculation') {

                                if ($field->disableQuantity) {
                                    $width = '50%';
                                } else {
                                    $width = '33.3%';
                                }

                                foreach ($field['inputs'] as $key => $sub_field) {

                                    if ($field->disableQuantity && $key == '2') {
                                        
                                    } else {
                                        $elements[] = $this->auto_field($sub_field, array(
                                            'type' => 'e2pdf-html',
                                            'block' => true,
                                            'float' => $key == '0' ? false : true,
                                            'properties' => array(
                                                'top' => '5',
                                                'left' => '20',
                                                'right' => '20',
                                                'width' => $width,
                                                'height' => 'auto',
                                                'value' => isset($sub_field['label']) && $sub_field['label'] ? $sub_field['label'] : '',
                                            )
                                        ));

                                        $elements[] = $this->auto_field($sub_field, array(
                                            'type' => 'e2pdf-input',
                                            'properties' => array(
                                                'top' => '5',
                                                'width' => '100%',
                                                'height' => 'auto',
                                                'value' => isset($merged_tags[$sub_field['id']]) ? $merged_tags[$sub_field['id']] : '',
                                            )
                                        ));
                                    }
                                }
                            }
                        }
                        break;

                    case 'signature':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => $field->label,
                            )
                        ));

                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-signature',
                            'properties' => array(
                                'top' => '5',
                                'width' => '100%',
                                'height' => '150',
                                'scale' => '1',
                                'dimension' => '1',
                                'value' => isset($merged_tags[$field->id]) ? $merged_tags[$field->id] : ''
                            )
                        ));
                        break;
                    default:
                        //non-supported fields
                        break;
                }
            }
        }

        $response['page'] = array(
            'bottom' => '20',
            'top' => '20',
            'left' => '20',
            'right' => '20'
        );

        $response['elements'] = $elements;
        return $response;
    }

    public function auto_field($field = false, $element = array()) {

        if (!$field) {
            return false;
        }

        if (!isset($element['block'])) {
            $element['block'] = false;
        }

        if (!isset($element['float'])) {
            $element['float'] = false;
        }

        return $element;
    }

    /**
     * Verify if item and dataset exists
     * 
     * @return bool - item and dataset exists
     */
    public function verify() {
        $item = $this->get('item');
        $dataset = $this->get('dataset');

        if ($item && $dataset && class_exists('GFFormsModel')) {
            $entry = GFFormsModel::get_entry($dataset);
            if ($entry && isset($entry['form_id']) && $entry['form_id'] == $item) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create Form based on uploaded PDF
     * 
     * @param object $template - Template Object to work with
     * @param array $data - Settings to create labels/shortcodes 
     * 
     * @return object - Mapped Template Object
     */
    public function auto_form($template, $data = array()) {

        if ($template->get('ID')) {

            $auto_form_label = isset($data['auto_form_label']) && $data['auto_form_label'] ? $data['auto_form_label'] : false;
            $auto_form_shortcode = isset($data['auto_form_shortcode']) ? true : false;

            if (class_exists('GFAPI')) {
                $confirmation_id = uniqid();
                $form = array(
                    'title' => $template->get('title'),
                    'fields' => array(
                    ),
                    'confirmations' => array()
                );

                $form['confirmations'][$confirmation_id] = array(
                    'id' => $confirmation_id,
                    'name' => __('Default Confirmation', 'gravityforms'),
                    'isDefault' => true,
                    'type' => 'message',
                    'message' => sprintf(__('Thanks for contacting us! We will get in touch with you shortly. [e2pdf-download id="%s"]', 'gravityforms'), $template->get('ID')),
                    'url' => '',
                    'pageId' => '',
                    'queryString' => '',
                );

                $pages = $template->get('pages');
                $checkboxes = array();
                $radios = array();

                $field_id = 1;

                foreach ($pages as $page_key => $page) {
                    if (isset($page['elements']) && !empty($page['elements'])) {
                        foreach ($page['elements'] as $element_key => $element) {
                            $type = false;
                            $labels = array();
                            $label = '';

                            if ($auto_form_shortcode) {
                                $labels[] = $field_id;
                            }
                            if ($auto_form_label && $auto_form_label == 'value' && isset($element['value']) && $element['value']) {
                                $labels[] = $element['value'];
                            } elseif ($auto_form_label && $auto_form_label == 'name' && isset($element['name']) && $element['name']) {
                                $labels[] = $element['name'];
                            }

                            if ($element['type'] == 'e2pdf-input' || $element['type'] == 'e2pdf-signature') {
                                $type = 'text';
                                $label = !empty($labels) ? implode(' ', $labels) : __('Text', 'e2pdf');
                            } elseif ($element['type'] == 'e2pdf-textarea') {

                                $type = 'textarea';
                                $label = !empty($labels) ? implode(' ', $labels) : __('Textarea', 'e2pdf');
                            } elseif ($element['type'] == 'e2pdf-select') {
                                $type = 'select';
                                $label = !empty($labels) ? implode(' ', $labels) : __('Select', 'e2pdf');

                                $choices = array();
                                $field_options = array();

                                if (isset($element['properties']['options'])) {
                                    $field_options = explode("\n", $element['properties']['options']);
                                    foreach ($field_options as $option) {
                                        $choices[] = array(
                                            'text' => $option,
                                            'value' => $option
                                        );
                                    }
                                }
                            } elseif ($element['type'] == 'e2pdf-checkbox') {
                                $field_key = array_search($element['name'], array_column($checkboxes, 'name'));
                                if ($field_key !== false) {
                                    $checkbox = array_search($checkboxes[$field_key]['id'], array_column($form['fields'], 'id'));
                                    if ($checkbox !== false) {
                                        $form['fields'][$checkbox]['choices'][] = array(
                                            'text' => $element['properties']['option'],
                                            'value' => $element['properties']['option'],
                                        );

                                        $num = count($form['fields'][$checkbox]['inputs']) + 1;
                                        $form['fields'][$checkbox]['inputs'][] = array(
                                            'id' => $field_id . '.' . $num,
                                            'label' => $element['properties']['option'],
                                        );
                                        $pages[$page_key]['elements'][$element_key]['value'] = '{' . $form['fields'][$checkbox]['label'] . ':' . $form['fields'][$checkbox]['id'] . '}';
                                    }
                                } else {
                                    $label = !empty($labels) ? implode(' ', $labels) : __('Checkbox', 'e2pdf');
                                    $type = 'checkbox';
                                    $checkboxes[] = array(
                                        'id' => $field_id,
                                        'name' => $element['name'],
                                    );
                                    $choices = array(
                                        array(
                                            'text' => $element['properties']['option'],
                                            'value' => $element['properties']['option'],
                                        )
                                    );

                                    $inputs = array(
                                        array(
                                            'id' => $field_id . '.1',
                                            'label' => $element['properties']['option'],
                                        )
                                    );
                                }
                            } elseif ($element['type'] == 'e2pdf-radio') {
                                if (isset($element['properties']['group']) && $element['properties']['group']) {
                                    $element['name'] = $element['properties']['group'];
                                } else {
                                    $element['name'] = $element['element_id'];
                                }

                                $field_key = array_search($element['name'], array_column($radios, 'name'));
                                if ($field_key !== false) {
                                    $radio = array_search($radios[$field_key]['id'], array_column($form['fields'], 'id'));
                                    if ($radio !== false) {
                                        $form['fields'][$radio]['choices'][] = array(
                                            'text' => $element['properties']['option'],
                                            'value' => $element['properties']['option'],
                                        );

                                        $pages[$page_key]['elements'][$element_key]['value'] = '{' . $form['fields'][$checkbox]['label'] . ':' . $form['fields'][$checkbox]['id'] . '}';
                                    }
                                } else {
                                    $label = !empty($labels) ? implode(' ', $labels) : __('Radio', 'e2pdf');
                                    $type = 'radio';
                                    $radios[] = array(
                                        'id' => $field_id,
                                        'name' => $element['name'],
                                    );
                                    $choices = array(
                                        array(
                                            'text' => $element['properties']['option'],
                                            'value' => $element['properties']['option'],
                                        )
                                    );
                                }
                            }

                            if ($type) {
                                $field = array(
                                    'id' => $field_id,
                                    'type' => $type,
                                    'label' => $label,
                                );
                                if ($type == 'select' || $type == 'radio' || $type == 'checkbox') {
                                    $field['choices'] = $choices;
                                }

                                if ($type == 'checkbox') {
                                    $field['inputs'] = $inputs;
                                }

                                $form['fields'][] = $field;
                                $pages[$page_key]['elements'][$element_key]['value'] = '{' . $label . ':' . $field_id . '}';

                                if (isset($element['properties']['esig'])) {
                                    unset($pages[$page_key]['elements'][$element_key]['properties']['esig']);
                                }
                                $field_id++;
                            }
                        }
                    }
                }

                $item = GFAPI::add_form($form);
                if (!is_wp_error($item)) {
                    $template->set('item', $item);
                    $template->set('pages', $pages);
                }
            }
        }

        return $template;
    }

    /**
     * Init Visual Mapper data
     * 
     * @return bool|string - HTML data source for Visual Mapper
     */
    public function visual_mapper() {

        $item = $this->get('item');
        $html = '';
        $source = '';

        if ($item && function_exists('gravity_form')) {

            $form = false;
            if (class_exists('GFFormsModel')) {
                $form = GFFormsModel::get_form_meta($item);
            }

            if (!$form) {
                return __('Form could not be found', 'e2pdf');
            }

            add_filter('gform_pre_render', array($this, 'filter_gform_pre_render'), 30, 1);
            $source = gravity_form($item, true, true, false, null, false, 0, false);
            remove_filter('gform_pre_render', array($this, 'filter_gform_pre_render'), 30);

            if ($source) {
                libxml_use_internal_errors(true);
                $dom = new DOMDocument();
                if (function_exists('mb_convert_encoding')) {
                    $html = $dom->loadHTML(mb_convert_encoding($source, 'HTML-ENTITIES', 'UTF-8'));
                } else {
                    $html = $dom->loadHTML('<?xml encoding="UTF-8">' . $source);
                }
                libxml_clear_errors();
            }

            if (!$source) {
                return __('Form source is empty', 'e2pdf');
            } elseif (!$html) {
                return __('Form could not be parsed due incorrect HTML', 'e2pdf');
            } else {

                $xml = $this->helper->load('xml');
                $xml->set('dom', $dom);
                $xpath = new DomXPath($dom);

                $remove_by_class = array(
                    'gf_progressbar_wrapper',
                    'gform_previous_button',
                    'gform_next_button',
                    'gform_button button'
                );
                foreach ($remove_by_class as $key => $class) {
                    $elements = $xpath->query("//*[contains(@class, '{$class}')]");
                    foreach ($elements as $element) {
                        $element->parentNode->removeChild($element);
                    }
                }

                $remove_by_tag = array(
                    'script'
                );
                foreach ($remove_by_tag as $key => $tag) {
                    $elements = $xpath->query("//{$tag}");
                    foreach ($elements as $element) {
                        $element->parentNode->removeChild($element);
                    }
                }

                //replace time fields
                $elements = $xpath->query("//*[contains(@class, 'gfield_time_hour')]");
                foreach ($elements as $element) {

                    $sub_elements = $xpath->query(".//*[self::input or self::select]", $element->parentNode);
                    foreach ($sub_elements as $sub_element) {
                        $sub_element = $xml->set_node_value($sub_element, 'class', $xml->get_node_value($sub_element, 'class') . ' e2pdf-no-vm');
                    }

                    $element = $xml->set_node_value($element, 'class', $xml->get_node_value($element->parentNode, 'class') . ' e2pdf-vm-field-wrapper', true);

                    $input = $xpath->query("./input", $element)->item(0);
                    $field = $dom->createElement('input');

                    $field = $xml->set_node_value($field, 'type', 'text');
                    $field = $xml->set_node_value($field, 'class', 'e2pdf-vm-field');
                    $field = $xml->set_node_value($field, 'name', $xml->get_node_value($input, 'name'));

                    $element->parentNode->appendChild($field);
                }

                //replace multiupload field
                $elements = $xpath->query("//*[contains(@class, 'gform_drop_area')]");
                foreach ($elements as $element) {

                    $element = $xml->set_node_value($element, 'class', $xml->get_node_value($element, 'class') . ' e2pdf-vm-field-wrapper', true);

                    $field_id = preg_replace('/(?:.*)_([\d]+)/', '${1}', $xml->get_node_value($element, 'id'));

                    $field = $dom->createElement('input');
                    $field = $xml->set_node_value($field, 'type', 'text');
                    $field = $xml->set_node_value($field, 'class', 'e2pdf-vm-field');
                    $field = $xml->set_node_value($field, 'name', 'input_' . $field_id);

                    $element->appendChild($field);
                }

                //replace single product
                $elements = $xpath->query("//*[contains(@class, 'ginput_container_singleproduct') or contains(@class, 'ginput_container_product_calculation') or contains(@class, 'ginput_container_singleshipping') or contains(@class, 'ginput_container_total') or contains(@class, 'gfield_signature_container')]");
                foreach ($elements as $element) {
                    $spans = $xpath->query(".//span", $element);
                    foreach ($spans as $key => $sub_element) {
                        $sub_element->parentNode->removeChild($sub_element);
                    }

                    $inputs = $xpath->query(".//input", $element);
                    foreach ($inputs as $key => $sub_element) {
                        $sub_element = $xml->set_node_value($sub_element, 'type', 'text');
                        $sub_element = $xml->set_node_value($sub_element, 'class', '');
                    }
                }

                $merged_tags = array();
                if (class_exists('GFCommon')) {
                    foreach ($form['fields'] as $field) {
                        $tags = GFCommon::get_field_merge_tags($field);
                        foreach ($tags as $tag) {
                            if (isset($tag['tag'])) {
                                if ($field->type == 'list') {
                                    $field_id = preg_replace('/\{(?:.*)\:(.*)\:\}/', '${1}', $tag['tag']);
                                } else {
                                    $field_id = preg_replace('/\{(?:.*)\:(.*)\}/', '${1}', $tag['tag']);
                                }
                                if ($field_id) {
                                    $merged_tags[$field_id] = $tag['tag'];
                                }
                            }
                        }
                    }
                }

                $fields = array();
                foreach ($form['fields'] as $field) {
                    $fields[$field->id] = $field;
                }

                $replace_by_types = array(
                    "//input",
                    "//textarea",
                    "//select"
                );

                foreach ($replace_by_types as $replace_by_type) {
                    $inputs = $xpath->query($replace_by_type);
                    foreach ($inputs as $element) {
                        if ($element->attributes->getNamedItem("name")) {

                            $field_id = false;
                            $field = false;

                            $sub_field_id = preg_replace('/input_([^\[]+)(?:.*)/', '${1}', $xml->get_node_value($element, 'name'));

                            if ($sub_field_id) {
                                if (substr($sub_field_id, -6) == '_valid') {
                                    $field_id = preg_replace('/(?:.*)\_([\d]+)\_valid/', '${1}', $sub_field_id);
                                } else {
                                    $field_id = preg_replace('/([\d]+)\.(?:.*)/', '${1}', $sub_field_id);
                                }
                                if (isset($fields[$field_id])) {
                                    $field = $fields[$field_id];
                                }
                            }

                            if ($field) {
                                if (
                                        $field->type == 'name' ||
                                        $field->type == 'address' ||
                                        (
                                        $field->type == 'product' &&
                                        $field->inputType &&
                                        ($field->inputType == 'singleproduct' || $field->inputType == 'calculation')
                                        )
                                ) {
                                    $value = $merged_tags[$sub_field_id];
                                    $element = $xml->set_node_value($element, 'name', $value);
                                } elseif ($field->type == 'consent') {
                                    $value = isset($merged_tags[$field_id . '.1']) ? $merged_tags[$field_id . '.1'] : '';
                                    $element = $xml->set_node_value($element, 'name', $value);
                                } else {
                                    if (isset($merged_tags[$field_id])) {
                                        $value = $merged_tags[$field_id];
                                        if (substr($value, -1) == '}') {
                                            if (false !== strpos($xml->get_node_value($element->parentNode, 'class'), 'ginput_post_image_')) {
                                                if (false !== strpos($xml->get_node_value($element->parentNode, 'class'), 'ginput_post_image_title')) {
                                                    $value = substr($value, 0, -1) . ':title}';
                                                } elseif (false !== strpos($xml->get_node_value($element->parentNode, 'class'), 'ginput_post_image_caption')) {
                                                    $value = substr($value, 0, -1) . ':caption}';
                                                } elseif (false !== strpos($xml->get_node_value($element->parentNode, 'class'), 'ginput_post_image_description')) {
                                                    $value = substr($value, 0, -1) . ':description}';
                                                }
                                            } elseif ($field->type == 'list') {
                                                if ($field->enableColumns) {
                                                    $parent_class = $xml->get_node_value($element->parentNode, 'class');
                                                    $index = 1;
                                                    $td = $xpath->query("./td[contains(@class, '{$parent_class}')]/preceding-sibling::td", $element->parentNode->parentNode);
                                                    if ($td && isset($td->length)) {
                                                        $index = (int) $td->length + 1;
                                                    }
                                                    $value = substr($value, 0, -1) . '1_' . $index . '}';
                                                } else {
                                                    $value = substr($value, 0, -1) . '1}';
                                                }
                                            } elseif ($field->enableChoiceValue && $value) {
                                                $value = substr($value, 0, -1) . ':value}';
                                            }
                                        }
                                        $element = $xml->set_node_value($element, 'name', $value);
                                    }
                                }
                            }
                        }
                    }
                }

                return $dom->saveHTML();
            }
        }

        return false;
    }

    /**
     * Convert Field name to Value
     * @since 0.01.34
     * 
     * @param string $name - Field name
     * 
     * @return bool|string - Converted value or false
     */
    public function auto_map($name = false) {

        $item = $this->get('item');

        $merged_tags = array();
        $form = false;
        $sub_field_id = false;

        if ($name) {
            $sub_field_id = preg_replace('/\{(?:.*)\:(.*)\:\}/', '${1}', $name);
        }

        if ($sub_field_id) {
            if (class_exists('GFFormsModel')) {
                $form = GFFormsModel::get_form_meta($item);
            }

            if ($form) {
                if (class_exists('GFCommon')) {
                    foreach ($form['fields'] as $field) {
                        $tags = GFCommon::get_field_merge_tags($field);
                        foreach ($tags as $tag) {
                            if (isset($tag['tag'])) {
                                if ($field->type == 'list') {
                                    $field_id = preg_replace('/\{(?:.*)\:(.*)\:\}/', '${1}', $tag['tag']);
                                } else {
                                    $field_id = preg_replace('/\{(?:.*)\:(.*)\}/', '${1}', $tag['tag']);
                                }
                                if ($field_id) {
                                    $merged_tags[$field_id] = $tag['tag'];
                                }
                            }
                        }
                    }
                }

                if (isset($merged_tags[$sub_field_id])) {
                    return $merged_tags[$sub_field_id];
                }
            }
        }

        return false;
    }

    /**
     * Load additional shortcodes for this extension
     */
    public function load_shortcodes() {
        
    }

    public function filter_gform_confirmation($content, $form, $dataset, $ajax) {

        if (false === strpos($content, '[')) {
            return $content;
        }

        $shortcode_tags = array(
            'e2pdf-download',
            'e2pdf-save',
            'e2pdf-view',
            'e2pdf-adobesign'
        );

        preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches);
        $tagnames = array_intersect($shortcode_tags, $matches[1]);

        if (!empty($tagnames)) {

            $pattern = $this->helper->load('shortcode')->get_shortcode_regex($tagnames);

            preg_match_all("/$pattern/", $content, $shortcodes);
            foreach ($shortcodes[0] as $key => $shortcode_value) {

                $shortcode = array();
                $shortcode[1] = $shortcodes[1][$key];
                $shortcode[2] = $shortcodes[2][$key];
                $shortcode[3] = $shortcodes[3][$key];
                $shortcode[4] = $shortcodes[4][$key];
                $shortcode[5] = $shortcodes[5][$key];
                $shortcode[6] = $shortcodes[6][$key];

                $atts = shortcode_parse_atts($shortcode[3]);

                if (($shortcode[2] === 'e2pdf-save' && isset($atts['attachment']) && $atts['attachment'] == 'true') || $shortcode[2] === 'e2pdf-attachment') {
                    
                } else {
                    if (!isset($atts['dataset']) && isset($atts['id'])) {
                        $template = new Model_E2pdf_Template();
                        $template->load($atts['id']);
                        if ($template->get('extension') === 'gravity') {
                            $entry_id = $dataset && isset($dataset['id']) ? $dataset['id'] : false;
                            if ($entry_id) {
                                $atts['dataset'] = $entry_id;
                                $shortcode[3] .= " dataset=\"{$entry_id}\"";
                            }
                        }
                    }

                    if (!isset($atts['apply'])) {
                        $shortcode[3] .= " apply=\"true\"";
                    }

                    if (!isset($atts['filter'])) {
                        $shortcode[3] .= " filter=\"true\"";
                    }

                    $new_shortcode = "[" . $shortcode[2] . $shortcode[3] . "]";
                    $content = str_replace($shortcode_value, $new_shortcode, $content);
                }
            }
        }

        return $content;
    }

    public function filter_gform_notification($notification, $form, $dataset) {

        $content = isset($notification['message']) && $notification['message'] ? $notification['message'] : '';

        if (false === strpos($content, '[')) {
            return $notification;
        }

        $shortcode_tags = array(
            'e2pdf-download',
            'e2pdf-save',
            'e2pdf-view',
            'e2pdf-adobesign',
            'e2pdf-attachment'
        );

        preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches);
        $tagnames = array_intersect($shortcode_tags, $matches[1]);

        if (!empty($tagnames)) {

            $pattern = $this->helper->load('shortcode')->get_shortcode_regex($tagnames);

            preg_match_all("/$pattern/", $content, $shortcodes);

            foreach ($shortcodes[0] as $key => $shortcode_value) {

                $shortcode = array();
                $shortcode[1] = $shortcodes[1][$key];
                $shortcode[2] = $shortcodes[2][$key];
                $shortcode[3] = $shortcodes[3][$key];
                $shortcode[4] = $shortcodes[4][$key];
                $shortcode[5] = $shortcodes[5][$key];
                $shortcode[6] = $shortcodes[6][$key];

                $atts = shortcode_parse_atts($shortcode[3]);

                if (!isset($atts['dataset']) && isset($atts['id'])) {
                    $template = new Model_E2pdf_Template();
                    $template->load($atts['id']);
                    if ($template->get('extension') === 'gravity') {
                        $entry_id = $dataset && isset($dataset['id']) ? $dataset['id'] : false;
                        if ($entry_id) {
                            $atts['dataset'] = $entry_id;
                            $shortcode[3] .= " dataset=\"{$entry_id}\"";
                        }
                    }
                }

                if (!isset($atts['apply'])) {
                    $shortcode[3] .= " apply=\"true\"";
                }

                if (!isset($atts['filter'])) {
                    $shortcode[3] .= "  filter=\"true\"";
                }

                $file = false;

                if (($shortcode[2] === 'e2pdf-save' && isset($atts['attachment']) && $atts['attachment'] == 'true') || $shortcode[2] === 'e2pdf-attachment') {

                    if (class_exists('GFCommon')) {
                        $shortcode[3] = GFCommon::replace_variables($shortcode[3], $form, $dataset, false, false, false, 'text');
                    }

                    $file = do_shortcode_tag($shortcode);
                    if ($file) {
                        if ($shortcode[2] != 'e2pdf-save' && !isset($atts['pdf'])) {
                            $this->helper->add('gravity_attachments', $file);
                        }

                        $notification['attachments'] = ( is_array(rgget('attachments', $notification)) ) ? rgget('attachments', $notification) : array();
                        $notification['attachments'][] = $file;
                    }

                    $notification['message'] = str_replace($shortcode_value, '', $notification['message']);
                } else {
                    $new_shortcode = "[" . $shortcode[2] . $shortcode[3] . "]";
                    $notification['message'] = str_replace($shortcode_value, $new_shortcode, $notification['message']);
                }
            }
        }

        return $notification;
    }

    public function filter_gform_pre_render($form) {

        if (isset($form['fields'])) {
            foreach ($form['fields'] as $key => $field) {
                if ($field->pageNumber != '1') {
                    $field->pageNumber = '1';
                    $form['fields'][$key] = $field;
                }
            }
        }

        return $form;
    }

    public function filter_gform_merge_tag_filter($value, $merge_tag, $modifier, $field, $raw_value) {

        if ($field && $value) {
            if ($field->type == 'consent') {
                $mod = explode('.', $merge_tag);
                if (isset($mod[1]) && $mod[1] == '1') {
                    $value = '1';
                }
            } elseif ($field->type == 'list') {

                if ($modifier && $modifier != 'text') {

                    $list_id = false;
                    $field_id = false;

                    if (false !== strpos($modifier, '_')) {
                        $mod = explode('_', $modifier);
                        if (isset($mod[0]) && is_numeric($mod[0])) {
                            $list_id = $mod[0] - 1;
                        }
                        if (isset($mod[1]) && is_numeric($mod[1])) {
                            $field_id = $mod[1] - 1;
                        }
                    } elseif (is_numeric($modifier)) {
                        $list_id = $modifier - 1;
                    }

                    if ($list_id !== false) {
                        $value = '';
                        $list = maybe_unserialize($raw_value);
                        if (is_array($list)) {
                            if (isset($list[$list_id])) {
                                if ($field_id !== false) {
                                    if (is_array($list[$list_id]) && isset(array_values($list[$list_id])[$field_id])) {
                                        $value = array_values($list[$list_id])[$field_id];
                                    }
                                } else {
                                    if (is_array($list[$list_id])) {
                                        $value = implode(',', $list[$list_id]);
                                    } else {
                                        $value = $list[$list_id];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $value;
    }

    /**
     * Delete attachments that were sent by email
     */
    public function action_gform_after_email($is_success) {

        $files = $this->helper->get('gravity_attachments');
        if (is_array($files) && !empty($files)) {
            foreach ($files as $key => $file) {
                $this->helper->delete_dir(dirname($file) . '/');
            }
            $this->helper->deset('gravity_attachments');
        }
    }

    /**
     * Get styles for generating Map Field function
     * 
     * @return array - List of css files to load
     */
    public function styles() {
        $styles = array();

        if (class_exists('GFCommon') && class_exists('GFForms')) {
            $base_url = GFCommon::get_base_url();
            $version = GFForms::$version;
            $min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
            $styles[] = $base_url . "/css/formreset{$min}.css?ver=" . $version;
            $styles[] = $base_url . "/css/datepicker{$min}.css?ver=" . $version;
            $styles[] = $base_url . "/css/formsmain{$min}.css?ver=" . $version;
            $styles[] = $base_url . "/css/readyclass{$min}.css?ver=" . $version;
            $styles[] = $base_url . "/css/browsers{$min}.css?ver=" . $version;
            $styles[] = $base_url . "/css/rtl{$min}.css?ver=" . $version;
            $styles[] = plugins_url('css/extension/gravity.css?v=' . time(), $this->helper->get('plugin_file_path'));
        }

        return $styles;
    }

}
