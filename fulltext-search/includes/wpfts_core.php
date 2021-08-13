<?php

/**  
 * Copyright 2013-2019 Epsiloncool
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
 *  @package Wordpress Fulltext Search
 *  @author Epsiloncool <info@e-wm.org>
 */

require_once dirname(__FILE__).'/wpfts_index.php';
require_once dirname(__FILE__).'/wpfts_jx.php';
require_once dirname(__FILE__).'/wpfts_htmltools.php';
require_once dirname(__FILE__).'/wpfts_output.php';
require_once dirname(__FILE__).'/wpfts_result_item.php';
require_once dirname(__FILE__).'/wpfts_db_updates.php';

class WPFTS_Core
{
	protected $_index = null;
	
	protected $_pid = false;
	
	public $_wpfts_domain = 'https://fulltextsearch.org';
	public $_documentation_link = '/documentation';
	
	public $root_url;
	
	public $index_error = '';
	
	public $forced_se_query = false;

	public $tablist = array('vectors', 'words', 'index', 'docs');
	
	public $is_wpfts_settings_page = false;

	public $se_css_cache_timeout = 600;	// 10 min
	public $se_css_tr_name = 'wpfts_se_css_transient';
	
	public function __construct()
	{
		$this->_index = new WPFTS_Index();
		
		if (is_admin()) {
			add_action('admin_notices', array($this, 'admin_notices'));
		}

		$this->root_url = dirname(plugins_url('', __FILE__));

		$upds = $this->GetUpdates();

		if ($upds['is_new']) {
			// New installation
			$this->set_option('index_ready', 0);	// Disable index
			//$this->get_option('is_welcome_message', json_encode($upds['texts']));	// Welcome message
		} else {
			if ($upds['is_rebuild']) {
				// DB is outdated
				// Require DB update
				$this->set_option('index_ready', 0);	// Disable index
				$this->set_option('is_db_outdated', 1);	// Set outdated flag
			} else {
				$this->set_option('is_db_outdated', 0);	// Ok with DB version
			}
		}
	}
	
	function set_is_settings_page()
	{
		// If we are on the WPFTS Settings Pages?
		$screen = get_current_screen();
		$hook_suffix = !is_null($screen) ? $screen->id : '';

		$is_wpfts_settings_page = false;
		if (preg_match('~^full\-text\-search_page_wpfts\-options\-~', $hook_suffix)) {
			$is_wpfts_settings_page = true;
		} else {
			if ($hook_suffix == 'toplevel_page_wpfts-options') {
				$is_wpfts_settings_page = true;
			}
		}
		$this->is_wpfts_settings_page = $is_wpfts_settings_page;
	}

	function admin_notices()
	{
		if ((strlen($this->get_option('is_welcome_message')) > 0) && (!$this->is_wpfts_settings_page)) {
			// Welcome message
			$text = json_decode($this->get_option('is_welcome_message'), true);
			if (isset($text[0])) {
				$s = $text[0];
				$this->output_admin_notice($s, 'notice notice-success is-dismissible wpfts-notice', 'welcome_message');
			}
		}

		// Let's check if we have something to say!
		$text = json_decode($this->get_option('change_log'), true);

		if (is_array($text) && (count($text) > 0)) {
			$s = '<b>WP Fulltext Search</b> new changes:<br><br>';
			$a = array();
			foreach ($text as $k => $d) {
				$a[] = 'In the version <b>'.$k.'</b>:<br>'.$d;
			}
			$s .= implode('<br>', $a);
			$this->output_admin_notice($s, 'notice notice-success is-dismissible wpfts-notice', 'change_log');
		}

		if (intval($this->get_option('is_db_outdated'))) {
			if ($this->is_wpfts_settings_page) {
				// DB update required message (for internal pages)
				$s = __('<b style="color: red;">The plugin\'s database requires update.</b><br>It is necessary to rebuild the index to ensure the correct operation of the search engine. This may take some time.<br><br>Click <a href="#" class="btn_notify_start_indexing">here</a> to rebuild the search index now.', 'wpfts_lang');

				$this->output_admin_notice($s, 'notice notice-warning wpfts-notice', 'db_update');
			} else {
				// DB update required message (for ext pages)
				$s = __('<b style="color: red;">Attention!</b> <b>WP Fulltext Search</b> plugin requires your attention.<br><br>Please <a href="admin.php?page=wpfts-options">click HERE</a> to go to WPFTS Settings page.', 'wpfts_lang');

				$this->output_admin_notice($s, 'notice notice-warning wpfts-notice', 'db_update');
			}
		}
	
	}

	public function GetUpdates()
	{
		global $wpfts_db_updates;

		$current_version = $this->get_option('current_db_version');
		$actual_version = WPFTS_VERSION;

		if (strlen($current_version) > 0) {
			// Check if we have an actual version of the database
			$changes = array(
				'is_new' => false,
				'is_rebuild' => false,
				'db_changes' => array(),
				'text' => array(),
			);
			// Find the first update newer than current version
			$is_found = false;
			foreach ($wpfts_db_updates as $k_version => $data) {
				if (version_compare($k_version, $current_version, '>') && (!$is_found)) {
					$is_found = true;
				}
				if ($is_found) {
					// Ok, now iterate till the actual version, inclusive
					if (version_compare($k_version, $actual_version, '<=')) {
						if (isset($data['is_rebuild']) && ($data['is_rebuild'])) {
							$changes['is_rebuild'] = true;
						}
						if (isset($data['db_changes']) && (count($data['db_changes']) > 0)) {
							$changes['db_changes'] = array_merge($changes['db_changes'], $data['db_changes']);
						}
						if (isset($data['text']) && (strlen($data['text']) > 0)) {
							$changes['text'][$k_version] = $data['text'];
						}
					} else {
						// Stop collecting changes here
						break;
					}
				}
			}
			return $changes;
		} else {
			// No updates (just create a new database and reindex)
			return array(
				'is_new' => true,
				'is_rebuild' => true,
				'text' => array(__('<b style="color: red;">Congratulations!</b> <b>WP FullText Search plugin</b> has just been installed and successfully activated!<br><br>To complete the installation, we need to create the Search Index of your existing WP posts data. To start this process, simply go to the <a href="admin.php?page=wpfts-options">WPFTS Settings Page</a>', 'wpfts_lang')),
			);
		}
	}

	function output_admin_notice($text, $type = 'error', $n_id = '')
	{
		?>
	    <div class="<?php echo $type; ?>" data-notificationid="<?php echo $n_id; ?>"><p><?php echo $text; ?></p></div>
		<?php
	}

	function queue_admin_notice($text, $type = 'error')
	{

		ob_start();
		?>
	    <div class="<?php echo $type; ?>"><p><?php echo $text; ?></p></div>
		<?php
		$this->set_option('admin_message', ob_get_clean());
	}

