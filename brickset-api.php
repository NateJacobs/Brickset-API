<?php

/**
 *	Plugin Name: Brickset API
 *	Plugin URI: http://natejacobs.org
 *	Description: Implementation of the Brickset Webservice. Includes methods to get set data from Brickset as well as pre-formated methods to display set data. This is not an official Brickset.com offering. For more information on the webservice please visit <a href="http://www.brickset.com/webservices/">Brickset.com</a>.
 *	Version: 1.0
 *	License: GPL V2
 *	Author: Nate Jacobs <nate@natejacobs.org>
 *	Author URI: http://natejacobs.org
 */

class BricksetAPILoad
{
	/** 
	 *	Load Plugin 
	 *
	 *	Hook into the necessary actions to load the constants and call
	 *	the includes and admin classes.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 */
	public function __construct()
	{
		add_action('init', array( __CLASS__, 'localization' ), 1 );
		add_action( 'plugins_loaded', array( __CLASS__, 'constants' ), 2 );
		add_action( 'plugins_loaded', array( __CLASS__, 'includes' ), 3 );
		add_action( 'plugins_loaded', array( __CLASS__, 'admin' ), 4 );
	}
	
	/** 
	 *	Define Constants
	 *
	 *	Define the constants used through out the plugin.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 */
	public function constants() 
	{
		define( 'BRICKSET_API_VERSION', '0.3' );
		define( 'BRICKSET_API_DB_VERSION', 1 );
		define( 'BRICKSET_API_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'BRICKSET_API_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );
		define( 'BRICKSET_API_INCLUDES', BRICKSET_API_DIR . trailingslashit( 'inc' ) );
		define( 'BRICKSET_API_ADMIN', BRICKSET_API_DIR . trailingslashit( 'admin' ) );
	}
	
	/** 
	 *	Load Include Classes
	 *
	 *	Load the files containing the classes in the includes folder.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 */
	public function includes()
	{
		require_once( BRICKSET_API_INCLUDES . 'class-api-functions.php' );
		require_once( BRICKSET_API_INCLUDES . 'class-update.php' );
		require_once( BRICKSET_API_INCLUDES . 'class-widgets.php' );
		require_once( BRICKSET_API_INCLUDES . 'class-template-tags.php' );
		require_once( BRICKSET_API_INCLUDES . 'class-shortcodes.php' );
	}
	
	/** 
	 *	Load Admin Classes
	 *
	 *	Load the files containing the classes in the admin folder
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 */
	public function admin()
	{
		if ( is_admin() ) 
		{
			require_once( BRICKSET_API_ADMIN . 'class-settings-page.php' );
			require_once( BRICKSET_API_ADMIN . 'class-users-profile.php' );
		}
	}
	
	/** 
	 *	Localization
	 *
	 *	Declare text domain to use in translation.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.3
	 */
	public function localization() {
  		load_plugin_textdomain( 'bs_api', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
	}
}

new BricksetAPILoad();