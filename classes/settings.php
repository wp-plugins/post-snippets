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
	// Constants
	const PLUGIN_OPTION_KEY = 'post_snippets_options';
	const USER_OPTION_KEY   = 'post_snippets';


	// -------------------------------------------------------------------------
	// Handle form I/O
	// -------------------------------------------------------------------------

	/**
	 * Update User Option.
	 *
	 * Sets the per user option for the read-only overview page.
	 *
	 * @since	Post Snippets 1.9.7
	 */
	private function set_user_options()
	{
		if ( isset( $_POST['update-post-snippets-user'] ) ) {
			$id = get_current_user_id();
			$render = isset( $_POST['render'] ) ? true : false;
			update_user_meta( $id, self::USER_OPTION_KEY, $render );
		}
	}

	/**
	 * Get User Option.
	 *
	 * Gets the per user option for the read-only overview page.
	 *
	 * @since	Post Snippets 1.9.7
	 * @return	boolean	If overview should be rendered on output or not
	 */
	private function get_user_options()
	{
		$id = get_current_user_id();
		$options = get_user_meta( $id, self::USER_OPTION_KEY, true ); 
		return $options;
	}


	// -------------------------------------------------------------------------
	// HTML generation for option pages
	// -------------------------------------------------------------------------

	/**
	 * Render the options page.
	 *
	 * @since	Post Snippets 1.9.7
	 * @param	string	$page	Admin page to render. Default: options
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
		$snippets = get_option( self::PLUGIN_OPTION_KEY );
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

<?php
		$this->submit( 'update-post-snippets', __('Update Snippets', 'post-snippets') );
		echo '</form>';
		// ---

		echo '</div>';
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
		// Header
		echo '<div class=wrap>';
		echo '<h2>Post Snippets</h2>';
		echo '<p>';
		_e( 'This is an overview of all snippets defined for this site. These snippets are inserted into posts from the post editor using the Post Snippets button. You can choose to see the snippets here as-is or as they are actually rendered on the website. Enabling rendered snippets for this overview might look strange if the snippet have dependencies on variables, CSS or other parameters only available on the frontend. If that is the case it is recommended to keep this option disabled.', 'post-snippets' );
		echo '</p>';

		// Form
		$this->set_user_options();
		$render = $this->get_user_options();

		echo '<form method="post" action="">';
		wp_nonce_field('update-user-options');
		$this->checkbox(__('Display rendered snippets', 'post-snippets'), 'render', $render  );
		$this->submit( 'update-post-snippets-user', __('Update', 'post-snippets') );
		echo '</form>';

		// Snippet List
		$snippets = get_option( self::PLUGIN_OPTION_KEY );
		if (!empty($snippets)) {
			foreach ($snippets as $key => $snippet) {

				echo "<hr style='border: none;border-top:1px dashed #aaa; margin:24px 0;' />";

				echo "<h3>{$snippet['title']}";
				if ($snippet['description'])
					echo "<span class='description'> {$snippet['description']}</span>";
				echo "</h3>";

				if ($snippet['vars'])
					printf( "<strong>%s:</strong> {$snippet['vars']}<br/>", __('Variables', 'post-snippets') );

				// echo "<strong>Variables:</strong> {$snippet['vars']}<br/>";

				$options = array();
				if ($snippet['shortcode'])
					array_push($options, 'Shortcode');
				if ($snippet['php'])
					array_push($options, 'PHP');
				if ($snippet['wptexturize'])
					array_push($options, 'wptexturize');
				if ($options)
					printf ( "<strong>%s:</strong> %s<br/>", __('Options', 'post-snippets'), implode(', ', $options) );

				printf( "<br/><strong>%s:</strong><br/>", __('Snippet', 'post-snippets') );
				if ( $render ) {
					echo do_shortcode( $snippet['snippet'] );
				} else {
					echo "<code>";
					echo nl2br( esc_html( $snippet['snippet'] ) );
					echo "</code>";
				}
			}
		}

		// Close
		echo '</div>';
	}


	// -------------------------------------------------------------------------
	// HTML and Form element methods
	// -------------------------------------------------------------------------
	
	/**
	 * Checkbox.
	 *
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

	/**
	 * Submit.
	 *
	 * Renders the HTML for a submit button.
	 *
	 * @since	Post Snippets 1.9.7
	 * @param	string	$name	The name that identifies the button on submit
	 * @param	string	$label	The label rendered on the button
	 */
	private function submit( $name, $label )
	{
		echo '<div class="submit">';
		printf( '<input type="submit" name="%s" value="%s" class="button-primary" />', $name, $label );
		echo '</div>';
	}
}
