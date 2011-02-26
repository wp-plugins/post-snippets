<?php
/*
Plugin Name: Post Snippets
Plugin URI: http://wpstorm.net/wordpress-plugins/post-snippets/
Description: Stores snippets of HTML code or reoccurring text that you often use in your posts. You can use predefined variables to replace parts of the snippet on insert. All snippets are available in the post editor with a TinyMCE button or Quicktags.
Version: 1.7.1
Author: Johan Steen
Author URI: http://wpstorm.net/
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

class post_snippets {
	var $plugin_options = "post_snippets_options";

	/**
	* Constructor
	*
	*/
	function post_snippets()
	{
		// define URL
		define('post_snippets_ABSPATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' );
		define('post_snippets_URLPATH', WP_PLUGIN_URL.'/'.plugin_basename( dirname(__FILE__) ).'/' );

		// Define the domain for translations
		load_plugin_textdomain(	'post-snippets', false, dirname(plugin_basename(__FILE__)) . '/languages/');

		// Check installed Wordpress version.
		global $wp_version;
		if ( version_compare($wp_version, '2.7', '<') ) {
			add_action( 'admin_notices', array(&$this, 'version_warning') ); 
		} else {
			include_once (dirname (__FILE__)."/tinymce/tinymce.php");
			$this->init_hooks();
		}
	}

	/**
	* Initializes the hooks for the plugin
	*
	* @returns	Nothing
	*/
	function init_hooks() {
		# Settings link on plugins list
		add_filter( 'plugin_action_links', array(&$this, 'plugin_action_links'), 10, 2 );
		# Options Page
		add_action( 'admin_menu', array(&$this,'wp_admin') );

		// This old methos is no longer used, DELETE when the new method is tried by users.
		// Add Quicktags to header (used to be in footer!)
		// add_action('admin_head', array(&$this,'quicktags'));
		// add_action('admin_footer', array(&$this,'quicktags'));

		$this->create_shortcodes();

		# Adds the JS and HTML code in the header and footer for the jQuery insert UI dialog in the editor
		add_action( 'admin_init', array(&$this,'enqueue_scripts') );
		add_action( 'admin_head', array(&$this,'jquery_ui_dialog_style') );
		add_action( 'admin_head', array(&$this,'jquery_ui_dialog') );
		add_action( 'admin_footer', array(&$this,'insert_ui_dialog') );
		
		# Add Editor QuickTag button
		add_action( 'edit_form_advanced', array(&$this,'add_quicktag_button'));
		add_action( 'edit_page_form', array(&$this,'add_quicktag_button'));
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
	* Displays a warning when installed in an old Wordpress Version
	*
	* @returns	Nothing
	*/
	function version_warning() {
		echo '<div class="updated fade"><p><strong>'.__('Post Snippets requires WordPress version 2.7 or higher.', 'post-snippets').'</strong></p></div>';
	}
		
	/**
	 * Enqueues the necessary scripts for the insert dialogue
	 *
	 * @since		Post Snippets 1.7
	 *
	 * @returns		Nothing
	 */
	function enqueue_scripts() {
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-tabs' );
	}


	/**
	 * CSS Styling for the jQuert Dialog
	 *
	 * @since		Post Snippets 1.7
	 *
	 * @returns		Nothing
	 */
	function jquery_ui_dialog_style() {
	?>
	<style type="text/css">
	#post-snippets-tabs {
		padding: 15px 15px 3px;
		background-color: #f1f1f1;
		border-bottom: 1px solid #dfdfdf;
	}
	#post-snippets-tabs li {
		display: inline;
	}
	#post-snippets-tabs a.current {
		background-color: #fff;
		border-color: #dfdfdf;
		border-bottom-color: #fff;
		color: #d54e21;
	}
	#post-snippets-tabs a {
		color: #2583AD;
		padding: 6px;
		border-width: 1px 1px 0;
		border-style: solid solid none;
		border-color: #f1f1f1;
		text-decoration: none;
	}
	#post-snippets-tabs a:hover {
		color: #d54e21;
	}
	</style>
	<?php
	}
	
	/**
	 * jQuery control for the dialog and Javascript needed to insert snippets into the editor
	 *
	 * @since		Post Snippets 1.7
	 *
	 * @returns		Nothing
	 */
	function jquery_ui_dialog() {
		echo "\n<!-- START: Post Snippets jQuery UI and related functions -->\n";
		echo "<script>\n";
		
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
				$theSnippet = $snippets[$i]['snippet'];
				$theSnippet = str_replace('"','\"',str_replace(chr(13), '', str_replace(chr(10), '%%LF%%', $theSnippet)));
				echo "var postsnippet_{$i} = \"" . $theSnippet . "\";\n";
				//echo "var postsnippet_{$i} = '" . esc_js( $snippets[$i]['snippet'] ) . "';\n";
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
							edInsertContent(muppCanv, insert_snippet);
						}
					},
					width: 500,
				});
			});
		});


