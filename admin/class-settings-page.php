<?php

// 'brickset_apikey'

class BricksetAPISettingsPage
{
	public function __construct()
	{
		add_action( 'admin_menu', array( $this, 'add_brickset_submenu' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );
	}
	
	/** 
	*	Add Brickset Submenu
	*
	*	Adds the submenu to the default WordPress settings menu.
	*
	*	@author		Nate Jacobs
	*	@date		2/2/13
	*	@since		1.0
	*
	*	@param		null
	*/
	public function add_brickset_submenu()
	{
		add_options_page( 
			__( 'Brickset API Settings', 'bs_api' ), 
			__( 'Brickset API', 'bs_api' ), 
			'manage_options', 
			'brickset-api-options', 
			array( $this, 'bs_api_options_callback' ) 
		);
	}
	
	/** 
	*	Brickset Options Page
	*
	*	
	*
	*	@author		Nate Jacobs
	*	@date		2/2/13
	*	@since		1.0
	*
	*	@param		null
	*/
	public function bs_api_options_callback()
	{
		?>
		<div class="wrap">
			<div class="icon32" id="icon-options-general"><br></div>
			<h2><?php _e( 'Brickset API Settings', 'bs_api' ); ?></h2>
			<?php //settings_errors(); ?>
			<form method="post" action="options.php">
				<?php settings_fields( 'bs_api_options' ); ?>
				<?php do_settings_sections( 'brickset-api-options' ); ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
	
	/** 
	*	Settings Init
	*
	*	
	*
	*	@author		Nate Jacobs
	*	@date		2/2/13
	*	@since		1.0
	*
	*	@param		null
	*/
	public function settings_init()
	{
		add_settings_section( 
			'bs-webservice-settings', 
			__( 'API Key', 'bs_api' ), 
			array( $this, 'webservice_settings_callback' ), 
			'brickset-api-options'	 
		);
		add_settings_field( 
			'bs-api-key', 
			__( 'Enter your API Key', 'bs_api' ), 
			array( $this, 'apikey_callback' ), 
			'brickset-api-options', 
			'bs-webservice-settings'
		);

		register_setting( 
			'bs_api_options', 
			'brickset-api-settings'
		);
	}
	
	/** 
	*	API Key
	*
	*	
	*
	*	@author		Nate Jacobs
	*	@date		2/2/13
	*	@since		1.0
	*
	*	@param		null
	*/
	public function webservice_settings_callback()
	{
		echo __( 'You may obtain a key at ', 'bs_api' )."<a href='http://brickset.com/contact/'>Brickset.com</a>";

	}
	
	public function apikey_callback() 
	{
 		$settings = (array) get_option( 'brickset-api-settings' );
		$api_key = isset( $settings['api_key'] ) ? esc_attr( $settings['api_key'] ) : '';

		echo "<input type='text' name='brickset-api-settings[api_key]' value='$api_key' />";
		
 	}
}

new BricksetAPISettingsPage();