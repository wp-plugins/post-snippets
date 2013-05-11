<?php
/*
Plugin Name: Post Snippets
Plugin URI: http://johansteen.se/code/post-snippets/
Description: Build a library with snippets of HTML, PHP code or reoccurring text that you often use in your posts. Variables to replace parts of the snippet on insert can be used. The snippets can be inserted as-is or as shortcodes.
Author: Johan Steen
Author URI: http://johansteen.se/
Version: 2.2.3
License: GPLv2 or later
Text Domain: post-snippets 

Copyright 2009-2013 Johan Steen  (email : artstorm [at] gmail [dot] com)

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

/** Load all of the necessary class files for the plugin */
spl_autoload_register('PostSnippets::autoload');

/**
 * Init Singleton Class.
 *
 * @author  Johan Steen <artstorm at gmail dot com>
 * @link    http://johansteen.se/
 */
class PostSnippets
{
    private static $instance = false;

    const MIN_PHP_VERSION     = '5.2.4';
    const MIN_WP_VERSION      = '3.3';
    const OPTION_KEY          = 'post_snippets_options';
    const USER_META_KEY       = 'post_snippets';
    const TEXT_DOMAIN         = 'post-snippets';
    const FILE                = __FILE__;

    /**
     * Singleton class
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initializes the plugin.
     */
    private function __construct()
    {
        if (!$this->testHost()) {
            return;
        }

        add_action('init', array($this, 'textDomain'));
        register_uninstall_hook(__FILE__, array(__CLASS__, 'uninstall'));

        $this->createShortcodes();
        new PostSnippets_Admin;
        new PostSnippets_WPEditor;
    }

    /**
     * PSR-0 compliant autoloader to load classes as needed.
     *
     * @param  string  $classname  The name of the class
     * @return null    Return early if the class name does not start with the
     *                 correct prefix
     */
    public static function autoload($className)
    {
        if (__CLASS__ !== mb_substr($className, 0, strlen(__CLASS__))) {
            return;
        }
        $className = ltrim($className, '\\');
        $fileName  = '';
        $namespace = '';
        if ($lastNsPos = strrpos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
            $fileName .= DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, 'lib_'.$className);
        $fileName .='.php';

        require $fileName;
    }

    /**
     * Loads the plugin text domain for translation
     */
    public function textDomain()
    {
        $domain = self::TEXT_DOMAIN;
        $locale = apply_filters('plugin_locale', get_locale(), $domain);
        load_textdomain(
            $domain,
            WP_LANG_DIR.'/'.$domain.'/'.$domain.'-'.$locale.'.mo'
        );
        load_plugin_textdomain(
            $domain,
            false,
            dirname(plugin_basename(__FILE__)).'/lang/'
        );
    }

    /**
     * Fired when the plugin is uninstalled.
     */
    public function uninstall()
    {
        // Delete all snippets
        delete_option('post_snippets_options');

        // Delete any per user settings
        global $wpdb;
        $wpdb->query(
            "
            DELETE FROM $wpdb->usermeta 
            WHERE meta_key = 'post_snippets'
            "
        );
    }


    // -------------------------------------------------------------------------
    // Shortcode
    // -------------------------------------------------------------------------

    /**
     * Create the functions for shortcodes dynamically and register them
     */
    public function createShortcodes()
    {
        $snippets = get_option(self::OPTION_KEY);
        if (!empty($snippets)) {
            foreach ($snippets as $snippet) {
                // If shortcode is enabled for the snippet, and a snippet has been entered, register it as a shortcode.
                if ($snippet['shortcode'] && !empty($snippet['snippet'])) {
                    
                    $vars = explode(",", $snippet['vars']);
                    $vars_str = "";
                    foreach ($vars as $var) {
                        $attribute = explode('=', $var);
                        $default_value = (count($attribute) > 1) ? $attribute[1] : '';
                        $vars_str .= "\"{$attribute[0]}\" => \"{$default_value}\",";
                    }

                    // Get the wptexturize setting
                    $texturize = isset( $snippet["wptexturize"] ) ? $snippet["wptexturize"] : false;

                    add_shortcode(
                        $snippet['title'],
                        create_function(
                            '$atts,$content=null',
                            '$shortcode_symbols = array('.$vars_str.');
                            extract(shortcode_atts($shortcode_symbols, $atts));
                            
                            $attributes = compact( array_keys($shortcode_symbols) );
                            
                            // Add enclosed content if available to the attributes array
                            if ( $content != null )
                                $attributes["content"] = $content;
                            

                            $snippet = \''. addslashes($snippet["snippet"]) .'\';
                            // Disables auto conversion from & to &amp; as that should be done in snippet, not code (destroys php etc).
                            // $snippet = str_replace("&", "&amp;", $snippet);

                            foreach ($attributes as $key => $val) {
                                $snippet = str_replace("{".$key."}", $val, $snippet);
                            }

                            // Handle PHP shortcodes
                            $php = "'. $snippet["php"] .'";
                            if ($php == true) {
                                $snippet = PostSnippets::phpEval( $snippet );
                            }

                            // Strip escaping and execute nested shortcodes
                            $snippet = do_shortcode(stripslashes($snippet));

                            // WPTexturize the Snippet
                            $texturize = "'. $texturize .'";
                            if ($texturize == true) {
                                $snippet = wptexturize( $snippet );
                            }

                            return $snippet;'
                        )
                    );
                }
            }
        }
    }

