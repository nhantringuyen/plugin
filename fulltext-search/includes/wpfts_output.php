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
 *  @package Wordpress Fulltext Search Pro
 *  @author Epsiloncool <info@e-wm.org>
 */

require_once dirname(__FILE__).'/wpfts_htmltools.php';

class WPFTS_Output 
{
	public function status_box($post, $status = false, $is_return = false) 
	{
		global $wpfts_core;
		
		if ($status) {
			$status = $wpfts_core->get_status();
		}
		
		ob_start();
		?>
		<p class="wpfts_status">
			<?php
			if (!$status['enabled']) {
				?><span class="wpfts_status_bullet wpfts_red">&#9679;</span>&nbsp;<b><?php echo __('Disabled', 'wpfts_lang'); ?></b><?php
			} else {
				if ($status['index_ready']) {
					?><span class="wpfts_status_bullet wpfts_green">&#9679;</span>&nbsp;<b><?php echo __('Active', 'wpfts_lang'); ?></b><?php
				} else {
					?><span class="wpfts_status_bullet wpfts_yellow">&#9679;</span>&nbsp;<b><?php echo __('Awaiting', 'wpfts_lang'); ?></b><?php
				}
			}
			?>
		</p>
		<p><?php echo __('In Index', 'wpfts_lang'); ?>: <b><span id="wpfts_st_inindex"><?php echo $status['n_inindex']; ?></span></b> <?php echo __('posts', 'wpfts_lang'); ?></p>
		<?php 
		if ($status['autoreindex']) {
			if ($status['n_pending'] > 0) {
				
				$percent = (0.0 + intval($status['n_actual'])) * 100 / (intval($status['n_inindex']) + intval($status['n_pending']));
		?>
		<div class="wpfts_indexer_info">
			<p><span class="wpfts_indexing_status_bullet wpfts_yellow">&#9679;</span>&nbsp;<?php echo __('Indexing is in progress', 'wpfts_lang'); ?></p>
			<span><?php echo __('Actual', 'wpfts_lang'); ?>: <b><span id="wpfts_st_actual"><?php echo $status['n_actual']; ?></span></b> <?php echo __('posts', 'wpfts_lang'); ?></span><br>
			<span><?php echo __('Pending', 'wpfts_lang'); ?>: <b><span id="wpfts_st_pending"><?php echo $status['n_pending']; ?></span></b> <?php echo __('posts', 'wpfts_lang'); ?></span>
			<div id="wpfts_st_1">
				<p class="wpfts_st_percent"><img src="<?php echo $wpfts_core->root_url; ?>/style/waiting16.gif" alt="" title="<?php echo __('Indexing is in progress', 'wpfts_lang'); ?>">&nbsp;<span><?php echo sprintf('%.2f', $percent).'%'; ?></span></p>
				<p><?php echo __('Est. time left: ', 'wpfts_lang'); ?><span class="wpfts_st_esttime"><?php echo $status['est_time']; ?></span></p>
			</div>
		</div>
		<p style="color: #00a000;"><i><?php echo __('In the process of indexing your site might run slower. Please do not worry and wait until index finishing.', 'wpfts_lang'); ?></i></p>
		<?php
			} else {
				?><p><span class="wpfts_indexing_status_bullet wpfts_green">&#9679;</span>&nbsp;<?php echo __('Index is OK', 'wpfts_lang'); ?></p><?php
			}
		} else {
			?><p><span class="wpfts_indexing_status_bullet wpfts_red">&#9679;</span>&nbsp;<?php echo __('Indexing is disabled', 'wpfts_lang'); ?></p><?php
		}
		
		$output = ob_get_clean();
		
		if ($is_return) {
			return $output;
		} else {
			echo $output;
		}
	}