	function network_actdeact($pfunction, $networkwide) 
	{
		global $wpdb;
	 
		if (function_exists('is_multisite') && is_multisite()) {
			// Multisite activation
			if ($networkwide) {
				$old_blog = $wpdb->blogid;
				$blogids = $wpdb->get_col('SELECT blog_id FROM '.$wpdb->blogs);
				foreach ($blogids as $blog_id) {
					switch_to_blog($blog_id);
					call_user_func($pfunction, $networkwide);
				}
				switch_to_blog($old_blog);
				return;
			}   
		}
		// One site activation
		call_user_func($pfunction, $networkwide);
	}
	 
	function activate_plugin($networkwide) 
	{
		$this->network_actdeact(array(&$this, '_activate_plugin'), $networkwide);
	}
	 
	function deactivate_plugin($networkwide) 
	{
		$this->network_actdeact(array(&$this, '_deactivate_plugin'), $networkwide);
	}

	public function _activate_plugin($networkwide = false)
	{
		if (!function_exists('register_post_status')) {
			deactivate_plugins(basename(dirname( __FILE__ )).'/'.basename (__FILE__));
			wp_die( __( "This plugin requires WordPress 3.0 or newer. Please update your WordPress installation to activate this plugin.", 'wpfts_lang' ));
		}

		if ((isset($_GET['action'])) && ($_GET['action'] == 'error_scrape')) {
			// Showing error
			echo __('Error: ', 'wpfts_lang').$this->get_option('activation_error');
			//$this->set_option('activation_error', '');
			
		} else {

			// Check db
			$this->_index->clearLog();

			$upds = $this->GetUpdates();

			if ($upds['is_new']) {
				// New installation
				$this->set_option('index_ready', 0);	// Disable index
				$this->set_option('is_welcome_message', json_encode($upds['text']));	// Welcome message
			} else {
				if ($upds['is_rebuild']) {
					// DB is outdated
					// Require DB update
					$this->set_option('index_ready', 0);	// Disable index
					$this->set_option('is_db_outdated', 1);	// Set outdated flag
				} else {
					$this->set_option('is_db_outdated', 0);	// Ok with DB version
				}

				if (isset($upds['text']) && (is_array($upds['text'])) && (count($upds['text']) > 0)) {
					$this->set_option('change_log', json_encode($upds['text']));
				}
			}

		}
	}
	
	public function _deactivate_plugin($networkwide = false) 
	{
		//
	}
	
	public function getPid()
	{
		if (!$this->_pid) {
			$this->_pid = sha1(time().uniqid());
		}
		
		return $this->_pid;
	}
	
	public function get_post_types()
	{
		$post_types = get_post_types('', 'objects');

		$z = array();
		foreach ($post_types as $k => $d) {
			$z[$k] = isset($d->labels->singular_name) ? $d->labels->singular_name : $k;
		}

		return $z;
	}
	
	public function get_cluster_types()
	{
		return $this->_index->getClusters();
	}

	protected function default_options() 
	{
		return array(
			'enabled' => 1,
			'autoreindex' => 1,
			'index_ready' => 0,
			'deflogic' => 0, // AND
			'minlen' => 3,
			'maxrepeat' => 80, // 80%
			'stopwords' => '',
			'epostype' => '',
			'cluster_weights' => serialize(array(
				'post_title' => 0.8,
				'post_content' => 0.5,
			)),
			'testpostid' => '',
			'testquery' => '',
			'tq_disable' => 0,
			'tq_nocache' => 1,
			'tq_post_status' => 'any',
			'tq_post_type' => 'any',
			'rebuild_time' => 0,
			'process_time' => '0|',
			'ping_period' => 30,
			'est_time' => '00:00:00',
			'internal_search_terms' => 1,
			'include_attachments' => 1,
			'deeper_search' => 0,
			'display_attachments' => 1,
			'is_welcome_message' => '',
			'current_db_version' => '',
			'is_db_outdated' => 0,
			'is_innodb' => 1,
			'mainsearch_orderby' => 'relevance',
			'mainsearch_order' => 'DESC',
			'is_smart_excerpts' => 1,
			'is_smart_excerpt_text' => 1,
			'is_show_score' => 1,
			'is_not_found_words' => 1,
			'optimal_length' => 300,
			'custom_se_css' => '_get_css_file_',
			
			'activation_error' => '',
			'admin_message' => '',
			'change_log' => '',
			'content_strip_tags' => 1,
		);
	}
	
	public function get_option($optname)
	{
		$defaults = $this->default_options();

		$v = get_option('wpfts_' . $optname, isset($defaults[$optname]) ? $defaults[$optname] : false);

		switch ($optname) {
			case 'epostype':
				$v = (strlen($v) > 0) ? @unserialize($v) : array();
				break;
			case 'cluster_weights':
				$v = (strlen($v) > 0) ? @unserialize($v) : array();
				// We have to have post_title and post_content
				if (!isset($v['post_title'])) {
					$v['post_title'] = 0.8;
				}
				if (!isset($v['post_content'])) {
					$v['post_content'] = 0.5;
				}
				break;
			case 'custom_se_css':
				if ($v == '_get_css_file_') {
					// Read from file
					$v = $this->ReadSEStyles();
				}
				break;
		}

		return $v;
	}

	public function set_option($optname, $value)
	{
		$defaults = $this->default_options();
		
		if (isset($defaults[$optname])) {
			// Allowed option
			$v = $value;
			switch ($optname) {
				case 'epostype':
				case 'cluster_weights':
					$v = serialize($value);
					break;
			}
			
			$option_name = 'wpfts_'.$optname;
			if (get_option($option_name, false) !== false) {
				update_option($option_name, $v);
			} else {
				add_option($option_name, $v, '', 'no');
			}			
			return true;
		} else {
			// Not allowed option
			return false;
		}
	}
	
	public function ReadSEStyles()
	{
		$style_fn = dirname(__FILE__).'/../style/wpfts_front_styles.css';
		if (is_file($style_fn) && file_exists($style_fn)) {
			return file_get_contents($style_fn);
		} else {
			return '/'.'* Unable to find default stylesheet file *'.'/';
		}
	}

	public function MinimizeSEStyle($buffer)
	{
		// Remove HTML (just in case!)
		$buffer = strip_tags($buffer);
		// Remove comments
		$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
		// Remove space after colons
		$buffer = str_replace(': ', ':', $buffer);
		// Remove whitespace
		$buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);

