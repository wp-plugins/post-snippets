<?php
/**
 * Post Snippets I/O.
 *
 * Class to handle import and export of Snippets.
 *
 * @package		Post Snippets
 * @author		Johan Steen <artstorm at gmail dot com>
 * @since		Post Snippets 2.0
 */
class Post_Snippets_ImportExport
{
	// Constants
	const PLUGIN_OPTION_KEY = 'post_snippets_options';


	/**
	 * Export Snippets.
	 *
	 * Check if an export file shall be created, or if a download url should be
	 * pushed to the footer. Also checks for old export files laying around and
	 * deletes them (for security).
	 *
	 * @since		Post Snippets 1.8
	 */
	public function export_snippets() {
		if ( isset($_POST['postsnippets_export']) ) {
			$url = $this->create_export_file();
			if ($url) {
				define('PSURL', $url);
				function psnippets_footer() {
					$export =  '<script type="text/javascript">
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
	 * @return		string			URL to the exported snippets
	 */
	private function create_export_file() {
		$snippets = serialize(get_option( self::PLUGIN_OPTION_KEY ));
		$snippets = apply_filters( 'post_snippets_export', $snippets );
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

}
