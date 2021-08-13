=== WP FullText Search - The Power of Indexed Search ===
Contributors: Epsiloncool
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=EJ6FG3AJKYGG8&item_name=New+laptop+for+faster+development&currency_code=USD&source=url
Tags: fulltext search, indexed, attachments, pdf, highlight words
Requires at least: 3.0.1
Tested up to: 5.2.2
Stable tag: 1.18.35
License: GPL3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This plugin creates a transparent word-based index to speed up the search, add a relevance, includes meta fields and custom post types to search index and even more. No external software/service required.

== Description ==

This plugin extends the standard search capabilities of Wordpress by creating a transparent word-based index. This allows you to quickly search not only by the title and content of posts, but also by meta-fields, custom types of posts, and even by the contents of the attached files. Yes, all this is possible!

WPFTS does not require the installation of external indexing software and therefore works even on shared hostings. It does not require any refinement of your site, thus all other plugins will also automatically use the word-based index after installing the WPFTS.

You will be able to justify the relevance function by specifying the weights for the title, the content, and each of the meta-fields in your posts.

Unlike other search plug-ins, WPFTS does not replace the standard WP search, instead, it significantly expands its functionality. Thus, all built-in functions of WP_Query are saved, in addition, other plug-ins begin to use advanced search automatically.

The extended (pro) version of the plugin allows you to automatically index the text content of the attached files (for example, PDF files, full list of supported files is listed in Documentation) and perform a quick search on them.

Here is a short summary of capabilities in this FREE version:

* TRUE indexed text search within title, content, meta values or programmatically-created text data
* Dramatically extends the native Wordpress search (works via WP_Query())
* Works with both MySQL table types (MYISAM, InnoDB)
* Supports Multisite (yes, in free version)
* Supports powerful index clustering system (to assign different relevance weights)
* Supports AND and OR logic 
* Ordering results by relevance, date, post ID, title, slug, type, random, comment_count
* Does not require 3rd-party libraries or services
* Displays search results like Google does (it shows sentences with queried words and highlighting them)
* Works well on shared hostings
* Supports language translations
* Dramatically extends default WP search (not replaces it)
* Removes HTML tags and comments from post content before indexing it (useful for Gutenberg-driven sites)
* Search full-text with true relevance
* Relevance formula can be justified via settings (post title, content and each meta field can have different weights)
* Make default search WP ordering configurable (very useful for WP site search via ?s=<query>)
* It has API and full documentation to customize plugin's behaviour
* Works well with PHP 5.6+ to PHP 7.2+

