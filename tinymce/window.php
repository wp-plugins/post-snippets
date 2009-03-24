<?php
// look up for the path
require_once( dirname( dirname(__FILE__) ) .'/post-snippets-config.php');

global $wpdb;

// check for rights
if ( !is_user_logged_in() || !current_user_can('edit_posts') ) 
	wp_die(__( "You are not allowed to be here", 'post-snippets' ));

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Post Snippets</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
	<script language="javascript" type="text/javascript">
	function init() {
		tinyMCEPopup.resizeToInnerSize();
	}
	
	function insertSnippet() {
		
		var insertString;

		<?php
		$snippets = get_option($postSnippets->plugin_options);
		for ($i = 0; $i < count($snippets); $i++) { ?>
		var panel<?= $i ?> = document.getElementById('ps_panel<?= $i ?>');
		<?php }	?>

		var rss = document.getElementById('ps_panel0');
		
		<?php
		$snippets = get_option($postSnippets->plugin_options);
		for ($i = 0; $i < count($snippets); $i++) {
			// Make it js safe
			$theString = str_replace('"','\"',str_replace(Chr(13), '', str_replace(Chr(10), '', $snippets[$i]['snippet'])))
		?>

		if (panel<?= $i ?>.className.indexOf('current') != -1) {
			insertString = "<?= $theString; ?>";
			<?php
			$var_arr = explode(",",$snippets[$i]['vars']);
			if (!empty($var_arr[0])) {
				for ($j = 0; $j < count($var_arr); $j++) { ?>
					var var_<?= $i ?>_<?= $j ?> = document.getElementById('var_<?= $i ?>_<?= $j ?>').value;
					insertString = insertString.replace(/{<?= $var_arr[$j] ?>}/g, var_<?= $i ?>_<?= $j ?>);
			<?php } } ?>
		}
		<?php }	?>
	
		
		if(window.tinyMCE) {
			window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, insertString);
			//Peforms a clean up of the current editor HTML. 
			//tinyMCEPopup.editor.execCommand('mceCleanup');
			//Repaints the editor. Sometimes the browser has graphic glitches. 
			tinyMCEPopup.editor.execCommand('mceRepaint');
			tinyMCEPopup.close();
		}
		
		return;
	}
	</script>
	<base target="_self" />
</head>
<body id="link" onload="tinyMCEPopup.executeOnLoad('init();');document.body.style.display='';" style="display: none">
<!-- <form onsubmit="insertLink();return false;" action="#"> -->
	<form name="postSnippets" action="#">

	<div class="tabs">
		<ul>
		<?php
		$snippets = get_option($postSnippets->plugin_options);
		for ($i = 0; $i < count($snippets); $i++) { ?>
			<li id="ps_tab<?= $i ?>"<?php if ($i == 0) {?> class="current"><?php } ?><span><a href="javascript:mcTabs.displayTab('ps_tab<?= $i ?>','ps_panel<?= $i ?>');" onmousedown="return false;"><?php echo $snippets[$i]['title']; ?></a></span></li>
		<?php }	?>
		</ul>
	</div>
	
	<div class="panel_wrapper">
    <?php
	$snippets = get_option($postSnippets->plugin_options);
	for ($i = 0; $i < count($snippets); $i++) { ?>
        <div id="ps_panel<?= $i ?>" class="panel<?php if ($i == 0) {?> current<?php } ?>">
        <br />
        <table border="0" cellpadding="4" cellspacing="0">
		<?php
        $var_arr = explode(",",$snippets[$i]['vars']);
		if (!empty($var_arr[0])) {
			for ($j = 0; $j < count($var_arr); $j++) { ?>
			 <tr>
				<td nowrap="nowrap"><label for="var_<?= $i ?>_<?= $j ?>"><?php echo($var_arr[$j]);?>:</label></td>
				<td><input type="text" id="var_<?= $i ?>_<?= $j ?>" name="var_<?= $i ?>_<?= $j ?>" style="width: 190px" />
				</td>
			  </tr>
        <?php } } ?>
        </table>
        </div>
<?php } ?>
	</div>

	<div class="mceActionPanel">
		<div style="float: left">
			<input type="button" id="cancel" name="cancel" value="<?php _e( 'Cancel', 'post-snippets' ); ?>" onclick="tinyMCEPopup.close();" />
		</div>

		<div style="float: right">
			<input type="submit" id="insert" name="insert" value="<?php _e( 'Insert', 'post-snippets' ); ?>" onclick="insertSnippet();" />
		</div>
	</div>
</form>
</body>
</html>