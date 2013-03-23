=== Brickset API ===

Contributors: NateJacobs 
Tags: brickset, lego, brick
Requires at least: 3.5
Tested up to: 3.5.1
Stable tag: 1.0

Display your favorite LEGO® set information on your website using the Brickset API. 

== Description ==

Implementation of the Brickset Webservice. Includes methods to get LEGO® set and theme data from Brickset as well as pre-formated methods to display set data. This is not an official Brickset.com offering. For more information on the webservice please visit http://www.brickset.com/webservices. LEGO® is a trademark of the LEGO Group of companies which does not sponsor, authorize or endorse this site.

You can obtain an API key from [Brickset](http://brickset.com/contact/).

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
The plugin has one widget you can activate to display a list of all themes on Brickset with a link to browse each theme on Brickset.com

= Advanced Use =
This example shows how to get the data about a specific set.

1. Instantiate the class.
`
$brickset = new BricksetAPIFunctions;
`

2. Pass a set number to the get_by_number method.
`
$set_data = $brickset->get_by_number( '8014' );
`
3. Test for an error
`
if( is_wp_error( $brickset ) {
	echo $brickset->get_error_message;
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
2. Allow users to update owned or wanted sets.
3. Allow users to manage minifig collection.

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