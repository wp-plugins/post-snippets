<?php
/**
 * Post Snippets Settings.
 *
 * Class that renders out the HTML for the settings screen and contains helpful
 * methods to simply the maintainance of the admin screen.
 *
 * @package		Post Snippets
 * @author		Johan Steen <artstorm at gmail dot com>
 * @since		Post Snippets 1.8.8
 */
class Post_Snippets_Settings
{

	private $plugin_options;

	public function set_options( $options )
	{
		$this->plugin_options = $options;
	}

	/**
	 * Render the options page.
	 *
	 * @since	Post Snippets 1.9.7
	 * @param	$page	string	Admin page to render. Default: options
	 */
	public function render( $page )
	{
		switch ( $page ) {
			case 'options':
				$this->options_page();
				break;
			
			default:
				$this->overview_page();
				break;
		}
	}


	// -------------------------------------------------------------------------
	// HTML generation for option pages.
	// -------------------------------------------------------------------------

	/**
	 * Creates the snippets administration page.
	 *
	 * For users with manage_options capability (admin, super admin).
	 *
	 * @since	Post Snippets 1.8.8
	 */
	private function options_page()
	{
?>
<div class=wrap>
    <h2>Post Snippets</h2>

	<form method="post" action="">
	<?php wp_nonce_field('update-options'); ?>

    <div class="tablenav">
        <div class="alignleft actions">
            <input type="submit" name="add-snippet" value="<?php _e( 'Add New Snippet', 'post-snippets' ) ?>" class="button-secondary" />
            <input type="submit" name="delete-selected" value="<?php _e( 'Delete Selected', 'post-snippets' ) ?>" class="button-secondary" />
			<span class="description"><?php _e( '(Use the help dropdown button above for additional information.)', 'post-snippets' ); ?></span>
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
		// $snippets = get_option($this->plugin_options);
		$snippets = $this->plugin_options;
		if (!empty($snippets)) {
			foreach ($snippets as $key => $snippet) {
			?>
			<tr class='recent'>
			<th scope='row' class='check-column'><input type='checkbox' name='checked[]' value='<?php echo $key; ?>' /></th>
			<td class='row-title'>
			<input type='text' name='<?php echo $key; ?>_title' value='<?php echo $snippet['title']; ?>' />
			</td>
			<td class='name'>
			<input type='text' name='<?php echo $key; ?>_vars' value='<?php echo $snippet['vars']; ?>' />
			<br/>
			<br/>
			<?php
			$this->checkbox(__('Shortcode', 'post-snippets'), $key.'_shortcode',
							$snippet['shortcode']);

			echo '<br/><strong>Shortcode Options:</strong><br/>';

			$this->checkbox(__('PHP Code', 'post-snippets'), $key.'_php',
							$snippet['php']);

			$wptexturize = isset( $snippet['wptexturize'] ) ? $snippet['wptexturize'] : false;
			$this->checkbox('wptexturize', $key.'_wptexturize',	$wptexturize);
			?>
			</td>
			<td class='desc'>
			<textarea name="<?php echo $key; ?>_snippet" class="large-text" style='width: 100%;' rows="5"><?php echo htmlspecialchars($snippet['snippet'], ENT_NOQUOTES); ?></textarea>
			<?php _e( 'Description', 'post-snippets' ) ?>:
			<input type='text' style='width: 100%;' name='<?php echo $key; ?>_description' value='<?php if (isset( $snippet['description'] ) ) echo esc_html($snippet['description']); ?>' /><br/>
			</td>
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


	/**
	 * Creates a read-only overview page.
	 *
	 * For users with edit_posts capability but without manage_options 
	 * capability.
	 *
	 * @since	Post Snippets 1.9.7
	 */
	private function overview_page()
	{
		echo '<div class=wrap>';
		echo '<h2>Post Snippets</h2>';
		// ---

		$snippets = $this->plugin_options;
		if (!empty($snippets)) {
			foreach ($snippets as $key => $snippet) {
				echo "<h3>{$snippet['title']}</h3>";

				if ($snippet['description'])
					echo "<p class='description'>{$snippet['description']}</p>";

			}
		}

		// ---
		echo '</div>';
	}


	// -------------------------------------------------------------------------
	// HTML and Form element methods
	// -------------------------------------------------------------------------
	
	/**
	 * Checkbox.
	 * Renders the HTML for an input checkbox.
	 *
	 * @param	string	$label		The label rendered to screen
	 * @param	string	$name		The unique name to identify the input
	 * @param	boolean	$checked	If the input is checked or not
	 */
	private function checkbox( $label, $name, $checked )
	{
		printf( '<input type="checkbox" name="%s" value="true"', $name );
		if ($checked)
			echo ' checked';
		echo ' />';
		echo ' '.$label.'<br/>';
	}
}
