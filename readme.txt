=== Post Snippets ===
Contributors: artstorm
Donate link: http://coding.cglounge.com/wordpress-plugins/post-snippets/#pintware
Tags: post, admin, snippet, html, custom, page, dynamic, editor, quicktag
Requires at least: 2.7
Tested up to: 2.7.1
Stable tag: 1.4.2

Store snippets of HTML code or reoccurring text that you often use in your posts. Custom variables can be used.

== Description ==

This admin plugin stores snippets of HTML code or reoccurring text that you often use in your posts. You can use predefined variables to replace parts of the snippet on insert. All snippets are available in the post editor with a TinyMCE button. The snippet can be inserted as defined, or as a shortcode to keep flexibility for updating the snippet.

For complete usage instructions see: [Post Snippets](http://coding.cglounge.com/wordpress-plugins/post-snippets/ "Complete Usage Instructions for Post Snippets") 

See the [Changelog](http://wordpress.org/extend/plugins/post-snippets/other_notes/) for what's new.

Some features:

* **Insert** All defined snippets is inserted from a button directly in the post editor.
* **Shortcodes** You can use this plugin to create your own shortcodes.
* **Buttons** The snippets are available in the viusal editor with a TinyMCE button and in the HTML editor with quicktag buttons.
* **Admin** Easy to use administration panel where you can add, edit and remove snippets.
* **Variables** Each snippet can have as many custom variables as you like, which can be used on insert.
* **Uninstall** If you delete the plugin from your plugins panel it cleans up all data it has created in the Wordpress database. 


== Installation ==

1. Upload the 'post-snippets' folder  to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to Settings -> Post Snippets and start entering your snippets.


== Frequently Asked Questions ==

= Guru? =

Meditation.

== Screenshots ==

1. The Admin page where you set up new snippets.
2. The TinyMCE button for Post Snippets.
3. The Post Snippet Insert Window.

== Changelog ==

= Version 1.4.2 - 11 Apr 2009 =
 * Fixed some additional syntax for servers where the short_open_tag configuration setting disabled.

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
