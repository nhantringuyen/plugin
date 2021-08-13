<?php

/*
Plugin Name: WP Fulltext Search
Description: Implementing a true indexed full-text search over wordpress posts and metas without using any external indexing software.
Version: 1.18.35
Tested up to: 5.2.2
Author: Epsiloncool
Author URI: http://e-wm.org
License: GPL3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: wpfts_lang
Domain Path: /languages/
*/

/**
 *   Copyright 2013-2019 Epsiloncool
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 ******************************************************************************
 *  I am thank you for the help by buying PRO version of this plugin 
 *  at https://fulltextsearch.org/ 
 *  It will keep me working further on this useful product.
 ******************************************************************************
 * 
 *  @copyright 2013-2019
 *  @license GPL v3
 *  @version 1.18.35
 *  @package Wordpress Fulltext Search
 *  @author Epsiloncool <info@e-wm.org>
 */

/**
 * ACE code editor
 * 
 * BSD License
 * Source: https://github.com/ajaxorg/ace
 * 
 */

define('WPFTS_VERSION', '1.18.35');

require_once dirname(__FILE__).'/includes/wpfts_core.php';
require_once dirname(__FILE__).'/includes/wpfts_output.php';

global $wpfts_core;

$wpfts_core = new WPFTS_Core();

register_activation_hook(__FILE__, array(&$wpfts_core, 'activate_plugin'));
register_deactivation_hook(__FILE__, array(&$wpfts_core, 'deactivate_plugin'));

add_action( 'wpmu_new_blog', 'wpfts_activate_new_blog', 10, 6);
function wpfts_activate_new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta)
{
    global $wpdb, $wpfts_core;
 
	if (!function_exists('is_plugin_active_for_network')) {
		require_once(ABSPATH.'/wp-admin/includes/plugin.php');
	}	

    if (is_plugin_active_for_network(plugin_basename(__FILE__))) {
        $old_blog = $wpdb->blogid;
        switch_to_blog($blog_id);
        $wpfts_core->_activate_plugin();
        switch_to_blog($old_blog);
    }
}

function wpfts_add_front_styles() 
{
	global $wpfts_core;
	
	if (($wpfts_core) && (intval($wpfts_core->get_option('is_smart_excerpts')) != 0)) {
		//wp_enqueue_style( 'wpfts_front_styles', $wpfts_core->root_url.'/style/wpfts_front_styles.css', array(), WPFTS_VERSION);
		echo '<style type="text/css">'.$wpfts_core->ReadSEStylesMinimized().'</style>';
	}
}
add_action('wp_enqueue_scripts', 'wpfts_add_front_styles');

add_action('init', 'wpfts_init');
function wpfts_init()
{
	global $wpfts_core;
	
	if ((is_object($wpfts_core)) && (is_callable(array($wpfts_core, 'set_hooks')))) {
	
		$wpfts_core->set_hooks();

		if (is_admin()) {
			add_action('admin_menu', 'wpfts_admin_menu');
			add_filter('plugin_row_meta', 'wpfts_plugin_links', 10, 2);
		
			/*
			// Initial rebuild
			if (intval($wpfts_core->get_option('rebuild_time')) < 1) {
				$wpfts_core->rebuild_index();
			}
			*/
		
			load_plugin_textdomain( 'wpfts_lang', false, basename(dirname(__FILE__)).'/languages/');
		
			add_action('admin_enqueue_scripts', 'wpfts_enqueues');

			add_action('wp_ajax_wpftsi_submit_settings', array($wpfts_core, 'ajax_submit_settings'));
			add_action('wp_ajax_wpftsi_submit_settings2', array($wpfts_core, 'ajax_submit_settings2'));
			add_action('wp_ajax_wpftsi_submit_settings5', array($wpfts_core, 'ajax_submit_settings5'));
			add_action('wp_ajax_wpftsi_rebuild_step', array($wpfts_core, 'ajax_rebuild_step'));
			add_action('wp_ajax_wpftsi_ping', array($wpfts_core, 'ajax_ping'));
			add_action('wp_ajax_wpftsi_submit_testpost', array($wpfts_core, 'ajax_submit_testpost'));
			add_action('wp_ajax_wpftsi_submit_testsearch', array($wpfts_core, 'ajax_submit_testsearch'));
			add_action('wp_ajax_wpftsi_submit_rebuild', array($wpfts_core, 'ajax_submit_rebuild'));
			add_action('wp_ajax_wpftsi_switch_engine', array($wpfts_core, 'ajax_switch_engine'));
			add_action('wp_ajax_wpftsi_hide_notification', array($wpfts_core, 'ajax_hide_notification'));
			add_action('wp_ajax_wpftsi_se_style_preview', array($wpfts_core, 'ajax_se_style_preview'));
			add_action('wp_ajax_wpftsi_se_style_reset', array($wpfts_core, 'ajax_se_style_reset'));
		}
	}
}