	public function useful_box($post)
	{
		global $wpfts_core;
		
		echo '<h3>'.sprintf(__('Current Version v%s', 'wpfts_lang'), WPFTS_VERSION).'</h3>';
		echo '<p><a href="'.$wpfts_core->_wpfts_domain.'" target="_blank">'.__('WPFTS Home', 'wpfts_lang').'</a></p>';
		echo '<p><a href="'.$wpfts_core->_wpfts_domain.$wpfts_core->_documentation_link.'" target="_blank">'.__('WPFTS Documentation', 'wpfts_lang').'</a></p>';
		//echo '<p><a href="'.$wpfts_core->_wpfts_domain.'/buy'.'" target="_blank" style="color:red;">'.__('Get WPFTS Pro', 'wpfts_lang').'</a></p>';
		?>
		<div class="donate-block">
		<hr>
		<p><?php echo __('Friends, I need a new laptop. My old one is very slow and it\'s hard to make frequent updates of the WPFTS plugin. I need your help.<br>
		<b>Thank you!</b>', 'wpfts_lang'); ?></p>
		<div style="text-align:center;">
			<iframe src="https://e-wm.org/donate.html" style="width:100%;height:70px;"></iframe>
		</div>
		<hr>
		</div>
		<?php
		
	}

	public function control_box($post)
	{
		global $wpfts_core;
		
		$enabled = intval($wpfts_core->get_option('enabled'));
		$autoreindex = intval($wpfts_core->get_option('autoreindex'));
		
		?>
		<table class="form-table wpfts_formtable">
			<tr>
				<th><?php echo __('Enable FullText Search', 'wpfts_lang'); ?></th>
				<td>
					<div>
						<?php
						echo WPFTS_Htmltools::makeLabelledCheckbox('wpfts_enabled', 1, __('Enabled', 'wpfts_lang'), $enabled);
						?>
					</div>
				</td>
				<td>
					<p><i><?php echo __('(If not enabled, regular integrated "not indexed" WordPress search will be used)', 'wpfts_lang'); ?></i></p>
				</td>
			</tr>
			<tr>
				<th><?php echo __('Auto-index', 'wpfts_lang'); ?></th>
				<td>
					<div>
						<?php
						echo WPFTS_Htmltools::makeLabelledCheckbox('wpfts_autoreindex', 1, __('Enabled', 'wpfts_lang'), $autoreindex);
						?>
					</div>
				</td>
				<td>
					<p><i><?php echo __('(Normally, WP FullText Search will autoindex any new post or post changes even if you disabled previous option. Disabling this option will completely stop all plugin functions. However, you probably have to do a full index rebuild, if you activate the plugin again.)', 'wpfts_lang'); ?></i><br>
						<?php echo __('<strong>NOTE</strong>: Disabling this option is NOT recommended!', 'wpfts_lang'); ?></p>
				</td>
			</tr>
			<tr>
				<th></th>
				<td>
					<div>
						<button type="button" class="button wpfts_btn_rebuild" name="wpfts_btn_rebuild" data-confirm="<?php echo htmlspecialchars(__('The action will rebuild the search index, which could take some time. Are you sure?', 'wpfts_lang')); ?>"><?php echo __('Rebuild Index', 'wpfts_lang'); ?></button>
						<div class="wpfts_show_resetting"><img src="<?php echo $wpfts_core->root_url; ?>/style/waiting16.gif" alt="">&nbsp;<?php echo __('Resetting', 'wpfts_lang'); ?></div>
					</div>
				</td>
				<td>
					<p><i><?php echo sprintf(__('(Use this button when you need to completely rebuild search index database, for example, when you changed custom <b>wpfts_index_post</b> filter function. Remember that this operation could take a long time. Please refer for <a href="%s" target="_blank">documentation</a> for more information.)', 'wpfts_lang'), $wpfts_core->_wpfts_domain.$wpfts_core->_documentation_link); ?></i></p>
				</td>
			</tr>
		</table>
		<?php
	}

	public function relevance_box($post)
	{
		global $wpfts_core;
		
		$deflogic = $wpfts_core->get_option('deflogic');

		$deflogic_data = array(
			0 => 'AND',
			1 => 'OR',
		);
		
		$cluster_weights = $wpfts_core->get_option('cluster_weights');
		
		?>
		<table class="form-table wpfts_formtable">
			<tr>
				<th><?php echo __('Default Search Logic', 'wpfts_lang'); ?></th>
				<td style="min-width:100px;">
					<div class="wpfts_search_logic_group">
					<?php
						echo WPFTS_Htmltools::makeRadioGroup('wpfts_deflogic', $deflogic_data, $deflogic, array());
					?>
					</div>
				</td>
				<td>
					<p><i><?php echo __('(This option tells the search engine whether all query words should contain in the found post (AND) or any of these words (OR).)', 'wpfts_lang'); ?></i></p>
				</td>
			</tr>
			<tr>
				<th><?php echo __('Cluster Weights', 'wpfts_lang'); ?></th>
				<td colspan="2">
					<div class="wpfts_scroller" style="margin-top: 0px;">
					<?php
						
						$cluster_types = $wpfts_core->get_cluster_types();

						foreach ($cluster_types as $d) {
							$name = 'eclustertype_' . $d;
							$w = isset($cluster_weights[$d]) ? floatval($cluster_weights[$d]) : 0.5;
							
							echo '<label for="'.$name.'_id"><span>'.htmlspecialchars($d).'</span>&nbsp;'.WPFTS_Htmltools::makeText($w, array('name' => $name, 'class' => 'wpfts_short_input')).'</label>';
						}
					?>
					</div>
					<p><i><?php echo __('(`Cluster` is a part of post (either title, content or even specific part which you can define using <b>wpfts_index_post</b> filter). You can assign some relevance weight to each of them)', 'wpfts_lang'); ?></i></p>
				</td>
			</tr>
			<tr>
				<th><?php echo __('Deeper Search', 'wpfts_lang'); ?></th>
				<td>
					<div>
						<?php
						$dps = intval($wpfts_core->get_option('deeper_search'));
						echo WPFTS_Htmltools::makeLabelledCheckbox('wpfts_deeper_search', 1, __('Enabled', 'wpfts_lang'), $dps);
						?>
					</div>
				</td>
				<td>
					<p><i><?php echo __('(Enables searching substrings in the middle of words. This is much slower than usual search, so use it with care. Keep it disabled if you have any issues with MySQL performance.)', 'wpfts_lang'); ?></i></p>
				</td>
			</tr>
			<tr>
				<th><?php echo __('Internal Query Filter', 'wpfts_lang'); ?></th>
				<td>
					<div>
						<?php
						$internal_sf = intval($wpfts_core->get_option('internal_search_terms'));
						echo WPFTS_Htmltools::makeLabelledCheckbox('wpfts_internal_search_terms', 1, __('Enabled', 'wpfts_lang'), $internal_sf);
						?>
					</div>
				</td>
				<td>
					<p><i><?php echo __('(Cleans up the query string before using it for search. Uncheck this only if you are using your own text splitting algorithm)', 'wpfts_lang'); ?></i></p>
				</td>
			</tr>
		</table>
		<?php
	}

	public function tweaks_box($post)
	{
		global $wpfts_core;
		
		?>
		<table class="form-table wpfts_formtable">
			<tr>
				<td colspan="3"><i><?php echo __('Wordpress is looking for posts using Main Search functionality when the visitor enters search term in Search Widget or enter the link with <b>/?s=term</b> parameter. This group of settings helps you to set up default parameters for such searches. Note: it will be not applied for other searches.', 'wpfts_lang'); ?></i></td>
			</tr>
			<tr>
				<th><?php echo __('Search Order By', 'wpfts_lang'); ?></th>
				<td>
					<div>
						<?php
						$mainsearch_orderby = $wpfts_core->get_option('mainsearch_orderby');
						$a = array(
							'relevance' => __('Relevance (WP default)', 'wpfts_lang'),
							'ID' => __('Post ID', 'wpfts_lang'),
							'author' => __('Author', 'wpfts_lang'),
							'title' => __('Title', 'wpfts_lang'),
							'name' => __('Post Slug', 'wpfts_lang'),
							'type' => __('Post Type', 'wpfts_lang'),
							'date' => __('Created Date', 'wpfts_lang'),
							'modified' => __('Modified Date', 'wpfts_lang'),
							'parent' => __('Parent Post ID', 'wpfts_lang'),
							'rand' => __('Random', 'wpfts_lang'),
							'comment_count' => __('Comment Count', 'wpfts_lang'),
						);
						echo WPFTS_Htmltools::makeSelect($a, $mainsearch_orderby, array('name' => 'wpfts_mainsearch_orderby'));
						?>
					</div>
				</td>
				<td>
					<p><i><?php echo __('(Modify main WP query defaults so it will order search results by specific data.)', 'wpfts_lang'); ?></i></p>
				</td>
			</tr>
			<tr>
				<th><?php echo __('Search Order', 'wpfts_lang'); ?></th>
				<td>
					<div>
						<?php
						$mainsearch_order = $wpfts_core->get_option('mainsearch_order');
						$a = array(
							'DESC' => 'DESC',
							'ASC' => 'ASC',
						);
						echo WPFTS_Htmltools::makeSelect($a, $mainsearch_order, array('name' => 'wpfts_mainsearch_order'));
						?>
					</div>
				</td>
				<td>
					<p><i><?php echo __('(Modify main WP query default search results ordering.)', 'wpfts_lang'); ?></i></p>
				</td>
			</tr>
			<tr style="color:#aaa;">
				<th style="color:#aaa;"><?php echo __('Display Attachments', 'wpfts_lang'); echo "<br>"; echo __('(Pro Version only)', 'wpfts_lang'); ?></th>
				<td>
					<div>
						<?php
						echo WPFTS_Htmltools::makeLabelledCheckbox('wpfts_display_attachments', 1, __('Enabled', 'wpfts_lang'), false, array('disabled' => 'disabled'));
						?>
					</div>
				</td>
				<td>
					<p><i><?php echo __('(Tweak standard WP query widget so it will search in attachments and display them in search results.)', 'wpfts_lang'); ?></i></p>
				</td>
			</tr>
		</table>
		<?php
	}

	public function smart_excerpts_box($post)
	{
		global $wpfts_core;
		
		?>
		<table class="form-table wpfts_formtable">
			<col width="15%"/>
			<col width="20%"/>
			<col width="65%"/>
			<tr>
				<td colspan="3"><i><?php echo __('WPFTS can output search results in a Google-like way - showing only sentences which contains search words and highlighting them. Wordpress by default does not show any content for result items if the items are attachments. <b>Pro version only</b>: Smart Excerpts function can output attachment content too. <a href="https://fulltextsearch.org/documentation/#smart_excerpts" target="_blank">Check details</a>.', 'wpfts_lang'); ?></i></td>
			</tr>
			<tr>
				<th><?php echo __('Enable Smart Excerpts', 'wpfts_lang'); ?></th>
				<td>
					<div>
						<?php
						$is_smart_excerpts = intval($wpfts_core->get_option('is_smart_excerpts'));
						echo WPFTS_Htmltools::makeLabelledCheckbox('wpfts_is_smart_excerpts', 1, __('Enabled', 'wpfts_lang'), $is_smart_excerpts);
						?>
					</div>
				</td>
				<td>
					<p><i><?php echo __('(Replaces Wordpress excerpts by WPFTS Smart Excerpts in search results)', 'wpfts_lang'); ?></i></p>
				</td>
			</tr>

			<tr>
				<th><?php echo __('Optimal Length', 'wpfts_lang'); ?></th>
				<td>
					<div>
						<?php
						$optimal_length = intval($wpfts_core->get_option('optimal_length'));
						echo WPFTS_Htmltools::makeText($optimal_length, array('name' => 'wpfts_optimal_length'));
						?>
					</div>
				</td>
				<td>
					<p><i><?php echo __('(WPFTS will try to keep excerpt length between 90% and 110% of this value)', 'wpfts_lang'); ?></i></p>
				</td>
			</tr>

			<tr>
				<th><?php echo __('Include to excerpt:', 'wpfts_lang'); ?></th>
				<td>
					<div>
						<ul>
							<li><label><?php 
								$is_smart_excerpt_text = intval($wpfts_core->get_option('is_smart_excerpt_text'));
								echo WPFTS_Htmltools::makeLabelledCheckbox('wpfts_is_smart_excerpt_text', 1, __('Smart Excerpt text', 'wpfts_lang'), $is_smart_excerpt_text);
								?></label>
							</li>
							<li><label><?php
								$is_show_score = intval($wpfts_core->get_option('is_show_score'));
								echo WPFTS_Htmltools::makeLabelledCheckbox('wpfts_is_show_score', 1, __('Score/Relevance', 'wpfts_lang'), $is_show_score);
								?></label>
							</li>
							<li><label><?php
								$is_not_found_words = intval($wpfts_core->get_option('is_not_found_words'));
								echo WPFTS_Htmltools::makeLabelledCheckbox('wpfts_is_not_found_words', 1, __('"Not Found" words', 'wpfts_lang'), $is_not_found_words);
								?></label>
							</li>
							<p style="padding: 10px 0px;color:#aaa;"><?php echo __('For Attachments: (PRO only)', 'wpfts_lang'); ?></p>
							<li style="color:#aaa;"><label><?php
								echo WPFTS_Htmltools::makeLabelledCheckbox('wpfts_is_file_ext', 1, __('File Extension', 'wpfts_lang'), 0, array('disabled' => 'disabled'));
								?></label>
							</li>
							<li style="color:#aaa;"><label><?php
								echo WPFTS_Htmltools::makeLabelledCheckbox('wpfts_is_filesize', 1, __('Filesize', 'wpfts_lang'), 0, array('disabled' => 'disabled'));
								?></label>
							</li>
							<li style="color:#aaa;"><label><?php
								echo WPFTS_Htmltools::makeLabelledCheckbox('wpfts_is_direct_link', 1, __('Direct Download Link', 'wpfts_lang'), 0, array('disabled' => 'disabled'));
								?></label>
							</li>
							<li style="color:#aaa;"><label><?php
								echo WPFTS_Htmltools::makeLabelledCheckbox('wpfts_is_title_direct_link', 1, __('Link Title to File Download', 'wpfts_lang'), 0, array('disabled' => 'disabled'));
								?></label>
							</li>
						</ul>
						<?php

						?>
					</div>
				</td>
				<td>
					<p><?php echo __('Demo Output:', 'wpfts_lang'); ?> <span title="<?php echo __('Optimal Length is ignored here', 'wpfts_lang'); ?>">[?]</span></p>
					<?php
					$wpfts_result_item = new WPFTS_Result_Item();
					$wpfts_result_item->demo_mode = true;
					?>
					<div class="wpfts_smart_excerpts_preview">
						<h2><a href="<?php echo esc_url($wpfts_result_item->TitleLink()); ?>" rel="bookmark"><?php echo $wpfts_result_item->TitleText(); ?></a></h2>
						<div class="wpfts-result-item">
							<?php echo $wpfts_result_item->Excerpt(); ?>
						</div>
					</div>
				</td>
			</tr>

			<tr>
				<th><?php echo __('Custom CSS Styling', 'wpfts_lang'); ?></th>
				<td>
					<div>
						<input type="button" value="Preview Styles" class="btn_se_style_preview"><br>
						<input type="button" value="Reset to Default" class="btn_se_style_reset">
					</div>
				</td>
				<td>
					<?php
						$custom_se_css = $wpfts_core->get_option('custom_se_css');
						//echo WPFTS_Htmltools::makeTextarea($custom_se_css, array('name' => 'wpfts_se_styles', 'rows' => 6, 'style' => 'width:100%;'));
						echo '<div id="wpfts_se_styles_editor">'.$custom_se_css.'</div>';
					?>
					<i><?php echo __('This CSS snippet will be automatically minimized upon usage with a frontend.', 'wpfts_lang'); ?></i>
					<?php echo '<style type="text/css" id="wpfts_se_styles_node">'.$wpfts_core->ReadSEStylesMinimized().'</style>'; ?>
				</td>
			</tr>

			<tr>
				<td colspan="3"><i><?php echo __('Notice: this is a <b>beta version</b> of the Smart Excerpt function. In case it does not work for your theme/site, please do not hesistate to send us some information with screenshots and theme name <a href="https://fulltextsearch.org/contact/" target="_blank">here</a>.', 'wpfts_lang'); ?></i></td>
			</tr>
		</table>
		<?php
	}
	
	public function indexing_box($post)
	{
		global $wpfts_core;
		
		$minlen = intval($wpfts_core->get_option('minlen'));
		$maxrepeat = intval($wpfts_core->get_option('maxrepeat'));
		$stopwords = $wpfts_core->get_option('stopwords');
		$epostype = $wpfts_core->get_option('epostype');
		$content_strip_tags = intval($wpfts_core->get_option('content_strip_tags'));
		
		?>
		<table class="form-table wpfts_formtable">
			<tr>
				<th><?php echo __('Minimum Word Length', 'wpfts_lang'); ?></th>
				<td>
					<div>
					<?php
						echo WPFTS_Htmltools::makeText($minlen, array('name' => 'wpfts_minlen', 'class' => 'wpfts_short_input'));
					?>
						&nbsp;<span><?php echo __('characters', 'wpfts_lang'); ?></span>
					</div>
				</td>
				<td>
					<p><i><?php echo sprintf(__('(Consider any word shorter than specified number of characters as a <a data-hint="%1s" href="%2s" target="_blank">stop word</a>.)', 'wpfts_lang'), htmlspecialchars(__('<b>Stop Word</b> is a word which is not indexing and can not be used to search for.', 'wpfts_lang')), $wpfts_core->_wpfts_domain.$wpfts_core->_documentation_link.'#stop_word'); ?></i></p>
				</td>
			</tr>
			<tr>
				<th><?php echo __('Maximum Word Frequency', 'wpfts_lang'); ?></th>
				<td>
					<div>
					<?php
						echo WPFTS_Htmltools::makeText($maxrepeat, array('name' => 'wpfts_maxrepeat', 'class' => 'wpfts_short_input'));
					?>
						&nbsp;<span>%</span>
					</div>
				</td>
				<td>
					<p><i><?php echo __('(Consider a word that is found in the specified or more amount of documents as a stop word.)', 'wpfts_lang'); ?></i></p>
				</td>
			</tr>
			<tr>
				<th><?php echo __('Stop Words', 'wpfts_lang'); ?></th>
				<td colspan="2">
					<p><?php echo __('A comma-separated list of custom stop words', 'wpfts_lang'); ?></p>
					<div>
					<?php
						echo WPFTS_Htmltools::makeTextarea(
								$stopwords, array('name' => 'wpfts_stopwords', 'class' => 'wpfts_long_textarea', 'placeholder' => __('the, a, an, ...etc', 'wpfts_lang'))
						);
					?>
					</div>
				</td>
			</tr>
			<tr>
				<th><?php echo __('Strip Tags From Content', 'wpfts_lang'); ?></th>
				<td>
					<div>
						<?php
						echo WPFTS_Htmltools::makeLabelledCheckbox('wpfts_content_strip_tags', 1, __('Enabled', 'wpfts_lang'), $content_strip_tags);
						?>
					</div>
				</td>
				<td>
					<p><i><?php echo __('Removes HTML tags and comments from the <code>post_content</code> while indexing (useful for Gutenberg-driven sites).', 'wpfts_lang'); ?></i></p>
				</td>
			</tr>
			<tr style="color:#aaa;">
				<th style="color:#aaa;"><?php echo __('Include Attachments', 'wpfts_lang'); echo "<br>"; echo __('(Pro Version only)', 'wpfts_lang'); ?></th>
				<td>
					<div>
						<?php
						echo WPFTS_Htmltools::makeLabelledCheckbox('wpfts_include_attachments', 1, __('Enabled', 'wpfts_lang'), false, array('disabled' => 'disabled'));
						?>
					</div>
				</td>
				<td>
					<p><i><?php echo __('(Allow for posts to be searchable by their attached files content. When enabled, this option will include attachments\' index to their parent post indexes.)', 'wpfts_lang'); ?></i></p>
				</td>
			</tr>
			<tr>
				<th><?php echo __('MySQL Engine Type', 'wpfts_lang'); ?></th>
				<td>
					<div>
						<?php
						$cnv_msg = $wpfts_core->getCnvMessageHtml();

						echo '<div id="wpfts_cnvmsg">'.$cnv_msg['html'].'</div>';
						?>
					</div>
				</td>
				<td>
					<p><i><?php echo __('(Sometimes other MySQL engine type can give better results in speed. Switch this to InnoDB if you have any issues with MySQL locks or your host is optimized specifically for InnoDB.)', 'wpfts_lang'); ?></i></p>
				</td>
			</tr>
			
			<?php
			/*
			<tr>
				<th><?php echo __('Exclude Post Types', 'wpfts_lang'); ?></th>
				<td colspan="2">
					<p>Check post types which will be excluded from index</p>
					<div class="wpfts_scroller">
					<?php
						
						$post_types = $wpfts_core->get_post_types();

						foreach ($post_types as $k => $d) {
							$name = 'epostype_' . $k;
							echo WPFTS_Htmltools::makeLabelledCheckbox($name, 1, $d . ' (' . $k . ')', (isset($epostype[$k]) && ($epostype[$k])) ? 1 : 0);
						}
					?>
					</div>
					<p><i>(To search for posts of selected post types built-in WordPress algorithm will be used.)</i></p>
				</td>
			</tr>
			*/
			?>
		</table>
		<?php
	}

	public function sandbox_box($post)
	{
		global $wpfts_core, $wpdb;
		
		$testpostid = $wpfts_core->get_option('testpostid');
		$testquery = $wpfts_core->get_option('testquery');
		$tq_disable = $wpfts_core->get_option('tq_disable');
		$tq_nocache = $wpfts_core->get_option('tq_nocache');
		$tq_post_status = $wpfts_core->get_option('tq_post_status');
		$tq_post_type = $wpfts_core->get_option('tq_post_type');
		
		$post_statuses = array(
			'any' => __('* (Any)', 'wpfts_lang'),
			'publish' => __('publish (Published)', 'wpfts_lang'),
			'future' => __('future (Future)', 'wpfts_lang'),
			'draft' => __('draft (Draft)', 'wpfts_lang'),
			'pending' => __('pending (Pending)', 'wpfts_lang'),
			'private' => __('private (Private)', 'wpfts_lang'),
			'trash' => __('trash (Trash)', 'wpfts_lang'),
			'auto-draft' => __('auto-draft (Auto-Draft)', 'wpfts_lang'),
			'inherit' => __('inherit (Inherit)', 'wpfts_lang'),
		);
		
		$q = 'select distinct post_type from `'.$wpdb->posts.'` order by post_type asc';
		$res = $wpdb->get_results($q, ARRAY_A);
		
		$post_types = array('any' => __('* (Any)', 'wpfts_lang'));
		foreach ($res as $d) {
			$post_types[$d['post_type']] = $d['post_type'];
		}
		
		?>
		<table class="form-table wpfts_formtable">
			<tr>
				<th>
					<?php echo __('Pre-indexing Filter Tester', 'wpfts_lang'); ?>
				</th>
				<td>
					<p><?php echo __('You can test your own <b>wpfts_index_post</b> filter here. Just enter an ID of a post you want and press Test Filter button.', 'wpfts_lang'); ?></p>
					<div>
						<p><?php echo __('Post ID:', 'wpfts_lang'); ?>
						<?php
							echo WPFTS_Htmltools::makeText($testpostid, array('name' => 'wpfts_testpostid', 'class' => 'wpfts_middle_input'));
						?>
						<?php
							echo WPFTS_Htmltools::makeButton(__('Test Filter', 'wpfts_lang'), array('id' => 'wpfts_testbutton', 'type' => 'button', 'class' => 'button'));
						?></p>
					</div>
				</td>
			</tr>
			<tr>
				<th>
					<?php echo __('Search Tester', 'wpfts_lang'); ?>
				</th>
				<td>
					<p><?php echo __('You can test search with any query here. Standard wordpress <b>WP_Query</b> object with WPFTS features will be used.', 'wpfts_lang'); ?></p>
					<div>
						<p><?php echo __('Query:', 'wpfts_lang'); ?> 
						<?php
							echo WPFTS_Htmltools::makeText($testquery, array('name' => 'wpfts_testquery', 'class' => 'wpfts_middle_input'));
						?>
						<?php
							echo WPFTS_Htmltools::makeButton(__('Test Search', 'wpfts_lang'), array('id' => 'wpfts_testquerybutton', 'type' => 'button', 'class' => 'button'));
						?></p>
					</div>
					
					<div>
						<p><b><?php echo __('Additional Options:', 'wpfts_lang'); ?></b></p>
						<p>
						<span style="margin-right: 20px;"><?php
							echo WPFTS_Htmltools::makeCheckbox($tq_disable, array('id' => 'wpfts_tq_disable', 'name' => 'wpfts_tq_disable', 'class' => 'wpfts_middle_input', 'value' => 1), __('Disable WPFTS', 'wpfts_lang'));
						?></span>
						<span style="margin-right: 20px;"><?php
							echo WPFTS_Htmltools::makeCheckbox($tq_nocache, array('id' => 'wpfts_tq_nocache', 'name' => 'wpfts_tq_nocache', 'class' => 'wpfts_middle_input', 'value' => 1), __('Disable SQL cache', 'wpfts_lang'));
						?></span>
						</p>
						<p>
							<span style="margin-right: 20px;"><?php
								echo __('Post Type:', 'wpfts_lang').'&nbsp;'; 
								echo WPFTS_Htmltools::makeSelect($post_types, $tq_post_type, array('id' => 'wpfts_tq_post_type', 'name' => 'wpfts_tq_post_type', 'class' => 'wpfts_middle_input'));
							?></span>
							<span style="margin-right: 20px;"><?php
								echo __('Post Status:', 'wpfts_lang').'&nbsp;';
								echo WPFTS_Htmltools::makeSelect($post_statuses, $tq_post_status, array('id' => 'wpfts_tq_post_status', 'name' => 'wpfts_tq_post_status', 'class' => 'wpfts_middle_input'));
							?></span>
						</p>
					</div>
					
				</td>
			</tr>
			<tr>
				<td colspan="2" id="wpfts_sandbox_output">
					
				</td>
			</tr>

		</table>
		<?php
	}
}
