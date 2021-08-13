<?php

/**
 * E2pdf License Controller
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

class Controller_E2pdf_License extends Helper_E2pdf_View {

    /**
     * @url admin.php?page=e2pdf-license
     */
    public function index_action() {

        $this->load_scripts();
        $this->load_styles();
        if ($this->helper->get('license')->get('error')) {
            $this->add_notification('error', $this->helper->get('license')->get('error'));
        }
        $this->view('license', $this->helper->get('license'));
    }

    /**
     * Load javascript on license page
     */
    public function load_scripts() {

        wp_enqueue_script('jquery-ui-dialog');
    }

    /**
     * Load style on license page
     */
    public function load_styles() {

        wp_enqueue_style('plugin_name-admin-ui-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css', false, false, false);
    }

    /**
     * Change license key via ajax
     * action: wp_ajax_e2pdf_license_key
     * function: e2pdf_license_key
     * 
     * @return json
     */
    public function ajax_change_license_key() {

        $data = $this->post->get('data');

        $model_e2pdf_api = new Model_E2pdf_Api();
        $model_e2pdf_api->set(array(
            'action' => 'license/update',
            'data' => array(
                'license_key' => trim($data['license_key'])
            )
        ));
        $request = $model_e2pdf_api->request();

        if (isset($request['error'])) {
            $this->add_notification('error', $request['error']);
        } else {
            $this->add_notification('update', __('License Key updated successfully', 'e2pdf'));
        }

        $response = array(
            'redirect' => $this->helper->get_url(array(
                'page' => 'e2pdf-license',
                    )
            )
        );
        $this->json_response($response);
    }

}