function wpfts_custom_js()
{
	global $wpfts_core;
	
	$is_settings = $wpfts_core->is_wpfts_settings_page ? 1 : 0;

	?><script type="text/javascript">
		var wpfts_pid = "<?php echo $wpfts_core->getPid(); ?>";
		var wpfts_pingtimeout = <?php echo intval($wpfts_core->get_option('ping_period')) * 1000; ?>;
		var wpfts_root_url = "<?php echo $wpfts_core->root_url; ?>";
		var switch_caution_txt = <?php echo json_encode(__("The conversion process will take some time,\nduring which you should stay on this page of the browser.\n\nIf the progress value does not change for more than 2 minutes,\nrefresh the page manually.", 'wpfts_lang')); ?>;
		var wpfts_is_settings_screen = <?php echo $is_settings; ?>;
		document.wpfts_settings_main_page = '<?php echo 'admin.php?page=wpfts-options'; ?>';
	</script><?php

	$version = (defined('WP_DEBUG') && WP_DEBUG) ? time() : WPFTS_VERSION;

	$current_tab = isset($_GET['page']) ? $_GET['page'] : 'wpfts-options';
	if ($current_tab == 'wpfts-options-smart-excerpts') {
		?>
		<script type="text/javascript">
		var wpfts_se_styles_editor = null;
		jQuery(document).ready(function()
		{
			wpfts_se_styles_editor = ace.edit("wpfts_se_styles_editor");
			wpfts_se_styles_editor.setTheme("ace/theme/chrome");
			wpfts_se_styles_editor.session.setMode("ace/mode/css");
		});
		</script>
		<?php
	}
}
add_action('admin_head', 'wpfts_custom_js');

add_action('plugins_loaded', 'wpfts_load_plugin_textdomain');
function wpfts_load_plugin_textdomain() {
	load_plugin_textdomain( 'wpfts_lang', false, dirname(plugin_basename(__FILE__)).'/languages/');
}

function wpfts_plugin_links($links, $file)
{
	if (basename($file) == basename(__FILE__)) {
		$links[] = '<a href="admin.php?page=wpfts-options">'.__('Settings', 'wpfts_lang').'</a>';
	}
	return $links;
}

function wpfts_admin_menu()
{
	//$position = ( ++$GLOBALS['_wp_last_object_menu'] );
	$position = ( ++$GLOBALS['_wp_last_utility_menu'] );
	
	$parent_menu = add_menu_page(__('WP FullText Search', 'wpfts_lang'), __('Full Text Search', 'wpfts_lang'), 'manage_options', 'wpfts-options', 'wpfts_option_page', 'dashicons-search', $position);

	// Add submenus
	add_submenu_page('wpfts-options', __('Main Configuration', 'wpfts_lang'), __('Main Configuration', 'wpfts_lang'), 'manage_options', 'wpfts-options', 'wpfts_option_page');
	add_submenu_page('wpfts-options', __('Indexing Engine Settings', 'wpfts_lang'), __('Indexing Engine', 'wpfts_lang'), 'manage_options', 'wpfts-options-indexing-engine', 'wpfts_option_page');
	add_submenu_page('wpfts-options', __('Smart Excerpts', 'wpfts_lang'), __('Smart Excerpts', 'wpfts_lang'), 'manage_options', 'wpfts-options-smart-excerpts', 'wpfts_option_page');
	add_submenu_page('wpfts-options', __('Sandbox Area', 'wpfts_lang'), __('Sandbox Area', 'wpfts_lang'), 'manage_options', 'wpfts-options-sandbox-area', 'wpfts_option_page');
	//add_submenu_page('wpfts-options', __('Licensing', 'wpfts_lang'), __('Licensing', 'wpfts_lang'), 'manage_options', 'wpfts-options-licensing', 'wpfts_option_page');
	
	add_filter('plugin_action_links', 'wpfts_settings_link', 10, 2);
}

function wpfts_enqueues($hook_suffix)
{
	global $wpfts_core;

	$version = (defined('WP_DEBUG') && WP_DEBUG) ? time() : WPFTS_VERSION;

	wp_enqueue_style('wpfts_style', plugins_url('style/wpfts_main.css', __FILE__), array(), $version);
	wp_enqueue_script('wpfts_script', plugins_url('js/wpfts_script.js', __FILE__), array(), $version);
	
	$current_tab = isset($_GET['page']) ? $_GET['page'] : 'wpfts-options';
	if ($current_tab == 'wpfts-options-smart-excerpts') {
		//echo '<style type="text/css">'.$wpfts_core->ReadSEStylesMinimized().'</style>';
		wp_enqueue_script('wpfts_ace_script', plugins_url('classes/ace/ace.js', __FILE__), array(), $version);
	}


	$wpfts_core->set_is_settings_page();

	if ($wpfts_core->is_wpfts_settings_page) {

		// Remove welcome_message
		$wpfts_core->set_option('is_welcome_message', '');

		//wp_enqueue_style( 'wpfts_front_styles', plugins_url('style/wpfts_front_styles.css', __FILE__), array(), $version);
		
		wp_enqueue_style('wp-pointer');

		wp_enqueue_script('postbox');
		wp_enqueue_script('wp-pointer');
	}
}

