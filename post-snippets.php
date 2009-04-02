<?php
/*
Plugin Name: Post Snippets
Plugin URI: http://coding.cglounge.com/wordpress-plugins/post-snippets/
Description: Stores snippets of HTML code or reoccurring text that you often use in your posts. You can use predefined variables to replace parts of the snippet on insert. All snippets are available in the post editor with a TinyMCE button or Quicktags.
Version: 1.3
Author: Johan Steen
Author URI: http://coding.cglounge.com/
Text Domain: post-snippets 

Copyright 2009  Johan Steen  (email : artstorm [at] gmail [dot] com)

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

class postSnippets {
	var $plugin_options = "post_snippets_options";

	/**
	* Constructor
	*
	*/
	function postSnippets()
	{
		// define URL
		define('postSnippets_ABSPATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' );
		define('postSnippets_URLPATH', WP_PLUGIN_URL.'/'.plugin_basename( dirname(__FILE__) ).'/' );

		// Define the domain for translations
		load_plugin_textdomain(	'post-snippets', false, dirname(plugin_basename(__FILE__)) . '/languages/');

		// Check installed Wordpress version.
		global $wp_version;
		if ( version_compare($wp_version, '2.7', '>=') ) {
			include_once (dirname (__FILE__)."/tinymce/tinymce.php");
			$this->init_hooks();
		} else {
			$this->version_warning();
		}
	}

	/**
	* Initializes the hooks for the plugin
	*
	* @returns	Nothing
	*/
	function init_hooks() {
		add_action('admin_menu', array(&$this,'wp_admin'));
		add_action('admin_footer', array(&$this,'quicktags'));
	}
	
	/**
	* Displays a warning when installed in an old Wordpress Version
	*
	* @returns	Nothing
	*/
	function version_warning() {
		echo '<div class="updated fade"><p><strong>'.__('Post Snippets requires WordPress version 2.7 or later!', 'post-snippets').'</strong></p></div>';
	}

	/**
	* Handling of QuickTags in the HTML editor
	*
	*/
	function quicktags() {
		$quicktag_pages = array( 'post.php', 'post-new.php', 'page-new.php', 'page.php', 'comment.php' );
		for($i = 0; $i < count($quicktag_pages); $i++) {
			if( strpos($_SERVER['REQUEST_URI'], $quicktag_pages[$i]) ) {

				$snippets = get_option($this->plugin_options);
				if (!empty($snippets)) {
					echo '
						<script type="text/javascript">
							<!--
							if (postSnippetsToolbar = document.getElementById("ed_toolbar")) {
								var postSnippetsNr, postSnippetsButton;
						';
								for ($i = 0; $i < count($snippets); $i++) {

									// Make it js safe
									$theSnippet = str_replace('"','\"',str_replace(Chr(13), '', str_replace(Chr(10), '', $snippets[$i]['snippet'])));
									$var_arr = explode(",",$snippets[$i]['vars']);
									$theVariables = "";
									if (!empty($var_arr[0])) {
										for ($j = 0; $j < count($var_arr); $j++) {
											$theVariables = $theVariables . "'" . $var_arr[$j] . "'";
											if ( $j < (count($var_arr) -1) )
												$theVariables = $theVariables . ", ";
											
										}
									}
		
									echo '
										postSnippetsNr = edButtons.length;
										edButtons[postSnippetsNr] = new edButton(\'ed_ps'. $i . '\',    \'' . $snippets[$i]['title'] . '\',    \''.$snippets[$i]['snippet'].'\',  \'\',       \'\', -1);
										var postSnippetsButton = postSnippetsToolbar.lastChild;
										
										while (postSnippetsButton.nodeType != 1) {
											postSnippetsButton = postSnippetsButton.previousSibling;
										}
										
										postSnippetsButton = postSnippetsButton.cloneNode(true);
										postSnippetsToolbar.appendChild(postSnippetsButton);
										postSnippetsButton.value = \'' . $snippets[$i]['title'] . '\';
										postSnippetsButton.title = postSnippetsNr;
										var variables' . $i .' = new Array('.$theVariables.');
										postSnippetsButton.onclick = function () {edInsertSnippet(edCanvas, \''.$theSnippet.'\', variables' . $i .', parseInt(this.title));}
										postSnippetsButton.id = "ed_ps' . $i .'";
									';
								}
						echo '
							}
							function edInsertSnippet(myField,theSnippet,theVariables) {
								var myValue;
								var insertString;
								insertString = theSnippet;
								for (x in theVariables)
								{
									myValue = prompt(theVariables[x]);
									var searchfor = \'{\' + theVariables[x] + \'}\';
									var re = new RegExp(searchfor, \'g\');
									insertString = insertString.replace(re, myValue);
									
								}
								theSnippet = insertString;
								if (theSnippet) {
									edInsertContent(myField, theSnippet);
								}
							}							
							//-->
						</script>
						';
				}
				break;
			}
		}
	}

	/**
	* The Admin Page and all it's functions
	*
	*/
	function wp_admin()	{
		if (function_exists('add_options_page')) {
			add_options_page( 'Post Snippets Options', 'Post Snippets', 10, __FILE__, array(&$this, 'options_page') );
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
					$snippets[$i]['vars'] = trim($_POST[$i.'_vars']);
					$snippets[$i]['snippet'] = trim(stripslashes($_POST[$i.'_snippet']));
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
        </tr>
        </thead>
    
        <tfoot>
        <tr>
            <th scope="col" class="check-column"><input type="checkbox" /></th>
            <th scope="col"><?php _e( 'Title', 'post-snippets' ) ?></th>
            <th scope="col"><?php _e( 'Variables', 'post-snippets' ) ?></th>
            <th scope="col"><?php _e( 'Snippet', 'post-snippets' ) ?></th>
        </tr>
        </tfoot>
    
        <tbody>
		<?php 
		$snippets = get_option($this->plugin_options);
		if (!empty($snippets)) {
			for ($i=0; $i < count($snippets); $i++) { ?>
			<tr class='recent'>
			<th scope='row' class='check-column'><input type='checkbox' name='checked[]' value='<?= $i ?>' /></th>
			<td class='row-title'><input type='text' name='<?= $i ?>_title' value='<?= $snippets[$i]['title'] ?>' /></td>
			<td class='name'><input type='text' name='<?= $i ?>_vars' value='<?= $snippets[$i]['vars'] ?>' /></td>
			<td class='desc'><textarea name="<?= $i ?>_snippet" class="large-text" rows="3"><?= $snippets[$i]['snippet'] ?></textarea></td>
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
        <div class="postbox closed">
            <h3><?php _e( 'Help', 'post-snippets' ); ?></h3>
            <div class="inside">
				<p><?php _e( '<strong>Title</strong><br/>Give the snippet a title that helps you identify it in the post editor.', 'post-snippets' ); ?></p>
							
				<p><?php _e( '<strong>Variables</strong><br/>A comma separated list of custom variables you can reference in your snippet.<br/><br/>Example:<br/>url,name', 'post-snippets' ); ?></p>

				<p><?php _e( '<strong>Snippet</strong><br/>This is the block of text or HTML to insert in the post when you select the snippet from the insert button in the TinyMCE panel in the post editor. If you have entered predefined variables you can reference them from the snippet by enclosing them in {} brackets.<br/><br/>Example:<br/>To reference the variables in the example above, you would enter {url} and {name}.<br/><br/>So if you enter this snippet:<br/><i>This is the website of &lt;a href="{url}"&gt;{name}&lt;/a&gt;</i><br/>You will get the option to replace url and name on insert if they are defined as variables.', 'post-snippets' ); ?></p>
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

add_action( 'plugins_loaded', create_function( '', 'global $postSnippets; $postSnippets = new postSnippets();' ) );
?>