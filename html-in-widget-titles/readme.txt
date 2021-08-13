=== HTML Widget Titles ===
Contributors: jabelone
Tags: html, titles, titles, widgets, widget
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=GZMGBSVXBK4BL
Requires at least: 0.71
Tested up to: 4.7.2
Stable tag: 1.1
License: GNU GPL3 or later
License URI: http://www.gnu.org/licenses/gpl.html

Allows the use of HTML in the title of any widget.

== Description ==
Features:
This is about as basic as a plugin gets.  HTML In Widget Titles allow you to use any normal HTML tag(s) in the Title section of any widget.  While originally designed for making titles linkable, it should also work for any other HTML tags.

Usage:
Simply replace the html arrows with [ or ] and use the HTML tags like normal. eg. [a href=#][/a]

Example:
Instead of using: &lt;a href=https://jabelone.com.au&gt;Click This Link&lt;/a&gt; <br>
Simply use: [a href=https://jabelone.com.au]Click This Link[/a]

How it works:
The plugin uses some very basic PHP and a WordPress filter to replace all instances of [ or ] with arrows.  (in the plugin title) This is because when you enter a normal <tag> into the title input area, WordPress will remove it.  This replacement happens as the page is generated from the PHP code so it avoids this problem.

Configuration:
If you wish to change what the replacement is than it is quite easy.  From the plugins menu select "editor" than in the top right hand corner choose \"HTML In Widget Text\" and click \"Select\".  Find the lines that look like the ones below and replace the [ and ].  Be sure to not change anything else!

	$html_widget_title_tagopen = "["; //Our HTML opening tag replacement
	$html_widget_title_tagclose = "]"; //Our HTML closing tag replacement

== Installation ==
Simply move the "html-in-widget-titles" folder into the WordPress directory "wp-content/plugins" or search for "HTML in widget titles" on the add new plugins page.

== Frequently Asked Questions ==
Q: I have changed the replace "[" and "]" to something else and it stopped working!
A: Delete the plugin, re-download it and try chaning it again.  Make sure you are extremely careful to only change the [ or ] and nothing else.  If you are still having problems open a bug/issue request.

Q: It isn't working or it is displaying some kind of error!
A: I haven't been able to check on EVERY WordPress version so please leave the version you are using and any errors you are getting in a comment and I will get back to you.

== Changelog ==
Version 1.0 (initial release)
Currently no bugs have been identified and all features are working as expected.

Version 1.1
Checked it works with latest WordPress