		return $buffer;
	}

	public function ReadSEStylesMinimized($force_reset = false)
	{
		$tr_name = $this->se_css_tr_name;
		$css = get_transient($tr_name);
		if (($css === false) || $force_reset) {
			// Ok, let's create new one
			$css = $this->MinimizeSEStyle($this->get_option('custom_se_css'));
			set_transient($tr_name, $css, $this->se_css_cache_timeout);
		}
		return $css;
	}

	public function checkAndSyncWPPosts()
	{
		return $this->_index->checkAndSyncWPPosts($this->get_option('rebuild_time'));
	}
	
	public function get_status()
	{
		$st = $this->_index->get_status();
		$st['est_time'] = $this->get_option('est_time');
		$st['enabled'] = $this->get_option('enabled');
		$st['index_ready'] = $this->get_option('index_ready');
		$st['autoreindex'] = $this->get_option('autoreindex');
		
		return $st;
	}
	
	public function rebuild_index($time = false)
	{
		if (!$time) {
			$time = time();
		}
		
		$this->set_option('rebuild_time', $time);
		
		return $this->checkAndSyncWPPosts();
	}
	
	public function indexerProcessState($process_id)
	{
		$time = time();
		$process_time = explode('|', $this->get_option('process_time'));
		$ping_period = intval($this->get_option('ping_period'));
		
		if ($process_id != $process_time[1]) {
			if ($process_time[0] + $ping_period * 4 > $time) {
				return 2;	// Other pid indexing goes
			} else {
				return 0;	// Free
			}
		} else {
			if ($process_time[0] + $ping_period * 2 > $time) {
				return 1;	// Our pid indexing goes
			} else {
				return 0;	// Free
			}
		}
		
		// Unreachable
		return 0;
	}
	
	public function split_to_words($str)
	{
		return $this->_index->split_to_words($str);
	}

	public function sql_select($search, &$wpq)
	{
		return $this->_index->sql_select($search, $wpq);
	}
	
	/**
	 * Insert, Update or Delete index record for specified post
	 * 
	 * @param int $post_id Post ID
	 * @return boolean Success or not
	 */
	public function reindex_post($post_id, $is_force_remove = false)
	{
		$post = get_post($post_id);
		if ($post && (!$is_force_remove)) {
			// Insert or update index record

			$chunks2 = $this->getPostChunks($post_id);
			
			$modt = $post->post_modified;
			$time = time();
			$build_time = $this->get_option('rebuild_time');
			$insert_id = $this->_index->updateIndexRecordForPost($post_id, $modt, $build_time, $time, 0);
			
			$this->_index->clearLog();
			$res = $this->_index->reindex($insert_id, $chunks2);
			$this->index_error = (!$res) ? 'Indexing error: ' . $this->_index->getLog() : '';
			
			return $res;
		} else {
			// Check if index record exists and delete it
			$this->_index->removeIndexRecordForPost($post_id);
			return true;
		}
	}

	public function GetDBPrefix()
	{
		return $this->_index->dbprefix();
	}

	public function contentStripTags($s)
	{
		return strip_tags($s);
	}

	public function getPostChunks($post_id)
	{
		$post = get_post($post_id);
		$chunks = array(
			'post_title' => $post->post_title,
			'post_content' => $post->post_content,
		);
		$chunks = apply_filters('wpfts_index_post', $chunks, $post);
		
		if ($this->get_option('content_strip_tags') != 0) {
			$chunks['post_content'] = $this->contentStripTags($chunks['post_content']);
		}

		return $chunks;
	}

	public function ajax_rebuild_step()
	{
		$jx = new WPFTS_jxResponse();
		
		if (($data = $jx->getData()) !== false) {
			
//$jx->console('ajax_rebuild_step! Time='.time());

			$process_id = $data['pid'];
			$status = $this->get_status();
			
			$st = $this->indexerProcessState($process_id);			
//$jx->console('indexerProcessState = '.$st);
			if ($st != 2) {
				// Allow to start indexer session
				// Set up lock
				$this->set_option('process_time', time() . '|' . $process_id);

				$build_time = $this->get_option('rebuild_time');

				$maxtime = 10;
				$start_ts = microtime(true);
				
				ignore_user_abort(true);
			
				$n = 0;
				while (microtime(true) - $start_ts < $maxtime) {
//$zt0 = microtime(true);
					$ids = $this->_index->getRecordsToRebuild(1000);
//$zt1 = microtime(true) - $zt0;
//$jx->console(count($ids).' '.$zt1);

//$jx->console('In while: count(ids)='.count($ids));
					if (count($ids) < 1) {
						break;
					}

					foreach ($ids as $item) {
						
						if (!(microtime(true) - $start_ts < $maxtime)) {
							break;
						}
						
						// Rebuild this record
						if ($item['tsrc'] == 'wp_posts') {

							// Check if locked and lock if not locked
							$post_id = $item['tid'];
//$jx->console('Check if post_id='.$post_id.' is locked');							
							if ($this->_index->lockUnlockedRecord($item['id'])) {
//$jx->console('Set build time to item_id='.$item['id']);
								// Set build time to prevent consecutive rebuilds in case of error
								$time = time();
								$this->_index->updateRecordData($item['id'], array(
									'build_time' => 9999,	// Special mark of possible error
									'update_dt' => date('Y-m-d H:i:s', $time),
									'force_rebuild' => 0,
								));

								// Record is locked, lets index it now
								$post = get_post($post_id);
								$modt = $post->post_modified;
								
								$chunks = $this->getPostChunks($post_id);
//$jx->console('Got post chunks');
//$jx->console($chunks);
								$this->_index->clearLog();
								$res = $this->_index->reindex($item['id'], $chunks);
								if (!$res) {
//$jx->console('Reindex FAILS: '.$this->_index->getLog());
									$jx->console('Indexing error: '.$this->_index->getLog());
								}
//$jx->console('Reindex OK');
								//$jx->console($this->_index->timelog);
								
								// Store some statistic
								$time = time();
								$this->_index->updateRecordData($item['id'], array(
									'tdt' => $modt,
									'build_time' => $build_time,
									'update_dt' => date('Y-m-d H:i:s', $time),
									'force_rebuild' => 0,
								));

								$this->_index->unlockRecord($item['id']);
							}
						}
						
						$n ++;
					}
					
					if ($n < 1) {
						break;
					}

				}

				$finish_ts = microtime(true);
				
				$jx->variable('code', 0);
				
				$status = $this->get_status();
				
				$est_seconds = $n > 0 ? intval((($finish_ts - $start_ts) * $status['n_pending']) / $n) : 0;
				
				$est_h = intval($est_seconds / 3600);
				$est_m = intval(($est_seconds - $est_h * 3600) / 60);
				$est_s = ($est_seconds - $est_h * 3600) % 60;
				$est_str = sprintf('%02d:%02d:%02d', $est_h, $est_m, $est_s);
				
				$this->set_option('est_time', $est_str);
				
				$status['est_time'] = $est_str;
				
				if ($status['n_pending'] > 0) {
					if (($finish_ts - $start_ts) < ($maxtime / 2)) {
						// Just a delay
						$this->set_option('process_time', '0|' . $process_id);
						$jx->variable('result', 10);
					} else {
						// There is something to index
						// Remove lock
						$this->set_option('process_time', '0|' . $process_id);
						$jx->variable('result', 5);	// Continue indexing
					}
				} else {
					// Nothing to index
					// Remove lock
					$this->set_option('process_time', '0|0');
					$jx->variable('result', 0);	// Indexing stopped, ping
					$this->set_option('index_ready', 1);
					$jx->variable('delay', 0);
				}
				
				$out = new WPFTS_Output();
				$jx->variable('status', $out->status_box(1, $status, true));
				
				$jx->console(sprintf(__('%s posts has been rebuilt', 'wpfts_lang'), $n));
			} else {
				// Unable to index
				$jx->variable('code', 1);
			}
			
		}
		
		echo $jx->getJSON();
		wp_die();
	}
	
	public function ajax_ping()
	{	
		$t0 = microtime(true);
		
		$jx = new WPFTS_jxResponse();
		
		if (($data = $jx->getData()) !== false) {
			
			$time = time();
			
			$process_id = $data['pid'];
			$status = $this->get_status();
			
			$st = $this->indexerProcessState($process_id);
			
			$jx->variable('code', 0);
			
			$out = new WPFTS_Output();
			$jx->variable('status', $out->status_box(1, $status, true));
			switch ($st) {
				case 2:
				case 1:
					// Other pid is indexing
					$jx->variable('result', 10);	// Just wait, ping
					break;
				case 0:
				default:
					// Indexer is free now, lets check what to do
					if ($status['n_pending'] > 0) {
						// There is something to index
						$jx->variable('result', 5);	// Start to index
					} else {
						// Nothing to index
						$jx->variable('result', 0);	// Indexing stopped, ping
					}
			}
			
			$jx->console('pong! '.(microtime(true) - $t0).' s');
		}
		
		echo $jx->getJSON();
		wp_die();
	}
	
	/**
	 * Save Main Configuration & Relevance Settings
	 */
	public function ajax_submit_settings()
	{
		$jx = new WPFTS_jxResponse();
		
		if (($data = $jx->getData()) !== false) {
			
			// Check nonce
			if (wp_verify_nonce($data['wpfts_options-nonce'], 'wpfts_options')) {
				
				//$jx->console($data);

				$e = array();

				$cluster_weights = array();	//$this->get_option('cluster_weights');
				
				foreach ($data as $k => $d) {
					if (preg_match('~^eclustertype_(.+)$~', $k, $m)) {
						$clname = $m[1];
						$clvalue = floatval($d);
						if ((is_numeric($d)) && ($clvalue >= 0) && ($clvalue <= 1.0)) {
							$cluster_weights[$clname] = $clvalue;
						} else {
							$e[] = array($k, sprintf(__('The weight value of cluster "%s" should be numeric value from 0.0 to 1.0', 'wpfts_lang'), $clname));
						}
					} elseif (preg_match('~^wpfts_(.+)$~', $k, $m)) {
						$key = $m[1];
						switch ($key) {
							case 'enabled':
							case 'autoreindex':
								$v = ($d) ? 1 : 0;
								$this->set_option($key, $v);
								break;
							case 'deflogic':
								$v = ($d) ? 1 : 0;
								$this->set_option($key, $v);
								break;
							case 'internal_search_terms':
								$v = ($d) ? 1 : 0;
								$this->set_option($key, $v);
								break;
							case 'deeper_search':
								$v = ($d) ? 1 : 0;
								$this->set_option($key, $v);
								break;
							case 'mainsearch_orderby':
							case 'mainsearch_order':
								$v = $d;
								$this->set_option($key, $v);
								break;
							default:
						}
					} else {
						// Not valid input name
					}
				}

				if (count($e) > 0) {

					$z = array();
					foreach ($e as $dd) {
						$z[] = '* ' . $dd[1];
					}
					$txt = __('There are errors', 'wpfts_lang') . ":\n\n" . implode("\n", $z);

					$jx->alert($txt);
				} else {
					// Validation passed!

					// We need to have post_title and post_content clusters even they are not set
					if (!isset($cluster_weights['post_title'])) {
						$cluster_weights['post_title'] = 0.8;
					}
					if (!isset($cluster_weights['post_content'])) {
						$cluster_weights['post_content'] = 0.5;
					}

					$this->set_option('cluster_weights', $cluster_weights);

					$jx->reload();
				}

			} else {
				$jx->alert(__('The form is outdated. Please refresh the page and try again.', 'wpfts_lang'));
			}
		}
		
		echo $jx->getJSON();
		wp_die();
	}
	
	/**
	 * Smart Excerpts options save
	 */
	public function ajax_submit_settings5()
	{
		$jx = new WPFTS_jxResponse();
		
		if (($data = $jx->getData()) !== false) {
			
			// Check nonce
			if (wp_verify_nonce($data['wpfts_options-nonce'], 'wpfts_options')) {
				
				//$jx->console($data);

				$e = array();

				foreach ($data as $k => $d) {
					if (preg_match('~^wpfts_(.+)$~', $k, $m)) {
						$key = $m[1];
						switch ($key) {
							case 'is_smart_excerpts':
							case 'is_smart_excerpt_text':
							case 'is_show_score':
							case 'is_not_found_words':
								$v = ($d) ? 1 : 0;
								$this->set_option($key, $v);
								break;
							case 'optimal_length':
								$optlen = intval($d);
								if (($optlen < 10) || ($optlen > 10240)) {
									$e[] = array($key, __('Optimal Length should be a number from 10 to 10240', 'wpfts_lang'));
								} else {
									$this->set_option($key, $optlen);
								}
								break;
							case 'se_styles':
								$this->set_option('custom_se_css', $d);
								$this->ReadSEStylesMinimized(true);	// Reset minimization cache
								break;
							default:
						}
					} else {
						// Not valid input name
					}
				}

				if (count($e) > 0) {

					$z = array();
					foreach ($e as $dd) {
						$z[] = '* ' . $dd[1];
					}
					$txt = __('There are errors', 'wpfts_lang') . ":\n\n" . implode("\n", $z);

					$jx->alert($txt);
				} else {
					// Validation passed!
					$jx->reload();
				}

			} else {
				$jx->alert(__('The form is outdated. Please refresh the page and try again.', 'wpfts_lang'));
			}
		}
		
		echo $jx->getJSON();
		wp_die();
	}

	/**
	 * Save Indexing Engine Settings
	 */
	public function ajax_submit_settings2()
	{
		$jx = new WPFTS_jxResponse();
		
		if (($data = $jx->getData()) !== false) {
			
			// Check nonce
			if (wp_verify_nonce($data['wpfts_options-nonce'], 'wpfts_options')) {
				
				//$jx->console($data);

				$e = array();

				$epostype = $this->get_option('epostype');
				$post_types = $this->get_post_types();
				
				foreach ($data as $k => $d) {
					if (preg_match('~^epostype_(.+)$~', $k, $m)) {
						$psname = $m[1];
						$psvalue = intval($d);
						if (isset($post_types[$psname])) {
							$epostype[$psname] = $psvalue ? 1 : 0;
						}
					} elseif (preg_match('~^wpfts_(.+)$~', $k, $m)) {
						$key = $m[1];
						switch ($key) {
							case 'maxrepeat':
								$v = intval($d);
								if (is_numeric($d) && ($v >= 0) && ($v <= 100)) {
									$this->set_option($key, $v);
								} else {
									$e[] = array($key, __('Maximum Word Frequency should be an integer number from 0 to 100', 'wpfts_lang'));
								}
								break;
							case 'minlen':
								$v = intval($d);
								if (is_numeric($d) && ($v >= 0) && ($v <= 50)) {
									$this->set_option($key, $v);
								} else {
									$e[] = array($key, __('Minimum Word Length should be an integer number from 0 to 50', 'wpfts_lang'));
								}
								break;
							case 'stopwords':
								$v = trim($d);
								$this->set_option($key, $v);
								break;
							case 'content_strip_tags':
								$v = ($d) ? 1 : 0;
								$this->set_option($key, $v);
								break;
							default:
						}
					} else {
						// Not valid input name
					}
				}

				if (count($e) > 0) {

					$z = array();
					foreach ($e as $dd) {
						$z[] = '* ' . $dd[1];
					}
					$txt = __('There are errors', 'wpfts_lang') . ":\n\n" . implode("\n", $z);

					$jx->alert($txt);
				} else {
					// Validation passed!
					$this->set_option('epostype', $epostype);
					$this->rebuild_index(time());
					$jx->reload();
				}

			} else {
				$jx->alert(__('The form is outdated. Please refresh the page and try again.', 'wpfts_lang'));
			}
		}
		
		echo $jx->getJSON();
		wp_die();
	}
	
	public function ajax_submit_testpost()
	{
		$jx = new WPFTS_jxResponse();
		
		if (($data = $jx->getData()) !== false) {
			if (wp_verify_nonce($data['wpfts_options-nonce'], 'wpfts_options')) {
				
				//$jx->console($data);
				
				$postid = trim($data['wpfts_testpostid']);

				$e = array();
				
				if (strlen($postid) < 1) {
					$e[] = array('testpostid', __('Please specify post ID', 'wpfts_lang'));
				} else {
					if (!is_numeric($postid)) {
						$e[] = array('testpostid', __('Please specify a number', 'wpfts_lang'));
					}
				}
				
				if (count($e) > 0) {
					$z = array();
					foreach ($e as $dd) {
						$z[] = '* ' . $dd[1];
					}
					$txt = __('There are errors', 'wpfts_lang') . ":\n\n" . implode("\n", $z);

					$jx->alert($txt);
				} else {
					
					$post_id = intval($postid);

					$o_title = sprintf(__('Results of Pre-indexing Filter Tester for Post ID = %s', 'wpfts_lang'), $post_id);
					
					// Looking for post ID
					$p = get_post($post_id);
					
					if ($p) {

						$index = $this->getPostChunks($post_id);
						
						if (is_array($index)) {
							
							ob_start();
							?>
							<table class="wpfts_testoutput">
							<tr>
								<th><?php echo __('Token', 'wpfts_lang'); ?></th>
								<th><?php echo __('Content', 'wpfts_lang'); ?></th>
							</tr>
							<?php
							$o_result = ob_get_clean();
							
							foreach ($index as $k => $d) {
								$o_result .= '<tr>';
								$o_result .= '<td><b>'.htmlspecialchars($k).'</b></td>';
								$o_result .= '<td>'.htmlspecialchars($d).'</td>';
								$o_result .= '</tr>';
							}
							
							ob_start();
							?>
							</table>
							<?php
							$o_result .= ob_get_clean();
							
						} else {
							// Wrong filter result
							$o_result = '<p>'.sprintf(__('Filter result is not array. Please read <a href="%s" target="_blank">documentation</a> to fix this error.', 'wpfts_lang'), $this->_wpfts_domain.$this->_documentation_link).'</p>';
						}
						
					} else {
						// Post not found
						$o_result = '<p>'.__('The post with specified ID is not found.', 'wpfts_lang').'</p>';
					}
					
					$this->set_option('testpostid', $postid);
					
					$output = '<hr>';
					$output .= '<h4>'.htmlspecialchars($o_title).'</h4>';
					$output .= $o_result;
					
					$jx->variable('code', 0);
					$jx->variable('text', $output);
				}
				
			} else {
				$jx->alert(__('The form is outdated. Please refresh the page and try again.', 'wpfts_lang'));
			}
		}
		echo $jx->getJSON();
		wp_die();
	}
	
	public function ajax_submit_testsearch()
	{
		$jx = new WPFTS_jxResponse();
		
		if (($data = $jx->getData()) !== false) {
			if (wp_verify_nonce($data['wpfts_options-nonce'], 'wpfts_options')) {
				
				//$jx->console($data);
				
				$query = trim($data['wpfts_testquery']);
				$tq_disable = $data['wpfts_tq_disable'];
				$tq_nocache = $data['wpfts_tq_nocache'];
				$tq_post_type = $data['wpfts_tq_post_type'];
				$tq_post_status = $data['wpfts_tq_post_status'];

				$current_page = max(1, isset($data['wpfts_tq_current_page']) ? intval($data['wpfts_tq_current_page']) : 0);
				$n_perpage = isset($data['wpfts_tq_n_perpage']) ? intval($data['wpfts_tq_n_perpage']) : 25;
				
				
				$e = array();
				
				if (strlen($query) < 1) {
					$e[] = array('testquery', __('Please specify search query', 'wpfts_lang'));
				}
				
				if (count($e) > 0) {
					$z = array();
					foreach ($e as $dd) {
						$z[] = '* ' . $dd[1];
					}
					$txt = __('There are errors', 'wpfts_lang') . ":\n\n" . implode("\n", $z);

					$jx->alert($txt);
				} else {
					
					$o_title = sprintf(__('Results of search for query = "%s"', 'wpfts_lang'), $query);
					
					$t0 = microtime(true);
					
					$wpq = new WP_Query(array(
						//'fields' => 'ids',
						'fields' => '*',
						's' => $query,
						'post_status' => 'any',
						//'nopaging' => true,
						'wpfts_disable' => $tq_disable ? 1 : 0,
						'wpfts_nocache' => $tq_nocache ? 1 : 0,
						'posts_per_page' => $n_perpage,
						'paged' => $current_page,
						'post_status' => $tq_post_status,
						'post_type' => $tq_post_type,
					));
					
					$t1 = microtime(true) - $t0;
					
					if (isset($GLOBALS['posts_clauses'])) {
						$jx->console($GLOBALS['posts_clauses']);
					}
					
					//$num = $wpq->have_posts() ? count($wpq->posts) : 0;
					$num = $wpq->found_posts;
					
					$o_result = '<p><i>'.sprintf(__('Time spent: <b>%.3f</b> sec', 'wpfts_lang'), $t1).'</i><br>';
					$o_result .= '</p>';
					
					global $post;
					
					$a = array();
					$n = ($current_page - 1) * $n_perpage + 1;
					while ( $wpq->have_posts() ) {
						$wpq->the_post();
						
						$relev = isset($post->relev) ? $post->relev : 0;
						$post = get_post($post->ID);
						setup_postdata($post);
						
						$tn = '';
						$post_tn = get_post_thumbnail_id($post->ID);
						if ($post_tn) {
							$large_image_url = wp_get_attachment_image_src($post_tn, 'thumbnail');
							if ( ! empty( $large_image_url[0] ) ) {
								$tn = '<img src="'.esc_url($large_image_url[0]).'" alt="" class="wpfts_table_img">';
							}
						}
						
						ob_start();
						the_excerpt();
						$exc = ob_get_clean();
						
						$a[] = array(
							'n' => $n ++,
							'ID' => $post->ID,
							'post_type' => $post->post_type,
							'post_title' => $post->post_title,
							'post_status' => $post->post_status,
							'tn' => $tn,
							'exc' => $exc,
							'relevance' => sprintf('%.2f', $relev * 100).'%',
						);
					}
					wp_reset_postdata();
					
					if (count($a) > 0) {
						
						$o_result .= '<p>'.$this->sandboxPaginator($current_page, $num, $n_perpage).'</p>';
						
						ob_start();
						?>
						<table class="wpfts_testoutput" style="width: 100%;">
						<tr>
							<th style="width: 10%;"><?php echo __('#', 'wpfts_lang'); ?></th>
							<th style="width: 10%;"><?php echo __('ID', 'wpfts_lang'); ?></th>
							<th style="width: 10%;"><?php echo __('Type', 'wpfts_lang'); ?></th>
							<th style="width: 10%;"><?php echo __('Status', 'wpfts_lang'); ?></th>
							<th style="width: 50%;"><?php echo __('Title, Thumbnail, Excerpt', 'wpfts_lang'); ?></th>
							<th style="width: 10%;"><?php echo __('Relevance', 'wpfts_lang'); ?></th>
						</tr>
						<?php
						$o_result .= ob_get_clean();
							
						foreach ($a as $d) {
							
							$content = '<div class="wpfts_tq_content"><div class="cont1">'.$d['tn'].'</div><div class="cont2"><b>'.htmlspecialchars($d['post_title']).'</b><br>'.$d['exc'].'</div></div>';
							
							$o_result .= '<tr>';
							$o_result .= '<td>'.$d['n'].'</td>';
							$o_result .= '<td><a href="/?p='.$d['ID'].'">'.$d['ID'].'</a></td>';
							$o_result .= '<td>'.$d['post_type'].'</td>';
							$o_result .= '<td>'.$d['post_status'].'</td>';
							$o_result .= '<td>'.$content.'</td>';
							$o_result .= '<td>'.$d['relevance'].'</td>';
							$o_result .= '</tr>';
						}
							
						ob_start();
						?>
						</table>
						<?php
						$o_result .= ob_get_clean();
						
						$o_result .= '<p>'.$this->sandboxPaginator($current_page, $num, $n_perpage).'</p>';
					} else {
						$o_result .= '<p><i>'.sprintf(__('Found: <b>%d</b> posts', 'wpfts_lang'), $num).'</i></p>';
					}
					
					$this->set_option('testquery', $query);
					$this->set_option('tq_disable', $tq_disable);
					$this->set_option('tq_post_type', $tq_post_type);
					$this->set_option('tq_post_status', $tq_post_status);
					
					$output = '<hr>';
					$output .= '<h4>'.htmlspecialchars($o_title).'</h4>';
					$output .= $o_result;
					
					$jx->variable('code', 0);
					$jx->variable('text', $output);
				}
				
			} else {
				$jx->alert(__('The form is outdated. Please refresh the page and try again.', 'wpfts_lang'));
			}
		}
		echo $jx->getJSON();
		wp_die();
	}
	
	public function ajax_submit_rebuild()
	{
		$jx = new WPFTS_jxResponse();
		
		if (($data = $jx->getData()) !== false) {
			if (wp_verify_nonce($data['wpfts_options-nonce'], 'wpfts_options')) {
				
				$this->set_option('index_ready', 0);
				$this->_index->create_db_tables();
				$this->rebuild_index(time());

				$jx->reload();
				
			} else {
				$jx->alert(__('The form is outdated. Please refresh the page and try again.', 'wpfts_lang'));
			}
		}
		echo $jx->getJSON();
		wp_die();
	}
	
	public function ajax_se_style_preview()
	{
		$jx = new WPFTS_jxResponse();
		
		if (($data = $jx->getData()) !== false) {

			$css = isset($data['wpfts_se_styles']) ? $data['wpfts_se_styles'] : '';
			// Minimize
			$c_css = $this->MinimizeSEStyle($css);

			$jx->variable('code', 0);
			$jx->variable('c_css', $c_css);
		}
		echo $jx->getJSON();
		wp_die();
	}

	public function ajax_se_style_reset()
	{
		$jx = new WPFTS_jxResponse();
		
		if (($data = $jx->getData()) !== false) {
			$def_styles = $this->ReadSEStyles();
			$c_css = $this->MinimizeSEStyle($def_styles);

			$jx->variable('code', 0);
			$jx->variable('c_css', $c_css);
			$jx->variable('css_data', $def_styles);
		}
		echo $jx->getJSON();
		wp_die();
	}

	public function sandboxPaginator($current_page, $total_items, $n_perpage)
	{
		$a_nn = array(
			10 => 10,
			25 => 25,
			50 => 50,
			100 => 100,
			250 => 250,
			500 => 500,
		);
		$sel_perpage = '<span class="wpfts_tq_perpage">'.WPFTS_HtmlTools::makeSelect($a_nn, $n_perpage, array('id' => 'wpfts_tq_n_perpage')).'&nbsp;'.__('posts per page', 'wpfts_lang').'</span>';
		
		$maxpage = ceil($total_items / $n_perpage);
		
		$a_pages = array();
		for ($i = 1; $i <= $maxpage; $i ++) {
			$a_pages[$i] = (($i - 1) * $n_perpage + 1).' - '.min($total_items, $i * $n_perpage);
		}
		
		$pager = '<span class="wpfts_tq_pager">';
		$pager .= '<button class="wpfts_tq_prevpage"'.(($current_page > 1) ? '' : ' disabled="disabled"').' type="button">&lt;&lt;</button>';
		$pager .= sprintf(__('Shown <span>%1s</span> from <b>%2s</b>', 'wpfts_lang'), WPFTS_HtmlTools::makeSelect($a_pages, $current_page, array('id' => 'wpfts_tq_current_page')), $total_items);
		$pager .= '<button class="wpfts_tq_nextpage"'.(($current_page < $maxpage) ? '' : ' disabled="disabled"').' type="button">&gt;&gt;</button>';
		$pager .= '</span>';
		
		return '<div class="wpfts_tq_pagerline">'.$pager.$sel_perpage.'<div style="clear: both;"></div></div>';
	}
	
	public function set_hooks()
	{
		add_action('pre_get_posts', array($this, 'index_pre_get_posts'), 10);
		add_filter('posts_search', array($this, 'index_sql_select'), 10, 2);
		add_filter('posts_join', array($this, 'index_sql_joins'), 10, 2);
		add_filter('posts_search_orderby', array($this, 'index_sql_orderby'), 10, 2);
		add_filter('the_posts', array($this, 'index_the_posts'), 10, 2);
		add_filter('posts_clauses', array($this, 'index_posts_clauses'), 10, 2);
		add_filter('posts_fields', array($this, 'index_posts_fields'), 10, 2);
		add_filter('posts_distinct', array($this, 'index_posts_distinct'), 10, 2);
/*
		add_action('posts_selection', function($s) {

			$wpud = wp_upload_dir();

			$log_fn = $wpud['path'] . '/wpfts_log.txt';

			file_put_contents($log_fn, date('Y-m-d H:i:s') . ' *** POSTS_SELECTION *******************' . "\n", FILE_APPEND);
			file_put_contents($log_fn, print_r($s, true) . "\n", FILE_APPEND);
			file_put_contents($log_fn, 'END POSTS_SELECTION %%%%%%%%%%%%%%%%%%%%' . "\n\n", FILE_APPEND);
		}, 1);
 */
	}

	function index_posts_clauses($clauses, $wpq)
	{
		if ((!isset($GLOBALS['posts_clauses'])) || (!is_array($GLOBALS['posts_clauses']))) {
			$GLOBALS['posts_clauses'] = array();
		}
		$GLOBALS['posts_clauses'][] = $clauses;
		
		return $clauses;
	}
	
	function index_posts_fields($fields, $wpq)
	{
		$is_disabled = intval($wpq->get('wpfts_disable', 0));
		if (!$is_disabled) {
			return $this->_index->sql_posts_fields($fields, $wpq);
		}
		return $fields;
	}
	
	function index_sql_joins($join, $wpq)
	{
		$is_disabled = intval($wpq->get('wpfts_disable', 0));
		if (!$is_disabled) {
			$t_rm = array();
			return $this->_index->sql_joins($join, $wpq, $t_rm);
		}
		return $join;
	}
	
	function index_sql_select($search, $wpq)
	{
		$wpud = wp_upload_dir();

		$log_fn = $wpud['path'] . '/wpfts_log.txt';

		$q = $wpq->query_vars;
/*
		file_put_contents($log_fn, date('Y-m-d H:i:s') . ' *** POSTS_SEARCH *******************' . "\n", FILE_APPEND);
		file_put_contents($log_fn, print_r($search, true) . "\n", FILE_APPEND);
		file_put_contents($log_fn, 'S: ' . print_r($q['s'], true) . "\n", FILE_APPEND);
		file_put_contents($log_fn, 'SENTENCE: ' . print_r($q['sentence'], true) . "\n", FILE_APPEND);
		file_put_contents($log_fn, 'SEARCH_TERMS_COUNT: ' . print_r($q['search_terms_count'], true) . "\n", FILE_APPEND);
		file_put_contents($log_fn, 'SEARCH_TERMS: ' . print_r($q['search_terms'], true) . "\n", FILE_APPEND);
		file_put_contents($log_fn, 'EXACT: ' . print_r($q['exact'], true) . "\n", FILE_APPEND);
		file_put_contents($log_fn, 'SEARCH_ORDERBY_TITLE: ' . print_r($q['search_orderby_title'], true) . "\n", FILE_APPEND);
		file_put_contents($log_fn, 'END POSTS_SEARCH ///////////////' . "\n\n", FILE_APPEND);
*/		
/*
			if (isset($wpfts_core) && (intval($wpfts_core->get_option('enabled')))) {
				return $wpfts_core->sql_select($search, $wpq);
			}
*/
		$is_disabled = intval($wpq->get('wpfts_disable', 0));
		if (!$is_disabled) {
			return $this->_index->sql_select($search, $wpq);
		}
		return $search;
	}
	
	function index_sql_orderby($orderby, $wpq)
	{
		$wpud = wp_upload_dir();

		$log_fn = $wpud['path'] . '/wpfts_log.txt';
/*
		file_put_contents($log_fn, date('Y-m-d H:i:s') . ' *** POSTS_SEARCH_ORDERBY *******************' . "\n", FILE_APPEND);
		file_put_contents($log_fn, print_r($orderby, true) . "\n", FILE_APPEND);
		file_put_contents($log_fn, 'END POSTS_SEARCH_ORDERBY ##############' . "\n\n", FILE_APPEND);
*/
		$is_disabled = intval($wpq->get('wpfts_disable', 0));
		if (!$is_disabled) {
			return $this->_index->sql_orderby($orderby, $wpq);
		}
		return $orderby;
	}
	
	function index_pre_get_posts(&$wpq)
	{
		$wpud = wp_upload_dir();

		$log_fn = $wpud['path'] . '/wpfts_log.txt';
/*
		file_put_contents($log_fn, date('Y-m-d H:i:s') . ' *** PRE_GET_POSTS *************************' . "\n", FILE_APPEND);
		file_put_contents($log_fn, print_r($wpq->query_vars, true) . "\n", FILE_APPEND);
		file_put_contents($log_fn, 'END PRE_GET_POSTS ****************************' . "\n\n", FILE_APPEND);
*/		
		// Set default values for WP_Query() parameters


		$is_wpfts_disabled = intval($wpq->get('wpfts_disable', intval($this->get_option('enabled')) ? 0 : 1));
		$wpq->set('wpfts_disable', $is_wpfts_disabled);

		$word_logic = mb_strtolower($wpq->get('word_logic', intval($this->get_option('deflogic')) ? 'or' : 'and'));
		$wpq->set('word_logic', $word_logic == 'and' ? 'and' : 'or');

		// Enable attachments in WP search (if enabled)
		if (!$is_wpfts_disabled) {

			// Check if we need to tweak main query
			if ($wpq->is_main_query() && !is_admin()) {
				$t = $wpq->get('orderby');
				if ((!is_array($t)) && (strlen(trim($t)) < 1)) {
					// Go tweak!
					$orderby = $this->get_option('mainsearch_orderby');
					$wpq->set('orderby', $orderby);
				}
				$t2 = $wpq->get('order');
				if ((!is_array($t2)) && (strlen(trim($t2)) < 1)) {
					// Go tweak!
					$order = $this->get_option('mainsearch_order');
					$wpq->set('order', $order);
				}
			}

			$cw = $wpq->get('cluster_weights', false);
			if ($cw === false) {
				$cw = $this->get_option('cluster_weights');
			}

			$cluster_weights = apply_filters('wpfts_cluster_weights', $cw);
			if (!is_array($cluster_weights)) {
				$cluster_weights = array();
			}
			$this->_index->sql_pre_posts($wpq, $cluster_weights);
		}
	}
	
	function index_the_posts($posts, $wpq)
	{
		$is_disabled = intval($wpq->get('wpfts_disable', 0));
		if (!$is_disabled) {
			return $this->_index->sql_the_posts($posts, $wpq);
		}
		return $posts;
	}
	
	function index_posts_distinct($distinct, $wpq)
	{
		$is_disabled = intval($wpq->get('wpfts_disable', 0));
		if (!$is_disabled) {
			return $this->_index->sql_posts_distinct($distinct, $wpq);
		}
		return $distinct;
	}

	function getTabList()
	{
		$pfx = $this->_index->dbprefix();

		$tt = array();
		foreach ($this->tablist as $dd) {
			$tt[] = $pfx.$dd;
		}
		return $tt;
	}

	function getTableTypeProgress() 
	{
		global $wpdb;

		$pfx = $this->_index->dbprefix();

		$tt = $this->getTabList();

		$target_type = intval($this->get_option('is_innodb')) ? 'innodb' : 'myisam';

		$q = 'show table status like "'.$pfx.'%"';
		$res = $wpdb->get_results($q, ARRAY_A);

		$r = array();
		$sum = 0;
		$nxt = '';
		foreach ($res as $d) {
			if (in_array($d['Name'], $tt)) {
				$eng = strtolower($d['Engine']);
				if ($eng == $target_type) {
					$r[] = array(1, $d['Name']);
					$sum ++;
				} else {
					$r[] = array(0, $d['Name']);
					$nxt = $d['Name'];
				}
			}
		}
		return array(
			'progress' => $sum / count($tt),
			'res' => $r,
			'next' => $nxt,
		);
	}

	function getCnvMessageHtml($is_processing = false)
	{
		$s = '';
		$status = 0;

		// New table type
		$new_type = intval($this->get_option('is_innodb')) ? 'MyISAM' : 'InnoDB';

		$et = intval($this->get_option('is_innodb')) ? 'InnoDB' : 'MyISAM';
		
		$s .= '<b>'.$et.'</b><br>';

		// Get actual table types
		$prg = $this->getTableTypeProgress();
		if ($prg['progress'] < 1) {
			// Converting
			if ($is_processing) {
				$s .= '<span>'.__('Switching', 'wpfts_lang').' ('.intval($prg['progress'] * 100).'%)...&nbsp;<img src="'.($this->root_url).'/style/waiting16.gif" alt="" title="'.(__('Switching is in progress', 'wpfts_lang')).'"></span><br>';
				$status = 1;
			} else {
				$s .= '<span style="color:red;">'.__('Switching was<br>not completed', 'wpfts_lang').' ('.intval($prg['progress'] * 100).'%)! </span><br>';
				$s .= '<span class="button-secondary wpfts_btn_switch_engine_finish">'.__('Finish Now', 'wpfts_lang').'</span>';
				$status = 2;
			}
		} else {
			$s .= '<span class="button-secondary wpfts_btn_switch_engine" data-type="'.$new_type.'">'.__('Switch to', 'wpfts_lang').' '.$new_type.'</span>';
			$status = 0;
		}
		
		return array(
			'html' => $s,
			'status' => $status,
		);
	}

	function convertOneTable($table_name) 
	{
		global $wpdb;

		// Target table type name
		$target_type = intval($this->get_option('is_innodb')) ? 'InnoDB' : 'MyISAM';
		
		$tt = $this->getTabList();

		if (in_array($table_name, $tt)) {
			@set_time_limit(1000);
			@ignore_user_abort(true);
			$wpdb->query('alter table `'.$table_name.'` engine="'.$target_type.'"');
		}
	}

	function ajax_switch_engine()
	{
		$ttypes = array(
			'innodb' => 1,
			'myisam' => 0,
		);

		$jx = new WPFTS_jxResponse();
		
		if (($data = $jx->getData()) !== false) {
			$process_id = isset($data['pid']) ? $data['pid'] : '';
			$is_finish = isset($data['is_finish']) ? intval($data['is_finish']) : 0;
			$engine_type = isset($data['engine_type']) ? strtolower($data['engine_type']) : '';
			$is_run = isset($data['is_run']) ? intval($data['is_run']) : 0;

			// Check if indexing is progress
			$ps = $this->indexerProcessState($process_id);
			if ($ps == 0) {
				// Ok, let's continue
				// Check if this is new conversion
				if ((!$is_finish) && (isset($ttypes[$engine_type]))) {
					$this->set_option('is_innodb', $ttypes[$engine_type]);
				}

				$prg = $this->getTableTypeProgress();
				if ($prg['progress'] < 1) {
					if ($is_run) {
						// Run the converting of one table
						$this->convertOneTable($prg['next']);
					}
				} else {
					// All is done. No need to convert
				}

				$jx->variable('code', 0);
				$jx->variable('status', $this->getCnvMessageHtml(true));
			} else {
				$jx->alert(__('This operation is impossible while indexing is in progress.', 'wpfts_lang'));
			}
		}
		echo $jx->getJSON();
		wp_die();
	}

	function ajax_hide_notification()
	{
		$jx = new WPFTS_jxResponse();
		
		if (($data = $jx->getData()) !== false) {
			$notification_id = isset($data['notification_id']) ? $data['notification_id'] : '';

			switch ($notification_id) {
				case 'change_log':
					$this->set_option('change_log', '');
					break;
				case 'welcome_message':
					$this->set_option('is_welcome_message', '');
					break;
			}
		}
		echo $jx->getJSON();
		wp_die();
	}

	public function ForceSmartExcerpts($query = false)
	{
		$this->forced_se_query = mb_strlen((string)$query) > 0 ? (string)$query : false;
	}
}