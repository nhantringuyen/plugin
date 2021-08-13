<?php

/**
 * E2pdf Forminator Extension
 * 
 * @copyright  Copyright 2017 https://e2pdf.com
 * @license    GPL v2
 * @version    1
 * @link       https://e2pdf.com
 * @since      01.01.01
 */
if (!defined('ABSPATH')) {
    die('Access denied.');
}

class Extension_E2pdf_Forminator extends Model_E2pdf_Model {

    private $options;
    private $info = array(
        'key' => 'forminator',
        'title' => 'Forminator'
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

    public function active() {

        if (!function_exists('is_plugin_active')) {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        if (is_plugin_active('forminator/forminator.php')) {
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
        if (class_exists("Forminator_API")) {
            $forms = Forminator_API::get_forms();
            foreach ($forms as $key => $value) {
                $content[] = $this->item($value->id);
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

        $datasets = array();

        if (class_exists("Forminator_API") && $item) {
            $entries = Forminator_API::get_entries($item);
            foreach ($entries as $key => $dataset) {
                $this->set('item', $item);
                $this->set('dataset', $dataset->entry_id);

                $dataset_title = $this->render($name);
                if (!$dataset_title) {
                    $dataset_title = $dataset->entry_id;
                }
                $datasets[] = array(
                    'key' => $dataset->entry_id,
                    'value' => $dataset_title
                );
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

        $data = new stdClass();
        $data->url = false;

        return $data;
    }

    /**
     * Get item
     * 
     * @param string $item - Item ID
     * 
     * @return object - Item
     */
    public function item($item = false) {

        $form = new stdClass();

        if (!$item && $this->get('item')) {
            $item = $this->get('item');
        }

        $forminator_form = false;
        if (class_exists("Forminator_API")) {
            $forminator_form = Forminator_API::get_form($item);
        }

        if (!is_wp_error($forminator_form)) {
            $form->id = (string) $item;
            $form->url = $this->helper->get_url(array('page' => 'forminator-cform-wizard', 'id' => $forminator_form->id));
            $form->name = $forminator_form->name;
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
     * @param string $field - Field
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
     * Render shortcodes which available in this extension
     * 
     * @param string $type - Type of rendering value
     * @param string $value - Content
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

        if ($this->verify() && function_exists("forminator_replace_form_data") && class_exists("Forminator_API")) {

            $args = apply_filters('e2pdf_extension_render_shortcodes_args', $args, $element_id, $template_id, $item, $dataset);

            $form = Forminator_API::get_form($item);

            $metas = array();
            $data = array();

            if ($form->is_prevent_store()) {
                if (class_exists('Forminator_Form_Entry_Model')) {
                    $entry = new Forminator_Form_Entry_Model();
                    if ($this->get('field_data_array') && is_array($this->get('field_data_array'))) {
                        $entry->set_fields($this->get('field_data_array'));
                        $metas = $entry->meta_data;
                    }
                } else {
                    $entry = null;
                }
            } else {
                $entry = Forminator_API::get_entry($item, $dataset);
                $metas = $entry->meta_data;
            }

            foreach ($metas as $key => $meta) {
                if (is_array($meta['value'])) {
                    if (array_unique(array_map("is_int", array_keys($meta['value']))) === array(true)) {
                        $data[$key] = $meta['value'];
                    } else {
                        if (isset($meta['value']['file']['file_url'])) {
                            $data[$key] = $meta['value']['file']['file_url'];
                        } else {
                            foreach ($meta['value'] as $sub_key => $sub_meta) {
                                $data[$key . "-" . $sub_key] = $sub_meta;
                            }
                        }
                    }
                } else {
                    $data[$key] = $meta['value'];
                }
            }

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
            $value = forminator_replace_form_data($value, $data, $form, $entry);
            $value = forminator_replace_variables($value, $item);

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
                $value = str_replace("&#91;", "[", $value);
                if (!$html) {
                    $value = wp_specialchars_decode($value, ENT_QUOTES);
                }
            } else {
                $value = str_replace("[", "&#91;", $value);
            }
        }
        return $value;
    }

    /**
     * Verify if item and dataset exists
     * 
     * @return bool - item and dataset exists
     */
    public function verify() {
        $item = $this->get('item');
        $dataset = $this->get('dataset');

        if ($item && $dataset && class_exists('Forminator_API')) {
            $form = Forminator_API::get_form($item);
            if (!is_wp_error($form)) {
                if ($form->is_prevent_store()) {
                    if ($dataset == 'is_prevent_store') {
                        return true;
                    }
                } else {
                    $entry = Forminator_API::get_entry($item, $dataset);
                    if (!is_wp_error($entry)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function auto_form($template, $data = array()) {

        if ($template->get('ID')) {

            $auto_form_label = isset($data['auto_form_label']) && $data['auto_form_label'] ? $data['auto_form_label'] : false;
            $auto_form_shortcode = isset($data['auto_form_shortcode']) ? true : false;

            $wrappers = array();

            $pages = $template->get('pages');

            $checkboxes = array();
            $radios = array();

            foreach ($pages as $page_key => $page) {
                if (isset($page['elements']) && !empty($page['elements'])) {
                    foreach ($page['elements'] as $element_key => $element) {

                        $type = false;
                        $label = '';

                        if ($element['type'] == 'e2pdf-input' || $element['type'] == 'e2pdf-signature') {
                            $type = 'text';
                            $label = __('Text', 'e2pdf');
                        } elseif ($element['type'] == 'e2pdf-textarea') {
                            $type = 'textarea';
                            $label = __('Textarea', 'e2pdf');
                        } elseif ($element['type'] == 'e2pdf-select') {

                            $type = 'select';
                            $label = __('Select', 'e2pdf');

                            $options = array();
                            $field_options = array();

                            if (isset($element['properties']['options'])) {
                                $field_options = explode("\n", $element['properties']['options']);
                                foreach ($field_options as $option) {
                                    $options[] = array(
                                        'label' => $option,
                                        'value' => ''
                                    );
                                }
                            }
                        } elseif ($element['type'] == 'e2pdf-checkbox') {
                            $field_key = array_search($element['name'], array_column($checkboxes, 'name'));
                            if ($field_key !== false) {
                                $checkboxes[$field_key]['options'][] = array(
                                    'label' => $element['properties']['option'],
                                    'value' => $element['properties']['option'],
                                );
                                $pages[$page_key]['elements'][$element_key]['value'] = '{checkbox-' . $checkboxes[$field_key]['element_id'] . '}';
                            } else {
                                $type = 'checkbox';
                                $label = __('Checkbox', 'e2pdf');
                                $options = array(
                                    'label' => $element['properties']['option'],
                                    'value' => $element['properties']['option'],
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
                                $radios[$field_key]['options'][] = array(
                                    'label' => $element['properties']['option'],
                                    'value' => '',
                                );
                                $pages[$page_key]['elements'][$element_key]['value'] = '{radio-' . $radios[$field_key]['element_id'] . '}';
                            } else {
                                $type = 'radio';
                                $label = __('Radio', 'e2pdf');
                                $options = array(
                                    'label' => $element['properties']['option'],
                                    'value' => '',
                                );
                            }
                        }

                        if ($type) {
                            $labels = array();
                            if ($auto_form_shortcode) {
                                $labels[] = '{' . $type . '-' . $element['element_id'] . '}';
                            }

                            if ($auto_form_label && $auto_form_label == 'value' && isset($element['value']) && $element['value']) {
                                $labels[] = $element['value'];
                            } elseif ($auto_form_label && $auto_form_label == 'name' && isset($element['name']) && $element['name']) {
                                $labels[] = $element['name'];
                            }

                            if ($type == 'checkbox' || $type == 'radio') {

                                $field_data = array(
                                    'name' => $element['name'],
                                    'element_id' => $element['element_id'],
                                    'field_label' => !empty($labels) ? implode(' ', $labels) : $label,
                                    'options' => array(
                                        $options
                                    )
                                );

                                if ($type == 'checkbox') {
                                    $checkboxes[] = $field_data;
                                } else {
                                    $radios[] = $field_data;
                                }
                            } else {
                                $field_data = array(
                                    'element_id' => $type . '-' . $element['element_id'],
                                    'type' => $type,
                                    'cols' => '12',
                                    'required' => false,
                                    'field_label' => !empty($labels) ? implode(' ', $labels) : $label,
                                    'placeholder' => '',
                                    'validation' => false,
                                );

                                if ($type == 'select') {
                                    $field_data['options'] = $options;
                                }

                                $wrappers[] = array(
                                    'wrapper_id' => 'wrapper-' . mt_rand(1000000000000, 9999999999999) . '-' . mt_rand(1000, 9999),
                                    'fields' => array(
                                        $field_data
                                    ),
                                );
                            }

                            $pages[$page_key]['elements'][$element_key]['value'] = '{' . $type . '-' . $element['element_id'] . '}';
                            if (isset($element['properties']['esig'])) {
                                unset($pages[$page_key]['elements'][$element_key]['properties']['esig']);
                            }
                        }
                    }
                }
            }

            foreach ($checkboxes as $element) {
                $wrappers[] = array(
                    'wrapper_id' => 'wrapper-' . mt_rand(1000000000000, 9999999999999) . '-' . mt_rand(1000, 9999),
                    'fields' => array(
                        array(
                            'element_id' => 'checkbox-' . $element['element_id'],
                            'type' => 'checkbox',
                            'cols' => '12',
                            'required' => false,
                            'options' => $element['options'],
                            'field_label' => $element['field_label'],
                            'placeholder' => '',
                            'validation' => false,
                        ),
                    ),
                );
            }

            foreach ($radios as $element) {
                $wrappers[] = array(
                    'wrapper_id' => 'wrapper-' . mt_rand(1000000000000, 9999999999999) . '-' . mt_rand(1000, 9999),
                    'fields' => array(
                        array(
                            'element_id' => 'radio-' . $element['element_id'],
                            'type' => 'radio',
                            'cols' => '12',
                            'required' => false,
                            'options' => $element['options'],
                            'field_label' => $element['field_label'],
                            'placeholder' => '',
                            'validation' => false,
                        ),
                    ),
                );
            }

            $template->set('pages', $pages);
            $settings = array(
                'formName' => $template->get('title'),
                'thankyou' => "true",
                'thankyou-message' => sprintf(__("Success. [e2pdf-download id=\"%s\"]", 'e2pdf'), $template->get('ID')),
                'use-custom-submit' => "true",
                'custom-submit-text' => __("Send Message", Forminator::DOMAIN),
                'use-custom-invalid-form' => "true",
                'custom-invalid-form-message' => __("Error: Your form is not valid, please fix the errors!", Forminator::DOMAIN),
                'enable-ajax' => "true",
                'validation' => "on_submit"
            );

            if (class_exists('Forminator_API')) {
                if ($item = Forminator_API::add_form($template->get('title'), $wrappers, $settings)) {
                    $template->set('item', $item);
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

        if ($item && function_exists("forminator_form_preview") && class_exists("Forminator_API")) {

            $form = Forminator_API::get_form($item);
            if (is_wp_error($form)) {
                return __('Form could not be found', 'e2pdf');
            }

            ob_start();
            forminator_form_preview($item, true, array());
            $source = ob_get_clean();
            if (!$source) {
                $source = forminator_form_preview($item, false, array());
            }

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
                    'forminator-pagination-submit',
                    'forminator-response-message'
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

                $inputs = $xpath->query("//*[contains(@class, 'forminator-input')]");
                foreach ($inputs as $element) {
                    $element = $xml->set_node_value($element, 'type', 'text');
                }

                //remove pagination
                $pagination = $xpath->query("//*[contains(@class, 'forminator-pagination')]");
                foreach ($pagination as $element) {
                    $element = $xml->set_node_value($element, 'style', '');
                }

                //remove buttons
                $remove_rows = $xpath->query("//*[contains(@class, 'forminator-row')][.//button[contains(@class, 'forminator-button') and not(contains(@class, 'forminator-upload-button'))]]");
                foreach ($remove_rows as $element) {
                    $element->parentNode->removeChild($element);
                }

                //replace name on fileuploads
                $fileuploads = $xpath->query("//*[contains(@class, 'forminator-upload')]");
                foreach ($fileuploads as $element) {
                    $file = $xpath->query(".//input[contains(@class, 'forminator-input-file')]", $element)->item(0);
                    $button = $xpath->query(".//button[contains(@class, 'forminator-upload-button')]", $element)->item(0);
                    if ($file && $button) {
                        $button = $xml->set_node_value($button, 'type', 'upload');
                        $button = $xml->set_node_value($button, 'name', $xml->get_node_value($file, 'name'));
                    }
                }

                //replace names on inputs
                $inputs = $xpath->query("//input|//textarea|//select");
                foreach ($inputs as $element) {
                    if ($xml->get_node_value($element, 'type') == 'checkbox') {
                        $element = $xml->set_node_value($element, 'name', str_replace("[]", "", $xml->get_node_value($element, 'name')));
                    }
                    $element = $xml->set_node_value($element, 'name', '{' . $xml->get_node_value($element, 'name') . '}');

                    if (strpos($xml->get_node_value($element, 'class'), 'forminator-checkbox--input') !== false) {
                        $element = $xml->set_node_value($element, 'class', $xml->get_node_value($element, 'class') . ' forminator-checkbox--design');
                    }
                    if (strpos($xml->get_node_value($element, 'class'), 'forminator-radio--input') !== false) {
                        $element = $xml->set_node_value($element, 'class', $xml->get_node_value($element, 'class') . ' forminator-radio--design');
                    }
                }

                //multiselects
                $multiselect = $xpath->query("//ul[contains(@class, 'forminator-multiselect')]");
                foreach ($multiselect as $element) {
                    $name = '';
                    $options = array();
                    $inputs = $xpath->query(".//*[contains(@class, 'forminator-multiselect--item')]", $element);

                    foreach ($inputs as $sub_element) {
                        $input = $xpath->query(".//input", $sub_element)->item(0);
                        $label = $xpath->query(".//label", $sub_element)->item(0);

                        if ($input && $label) {
                            if ($label->childNodes->item(0)) {
                                $options[] = array(
                                    'value' => $xml->get_node_value($input, 'value'),
                                    'label' => $label->childNodes->item(0)->nodeValue
                                );
                            }
                            $name = $xml->get_node_value($input, 'name');
                        }
                        $sub_element->parentNode->removeChild($sub_element);
                    }

                    $li = $dom->createElement('li');
                    $field_atts = array(
                        'class' => 'forminator-multiselect--item',
                    );
                    foreach ($field_atts as $key => $value) {
                        $attr = $dom->createAttribute($key);
                        $attr->value = $value;
                        $li->appendChild($attr);
                    }
                    $element->appendChild($li);


                    $field = $dom->createElement('select');
                    $field_atts = array(
                        'multiple' => 'multiple',
                        'name' => $name,
                    );
                    foreach ($field_atts as $key => $value) {
                        $attr = $dom->createAttribute($key);
                        $attr->value = $value;
                        $field->appendChild($attr);
                    }

                    $li->appendChild($field);

                    foreach ($options as $option) {
                        $option_field = $dom->createElement('option');
                        $field_atts = array(
                            'value' => $option['value'],
                        );
                        foreach ($field_atts as $key => $value) {
                            $attr = $dom->createAttribute($key);
                            $attr->value = $value;
                            $option_field->appendChild($attr);
                        }

                        $label = $dom->createTextNode($option['label']);
                        $option_field->appendChild($label);
                        $field->appendChild($option_field);
                    }
                }

                return $dom->saveHTML();
            }
        }
        return false;
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
        $fields = array();
        $form = false;

        if (class_exists("Forminator_API")) {
            $forminator_form = Forminator_API::get_form($item);
            if (!is_wp_error($forminator_form)) {
                $form = $forminator_form;
                $fields = $form->fields;
            }
        }

        if ($form && $fields) {

            foreach ($fields as $field_obj) {
                $field = $field_obj->raw;
                $float = true;
                $width = 100 / (12 / $field['cols']);

                switch ($field['type']) {
                    case 'name':
                    case 'email':
                    case 'phone':
                    case 'url':
                    case 'upload':
                    case 'number':
                    case 'date':
                        //multiple name field
                        if ($field['type'] == 'name' && isset($field['multiple_name']) && $field['multiple_name']) {

                            if (!$field['prefix'] && !$field['fname']) {
                                if ($field['mname']) {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-html',
                                        'block' => true,
                                        'float' => true,
                                        'properties' => array(
                                            'top' => '20',
                                            'left' => '20',
                                            'right' => '20',
                                            'width' => $field['lname'] ? $width / 2 . '%' : $width . '%',
                                            'height' => 'auto',
                                            'value' => $field['mname_label'],
                                        )
                                    ));

                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-input',
                                        'properties' => array(
                                            'top' => '5',
                                            'width' => '100%',
                                            'height' => 'auto',
                                            'value' => "{" . $field['element_id'] . "-middle-name" . "}",
                                        )
                                    ));
                                }

                                if ($field['lname']) {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-html',
                                        'block' => true,
                                        'float' => true,
                                        'properties' => array(
                                            'top' => '20',
                                            'left' => '20',
                                            'right' => '20',
                                            'width' => $field['mname'] ? $width / 2 . '%' : $width . '%',
                                            'height' => 'auto',
                                            'value' => $field['lname_label'],
                                        )
                                    ));

                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-input',
                                        'properties' => array(
                                            'top' => '5',
                                            'width' => '100%',
                                            'height' => 'auto',
                                            'value' => "{" . $field['element_id'] . "-last-name" . "}",
                                        )
                                    ));
                                }
                            } else {

                                if ($field['prefix']) {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-html',
                                        'block' => true,
                                        'float' => true,
                                        'properties' => array(
                                            'top' => '20',
                                            'left' => '20',
                                            'right' => '20',
                                            'width' => $field['fname'] ? $width / 2 . '%' : $width . '%',
                                            'height' => 'auto',
                                            'value' => $field['prefix_label'],
                                        )
                                    ));

                                    $options_tmp = array();
                                    if (function_exists('forminator_get_name_prefixes')) {
                                        $options = forminator_get_name_prefixes();
                                        foreach ($options as $key => $option) {
                                            $options_tmp[] = $key;
                                        }
                                    }

                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-select',
                                        'properties' => array(
                                            'top' => '5',
                                            'width' => '100%',
                                            'height' => 'auto',
                                            'options' => implode("\n", $options_tmp),
                                            'value' => "{" . $field['element_id'] . "-prefix" . "}",
                                        )
                                    ));

                                    if ($field['mname'] && $field['fname']) {
                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-html',
                                            'properties' => array(
                                                'top' => '20',
                                                'width' => $field['lname'] ? '100%' : '200%',
                                                'right' => $field['lname'] ? '0' : '-40',
                                                'height' => 'auto',
                                                'value' => $field['mname_label'],
                                            )
                                        ));

                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-input',
                                            'properties' => array(
                                                'top' => '5',
                                                'width' => $field['lname'] ? '100%' : '200%',
                                                'height' => 'auto',
                                                'right' => $field['lname'] ? '0' : '-40',
                                                'value' => "{" . $field['element_id'] . "-middle-name" . "}",
                                            )
                                        ));
                                    } elseif ($field['lname'] && !$field['mname'] && $field['fname']) {

                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-html',
                                            'properties' => array(
                                                'top' => '20',
                                                'width' => $field['mname'] ? '100%' : '200%',
                                                'height' => 'auto',
                                                'right' => $field['mname'] ? '0' : '-40',
                                                'value' => $field['lname_label'],
                                            )
                                        ));

                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-input',
                                            'properties' => array(
                                                'top' => '5',
                                                'width' => $field['mname'] ? '100%' : '200%',
                                                'height' => 'auto',
                                                'right' => $field['mname'] ? '0' : '-40',
                                                'value' => "{" . $field['element_id'] . "-last-name" . "}",
                                            )
                                        ));
                                    }
                                }

                                if ($field['fname']) {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-html',
                                        'block' => true,
                                        'float' => true,
                                        'properties' => array(
                                            'top' => '20',
                                            'left' => '20',
                                            'right' => '20',
                                            'width' => $field['prefix'] ? ($width / 2) . '%' : $width . '%',
                                            'height' => 'auto',
                                            'value' => $field['fname_label'],
                                        )
                                    ));

                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-input',
                                        'properties' => array(
                                            'top' => '5',
                                            'width' => '100%',
                                            'height' => 'auto',
                                            'value' => "{" . $field['element_id'] . "-first-name" . "}",
                                        )
                                    ));

                                    if ($field['lname'] && $field['mname'] && $field['prefix']) {
                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-html',
                                            'properties' => array(
                                                'top' => '20',
                                                'width' => $field['mname'] ? '100%' : '200%',
                                                'height' => 'auto',
                                                'right' => $field['mname'] ? '0' : '-40',
                                                'value' => $field['lname_label'],
                                            )
                                        ));

                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-input',
                                            'properties' => array(
                                                'top' => '5',
                                                'width' => $field['mname'] ? '100%' : '200%',
                                                'height' => 'auto',
                                                'right' => $field['mname'] ? '0' : '-40',
                                                'value' => "{" . $field['element_id'] . "-last-name" . "}",
                                            )
                                        ));
                                    }
                                }

                                if (!$field['prefix'] || !$field['fname']) {
                                    if ($field['mname']) {
                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-html',
                                            'properties' => array(
                                                'top' => '20',
                                                'right' => $field['lname'] ? '20' : '0',
                                                'width' => $field['lname'] ? '50%' : '100%',
                                                'height' => 'auto',
                                                'value' => $field['mname_label'],
                                            )
                                        ));
                                    }

                                    if ($field['lname']) {
                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-html',
                                            'float' => $field['mname'] ? true : false,
                                            'properties' => array(
                                                'top' => '20',
                                                'left' => $field['mname'] ? '20' : '0',
                                                'width' => $field['mname'] ? '50%' : '100%',
                                                'height' => 'auto',
                                                'value' => $field['lname_label'],
                                            )
                                        ));
                                    }

                                    if ($field['mname']) {
                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-input',
                                            'properties' => array(
                                                'top' => '5',
                                                'right' => $field['lname'] ? '20' : '0',
                                                'width' => $field['lname'] ? '50%' : '100%',
                                                'height' => 'auto',
                                                'value' => "{" . $field['element_id'] . "-middle-name" . "}",
                                            )
                                        ));
                                    }

                                    if ($field['lname']) {
                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-input',
                                            'float' => $field['mname'] ? true : false,
                                            'properties' => array(
                                                'top' => '5',
                                                'left' => $field['mname'] ? '20' : '0',
                                                'width' => $field['mname'] ? '50%' : '100%',
                                                'height' => 'auto',
                                                'value' => "{" . $field['element_id'] . "-last-name" . "}",
                                            )
                                        ));
                                    }
                                }
                            }
                        } elseif ($field['type'] == 'date' && $field['field_type'] == 'select') {

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'block' => true,
                                'float' => $float,
                                'properties' => array(
                                    'top' => '20',
                                    'left' => '20',
                                    'right' => '20',
                                    'width' => $width . "%",
                                    'height' => 'auto',
                                    'value' => $field['field_label'],
                                )
                            ));

                            $days = array();
                            $months = array();
                            $years = array();
                            if (class_exists('Forminator_Date')) {
                                $forminator_date = new Forminator_Date();
                                $options = $forminator_date->get_day();
                                foreach ($options as $option) {
                                    $days[] = $option['value'];
                                }

                                $options = $forminator_date->get_months();
                                foreach ($options as $option) {
                                    $months[] = $option['value'];
                                }

                                $options = $forminator_date->get_years($field['min_year'], $field['max_year']);
                                foreach ($options as $option) {
                                    $years[] = $option['value'];
                                }
                            }

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-select',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '30%',
                                    'height' => 'auto',
                                    'options' => implode("\n", $days),
                                    'value' => "{" . $field['element_id'] . "-month" . "}",
                                )
                            ));

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-select',
                                'float' => true,
                                'properties' => array(
                                    'top' => '5',
                                    'left' => '5%',
                                    'right' => '5%',
                                    'width' => '40%',
                                    'height' => 'auto',
                                    'options' => implode("\n", $months),
                                    'value' => "{" . $field['element_id'] . "-day" . "}",
                                )
                            ));

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-select',
                                'float' => true,
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '30%',
                                    'height' => 'auto',
                                    'options' => implode("\n", $years),
                                    'value' => "{" . $field['element_id'] . "-year" . "}",
                                )
                            ));
                        } elseif ($field['type'] == 'date' && $field['field_type'] == 'input') {

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'block' => true,
                                'float' => $float,
                                'properties' => array(
                                    'top' => '20',
                                    'left' => '20',
                                    'right' => '20',
                                    'width' => $width . "%",
                                    'height' => 'auto',
                                    'value' => $field['field_label'],
                                )
                            ));

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-input',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '30%',
                                    'height' => 'auto',
                                    'value' => "{" . $field['element_id'] . "-month" . "}",
                                )
                            ));

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-input',
                                'float' => true,
                                'properties' => array(
                                    'top' => '5',
                                    'left' => '5%',
                                    'right' => '5%',
                                    'width' => '40%',
                                    'height' => 'auto',
                                    'value' => "{" . $field['element_id'] . "-day" . "}",
                                )
                            ));

                            $elements[] = $this->auto_field($field, array(
                                'float' => true,
                                'type' => 'e2pdf-input',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '30%',
                                    'height' => 'auto',
                                    'value' => "{" . $field['element_id'] . "-year" . "}",
                                )
                            ));
                        } else {

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'block' => true,
                                'float' => $float,
                                'properties' => array(
                                    'top' => '20',
                                    'left' => '20',
                                    'right' => '20',
                                    'width' => $width . "%",
                                    'height' => 'auto',
                                    'value' => $field['field_label'],
                                )
                            ));

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-input',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => "{" . $field['element_id'] . "}",
                                )
                            ));
                        }
                        break;
                    case 'address':
                        if (!$field['street_address'] && !$field['address_line']) {

                            if (!$field['address_city'] && !$field['address_state']) {
                                if ($field['address_zip']) {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-html',
                                        'block' => true,
                                        'float' => true,
                                        'properties' => array(
                                            'top' => '20',
                                            'left' => '20',
                                            'right' => '20',
                                            'width' => $field['address_country'] ? $width / 2 . '%' : $width . '%',
                                            'height' => 'auto',
                                            'value' => $field['address_zip_label']
                                        )
                                    ));

                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-input',
                                        'properties' => array(
                                            'top' => '5',
                                            'width' => '100%',
                                            'height' => 'auto',
                                            'value' => "{" . $field['element_id'] . "-zip" . "}",
                                        )
                                    ));
                                }

                                if ($field['address_country']) {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-html',
                                        'block' => true,
                                        'float' => true,
                                        'properties' => array(
                                            'top' => '20',
                                            'left' => '20',
                                            'right' => '20',
                                            'width' => $field['address_zip'] ? $width / 2 . '%' : $width . '%',
                                            'height' => 'auto',
                                            'value' => $field['address_country_label'],
                                        )
                                    ));

                                    $options_tmp = array();
                                    if (function_exists('forminator_to_field_array') && function_exists('forminator_get_countries_list')) {
                                        $options = forminator_to_field_array(forminator_get_countries_list(), true);
                                        foreach ($options as $option) {
                                            $options_tmp[] = $option['value'];
                                        }
                                        //can be value or label
                                    }

                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-select',
                                        'properties' => array(
                                            'top' => '5',
                                            'width' => '100%',
                                            'height' => 'auto',
                                            'options' => implode("\n", $options_tmp),
                                            'value' => "{" . $field['element_id'] . "-country" . "}",
                                        )
                                    ));
                                }
                            } else {

                                if ($field['address_city']) {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-html',
                                        'block' => true,
                                        'float' => true,
                                        'properties' => array(
                                            'top' => '20',
                                            'left' => '20',
                                            'right' => '20',
                                            'width' => $field['address_state'] ? $width / 2 . '%' : $width . '%',
                                            'height' => 'auto',
                                            'value' => $field['address_city_label'],
                                        )
                                    ));

                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-input',
                                        'properties' => array(
                                            'top' => '5',
                                            'width' => '100%',
                                            'height' => 'auto',
                                            'value' => "{" . $field['element_id'] . "-city" . "}",
                                        )
                                    ));

                                    if ($field['address_zip'] && $field['address_state']) {
                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-html',
                                            'properties' => array(
                                                'top' => '20',
                                                'right' => $field['address_country'] ? '0' : '-40',
                                                'width' => $field['address_country'] ? '100%' : '200%',
                                                'height' => 'auto',
                                                'value' => $field['address_zip_label'],
                                            )
                                        ));

                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-input',
                                            'properties' => array(
                                                'top' => '5',
                                                'right' => $field['address_country'] ? '0' : '-40',
                                                'width' => $field['address_country'] ? '100%' : '200%',
                                                'height' => 'auto',
                                                'value' => "{" . $field['element_id'] . "-zip" . "}",
                                            )
                                        ));
                                    } elseif ($field['address_country'] && !$field['address_zip'] && $field['address_state']) {

                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-html',
                                            'properties' => array(
                                                'top' => '20',
                                                'right' => $field['address_zip'] ? '0' : '-40',
                                                'width' => $field['address_zip'] ? '100%' : '200%',
                                                'height' => 'auto',
                                                'value' => $field['address_country_label'],
                                            )
                                        ));

                                        $options_tmp = array();
                                        if (function_exists('forminator_to_field_array') && function_exists('forminator_get_countries_list')) {
                                            $options = forminator_to_field_array(forminator_get_countries_list(), true);
                                            foreach ($options as $option) {
                                                $options_tmp[] = $option['value'];
                                            }
                                            //can be value or label
                                        }

                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-select',
                                            'properties' => array(
                                                'top' => '5',
                                                'right' => $field['address_zip'] ? '0' : '-40',
                                                'width' => $field['address_zip'] ? '100%' : '200%',
                                                'height' => 'auto',
                                                'options' => implode("\n", $options_tmp),
                                                'value' => "{" . $field['element_id'] . "-country" . "}",
                                            )
                                        ));
                                    }
                                }

                                if ($field['address_state']) {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-html',
                                        'block' => true,
                                        'float' => true,
                                        'properties' => array(
                                            'top' => '20',
                                            'left' => '20',
                                            'right' => '20',
                                            'width' => $field['address_city'] ? ($width / 2) . '%' : $width . '%',
                                            'height' => 'auto',
                                            'value' => $field['address_state_label'],
                                        )
                                    ));

                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-input',
                                        'properties' => array(
                                            'top' => '5',
                                            'width' => '100%',
                                            'height' => 'auto',
                                            'value' => "{" . $field['element_id'] . "-state" . "}",
                                        )
                                    ));

                                    if ($field['address_country'] && $field['address_zip'] && $field['address_city']) {

                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-html',
                                            'properties' => array(
                                                'top' => '20',
                                                'right' => $field['address_zip'] ? '0' : '-40',
                                                'width' => $field['address_zip'] ? '100%' : '200%',
                                                'height' => 'auto',
                                                'value' => $field['address_country_label'],
                                            )
                                        ));

                                        $options_tmp = array();
                                        if (function_exists('forminator_to_field_array') && function_exists('forminator_get_countries_list')) {
                                            $options = forminator_to_field_array(forminator_get_countries_list(), true);
                                            foreach ($options as $option) {
                                                $options_tmp[] = $option['value'];
                                            }
                                            //can be value or label
                                        }

                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-select',
                                            'properties' => array(
                                                'top' => '5',
                                                'right' => $field['address_zip'] ? '0' : '-40',
                                                'width' => $field['address_zip'] ? '100%' : '200%',
                                                'height' => 'auto',
                                                'options' => implode("\n", $options_tmp),
                                                'value' => "{" . $field['element_id'] . "-country" . "}",
                                            )
                                        ));
                                    }
                                }

                                if (!$field['address_city'] || !$field['address_state']) {
                                    if ($field['address_zip']) {
                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-html',
                                            'properties' => array(
                                                'top' => '20',
                                                'right' => $field['address_country'] ? '20' : '0',
                                                'width' => $field['address_country'] ? '50%' : '100%',
                                                'height' => 'auto',
                                                'value' => $field['address_zip_label'],
                                            )
                                        ));
                                    }

                                    if ($field['address_country']) {

                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-html',
                                            'float' => $field['address_zip'] ? true : false,
                                            'properties' => array(
                                                'top' => '20',
                                                'left' => $field['address_zip'] ? '20' : '0',
                                                'width' => $field['address_zip'] ? '50%' : '100%',
                                                'height' => 'auto',
                                                'value' => $field['address_country_label'],
                                            )
                                        ));
                                    }

                                    if ($field['address_zip']) {
                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-input',
                                            'properties' => array(
                                                'top' => '5',
                                                'right' => $field['address_country'] ? '20' : '0',
                                                'width' => $field['address_country'] ? '50%' : '100%',
                                                'height' => 'auto',
                                                'value' => "{" . $field['element_id'] . "-zip" . "}",
                                            )
                                        ));
                                    }

                                    if ($field['address_country']) {
                                        $options_tmp = array();
                                        if (function_exists('forminator_to_field_array') && function_exists('forminator_get_countries_list')) {
                                            $options = forminator_to_field_array(forminator_get_countries_list(), true);
                                            foreach ($options as $option) {
                                                $options_tmp[] = $option['value'];
                                            }
                                            //can be value or label
                                        }

                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-select',
                                            'float' => $field['address_zip'] ? true : false,
                                            'properties' => array(
                                                'top' => '5',
                                                'left' => $field['address_zip'] ? '20' : '0',
                                                'width' => $field['address_zip'] ? '50%' : '100%',
                                                'height' => 'auto',
                                                'options' => implode("\n", $options_tmp),
                                                'value' => "{" . $field['element_id'] . "-country" . "}",
                                            )
                                        ));
                                    }
                                }
                            }
                        } else {

                            if ($field['street_address']) {
                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-html',
                                    'block' => true,
                                    'float' => true,
                                    'properties' => array(
                                        'top' => '20',
                                        'left' => '20',
                                        'right' => '20',
                                        'width' => $width . '%',
                                        'height' => 'auto',
                                        'value' => $field['street_address_label'],
                                    )
                                ));

                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-input',
                                    'properties' => array(
                                        'top' => '5',
                                        'width' => '100%',
                                        'height' => 'auto',
                                        'value' => "{" . $field['element_id'] . "-street_address" . "}",
                                    )
                                ));
                            }

                            if ($field['address_line']) {

                                if ($field['address_line_label']) {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-html',
                                        'block' => $field['street_address'] ? false : true,
                                        'float' => $field['street_address'] ? false : true,
                                        'properties' => array(
                                            'top' => '20',
                                            'left' => $field['street_address'] ? '0' : '20',
                                            'right' => $field['street_address'] ? '0' : '20',
                                            'width' => $field['street_address'] ? '100%' : $width . '%',
                                            'height' => 'auto',
                                            'value' => $field['address_line_label'],
                                        )
                                    ));
                                }

                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-input',
                                    'properties' => array(
                                        'top' => $field['address_line_label'] ? '0' : '20',
                                        'width' => '100%',
                                        'height' => 'auto',
                                        'value' => "{" . $field['element_id'] . "-address_line" . "}",
                                    )
                                ));
                            }

                            if ($field['address_city']) {

                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-html',
                                    'properties' => array(
                                        'top' => '20',
                                        'right' => $field['address_state'] ? '20' : '0',
                                        'width' => $field['address_state'] ? '50%' : '100%',
                                        'height' => 'auto',
                                        'value' => $field['address_city_label'],
                                    )
                                ));
                            }

                            if ($field['address_state']) {
                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-html',
                                    'float' => $field['address_city'] ? true : false,
                                    'properties' => array(
                                        'top' => $field['address_city'] ? '0' : '20',
                                        'left' => $field['address_city'] ? '20' : '0',
                                        'width' => $field['address_city'] ? '50%' : '100%',
                                        'height' => 'auto',
                                        'value' => $field['address_state_label'],
                                    )
                                ));
                            }

                            if ($field['address_city']) {
                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-input',
                                    'properties' => array(
                                        'top' => '5',
                                        'right' => $field['address_state'] ? '20' : '0',
                                        'width' => $field['address_state'] ? '50%' : '100%',
                                        'height' => 'auto',
                                        'value' => "{" . $field['element_id'] . "-city" . "}",
                                    )
                                ));
                            }

                            if ($field['address_state']) {
                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-input',
                                    'float' => $field['address_city'] ? true : false,
                                    'properties' => array(
                                        'top' => '5',
                                        'left' => $field['address_city'] ? '20' : '0',
                                        'width' => $field['address_city'] ? '50%' : '100%',
                                        'height' => 'auto',
                                        'value' => "{" . $field['element_id'] . "-state" . "}",
                                    )
                                ));
                            }

                            if ($field['address_zip']) {

                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-html',
                                    'properties' => array(
                                        'top' => '20',
                                        'right' => $field['address_country'] ? '20' : '0',
                                        'width' => $field['address_country'] ? '50%' : '100%',
                                        'height' => 'auto',
                                        'value' => $field['address_zip_label'],
                                    )
                                ));
                            }

                            if ($field['address_country']) {
                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-html',
                                    'float' => $field['address_zip'] ? true : false,
                                    'properties' => array(
                                        'top' => '20',
                                        'left' => $field['address_zip'] ? '20' : '0',
                                        'width' => $field['address_zip'] ? '50%' : '100%',
                                        'height' => 'auto',
                                        'value' => $field['address_country_label'],
                                    )
                                ));
                            }

                            if ($field['address_zip']) {
                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-input',
                                    'properties' => array(
                                        'top' => '5',
                                        'right' => $field['address_country'] ? '20' : '0',
                                        'width' => $field['address_country'] ? '50%' : '100%',
                                        'height' => 'auto',
                                        'value' => "{" . $field['element_id'] . "-zip" . "}",
                                    )
                                ));
                            }

                            if ($field['address_country']) {
                                $options_tmp = array();
                                if (function_exists('forminator_to_field_array') && function_exists('forminator_get_countries_list')) {
                                    $options = forminator_to_field_array(forminator_get_countries_list(), true);
                                    foreach ($options as $option) {
                                        $options_tmp[] = $option['value'];
                                    }
                                    //can be value or label
                                }

                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-select',
                                    'float' => $field['address_zip'] ? true : false,
                                    'properties' => array(
                                        'top' => '5',
                                        'left' => $field['address_zip'] ? '20' : '0',
                                        'width' => $field['address_zip'] ? '50%' : '100%',
                                        'height' => 'auto',
                                        'options' => implode("\n", $options_tmp),
                                        'value' => "{" . $field['element_id'] . "-country" . "}",
                                    )
                                ));
                            }
                        }
                        break;
                    case 'time':
                        $hours = array();
                        $minutes = array();

                        if (class_exists('Forminator_Time')) {
                            $forminator_time = new Forminator_Time();
                            $options = $forminator_time->get_hours($field['time_type']);
                            foreach ($options as $option) {
                                $hours[] = $option['value'];
                            }

                            $options = $forminator_time->get_minutes();
                            foreach ($options as $option) {
                                $minutes[] = $option['value'];
                            }
                        }

                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'float' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => ($field['time_type'] == 'twelve' ? $width / 3 : $width / 2) . '%',
                                'height' => 'auto',
                                'value' => $field['hh_label'],
                            )
                        ));

                        if ($field['field_type'] == 'select') {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-select',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'options' => implode("\n", $hours),
                                    'value' => "{" . $field['element_id'] . "-hours" . "}",
                                )
                            ));
                        } else {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-input',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => "{" . $field['element_id'] . "-hours" . "}",
                                )
                            ));
                        }

                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'float' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => ($field['time_type'] == 'twelve' ? $width / 3 : $width / 2) . '%',
                                'height' => 'auto',
                                'value' => $field['mm_label'],
                            )
                        ));

                        if ($field['field_type'] == 'select') {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-select',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'options' => implode("\n", $minutes),
                                    'value' => "{" . $field['element_id'] . "-minutes" . "}",
                                )
                            ));
                        } else {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-input',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => "{" . $field['element_id'] . "-minutes" . "}",
                                )
                            ));
                        }

                        if ($field['time_type'] == 'twelve') {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'block' => true,
                                'float' => $float,
                                'properties' => array(
                                    'top' => '20',
                                    'left' => '20',
                                    'right' => '20',
                                    'width' => ($width / 3) . '%',
                                    'height' => 'auto',
                                    'value' => '',
                                )
                            ));

                            $ampm = array(
                                'am', 'pm'
                            );

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-select',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'options' => implode("\n", $ampm),
                                    'value' => "{" . $field['element_id'] . "-ampm" . "}",
                                )
                            ));
                        }
                        break;
                    case 'html':
                        if ($field['field_label']) {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'block' => true,
                                'properties' => array(
                                    'top' => '20',
                                    'left' => '20',
                                    'right' => '20',
                                    'width' => $width . '%',
                                    'height' => 'auto',
                                    'value' => $field['field_label'],
                                )
                            ));

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => $field['variations'],
                                )
                            ));
                        } else {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'block' => true,
                                'properties' => array(
                                    'top' => '20',
                                    'left' => '20',
                                    'right' => '20',
                                    'width' => $width . '%',
                                    'height' => 'auto',
                                    'value' => $field['variations'],
                                )
                            ));
                        }
                        break;
                    case 'section':
                        $section = '';
                        if ($field['section_title']) {
                            $section .= "<h2>" . $field['section_title'] . "</h2>";
                        }
                        if ($field['section_subtitle']) {
                            $section .= $field['section_subtitle'];
                        }
                        if ($section) {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'block' => true,
                                'properties' => array(
                                    'top' => '20',
                                    'left' => '20',
                                    'right' => '20',
                                    'width' => $width . '%',
                                    'height' => 'auto',
                                    'value' => $section,
                                )
                            ));
                        }
                        break;
                    case 'text':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => $width . '%',
                                'height' => 'auto',
                                'value' => $field['field_label'],
                            )
                        ));

                        if (isset($field['input_type']) && $field['input_type'] == 'line') {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-input',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => "{" . $field['element_id'] . "}",
                                )
                            ));
                        } else {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-textarea',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => "{" . $field['element_id'] . "}",
                                )
                            ));
                        }
                        break;
                    case 'textarea':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => $width . '%',
                                'height' => 'auto',
                                'value' => $field['field_label'],
                            )
                        ));
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-textarea',
                            'properties' => array(
                                'top' => '5',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => "{" . $field['element_id'] . "}",
                            )
                        ));
                        break;
                    case 'select':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'float' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => $width . '%',
                                'height' => 'auto',
                                'value' => $field['field_label'],
                            )
                        ));

                        if ($field['value_type'] == 'radio') {
                            foreach ($field['options'] as $opt_key => $option) {
                                if (is_array($option)) {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-radio',
                                        'properties' => array(
                                            'top' => '5',
                                            'width' => 'auto',
                                            'height' => 'auto',
                                            'value' => "{" . $field['element_id'] . "}",
                                            'option' => $option['value'] ? $option['value'] : $option['label'],
                                            'group' => "{" . $field['element_id'] . "}",
                                        )
                                    ));
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-html',
                                        'float' => true,
                                        'properties' => array(
                                            'left' => '5',
                                            'width' => '100%',
                                            'height' => 'auto',
                                            'value' => $option['label']
                                        )
                                    ));
                                }
                            }
                        } else {
                            $options_tmp = array();
                            foreach ($field['options'] as $option) {
                                $options_tmp[] = $option['value'] ? $option['value'] : $option['label'];
                            }
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-select',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'options' => implode("\n", $options_tmp),
                                    'value' => "{" . $field['element_id'] . "}",
                                )
                            ));
                        }
                        break;
                    case 'checkbox':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'float' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => $width . '%',
                                'height' => 'auto',
                                'value' => $field['field_label'],
                            )
                        ));

                        if ($field['value_type'] == 'multiselect') {
                            $options_tmp = array();
                            foreach ($field['options'] as $opt_key => $option) {
                                $options_tmp[] = $option['value'] ? $option['value'] : $option['label'];
                            }
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-select',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => '44',
                                    'multiline' => '1',
                                    'options' => implode("\n", $options_tmp),
                                    'value' => "{" . $field['element_id'] . "}",
                                )
                            ));
                        } else {
                            foreach ($field['options'] as $opt_key => $option) {
                                if (is_array($option)) {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-checkbox',
                                        'properties' => array(
                                            'top' => '5',
                                            'width' => 'auto',
                                            'height' => 'auto',
                                            'value' => "{" . $field['element_id'] . "}",
                                            'option' => $option['value'] ? $option['value'] : $option['label']
                                        )
                                    ));
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-html',
                                        'float' => true,
                                        'properties' => array(
                                            'left' => '5',
                                            'width' => '100%',
                                            'height' => 'auto',
                                            'value' => $option['label']
                                        )
                                    ));
                                }
                            }
                        }
                        break;
                    case 'gdprcheckbox':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'float' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => $width . '%',
                                'height' => 'auto',
                                'value' => '',
                            )
                        ));

                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-checkbox',
                            'properties' => array(
                                'top' => '5',
                                'width' => 'auto',
                                'height' => 'auto',
                                'value' => "{" . $field['element_id'] . "}",
                                'option' => 'true'
                            )
                        ));
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'float' => true,
                            'properties' => array(
                                'left' => '5',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => $field['gdpr_description']
                            )
                        ));
                        break;
                    default:
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
     * Load actions for this extension
     */
    public function load_actions() {
        add_action('forminator_custom_form_submit_before_set_fields', array($this, 'action_forminator_custom_form_submit_before_set_fields'), 30, 3);
        add_action('forminator_custom_form_mail_admin_message', array($this, 'action_forminator_mail_message'), 30, 5);
        add_action('forminator_custom_form_mail_user_message', array($this, 'action_forminator_mail_message'), 30, 5);
        add_action('forminator_custom_form_mail_before_send_mail', array($this, 'action_forminator_custom_form_mail_before_send_mail'), 30, 4);
        add_action('forminator_custom_form_mail_after_send_mail', array($this, 'action_forminator_custom_form_mail_after_send_mail'), 30, 3);
    }

    /**
     * Load filters for this extension
     */
    public function load_filters() {

        //add_filter('forminator_custom_form_submit_response', array($this, 'filter_pre_filter'), 10, 2);
        //add_filter('forminator_custom_form_submit_response', array($this, 'filter_filter'), 25, 2);
        add_filter('forminator_custom_form_submit_response', array($this, 'filter_forminator_custom_form_submit_response'), 30, 2);

        //add_filter('forminator_custom_form_ajax_submit_response', array($this, 'filter_pre_filter'), 10, 2);
        //add_filter('forminator_custom_form_ajax_submit_response', array($this, 'filter_filter'), 25, 2);
        add_filter('forminator_custom_form_ajax_submit_response', array($this, 'filter_forminator_custom_form_submit_response'), 30, 2);
    }

    public function action_forminator_custom_form_submit_before_set_fields($entry, $form_id, $field_data_array) {
        if (class_exists('Forminator_API')) {
            $form = Forminator_API::get_form($form_id);
            if (!is_wp_error($form)) {
                if ($form->is_prevent_store()) {
                    $this->set('field_data_array', $field_data_array);
                    $this->set('dataset', 'is_prevent_store');
                } elseif ($entry && is_object($entry) && property_exists($entry, 'entry_id') && isset($entry->entry_id)) {
                    $this->set('dataset', $entry->entry_id);
                }
            }
        }
    }

    public function action_forminator_custom_form_mail_before_send_mail($mail, $custom_form, $data, $entry) {
        add_filter('wp_mail', array($this, 'filter_wp_mail'), 30, 1);
    }

    public function action_forminator_custom_form_mail_after_send_mail($mail, $custom_form, $data) {
        remove_filter('wp_mail', array($this, 'filter_wp_mail'), 30);
        $files = $this->helper->get('forminator_attachments');
        if (is_array($files) && !empty($files)) {
            foreach ($files as $key => $file) {
                $this->helper->delete_dir(dirname($file) . '/');
            }
            $this->helper->deset('forminator_attachments');
            $this->helper->deset('forminator_mail_attachments');
        }
    }

    public function action_forminator_mail_message($message, $custom_form, $data, $entry, $mail) {
        if (isset($message) && false !== strpos($message, '[')) {
            $shortcode_tags = array(
                'e2pdf-download',
                'e2pdf-save',
                'e2pdf-attachment',
                'e2pdf-adobesign'
            );

            preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $message, $matches);

            $tagnames = array_intersect($shortcode_tags, $matches[1]);

            if (!empty($tagnames)) {

                $pattern = $this->helper->load('shortcode')->get_shortcode_regex($tagnames);

                preg_match_all("/$pattern/", $message, $shortcodes);
                foreach ($shortcodes[0] as $key => $shortcode_value) {
                    $shortcode = array();
                    $shortcode[1] = $shortcodes[1][$key];
                    $shortcode[2] = $shortcodes[2][$key];
                    $shortcode[3] = $shortcodes[3][$key];
                    $shortcode[4] = $shortcodes[4][$key];
                    $shortcode[5] = $shortcodes[5][$key];
                    $shortcode[6] = $shortcodes[6][$key];

                    $atts = shortcode_parse_atts($shortcode[3]);

                    $file = false;

                    if (!isset($atts['dataset']) && isset($atts['id'])) {
                        $template = new Model_E2pdf_Template();
                        $template->load($atts['id']);
                        if ($template->get('extension') === 'forminator') {
                            $entry_id = $this->get('dataset');
                            if ($entry_id) {
                                if (($shortcode[2] === 'e2pdf-download' || $shortcode['2'] === 'e2pdf-view') && $entry_id == 'is_prevent_store') {
                                    //data is empty to fill pdf
                                } else {
                                    if ($entry_id == 'is_prevent_store') {
                                        add_filter('e2pdf_model_shortcode_extension_options', array($this, 'filter_e2pdf_model_shortcode_extension_options'), 30, 2);
                                    }
                                    $atts['dataset'] = $entry_id;
                                    $shortcode[3] .= " dataset=\"{$entry_id}\"";
                                }
                            }
                        }
                    }

                    if (!isset($atts['apply'])) {
                        $shortcode[3] .= " apply=\"true\"";
                    }

                    if (!isset($atts['filter'])) {
                        $shortcode[3] .= " filter=\"true\"";
                    }

                    if (($shortcode[2] === 'e2pdf-save' && isset($atts['attachment']) && $atts['attachment'] == 'true') || $shortcode[2] === 'e2pdf-attachment') {
                        $file = do_shortcode_tag($shortcode);
                        if ($file) {
                            if ($shortcode[2] != 'e2pdf-save' && !isset($atts['pdf'])) {
                                $this->helper->add('forminator_attachments', $file);
                            }
                            $this->helper->add('forminator_mail_attachments', $file);
                        }
                        $message = str_replace($shortcode_value, '', $message);
                    } else {
                        $message = str_replace($shortcode_value, do_shortcode_tag($shortcode), $message);
                    }
                    remove_filter('e2pdf_model_shortcode_extension_options', array($this, 'filter_e2pdf_model_shortcode_extension_options'), 30);
                }
            }
        }

        return $message;
    }

    public function filter_e2pdf_model_shortcode_extension_options($options = array(), $template) {
        if ($this->get('dataset') && $this->get('dataset') == 'is_prevent_store') {
            $options['field_data_array'] = $this->get('field_data_array');
        }
        return $options;
    }

    public function filter_pre_filter($response, $form_id) {
        if (isset($response['message']) && false !== strpos($response['message'], '[') && isset($response['success']) && $response['success']) {
            $model_e2pdf_filter = new Model_E2pdf_Filter();
            $response['message'] = $model_e2pdf_filter->pre_filter($response['message']);
        }
        return $response;
    }

    public function filter_filter($response, $form_id) {
        if (isset($response['message']) && false !== strpos($response['message'], '[') && isset($response['success']) && $response['success']) {
            $model_e2pdf_filter = new Model_E2pdf_Filter();
            $response['message'] = $model_e2pdf_filter->filter($response['message']);
        }
        return $response;
    }

    public function filter_forminator_custom_form_submit_response($response, $form_id) {

        if (isset($response['message']) && false !== strpos($response['message'], '[') && isset($response['success']) && $response['success']) {
            $shortcode_tags = array(
                'e2pdf-download',
                'e2pdf-save',
                'e2pdf-view',
                'e2pdf-adobesign'
            );

            preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $response['message'], $matches);

            $tagnames = array_intersect($shortcode_tags, $matches[1]);

            if (!empty($tagnames)) {

                $pattern = $this->helper->load('shortcode')->get_shortcode_regex($tagnames);

                preg_match_all("/$pattern/", $response['message'], $shortcodes);
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
                            if ($template->get('extension') === 'forminator') {
                                $entry_id = $this->get('dataset');
                                if ($entry_id) {
                                    if (($shortcode[2] === 'e2pdf-download' || $shortcode['2'] === 'e2pdf-view') && $entry_id == 'is_prevent_store') {
                                        //data is empty to fill pdf
                                    } else {
                                        if ($entry_id == 'is_prevent_store') {
                                            add_filter('e2pdf_model_shortcode_extension_options', array($this, 'filter_e2pdf_model_shortcode_extension_options'), 30, 2);
                                        }
                                        $atts['dataset'] = $entry_id;
                                        $shortcode[3] .= " dataset=\"{$entry_id}\"";
                                    }
                                }
                            }
                        }

                        if (!isset($atts['apply'])) {
                            $shortcode[3] .= " apply=\"true\"";
                        }

                        if (!isset($atts['filter'])) {
                            $shortcode[3] .= " filter=\"true\"";
                        }

                        $response['message'] = str_replace($shortcode_value, do_shortcode_tag($shortcode), $response['message']);
                        remove_filter('e2pdf_model_shortcode_extension_options', array($this, 'filter_e2pdf_model_shortcode_extension_options'), 30);
                    }
                }
            }
        }
        $this->set('dataset', false);
        return $response;
    }

    public function filter_wp_mail($args = array()) {
        $files = $this->helper->get('forminator_mail_attachments');
        if (is_array($files) && !empty($files)) {
            foreach ($files as $file) {
                $args['attachments'][] = $file;
            }
        }
        $wp_mail = array(
            'to' => $args['to'],
            'subject' => $args['subject'],
            'message' => $args['message'],
            'headers' => $args['headers'],
            'attachments' => $args['attachments'],
        );

        return $wp_mail;
    }

    /**
     * Get styles for generating Map Field function
     * 
     * @return array - List of css files to load
     */
    public function styles() {
        $styles = array();
        if (function_exists('forminator_plugin_url')) {
            if (defined('FORMINATOR_VERSION')) {
                $version = FORMINATOR_VERSION;
            } else {
                $version = '0';
            }
            $styles = array(
                forminator_plugin_url() . 'assets/forminator-ui/css/forminator-forms.min.css?v=' . $version,
                plugins_url('css/extension/forminator.css?v=' . time(), $this->helper->get('plugin_file_path'))
            );
        }
        return $styles;
    }

}
