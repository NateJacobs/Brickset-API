=== Brickset API ===

Contributors: NateJacobs 
Tags: brickset, lego, brick
Requires at least: 3.5
Tested up to: 4.1
Stable tag: 1.4.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display your favorite LEGO® set information on your website using the Brickset API. 

== Description ==

This plugin is an implementation of the Brickset Webservice. It includes methods to get LEGO® set and theme data from Brickset as well as pre-formated methods to display set data. The set data requested is cached daily to reduce HTTP requests and page load time. Please visit [Brickset](http://brickset.com/) for more information on the [webservice](http://www.brickset.com/webservices). You can obtain an API key from [Brickset](http://brickset.com/contact/).

The main development is all happening on [GitHub](https://github.com/NateJacobs/Brickset-API).

This is not an official Brickset.com plugin. LEGO® is a trademark of the LEGO Group of companies which does not sponsor, authorize or endorse this plugin.

= Oembed =
You can paste a Brickset set or theme url into your post or page. Once you publish the post or page information about the set or theme will be displayed. Two example URLs are below.

* Set URL: http://brickset.com/detail/?Set=79003-1
* Theme URL: http://brickset.com/browse/themes/?theme=The%20Hobbit

= Template Tags =
The plugin has numerous template tags you are able to use in your theme. They are listed below with a short description.

*	`brickset_themes();` outputs a list of all themes.
*	`brickset_subthemes( $theme );` outputs a table of all subthemes of the theme specified, the number of sets, and years available.
*	`brickset_theme_years( $theme );` outputs a table of all the years the specified theme was available and the number of sets each year.
*	`brickset_popular_searches();` outputs a list of the most popular search terms on Brickset.com.
*	`brickset_updated_since( $date );` outputs a table of the sets updated since the date specified. The table includes thumbnail, set name and set number.
*	`brickset_set_number( $set );` outputs a list of details about the sets specified. More than one set can be requested by separating the set numbers with a comma, e.g. '1380,10240'.

= Shortcode = 
The plugin has a shortcode you may use in your posts and pages to display information about a set or sets.

*	`[bs_set number='XXX']` outputs information about a specific set. Thumbnail, set name, set number, pieces, theme, subtheme and year. You can display multiple sets by seperating the set numbers with a comma.

= Widgets =
The plugin has four widgets you can activate in your theme. The first displays a list of all themes on Brickset with a link to browse each theme on Brickset.com and the second to display the details about a specific set. The third allows you to show off the total count of your minifigs. The last widget displays a count of sets produced in each year for a given theme.

= Advanced Use =
This example shows how to get the data about a specific set. You can find the developer docs [here](https://github.com/NateJacobs/Brickset-API/wiki).

1. Instantiate the class.
`
$brickset = new BricksetAPISearch();
`

2. Pass a set number to the get_by_number method.
`
$set_data = $brickset->get_by_number( '8014' );
`
3. Test for an error
`
if( is_wp_error( $set_data ) {
	echo $set_data->get_error_message;
}
`

4. Display the results how you like. This will display the set's theme.
`
else {
	echo $set_data->theme;
}
`
== Road Map ==
1. Template tags to display sets specific to a user.
2. Caching of images

You can follow all the open feature requests and enhancements on [GitHub](https://github.com/NateJacobs/Brickset-API/issues?milestone=&page=1&state=open).

== Installation ==

1. Upload the entire `brickset-api` folder to the `wp-content/plugins/` directory of your WordPress installation or download from the Plugin Repository
2. Activate the plugin through the Plugins menu in WordPress
3. Add your Brickset.com API key to the Brickset settings page which can be found as a submenu under the Settings menu
3. Either add a Brickset link (url) to a set or theme in a post or page, or add a template tag to a theme file, or for advanced use instantiate the class and create your own display method.

== Frequently Asked Questions ==

= Can I add sets to my set list on Brickset with this plugin? =
Yes you can, but it requires writing custom code using the API functions of the plugin.

= Does this plugin require an API Key from Brickset = 
To display more than twenty sets from a search query an API key is required. You can obtain one by contacting Brickset on this [page](http://brickset.com/contact/).

== Screenshots ==
1. Individual Set Display
2. Years theme is available
3. Sets in space theme

== Changelog ==

= Version 1.4.0 =
*	New owned minifig widget - display how many minifigs you own
*	New theme years widget - display how many sets were produced for a given theme in a given year
*	Introduce filters to enable overwriting the default cache time of 24 hours for Brickset data
*	Add third parameter to shortcode functions

= Version 1.3 =
*	Add a new widget to display set details
*	Add current_user_can check to user profile settings
*	Administrators can now select which currency (US dollar, CA dollar, UK pound) is displayed when using the built in template tags, shortcode and oembed set data
*	Administrators can choose if the Bricklink link is displayed in template tags, shortcode and oembed set data

= Version 1.2 =
*	Removed anonymous function from class-widgets.php

= Version 1.1 =
*	Add ability to update wanted and owned sets
*	Add ability to update the quantity owned of a set
*	Add ability to search a user's minifig collections
*	Add ability to update wanted and owned minifigs
*	Updated class name - BricksetAPIFunctions became BricksetAPISearch

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