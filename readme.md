# Brickset API
A WordPress plugin that implementats the Brickset Webservice. Includes methods to get set data from Brickset as well as pre-formated methods to display set data. This is not an official Brickset.com offering. For more information on the webservice please visit [Brickset.com](http://www.brickset.com/webservices/ "Brickset").

### Template Tags
The plugin has numerous template tags you are able to use in your theme. They are listed below with a short description.

*	```brickset_themes();``` outputs a list of all themes.
*	```brickset_subthemes( $theme );``` outputs a table of all subthemes of the theme specified, the number of sets, and years available.
*	```brickset_theme_years( $theme );``` outputs a table of all the years the specified theme was available and the number of sets each year.
*	```brickset_popular_searches();``` outputs a table of the most popular search terms on Brickset.com and the weights applied.
*	```brickset_updated_since( $date );``` outputs a table of the sets updated since the date specified. The table includes thumbnail, set name and set number.

### Shortcodes
The plugin has several shortcodes you may use in your posts and pages to display information.

*	```[bs_set number='XXX']``` outputs information about a specific set. Thumbnail, set name, set number, pieces, theme, subtheme and year.

### Widgets
The plugin has one widget you can activate to display a list of all themes on Brickset with a link to browse each theme on Brickset.com

### Advanced Use
This example shows how to get the data about a specific set.

1. Instantiate the class.
```
$brickset = new BricksetAPIFunctions;
```

2. Pass a set number to the get_by_number method.
```
$set_data = $brickset->get_by_number( '8014' );
```
3. Test for an error
```
if( is_wp_error( $brickset ) {
	echo $brickset->get_error_message;
}
```

4. Display the results how you like. This will display the set's theme.
```
else {
	echo $set_data->theme;
}
```

### Road Map
1. Create methods to display sets specific to a user.
2. Create methods to allow users to update owned or wanted sets.

### Changelog
#### Version 1.0
*	Enter your Brickset API from settings submenu.
*	Allow users to authenticate with Brickset from their profile page.

#### Version 0.3
*	Added get_owned function and shortcode.

#### Version 0.2
*	Get_set and my_wanted shortcodes added.
*	BricksetAPIFunctions updated to use wp_remote_get.
*	Added template tags for use in themes.

#### Version 0.1
*	Initial plugin.