Please note, the [PRO](https://fulltextsearch.org/buy/ "WP FullText Search Pro plugin") version of this plugin also supports:

* File attachments search by their content (PDF, DOC, DOCX and other files currently supported)
* Filter file search by filetype
* Display file content in search results using Smart Excerpts
* External service to extract text information from files can be used (license included)
* Technical support (with installing, configuring, fixing conflicts)
* Regular automatic updates (it works the same way as WP repository updates)



= Documentation =

Please refer [Documentation](https://fulltextsearch.org/documentation/ "WP FullText Search Documentation").

== Installation ==

1. Unpack and upload `fulltext-search` folder with all files to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Press `Rebuild Index` button to initialize index (actually this function will run automatically on first plugin install)

== Frequently Asked Questions ==

= Where can I put some notices, comments or bugreports? =

Do not hesistate to write to us at [Contact Us](https://fulltextsearch.org/contact/ "Contact Us") page.

== Screenshots ==

1. Smart Excerpts (Google-like search results) settings
2. Plugin configuration screen
3. Search and Relevance settings
4. Standard WP search tweaks (change ordering and direction of search)
5. Search core status widget
6. Indexing engine settings
7. Search tester screen

== Changelog ==

= 1.18.35 =
* Added CSS style editor for Smart Excerpts block
* Added external parameters for WP_Query: "word_logic" and "wpfts_disable" (refer documentation)
* Fixed: Smart Excerpts now works well for content contains non-UTF-8 characters

= 1.17.33 =
* Fixed: The Smart Excerpts algorithm was completely rebuilt. Now working on any post and excerpt length. Thanks to Kathy

= 1.16.31 =
* Fixed: single UTF-8 quote issue made some "beautifyed strings" unsearchable. Fixed now. Thanks to Sophia.

= 1.16.29 =
* Fixed: make text search case insensitive not depends from MySQL config. Thanks to Sophia

= 1.16.27 =
* Fixed: search result items with zero relevance does not show anymore (BIG thanks to @bolus150 for the bugreport!)
* Added possibility to set up cluster_weights as a WP_Query parameter
* Added wpfts_cluster_weights filter
* Added Settings option to strip_tags before put post content to the index (useful for Gutenberg driven sites)

= 1.15.24 =
* Localization improved (new pot file, added __ in some places the code)

= 1.14.22 =
* Big update: lots of functions was moved from the Pro version to the Free WPFTS Version
* Interface bugs were fixed
* Relevance formula was completely rebuilt
* Reindex algorithm was sufficiently improved (now 5 times faster!)
* Word max length was increased to 255 characters

= 1.11.16 =
* Code optimizations
* Indexing speed increased

= 1.11.15 =
* Improved compatibility with Wordpress 5.2.2
* Fixed 3 small issues

= 1.10.14 =
* Fixed an issue with database locking with MYISAM
* Small interface fixes

= 1.10.13 =
* Fixed an issue with indexing
* Added compatibility with Wordpress 5.2

= 1.10.12 =
* Fixed 3 issues

= 1.10.11 =
* Improved compatibility with WP 5.1
* Fixed 7 issues

= 1.9.10 =
* Added Google-like Smart Excerpts

= 1.8.9 =
* Fixed 5 tiny bugs (thanks users for reports!)

= 1.8.7 =
* Added Multisite support

= 1.7.6 =
* Fixed 9 warnings and 21 notices while optimizing plugin for PHP 7.2
* Added support of PHP 7.2

= 1.7.5 =
* Added Main WP Search Tweaks settings

= 1.6.4 =
* Fixed a bug - it was a reason why plugin can't activate correctly on some hostings

= 1.6.3 =
* Added InnoDB support
* Added a switch of MySQL table type (InnoDB/MySQL)
* Fixed a bug with popup message

= 1.6.2 =
* Fixed MySQL queries: search speed sufficiently improved

= 1.6.1 =
* Added "Deeper Search" flag and functionality

= 1.6.0 =
* Added support for internal query filtering
* Added wpfts_search_terms filter
* Fixed some indexing speed issues

= 1.5.9 =
* Fixed Readme.txt
* Fixed queries to WP multisite support

= 1.5.8 =
* Compatibility with WP 4.8.1
* Indexing speed increased a bit (code was optimized)

= 1.4.6 =
* Added support for sites with specific DB table names

= 1.3.4 =
* Cosmetic changes

= 1.2.3 =
* Changed regexp which is splitting texts to words (non-english characters are now supported)
* Added `wpftp_split_to_words` filter which enables you to define your own "text splitting" algorithm

= 1.2.1 =
* Added complex query analyzer (support quotes)

= 1.1.7 =
* Added plugin icon
* Fixed description

= 1.1.6 =
* Lowered save_post hook priority to index metadata correctly

= 1.1.5 =
* Small bug fixes
* Debug logging removed

= 1.1.4 =
* Added cluster weights capability
* Plugin assigned to GPL license

= 1.0 =
* First Wordpress version

= 0.4 =
* Automatic indexing were added, over 30 bugs were fixed

= 0.1 =
* Initial edition. Basic functions added

== Upgrade Notice ==

= 1.1.4 =
* Upgrade immediately, because of some security issues found and fixed

= 1.0 =
* First version to be in Wordpress repository, just install it
