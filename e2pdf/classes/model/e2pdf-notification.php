<?php

/**
 * E2pdf Notification Helper
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

class Model_E2pdf_Notification extends Model_E2pdf_Model {

    /**
     * Add notification
     * 
     * @param string $type - Notification type
     * @param string $text - Notification text
     */
    public function add_notification($type, $text) {
        $notifications = get_transient('e2pdf_notifications');
        $notifications[] = array(
            'type' => $type,
            'text' => $text,
        );
        set_transient('e2pdf_notifications', $notifications);
    }

    /**
     * Get notifications
     * 
     * @return array - List of notifications
     */
    public function get_notifications() {
        $notifications = get_transient('e2pdf_notifications');
        if (!get_option('e2pdf_hide_warnings')) {

            if (!is_array($notifications)) {
                $notifications = array();
            }

            if ($this->helper->get('license')->get('status') == 'pre_expired') {
                array_unshift($notifications, array(
                    'type' => 'notice',
                    'text' => sprintf(__("Your E2Pdf License Key will expire at <strong>%s</strong>. Please click <a target='_blank' href='%s'>here</a> to renew it.", 'e2pdf'), $this->helper->get('license')->get('expire'), 'https://e2pdf.com/checkout/license/renew/' . get_option('e2pdf_license'))
                ));
            }

            if ($this->helper->get('license')->get('status') == 'expired') {
                array_unshift($notifications, array(
                    'type' => 'error',
                    'text' => sprintf(__("Your E2Pdf License Key has expired. Please click <a target='_blank' href='%s'>here</a> to renew it.", 'e2pdf'), 'https://e2pdf.com/checkout/license/renew/' . get_option('e2pdf_license'))
                ));
            }

            if ($this->helper->get('license')->get('type') == 'FREE') {
                array_unshift($notifications, array(
                    'type' => 'notice',
                    'text' => sprintf(__("You are using FREE license type. Up to 1 page and up to 1 template allowed. Please check <a target='_blank' href='%s'>%s</a> for upgrade options.", 'e2pdf'), 'https://e2pdf.com/price', 'https://e2pdf.com')
                ));
            }
        }

        set_transient('e2pdf_notifications', array());
        return $notifications;
    }

}