function wpfts_settings_link($links, $file)
{
	$this_plugin = dirname(plugin_basename(dirname(__FILE__))) . '/fulltext-search.php';
	if ($file == $this_plugin) {
		$links[] = '<a href="admin.php?page=wpfts-options">' . __('Settings', 'wpfts_lang' ) . '</a>';
	}
	return $links;
}

function wpfts_option_page()
{
	global $wpfts_core;

	if (!current_user_can('manage_options')) {
		wp_die(__('Sorry, but you do not have permissions to change settings.', 'wpfts_lang'));
	}

	/* Make sure post was from this page */
	if (isset($_POST) && (count($_POST) > 0)) {
		check_admin_referer('wpfts-options');
	}

	$wpfts_core->set_option('is_welcome_message', '');
	
	require dirname(__FILE__).'/includes/templates/admin_options.php';
}

/**
 * Called when any post/page/etc updated or created
 * 
 * We need to reindex the post by this action
 */
function wpfts_save_post_action($post_id)
{
	wpfts_post_reindex($post_id);
}
add_action('save_post', 'wpfts_save_post_action', 99);

/**
 * Called when any post/page/etc was deleted
 * 
 * We need to delete the post from the index by this action
 */
function wpfts_deleted_post_action($post_id)
{
	wpfts_post_reindex($post_id);
}
add_action('after_delete_post', 'wpfts_deleted_post_action', 99);

function wpfts_post_reindex($post_id, $is_force_remove = false)
{
	global $wpfts_core;
	
	$res = $wpfts_core->reindex_post($post_id, $is_force_remove);
	
	if (!$res) {
		trigger_error('Error reindex post ID='.$post_id.': '.$wpfts_core->index_error, E_USER_NOTICE);
		return false;
	}
	
	return true;
}

/** Smart Excerpts filters */
add_filter('the_title', function($out)
{
	global $wpfts_core;

	if ($wpfts_core && is_a($wpfts_core, 'WPFTS_Core')) {
		$is_smart_excerpts = intval($wpfts_core->get_option('is_smart_excerpts'));
		if ($is_smart_excerpts != 0) {
			if ((is_search() && in_the_loop()) || ($wpfts_core->forced_se_query !== false)) {
				$post_id = get_the_ID();
				$ri = new WPFTS_Result_Item($post_id);
				return $ri->TitleText($out);
			}
		}
	}

	return $out;
});

add_filter('attachment_link', function($link, $post_id)
{
	global $wpfts_core;

	if ($wpfts_core && is_a($wpfts_core, 'WPFTS_Core')) {
		$is_smart_excerpts = intval($wpfts_core->get_option('is_smart_excerpts'));
		if ($is_smart_excerpts != 0) {
			if ((is_search() && in_the_loop()) || ($wpfts_core->forced_se_query !== false)) {
				$ri = new WPFTS_Result_Item($post_id);
				return $ri->TitleLink($link);
			}
		}
	}

	return $link;
}, 10, 2);

add_filter('page_link', function($link, $post_id, $leavename)
{
	global $wpfts_core;

	if ($wpfts_core && is_a($wpfts_core, 'WPFTS_Core')) {
		$is_smart_excerpts = intval($wpfts_core->get_option('is_smart_excerpts'));
		if ($is_smart_excerpts != 0) {
			if ((is_search() && in_the_loop()) || ($wpfts_core->forced_se_query !== false)) {
				$ri = new WPFTS_Result_Item($post_id);
				return $ri->TitleLink($link);
			}
		}
	}

	return $link;
}, 10, 3);

add_filter('post_type_link', function($link, $post_id, $leavename)
{
	global $wpfts_core;

	if ($wpfts_core && is_a($wpfts_core, 'WPFTS_Core')) {
		$is_smart_excerpts = intval($wpfts_core->get_option('is_smart_excerpts'));
		if ($is_smart_excerpts != 0) {
			if ((is_search() && in_the_loop()) || ($wpfts_core->forced_se_query !== false)) {
				$ri = new WPFTS_Result_Item($post_id);
				return $ri->TitleLink($link);
			}
		}
	}

	return $link;
}, 10, 3);

add_filter('get_the_excerpt', function($out)
{
	global $wpfts_core;

	if ($wpfts_core && is_a($wpfts_core, 'WPFTS_Core')) {
		$is_smart_excerpts = intval($wpfts_core->get_option('is_smart_excerpts'));
		if ($is_smart_excerpts != 0) {
			$post_id = get_the_ID();
			if (is_search() && in_the_loop()) {
				$ri = new WPFTS_Result_Item($post_id);
				$query = get_search_query(false);
				$out = '<div class="wpfts-result-item">'.$ri->Excerpt($query).'</div>';
				return $out;
			} elseif ($wpfts_core->forced_se_query !== false) {
				$ri = new WPFTS_Result_Item($post_id);
				$query = $wpfts_core->forced_se_query;
				$out = '<div class="wpfts-result-item">'.$ri->Excerpt($query).'</div>';
				return $out;
			} else {
				// Leave excerpt unchanged
			}
		}
	}

	return $out;
});
