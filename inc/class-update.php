<?php

class BricksetAPIUpdate
{
	CONST API_DB = 'brickset_api_db_version';
	
	public function __construct()
	{
		// Hook into init and run the version check
		add_action( 'init', array( $this, 'ver_check' ));
	}
	
	/** 
	 *	Version Check
	 *
	 *	Grab the database version from the *_options table. If the db version is not there, install settings.
	 *	If the db version is there, but is less than the plugin db version, update settings.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 */
	public function ver_check()
	{
		// Grab the current database version from the wp_options table
		$current_db_ver = get_option( self::API_DB );
		// Grab the plugin settings from the wp_options table
		$settings = get_option( self::API_DB );
		// Check if there is a current database version and if there are no plugin settings
		if ( empty( $current_db_ver ) && false === $settings )
		{
			// If that is the case then run the install method
			$this->install();
		}
		// Now check if the current database version is less than the plugin defined database value
		elseif ( intval( $current_db_ver ) < intval( BRICKSET_API_DB_VERSION ) )
		{
			// If that is the case then run the update method. 
			$this->update();
		}
	}
	
	/** 
	 *	Install Settings
	 *
	 *	If the plugin has not been activated before (no db version exists in wp_options) install settings.
	 *
	 *	@author		Nate Jacobs
	 *	@since 		0.1
	 */
	protected function install()
	{
		add_option( self::API_DB, BRICKSET_API_DB_VERSION );
	}
	
	/** 
	 *	Update Settings
	 *
	 *	If the plugin has been activated before (the db version exists in wp_options), but the db version is older than the plugin db version, update the settings.
	 *
	 *	@author		Nate Jacobs
	 *	@since 		0.1
	 */
	protected function update()
	{
		update_option( self::API_DB, BRICKSET_API_DB_VERSION );
	}
}
new BricksetAPIUpdate();