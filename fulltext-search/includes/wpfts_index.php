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

class WPFTS_Index 
{
	public $prefix = 'wpftsi_';
	public $max_word_length = 255;
	public $error = '';
	public $lock_time = 300;	// 5 min

	protected $stops = array();
	
	protected $log = array();
	
	public $timelog = array();
	
	protected $_islock = true;
	
	function __construct()
	{
		//
	}

	function dbprefix()
	{
		global $wpdb;

		if (function_exists('is_multisite') && is_multisite()) {
			$blog_id = $wpdb->blogid;
			if ($blog_id > 1) {
				return $this->prefix.$blog_id.'_';
			}
		}
		return $this->prefix;
	}

	protected function load_stops()
	{	
		global $wpdb;
		
		$q = 'select `word` from `'.$this->dbprefix().'stops`';
		$res = $wpdb->get_result($q, ARRAY_A);
		
		$z = array();
		foreach ($res as $d) {
			$z[mb_strtolower($d['word'])] = 1;
		}
		$this->stops = $z;
	}
	
	protected function log($message)
	{
		$this->log[] = $message;
	}
	
	public function clearLog()
	{
		$this->log = array();
	}
	
	public function getLog()
	{
		return implode("\n", $this->log);
	}
	
	public function check_db_tables()
	{
		global $wpdb;
		
		$sch = $this->getDbScheme();
		
		// Check all tables
		$wrongs = array();
		foreach ($sch as $k => $d) {
			$q = 'SHOW TABLES LIKE "'.$this->dbprefix().$k.'"';
			$res = $wpdb->get_results($q, ARRAY_A);
			if (count($res) > 0) {
				// Table exists
				
				// Check columns
				$q = 'show columns from `'.$this->dbprefix().$k.'`';
				$res = $wpdb->get_results($q, ARRAY_A);
				
				if (count($d['cols']) === count($res)) {
					
					foreach ($res as $dd) {
						if (isset($d['cols'][$dd['Field']])) {
							
							$cl = $d['cols'][$dd['Field']];
							if ($cl[0] != $dd['Type']) {
								$wrongs[$k] = 'ntype:'.$dd['Field'];
								break;
							}
							if ($cl[1] != $dd['Null']) {
								$wrongs[$k] = 'nnull:'.$dd['Field'];
								break;
							}
							/*
							Key
							Default
							Extra
							*/	
							
						} else {
							$wrongs[$k] = 'ncol:'.$dd['Field'];
							break;
						}
					}
					
				} else {
					$wrongs[$k] = 'nrows';
				}
				
			} else {
				$wrongs[$k] = 'nexist';
			}
		}
		
		return $wrongs;
	}
	
	public function create_db_tables() 
	{
		global $wpdb, $wpfts_core;
		
		$success = true;
		
		$sch = $this->getDbScheme();

		foreach ($sch as $k => $d) {
			
			$q = 'drop table if exists `'.$this->dbprefix().$k.'`';
			$wpdb->query($q);
			
			$wpdb->query($d['create2']);
			if ($wpdb->last_error) {
				$this->log('Can\'t create table "'.$this->dbprefix().$k.'": '.$wpdb->last_error);
				$success = false;
			}
		}
		if ($success) {
			$wpfts_core->set_option('current_db_version', WPFTS_VERSION);
		}
		
		return $success;
	}

