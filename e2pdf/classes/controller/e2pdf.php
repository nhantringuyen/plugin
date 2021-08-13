<?php

/**
 * E2pdf Controller
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

class Controller_E2pdf extends Helper_E2pdf_View {

    /**
     * @url admin.php?page=e2pdf
     */
    public function index_action() {

        $users_tmp = get_users(array(
            'fields' => array(
                'ID', 'user_login'
            )
        ));

        $users = array(
            '0' => __('--- Select ---', 'e2pdf')
        );
        foreach ($users_tmp as $user) {
            $users[$user->ID] = $user->user_login;
        }

        $this->view('users', $users);
    }

    /**
     * @url admin.php?page=e2pdf&action=export
     */
    public function export_action() {

        $template_id = (int) $this->get->get('template_id');
        $dataset_id = $this->get->get('dataset_id');
        $user_id = 0;

        if ($template_id && $dataset_id) {

            $template = new Model_E2pdf_Template();

            if ($template->load($template_id)) {

                if ($this->post->get()) {
                    foreach ($this->post->get('options') as $key => $value) {
                        if ($key == 'user_id') {
                            $user_id = $value;
                        } else {
                            $template->set($key, stripslashes_deep($value));
                        }
                    }
                }

                $uid = false;
                $uid_params = array();
                $uid_params['template_id'] = $template_id;
                $uid_params['dataset'] = $dataset_id;
                $uid_params['user_id'] = $user_id;

                $entry = new Model_E2pdf_Entry();
                $entry->set('entry', $uid_params);
                if ($entry->load_by_uid($entry->get('uid'))) {
                    $uid = $entry->get('uid');
                }

                $template->extension()->set('dataset', $dataset_id);
                $template->extension()->set('user_id', $user_id);

                $template->set('name', $template->extension()->render($template->get('name')));
                $template->set('password', $template->extension()->render($template->get('password')));
                $template->set('meta_title', $template->extension()->render($template->get('meta_title')));
                $template->set('meta_subject', $template->extension()->render($template->get('meta_subject')));
                $template->set('meta_author', $template->extension()->render($template->get('meta_author')));
                $template->set('meta_keywords', $template->extension()->render($template->get('meta_keywords')));

                $template->fill($dataset_id, $uid);
                $request = $template->render();

                if (isset($request['error'])) {
                    $this->add_notification('error', $request['error']);
                    $this->render('blocks', 'notifications');
                } else {
                    $filename = $template->get_filename();
                    $file = $request['file'];
                    $this->download_response('pdf', $file, $filename);
                }
            } else {
                $this->add_notification('error', __("Template can't be loaded", 'e2pdf'));
                $this->render('blocks', 'notifications');
            }
        } else {
            $this->error('404');
        }
    }

    /**
     * Get activated templates list
     * 
     * @return array() - Activated templates list
     */
    public function get_active_templates() {
        global $wpdb;
        $model_e2pdf_template = new Model_E2pdf_Template();

        $condition = array(
            'trash' => array(
                'condition' => '<>',
                'value' => '1',
                'type' => '%d'
            ),
            'activated' => array(
                'condition' => '=',
                'value' => '1',
                'type' => '%d'
            )
        );

        $order_condition = array(
            'orderby' => 'id',
            'order' => 'desc',
        );

        $where = $this->helper->load('db')->prepare_where($condition);
        $orderby = $this->helper->load('db')->prepare_orderby($order_condition);


        $templates = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $model_e2pdf_template->get_table() . $where['sql'] . $orderby . "", $where['filter']));
        $export_templates = array();

        $export_templates[] = array(
            'key' => '',
            'value' => __('--- Select ---', 'e2pdf')
        );

        if (!empty($templates)) {
            foreach ($templates as $key => $value) {
                $export_templates[] = array(
                    'key' => $value->ID,
                    'value' => $value->title
                );
            }
        }

        return $export_templates;
    }

    /**
     * Get entries for template
     * 
     * @return array() - Entries for template
     */
    public function get_datasets($template_id = false) {

        $datasets = array();

        $datasets[] = array(
            'key' => '',
            'value' => __('--- Select ---', 'e2pdf')
        );

        if ($template_id) {
            $template = new Model_E2pdf_Template();

            if ($template->load($template_id)) {
                $datasets_tmp = $template->extension()->
                        datasets(
                        $template->get('item'), $template->get('dataset_title')
                );

                if ($datasets_tmp && is_array($datasets_tmp)) {
                    $datasets = array_merge($datasets, $datasets_tmp);
                }
            }
        }

        return $datasets;
    }

    /**
     * Get options to overwrite on template
     * 
     * @return array() - List of options to overwrite
     */
    public function get_options($template_id = false) {

        $options = array();

        if ($template_id) {
            $template = new Model_E2pdf_Template();
            $template->load($template_id);
            $options['name'] = $template->get('name');
            $options['password'] = $template->get('password');
            $options['flatten'] = $template->get('flatten');
            $options['user_id'] = get_current_user_id();
        }

        return $options;
    }

    /**
     * Get templates list via ajax
     * action: wp_ajax_e2pdf_templates
     * function: e2pdf_templates
     * 
     * @return json - Templates list
     */
    public function ajax_templates() {

        $this->check_nonce($this->get->get('_nonce'), 'e2pdf_ajax');

        $template_id = (int) $this->post->get('data');
        $content = array();
        $content['datasets'] = $this->get_datasets($template_id);
        $content['options'] = $this->get_options($template_id);
        $content['url'] = $this->helper->get_url(array('page' => 'e2pdf-templates', 'action' => 'edit', 'id' => $template_id));

        $template = new Model_E2pdf_Template();
        if ($template->load($template_id)) {
            $content['delete_items'] = $template->extension()->method('delete_items');
        }

        $response = array(
            'content' => $content,
        );

        $this->json_response($response);
    }

    /**
     * Get entries list via ajax
     * action: wp_ajax_e2pdf_entry
     * function: e2pdf_entry
     * 
     * @return json - Entries list
     */
    public function ajax_dataset() {

        $this->check_nonce($this->get->get('_nonce'), 'e2pdf_ajax');

        $data = $this->post->get('data');

        $template_id = (int) $data['template'];
        $dataset_id = (int) $data['dataset'];

        $template = new Model_E2pdf_Template();

        $content = array(
            'export' => false,
            'view' => false,
            'delete_item' => false,
            'dataset' => false
        );

        if ($template->load($template_id)) {
            $dataset = $template->extension()->dataset($dataset_id);

            if ($dataset) {
                $content = array(
                    'export' => true,
                    'view' => isset($dataset->url) && $dataset->url ? true : false,
                    'delete_item' => $template->extension()->method('delete_item'),
                    'dataset' => $dataset
                );
            }
        }

        $response = array(
            'content' => $content,
        );

        $this->json_response($response);
    }

    public function ajax_delete_item() {

        $this->check_nonce($this->get->get('_nonce'), 'e2pdf_ajax');

        $data = $this->post->get('data');

        $template_id = (int) $data['template'];
        $dataset_id = (int) $data['dataset'];

        if (!$template_id || !$dataset_id) {
            return;
        }

        $template = new Model_E2pdf_Template();

        $action = false;
        if ($template->load($template_id)) {
            $action = $template->extension()->delete_item($template_id, $dataset_id);
        }

        if ($action) {
            $response = array(
                'redirect' => $this->helper->get_url(array(
                    'page' => 'e2pdf',
                    'template_id' => $template_id
                        )
                )
            );

            $this->add_notification('update', __('Dataset removed successfully', 'e2pdf'));
        } else {
            $response = array(
                'error' => __("Dataset can't be removed!", 'e2pdf')
            );
        }

        $this->json_response($response);
    }

    public function ajax_delete_items() {
        $this->check_nonce($this->get->get('_nonce'), 'e2pdf_ajax');

        $data = $this->post->get('data');
        $template_id = (int) $data['template'];


        if (!$template_id) {
            return;
        }

        $template = new Model_E2pdf_Template();

        $action = false;
        if ($template->load($template_id)) {
            $action = $template->extension()->delete_items($template_id);
        }

        if ($action) {
            $response = array(
                'redirect' => $this->helper->get_url(array(
                    'page' => 'e2pdf',
                    'template_id' => $template_id
                ))
            );

            $this->add_notification('update', __('Datasets removed successfully', 'e2pdf'));
        } else {
            $response = array(
                'error' => __("Datasets can't be removed!", 'e2pdf')
            );
        }

        $this->json_response($response);
    }

}
