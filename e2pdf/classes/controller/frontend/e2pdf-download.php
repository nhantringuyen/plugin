<?php

/**
 * E2pdf Frontend Download Controller
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

class Controller_Frontend_E2pdf_Download extends Helper_E2pdf_View {

    /**
     * Frontend download action
     * 
     * @url page=e2pdf-download&uid=$uid
     */
    public function index_action() {
        global $wp_query;

        $name = '';
        $download = false;
        $uid = $this->get->get('uid');

        $entry = new Model_E2pdf_Entry();
        if ($entry->load_by_uid($uid)) {

            $uid_params = $entry->get('entry');
            $template = new Model_E2pdf_Template();

            if (isset($uid_params['pdf']) && $uid_params['pdf'] && file_exists($uid_params['pdf']) && !isset($uid_params['template_id']) && !isset($uid_params['dataset'])) {

                $download = true;
                $disposition = 'attachment';
                if (isset($uid_params['inline']) && $uid_params['inline']) {
                    $disposition = 'inline';
                }

                if (isset($uid_params['name']) && $uid_params['name']) {
                    $name = $uid_params['name'];
                } else {
                    $name = basename($uid_params['pdf'], ".pdf");
                }

                $file = base64_encode(file_get_contents($uid_params['pdf']));
                $this->download_response('pdf', $file, $name, $disposition);
            } elseif (isset($uid_params['template_id']) && isset($uid_params['dataset']) && $template->load($uid_params['template_id'])) {

                $template->extension()->set('dataset', $uid_params['dataset']);

                if (isset($uid_params['user_id'])) {
                    $template->extension()->set('user_id', $uid_params['user_id']);
                }

                if (isset($uid_params['args'])) {
                    $template->extension()->set('args', $uid_params['args']);
                }

                $template->extension()->set('uid', $uid);

                if ($template->get('actions')) {
                    $model_e2pdf_action = new Model_E2pdf_Action();
                    $model_e2pdf_action->load($template->extension());
                    $actions = $model_e2pdf_action->process_global_actions($template->get('actions'));

                    foreach ($actions as $action) {
                        if (isset($action['action']) && $action['action'] == 'access_by_url' && !isset($action['success'])) {
                            $error_message = __('Access Denied!', 'e2pdf');
                            if (isset($action['error_message']) && $action['error_message']) {
                                $error_message = $template->extension()->render($action['error_message']);
                            }
                            wp_die($error_message);
                            die();
                        }
                    }
                }

                if (isset($uid_params['flatten'])) {
                    $template->set('flatten', $uid_params['flatten']);
                }

                if (isset($uid_params['format'])) {
                    $template->set('format', $uid_params['format']);
                }

                if (isset($uid_params['password'])) {
                    $template->set('password', $uid_params['password']);
                } else {
                    $template->set('password', $template->extension()->render($template->get('password')));
                }

                if (isset($uid_params['meta_title'])) {
                    $template->set('meta_title', $uid_params['meta_title']);
                } else {
                    $template->set('meta_title', $template->extension()->render($template->get('meta_title')));
                }

                if (isset($uid_params['meta_subject'])) {
                    $template->set('meta_subject', $uid_params['meta_subject']);
                } else {
                    $template->set('meta_subject', $template->extension()->render($template->get('meta_subject')));
                }

                if (isset($uid_params['meta_author'])) {
                    $template->set('meta_author', $uid_params['meta_author']);
                } else {
                    $template->set('meta_author', $template->extension()->render($template->get('meta_author')));
                }

                if (isset($uid_params['meta_keywords'])) {
                    $template->set('meta_keywords', $uid_params['meta_keywords']);
                } else {
                    $template->set('meta_keywords', $template->extension()->render($template->get('meta_keywords')));
                }

                if (isset($uid_params['name'])) {
                    $template->set('name', $uid_params['name']);
                } else {
                    $template->set('name', $template->extension()->render($template->get('name')));
                }

                $disposition = 'attachment';
                if ((isset($uid_params['inline']) && $uid_params['inline']) || $template->get('inline')) {
                    $disposition = 'inline';
                }

                if (isset($uid_params['pdf']) && file_exists($uid_params['pdf'])) {

                    $download = true;

                    $entry->set('pdf_num', $entry->get('pdf_num') + 1);
                    $entry->save();

                    if ($template->get('name')) {
                        $name = $template->get('name');
                    } else {
                        $name = $template->extension()->render($template->get_filename());
                    }

                    if (!$name) {
                        $name = basename($uid_params['pdf'], ".pdf");
                    }

                    $file = base64_encode(file_get_contents($uid_params['pdf']));
                    $this->download_response('pdf', $file, $name, $disposition);
                } elseif ($template->extension()->verify()) {

                    $template->fill($uid_params['dataset'], $uid);
                    $request = $template->render();

                    if (isset($request['error'])) {
                        wp_die($request['error']);
                    } elseif ($request['file'] === '') {
                        wp_die(__('Something went wrong!', 'e2pdf'));
                    } else {
                        $download = true;

                        $entry->set('pdf_num', $entry->get('pdf_num') + 1);
                        $entry->save();

                        if ($template->get('name')) {
                            $name = $template->get('name');
                        } else {
                            $name = $template->extension()->render($template->get_filename());
                        }

                        $file = $request['file'];
                        $this->download_response('pdf', $file, $name, $disposition);
                    }
                }
            }
        }

        if (!$download) {
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
        }
    }

}
