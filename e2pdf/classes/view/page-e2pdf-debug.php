<?php
if (!defined('ABSPATH')) {
    die('Access denied.');
}
?>
<div class="wrap <?php echo $this->page; ?>">
    <h1><?php _e('Debug', 'e2pdf') ?></h1>
    <hr class="wp-header-end">
    <h3 class="nav-tab-wrapper wp-clearfix">
        <a href="<?php echo $this->helper->get_url(array('page' => 'e2pdf-debug')); ?>" class="nav-tab <?php if (!$this->get->get()) { ?>nav-tab-active<?php } ?>"><?php echo _e('Debug', 'e2pdf'); ?></a>
        <a href="<?php echo $this->helper->get_url(array('page' => 'e2pdf-debug', 'action' => 'db')); ?>" class="nav-tab <?php if ($this->get->get('action') == 'db') { ?>nav-tab-active<?php } ?>"><?php _e('DB', 'e2pdf'); ?></a>
        <a href="<?php echo $this->helper->get_url(array('page' => 'e2pdf-debug', 'action' => 'phpinfo')); ?>" class="nav-tab <?php if ($this->get->get('action') == 'phpinfo') { ?>nav-tab-active<?php } ?>"><?php _e('PHP Info', 'e2pdf'); ?></a>
        <a href="<?php echo $this->helper->get_url(array('page' => 'e2pdf-debug', 'action' => 'requests')); ?>" class="nav-tab <?php if ($this->get->get('action') == 'requests') { ?>nav-tab-active<?php } ?>"><?php _e('Requests', 'e2pdf'); ?></a>
    </h3>

    <div class="wrap">
        <?php if (!$this->get->get()) { ?>
            <ul class="e2pdf-view-area">
                <li><h2><?php _e('Common', 'e2pdf') ?></h2></li>
                <li><span class="e2pdf-bold"><?php _e('Domain', 'e2pdf') ?>:</span> <?php echo $this->view->api->get_domain(); ?></li>
                <li><span class="e2pdf-bold"><?php _e('PHP Version', 'e2pdf') ?>:</span> <?php echo phpversion(); ?></li>
                <li><span class="e2pdf-bold"><?php _e('WP Version', 'e2pdf') ?>:</span> <?php echo get_bloginfo('version'); ?></li>
                <li><span class="e2pdf-bold"><?php _e('Multisite', 'e2pdf') ?>:</span> <?php is_multisite() ? _e('Yes', 'e2pdf') : _e('No', 'e2pdf'); ?></span></li>
                <li><span class="e2pdf-bold"><?php _e('Is Main Site', 'e2pdf') ?>:</span> <?php is_main_site() ? _e('Yes', 'e2pdf') : _e('No', 'e2pdf'); ?></span></li>
                <li><h2><?php _e('Folders', 'e2pdf') ?></h2></li>
                <li><span class="e2pdf-bold"><?php _e('WP Folder', 'e2pdf') ?>:</span></li>
                <li><?php echo ABSPATH ?></li>
                <li><span class="e2pdf-bold"><?php _e('Plugin Folrder', 'e2pdf') ?>:</span></li>
                <li><?php echo $this->helper->get('plugin_dir'); ?></li>
                <li><span class="e2pdf-bold"><?php _e('Folders permission', 'e2pdf') ?>:</span></li>
                <li><span class="<?php echo is_writable($this->helper->get('tmp_dir')) ? 'e2pdf-color-green' : 'e2pdf-color-red'; ?>"><?php echo $this->helper->get('tmp_dir'); ?></span></li>
                <li><span class="<?php echo is_writable($this->helper->get('pdf_dir')) ? 'e2pdf-color-green' : 'e2pdf-color-red'; ?>"><?php echo $this->helper->get('pdf_dir'); ?></span></li>
                <li><h2><?php _e('PHP Extensions', 'e2pdf') ?></h2></li>
                <li><span class="<?php echo function_exists('curl_version') ? 'e2pdf-color-green' : 'e2pdf-color-red'; ?>"><?php _e('CURL', 'e2pdf') ?></span>
                    <span class="<?php echo extension_loaded('simplexml') ? 'e2pdf-color-green' : 'e2pdf-color-red'; ?>"><?php _e('SIMPLEXML', 'e2pdf') ?></span>
                    <span class="<?php echo extension_loaded('libxml') ? 'e2pdf-color-green' : 'e2pdf-color-red'; ?>"><?php _e('LIBXML', 'e2pdf') ?></span>
                    <span class="<?php echo extension_loaded('Dom') ? 'e2pdf-color-green' : 'e2pdf-color-red'; ?>"><?php _e('DOM', 'e2pdf') ?></span>
                </li>
                <li><h2><?php _e('Plugins', 'e2pdf') ?>:</h2></li>
                <li>
                    <?php echo implode(", ", get_option('active_plugins')); ?>
                </li>
            </ul>
        <?php } elseif ($this->get->get('action') == 'db') { ?>
            <div class="e2pdf-view-area">
                <?php foreach ($this->view->db_structure as $table_key => $table) { ?>
                    <ul>
                        <li><span class="e2pdf-bold <?php echo isset($table['check']) && $table['check'] ? 'e2pdf-color-green' : 'e2pdf-color-red' ?>"><?php echo $table_key; ?></span></li>
                        <li>
                            <?php foreach ($table['columns'] as $column_key => $column) { ?>
                                <span class="<?php echo isset($column['check']) && $column['check'] ? 'e2pdf-color-green' : 'e2pdf-color-red' ?>"><?php echo $column_key; ?></span>
                            <?php } ?>
                        </li>

                    </ul>
                <?php } ?>
            </div>
        <?php } elseif ($this->get->get('action') == 'phpinfo') { ?>
            <div class="e2pdf-view-area">
                <div class="phpinfo_wrapper">
                    <?php echo $this->view->phpinfo; ?>
                </div>
            </div>
        <?php } elseif ($this->get->get('action') == 'requests') { ?>
            <div class="e2pdf-view-area">
                <?php echo $this->view->requests; ?>
            </div>
        <?php } ?>
    </div>
</div>
<?php $this->render('blocks', 'debug-panel'); ?>


