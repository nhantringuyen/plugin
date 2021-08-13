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

function wpfts_add_meta_boxes_cb() 
{
	$out = new WPFTS_Output();
	
	// Side
	add_meta_box( 
        'wpfts_status_box',
        __( 'Search Engine Status', 'wpfts_lang' ),
        array(&$out, 'status_box'),
        'wpfts-options',
        'side',
        'core'
    );
	// Side2
	add_meta_box( 
        'wpfts_useful_box',
        __( 'Useful Information', 'wpfts_lang' ),
        array(&$out, 'useful_box'),
        'wpfts-options',
        'side',
        'core'
    );
	// Main
	add_meta_box(
        'wpfts_control_box',
        __( 'Main Configuration', 'wpfts_lang' ),
        array(&$out, 'control_box'),
        'wpfts-options',
        'normal1',
        'core'
    );
	add_meta_box(
        'wpfts_relevance_box',
        __( 'Search and Relevance Settings', 'wpfts_lang' ),
        array(&$out, 'relevance_box'),
        'wpfts-options',
        'normal1',
        'core'
    );
	add_meta_box(
        'wpfts_tweaks_box',
        __( 'Main WP Search Tweaks', 'wpfts_lang' ),
        array(&$out, 'tweaks_box'),
        'wpfts-options',
        'normal1',
        'core'
	);
	/*
	add_meta_box( 
        'wpfts_extraction',
        __( 'Attachment Processing', 'wpfts_lang' ),
        array(&$out, 'extraction_box'),
        'wpfts-options',
        'normal1',
        'core'
	);
	*/
	// Relevance
	add_meta_box( 
        'wpfts_indexing_box',
        __( 'Indexing Engine Settings', 'wpfts_lang' ),
        array(&$out, 'indexing_box'),
        'wpfts-options',
        'normal2',
        'core'
	);
	// Smart Excerpts
	add_meta_box( 
        'wpfts_smart_excerpts_box',
        __( 'Smart Excerpts', 'wpfts_lang' ),
        array(&$out, 'smart_excerpts_box'),
        'wpfts-options',
        'normal5',
        'core'
	);	
	add_meta_box( 
        'wpfts_sandbox_box',
        __( 'Sandbox Area', 'wpfts_lang' ),
        array(&$out, 'sandbox_box'),
        'wpfts-options',
        'normal3',
        'core'
	);
	/*	
	add_meta_box(
		'wpfts_licensing',
		__( 'Licensing Info', 'wpfts_lang' ),
		array(&$out, 'licensing_box'),
        'wpfts-options',
        'normal4',
        'core'
	);
	*/
}

global $wpfts_core;

wpfts_add_meta_boxes_cb();

?><div class="wrap">
    <?php require dirname(__FILE__).'/admin_header.php'; ?>

	<?php

	// Do we need to show an invitation message?
	$upds = $wpfts_core->GetUpdates();

	if ($upds['is_new'] && $wpfts_core->is_wpfts_settings_page) {
	?>
	<div class="notice notice-warning wpfts-notice">
		<hr>
		<?php

		echo sprintf(__('<h2>Important Notice Before You Start</h2>
		<p>Everything is ready to index the contents of your site. When creating a Search Index, the plugin will use its own tables in the database, no your data will be affected.</p>
		<p>The process may take a long time (it depends on the amount of data on the site) and the site may work a little slower. There is no reason to worry - this slowness will end with the end of the indexing process. To reduce the time to build the index, please <b>do not close</b> the plugin settings page.</p>
		<p>If you didn’t install WPFTS Add-ons and didn’t set up your own <code>wpfts_index_post</code> hook, then this time only the Titles and the main Content of the publications will be included in the index. If you want other data to participate in the search (such as <b>post meta data</b>), now is the time to read the <a href="%s" target="_blank">WPFTS Documentation</a> and make the necessary changes.</p>
		<p>We wish you a pleasant work with the WP FullText Search plugin.</p>
		<p>We also thank you for your <a href="%s" target="_blank">comments and suggestions</a>.</p>
		<p><i>WPFTS plugin development team.</i></p>', 'wpfts_lang'), 
					'https://fulltextsearch.org/documentation',
					'https://fulltextsearch.org/contact/'
				);
		?>
		<p style="text-align: center;">
			<button type="button" class="button-primary btn_start_indexing"><?php echo __('Start Indexing', 'wpfts_lang'); ?></button>&nbsp;<span class="wpfts_show_resetting"><img src="<?php echo $wpfts_core->root_url; ?>/style/waiting16.gif" alt="">&nbsp;<?php echo __('Resetting', 'wpfts_lang'); ?></span>
		</p>
		<hr>
	</div>
	<?php
	}
	?>

	<h2 class="nav-tab-wrapper wpfts_tabs">
	<?php
	$tabs = array(
		'wpfts-options' => __('Main Configuration', 'wpfts_lang'),
		'wpfts-options-indexing-engine' => __('Indexing Engine Settings', 'wpfts_lang'),
		'wpfts-options-smart-excerpts' => __('Smart Excerpts', 'wpfts_lang'),
		'wpfts-options-sandbox-area' => __('Sandbox Area', 'wpfts_lang'),
		//'wpfts-options-licensing' => __('Licensing', 'wpfts_lang'),
	);

	/*
	$lic_status = WPFTS_Updater::get_subscription_status(); 

	if (!$lic_status) 
	{
		$tabs['licensing'] .= '<span class="wpfts-license-warning"><span class="warning-count">!</span></span>';
	}
	*/

	$current_tab = isset($_GET['page']) ? $_GET['page'] : 'wpfts-options';
	foreach ($tabs as $tab_key => $tab_caption) {
		$active = ($current_tab == $tab_key) ? " nav-tab-active" : "";
		echo '<a class="nav-tab'.$active.'" href="?page='.$tab_key.'">'.$tab_caption.'</a>';
	}
	?>
	</h2>
	<?php

	switch ($current_tab) {
		case 'wpfts-options':
			require dirname(__FILE__).'/main-configuration.php';
			break;
		case 'wpfts-options-indexing-engine':
			require dirname(__FILE__).'/indexing-engine.php';
			break;
		case 'wpfts-options-smart-excerpts':
			require dirname(__FILE__).'/smart-excerpts.php';
			break;
		case 'wpfts-options-sandbox-area':
			require dirname(__FILE__).'/sandbox-area.php';
			break;
		case 'wpfts-options-licensing':
			//require dirname(__FILE__).'/licensing.php';
			break;
	}
	?>
</div>

