=== Post Snippets ===
Contributors: artstorm
Donate link: http://wpstorm.net/wordpress-plugins/post-snippets/#donation
Tags: post, admin, snippet, html, custom, page, dynamic, editor, quicktag
Requires at least: 2.7
Tested up to: 3.0.1
Stable tag: 1.5.4

Store snippets of HTML code or reoccurring text that you often use in your posts. Custom variables can be used.

== Description ==

This admin plugin stores snippets of HTML code or reoccurring text that you often use in your posts. You can use predefined variables to replace parts of the snippet on insert. All snippets are available in the post editor with a TinyMCE button. The snippet can be inserted as defined, or as a shortcode to keep flexibility for updating the snippet.

For complete usage instructions see: [Post Snippets](http://wpstorm.net/wordpress-plugins/post-snippets/ "Complete Usage Instructions for Post Snippets") 

See the [Changelog](http://wordpress.org/extend/plugins/post-snippets/changelog/) for what's new. Available [Translations](http://wpstorm.net/wordpress-plugins/post-snippets/#translations).

= Features =

* **Insert** All defined snippets is inserted from a button directly in the post editor.
* **Shortcodes** You can use this plugin to create your own shortcodes.
* **Buttons** The snippets are available in the viusal editor with a TinyMCE button and in the HTML editor with quicktag buttons.
* **Admin** Easy to use administration panel where you can add, edit and remove snippets.
* **Variables** Each snippet can have as many custom variables as you like, which can be used on insert.
* **Uninstall** If you delete the plugin from your plugins panel it cleans up all data it has created in the Wordpress database. 


== Installation ==

= Install =

1. Upload the 'post-snippets' folder  to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to Settings -> Post Snippets and start entering your snippets.

= Uninstall =

1. Deactivate Post Snippets in the 'Plugins' menu in Wordpress.
2. Select Post Snippets in the 'Recently Active Plugins' section and select 'Delete' from the 'Bulk Actions' drop down menu.
3. This will delete all the plugin files from the server as well as erasing all options the plugin has stored in the database.

== Frequently Asked Questions ==

Please visit [Post Snippets' Comments](http://wpstorm.net/wordpress-plugins/post-snippets/#comments) for questions and answers.

== Screenshots ==

1. The Admin page where you set up new snippets.
2. The TinyMCE button for Post Snippets.
3. The Post Snippet Insert Window.

== Changelog ==

= Version 1.5.4 - 26 Jan 2011 =
 * Included Turkish translation by [Ersan Özdil](http://www.tml.web.tr/).
 
= Version 1.5.3 - 19 Sep 2010 =
 * Included Spanish translation by [Melvis E. Leon Lopez](http://www.soludata.net/site/).

= Version 1.5.2 - 17 Sep 2010 =
 * The plugin now keeps linefeed formatting when inserting a snippet directly with a quicktag in the HTML editor. 
 * Updated the code to not generate warnings when running WordPress in debug mode.

= Version 1.5.1 - 12 Mar 2010 =
 * Fixed ampersands when used in a shortcode, so they are XHTML valid.

= Version 1.5 - 12 Jan 2010 =
 * Updated the plugin so it works with WordPress 2.9.x (the quicktags didn't work in 2.9, now fixed.).

= Version 1.4.9.1 - 5 Sep 2009 =
 * Included French translation by [Thomas Cailhe (Oyabi)](http://www.oyabi.fr/).

= Version 1.4.9 - 10 Aug 2009 =
 * Included Russian translation by [FatCow](http://www.fatcow.com/).
 
= Version 1.4.8 - 9 May 2009 =
 * Changed the handling of the TinyMCE button as some server configurations had problems finding the correct path.
 * Fixed a problem that didn't let a snippet contain a </script> tag.
 
= Version 1.4.7 - 27 Apr 2009 =
 * Added a workaround for a bug in WordPress 2.7.x wp-includes/compat.php that prevented the plugin to work correctly on webservers running with PHP below version 5.1.0 together with WP 2.7.x. This bug is patched in WordPress 2.8.

= Version 1.4.6 - 25 Apr 2009 =
 * Updated all code to follow the WordPress Coding Standards for consistency, if someone wants to modify my code.
 * Removed the nodechangehandler from the TinyMCE js, as it didn't fill any purpose.
 * Updated the save code to remove the PHP Notice messages, if using error logging on the server.
 * Added additional proofing for the variables string.

= Version 1.4.5 - 24 Apr 2009 =
 * Fixed a problem in the admin options that didn't allow a form with a textarea to be used as a snippet.
 * Widened the columns for SC and QT slightly in the options panel so they should look a bit better on the mac.

= Version 1.4.4 - 19 Apr 2009 =
 * Minor fix with quicktags and certain snippets that was left out in the last update.
 
= Version 1.4.3 - 16 Apr 2009 =
 * Fixed an escaping problem with the recently implemented shortcode function, that could cause problems on certain strings.
 * Fixed an escaping problem with the quicktag javascript, that could cause problems on certain strings.

= Version 1.4.2 - 11 Apr 2009 =
 * Fixed some additional syntax for servers where the short_open_tag configuration setting is disabled.

= Version 1.4.1 - 10 Apr 2009 =
 * Removed all short syntax commands and replaced them with the full versions so the plugin also works on servers with the short_open_tag configuration setting disabled.

= Version 1.4 - 10 Apr 2009 =
 * Added a checkbox for Shortcodes (SC) in the admin panel. When checking this one a dynamic shortcode will be generated and inserted instead of the snippet, which allows snippets to be updated later on for all posts it's been inserted into when using this option.
 * Added a checkbox for Quicktags (QT) in the admin panel, so Quicktags are optional. Speeds up loading of the post editor if you don't need the quicktag support, and only use the visual editor. Defaults to off.
 
= Version 1.3.5 - 9 Apr 2009 =
 * Fixed so the TinyMCE window adds a scrollbar if there is more variables for a snippet than fits in the window.
 * Fixed a bug that snippets didn't get inserted when using the visual editor in fullscreen mode.
 
= Version 1.3 - 2 Apr 2009 =
 * Fixed a problem with the regular expressions that prohibited variables consisting of just a single number to work.
 * Updated the Help info in the admin page to take less space.
 * Included a check so the plugin only runs in WP 2.7 or newer.

= Version 1.2 - 1 Apr 2009 =
 * Added support for Quicktags so the snippets can be made available in the HTML editor as well.
 
= Version 1.1 - 24 Mar 2009 =
 * Included Swedish translation.
 * Added TextDomain functionality for I18n.

= Version 1.0 - 23 Mar 2009 =
 * Initial Release