	public function getDbScheme()
	{
		global $wpfts_core;

		$dbscheme = array(
			'docs' => array(
				'cols' => array(
					// name => type, isnull, keys, default, extra
					'id' => array('int(10) unsigned', 'NO', 'PRI', null, 'auto_increment'),
					'index_id' => array('int(10) unsigned', 'NO', 'MUL'),
					'token' => array('varchar(255)', 'NO', 'MUL'),
					'n' => array('int(10) unsigned', 'NO'),
				),
				'index' => array(
					'PRIMARY' => array(0, 'id'),
					'token' => array(1, 'token'),
					'index_id' => array(1, 'index_id'),
				),
				'create' => "CREATE TABLE `wpftsi_docs` (
								`id` int(10) unsigned NOT NULL auto_increment,
								`index_id` int(10) unsigned NOT NULL,
								`token` varchar(255) NOT NULL,
								`n` int(10) unsigned NOT NULL,
								PRIMARY KEY  (`id`),
								KEY `token` (`token`),
								KEY `index_id` USING BTREE (`index_id`)
							) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8",
			),
			'index' => array(
				'cols' => array(
					'id' => array('int(10) unsigned', 'NO', 'PRI', null, 'auto_increment'),
					'tid' => array('bigint(10) unsigned', 'NO', 'MUL'),		
					'tsrc' => array('varchar(255)', 'NO', 'MUL'),		
					'tdt' => array('datetime', 'NO', '', '1970-01-01 00:00:00'),
					'build_time' => array('int(11)', 'NO', 'MUL', '0'),	
					'update_dt' => array('datetime', 'NO', '', '1970-01-01 00:00:00'),
					'force_rebuild' => array('tinyint(4)', 'NO', 'MUL', '0'),
					'locked_dt' => array('datetime', 'NO', 'MUL', '1970-01-01 00:00:00'),
				),
				'index' => array(
					'PRIMARY' => array(0, 'id'),
					'tid_tsrc_unique' => array(0, 'tid,tsrc'),
					'tid' => array(1, 'tid'),
					'build_time' => array(1, 'build_time'),
					'force_rebuild' => array(1, 'force_rebuild'),
					'locked_dt' => array(1, 'locked_dt'),
					'tsrc' => array(1, 'tsrc'),
				),
				'create' => "CREATE TABLE `wpftsi_index` (
								`id` int(10) unsigned NOT NULL auto_increment,
								`tid` bigint(10) unsigned NOT NULL,
								`tsrc` varchar(255) NOT NULL,
								`tdt` datetime NOT NULL default '1970-01-01 00:00:00',
								`build_time` int(11) NOT NULL default '0',
								`update_dt` datetime NOT NULL default '1970-01-01 00:00:00',
								`force_rebuild` tinyint(4) NOT NULL default '0',
								`locked_dt` datetime NOT NULL default '1970-01-01 00:00:00',
								PRIMARY KEY  (`id`),
								UNIQUE KEY `tid_tsrc_unique` USING BTREE (`tid`,`tsrc`),
								KEY `tid` (`tid`),
								KEY `build_time` (`build_time`),
								KEY `force_rebuild` (`force_rebuild`),
								KEY `locked_dt` (`locked_dt`),
								KEY `tsrc` USING HASH (`tsrc`)
							) ENGINE=MyISAM AUTO_INCREMENT=114874 DEFAULT CHARSET=utf8",
			),
			'stops' => array(
				'cols' => array(
					'id' => array('int(10) unsigned', 'NO', 'PRI', null, 'auto_increment'),
					'word' => array('varchar(32)', 'NO', 'UNI'),
				),
				'index' => array(
					'PRIMARY' => array(0, 'id'),
					'word' => array(0, 'word'),
				),
				'create' => 'CREATE TABLE `wpftsi_stops` (
								`id` int(10) unsigned NOT NULL auto_increment,
								`word` varchar(32) character set utf8 collate utf8_bin NOT NULL,
								PRIMARY KEY  (`id`),
								UNIQUE KEY `word` (`word`)
							) ENGINE=MyISAM DEFAULT CHARSET=utf8',
			),
			'vectors' => array(
				'cols' => array(
					'wid' => array('int(10) unsigned', 'NO', 'PRI'),
					'did' => array('int(10) unsigned', 'NO', 'PRI'),
					'f' => array('float', 'NO', ''),
				),
				'index' => array(
					'wid_did' => array(0, 'wid,did'),
					'wid' => array(1, 'wid'),
					'did' => array(1, 'did'),
				),
				'create' => 'CREATE TABLE `wpftsi_vectors` (
								`wid` int(10) unsigned NOT NULL,
								`did` int(10) unsigned NOT NULL,
								`f` float(10,4) NOT NULL,
								UNIQUE KEY `wid` (`wid`,`did`),
								KEY `wid_2` (`wid`),
								KEY `did` (`did`)
							) ENGINE=MyISAM DEFAULT CHARSET=utf8',
			),
			'words' => array(
				'cols' => array(
					'id' => array('int(10) unsigned', 'NO', 'PRI', null, 'auto_increment'),
					'word' => array('varchar(255)', 'NO', 'UNI'),
				),
				'index' => array(
					'PRIMARY' => array(0, 'id'),
					'word' => array(0, 'word'),
				),
				'create' => 'CREATE TABLE `wpftsi_words` (
								`id` int(10) unsigned NOT NULL auto_increment,
								`word` varchar(255) character set utf8 collate utf8_bin NOT NULL,
								PRIMARY KEY  (`id`),
								UNIQUE KEY `word` (`word`)
							) ENGINE=MyISAM AUTO_INCREMENT=173320 DEFAULT CHARSET=utf8',
			),
		);

		$engine_type = intval($wpfts_core->get_option('is_innodb')) ? 'InnoDB' : 'MyISAM';
	
		// Make Mysql Db creation queries
		foreach ($dbscheme as $k => $d) {
			
			$s = 'CREATE TABLE `'.$this->dbprefix().$k.'` ('."\n";
			
			$cs = array();
			$ai = false;
			foreach ($d['cols'] as $kk => $dd) {
				$ss = '`'.$kk.'` '.$dd[0].' '.($dd[1] == 'NO' ? 'NOT NULL' : 'NULL');
				if (isset($dd[3])) {
					$ss .= ' default \''.$dd[3].'\'';
				}
				if ((isset($dd[4])) && ($dd[4] == 'auto_increment')) {
					$ss .= ' auto_increment';
					$ai = true;
				}
				$cs[] = $ss;
			}
			
			$iz = array();
			foreach ($d['index'] as $kk => $dd) {
				$ss = '';
				if ($kk == 'PRIMARY') {
					$ss = 'PRIMARY KEY';
				} else {
					if ($dd[0] == 0) {
						$ss = 'UNIQUE KEY `'.$kk.'`';
					} else {
						$ss = 'KEY `'.$kk.'`';
					}
				}
				$ws = explode(',', $dd[1]);
				$zz = array();
				foreach ($ws as $z) {
					$zz[] = '`'.$z.'`';
				}
				$ss .= ' ('.implode(',', $zz).')';
				
				$iz[] = $ss;
			}
			
			$s .= implode(",\n", $cs);
			
			if (count($iz) > 0) {
				$s .= ",\n".implode(",\n", $iz);
			}
			
			$s .= "\n".') ENGINE='.$engine_type.($ai ? ' AUTO_INCREMENT=1' : '').' DEFAULT CHARSET=utf8';
			
			$dbscheme[$k]['create2'] = $s;
		}
		
		return $dbscheme;
	}
	
	public function split_to_words($str)
	{
		// Replace UTF-8 apostrophes/quotes with ASCII ones
		$str2 = preg_replace("~[\x{00b4}\x{2018}\x{2019}]~u", "'", mb_strtolower($str));

		// Replace quotes also (intentionally commented out for future usage)
		//$str2 = str_replace(array("\x{201C}", "\x{201D}"), '"', $str2);
	
		preg_match_all("~([\x{00C0}-\x{1FFF}\x{2C00}-\x{D7FF}\w][\x{00C0}-\x{1FFF}\x{2C00}-\x{D7FF}\w'\-]*[\x{00C0}-\x{1FFF}\x{2C00}-\x{D7FF}\w]+|[\x{00C0}-\x{1FFF}\x{2C00}-\x{D7FF}\w]+)~u", $str2, $matches);
		if (isset($matches[1])) {
			$ws = $matches[1];
		} else {
			$ws = array();
		}
		return apply_filters('wpfts_split_to_words', $ws, $str);
	}

	public function reindex($index_id, $chunks)
	{	
		global $wpdb;
		
		if (!is_array($chunks)) {
			$this->log('Reindex: wrong chunks format');
			return false;
		}
		// Remove all existing vectors and chunks first
		/*
		$q = 'delete from `'.$this->prefix.'vectors` where `did` in (select `id` from `'.$this->prefix.'docs` where `index_id` = "'.addslashes($index_id).'")';
		$wpdb->query($q);

		$q = 'delete from `'.$this->prefix.'docs` where `index_id` = "'.addslashes($index_id).'"';
		$wpdb->query($q);
		*/

		// Fast fix for slow mysql servers / limited resources e.g. shared hostings
		$wpdb->query('set interactive_timeout = 10');
		$wpdb->query('set wait_timeout = 10');

		$q = 'delete from `'.$this->dbprefix().'vectors`, `'.$this->dbprefix().'docs` using `'.$this->dbprefix().'vectors`
				inner join `'.$this->dbprefix().'docs`
					on `'.$this->dbprefix().'docs`.id = `'.$this->dbprefix().'vectors`.did
		where 
		`'.$this->dbprefix().'docs`.`index_id` = "'.addslashes($index_id).'"';
		$wpdb->query($q);

		foreach ($chunks as $k => $d) {
			$q = 'select id from `'.$this->dbprefix().'docs` where `index_id` = "'.addslashes($index_id).'" and `token` = "'.addslashes($k).'"';
			$res = $wpdb->get_results($q, ARRAY_A);
			
			if (!isset($res[0]['id'])) {
				// Insert token record
				$wpdb->insert($this->dbprefix().'docs', array(
					'index_id' => $index_id,
					'token' => $k,
					'n' => 0,
				));
				
				$doc_id = $wpdb->insert_id;
				
			} else {
				$doc_id = $res[0]['id'];
			}
			
			$r2 = $this->add(array($doc_id => $d));
			if (!$r2) {
				return false;
			}
		}
		return true;
	}
	
	/** Using bulk insert for vectors **/
	public function add($docs = array()) {
		
		global $wpdb;
		
		// Validate
		if (!is_array($docs)) {
			$this->log('Add document: parameter should be an array');
			return false;
		}
		
		if (count($docs) < 1) {
			// Nothing to do
			return true;
		}
		
		foreach ($docs as $id => $doc) {
			if (!is_numeric($id)) {
				$this->log('Add document: bad index "'.$id.'" given.');
				return false;
			} else {
				$a_ids[] = $id;
			}
		}
		
		$pfx = $this->dbprefix();

		$wordlist = array();
		$doclist = array();
		foreach ($docs as $id => $doc) {
			
			if (!isset($doc) || (mb_strlen($doc) < 1)) {
				continue;
			}
			
			$words = $this->split_to_words($doc);
			$num_of_words = count($words);
			$doclist[$id] = $num_of_words;

			// Remove 1-char words, stop words and too long words
			$w2 = array();
			foreach ($words as $k => $v) {
				$len = mb_strlen($v);
				$lv = mb_strtolower($v);
				if (($len > 1) && ($len <= $this->max_word_length) && (!isset($this->stops[$lv]))) {
					if (!isset($w2[$lv])) {
						$w2[$lv] = 1;
					} else {
						$w2[$lv] ++;
					}
				}
			}
			
			foreach ($w2 as $k => $v) {
				$wordlist[] = array($k, $id, 1 / log( ($num_of_words + 1) / $v));	// Weight of word
			}
			
			$wpdb->update($pfx.'docs', array('n' => $num_of_words), array('id' => $id));
		}

		// lock the tables in case some other process remove a certain word
		// between step 0 and 1 and 2 and 3
		if ($this->_islock) {
			$q = 'lock tables `'.$pfx.'vectors` write, `'.$pfx.'words` write';
			$wpdb->query($q);

			if ($wpdb->last_error) {
				// Disable locking
				$this->_islock = false;
				$wpdb->query('unlock tables');
				//$this->log('Add document: Error when locking tables: '.$wpdb->last_error);
				//return false;
			}
		}

		// Remove old vectors
		$q = 'delete from `'.$pfx.'vectors` where `did` in ('.implode(',', $a_ids).')';
		$wpdb->query($q);
		
		if ($wpdb->last_error) {
			$this->log('Add document: Error when removing old vectors: '.$wpdb->last_error);
			if ($this->_islock) {
				$wpdb->query('unlock tables');
			}
			return false;
		}

		// Insert words to `words` table
		$wordlist_ch = array_chunk($wordlist, 1000);
		foreach ($wordlist_ch as $d) {
		
			// Insert new data
			$q = 'start transaction';
			$wpdb->query($q);
		
			$z = array();
			$vec = array();
			foreach ($d as $dd) {
				$z[] = '("'.addslashes($dd[0]).'")';
				$vec[] = '('.$dd[1].', (select id from `'.$pfx.'words` where `word` = "'.addslashes($dd[0]).'"), '.$dd[2].')';
			}

			$q = 'insert ignore into `'.$pfx.'words` (`word`) values '.implode(',', $z);
			$wpdb->query($q);

			if ($wpdb->last_error) {
				$this->log('Add document: can not add words. Error: '.$wpdb->last_error);
				if ($this->_islock) {
					$wpdb->query('unlock tables');
				}
				$wpdb->query('rollback');
				return false;
			}
			
			// Generate bulk request for vectors
			$q = 'insert ignore into `'.$pfx.'vectors` (`did`,`wid`,`f`) values '.implode(',', $vec);
			//$q = 'insert into `'.$pfx.'vectors` (`did`,`wid`,`f`) values '.implode(',', $vec);
			$wpdb->query($q);
			
			if ($wpdb->last_error) {
				$this->log('Add vectors: can not add vector. Error: '.$wpdb->last_error);
				if ($this->_islock) {
					$wpdb->query('unlock tables');
				}
				$wpdb->query('rollback');
				return false;
			}
			$wpdb->query('commit');

		}
		if ($this->_islock) {
			$wpdb->query('unlock tables');
		}
		
		return true;
	}

	function is_stop_word($word) {
		return isset($this->stoplist[mb_strtolower($word)]);
	}

	function parse_search_terms($a, &$wpq) {
		
		global $wpfts_core;

		$z = array();
		foreach ($a as $d) {
			$v = mb_strtolower(trim($d), 'utf-8');
			$is_quoted = (mb_strlen($v) > 0) && ($v[0] == '"');
			if ($is_quoted) {
				$v = trim($v, '"');
			}
			if (mb_strlen($v) > 0) {
				if (($wpfts_core != null) && ($wpfts_core->get_option('internal_search_terms') != 0)) {
					$vv = $this->split_to_words($d);
					$v = implode(' ', $vv);
					if ($is_quoted) {
						$v = '"'.$v.'"';
					}
				}	
				$z[] = $v;
			}
		}
		
		return apply_filters('wpfts_search_terms', $z, $wpq);
	}
	
	function sql_parts(&$wpq, $cw, $issearch, $nocache) {
		
		global $wpdb, $wpfts_core;
		
		$pfx = $this->dbprefix();

		$q = &$wpq->query_vars;
		
		$join = '';
		$fields = '';
		$orderby = '';
		$where_part = '';
		$matches = array();
		if ((!empty( $q['s'])) && ($issearch)) {
			
			$qs = stripslashes($q['s']);
			if ( empty( $_GET['s'] ) && $wpq->is_main_query() ) {
				$qs = urldecode( $qs );
			}

			$qs = str_replace( array( "\r", "\n" ), '', $qs );
			$q['search_terms_count'] = 1;
			if ( ! empty( $q['sentence'] ) ) {
				$q['search_terms'] = array( $qs );
			} else {
				if ( preg_match_all( '/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $qs, $matches ) ) {
					$q['search_terms_count'] = count( $matches[0] );
					$q['search_terms'] = $this->parse_search_terms( $matches[0], $wpq );
					// if the search string has only short terms or stopwords, or is 10+ terms long, match it as sentence
					if ( empty( $q['search_terms'] ) || count( $q['search_terms'] ) > 9 ) {
						$q['search_terms'] = array( $qs );
					}
				} else {
					$q['search_terms'] = array( $qs );
				}
			}

			// Decode terms
			$minchars = 3;
			$ts = array();
			foreach ($q['search_terms'] as $t) {
				$f = !empty( $q['exact'] ) ? 1 : 0;
				if (mb_substr($t, 0, 1, 'utf-8') == '"') {
					$t2 = explode(' ', trim($t, '"'));
					$f = 1;
				} else {
					$t2 = explode(' ', trim($t));
				}
				if (is_array($t2)) {
					foreach ($t2 as $tt) {
						if (mb_strlen(trim($tt), 'utf-8') >= $minchars) {
							if ($f) {
								$ts[] = array(1, trim($tt));
							} else {
								$ts[] = array(0, trim($tt));
							}
						}
					}
				}
			}
			$q['search_terms'] = $ts;
			$q['search_terms_count'] = count($ts);

			$j = '';
			$i = 0;
			if ($q['search_terms_count'] > 0) {
			
				$q['search_orderby_title'] = array();
				$i = 1;
				$is_deeper_search = false;
				if ($wpfts_core->get_option('deeper_search') != 0) {
					$is_deeper_search = true;
				}

				$algorithm_id = 1;				
				
				$w_isnull = array();
				foreach ( $q['search_terms'] as $term ) {
				
					if ($i == 1) {
						$j .= ' `'.$pfx.'docs` tbase 
						';
					}
					if (!$term[0]) {
						// Like
						$trm = $is_deeper_search ? '%'.$wpdb->esc_like( $term[1] ).'%' : $wpdb->esc_like( $term[1] ).'%';
						$j .= '	LEFT JOIN (
								select 
									ds1.id,
									ds1.index_id,
									ds1.token,
									sum(v1.f * '.mb_strlen($trm).' / length(w1.`word`)) fs
								from `'.$pfx.'words` w1	
								straight_join `'.$pfx.'vectors` v1
									on v1.wid = w1.id
								straight_join `'.$pfx.'docs` ds1
									on v1.did = ds1.id
								where
									(w1.`word` like "'.$trm.'")
								group by ds1.id
								) t'.$i.' 
									on t'.$i.'.id = tbase.id
								';
								
					} else {
						$j .= '	LEFT JOIN (
								select 
									ds1.id,
									ds1.index_id,
									ds1.token,
									sum(v1.f * '.mb_strlen($term[1]).' / length(w1.`word`)) fs
								from `'.$pfx.'words` w1	
								straight_join `'.$pfx.'vectors` v1
									on v1.wid = w1.id
								straight_join `'.$pfx.'docs` ds1
									on v1.did = ds1.id
								where
									(w1.`word` = "'.$wpdb->esc_like( $term[1] ).'")
								group by ds1.id
								) t'.$i.' 
									on t'.$i.'.id = tbase.id
								';
					
					}
					$w_isnull[] = ' (t'.$i.'.id is not null) ';

					$i ++;
				}

				if (mb_strtolower(trim($wpq->get('word_logic', 'and'))) == 'and') {
					$j .= ' where '.implode(' and ', $w_isnull).' ';
				} else {
					$j .= ' where '.implode(' or ', $w_isnull).' ';
				}
				
				$j .= ' group by tbase.index_id
						) t_end
							on t_end.index_id = fi.id
							) '.$pfx.'t
							on '.$pfx.'t.tid = '.$wpdb->posts.'.ID';
		
				$fields = ', '.$pfx.'t.relev ';
			}

			$i --;
			if ($i < 2) {
				$relev = 't1.fs';
			} else {
				$sum = array();
				$for_nulls = array();
				for ($ii = 1; $ii <= $i; $ii ++) {
					$sum[] = 'coalesce(t'.$ii.'.fs,0)';
					$for_nulls[] = 'if(isnull(t'.$ii.'.id),0,1)';
				}
				$relev = '('.implode(' + ', $sum).' + '.implode(' + ', $for_nulls).') / '.count($sum);
			}
			if (count($cw) > 1) {
				$x = array();
				foreach ($cw as $k => $d) {
					//if(t1.token = "post_title", 100, 50)
					$x[] = ' when "'.$k.'" then '.floatval($d);
				}
				$rcv = ' (case tbase.token '.implode('', $x).' else 1 end)';
			} else {
				$rcv = 1;
			}
			
			if ($i > 0) {
				// first "left join" -> "inner join"
				$jhdr = ' inner join (
							select 
								fi.tid,
								t_end.relev
							from `'.$pfx.'index` fi
							inner join (
								select
									tbase.index_id, 
									sum('.$relev.' * '.$rcv.') relev
								from ';
			
				$join .= $jhdr.$j;

				// Create orderby part of MySQL query
				$orderby = ' ('.$pfx.'t.relev)';
				$where_part = ' and ('.$pfx.'t.relev > 0)';
			} else {
				$issearch = 0;
			}
			
		} else {
			$issearch = 0;
		}
		
		$parts = array(
				'token' => md5(time().'|'.uniqid('session')),
				'issearch' => $issearch,
				'nocache' => $nocache,
				'join' => $join,
				'select' => ' and (((1)))'.$where_part,	// "and (not isnull(wpftsi_t.tid))" => " "
				'orderby' => $orderby,
				'fields' => $fields,
				'sql_no_cache' => $nocache ? ' SQL_NO_CACHE' : '',
			);
			
		return $parts;
	}
	
	function sql_joins($join, &$wpq, $cw) {
		
		if ((isset($wpq->wpftsi_session['token'])) && ($wpq->wpftsi_session['issearch'])) {
			return $join.$wpq->wpftsi_session['join'];
		}
		return $join;
	}
	
	/**
	 * Constructing SQL search part
	 * 
	 * @param string $search Search SQL from WP
	 * @param WP_Query $wpq WP query object
	 */
	function sql_select($search, &$wpq) {
		
		if ((isset($wpq->wpftsi_session['token'])) && ($wpq->wpftsi_session['issearch'])) {
			$search = $wpq->wpftsi_session['select'];
		}
		
		return $search;
	}
	
	function sql_orderby($orderby, &$wpq) {
		
		if ((isset($wpq->wpftsi_session['token'])) && ($wpq->wpftsi_session['issearch']) && (strlen($wpq->wpftsi_session['orderby']) > 2)) {

			// Only replace if orderby = empty or orderby = relevance
			$t = $wpq->get('orderby');
			if ((strlen(trim($t)) < 1) || ($t == 'relevance')) {

				$t2 = $wpq->get('order');
				if ($t2 != 'ASC') {
					$t2 = 'DESC';
				}
				$orderby = $wpq->wpftsi_session['orderby'].' '.$t2;
			}
		}
		
		return $orderby;
	}
	
	function sql_pre_posts(&$wpq, $cw) {
		
		if ((!isset($wpq->wpftsi_session['token'])) || (!$wpq->wpftsi_session['token'])) {
			
			$disable = (isset($wpq->query_vars['wpfts_disable']) && ($wpq->query_vars['wpfts_disable'])) ? 1 : 0;
			$nocache = (isset($wpq->query_vars['wpfts_nocache']) && ($wpq->query_vars['wpfts_nocache'])) ? 1 : 0;
			
			// Calculate data
			$sql_parts = $this->sql_parts($wpq, $cw, $disable ? 0 : 1, $nocache);
			$sql_parts['token'] = md5(time().'|'.uniqid('session'));
			$wpq->wpftsi_session = $sql_parts;
		}
	}
	
	function sql_posts_fields($fields, &$wpq) {

		if ((isset($wpq->wpftsi_session['token'])) && ($wpq->wpftsi_session['issearch'])) {
			return $fields.$wpq->wpftsi_session['fields'];
		}
		
		return $fields;
	}
	
	function sql_posts_distinct($distinct, &$wpq) {

		if ((isset($wpq->wpftsi_session['token'])) /*&& ($wpq->wpftsi_session['issearch'])*/) {
			return str_replace('SQL_NO_CACHE', '', $distinct).$wpq->wpftsi_session['sql_no_cache'];
		}
		
		return $distinct;
	}
	
	function sql_the_posts($posts, &$wpq) {

		if (isset($wpq->wpftsi_session)) {
			$wpq->wpftsi_session = null;
		}
		return $posts;
	}
	
	function getRecordsToRebuild($n_max = 1) {
		
		global $wpdb;
		
		$idx = $this->dbprefix();
		
		$time = time();
		$time2 = date('Y-m-d H:i:s', $time - $this->lock_time);
		
		$q = 'select 
					id, tid, tsrc 
			from `'.$idx.'index` 
			where 
				((force_rebuild != 0) or (build_time = 0)) and 
				((locked_dt = "1970-01-01 00:00:00") or (locked_dt < "'.$time2.'"))
			order by build_time asc, id asc 
			limit '.intval($n_max).'';
		$r = $wpdb->get_results($q, ARRAY_A);
		
		return $r;
	}
	
	function checkAndSyncWPPosts($current_build_time) {
		
		global $wpdb;
		
		$idx = $this->dbprefix();
		
		// Step 1. Mark index rows contains old posts and posts with wrong date of post or build time.
		$q = 'update `'.$idx.'index` wi
				left join `'.$wpdb->posts.'` p
					on p.ID = wi.tid
				set 
					wi.force_rebuild = if(p.ID is null, 2, if ((wi.build_time = "'.addslashes($current_build_time).'") and (wi.tdt = p.post_modified), 0, 1))
				where 
					(wi.tsrc = "wp_posts") and (wi.force_rebuild = 0)';
		$wpdb->query($q);
		
		// Step 2. Find and add new posts // @todo need to be optimized!
		$q = 'insert ignore into `'.$idx.'index` 
				(`tid`, `tsrc`, `tdt`, `build_time`, `update_dt`, `force_rebuild`, `locked_dt`) 
				select 
					p.ID tid,
					"wp_posts" tsrc,
					"1970-01-01 00:00:00" tdt,
					0 build_time,
					"1970-01-01 00:00:00" update_dt,
					1 force_rebuild,
					"1970-01-01 00:00:00" locked_dt
				from `'.$wpdb->posts.'` p';
		$wpdb->query($q);
		
		// Step 3. What else?
	}
	
	function get_status() {
		
		global $wpdb;
		
		$idx = $this->dbprefix();
		
		$q = 'select 
				sum(if (build_time != 0, 1, 0)) n_inindex, 
				sum(if ((force_rebuild = 0) and (build_time != 0), 1, 0)) n_actual,
				sum(if ((force_rebuild != 0) or (build_time = 0), 1, 0)) n_pending
			from `'.$idx.'index` 
			where tsrc = "wp_posts"';
		$res = $wpdb->get_results($q, ARRAY_A);
		
		$ret = array();
		if (isset($res[0]['n_inindex'])) {
			$ret = array(
				'n_inindex' => $res[0]['n_inindex'],
				'n_actual' => $res[0]['n_actual'],
				'n_pending' => $res[0]['n_pending'],
			);
		} else {
			$ret = array(
				'n_inindex' => 0,
				'n_actual' => 0,
				'n_pending' => 0,
			);
		}
		
		return $ret;
	}
	
	function getClusters() {
		
		global $wpdb;
		
		$idx = $this->dbprefix();
		
		$z = array('post_title' => 1, 'post_content' => 1);
		
		$q = 'select distinct `token` from `'.$idx.'docs` limit 100';
		$res = $wpdb->get_results($q, ARRAY_A);
		
		$z = array();
		foreach ($res as $d) {
			if (!isset($z[$d['token']])) {
				$z[$d['token']] = 1;
			}
		}
		
		return array_keys($z);
	}

	function lockUnlockedRecord($id) {
		
		global $wpdb;
		
		$idx = $this->dbprefix();
		
		$time = time();
		$time2 = date('Y-m-d H:i:s', $time - $this->lock_time);
		$new_time = date('Y-m-d H:i:s', $time);
		
		$q = 'select id, if((locked_dt = "1970-01-01 00:00:00") or (locked_dt < "'.$time2.'"), 0, 1) islocked from `'.$idx.'index` where id = "'.addslashes($id).'"';
		$res = $wpdb->get_results($q, ARRAY_A);
		
		if (isset($res[0])) {
			if ($res[0]['islocked']) {
				// Already locked
				return false;
			} else {
				// Lock it
				$wpdb->update($idx.'index', array('locked_dt' => $new_time), array('id' => $id));
				return true;
			}
		} else {
			// Record not found
			return false;
		}
		
	}
	
	function unlockRecord($id) {
		
		global $wpdb;
		
		$idx = $this->dbprefix();
		
		$wpdb->update($idx.'index', array('locked_dt' => '1970-01-01 00:00:00'), array('id' => $id));
	}
	
	function updateRecordData($id, $data = array()) {
		
		global $wpdb;
		
		$idx = $this->dbprefix();
		
		$a = array();
		foreach ($data as $k => $d) {
			if (in_array($k, array('tdt', 'build_time', 'update_dt', 'force_rebuild', 'locked_dt'))) {
				$a[$k] = $d;
			}
		}
		$wpdb->update($idx.'index', $a, array('id' => $id));
	}
	
	function insertRecordData($data = array()) {
		
		global $wpdb;
		
		$idx = $this->dbprefix();
		
		$a = array();
		foreach ($data as $k => $d) {
			if (in_array($k, array('tdt', 'build_time', 'update_dt', 'force_rebuild', 'locked_dt', 'tid', 'tsrc'))) {
				$a[$k] = $d;
			}
		}
		$wpdb->insert($idx.'index', $a);
		
		return $wpdb->insert_id;
	}
	
	function updateIndexRecordForPost($post_id, $modt, $build_time, $time = false, $force_rebuild = 0) {
		
		global $wpdb;
		
		if ($time === false) {
			$time = time();
		}
		
		$q = 'select * from `'.$this->dbprefix().'index` where (`tid` = "'.$post_id.'") and (`tsrc` = "wp_posts")';
		$res = $wpdb->get_results($q, ARRAY_A);
		
		if (isset($res[0])) {
			// Update existing record
			$this->updateRecordData(
					$res[0]['id'], 
					array(
						'tdt' => $modt,
						'build_time' => $build_time,
						'update_dt' => date('Y-m-d H:i:s', $time),
						'force_rebuild' => $force_rebuild,
						'locked_dt' => '1970-01-01 00:00:00',
						)
			);
			
			return $res[0]['id'];
		} else {
			// Insert new record
			$insert_id = $this->insertRecordData(
					array(
						'tid' => $post_id,
						'tsrc' => 'wp_posts',
						'tdt' => $modt,
						'build_time' => $build_time,
						'update_dt' => date('Y-m-d H:i:s', $time),
						'force_rebuild' => $force_rebuild,
						'locked_dt' => '1970-01-01 00:00:00',
						)
			);
			
			return $insert_id;
		}
	}
	
	function getColumn($a, $col) {
		$r = array();
		foreach ($a as $d) {
			if (isset($d[$col])) {
				$r[] = $d[$col];
			}
		}
		return $r;
	}
	
	function removeIndexRecordForPost($post_id) {
		
		global $wpdb;
		
		$idx = $this->dbprefix();
		
		$q = 'select `id` from `'.$idx.'index` where (`tid` = "'.addslashes($post_id).'") and (`tsrc` = "wp_posts")';
		$res_index = $wpdb->get_results($q, ARRAY_A);
		
		if (isset($res_index[0])) {
			$q = 'select `id` from `'.$idx.'docs` where `index_id` in ('.implode(',', $this->getColumn($res_index, 'id')).')';
			$res_docs = $wpdb->get_results($q, ARRAY_A);
			
			if (isset($res_docs[0])) {
				$q = 'delete from `'.$idx.'vectors` where `did` in ('.implode(',', $this->getColumn($res_docs, 'id')).')';
				$wpdb->query($q);
				
				$q = 'delete from `'.$idx.'docs` where `index_id` in ('.implode(',', $this->getColumn($res_index, 'id')).')';
				$wpdb->query($q);
			}
			
			$q = 'delete from `'.$idx.'index` where (`tid` = "'.addslashes($post_id).'") and (`tsrc` = "wp_posts")';
			$wpdb->query($q);
		}
		
		return true;
	}
}
