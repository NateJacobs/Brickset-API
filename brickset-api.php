<?php

/**
 *	Plugin Name: Brickset API
 *	Plugin URI: https://github.com/NateJacobs/Brickset-API
 *	Description: Implementation of the Brickset Webservice. Includes methods to get set data from Brickset as well as pre-formated methods to display set data. This is not an official Brickset.com offering. For more information on the webservice please visit <a href="http://www.brickset.com/webservices/">Brickset.com</a>.
 *	Version: 1.3
 *	License: GPL V2
 *	Author: Nate Jacobs <nate@natejacobs.org>
 *	Author URI: http://natejacobs.org
 */

class BricksetAPILoad
{
	/** 
	 *	Hook into the necessary actions to load the constants and call
	 *	the includes and admin classes.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 */
	public function __construct()
	{
		add_action( 'init', array( $this, 'localization' ), 1 );
		add_action( 'plugins_loaded', array( $this, 'constants' ), 2 );
		add_action( 'plugins_loaded', array( $this, 'includes' ), 3 );
		add_action( 'plugins_loaded', array( $this, 'admin' ), 4 );
		add_action( 'plugins_loaded', array( $this, 'upgrade' ), 5 );
		add_filter( 'http_request_timeout', array ( $this, 'http_request_timeout' ) );
	}
	
	/** 
	 *	Define the constants used through out the plugin.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 */
	public function constants() 
	{
		define( 'BRICKSET_API_VERSION', '1.3' );
		define( 'BRICKSET_API_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'BRICKSET_API_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );
		define( 'BRICKSET_API_INCLUDES', BRICKSET_API_DIR . trailingslashit( 'inc' ) );
		define( 'BRICKSET_API_ADMIN', BRICKSET_API_DIR . trailingslashit( 'admin' ) );
	}
	
	/** 
	 *	Load the files containing the classes in the includes folder.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 */
	public function includes()
	{
		require_once( BRICKSET_API_INCLUDES . 'class-utilities.php' );
		require_once( BRICKSET_API_INCLUDES . 'class-search-functions.php' );
		require_once( BRICKSET_API_INCLUDES . 'class-oembed.php' );
		require_once( BRICKSET_API_INCLUDES . 'class-widgets.php' );
		require_once( BRICKSET_API_INCLUDES . 'class-template-tags.php' );
		require_once( BRICKSET_API_INCLUDES . 'class-shortcodes.php' );
		require_once( BRICKSET_API_INCLUDES . 'class-update-functions.php' );
	}
	
	/** 
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
	 *	Declare text domain to use in translation.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.3
	 */
	public function localization() 
	{
  		load_plugin_textdomain( 'bs_api', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
	}
	
	/** 
	 *	Sometimes requests take longer than 5 seconds
	 *
	 *	@author		Nate Jacobs
	 *	@date		3/13/13
	 *	@since		1.0
	 *
	 *	@param		int	$seconds
	 */
	public function http_request_timeout ( $seconds ) 
	{
		return $seconds < 10 ? 15 : $seconds;
	}
	
	/** 
	 *	Ensure the currency option is set. The default will be US dollars
	 *
	 *	@author		Nate Jacobs
	 *	@date		6/2/13
	 *	@since		1.3	
	 */
	public function upgrade()
	{
		if( '1.3' == BRICKSET_API_VERSION )
		{
			$settings = (array) get_option( 'brickset-api-settings' );
			
			if( !isset( $settings['currency'] ) )
			{
				$settings['currency'] = 'us';
				update_option( 'brickset-api-settings', $settings );
			}
		}
	}
}

$brickset_load = new BricksetAPILoad();