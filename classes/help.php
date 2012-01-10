<?php
/**
 * Post Snippets Help.
 *
 * Class to handle the help texts and tabs on the settings screen.
 *
 * @package		Post Snippets
 * @author		Johan Steen <artstorm at gmail dot com>
 * @since		Post Snippets 1.8.9
 */
class Post_Snippets_Help
{
	public function __construct( $option_page )
	{
		// If WordPress is 3.3 or higher, use the new Help API, otherwise call
		// the old pre 3.3 help function.
		global $wp_version;
		if ( version_compare($wp_version, '3.3', '>=') ) {
			add_action( 'load-' . $option_page, array(&$this, 'add_help_tabs') );
		} else {
			add_action( 'contextual_help', array(&$this,'add_help'), 10, 3 );
		}
	}

	public function add_help_tabs() {
	    $screen = get_current_screen();
		$screen->set_help_sidebar( 'cp' );
	    $screen->add_help_tab( array(
	        'id'      => 'additional-plugin-help', // This should be unique for the screen.
	        'title'   => 'Special Instructions',
	        'content' => '<p>This is the content for the tab.</p>',
	        // Use 'callback' instead of 'content' for a function callback that renders the tab content.
	    ) );
	}

	/**
	 * Display contextual help in the help drop down menu at the options page.
	 *
	 * @deprecated	Since WordPress 3.3
	 * @since		Post Snippets 1.7.1
	 * @return		string		The Contextual Help
	 */
	public function add_help($contextual_help, $screen_id, $screen) {
		//$contextual_help .= var_dump($screen); // use this to help determine $screen->id
		if ( $screen->id == 'settings_page_post-snippets/post-snippets' ) {
			$contextual_help =
			'<p><strong>' . __('Title', 'post-snippets') . '</strong></p>' .
			'<p>' . __('Give the snippet a title that helps you identify it in the post editor. If you make it into a shortcode, this is the name of the shortcode as well.', 'post-snippets') . '</p>' .

			'<p><strong>' . __('Variables', 'post-snippets') . '</strong></p>' .
			'<p>' . __('A comma separated list of custom variables you can reference in your snippet.<br/><br/>Example:<br/>url,name', 'post-snippets') . '</p>' .

			'<p><strong>' . __('Snippet', 'post-snippets') . '</strong></p>' .
			'<p>' . __('This is the block of text or HTML to insert in the post when you select the snippet from the insert button in the TinyMCE panel in the post editor. If you have entered predefined variables you can reference them from the snippet by enclosing them in {} brackets.<br/><br/>Example:<br/>To reference the variables in the example above, you would enter {url} and {name}.<br/><br/>So if you enter this snippet:<br/><i>This is the website of &lt;a href="{url}"&gt;{name}&lt;/a&gt;</i><br/>You will get the option to replace url and name on insert if they are defined as variables.', 'post-snippets') . '</p>' .

			'<p><strong>' . __('Description', 'post-snippets') . '</strong></p>' .
			'<p>' . __('An optional description for the Snippet. If entered it will be displayed in the snippets popup window in the post editor.', 'post-snippets') . '</p>' .

			'<p><strong>' . __('Shortcode', 'post-snippets') . '</strong></p>' .
			'<p>' . __('Treats the snippet as a shortcode. The name for the shortcode is the same as the title of the snippet (spaces not allowed) and will be used on insert. If you enclose the shortcode in your posts, you can access the enclosed content by using the variable {content} in your snippet. The content variable is reserved, so don\'t use it in the variables field.', 'post-snippets') . '</p>' .

			'<p><strong>' . __('Advanced', 'post-snippets') . '</strong></p>' .
			'<p>' . __('The snippets can be retrieved directly from PHP, in a theme for instance, with the get_post_snippet() function. Visit the Post Snippets link under more information for instructions.', 'post-snippets') . '</p>' .

			'<p><strong>' . __('For more information:', 'post-snippets') . '</strong></p>' .
			'<p>' . __('Visit my <a href="http://wpstorm.net/wordpress-plugins/post-snippets/">Post Snippets</a> page for additional information.', 'post-snippets') . '</p>';
		}
		return $contextual_help;
	}
}
