<?php

/**
 * E2pdf Post Helper
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

class Helper_E2pdf_Post {

    private $post = array();

    /**
     * On init
     * Assign $_POST params to $post
     */
    public function __construct() {
        $this->post = $_POST;
    }

    /**
     * Get value from $_POST
     * 
     * @param string $key - Key of $_POST
     * 
     * @return mixed
     */
    public function get($key = false) {
        if (!$key) {
            if (!empty($this->post)) {
                return $this->post;
            } else {
                return array();
            }
        } else {

            if (isset($this->post[$key])) {
                return $this->post[$key];
            } else {
                return null;
            }
        }
    }

}
