<?php
/*
Plugin Name: Post Snippets
Plugin URI: http://wpstorm.net/wordpress-plugins/post-snippets/
Description: Stores snippets of HTML code or reoccurring text that you often use in your posts. You can use predefined variables to replace parts of the snippet on insert. All snippets are available in the post editor with a TinyMCE button or Quicktags.
Version: 1.8.8
Author: Johan Steen
Author URI: http://johansteen.se/
Text Domain: post-snippets 

Copyright 2009-2011  Johan Steen  (email : artstorm [at] gmail [dot] com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class Post_Snippets {
	private $tinymce_plugin_name = 'post_snippets';
	var $plugin_options = "post_snippets_options";

	/**
	 * Constructor
	 *
	 */
	public function post_snippets() {
		// Define the domain for translations
		load_plugin_textdomain(	'post-snippets', false, 
			dirname(plugin_basename(__FILE__)) . '/languages/');

		$this->init_hooks();
	}

	/**
	 * Initializes the hooks for the plugin
	 *
	 * @returns	Nothing
	 */
	function init_hooks() {

		// Add TinyMCE button
		add_action('init', array(&$this, 'add_tinymce_button') );

		# Settings link on plugins list
		add_filter( 'plugin_action_links', array(&$this, 'plugin_action_links'), 10, 2 );
		# Options Page
		add_action( 'admin_menu', array(&$this,'wp_admin') );

		$this->create_shortcodes();

		# Adds the JS and HTML code in the header and footer for the jQuery insert UI dialog in the editor
		add_action( 'admin_init', array(&$this,'enqueue_assets') );
		add_action( 'admin_head', array(&$this,'jquery_ui_dialog') );
		add_action( 'admin_footer', array(&$this,'insert_ui_dialog') );
		
		# Add Editor QuickTag button
		// IF WordPress is 3.3 or higher, use the new refactored method to add
		// the quicktag button.
		// Start showing a deprecated message from version 1.9 of the plugin for
		// the old method. And remove it completely when the plugin hits 2.0.
		global $wp_version;
		if ( version_compare($wp_version, '3.3', '>=') ) {
			add_action( 'admin_print_footer_scripts', 
						array(&$this,'add_quicktag_button'), 100 );
		} else {
			add_action( 'edit_form_advanced', array(&$this,'add_quicktag_button_pre33') );
			add_action( 'edit_page_form', array(&$this,'add_quicktag_button_pre33') );
		}
	}


	/**
	 * Quick link to the Post Snippets Settings page from the Plugins page.
	 *
	 * @returns	Array with all the plugin's action links
	 */
	function plugin_action_links( $links, $file ) {
		if ( $file == plugin_basename( dirname(__FILE__).'/post-snippets.php' ) ) {
			$links[] = '<a href="options-general.php?page=post-snippets/post-snippets.php">'.__('Settings', 'post-snippets').'</a>';
		 }
		return $links;
	}


	/**
	 * Enqueues the necessary scripts and styles for the plugins
	 *
	 * @since		Post Snippets 1.7
	 *
	 * @returns		Nothing
	 */
	function enqueue_assets() {
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_style( 'wp-jquery-ui-dialog');

		# Adds the CSS stylesheet for the jQuery UI dialog
		$style_url = plugins_url( '/assets/post-snippets.css', __FILE__);
		wp_register_style('post-snippets', $style_url);
		wp_enqueue_style( 'post-snippets');
	}
	

	// -------------------------------------------------------------------------
	// WordPress Editor Buttons
	// -------------------------------------------------------------------------

	/**
	 * Add TinyMCE button.
	 *
	 * Adds filters to add custom buttons to the TinyMCE editor (Visual Editor)
	 * in WordPress.
	 *
	 * @since	Post Snippets 1.8.7
	 */
	public function add_tinymce_button()
	{
		// Don't bother doing this stuff if the current user lacks permissions
		if ( !current_user_can('edit_posts') &&
			 !current_user_can('edit_pages') )
			return;

		// Add only in Rich Editor mode
		if ( get_user_option('rich_editing') == 'true') {
			add_filter('mce_external_plugins', 
						array(&$this, 'register_tinymce_plugin') );
			add_filter('mce_buttons',
						array(&$this, 'register_tinymce_button') );
		}
	}

	/**
	 * Register TinyMCE button.
	 *
	 * Pushes the custom TinyMCE button into the array of with button names.
	 * 'separator' or '|' can be pushed to the array as well. See the link
	 * for all available TinyMCE controls.
	 *
	 * @see		wp-includes/class-wp-editor.php
	 * @link	http://www.tinymce.com/wiki.php/Buttons/controls
	 * @since	Post Snippets 1.8.7
	 *
	 * @param	array	$buttons	Filter supplied array of buttons to modify
	 * @return	array				The modified array with buttons
	 */
	public function register_tinymce_button( $buttons )
	{
		array_push( $buttons, 'separator', $this->tinymce_plugin_name );
		return $buttons;
	}

	/**
	 * Register TinyMCE plugin.
	 *
	 * Adds the absolute URL for the TinyMCE plugin to the associative array of
	 * plugins. Array structure: 'plugin_name' => 'plugin_url'
	 *
	 * @see		wp-includes/class-wp-editor.php
	 * @since	Post Snippets 1.8.7
	 *
	 * @param	array	$plugins	Filter supplied array of plugins to modify
	 * @return	array				The modified array with plugins
	 */
	public function register_tinymce_plugin( $plugins )
	{
		// Load the TinyMCE plugin, editor_plugin.js, into the array
		$plugins[$this->tinymce_plugin_name] = 
			plugins_url('/tinymce/editor_plugin.js', __FILE__);

		return $plugins;
	}


	// -------------------------------------------------------------------------



	/**
	 * jQuery control for the dialog and Javascript needed to insert snippets into the editor
	 *
	 * @since		Post Snippets 1.7
	 *
	 * @returns		Nothing
	 */
	function jquery_ui_dialog() {
		echo "\n<!-- START: Post Snippets jQuery UI and related functions -->\n";
		echo "<script type='text/javascript'>\n";
		
		# Prepare the snippets and shortcodes into javascript variables
		# so they can be inserted into the editor, and get the variables replaced
		# with user defined strings.
		$snippets = get_option($this->plugin_options);
		for ($i = 0; $i < count($snippets); $i++) {
			if ($snippets[$i]['shortcode']) {
				# Build a long string of the variables, ie: varname1={varname1} varname2={varname2}
				# so {varnameX} can be replaced at runtime.
				$var_arr = explode(",",$snippets[$i]['vars']);
				$variables = '';
				if (!empty($var_arr[0])) {
					for ($j = 0; $j < count($var_arr); $j++) {
						$variables .= ' ' . $var_arr[$j] . '="{' . $var_arr[$j] . '}"';
					}
				}
				$shortcode = $snippets[$i]['title'] . $variables;
				echo "var postsnippet_{$i} = '[" . $shortcode . "]';\n";
			} else {
				$snippet = $snippets[$i]['snippet'];
				# Fixes for potential collisions:
				/* Replace <> with char codes, otherwise </script> in a snippet will break it */ 
				$snippet = str_replace( '<', '\x3C', str_replace( '>', '\x3E', $snippet ) );
				/* Escape " with \" */
				$snippet = str_replace( '"', '\"', $snippet );
				/* Remove CR and replace LF with \n to keep formatting */
				$snippet = str_replace( chr(13), '', str_replace( chr(10), '\n', $snippet ) );
				# Print out the variable containing the snippet
				echo "var postsnippet_{$i} = \"" . $snippet . "\";\n";
			}
		}
		?>
		
		jQuery(document).ready(function($){
			<?php
			# Create js variables for all form fields
			for ($i = 0; $i < count($snippets); $i++) {
				$var_arr = explode(",",$snippets[$i]['vars']);
				if (!empty($var_arr[0])) {
					for ($j = 0; $j < count($var_arr); $j++) {
						$varname = "var_" . $i . "_" . $j;
						echo "var {$varname} = $( \"#{$varname}\" );\n";
					}
				}
			}
			?>
			
			var $tabs = $("#post-snippets-tabs").tabs();
			
			$(function() {
				$( "#post-snippets-dialog" ).dialog({
					autoOpen: false,
					modal: true,
					dialogClass: 'wp-dialog',
					buttons: {
						Cancel: function() {
							$( this ).dialog( "close" );
						},
						"Insert": function() {
							$( this ).dialog( "close" );
							var selected = $tabs.tabs('option', 'selected');
							<?php
							for ($i = 0; $i < count($snippets); $i++) {
							?>
								if (selected == <?php echo $i; ?>) {
									insert_snippet = postsnippet_<?php echo $i; ?>;
									<?php
									$var_arr = explode(",",$snippets[$i]['vars']);
									if (!empty($var_arr[0])) {
										for ($j = 0; $j < count($var_arr); $j++) {
											$varname = "var_" . $i . "_" . $j; ?>
											insert_snippet = insert_snippet.replace(/\{<?php echo $var_arr[$j]; ?>\}/g, <?php echo $varname; ?>.val());
									<?php
											echo "\n";
										}
									}
									?>
								}
							<?php
							}
							?>

							if (caller == 'html') {
								edInsertContent(muppCanv, insert_snippet);
							} else {
								muppCanv.execCommand('mceInsertContent', false, insert_snippet);
							}

						}
					},
					width: 500,
				});
			});
		});

var muppCanv;
caller = '';

<!-- Deprecated -->
function edOpenPostSnippets(myField) {
		muppCanv = myField;
		caller = 'html';
		jQuery( "#post-snippets-dialog" ).dialog( "open" );
};
<?php
		echo "</script>\n";
		echo "\n<!-- END: Post Snippets jQuery UI and related functions -->\n";
	}


	/**
	 * Insert Snippet jQuery UI dialog HTML for the post editor
	 *
	 * @since		Post Snippets 1.7
	 *
	 * @returns		Nothing
	 */
	function insert_ui_dialog() {
		echo "\n<!-- START: Post Snippets UI Dialog -->\n";
		?>
		<div class="hidden">
			<div id="post-snippets-dialog" title="Post Snippets">

				<div id="post-snippets-tabs">
					<ul>
						<?php
						# Create a tab for each available snippet
						$snippets = get_option($this->plugin_options);
						for ($i = 0; $i < count($snippets); $i++) { ?>
							<li><a href="#ps-tabs-<?php echo $i; ?>"><?php echo $snippets[$i]['title']; ?></a></li>
						<?php }	?>					
					</ul>

					<?php
					# Create a panel with form fields for each available snippet
					for ($i = 0; $i < count($snippets); $i++) { ?>
						<div id="ps-tabs-<?php echo $i; ?>">
							<?php
							// Print a snippet description is available
							if ( isset($snippets[$i]['description']) )
								echo '<p class="howto">' . $snippets[$i]['description'] . "</p>\n";

							// Get all variables defined for the snippet and output them as input fields
							$var_arr = explode(",",$snippets[$i]['vars']);
							if (!empty($var_arr[0])) {
								for ($j = 0; $j < count($var_arr); $j++) { ?>
									<label for="var_<?php echo $i; ?>_<?php echo $j; ?>"><?php echo($var_arr[$j]);?>:</label>
									<input type="text" id="var_<?php echo $i; ?>_<?php echo $j; ?>" name="var_<?php echo $i; ?>_<?php echo $j; ?>" style="width: 190px" />
									<br/>
							<?php
								}
							} else {
								// If no variables and no description available, output a text to inform the user that it's an insert snippet only
								if ( empty($snippets[$i]['description']) )
									echo '<p class="howto">' . __('This snippet is insert only, no variables defined.', 'post-snippets') . "</p>";
							}
							?>
						</div><!-- #ps-tabs-## -->
					<?php
					}
					?>					
				</div><!-- #post-snippets-tabs -->
			</div><!-- #post-snippets-dialog -->
		</div><!-- .hidden -->
		<?php
		echo "\n<!-- END: Post Snippets UI Dialog -->\n";
	}


	/**
	 * Adds a QuickTag button to the HTML editor.
	 *
	 * Compatible with WordPress 3.3 and newer.
	 *
	 * @see			wp-includes/js/quicktags.dev.js -> qt.addButton()
	 * @since		Post Snippets 1.8.6
	 *
	 * @returns		Nothing
	 */
	public function add_quicktag_button() {
		echo "\n<!-- START: Add QuickTag button for Post Snippets -->\n";
		?>
		<script type="text/javascript" charset="utf-8">
			QTags.addButton( 'post_snippets_id', 'Post Snippets', qt_post_snippets );
			function qt_post_snippets() {
				caller = 'html';
				jQuery( "#post-snippets-dialog" ).dialog( "open" );
			}
		</script>
		<?php
		echo "\n<!-- END: Add QuickTag button for Post Snippets -->\n";
	}


	/**
	 * Adds a QuickTag button to the HTML editor
	 *
	 * @see			wp-includes/js/quicktags.dev.js
	 * @since		Post Snippets 1.7
	 * @deprecated	Since 1.8.6
	 *
	 * @returns		Nothing
	 */
	function add_quicktag_button_pre33() {
		echo "\n<!-- START: Post Snippets QuickTag button -->\n";
		?>
		<script type="text/javascript" charset="utf-8">
		// <![CDATA[
			//edButton(id, display, tagStart, tagEnd, access, open)
			edbuttonlength = edButtons.length;
			edButtons[edbuttonlength++] = new edButton('ed_postsnippets', 'Post Snippets', '', '', '', -1);
		   (function(){
				  if (typeof jQuery === 'undefined') {
						 return;
				  }
				  jQuery(document).ready(function(){
						 jQuery("#ed_toolbar").append('<input type="button" value="Post Snippets" id="ed_postsnippets" class="ed_button" onclick="edOpenPostSnippets(edCanvas);" title="Post Snippets" />');
				  });
			}());
		// ]]>
		</script>
		<?php
		echo "\n<!-- END: Post Snippets QuickTag button -->\n";
	}


	/**
	 * Create the functions for shortcodes dynamically and register them
	 *
	 */
	function create_shortcodes() {
		$snippets = get_option($this->plugin_options);
		if (!empty($snippets)) {
			for ($i=0; $i < count($snippets); $i++) {
				// If shortcode is enabled for the snippet, and a snippet has been entered, register it as a shortcode.
				if ( $snippets[$i]['shortcode'] && !empty($snippets[$i]['snippet']) ) {
					
					$vars = explode(",",$snippets[$i]['vars']);
					$vars_str = '';
					for ($j=0; $j < count($vars); $j++) {
						$vars_str = $vars_str . '"'.$vars[$j].'" => "",';
						
					}
					add_shortcode($snippets[$i]['title'], create_function('$atts,$content=null', 
								'$shortcode_symbols = array('.$vars_str.');
								extract(shortcode_atts($shortcode_symbols, $atts));
								
								$attributes = compact( array_keys($shortcode_symbols) );
								
								// Add enclosed content if available to the attributes array
								if ( $content != null )
									$attributes["content"] = $content;
								
								$snippet = "'. addslashes($snippets[$i]["snippet"]) .'";
								$snippet = str_replace("&", "&amp;", $snippet);

								foreach ($attributes as $key => $val) {
									$snippet = str_replace("{".$key."}", $val, $snippet);
								}
	
								return do_shortcode(stripslashes($snippet));') );
				}
			}
		}
	}


	/**
	 * The Admin Page and all it's functions
	 *
	 */
	function wp_admin()	{
		add_action( 'contextual_help', array(&$this,'add_help_text'), 10, 3 );
		add_options_page( 'Post Snippets Options', 'Post Snippets', 'administrator', __FILE__, array(&$this, 'options_page') );
	}

	function admin_message($message) {
		if ( $message ) {
			?>
			<div class="updated"><p><strong><?php echo $message; ?></strong></p></div>
			<?php	
		}
	}

	/**
	 * Display contextual help in the help drop down menu at the options page.
	 *
	 * @since		Post Snippets 1.7.1
	 *
	 * @returns		string			The Contextual Help
	 */
	function add_help_text($contextual_help, $screen_id, $screen) {
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


	function options_page() {
		// Add a new Snippet		
		if (isset($_POST['add-snippet'])) {
			$snippets = get_option($this->plugin_options);
			if (empty($snippets)) { $snippets = array(); }
			array_push($snippets, array (
			    'title' => "Untitled",
			    'vars' => "",
			    'description' => "",
			    'shortcode' => false,
			    'php' => false,
			    'snippet' => ""));
			update_option($this->plugin_options, $snippets);
			$this->admin_message( __( 'A snippet named Untitled has been added.', 'post-snippets' ) );
		}
		
		// Update Snippets
		if (isset($_POST['update-post-snippets'])) {
			$snippets = get_option($this->plugin_options);
			if (!empty($snippets)) {
				for ($i=0; $i < count($snippets); $i++) {
					$new_snippets[$i]['title'] = trim($_POST[$i.'_title']);
					$new_snippets[$i]['vars'] = str_replace(" ", "", trim($_POST[$i.'_vars']) );
					$new_snippets[$i]['shortcode'] = isset($_POST[$i.'_shortcode']) ? true : false;
					$new_snippets[$i]['php'] = isset($_POST[$i.'_php']) ? true : false;
					/*	Check if the plugin runs on PHP below version 5.1.0
						Because of a bug in WP 2.7.x in includes/compat.php the htmlspecialchars_decode
						don't revert back to a PHP 4.x compatible version. So this is a workaround to make
						the plugin work correctly on PHP versions below 5.1.
						This problem is fixed in WP 2.8.
					*/
					if (version_compare(PHP_VERSION, '5.1.0', '<')) {
						$new_snippets[$i]['snippet'] = htmlspecialchars_decode( trim(stripslashes($_POST[$i.'_snippet'])), ENT_NOQUOTES);
						$new_snippets[$i]['description'] = htmlspecialchars_decode( trim(stripslashes($_POST[$i.'_description'])), ENT_NOQUOTES);
					} else {
						$new_snippets[$i]['snippet'] = wp_specialchars_decode( trim(stripslashes($_POST[$i.'_snippet'])), ENT_NOQUOTES);
						$new_snippets[$i]['description'] = wp_specialchars_decode( trim(stripslashes($_POST[$i.'_description'])), ENT_NOQUOTES);
					}
				}
				update_option($this->plugin_options, $new_snippets);
				$this->admin_message( __( 'Snippets have been updated.', 'post-snippets' ) );
			}
		}

		// Delete Snippets
		if (isset($_POST['delete-selected'])) {
			$snippets = get_option($this->plugin_options);
			if (!empty($snippets)) {
				$delete = $_POST['checked'];
				$newsnippets = array();
				for ($i=0; $i < count($snippets); $i++) {
					if (in_array($i,$delete) == false) {
						array_push($newsnippets,$snippets[$i]);	
					}
				}
				update_option($this->plugin_options, $newsnippets);
				$this->admin_message( __( 'Selected snippets have been deleted.', 'post-snippets' ) );
			}
		}
		
		// Handle import of snippets (Run before the option page is outputted, in case any snippets have been imported, so they are displayed).
		$import = $this->import_snippets();


		// Render the settings screen
		$settings = new Post_Snippets_Settings();
		$settings->set_options( get_option($this->plugin_options) );
		$settings->render();

?>
	<h3><?php _e( 'Import/Export', 'post-snippets' ); ?></h3>
	<strong><?php _e( 'Export', 'post-snippets' ); ?></strong><br/>
	<form method="post">
		<p><?php _e( 'Export your snippets for backup or to import them on another site.', 'post-snippets' ); ?></p>
		<input type="submit" class="button" name="postsnippets_export" value="<?php _e( 'Export Snippets', 'post-snippets');?>"/>
	</form>
<?php
		$this->export_snippets();
		echo $import;
	}


	/**
	 * Check if an export file shall be created, or if a download url should be pushed to the footer.
	 * Also checks for old export files laying around and deletes them (for security).
	 *
	 * @since		Post Snippets 1.8
	 *
	 * @returns		string			URL to the exported snippets
	 */
	function export_snippets() {
		if ( isset($_POST['postsnippets_export']) ) {
			$url = $this->create_export_file();
			if ($url) {
				define('PSURL', $url);
				function psnippets_footer() {
					$export .= '<script type="text/javascript">
									document.location = \''.PSURL.'\';
								</script>';
					echo $export;
				}
				add_action('admin_footer', 'psnippets_footer', 10000);

			} else {
				$export .= 'Error: '.$url;
			}
		} else {
			// Check if there is any old export files to delete
			$dir = wp_upload_dir();
			$upload_dir = $dir['basedir'] . '/';
			chdir($upload_dir);
			if (file_exists ( './post-snippets-export.zip' ) )
				unlink('./post-snippets-export.zip');
		}
	}

	/**
	 * Create a zipped filed containing all Post Snippets, for export.
	 *
	 * @since		Post Snippets 1.8
	 *
	 * @returns		string			URL to the exported snippets
	 */
	function create_export_file() {
		$snippets = serialize(get_option($this->plugin_options));
		$dir = wp_upload_dir();
		$upload_dir = $dir['basedir'] . '/';
		$upload_url = $dir['baseurl'] . '/';
		
		// Open a file stream and write the serialized options to it.
		if ( !$handle = fopen( $upload_dir.'post-snippets-export.cfg', 'w' ) )
			die();
		if ( !fwrite($handle, $snippets) ) 
			die();
	    fclose($handle);

		// Create a zip archive
		require_once (ABSPATH . 'wp-admin/includes/class-pclzip.php');
		chdir($upload_dir);
		$zip = new PclZip('./post-snippets-export.zip');
		$zipped = $zip->create('./post-snippets-export.cfg');

		// Delete the snippet file
		unlink('./post-snippets-export.cfg');

		if (!$zipped)
			return false;
		
		return $upload_url.'post-snippets-export.zip'; 
	}
	
	/**
	 * Handles uploading of post snippets archive and import the snippets.
	 *
	 * @uses 		wp_handle_upload() in wp-admin/includes/file.php
	 * @since		Post Snippets 1.8
	 *
 	 * @returns		string			HTML to handle the import
	 */
	function import_snippets() {
		$import = '<br/><br/><strong>'.__( 'Import', 'post-snippets' ).'</strong><br/>';
		if ( !isset($_FILES['postsnippets_import_file']) || empty($_FILES['postsnippets_import_file']) ) {
			$import .= '<p>'.__( 'Import snippets from a post-snippets-export.zip file. Importing overwrites any existing snippets.', 'post-snippets' ).'</p>';
			$import .= '<form method="post" enctype="multipart/form-data">';
			$import .= '<input type="file" name="postsnippets_import_file"/>';
			$import .= '<input type="hidden" name="action" value="wp_handle_upload"/>';
			$import .= '<input type="submit" class="button" value="'.__( 'Import Snippets', 'post-snippets' ).'"/>';
			$import .= '</form>';
		} else {
			$file = wp_handle_upload( $_FILES['postsnippets_import_file'] );
			
			if ( isset( $file['file'] ) && !is_wp_error($file) ) {
				require_once (ABSPATH . 'wp-admin/includes/class-pclzip.php');
				$zip = new PclZip( $file['file'] );
				$dir = wp_upload_dir();
				$upload_dir = $dir['basedir'] . '/';
				chdir($upload_dir);
				$unzipped = $zip->extract();

				if ( $unzipped[0]['stored_filename'] == 'post-snippets-export.cfg' && $unzipped[0]['status'] == 'ok') {
					// Delete the uploaded archive
					unlink($file['file']);

					$options = file_get_contents( $upload_dir.'post-snippets-export.cfg' );		// Returns false on failure, else the contents
					if ($options)
						update_option($this->plugin_options, unserialize($options));

					// Delete the snippet file
					unlink('./post-snippets-export.cfg');

					$this->admin_message( __( 'Snippets have been updated.', 'post-snippets' ) );

					$import .= '<p><strong>'.__( 'Snippets successfully imported.').'</strong></p>';
				} else {
					$import .= '<p><strong>'.__( 'Snippets could not be imported:').' '.__('Unzipping failed.').'</strong></p>';
				}
			} else {
				if ( $file['error'] || is_wp_error( $file ) )
					$import .= '<p><strong>'.__( 'Snippets could not be imported:').' '.$file['error'].'</strong></p>';
				else
					$import .= '<p><strong>'.__( 'Snippets could not be imported:').' '.__('Upload failed.').'</strong></p>';
			}
		}
		return $import;
	}
	
}


// -----------------------------------------------------------------------------
// Start the plugin
// -----------------------------------------------------------------------------


// Check the host environment
$test_post_snippets_host = new Post_Snippets_Host_Environment();

// If environment is up to date, start the plugin
if($test_post_snippets_host->passed) {
	// Load external classes
	if (is_admin()) {
		require plugin_dir_path(__FILE__).'classes/settings.php';
	}

	add_action(
		'plugins_loaded', 
		create_function( 
			'',
			'global $post_snippets; $post_snippets = new Post_Snippets();'
		)
	);
}


/**
 * Post Snippets Host Environment.
 *
 * Checks that the host environment fulfils the requirements of Post Snippets.
 * This class is designed to work with PHP versions below 5, to make sure it's
 * always executed.
 *
 * - PHP Version 5.2.4 is on par with the requirements for WordPress 3.3.
 *
 * @since	Post Snippets 1.8.8
 */
class Post_Snippets_Host_Environment
{
	// Minimum versions required
	var $MIN_PHP_VERSION	= '5.2.4';
	var $MIN_WP_VERSION		= '3.0';
	var $passed				= true;

	/**
	 * Constructor.
	 *
	 * Checks PHP and WordPress versions. If any check failes, a system notice
	 * is added and $passed is set to fail, which can be checked before trying
	 * to create the main class.
	 */
	function Post_Snippets_Host_Environment()
	{
		// Check if PHP is too old
		if (version_compare(PHP_VERSION, $this->MIN_PHP_VERSION, '<')) {
			// Display notice
			add_action( 'admin_notices', array(&$this, 'php_version_error') );
		}

		// Check if WordPress is too old
		global $wp_version;
		if ( version_compare($wp_version, $this->MIN_WP_VERSION, '<') ) {
			add_action( 'admin_notices', array(&$this, 'wp_version_error') );
			$this->passed = false;
		}
	}

	/**
	 * Displays a warning when installed on an old PHP version.
	 */
	function php_version_error() {
		echo '<div class="error"><p><strong>';
		printf( __(
			'Notice:<br/>
			When Post Snippets v1.9 will be released, the minimum 
			required PHP Version will be %1$s to be on par with WordPress 3.3.
			<br/>
			Please update your
			PHP installation before updating Post Snippets to v1.9+, or 
			contact the plugin author to plead your case.<br/>
			Your installed PHP version: %2$s',
			'post-snippets'),
			$this->MIN_PHP_VERSION, PHP_VERSION);
		echo '</strong></p></div>';
	}

	/**
	 * Displays a warning when installed in an old Wordpress version.
	 */
	function wp_version_error() {
		echo '<div class="error"><p><strong>';
		printf( __( 
			'Error: Post Snippets requires WordPress Version %s or higher.',
			'post-snippets'),
			$this->MIN_WP_VERSION );
		echo '</strong></p></div>';
	}
}


// -----------------------------------------------------------------------------
// Helper functions
// -----------------------------------------------------------------------------

/**
 * Allow snippets to be retrieved directly from PHP
 *
 * @since	Post Snippets 1.6
 *
 * @param	string		$snippet_name
 *			The name of the snippet to retrieve
 * @param	string		$snippet_vars
 *			The variables to pass to the snippet, formatted as a query string.
 * @return	string
 *			The Snippet
 */
function get_post_snippet( $snippet_name, $snippet_vars = '' ) {
	global $post_snippets;
	$snippets = get_option($post_snippets -> plugin_options);
	for ($i = 0; $i < count($snippets); $i++) {
		if ($snippets[$i]['title'] == $snippet_name) {
			parse_str( htmlspecialchars_decode($snippet_vars), $snippet_output );
			$snippet = $snippets[$i]['snippet'];
			$var_arr = explode(",",$snippets[$i]['vars']);

			if ( !empty($var_arr[0]) ) {
				for ($j = 0; $j < count($var_arr); $j++) {
					$snippet = str_replace("{".$var_arr[$j]."}", $snippet_output[$var_arr[$j]], $snippet);
				}
			}
		}
	}
	return $snippet;
}

