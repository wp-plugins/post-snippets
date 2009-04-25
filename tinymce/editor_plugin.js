// Docu : http://wiki.moxiecode.com/index.php/TinyMCE:Create_plugin/3.x#Creating_your_own_plugins

(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('post_snippets');
	
	tinymce.create('tinymce.plugins.post_snippets', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');

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

			// Register example button
			ed.addButton('post_snippets', {
				title : 'post_snippets.desc',
				cmd : 'mcepost_snippets',
				image : url + '/post-snippets.gif'
			});
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
					longname  : 'post_snippets',
					author 	  : 'Johan Steen',
					authorurl : 'http://coding.cglounge.com/',
					infourl   : 'http://coding.cglounge.com/',
					version   : "1.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('post_snippets', tinymce.plugins.post_snippets);
})();


