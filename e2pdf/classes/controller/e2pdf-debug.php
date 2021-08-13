<?php

/**
 * E2pdf Debug Controller
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

class Controller_E2pdf_Debug extends Helper_E2pdf_View {

    /**
     * @url admin.php?page=e2pdf-debug
     */
    public function index_action() {
        $this->view('api', new Model_E2pdf_Api());
    }

    /**
     * @url admin.php?page=e2pdf-debug&action=db
     */
    public function db_action() {
        global $wpdb;

        $db_structure = $this->get_db_structure();
        foreach ($db_structure as $table_key => $table) {
            $condition = array(
                'TABLE_SCHEMA' => array(
                    'condition' => '=',
                    'value' => DB_NAME,
                    'type' => '%s'
                ),
                'TABLE_NAME' => array(
                    'condition' => '=',
                    'value' => $wpdb->prefix . $table_key,
                    'type' => '%s'
                ),
            );
            $where = $this->helper->load('db')->prepare_where($condition);

            $table_exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES " . $where['sql'] . "", $where['filter']));
            if ($table_exists) {

                $db_structure[$table_key]['check'] = true;
                $table_columns = $wpdb->get_results($wpdb->prepare("SELECT `COLUMN_NAME` FROM INFORMATION_SCHEMA.COLUMNS " . $where['sql'] . "", $where['filter']), ARRAY_A);

                foreach ($table_columns as $table_column) {
                    $table_column_name = isset($table_column['COLUMN_NAME']) ? $table_column['COLUMN_NAME'] : false;
                    if ($table_column_name) {
                        if (isset($table['columns'][$table_column_name])) {
                            $db_structure[$table_key]['columns'][$table_column_name]['check'] = true;
                        } else {
                            $db_structure[$table_key]['columns'][$table_column_name] = array(
                                'check' => false
                            );
                        }
                    }
                }
            }
        }

        $this->view('db_structure', $db_structure);
    }

    /**
     * @url admin.php?page=e2pdf-debug&action=phpinfo
     */
    public function phpinfo_action() {
        $this->view('phpinfo', $this->get_php_info());
    }

    /**
     * @url admin.php?page=e2pdf-debug&action=requests
     */
    public function requests_action() {
        $this->view('requests', $this->get_requests());
    }

    /**
     * Get debug model
     * 
     * @return object - Debug model
     */
    public function get_debug() {
        $model_e2pdf_debug = new Model_E2pdf_Debug();
        return $model_e2pdf_debug;
    }

    /**
     * Get phpinfo
     * 
     * @return string - PHP Info
     */
    public function get_php_info() {
        ob_start();
        phpinfo();
        $contents = ob_get_contents();
        ob_end_clean();
        $php_info = (str_replace("module_Zend Optimizer", "module_Zend_Optimizer", preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $contents)));
        return $php_info;
    }

    /**
     * Get phpinfo
     * 
     * @return string
     */
    public function get_requests() {

        $model_e2pdf_api = new Model_E2pdf_Api();
        $requests = "";
        $requests .= '<strong>common/debug</strong>';
        $model_e2pdf_api->set(array(
            'action' => 'common/debug',
        ));
        $requests .= '<pre>';
        $requests .= print_r($model_e2pdf_api->request(), true);
        $requests .= '</pre>';

        return $requests;
    }

    public function get_db_structure() {

        $db_structure = array(
            'e2pdf_templates' => array(
                'columns' => array(
                    'ID' => array(),
                    'uid' => array(),
                    'pdf' => array(),
                    'title' => array(),
                    'created_at' => array(),
                    'updated_at' => array(),
                    'flatten' => array(),
                    'compression' => array(),
                    'appearance' => array(),
                    'width' => array(),
                    'height' => array(),
                    'extension' => array(),
                    'item' => array(),
                    'format' => array(),
                    'dataset_title' => array(),
                    'button_title' => array(),
                    'inline' => array(),
                    'auto' => array(),
                    'name' => array(),
                    'password' => array(),
                    'meta_title' => array(),
                    'meta_subject' => array(),
                    'meta_author' => array(),
                    'meta_keywords' => array(),
                    'font' => array(),
                    'font_size' => array(),
                    'font_color' => array(),
                    'line_height' => array(),
                    'fonts' => array(),
                    'trash' => array(),
                    'activated' => array(),
                    'locked' => array(),
                    'author' => array(),
                    'actions' => array(),
                )
            ),
            'e2pdf_entries' => array(
                'columns' => array(
                    'ID' => array(),
                    'uid' => array(),
                    'entry' => array(),
                    'pdf_num' => array(),
                )
            ),
            'e2pdf_datasets' => array(
                'columns' => array(
                    'ID' => array(),
                    'extension' => array(),
                    'item' => array(),
                    'entry' => array(),
                )
            ),
            'e2pdf_pages' => array(
                'columns' => array(
                    'page_id' => array(),
                    'template_id' => array(),
                    'properties' => array(),
                    'actions' => array(),
                    'revision_id' => array(),
                )
            ),
            'e2pdf_elements' => array(
                'columns' => array(
                    'page_id' => array(),
                    'template_id' => array(),
                    'element_id' => array(),
                    'name' => array(),
                    'type' => array(),
                    'top' => array(),
                    'left' => array(),
                    'width' => array(),
                    'height' => array(),
                    'value' => array(),
                    'properties' => array(),
                    'actions' => array(),
                    'revision_id' => array(),
                )
            ),
            'e2pdf_revisions' => array(
                'columns' => array(
                    'revision_id' => array(),
                    'template_id' => array(),
                    'pdf' => array(),
                    'title' => array(),
                    'created_at' => array(),
                    'updated_at' => array(),
                    'flatten' => array(),
                    'compression' => array(),
                    'appearance' => array(),
                    'width' => array(),
                    'height' => array(),
                    'extension' => array(),
                    'item' => array(),
                    'format' => array(),
                    'dataset_title' => array(),
                    'button_title' => array(),
                    'inline' => array(),
                    'auto' => array(),
                    'name' => array(),
                    'password' => array(),
                    'meta_title' => array(),
                    'meta_subject' => array(),
                    'meta_author' => array(),
                    'meta_keywords' => array(),
                    'font' => array(),
                    'font_size' => array(),
                    'font_color' => array(),
                    'line_height' => array(),
                    'fonts' => array(),
                    'author' => array(),
                    'actions' => array(),
                )
            )
        );

        return $db_structure;
    }

}
