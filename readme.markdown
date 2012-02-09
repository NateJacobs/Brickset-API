##Brickset API
A WordPress plugin that implementats the Brickset Webservice. Includes methods to get set data from Brickset as well as pre-formated methods to display set data. This is not an official Brickset.com offering. For more information on the webservice please visit <a href="http://www.brickset.com/webservices/">Brickset.com</a>.

This plugin is in beta. 

###Template Tags
The plugin has numerous template tags you are able to use in your theme. They are listed below with a short description.

*	```brick_themes();``` outputs a list of all themes.
*	```brick_subthemes( $theme );``` outputs a table of all subthemes of the theme specified, the number of sets, and years available.
*	```brick_theme_years( $theme );``` outputs a table of all the years the specified theme was available and the number of sets each year.
*	```brick_popular_searches();``` outputs a table of the most popular search terms on Brickset.com and the weights applied.
*	```brick_updated_since( $date );``` outputs a table of the sets updated since the date specified. The table includes thumbnail, set name and set number.

###Shortcodes
The plugin has several shortcodes you may use in your posts and pages to display information.

*	```[bs_set number='XXX']``` outputs information about a specific set. Thumbnail, set name, set number, pieces, theme, subtheme and year.

*	```[bs_my_wanted]``` outputs a table with the sets wanted by the post or page's author.

*	```[bs_my_owned]``` outputs a table with the sets owned by the post or page's author.

###How to Use
This example shows how to get the data about a specific set.

1. Instantiate the class.
```
<?php $brickset_api = new BricksetAPIFunctions; ?>
```

2. Pass a set number to the get_set_number method.
```
<?php $set_data = $brickset_api->get_by_number( '8014' ); ?>
```

3. Display the results how you like. This will display the set's theme.
```
<?php echo $set_data->theme.'<br>'; ?>
```

###Future Development
1. Create a settings page to allow for front-end addition of api key.
2. Allow users to authenticate with Brickset.com and store the user_hash.
3. Create methods to display sets specific to a user.
4. Create methods to allow users to update owned or wanted sets.

###Changelog
0.1

*	Initial plugin

0.2

*	Get_set and my_wanted shortcodes added
*	BricksetAPIFunctions updated to use wp_remote_get
*	Added template tags for use in themes

0.3

*	Added get_owned function and shortcode