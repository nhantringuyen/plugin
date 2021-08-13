<?php

/**
 * E2pdf Shortcode Model
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

class Model_E2pdf_Shortcode extends Model_E2pdf_Model {

    function __construct() {
        parent::__construct();
    }

    /**
     * [e2pdf-attachment] shortcode
     * 
     * @param array $atts - Attributes
     */
    function e2pdf_attachment($atts = array()) {

        $response = '';

        $template_id = isset($atts['id']) ? (int) $atts['id'] : 0;
        $pdf = isset($atts['pdf']) ? $atts['pdf'] : false;
        $apply = isset($atts['apply']) ? true : false;
        $dataset = isset($atts['dataset']) ? $atts['dataset'] : false;
        $uid = isset($atts['uid']) ? $atts['uid'] : false;

        $args = array();
        foreach ($atts as $att_key => $att_value) {
            if (substr($att_key, 0, 3) === "arg") {
                $args[$att_key] = $att_value;
            }
        }

        if ($uid) {
            $entry = new Model_E2pdf_Entry();
            if ($entry->load_by_uid($uid)) {
                $uid_params = $entry->get('entry');
                $template_id = isset($uid_params['template_id']) ? (int) $uid_params['template_id'] : 0;
                $dataset = isset($uid_params['dataset']) ? $uid_params['dataset'] : false;
                $pdf = isset($uid_params['pdf']) ? $uid_params['pdf'] : false;
            } else {
                return $response;
            }
        }

        if ($pdf) {
            if ($apply && !$this->helper->load('filter')->is_stream($pdf) && file_exists($pdf)) {
                return $pdf;
            } else {
                return $response;
            }
        }

        if (!$apply || !$dataset || !$template_id) {
            return $response;
        }

        $template = new Model_E2pdf_Template();
        if ($template->load($template_id)) {

            $uid_params = array();
            $uid_params['template_id'] = $template_id;
            $uid_params['dataset'] = $dataset;

            $template->extension()->set('dataset', $dataset);

            $options = array();
            $options = apply_filters('e2pdf_model_shortcode_extension_options', $options, $template);
            $options = apply_filters('e2pdf_model_shortcode_e2pdf_attachment_extension_options', $options, $template);

            foreach ($options as $option_key => $option_value) {
                $template->extension()->set($option_key, $option_value);
            }

            if ($template->extension()->verify()) {

                if (array_key_exists('inline', $atts)) {
                    $inline = $atts['inline'] == 'true' ? 1 : 0;
                    $uid_params['inline'] = $inline;
                }

                if (array_key_exists('flatten', $atts)) {
                    $flatten = (int) $atts['flatten'];
                    $uid_params['flatten'] = $flatten;
                    $template->set('flatten', $flatten);
                }

                if (array_key_exists('format', $atts)) {
                    $format = $atts['format'];
                    $uid_params['format'] = $format;
                    $template->set('format', $format);
                }

                if (array_key_exists('password', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $password = $template->extension()->render($atts['password']);
                    } else {
                        $password = $template->extension()->convert_shortcodes($atts['password'], true);
                    }
                    $uid_params['password'] = $password;
                    $template->set('password', $password);
                } else {
                    $template->set('password', $template->extension()->render($template->get('password')));
                }

                if (array_key_exists('meta_title', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_title = $template->extension()->render($atts['meta_title']);
                    } else {
                        $meta_title = $template->extension()->convert_shortcodes($atts['meta_title'], true);
                    }
                    $uid_params['meta_title'] = $meta_title;
                    $template->set('meta_title', $meta_title);
                } else {
                    $template->set('meta_title', $template->extension()->render($template->get('meta_title')));
                }

                if (array_key_exists('meta_subject', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_subject = $template->extension()->render($atts['meta_subject']);
                    } else {
                        $meta_subject = $template->extension()->convert_shortcodes($atts['meta_subject'], true);
                    }
                    $uid_params['meta_subject'] = $meta_subject;
                    $template->set('meta_subject', $meta_subject);
                } else {
                    $template->set('meta_subject', $template->extension()->render($template->get('meta_subject')));
                }

                if (array_key_exists('meta_author', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_author = $template->extension()->render($atts['meta_author']);
                    } else {
                        $meta_author = $template->extension()->convert_shortcodes($atts['meta_author'], true);
                    }
                    $uid_params['meta_author'] = $meta_author;
                    $template->set('meta_author', $meta_author);
                } else {
                    $template->set('meta_author', $template->extension()->render($template->get('meta_author')));
                }

                if (array_key_exists('meta_keywords', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_keywords = $template->extension()->render($atts['meta_keywords']);
                    } else {
                        $meta_keywords = $template->extension()->convert_shortcodes($atts['meta_keywords'], true);
                    }
                    $uid_params['meta_keywords'] = $meta_keywords;
                    $template->set('meta_keywords', $meta_keywords);
                } else {
                    $template->set('meta_keywords', $template->extension()->render($template->get('meta_keywords')));
                }

                if (array_key_exists('name', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $name = $template->extension()->render($atts['name']);
                    } else {
                        $name = $template->extension()->convert_shortcodes($atts['name'], true);
                    }
                    $uid_params['name'] = $name;
                    $template->set('name', $name);
                } else {
                    $template->set('name', $template->extension()->render($template->get('name')));
                }

                if (array_key_exists('user_id', $atts)) {
                    $uid_params['user_id'] = (int) $atts['user_id'];
                } else {
                    $uid_params['user_id'] = get_current_user_id();
                }

                $uid_params['args'] = $args;

                $entry = new Model_E2pdf_Entry();
                $entry->set('entry', $uid_params);
                if (!$entry->load_by_uid($entry->get('uid'))) {
                    $entry->save();
                }

                $template->fill($dataset, $entry->get('uid'));
                $request = $template->render();

                if (!isset($request['error']) && $entry->get('ID')) {
                    $tmp_dir = $this->helper->get('tmp_dir') . 'e2pdf' . md5($entry->get('uid')) . '/';

                    $this->helper->create_dir($tmp_dir);

                    if ($template->get('name')) {
                        $name = $template->get('name');
                    } else {
                        $name = $template->extension()->render($template->get_filename());
                    }

                    $file_name = $name . '.pdf';
                    $file_name = $this->helper->load('convert')->to_file_name($file_name);
                    $file_path = $tmp_dir . $file_name;
                    file_put_contents($file_path, base64_decode($request['file']));

                    if (file_exists($file_path)) {
                        $entry->set('pdf_num', $entry->get('pdf_num') + 1);
                        $entry->save();
                        return $file_path;
                    }
                }
            }
        }
        return $response;
    }

    /**
     * [e2pdf-download] shortcode
     * 
     * @param array $atts - Attributes
     */
    function e2pdf_download($atts = array()) {

        $response = '';

        $template_id = isset($atts['id']) ? (int) $atts['id'] : 0;
        $dataset = isset($atts['dataset']) ? $atts['dataset'] : false;
        $uid = isset($atts['uid']) ? $atts['uid'] : false;
        $output = isset($atts['output']) ? $atts['output'] : false;
        $pdf = isset($atts['pdf']) ? $atts['pdf'] : false;
        $iframe_download = false;

        /*
         * Backward compatiability with old format since 1.09.05
         */
        if (isset($atts['button-title'])) {
            $atts['button_title'] = $atts['button-title'];
        }

        $args = array();
        foreach ($atts as $att_key => $att_value) {
            if (substr($att_key, 0, 3) === "arg") {
                $args[$att_key] = $att_value;
            }
        }

        if ($uid) {
            $entry = new Model_E2pdf_Entry();
            if ($entry->load_by_uid($uid)) {
                $uid_params = $entry->get('entry');
                $template_id = isset($uid_params['template_id']) ? (int) $uid_params['template_id'] : 0;
                $dataset = isset($uid_params['dataset']) ? $uid_params['dataset'] : false;
                $pdf = isset($uid_params['pdf']) ? $uid_params['pdf'] : false;
            } else {
                return $response;
            }
        }

        if ($pdf) {
            if (!$this->helper->load('filter')->is_stream($pdf) && file_exists($pdf)) {

                $uid_params = array();
                $uid_params['pdf'] = $pdf;

                if (array_key_exists('class', $atts)) {
                    $classes = explode(" ", $atts['class']);
                } else {
                    $classes = array();
                }
                $classes[] = 'e2pdf-download';

                $inline = 0;
                if (array_key_exists('inline', $atts)) {
                    $inline = $atts['inline'] == 'true' ? 1 : 0;
                    $uid_params['inline'] = $inline;
                }

                if ($inline) {
                    $classes[] = 'e2pdf-inline';
                }

                $auto = 0;
                if (array_key_exists('auto', $atts)) {
                    $auto = $atts['auto'] == 'true' ? 1 : 0;
                }

                if ($auto) {
                    $classes[] = 'e2pdf-auto';
                    if (array_key_exists('iframe_download', $atts) && $atts['iframe_download'] == 'true' && !$inline) {
                        $classes[] = 'e2pdf-iframe-download';
                        $iframe_download = true;
                    }
                }

                if (array_key_exists('name', $atts)) {
                    $name = $atts['name'];
                    $uid_params['name'] = $name;
                }

                if (array_key_exists('button_title', $atts)) {
                    $button_title = $atts['button_title'];
                } else {
                    $button_title = __('Download', 'e2pdf');
                }

                $entry = new Model_E2pdf_Entry();
                $entry->set('entry', $uid_params);
                if (!$entry->load_by_uid($entry->get('uid'))) {
                    $entry->save();
                }

                if ($entry->get('ID')) {
                    $url = esc_url(add_query_arg(array(
                        'page' => 'e2pdf-download',
                        'uid' => $entry->get('uid'),
                                    ), site_url('/')
                    ));

                    if ($output && $output == 'url') {
                        $response = $url;
                    } else {
                        $response = "<a id='e2pdf-download' class='" . implode(" ", $classes) . "' target='_blank' href='{$url}'>{$button_title}</a>";
                        if ($iframe_download) {
                            $response .= "<iframe style='width:0;height:0;border:0; border:none;' src='{$url}'></iframe>";
                        }
                    }
                }
            }
            return $response;
        }

        if (!$dataset || !$template_id) {
            return $response;
        }

        $template = new Model_E2pdf_Template();
        if ($template->load($template_id, false)) {

            $uid_params = array();
            $uid_params['template_id'] = $template_id;
            $uid_params['dataset'] = $dataset;

            if (array_key_exists('class', $atts)) {
                $classes = explode(" ", $atts['class']);
            } else {
                $classes = array();
            }
            $classes[] = 'e2pdf-download';

            $template->extension()->set('dataset', $dataset);

            $options = array();
            $options = apply_filters('e2pdf_model_shortcode_extension_options', $options, $template);
            $options = apply_filters('e2pdf_model_shortcode_e2pdf_download_extension_options', $options, $template);

            foreach ($options as $option_key => $option_value) {
                $template->extension()->set($option_key, $option_value);
            }

            if ($template->extension()->verify()) {

                if (array_key_exists('inline', $atts)) {
                    $inline = $atts['inline'] == 'true' ? 1 : 0;
                    $uid_params['inline'] = $inline;
                } else {
                    $inline = $template->get('inline');
                }

                if ($inline) {
                    $classes[] = 'e2pdf-inline';
                }

                if (array_key_exists('auto', $atts)) {
                    $auto = $atts['auto'] == 'true' ? 1 : 0;
                } else {
                    $auto = $template->get('auto');
                }

                if ($auto) {
                    $classes[] = 'e2pdf-auto';
                    if (array_key_exists('iframe_download', $atts) && $atts['iframe_download'] == 'true' && !$inline) {
                        $classes[] = 'e2pdf-iframe-download';
                        $iframe_download = true;
                    }
                }

                if (array_key_exists('flatten', $atts)) {
                    $flatten = (int) $atts['flatten'];
                    $uid_params['flatten'] = $flatten;
                }

                if (array_key_exists('format', $atts)) {
                    $format = $atts['format'];
                    $uid_params['format'] = $format;
                }

                if (array_key_exists('button_title', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $button_title = $template->extension()->render($atts['button_title']);
                    } else {
                        $button_title = $template->extension()->convert_shortcodes($atts['button_title'], true);
                    }
                } elseif ($template->extension()->render($template->get('button_title')) !== '') {
                    $button_title = $template->extension()->render($template->get('button_title'));
                } else {
                    $button_title = __('Download', 'e2pdf');
                }

                if (array_key_exists('password', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $password = $template->extension()->render($atts['password']);
                    } else {
                        $password = $template->extension()->convert_shortcodes($atts['password'], true);
                    }
                    $uid_params['password'] = $password;
                }

                if (array_key_exists('meta_title', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_title = $template->extension()->render($atts['meta_title']);
                    } else {
                        $meta_title = $template->extension()->convert_shortcodes($atts['meta_title'], true);
                    }
                    $uid_params['meta_title'] = $meta_title;
                }

                if (array_key_exists('meta_subject', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_subject = $template->extension()->render($atts['meta_subject']);
                    } else {
                        $meta_subject = $template->extension()->convert_shortcodes($atts['meta_subject'], true);
                    }
                    $uid_params['meta_subject'] = $meta_subject;
                }

                if (array_key_exists('meta_author', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_author = $template->extension()->render($atts['meta_author']);
                    } else {
                        $meta_author = $template->extension()->convert_shortcodes($atts['meta_author'], true);
                    }
                    $uid_params['meta_author'] = $meta_author;
                }

                if (array_key_exists('meta_keywords', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_keywords = $template->extension()->render($atts['meta_keywords']);
                    } else {
                        $meta_keywords = $template->extension()->convert_shortcodes($atts['meta_keywords'], true);
                    }
                    $uid_params['meta_keywords'] = $meta_keywords;
                }

                if (array_key_exists('name', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $name = $template->extension()->render($atts['name']);
                    } else {
                        $name = $template->extension()->convert_shortcodes($atts['name'], true);
                    }
                    $uid_params['name'] = $name;
                }

                if (array_key_exists('user_id', $atts)) {
                    $uid_params['user_id'] = (int) $atts['user_id'];
                } else {
                    $uid_params['user_id'] = get_current_user_id();
                }

                $uid_params['args'] = $args;

                $entry = new Model_E2pdf_Entry();
                $entry->set('entry', $uid_params);
                if (!$entry->load_by_uid($entry->get('uid'))) {
                    $entry->save();
                }

                if ($entry->get('ID')) {
                    $url = esc_url(add_query_arg(array(
                        'page' => 'e2pdf-download',
                        'uid' => $entry->get('uid'),
                                    ), site_url('/')
                    ));

                    if ($output && $output == 'url') {
                        $response = $url;
                    } else {
                        $response = "<a id='e2pdf-download' class='" . implode(" ", $classes) . "' target='_blank' href='{$url}'>{$button_title}</a>";
                        if ($iframe_download) {
                            $response .= "<iframe style='width:0;height:0;border:0; border:none;' src='{$url}'></iframe>";
                        }
                    }
                }
            }
        }
        return $response;
    }

    /**
     * @since 0.01.44
     * 
     * [e2pdf-save] shortcode
     * 
     * @param array $atts - Attributes
     */
    function e2pdf_save($atts = array()) {

        $response = '';

        $template_id = isset($atts['id']) ? (int) $atts['id'] : 0;
        $dataset = isset($atts['dataset']) ? $atts['dataset'] : false;
        $download = isset($atts['download']) && $atts['download'] == 'true' ? true : false;
        $view = isset($atts['view']) && $atts['view'] == 'true' ? true : false;
        $attachment = isset($atts['attachment']) && $atts['attachment'] == 'true' ? true : false;
        $overwrite = isset($atts['overwrite']) && $atts['overwrite'] == 'false' ? false : true;
        $output = isset($atts['output']) ? $atts['output'] : false;
        $apply = isset($atts['apply']) ? true : false;

        $args = array();
        foreach ($atts as $att_key => $att_value) {
            if (substr($att_key, 0, 3) === "arg") {
                $args[$att_key] = $att_value;
            }
        }

        if (!$apply || !$dataset || !$template_id) {
            return $response;
        }

        $template = new Model_E2pdf_Template();

        if ($template->load($template_id)) {

            $uid_params = array();
            $uid_params['template_id'] = $template_id;
            $uid_params['dataset'] = $dataset;

            $template->extension()->set('dataset', $dataset);

            $options = array();
            $options = apply_filters('e2pdf_model_shortcode_extension_options', $options, $template);
            $options = apply_filters('e2pdf_model_shortcode_e2pdf_save_extension_options', $options, $template);

            foreach ($options as $option_key => $option_value) {
                $template->extension()->set($option_key, $option_value);
            }

            if ($template->extension()->verify()) {

                if (array_key_exists('inline', $atts)) {
                    $inline = $atts['inline'] == 'true' ? 1 : 0;
                    $uid_params['inline'] = $inline;
                }

                if (array_key_exists('flatten', $atts)) {
                    $flatten = (int) $atts['flatten'];
                    $uid_params['flatten'] = $flatten;
                    $template->set('flatten', $flatten);
                }

                if (array_key_exists('format', $atts)) {
                    $format = $atts['format'];
                    $uid_params['format'] = $format;
                    $template->set('format', $format);
                }

                if (array_key_exists('password', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $password = $template->extension()->render($atts['password']);
                    } else {
                        $password = $template->extension()->convert_shortcodes($atts['password'], true);
                    }
                    $uid_params['password'] = $password;
                    $template->set('password', $password);
                } else {
                    $template->set('password', $template->extension()->render($template->get('password')));
                }

                if (array_key_exists('meta_title', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_title = $template->extension()->render($atts['meta_title']);
                    } else {
                        $meta_title = $template->extension()->convert_shortcodes($atts['meta_title'], true);
                    }
                    $uid_params['meta_title'] = $meta_title;
                    $template->set('meta_title', $meta_title);
                } else {
                    $template->set('meta_title', $template->extension()->render($template->get('meta_title')));
                }

                if (array_key_exists('meta_subject', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_subject = $template->extension()->render($atts['meta_subject']);
                    } else {
                        $meta_subject = $template->extension()->convert_shortcodes($atts['meta_subject'], true);
                    }
                    $uid_params['meta_subject'] = $meta_subject;
                    $template->set('meta_subject', $meta_subject);
                } else {
                    $template->set('meta_subject', $template->extension()->render($template->get('meta_subject')));
                }

                if (array_key_exists('meta_author', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_author = $template->extension()->render($atts['meta_author']);
                    } else {
                        $meta_author = $template->extension()->convert_shortcodes($atts['meta_author'], true);
                    }
                    $uid_params['meta_author'] = $meta_author;
                    $template->set('meta_author', $meta_author);
                } else {
                    $template->set('meta_author', $template->extension()->render($template->get('meta_author')));
                }

                if (array_key_exists('meta_keywords', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_keywords = $template->extension()->render($atts['meta_keywords']);
                    } else {
                        $meta_keywords = $template->extension()->convert_shortcodes($atts['meta_keywords'], true);
                    }
                    $uid_params['meta_keywords'] = $meta_keywords;
                    $template->set('meta_keywords', $meta_keywords);
                } else {
                    $template->set('meta_keywords', $template->extension()->render($template->get('meta_keywords')));
                }

                if (array_key_exists('name', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $name = $template->extension()->render($atts['name']);
                    } else {
                        $name = $template->extension()->convert_shortcodes($atts['name'], true);
                    }
                    $uid_params['name'] = $name;
                    $template->set('name', $name);
                } else {
                    $template->set('name', $template->extension()->render($template->get('name')));
                }

                if ($download || $view || $attachment) {
                    $uid_params['pdf'] = false;
                }

                if (array_key_exists('user_id', $atts)) {
                    $uid_params['user_id'] = (int) $atts['user_id'];
                } else {
                    $uid_params['user_id'] = get_current_user_id();
                }

                $uid_params['args'] = $args;

                $entry = new Model_E2pdf_Entry();
                $entry->set('entry', $uid_params);
                if (!$entry->load_by_uid($entry->get('uid'))) {
                    $entry->save();
                }

                if (array_key_exists('dir', $atts)) {
                    $save_dir = $atts['dir'];
                } else {
                    $tpl_dir = $this->helper->get('tpl_dir') . $template->get('ID') . "/";
                    $save_dir = $tpl_dir . "save/";
                    $this->helper->create_dir($tpl_dir);
                    $this->helper->create_dir($save_dir);
                }

                if ($template->get('name')) {
                    $name = $template->get('name');
                } else {
                    $name = $template->extension()->render($template->get_filename());
                }

                $file_name = $name . '.pdf';
                $file_name = $this->helper->load('convert')->to_file_name($file_name);
                $file_path = $save_dir . $file_name;

                if ($overwrite || !file_exists($file_path)) {
                    $template->fill($dataset, $entry->get('uid'));
                    $request = $template->render();
                }

                if (isset($request['error'])) {
                    return false;
                } else {
                    if ($entry->get('ID')) {

                        if (is_dir($save_dir) && is_writable($save_dir)) {

                            if ($overwrite || !file_exists($file_path)) {
                                file_put_contents($file_path, base64_decode($request['file']));
                            }

                            if (!$this->helper->load('filter')->is_stream($file_path) && file_exists($file_path)) {
                                $entry->set('pdf_num', $entry->get('pdf_num') + 1);
                                if ($download || $view || $attachment) {
                                    $uid_params['pdf'] = $file_path;
                                    $entry->set('entry', $uid_params);
                                    $atts['uid'] = $entry->get('uid');
                                }
                                $entry->save();
                                if ($download) {
                                    $response = $this->e2pdf_download($atts);
                                } elseif ($view) {
                                    $response = $this->e2pdf_view($atts);
                                } elseif ($attachment) {
                                    $response = $this->e2pdf_attachment($atts);
                                } elseif ($output && $output == 'path') {
                                    $response = $file_path;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $response;
    }

    /**
     * [e2pdf-view] shortcode
     * 
     * @param array $atts - Attributes
     */
    function e2pdf_view($atts = array()) {

        $response = '';
        $name = '';

        $template_id = isset($atts['id']) ? (int) $atts['id'] : 0;
        $dataset = isset($atts['dataset']) ? $atts['dataset'] : false;
        $uid = isset($atts['uid']) ? $atts['uid'] : false;
        $width = isset($atts['width']) ? $atts['width'] : '100%';
        $height = isset($atts['height']) ? $atts['height'] : '500';
        $pdf = isset($atts['pdf']) ? $atts['pdf'] : false;
        $page = isset($atts['page']) ? $atts['page'] : false;
        $zoom = isset($atts['zoom']) ? $atts['zoom'] : false;
        $nameddest = isset($atts['nameddest']) ? $atts['nameddest'] : false;
        $pagemode = isset($atts['pagemode']) ? $atts['pagemode'] : false;
        $responsive = isset($atts['responsive']) && $atts['responsive'] == 'true' ? true : false;
        $viewer = isset($atts['viewer']) && $atts['viewer'] ? $atts['viewer'] : false;
        $single_page_mode = isset($atts['single_page_mode']) && $atts['single_page_mode'] == 'true' ? true : false;
        $hide = isset($atts['hide']) ? $atts['hide'] : false;
        $background = isset($atts['background']) ? $atts['background'] : false;
        $border = isset($atts['border']) ? $atts['border'] : false;

        $args = array();
        foreach ($atts as $att_key => $att_value) {
            if (substr($att_key, 0, 3) === "arg") {
                $args[$att_key] = $att_value;
            }
        }

        $viewer_options = array();
        if ($page) {
            $viewer_options[] = 'page=' . $page;
        }
        if ($zoom) {
            $viewer_options[] = 'zoom=' . $zoom;
        }
        if ($nameddest) {
            $viewer_options[] = 'nameddest=' . $nameddest;
        }
        if ($pagemode) {
            $viewer_options[] = 'pagemode=' . $pagemode;
        }

        if (array_key_exists('class', $atts)) {
            $classes = explode(" ", $atts['class']);
        } else {
            $classes = array();
        }
        $classes[] = 'e2pdf-view';

        if ($responsive) {
            $classes[] = 'e2pdf-responsive';
        }

        if ($single_page_mode) {
            $classes[] = 'e2pdf-single-page-mode';
        }


        if ($hide) {
            $hidden = array_map('trim', explode(',', $hide));
            if (in_array('toolbar', $hidden)) {
                $classes[] = 'e2pdf-hide-toolbar';
            }

            if (in_array('secondary-toolbar', $hidden)) {
                $classes[] = 'e2pdf-hide-secondary-toolbar';
            }

            if (in_array('left-toolbar', $hidden)) {
                $classes[] = 'e2pdf-hide-left-toolbar';
            }

            if (in_array('middle-toolbar', $hidden)) {
                $classes[] = 'e2pdf-hide-middle-toolbar';
            }

            if (in_array('right-toolbar', $hidden)) {
                $classes[] = 'e2pdf-hide-right-toolbar';
            }

            if (in_array('sidebar', $hidden)) {
                $classes[] = 'e2pdf-hide-sidebar';
            }
            if (in_array('search', $hidden)) {
                $classes[] = 'e2pdf-hide-search';
            }
            if (in_array('pageupdown', $hidden)) {
                $classes[] = 'e2pdf-hide-pageupdown';
            }
            if (in_array('pagenumber', $hidden)) {
                $classes[] = 'e2pdf-hide-pagenumber';
            }

            if (in_array('zoom', $hidden)) {
                $classes[] = 'e2pdf-hide-zoom';
            }

            if (in_array('scale', $hidden)) {
                $classes[] = 'e2pdf-hide-scale';
            }
            if (in_array('presentation', $hidden)) {
                $classes[] = 'e2pdf-hide-presentation';
            }
            if (in_array('openfile', $hidden)) {
                $classes[] = 'e2pdf-hide-openfile';
            }
            if (in_array('print', $hidden)) {
                $classes[] = 'e2pdf-hide-print';
            }
            if (in_array('download', $hidden)) {
                $classes[] = 'e2pdf-hide-download';
            }
            if (in_array('bookmark', $hidden)) {
                $classes[] = 'e2pdf-hide-bookmark';
            }
            if (in_array('firstlastpage', $hidden)) {
                $classes[] = 'e2pdf-hide-firstlastpage';
            }
            if (in_array('rotate', $hidden)) {
                $classes[] = 'e2pdf-hide-rotate';
            }
            if (in_array('cursor', $hidden)) {
                $classes[] = 'e2pdf-hide-cursor';
            }
            if (in_array('scroll', $hidden)) {
                $classes[] = 'e2pdf-hide-scroll';
            }
            if (in_array('spread', $hidden)) {
                $classes[] = 'e2pdf-hide-spread';
            }
            if (in_array('properties', $hidden)) {
                $classes[] = 'e2pdf-hide-properties';
            }
        }


        if ($background !== false) {
            $classes[] = 'e2pdf-hide-background';
        }

        $styles = array();

        if ($background !== false) {
            $styles[] = "background:" . $background;
        }

        if ($border !== false) {
            $styles[] = "border:" . $border;
        }

        if ($uid) {
            $entry = new Model_E2pdf_Entry();
            if ($entry->load_by_uid($uid)) {

                $uid_params = $entry->get('entry');

                $template_id = isset($uid_params['template_id']) ? (int) $uid_params['template_id'] : 0;
                $dataset = isset($uid_params['dataset']) ? $uid_params['dataset'] : false;

                $template = new Model_E2pdf_Template();
                if ($uid_params['pdf'] && file_exists($uid_params['pdf']) && isset($uid_params['template_id']) && isset($uid_params['dataset']) && $template->load($uid_params['template_id'])) {

                    if (isset($uid_params['name'])) {
                        $template->set('name', $uid_params['name']);
                    } else {
                        $template->set('name', $template->extension()->render($template->get('name')));
                    }
                    if ($template->get('name')) {
                        $name = $template->get('name');
                    } else {
                        $name = $template->extension()->render($template->get_filename());
                    }

                    if (!$name) {
                        $name = basename($uid_params['pdf'], ".pdf");
                    }

                    $pdf = esc_url_raw(add_query_arg(array(
                        'page' => 'e2pdf-download',
                        'uid' => $entry->get('uid'),
                        'saveName' => $name
                                    ), site_url('/')
                    ));
                }
            } else {
                return $response;
            }
        }

        if ($pdf) {
            if (filter_var($pdf, FILTER_VALIDATE_URL)) {
                $file = urlencode($pdf);
                if (!empty($viewer_options)) {
                    $file .= '#' . implode('&', $viewer_options);
                }

                if ($viewer) {
                    $url = esc_url(add_query_arg('file', $file, $viewer));
                } else {
                    $url = esc_url(add_query_arg('file', $file, plugins_url('assets/pdf.js/web/viewer.html', $this->helper->get('plugin_file_path'))));
                }
                $response = "<iframe style='" . implode(";", $styles) . "' class='" . implode(" ", $classes) . "' width='{$width}' height='{$height}' src='{$url}'></iframe>";
            } else if (!$this->helper->load('filter')->is_stream($pdf) && file_exists($pdf)) {

                $uid_params['pdf'] = $pdf;

                if (array_key_exists('name', $atts)) {
                    $name = $atts['name'];
                    $uid_params['name'] = $name;
                }

                $entry = new Model_E2pdf_Entry();
                $entry->set('entry', $uid_params);
                if (!$entry->load_by_uid($entry->get('uid'))) {
                    $entry->save();
                }

                if ($entry->get('ID')) {
                    if (!$name) {
                        $name = basename($uid_params['pdf'], ".pdf");
                    }

                    $pdf_url = esc_url_raw(add_query_arg(array(
                        'page' => 'e2pdf-download',
                        'uid' => $entry->get('uid'),
                        'saveName' => $name
                                    ), site_url('/')
                    ));

                    $file = urlencode($pdf_url);
                    if (!empty($viewer_options)) {
                        $file .= '#' . implode('&', $viewer_options);
                    }

                    if ($viewer) {
                        $url = esc_url(add_query_arg('file', $file, $viewer));
                    } else {
                        $url = esc_url(add_query_arg('file', $file, plugins_url('assets/pdf.js/web/viewer.html', $this->helper->get('plugin_file_path'))));
                    }
                    $response = "<iframe style='" . implode(";", $styles) . "' class='" . implode(" ", $classes) . "' width='{$width}' height='{$height}' src='{$url}'></iframe>";
                }
            }
            return $response;
        }

        if (!$template_id || !$dataset) {
            return $response;
        }

        $template = new Model_E2pdf_Template();
        if ($template->load($template_id, false)) {

            $uid_params = array();
            $uid_params['template_id'] = $template_id;
            $uid_params['dataset'] = $dataset;

            $template->extension()->set('dataset', $dataset);

            $options = array();
            $options = apply_filters('e2pdf_model_shortcode_extension_options', $options, $template);
            $options = apply_filters('e2pdf_model_shortcode_e2pdf_view_extension_options', $options, $template);

            foreach ($options as $option_key => $option_value) {
                $template->extension()->set($option_key, $option_value);
            }

            if ($template->extension()->verify()) {

                if (array_key_exists('inline', $atts)) {
                    $inline = $atts['inline'] == 'true' ? 1 : 0;
                    $uid_params['inline'] = $inline;
                }

                if (array_key_exists('flatten', $atts)) {
                    $flatten = (int) $atts['flatten'];
                    $uid_params['flatten'] = $flatten;
                }

                if (array_key_exists('format', $atts)) {
                    $format = $atts['format'];
                    $uid_params['format'] = $format;
                }

                if (array_key_exists('password', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $password = $template->extension()->render($atts['password']);
                    } else {
                        $password = $template->extension()->convert_shortcodes($atts['password'], true);
                    }
                    $uid_params['password'] = $password;
                }

                if (array_key_exists('meta_title', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_title = $template->extension()->render($atts['meta_title']);
                    } else {
                        $meta_title = $template->extension()->convert_shortcodes($atts['meta_title'], true);
                    }
                    $uid_params['meta_title'] = $meta_title;
                }

                if (array_key_exists('meta_subject', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_subject = $template->extension()->render($atts['meta_subject']);
                    } else {
                        $meta_subject = $template->extension()->convert_shortcodes($atts['meta_subject'], true);
                    }
                    $uid_params['meta_subject'] = $meta_subject;
                }

                if (array_key_exists('meta_author', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_author = $template->extension()->render($atts['meta_author']);
                    } else {
                        $meta_author = $template->extension()->convert_shortcodes($atts['meta_author'], true);
                    }
                    $uid_params['meta_author'] = $meta_author;
                }

                if (array_key_exists('meta_keywords', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_keywords = $template->extension()->render($atts['meta_keywords']);
                    } else {
                        $meta_keywords = $template->extension()->convert_shortcodes($atts['meta_keywords'], true);
                    }
                    $uid_params['meta_keywords'] = $meta_keywords;
                }

                if (array_key_exists('name', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $name = $template->extension()->render($atts['name']);
                    } else {
                        $name = $template->extension()->convert_shortcodes($atts['name'], true);
                    }
                    $uid_params['name'] = $name;
                    $template->set('name', $name);
                } else {
                    $template->set('name', $template->extension()->render($template->get('name')));
                }

                if (array_key_exists('user_id', $atts)) {
                    $uid_params['user_id'] = (int) $atts['user_id'];
                } else {
                    $uid_params['user_id'] = get_current_user_id();
                }

                $uid_params['args'] = $args;

                $entry = new Model_E2pdf_Entry();
                $entry->set('entry', $uid_params);
                if (!$entry->load_by_uid($entry->get('uid'))) {
                    $entry->save();
                }

                if ($entry->get('ID')) {

                    $pdf_url = esc_url_raw(add_query_arg(array(
                        'page' => 'e2pdf-download',
                        'uid' => $entry->get('uid'),
                        'saveName' => $template->get('name') ? $template->get('name') . ".pdf" : $template->extension()->render($template->get_filename()) . ".pdf"
                                    ), site_url('/')
                    ));

                    $file = urlencode($pdf_url);
                    if (!empty($viewer_options)) {
                        $file .= '#' . implode('&', $viewer_options);
                    }

                    if ($viewer) {
                        $url = esc_url(add_query_arg('file', $file, $viewer));
                    } else {
                        $url = esc_url(add_query_arg('file', $file, plugins_url('assets/pdf.js/web/viewer.html', $this->helper->get('plugin_file_path'))));
                    }
                    $response = "<iframe style='" . implode(";", $styles) . "' class='" . implode(" ", $classes) . "' width='{$width}' height='{$height}' src='{$url}'></iframe>";
                }
            }
        }
        return $response;
    }

    /**
     * [e2pdf-adobesign] shortcode
     * 
     * @param array $atts - Attributes
     */
    function e2pdf_adobesign($atts = array()) {

        $template_id = isset($atts['id']) ? (int) $atts['id'] : 0;

        $args = array();
        foreach ($atts as $att_key => $att_value) {
            if (substr($att_key, 0, 3) === "arg") {
                $args[$att_key] = $att_value;
            }
        }

        $response = '';

        if (!array_key_exists('apply', $atts) || !array_key_exists('dataset', $atts) || !$template_id) {
            return $response;
        }

        $dataset = $atts['dataset'];
        $template = new Model_E2pdf_Template();
        if ($template->load($template_id)) {
            $uid_params = array();
            $uid_params['template_id'] = $template_id;
            $uid_params['dataset'] = $dataset;

            $template->extension()->set('dataset', $dataset);

            $options = array();
            $options = apply_filters('e2pdf_model_shortcode_extension_options', $options, $template);
            $options = apply_filters('e2pdf_model_shortcode_e2pdf_adobesign_extension_options', $options, $template);

            foreach ($options as $option_key => $option_value) {
                $template->extension()->set($option_key, $option_value);
            }

            if ($template->extension()->verify()) {

                if (array_key_exists('inline', $atts)) {
                    $inline = $atts['inline'] == 'true' ? 1 : 0;
                    $uid_params['inline'] = $inline;
                }

                if (array_key_exists('flatten', $atts)) {
                    $flatten = (int) $atts['flatten'];
                    $uid_params['flatten'] = $flatten;
                    $template->set('flatten', $flatten);
                }

                if (array_key_exists('format', $atts)) {
                    $format = $atts['format'];
                    $uid_params['format'] = $format;
                    $template->set('format', $format);
                }

                if (array_key_exists('password', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $password = $template->extension()->render($atts['password']);
                    } else {
                        $password = $template->extension()->convert_shortcodes($atts['password'], true);
                    }
                    $uid_params['password'] = $password;
                    $template->set('password', $password);
                } else {
                    $template->set('password', $template->extension()->render($template->get('password')));
                }

                if (array_key_exists('meta_title', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_title = $template->extension()->render($atts['meta_title']);
                    } else {
                        $meta_title = $template->extension()->convert_shortcodes($atts['meta_title'], true);
                    }
                    $uid_params['meta_title'] = $meta_title;
                    $template->set('meta_title', $meta_title);
                } else {
                    $template->set('meta_title', $template->extension()->render($template->get('meta_title')));
                }

                if (array_key_exists('meta_subject', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_subject = $template->extension()->render($atts['meta_subject']);
                    } else {
                        $meta_subject = $template->extension()->convert_shortcodes($atts['meta_subject'], true);
                    }
                    $uid_params['meta_subject'] = $meta_subject;
                    $template->set('meta_subject', $meta_subject);
                } else {
                    $template->set('meta_subject', $template->extension()->render($template->get('meta_subject')));
                }

                if (array_key_exists('meta_author', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_author = $template->extension()->render($atts['meta_author']);
                    } else {
                        $meta_author = $template->extension()->convert_shortcodes($atts['meta_author'], true);
                    }
                    $uid_params['meta_author'] = $meta_author;
                    $template->set('meta_author', $meta_author);
                } else {
                    $template->set('meta_author', $template->extension()->render($template->get('meta_author')));
                }

                if (array_key_exists('meta_keywords', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_keywords = $template->extension()->render($atts['meta_keywords']);
                    } else {
                        $meta_keywords = $template->extension()->convert_shortcodes($atts['meta_keywords'], true);
                    }
                    $uid_params['meta_keywords'] = $meta_keywords;
                    $template->set('meta_keywords', $meta_keywords);
                } else {
                    $template->set('meta_keywords', $template->extension()->render($template->get('meta_keywords')));
                }

                if (array_key_exists('name', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $name = $template->extension()->render($atts['name']);
                    } else {
                        $name = $template->extension()->convert_shortcodes($atts['name'], true);
                    }
                    $uid_params['name'] = $name;
                    $template->set('name', $name);
                } else {
                    $template->set('name', $template->extension()->render($template->get('name')));
                }

                $disable = array();
                if (array_key_exists('disable', $atts)) {
                    $disable = explode(',', $atts['disable']);
                }

                if (array_key_exists('user_id', $atts)) {
                    $uid_params['user_id'] = (int) $atts['user_id'];
                } else {
                    $uid_params['user_id'] = get_current_user_id();
                }

                $uid_params['args'] = $args;

                $entry = new Model_E2pdf_Entry();
                $entry->set('entry', $uid_params);
                if (!$entry->load_by_uid($entry->get('uid'))) {
                    $entry->save();
                }

                $template->fill($dataset, $entry->get('uid'));
                $request = $template->render();

                if (!isset($request['error']) && $entry->get('ID')) {
                    $tmp_dir = $this->helper->get('tmp_dir') . 'e2pdf' . md5($entry->get('uid')) . '/';
                    $this->helper->create_dir($tmp_dir);

                    if ($template->get('name')) {
                        $name = $template->get('name');
                    } else {
                        $name = $template->extension()->render($template->get_filename());
                    }

                    $file_name = $name . '.pdf';
                    $file_name = $this->helper->load('convert')->to_file_name($file_name);
                    $file_path = $tmp_dir . $file_name;
                    file_put_contents($file_path, base64_decode($request['file']));

                    if (file_exists($file_path)) {

                        $agreement_id = false;
                        $documents = array();
                        if (!in_array('post_transientDocuments', $disable)) {
                            $model_e2pdf_adobesign = new Model_E2pdf_AdobeSign();
                            $model_e2pdf_adobesign->set(array(
                                'action' => 'api/rest/v5/transientDocuments',
                                'headers' => array(
                                    'Content-Type: multipart/form-data',
                                ),
                                'data' => array(
                                    'File-Name' => $file_name,
                                    'Mime-Type' => 'application/pdf',
                                    'File' => class_exists('cURLFile') ? new cURLFile($file_path) : '@' . $file_path
                                ),
                            ));

                            if ($transientDocumentId = $model_e2pdf_adobesign->request('transientDocumentId')) {
                                $documents[] = array(
                                    'transientDocumentId' => $transientDocumentId
                                );
                            }
                            $model_e2pdf_adobesign->flush();
                        }

                        $documents = apply_filters('e2pdf_model_shortcode_e2pdf_adobesign_fileInfos', $documents, $atts, $template, $entry, $template->extension(), $file_path);

                        if (!in_array('post_agreements', $disable) && !empty($documents)) {

                            $output = false;
                            if (array_key_exists('output', $atts)) {
                                $output = $atts['output'];
                            }

                            $recipients = array();
                            if (array_key_exists('recipients', $atts)) {
                                $atts['recipients'] = $template->extension()->render($atts['recipients']);
                                $recipients_list = explode(',', $atts['recipients']);

                                foreach ($recipients_list as $recipient_info) {
                                    $recipients[] = array(
                                        'recipientSetMemberInfos' => array(
                                            'email' => trim($recipient_info)
                                        ),
                                        'recipientSetRole' => 'SIGNER'
                                    );
                                }
                            }

                            $data = array(
                                'documentCreationInfo' => array(
                                    'signatureType' => 'ESIGN',
                                    'recipientSetInfos' => $recipients,
                                    'signatureFlow' => 'SENDER_SIGNATURE_NOT_REQUIRED',
                                    'fileInfos' => $documents,
                                    'name' => $name
                                )
                            );

                            $data = apply_filters('e2pdf_model_shortcode_e2pdf_adobesign_post_agreements_data', $data, $atts, $template, $entry, $template->extension(), $file_path, $documents);

                            $model_e2pdf_adobesign = new Model_E2pdf_AdobeSign();
                            $model_e2pdf_adobesign->set(array(
                                'action' => 'api/rest/v5/agreements',
                                'data' => $data,
                            ));

                            $agreement_id = $model_e2pdf_adobesign->request('agreementId');
                            $model_e2pdf_adobesign->flush();
                        }

                        $response = apply_filters('e2pdf_model_shortcode_e2pdf_adobesign_response', $response, $atts, $template, $entry, $template->extension(), $file_path, $documents, $agreement_id);
                    }

                    $this->helper->delete_dir($tmp_dir);
                    return $response;
                }
            }
        }
        return $response;
    }

    /**
     * [e2pdf-format-number] shortcode
     * 
     * @param array $atts - Attributes
     * @param string $value - Content
     */
    function e2pdf_format_number($atts = array(), $value = '') {

        $dec_point = isset($atts['dec_point']) ? $atts['dec_point'] : '.';
        $thousands_sep = isset($atts['thousands_sep']) ? $atts['thousands_sep'] : '';
        $decimal = isset($atts['decimal']) ? $atts['decimal'] : false;
        $explode = isset($atts['explode']) ? $atts['explode'] : '';
        $implode = isset($atts['implode']) ? $atts['implode'] : '';

        $new_value = array();
        $value = array_filter((array) $value, 'strlen');
        foreach ($value as $v) {
            if ($explode && strpos($v, $explode)) {
                $v = explode($explode, $v);
            }
            foreach ((array) $v as $n) {
                $n = str_replace(array(" ", ","), array("", "."), $n);
                $n = preg_replace('/\.(?=.*\.)/', '', $n);
                $n = floatval($n);

                if (!$decimal) {
                    $num = explode('.', $n);
                    $decimal = isset($num[1]) ? strlen($num[1]) : 0;
                }

                $n = number_format($n, $decimal, $dec_point, $thousands_sep);
                $new_value[] = $n;
            }
            unset($v);
        }
        $new_value = array_filter((array) $new_value, 'strlen');
        return implode($implode, $new_value);
    }

    /**
     * [e2pdf-format-date] shortcode
     * 
     * @param array $atts - Attributes
     * @param string $value - Content
     */
    function e2pdf_format_date($atts = array(), $value = '') {
        $format = isset($atts['format']) ? $atts['format'] : get_option('date_format');
        if (!$value) {
            return '';
        }
        $value = date($format, strtotime($value));
        return $value;
    }

    /**
     * [e2pdf-format-output] shortcode
     * 
     * @param array $atts - Attributes
     * @param string $value - Content
     */
    function e2pdf_format_output($atts = array(), $value = '') {

        $explode = isset($atts['explode']) ? $atts['explode'] : false;
        $implode = isset($atts['implode']) ? $atts['implode'] : '';
        $output = isset($atts['output']) ? $atts['output'] : false;
        $filter = isset($atts['filter']) ? $atts['filter'] : false;
        $search = isset($atts['search']) ? explode("|||", $atts['search']) : array();
        $ireplace = isset($atts['ireplace']) ? explode("|||", $atts['ireplace']) : array();
        $replace = isset($atts['replace']) ? explode("|||", $atts['replace']) : array();

        $filters = array();
        if ($filter) {
            if (strpos($filter, ',')) {
                $filters = explode(',', $filter);
            } else {
                $filters = array_filter((array) $filter, 'strlen');
            }
        }

        $new_value = array();
        if (!empty($ireplace)) {
            $value = str_ireplace($search, $ireplace, $value);
        } elseif (!empty($replace)) {
            $value = str_replace($search, $replace, $value);
        }

        $value = array_filter((array) $value, 'strlen');

        foreach ($value as $v) {
            if ($explode && strpos($v, $explode)) {
                $v = explode($explode, $v);
            }
            foreach ((array) $v as $n) {
                if (!empty($filters)) {
                    foreach ((array) $filters as $sub_filter) {
                        switch ($sub_filter) {
                            case 'trim':
                                $n = trim($n);
                                break;

                            case 'strip_tags':
                                $n = strip_tags($n);
                                break;

                            case 'strtolower':
                                if (function_exists('mb_strtolower')) {
                                    $n = mb_strtolower($n);
                                } elseif (function_exists('strtolower')) {
                                    $n = strtolower($n);
                                }
                                break;

                            case 'ucfirst':
                                if (function_exists('mb_strtoupper') && function_exists('mb_strtolower')) {
                                    $fc = mb_strtoupper(mb_substr($n, 0, 1));
                                    $n = $fc . mb_substr($n, 1);
                                } else if (function_exists('ucfirst') && function_exists('strtolower')) {
                                    $n = ucfirst($n);
                                }
                                break;

                            case 'strtoupper':
                                if (function_exists('mb_strtoupper')) {
                                    $n = mb_strtoupper($n);
                                } elseif (function_exists('strtoupper')) {
                                    $n = strtoupper($n);
                                }
                                break;

                            case 'lines':
                                $n = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $n);
                                break;

                            case 'nl2br':
                                $n = nl2br($n);
                                break;

                            default:
                                break;
                        }
                    }
                }
                $new_value[] = $n;
            }
            unset($v);
        }

        if ($output) {
            $o_search = array();
            $o_replace = array();

            foreach ($new_value as $key => $value) {
                $o_search[] = "{" . $key . "}";
                $o_replace[] = $value;
            }
            $output = str_replace($o_search, $o_replace, $output);
            return preg_replace('~(?:{/?)[^/}]+/?}~s', "", $output);
        } else {
            return implode($implode, $new_value);
        }
    }

    /**
     * [e2pdf-user] shortcode
     * 
     * @param array $atts - Attributes
     */
    function e2pdf_user($atts = array()) {

        $id = isset($atts['id']) ? $atts['id'] : '0';
        $id = $id == 'current' ? get_current_user_id() : (int) $id;

        $key = isset($atts['key']) ? $atts['key'] : 'ID';
        $meta = isset($atts['meta']) && $atts['meta'] == 'true' ? true : false;

        $response = '';

        $data_fields = array(
            'ID',
            'user_login',
            'user_nicename',
            'user_email',
            'user_url',
            'user_registered',
            'display_name'
        );

        $data_fields = apply_filters('e2pdf_model_shortcode_user_data_fields', $data_fields);

        if (in_array($key, $data_fields) && !$meta) {
            $user = get_userdata($id);
            if (isset($user->$key)) {
                $response = $user->$key;
            } elseif ($key == 'ID') {
                $response = $id;
            }
        } else {
            $response = get_user_meta($id, $key, true);
        }

        return $response;
    }

    /**
     * [e2pdf-content] shortcode
     * 
     * @param array $atts - Attributes
     * @param string $value - Content
     */
    function e2pdf_content($atts = array(), $value = '') {
        $response = '';

        $id = isset($atts['id']) ? $atts['id'] : false;
        $key = isset($atts['key']) ? $atts['key'] : false;

        if ($id && $key) {
            $post = get_post($id);
            if ($post) {
                if (isset($post->post_content) && $post->post_content) {
                    $content = $this->helper->load('convert')->to_content_key($key, $post->post_content);
                    remove_filter('the_content', 'wpautop');
                    $content = apply_filters('the_content', $content);
                    add_filter('the_content', 'wpautop');
                    $content = str_replace("</p>", "</p>\r\n", $content);
                    $response = $content;
                }
            }
        } elseif ($value) {
            $response = apply_filters('the_content', $value);
        }
        return $response;
    }

    /**
     * [e2pdf-exclude] shortcode
     * 
     * @param array $atts - Attributes
     * @param string $value - Content
     */
    function e2pdf_exclude($atts = array(), $value = '') {

        $apply = isset($atts['apply']) ? true : false;

        if ($apply) {
            $response = '';
        } else {
            $response = apply_filters('the_content', $value);
        }

        return $response;
    }

    /**
     * [e2pdf-wp] shortcode
     * 
     * @param array $atts - Attributes
     * @param string $value - Content
     */
    function e2pdf_wp($atts = array(), $value = '') {

        $response = '';

        $id = isset($atts['id']) ? $atts['id'] : false;
        $key = isset($atts['key']) ? $atts['key'] : false;
        $path = isset($atts['path']) ? $atts['path'] : false;
        $names = isset($atts['names']) && $atts['names'] == 'true' ? true : false;
        $explode = isset($atts['explode']) ? $atts['explode'] : '.';
        $implode = isset($atts['implode']) ? $atts['implode'] : ', ';
        $attachment_url = isset($atts['attachment_url']) && $atts['attachment_url'] == 'true' ? true : false;
        $attachment_image_url = isset($atts['attachment_image_url']) && $atts['attachment_image_url'] == 'true' ? true : false;
        $size = isset($atts['size']) ? $atts['size'] : 'thumbnail';
        $meta = isset($atts['meta']) && $atts['meta'] == 'true' ? true : false;
        $terms = isset($atts['terms']) && $atts['terms'] == 'true' ? true : false;

        $data_fields = array(
            'id',
            'post_author',
            'post_date',
            'post_date_gmt',
            'post_content',
            'post_title',
            'post_excerpt',
            'post_status',
            'comment_status',
            'ping_status',
            'post_password',
            'post_name',
            'to_ping',
            'pinged',
            'post_modified',
            'post_modified_gmt',
            'post_content_filtered',
            'post_parent',
            'guid',
            'menu_order',
            'post_type',
            'post_mime_type',
            'comment_count',
            'filter',
            'post_thumbnail'
        );

        $data_fields = apply_filters('e2pdf_model_shortcode_wp_data_fields', $data_fields);

        if ($id && $key) {
            $post = get_post($id);
            if ($post) {
                if (in_array($key, $data_fields) && !$meta && !$terms) {
                    if ($key == 'post_author') {
                        $response = isset($post->post_author) && $post->post_author ? get_userdata($post->post_author)->user_nicename : '';
                    } elseif ($key == 'id' && isset($post->ID)) {
                        $response = $post->ID;
                    } elseif ($key == 'post_thumbnail' && isset($post->ID)) {
                        $response = get_the_post_thumbnail_url($post->ID, $size);
                    } elseif ($key == 'post_content' && isset($post->post_content)) {
                        $content = $post->post_content;

                        if (false !== strpos($content, '[')) {
                            $shortcode_tags = array(
                                'e2pdf-exclude',
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

                                    $shortcode[3] .= " apply=\"true\"";
                                    $content = str_replace($shortcode_value, "[" . $shortcode['2'] . $shortcode['3'] . "]" . $shortcode['5'] . "[/" . $shortcode['2'] . "]", $content);
                                }
                            }
                        }
                        $content = apply_filters('the_content', $content);
                        $content = str_replace("</p>", "</p>\r\n", $content);
                        $response = $content;
                    } elseif (isset($post->$key)) {
                        $response = $post->$key;
                    }
                } elseif ($terms) {
                    if ($names) {
                        $post_terms = wp_get_post_terms($id, $key, array('fields' => 'names'));
                        if (!is_wp_error($post_terms) && is_array($post_terms)) {
                            $response = implode($implode, $post_terms);
                        }
                    } else {
                        $post_terms = wp_get_post_terms($id, $key);
                        if (!is_wp_error($post_terms)) {
                            $post_terms = json_decode(json_encode($post_terms), true);
                            if ($path !== false) {
                                $path_parts = explode($explode, $path);
                                $path_value = &$post_terms;
                                $found = true;
                                foreach ($path_parts as $path_part) {
                                    if (isset($path_value[$path_part])) {
                                        $path_value = &$path_value[$path_part];
                                    } else {
                                        $found = false;
                                        break;
                                    }
                                }

                                if ($found) {
                                    if ($attachment_url) {
                                        if (!is_array($path_value)) {
                                            $response = wp_get_attachment_url($path_value);
                                        }
                                    } elseif ($attachment_image_url) {
                                        if (!is_array($path_value)) {
                                            $image = wp_get_attachment_image_url($path_value, $size);
                                            if ($image) {
                                                $response = $image;
                                            }
                                        }
                                    } else {
                                        if (is_array($path_value)) {
                                            $response = serialize($path_value);
                                        } else {
                                            $response = $path_value;
                                        }
                                    }
                                }
                            } else {
                                $response = serialize($post_terms);
                            }
                        }
                    }
                } else {
                    $post_meta = get_post_meta($id, $key, true);
                    if ($post_meta) {
                        if (is_array($post_meta)) {
                            if ($path !== false) {
                                $path_parts = explode($explode, $path);
                                $path_value = &$post_meta;
                                $found = true;
                                foreach ($path_parts as $path_part) {
                                    if (isset($path_value[$path_part])) {
                                        $path_value = &$path_value[$path_part];
                                    } else {
                                        $found = false;
                                        break;
                                    }
                                }

                                if ($found) {
                                    if ($attachment_url) {
                                        if (!is_array($path_value)) {
                                            $response = wp_get_attachment_url($path_value);
                                        }
                                    } elseif ($attachment_image_url) {
                                        if (!is_array($path_value)) {
                                            $image = wp_get_attachment_image_url($path_value, $size);
                                            if ($image) {
                                                $response = $image;
                                            }
                                        }
                                    } else {
                                        if (is_array($path_value)) {
                                            $response = serialize($path_value);
                                        } else {
                                            $response = $path_value;
                                        }
                                    }
                                }
                            } else {
                                $response = serialize($post_meta);
                            }
                        } else {
                            if ($attachment_url) {
                                $response = wp_get_attachment_url($post_meta);
                            } elseif ($attachment_image_url) {
                                $image = wp_get_attachment_image_url($post_meta, $size);
                                if ($image) {
                                    $response = $image;
                                }
                            } else {
                                $response = $post_meta;
                            }
                        }
                    }
                }
            }
        }
        return $response;
    }

    /**
     * [e2pdf-filter] shortcode
     * 
     * @param array $atts - Attributes
     * @param string $value - Content
     */
    function e2pdf_filter($atts = array(), $value = '') {
        if ($value) {
            $search = array('[', ']', '&#091;', '&#093;');
            $replace = array('&#91;', '&#93;', '&#91;', '&#93;');
            $value = str_replace($search, $replace, $value);
            $value = esc_attr($value);
        }
        return $value;
    }

}
