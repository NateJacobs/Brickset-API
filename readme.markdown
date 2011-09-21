##Brickset API##
A WordPress plugin that implementats the Brickset Webservice. Includes methods to get set data from Brickset as well as pre-formated methods to display set data. This is not an official Brickset.com offering. For more information on the webservice please visit <a href="http://www.brickset.com/webservices/">Brickset.com</a>.

This plugin is in beta, only version 0.1. 

###How to Use###
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

###Future Development###
1. Create a settings page to allow for front-end addition of api key.
2. Allow users to authenticate with Brickset.com and store the user_hash.

###Changelog###
0.1
◊ Initial plugin