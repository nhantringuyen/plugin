<?php

/**
 * E2pdf Properties Helper
 * 
 * @copyright  Copyright 2017 https://e2pdf.com
 * @license    GPL v2
 * @version    1
 * @link       https://e2pdf.com
 * @since      1.08.08
 */
if (!defined('ABSPATH')) {
    die('Access denied.');
}

class Helper_E2pdf_Properties {

    public function apply($field = array(), $value = '') {
        if ($value) {
            if (isset($field['properties']['nl2br']) && $field['properties']['nl2br']) {
                $value = nl2br($value);
            }
            if (isset($field['properties']['preg_pattern']) && $field['properties']['preg_pattern'] && isset($field['properties']['preg_replacement'])) {
                $value = $this->preg_replace($field['properties']['preg_pattern'], $field['properties']['preg_replacement'], $value);
            }
        }

        return $value;
    }

    public function preg_replace($pattern = '', $replacement = '', $value = '') {
        if ($pattern && $value) {
            return preg_replace($pattern, $replacement, $value);
        }
        return $value;
    }

}
