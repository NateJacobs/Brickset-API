<?php

/**
 *	Plugin Name: Brickset API
 *	Plugin URI: http://natejacobs.org
 *	Description: Implementation of the Brickset Webservice. Includes methods to get set data from Brickset as well as pre-formated methods to display set data. This is not an official Brickset.com offering. For more information on the webservice please visit <a href="http://www.brickset.com/webservices/">Brickset.com</a>.
 *	Version: 0.2
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
		add_action( 'plugins_loaded', array( $this, 'constants' ), 1 );
		add_action( 'plugins_loaded', array( $this, 'includes' ), 2 );
		add_action( 'plugins_loaded', array( $this, 'admin' ), 3 );
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
		define( 'BRICKSET_API_VERSION', '0.2' );
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
		require_once( BRICKSET_API_INCLUDES . 'functions.php' );
		require_once( BRICKSET_API_INCLUDES . 'display.php' );
		require_once( BRICKSET_API_INCLUDES . 'update.php' );
		require_once( BRICKSET_API_INCLUDES . 'widgets.php' );
		require_once( BRICKSET_API_INCLUDES . 'template_tags.php' );
		require_once( BRICKSET_API_INCLUDES . 'shortcodes.php' );
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
			require_once( BRICKSET_API_ADMIN . 'admin.php' );
			//require_once( BRICKSET_API_ADMIN . 'help-text.php' );
		}
	}
}

new BricksetAPILoad();