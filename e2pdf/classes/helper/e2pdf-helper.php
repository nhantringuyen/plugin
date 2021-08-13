<?php

/**
 * E2pdf Helper
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

class Helper_E2pdf_Helper {

    protected static $instance = NULL;
    private $helper;

    const CHMOD_DIR = 0755;
    const CHMOD_FILE = 0644;

    public static function instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Set option by Key
     * 
     * @param string $key - Key of option
     * @param mixed $value - Value of option
     */
    public function set($key, $value) {
        if (!$this->helper) {
            $this->helper = new stdClass();
        }
        $this->helper->$key = $value;
    }

    /**
     * Add value to option by Key
     * 
     * @param string $key - Key of option
     *  @param mixed $value - Value of option
     */
    public function add($key, $value) {
        if (!$this->helper) {
            $this->helper = new stdClass();
        }

        if (isset($this->helper->$key)) {
            if (is_array($this->helper->$key)) {
                array_push($this->helper->$key, $value);
            }
        } else {
            $this->helper->$key = array();
            array_push($this->helper->$key, $value);
        }
    }

    /**
     * Unset option
     * 
     * @param string $key - Key of option
     */
    public function deset($key) {
        if (isset($this->helper->$key)) {
            unset($this->helper->$key);
        }
    }

    /**
     * Set option
     * 
     * @param string $key - Key of option
     * 
     * @return mixed - Get value of option by Key
     */
    public function get($key) {
        if (isset($this->helper->$key)) {
            return $this->helper->$key;
        } else {
            return '';
        }
    }

    /**
     * Get url path
     * 
     * @param string $url - Url path
     * 
     * @return string - Url path
     */
    public function get_url_path($url) {
        return plugins_url($url, $this->get('plugin_file_path'));
    }

    /**
     * Get url
     * 
     * @param array $data - Array list of url params
     * @param string $prefix -  Prefix of url
     * 
     * @return string Url
     */
    public function get_url($data = array(), $prefix = 'admin.php?') {
        $url = $prefix . http_build_query($data);
        return admin_url($url);
    }

    /**
     * Get Ip address
     * 
     * @return mixed - IP address or FALSE
     */
    public function get_ip() {
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = false;
        }
        return $ip;
    }

    /**
     * Remove dir and its content
     * 
     * @param string $dir - Path of directory to remove
     */
    public function delete_dir($dir) {
        if (!is_dir($dir)) {
            return;
        }
        if (substr($dir, strlen($dir) - 1, 1) != '/') {
            $dir .= '/';
        }
        $files = glob($dir . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->delete_dir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dir);
    }

    public function create_dir($dir = false) {
        if ($dir && !file_exists($dir)) {
            if (mkdir($dir, self::CHMOD_DIR)) {
                $index = $dir . 'index.php';
                $this->create_file($index, "<?php\n// Silence is golden.\n?>");
            }
        }
        return is_dir($dir);
    }

    public function create_file($file = false, $content = '') {
        if ($file && !file_exists($file)) {
            if (file_put_contents($file, $content)) {
                chmod($file, self::CHMOD_FILE);
            }
        }
        return is_file($file);
    }

    public function get_upload_url($path = false) {
        $wp_upload_dir = wp_upload_dir();
        if ($path) {
            return $wp_upload_dir['baseurl'] . "/" . basename(untrailingslashit($this->get('upload_dir'))) . "/" . $path;
        } else {
            return $wp_upload_dir['baseurl'] . "/" . basename(untrailingslashit($this->get('upload_dir')));
        }
    }

    /**
     * Get Capabilities
     * 
     * @return array()
     */
    public function get_caps() {
        $caps = array(
            'e2pdf' => array(
                'name' => __('Export', 'e2pdf'),
                'cap' => 'e2pdf',
            ),
            'e2pdf_templates' => array(
                'name' => __('Templates', 'e2pdf'),
                'cap' => 'e2pdf_templates'
            ),
            'e2pdf_settings' => array(
                'name' => __('Settings', 'e2pdf'),
                'cap' => 'e2pdf_settings'
            ),
            'e2pdf_license' => array(
                'name' => __('License', 'e2pdf'),
                'cap' => 'e2pdf_license'
            ),
            'e2pdf_debug' => array(
                'name' => __('Debug', 'e2pdf'),
                'cap' => 'e2pdf_debug'
            )
        );
        return $caps;
    }

    /**
     * Load sub-helper
     * 
     * @return object
     */
    public function load($helper) {
        $model = null;
        $class = "Helper_E2pdf_" . ucfirst($helper);
        if (class_exists($class)) {
            if (!$this->get($class)) {
                $this->set($class, new $class());
            }
            $model = $this->get($class);
        }
        return $model;
    }

}