var muppCanv;


function edOpenPostSnippets(myField) {
		muppCanv = myField;
		jQuery( "#post-snippets-dialog" ).dialog( "open" );
};


<?php
		echo "</script>\n";
		echo "\n<!-- END: Post Snippets jQuery UI and related functions -->\n";
	}



	/**
	 * jQuery Insert Dialog for the editor
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
						$snippets = get_option($this->plugin_options);
						for ($i = 0; $i < count($snippets); $i++) { ?>
							<li><a href="#ps-tabs-<?php echo $i; ?>"><?php echo $snippets[$i]['title']; ?></a></li>
						<?php }	?>					
					</ul>

					<?php
					for ($i = 0; $i < count($snippets); $i++) { ?>
						<div id="ps-tabs-<?php echo $i; ?>">
							<h4><?php echo $snippets[$i]['title']; ?></h4>
							<!--<p>Snippet Description</p>-->

							
		<?php
        $var_arr = explode(",",$snippets[$i]['vars']);
		if (!empty($var_arr[0])) {
			for ($j = 0; $j < count($var_arr); $j++) { ?>
			 <label for="var_<?php echo $i; ?>_<?php echo $j; ?>"><?php echo($var_arr[$j]);?>:</label>
			<input type="text" id="var_<?php echo $i; ?>_<?php echo $j; ?>" name="var_<?php echo $i; ?>_<?php echo $j; ?>" style="width: 190px" />
			<br/>

        <?php } } ?>

		
						</div>
					<?php }	?>					
				</div>

			</div>
		</div>
		<?
		echo "\n<!-- END: Post Snippets UI Dialog -->\n";
	}


	/**
	 * Adds a QuickTag button to the HTML editor
	 *
	 * @see			wp-includes/js/quicktags.dev.js
	 * @since		Post Snippets 1.7
	 *
	 * @returns		Nothing
	 */
	function add_quicktag_button() {
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
					add_shortcode($snippets[$i]['title'], create_function('$atts', 
								'$shortcode_symbols = array('.$vars_str.');
								extract(shortcode_atts($shortcode_symbols, $atts));
								
								$newArr = compact( array_keys($shortcode_symbols) );
								
								$snippet = "'. addslashes($snippets[$i]["snippet"]) .'";
								$snippet = str_replace("&", "&amp;", $snippet);

								foreach ($newArr as $key => $val) {
									$snippet = str_replace("{".$key."}", $val, $snippet);
								}
	
								return stripslashes($snippet);') );
				}
			}
		}
	}

	/**
	* Handling of QuickTags in the HTML editor
	*
	* DELETE THIS PART WHEN THE NEW QUICKTAGS FUNCTION HAVE BEEN TRIED BY USERS.
	*/
	function quicktags() {
		$snippets = get_option($this->plugin_options);
		if (!empty($snippets)) { ?>
			<script type="text/javascript">
				if(typeof(edButtons)!='undefined') {
					var postSnippetsNr, postSnippetsButton; <?php
					for ($i = 0; $i < count($snippets); $i++) {
						if ($snippets[$i]['quicktag']) {
							// Make it js safe
							$theSnippet = $snippets[$i]['snippet'];
							$theSnippet = str_replace('"','\"',str_replace(Chr(13), '', str_replace(Chr(10), '', $theSnippet)));
							//$theSnippet = str_replace('<', '\x3C', str_replace('>', '\x3E', $theSnippet));
							$var_arr = explode(",",$snippets[$i]['vars']);
							$theVariables = "";
							if (!empty($var_arr[0])) {
								for ($j = 0; $j < count($var_arr); $j++) {
									$theVariables = $theVariables . "'" . $var_arr[$j] . "'";
									if ( $j < (count($var_arr) -1) )
										$theVariables = $theVariables . ", ";
								}
							}

							if ($snippets[$i]['shortcode']) { 
								echo "var variables" . $i ." = new Array(".$theVariables.");";
								echo "var insertString" . $i ." = createShortcode('".$snippets[$i]['title']."', variables".$i.");";
							}else{
								//echo "var insertString" . $i ." = '" . addslashes(stripslashes($theSnippet)). "';";
								echo "var insertString" . $i ." = '" . str_replace('<', '\x3C', str_replace('>', '\x3E',  addslashes(stripslashes($theSnippet)) )). "';";
							}

							echo '
								postSnippetsNr = edButtons.length;
								edButtons[postSnippetsNr] = new edButton(\'ed_psnip'. $i . '\',    \'' . $snippets[$i]['title'] . '\',    insertString'. $i .',  \'\',       \'\', -1);
								
							';
/*							postSnippetsNr = edButtons.length;
							edButtons[postSnippetsNr] = new edButton(\'ed_ps'. $i . '\',    \'' . $snippets[$i]['title'] . '\',    insertString'. $i .',  \'\',       \'\', -1);
							var postSnippetsButton = postSnippetsToolbar.lastChild;
							
							while (postSnippetsButton.nodeType != 1) {
								postSnippetsButton = postSnippetsButton.previousSibling;
							}
							
							postSnippetsButton = postSnippetsButton.cloneNode(true);
							postSnippetsToolbar.appendChild(postSnippetsButton);
							postSnippetsButton.value = \'' . $snippets[$i]['title'] . '\';
							postSnippetsButton.title = postSnippetsNr;
							var variables' . $i .' = new Array('.$theVariables.');
							postSnippetsButton.onclick = function () {edInsertSnippet(edCanvas, insertString' . $i .', variables' . $i .', parseInt(this.title));}
							postSnippetsButton.id = "ed_ps' . $i .'"; */
						} // end if
					} //next ?>
				};
				window.onload = ps_quicktags;
				function ps_quicktags() { <?php
					for ($i = 0; $i < count($snippets); $i++) {
						if ($snippets[$i]['quicktag']) { 
							// Make it js safe
							$theSnippet = $snippets[$i]['snippet'];
							$theSnippet = str_replace('"','\"',str_replace(chr(13), '', str_replace(chr(10), '%%LF%%', $theSnippet)));
							//$theSnippet = str_replace('<', '\x3C', str_replace('>', '\x3E', $theSnippet));
							$var_arr = explode(",",$snippets[$i]['vars']);
							$theVariables = "";
							if (!empty($var_arr[0])) {
								for ($j = 0; $j < count($var_arr); $j++) {
									$theVariables = $theVariables . "'" . $var_arr[$j] . "'";
									if ( $j < (count($var_arr) -1) )
										$theVariables = $theVariables . ", ";
								}
							}
							if ($snippets[$i]['shortcode']) { 
								echo "var variables" . $i ." = new Array(".$theVariables.");";
								echo "var insertString" . $i ." = createShortcode('".$snippets[$i]['title']."', variables".$i.");";
							}else{
								//echo "var insertString" . $i ." = '" . addslashes(stripslashes($theSnippet)). "';";
								$theSnippet = str_replace('<', '\x3C', str_replace('>', '\x3E',  addslashes(stripslashes($theSnippet)) ));
								$theSnippet = str_replace('%%LF%%', '\n', $theSnippet);
								echo "var insertString" . $i ." = '" . $theSnippet . "';";
							}
					?>
					var postSnippetsButton = document.getElementById('ed_psnip<?php echo $i; ?>');
					var variables<?php echo $i; ?> = new Array(<?php echo $theVariables; ?>);
					postSnippetsButton.onclick = function () {edInsertSnippet(edCanvas, insertString<?php echo $i; ?>, variables<?php echo $i; ?>, parseInt(this.title));}
					<?php
						} // end if
					} // next ?>
				}


<?php
echo <<<JAVASCRIPT
							function createShortcode(shortcodeTag, shortcodeAtts) {
								theSnippet = '[' + shortcodeTag;
								for (x in shortcodeAtts)
								{
									theSnippet += ' ' + shortcodeAtts[x] + '="{' + shortcodeAtts[x] + '}"';
								}		
								theSnippet += ']';
								return theSnippet;
							}
							
							function edInsertSnippet(myField,theSnippet,theVariables) {
								var myValue;
								var insertString;
								insertString = theSnippet;
								for (x in theVariables)
								{
									myValue = prompt(theVariables[x]);
									var searchfor = '{' + theVariables[x] + '}';
									var re = new RegExp(searchfor, 'g');
									insertString = insertString.replace(re, myValue);
									
								}
								//theSnippet = str_replace('\x3C', '<', str_replace('\x3E', '>', insertString));
								theSnippet = insertString;
								if (theSnippet) {
									edInsertContent( myField, theSnippet );
								}
							}
							//-->
						</script>
JAVASCRIPT;
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

			'<p><strong>' . __('SC', 'post-snippets') . '</strong></p>' .
			'<p>' . __('Treats the snippet as a shortcode. The name for the shortcode is the same as the title of the snippet (spaces not allowed) and will be used on insert.', 'post-snippets') . '</p>' .

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
			    'shortcode' => false,
			    'quicktag' => false,
			    'snippet' => ""));
			update_option($this->plugin_options, $snippets);
			$this->admin_message( __( 'A snippet named Untitled has been added.', 'post-snippets' ) );
		}
		
		// Update Snippets
		if (isset($_POST['update-post-snippets'])) {
			$snippets = get_option($this->plugin_options);
			if (!empty($snippets)) {
				for ($i=0; $i < count($snippets); $i++) {
					$snippets[$i]['title'] = trim($_POST[$i.'_title']);
					$snippets[$i]['vars'] = str_replace(" ", "", trim($_POST[$i.'_vars']) );
					$snippets[$i]['shortcode'] = isset($_POST[$i.'_shortcode']) ? true : false;
					$snippets[$i]['quicktag'] = isset($_POST[$i.'_quicktag']) ? true : false;
					/*	Check if the plugin runs on PHP below version 5.1.0
						Because of a bug in WP 2.7.x in includes/compat.php the htmlspecialchars_decode
						don't revert back to a PHP 4.x compatible version. So this is a workaround to make
						the plugin work correctly on PHP versions below 5.1.
						This problem is fixed in WP 2.8.
					*/
					if (version_compare(PHP_VERSION, '5.1.0', '<')) {
						$snippets[$i]['snippet'] = htmlspecialchars_decode( trim(stripslashes($_POST[$i.'_snippet'])), ENT_NOQUOTES);
					} else {
						$snippets[$i]['snippet'] = wp_specialchars_decode( trim(stripslashes($_POST[$i.'_snippet'])), ENT_NOQUOTES);
					}
				}
				update_option($this->plugin_options, $snippets);
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
?>
<div class=wrap>
    <h2>Post Snippets</h2>

	<form method="post" action="">
	<?php wp_nonce_field('update-options'); ?>

    <div class="tablenav">
        <div class="alignleft actions">
            <input type="submit" name="add-snippet" value="<?php _e( 'Add New Snippet', 'post-snippets' ) ?>" class="button-secondary" />
            <input type="submit" name="delete-selected" value="<?php _e( 'Delete Selected', 'post-snippets' ) ?>" class="button-secondary" />
			<span class="description">(Use the help dropdown button above for additional information.)</span>
        </div>
    </div>
    <div class="clear"></div>

    <table class="widefat fixed" cellspacing="0">
        <thead>
        <tr>
            <th scope="col" class="check-column"><input type="checkbox" /></th>
            <th scope="col" style="width: 180px;"><?php _e( 'Title', 'post-snippets' ) ?></th>
            <th scope="col" style="width: 180px;"><?php _e( 'Variables', 'post-snippets' ) ?></th>
            <th scope="col"><?php _e( 'Snippet', 'post-snippets' ) ?></th>
            <th scope="col" style="width: 20px;"><?php _e( 'SC', 'post-snippets' ) ?></th>
            <th scope="col" style="width: 20px;"><?php _e( 'QT', 'post-snippets' ) ?></th>
        </tr>
        </thead>
    
        <tfoot>
        <tr>
            <th scope="col" class="check-column"><input type="checkbox" /></th>
            <th scope="col"><?php _e( 'Title', 'post-snippets' ) ?></th>
            <th scope="col"><?php _e( 'Variables', 'post-snippets' ) ?></th>
            <th scope="col"><?php _e( 'Snippet', 'post-snippets' ) ?></th>
            <th scope="col"><?php _e( 'SC', 'post-snippets' ) ?></th>
            <th scope="col"><?php _e( 'QT', 'post-snippets' ) ?></th>
        </tr>
        </tfoot>
    
        <tbody>
		<?php 
		$snippets = get_option($this->plugin_options);
		if (!empty($snippets)) {
			for ($i=0; $i < count($snippets); $i++) { ?>
			<tr class='recent'>
			<th scope='row' class='check-column'><input type='checkbox' name='checked[]' value='<?php echo $i; ?>' /></th>
			<td class='row-title'><input type='text' name='<?php echo $i; ?>_title' value='<?php echo $snippets[$i]['title']; ?>' /></td>
			<td class='name'><input type='text' name='<?php echo $i; ?>_vars' value='<?php echo $snippets[$i]['vars']; ?>' /></td>
			<td class='desc'><textarea name="<?php echo $i; ?>_snippet" class="large-text" rows="3"><?php echo htmlspecialchars($snippets[$i]['snippet'], ENT_NOQUOTES); ?></textarea></td>
			<td class='name'><input type='checkbox' name='<?php echo $i; ?>_shortcode' value='true'<?php if ($snippets[$i]['shortcode'] == true) { echo " checked"; }?> /></td>
			<td class='name'><input type='checkbox' name='<?php echo $i; ?>_quicktag' value='true'<?php if ($snippets[$i]['quicktag'] == true) { echo " checked"; }?> /></td>
			</tr>
		<?php
			}
		}
		?>
	    </tbody>
	</table>
	<div class="submit">
		<input type="submit" name="update-post-snippets" value="<?php _e( 'Update Snippets', 'post-snippets' ) ?>"  class="button-primary" /></div>
	</form>
</div>
<?php
	}
}

add_action( 'plugins_loaded', create_function( '', 'global $post_snippets; $post_snippets = new post_snippets();' ) );


/**
 * Allow snippets to be retrieved directly from PHP
 *
 * @since		Post Snippets 1.6
 *
 * @param		string		$snippet_name		The name of the snippet to retrieve
 * @param		string		$snippet_vars		The variables to pass to the snippet, formatted as a query string.
 * @returns		string							The Snippet
 */
function get_post_snippet( $snippet_name, $snippet_vars ) {
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

?>