    /**
     * Evaluate a snippet as PHP code.
     *
     * @since   Post Snippets 1.9
     * @param   string  $content    The snippet to evaluate
     * @return  string              The result of the evaluation
     */
    public static function phpEval($content)
    {
        if (!self::canExecutePHP()) {
            return $content;
        }

        $content = stripslashes($content);

        ob_start();
        eval ($content);
        $content = ob_get_clean();

        return addslashes($content);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Allow snippets to be retrieved directly from PHP.
     *
     * @since   Post Snippets 1.8.9.1
     *
     * @param   string      $snippet_name
     *          The name of the snippet to retrieve
     * @param   string      $snippet_vars
     *          The variables to pass to the snippet, formatted as a query string.
     * @return  string
     *          The Snippet
     */
    public static function getSnippet($snippet_name, $snippet_vars = '')
    {
        $snippets = get_option(self::OPTION_KEY, array());
        for ($i = 0; $i < count($snippets); $i++) {
            if ($snippets[$i]['title'] == $snippet_name) {
                parse_str(htmlspecialchars_decode($snippet_vars), $snippet_output);
                $snippet = $snippets[$i]['snippet'];
                $var_arr = explode(",", $snippets[$i]['vars']);

                if (!empty($var_arr[0])) {
                    for ($j = 0; $j < count($var_arr); $j++) {
                        $snippet = str_replace("{".$var_arr[$j]."}", $snippet_output[$var_arr[$j]], $snippet);
                    }
                }
            }
        }
        return do_shortcode($snippet);
    }

    /**
     * Allow other plugins to disable the PHP Code execution feature.
     *
     * @see   http://wordpress.org/extend/plugins/post-snippets/faq/
     * @since 2.1
     */
    public static function canExecutePHP()
    {
        return apply_filters('post_snippets_php_execution_enabled', true);
    }


    // -------------------------------------------------------------------------
    // Environment Checks
    // -------------------------------------------------------------------------

    /**
     * Checks PHP and WordPress versions.
     */
    private function testHost()
    {
        // Check if PHP is too old
        if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<')) {
            // Display notice
            add_action('admin_notices', array(&$this, 'phpVersionError'));
            return false;
        }

        // Check if WordPress is too old
        global $wp_version;
        if (version_compare($wp_version, self::MIN_WP_VERSION, '<')) {
            add_action('admin_notices', array(&$this, 'wpVersionError'));
            return false;
        }
        return true;
    }

    /**
     * Displays a warning when installed on an old PHP version.
     */
    public function phpVersionError()
    {
        echo '<div class="error"><p><strong>';
        printf(
            'Error: %3$s requires PHP version %1$s or greater.<br/>'.
            'Your installed PHP version: %2$s',
            self::MIN_PHP_VERSION,
            PHP_VERSION,
            $this->getPluginName()
        );
        echo '</strong></p></div>';
    }

    /**
     * Displays a warning when installed in an old Wordpress version.
     */
    public function wpVersionError()
    {
        echo '<div class="error"><p><strong>';
        printf(
            'Error: %2$s requires WordPress version %1$s or greater.',
            self::MIN_WP_VERSION,
            $this->getPluginName()
        );
        echo '</strong></p></div>';
    }

    /**
     * Get the name of this plugin.
     *
     * @return string The plugin name.
     */
    private function getPluginName()
    {
        $data = get_plugin_data(self::FILE);
        return $data['Name'];
    }
}

add_action('plugins_loaded', array('PostSnippets', 'getInstance'));

// -----------------------------------------------------------------------------
// Helper functions
// -----------------------------------------------------------------------------

/**
 * Allow snippets to be retrieved directly from PHP.
 * This function is a wrapper for Post_Snippets::get_snippet().
 *
 * @since   Post Snippets 1.6
 * @deprecated Post Snippets 2.1
 *
 * @param   string      $snippet_name
 *          The name of the snippet to retrieve
 * @param   string      $snippet_vars
 *          The variables to pass to the snippet, formatted as a query string.
 * @return  string
 *          The Snippet
 */
function get_post_snippet($snippet_name, $snippet_vars = '')
{
    _deprecated_function(__FUNCTION__, '2.1', 'PostSnippets::getSnippet()');
    return PostSnippets::getSnippet($snippet_name, $snippet_vars);
}
