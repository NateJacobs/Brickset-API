=== Brickset API ===

Contributors: NateJacobs 
Tags: brickset, lego, brick
Requires at least: 3.5
Tested up to: 3.5.1
Stable tag: 1.0

Display your favorite LEGO® set information on your website using the Brickset API. 

== Description ==

Implementation of the Brickset Webservice. Includes methods to get LEGO® set and theme data from Brickset as well as pre-formated methods to display set data. This is not an official Brickset.com offering. For more information on the webservice please visit http://www.brickset.com/webservices. LEGO® is a trademark of the LEGO Group of companies which does not sponsor, authorize or endorse this site.

== Installation ==

1. Upload the entire `bricset-api` folder to the `wp-content/plugins/` directory of your WordPress installation
2. Activate the plugin through the Plugins menu in WordPress
3. Add your Brickset.com API key to the Brickset settings page which can be found as a submenu under the Settings menu
3. Either add a Brickset link (url) to a set or theme in a post or page, or add a template tag to a theme file, or for advanced use instantiate the class and create your own display method.

== Frequently Asked Questions ==

= Can I add sets to my set list on Brickset with this plugin? =
Not yet, but functions like that are on the way.

= Does this plugin require an API Key from Brickset = 
To display more than twenty sets from a search query an API key is required. You can obtain one by contacting Brickset on this [page](http://brickset.com/contact/)

== Screenshots ==
1. Individual Set Display
2. Years theme is available
3. Sets in space theme

== Changelog ==

= Version 1.0 =
*	Enter your Brickset API from the settings submenu.
*	Allow users to authenticate with Brickset from their profile page.
*	Refactor code base
*	Add localization and translation .po
*	Create webservice-definition.json to display details about the availble calls
*	Brickset oembed support for set and theme URLs

= Version 0.3 =
*	Added get_owned function and shortcode.

= Version 0.2 =
*	Get_set and my_wanted shortcodes added.
*	BricksetAPIFunctions updated to use wp_remote_get.
*	Added template tags for use in themes.

= Version 0.1 =
*	Initial plugin.