<?php
/*
Plugin Name: Post Snippets
Plugin URI: http://wpstorm.net/wordpress-plugins/post-snippets/
Description: Stores snippets of HTML code or reoccurring text that you often use in your posts. You can use predefined variables to replace parts of the snippet on insert. All snippets are available in the post editor with a TinyMCE button or Quicktags.
Version: 1.7
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
		add_filter( 'plugin_action_links', array(&$this, 'plugin_action_links'), 10, 2 );
		add_action( 'admin_menu', array(&$this,'wp_admin') );
		add_action('admin_head', array(&$this,'quicktags'));
//		add_action('admin_footer', array(&$this,'quicktags'));


		$this->create_shortcodes();

add_action( 'edit_form_advanced', array(&$this,'manchumahara_quicktags'));
//add_action( 'edit_page_form',');
// admin_enqueuy_script??
wp_enqueue_script( 'jquery-ui-dialog' );
		add_action('admin_head', array(&$this,'dialog'));
		add_action('admin_footer', array(&$this,'fot'));



		}


		
		
		
		
		
		
		
		

		
		
		
		
		
		
		function dialog() {
?>
	<script>
jQuery(document).ready(function($){
	$(function() {
		$( "#dialog" ).dialog({
			autoOpen: false
		});
	});
});

	</script>
<?
}

function fot() {
echo "\n<!-- Post Snippets -->\n";
?>
<div id="dialog" title="Basic dialog">
	<p>This is the default dialog which is useful for displaying information. The dialog window can be moved, resized and closed with the 'x' icon.</p>
</div>
<?
}

/*
See wp-includes/js/quicktags.dev.js
*/	
function manchumahara_quicktags()
{
/*
ed.addCommand('mcepost_snippets', function() {
				ed.windowManager.open({
					file : url + '/window.php',
					width : 360 + ed.getLang('post_snippets.delta_width', 0),
					height : 210 + ed.getLang('post_snippets.delta_height', 0),
					inline : 1
				}, {
					plugin_url : url // Plugin absolute URL
				});
			});
*/

 
    ?>
    <script type="text/javascript" charset="utf-8">
    // <![CDATA[
        //edButton(id, display, tagStart, tagEnd, access, open)
        edbuttonlength = edButtons.length;
        edbuttonlength_t = edbuttonlength;
        //alert(edButtons);
        edButtons[edbuttonlength++] = new edButton('ed_itemname','Item Name','<span class="itemleft">','</span>');
        edButtons[edbuttonlength++] = new edButton('ed_itemprice','Item Price','<span class="itemprice">','</span>');
        edButtons[edbuttonlength++] = new edButton('ed_postsnippets','Post Snippets','<span class="itemcaption">','</span>');
            //alert(edButtons[edButtons.length]);
               (function(){
 
              if (typeof jQuery === 'undefined') {
                     return;
              }
              jQuery(document).ready(function(){
                     jQuery("#ed_toolbar").append('<br/><input type="button" value="Item Name" id="ed_itemname" class="ed_button" onclick="edInsertTag(edCanvas, edbuttonlength_t);" title="Item Name" />');
                     jQuery("#ed_toolbar").append('<input type="button" value="Item Price" id="ed_itemprice" class="ed_button" onclick="edInsertTag(edCanvas, edbuttonlength_t+1);" title="Item Price" />');
                     jQuery("#ed_toolbar").append('<input type="button" value="Post Snippets" id="ed_postsnippets" class="ed_button" onclick="edInsertTag(edCanvas, edbuttonlength_t+2);" title="Post Snippets" />');
              });
       }());
    // ]]>
    </script>
    <?php
 
}


	/**
	* Quick link to the Post Snippets Settings page from the Plugins page.
	*
	* @returns	Array with all the plugin's action links
	*/
	function plugin_action_links( $links, $file ) {
		$links[] = '<a href="options-general.php?page=post-snippets/post-snippets.php">'.__('Settings', 'post-snippets').'</a>';
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
	* Create the functions for shortcodes dynamically and register them
	*
	*/
	function create_shortcodes() {
		$snippets = get_option($this->plugin_options);
		if (!empty($snippets)) {
			for ($i=0; $i < count($snippets); $i++) {
				if ($snippets[$i]['shortcode'] == true) {
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
		if (function_exists('add_options_page')) {
			add_options_page( 'Post Snippets Options', 'Post Snippets', 'administrator', __FILE__, array(&$this, 'options_page') );
		}
	}

	function admin_message($message) {
		if ( $message ) {
			?>
			<div class="updated"><p><strong><?php echo $message; ?></strong></p></div>
			<?php	
		}
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

    <div id="poststuff" class="ui-sortable">
        <div class="postbox">
            <h3><?php _e( 'Help', 'post-snippets' ); ?></h3>
            <div class="inside">
				<p><?php _e( '<strong>Title</strong><br/>Give the snippet a title that helps you identify it in the post editor.', 'post-snippets' ); ?></p>
							
				<p><?php _e( '<strong>Variables</strong><br/>A comma separated list of custom variables you can reference in your snippet.<br/><br/>Example:<br/>url,name', 'post-snippets' ); ?></p>

				<p><?php _e( '<strong>Snippet</strong><br/>This is the block of text or HTML to insert in the post when you select the snippet from the insert button in the TinyMCE panel in the post editor. If you have entered predefined variables you can reference them from the snippet by enclosing them in {} brackets.<br/><br/>Example:<br/>To reference the variables in the example above, you would enter {url} and {name}.<br/><br/>So if you enter this snippet:<br/><i>This is the website of &lt;a href="{url}"&gt;{name}&lt;/a&gt;</i><br/>You will get the option to replace url and name on insert if they are defined as variables.', 'post-snippets' ); ?></p>

				<p><?php _e( '<strong>SC</strong><br/>Treats the snippet as a shortcode. The name for the shortcode is the same as the title of the snippet (spaces not allowed) and will be used on insert.', 'post-snippets' ); ?></p>

				<p><?php _e( '<strong>QT</strong><br/>Enables the snippet to be available as a quicktag in the HTML editor.', 'post-snippets' ); ?></p>
                
                <p><?php _e( '<strong>About Post Snippets</strong><br/>Visit my <a href="http://coding.cglounge.com/wordpress-plugins/post-snippets/">Post Snippets</a> page for additional information.', 'post-snippets' ); ?></p>
            </div>
        </div>
    </div>

    <script type="text/javascript">
    <!--
	<?php global $wp_version; ?>
    <?php if ( version_compare( $wp_version, '2.6.999', '<' ) ) { ?>
    jQuery('.postbox h3').prepend('<a class="togbox">+</a> ');
    <?php } ?>
    jQuery('.postbox h3').click( function() { jQuery(jQuery(this).parent().get(0)).toggleClass('closed'); } );
    jQuery('.postbox.close-me').each(function(){
        jQuery(this).addClass("closed");
    });
    //-->
    </script>        
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