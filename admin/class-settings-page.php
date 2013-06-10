<?php

/** 
 *	Class that handles the interaction with the WordPress Settings API
 *
 *	@author		Nate Jacobs
 *	@date		6/2/13
 *	@since		1.0
 */
class BricksetAPISettingsPage
{
	public function __construct()
	{
		add_action( 'admin_menu', array( $this, 'add_brickset_submenu' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );
	}
	
	/** 
	 *	Adds the submenu to the default WordPress settings menu.
	 *
	 *	@author		Nate Jacobs
	 *	@date		2/2/13
	 *	@since		1.0
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
	 *	Display the settings page
	 *
	 *	@author		Nate Jacobs
	 *	@date		2/2/13
	 *	@since		1.0
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
	 *	Register all the settings sections and fields with the Settings API
	 *
	 *	@author		Nate Jacobs
	 *	@date		2/2/13
	 *	@since		1.0
	 */
	public function settings_init()
	{
		add_settings_section( 
			'bs-api-key-settings', 
			__( 'API Key', 'bs_api' ), 
			array( $this, 'webservice_settings_callback' ), 
			'brickset-api-options'	 
		);
		
		add_settings_field( 
			'bs-api-key', 
			__( 'Enter your API Key', 'bs_api' ), 
			array( $this, 'apikey_callback' ), 
			'brickset-api-options', 
			'bs-api-key-settings'
		);
		
		add_settings_section( 
			'bs-template-settings', 
			__( 'Template Tag Settings', 'bs_api' ), 
			array( $this, 'template_settings_callback' ), 
			'brickset-api-options'	 
		);
		
		add_settings_field( 
			'bs-currency', 
			__( 'Which currency to use?', 'bs_api' ), 
			array( $this, 'currency_callback' ), 
			'brickset-api-options', 
			'bs-template-settings'
		);
		
		add_settings_field( 
			'bs-bricklink', 
			__( 'Display link to Bricklink?', 'bs_api' ), 
			array( $this, 'bricklink_callback' ), 
			'brickset-api-options', 
			'bs-template-settings'
		);
		
/*
		add_settings_field( 
			'bs-transient', 
			__( 'How long should the data from Brickset be cached?', 'bs_api' ), 
			array( $this, 'transient_callback' ), 
			'brickset-api-options', 
			'bs-template-settings'
		);
*/
		
		register_setting( 
			'bs_api_options', 
			'brickset-api-settings'
		);
	}
	
	/** 
	 *	Displays the API key settings header
	 *
	 *	@author		Nate Jacobs
	 *	@date		2/2/13
	 *	@since		1.0
	 */
	public function webservice_settings_callback()
	{
		echo __( 'You may obtain a key at ', 'bs_api' )."<a href='http://brickset.com/contact/'>Brickset.com</a>";

	}
	
	/** 
	 *	Displays the text field for the API Key
	 *
	 *	@author		Nate Jacobs
	 *	@date		2/2/13
	 *	@since		1.0
	 */
	public function apikey_callback() 
	{
 		$settings = (array) get_option( 'brickset-api-settings' );
		$api_key = isset( $settings['api_key'] ) ? esc_attr( $settings['api_key'] ) : '';

		echo "<input type='text' name='brickset-api-settings[api_key]' value='$api_key' />";
		
 	}
 	
 	/** 
	 *	Displays the template tag settings header
	 *
	 *	@author		Nate Jacobs
	 *	@date		6/1/13
	 *	@since		1.3
	 */
	public function template_settings_callback()
	{
		echo __( 'These settings control what is displayed when you use the provided template tags, shortcodes, widgets and oembed.', 'bs_api' );

	}
	
	/** 
	 *	Displays the radio options for currency
	 *
	 *	@author		Nate Jacobs
	 *	@date		6/1/13
	 *	@since		1.3
	 */
	public function currency_callback() 
	{
 		$settings = (array) get_option( 'brickset-api-settings' );
		$currency = isset( $settings['currency'] ) ? esc_attr( $settings['currency'] ) : '';
		$currency_unk = isset( $settings['currency_unknown'] ) ? esc_attr( $settings['currency_unknown'] ) : '';
		
		echo "<input type='radio' class='radio' name='brickset-api-settings[currency]' value='us' ".checked( $currency, 'us', false )."/><label for='brickset-api-settings[us]'>".__( 'US Dollar', 'bs_api' )."</label>";
		echo "<br><input type='radio' class='radio' name='brickset-api-settings[currency]' value='ca' ".checked( $currency, 'ca', false )." /><label for='brickset-api-settings[ca]'>".__( 'CA Dollar', 'bs_api' )."</label>";
		echo "<br><input type='radio' class='radio' name='brickset-api-settings[currency]' value='uk' ".checked( $currency, 'uk', false )." /><label for='brickset-api-settings[uk]'>".__( 'UK Pound Sterling', 'bs_api' )."</label>";
		echo "<br><br>". __( 'If no retail price is available for the currency selected the plugin should', 'bs_api' ).":";
		echo "<br><br><input type='radio' name='brickset-api-settings[currency_unknown]' value='unk' ".checked( $currency_unk, 'unk', false )." /><label for='brickset-api-settings[currency_unknown]'>". __( 'Display the word Unknown', 'bs_api' )."</label>";
		echo "<br><input type='radio' name='brickset-api-settings[currency_unknown]' value='us' ".checked( $currency_unk, 'us', false )." /><label for='brickset-api-settings[currency_unknown]'>". __( 'Display the US retail price', 'bs_api' )."</label>";
		echo "<br><br><span>".__( 'Brickset provides retail prices in US dollars, CA dollars, and UK pound sterling ', 'bs_api' )."</span>";
		
 	}
 	
 	/** 
 	 *	Control if the Bricklink link should be appended to the set display
 	 *
 	 *	@author		Nate Jacobs
 	 *	@date		6/2/13
 	 *	@since		1.3
 	 */
 	public function bricklink_callback()
 	{
 		$settings = (array) get_option( 'brickset-api-settings' );
		$bricklink = isset( $settings['bricklink_link'] ) ? esc_attr( $settings['bricklink_link'] ) : '';
		
		echo "<input type='radio' class='radio' name='brickset-api-settings[bricklink_link]' value='1' ".checked( (bool) $bricklink, true, false )."/><label for='brickset-api-settings[bricklink_link]'>".__( 'Yes', 'bs_api' )."</label>";
		echo "<br><input type='radio' class='radio' name='brickset-api-settings[bricklink_link]' value='0' ".checked( (bool) $bricklink, false, false )." /><label for='brickset-api-settings[bricklink_link]'>".__( 'No', 'bs_api' )."</label>";
 	}
 	
 	/** 
 	 *	Control how long the transient is kept for
 	 *
 	 *	@author		Nate Jacobs
 	 *	@date		6/3/13
 	 *	@since		1.3
 	 */
 	public function transient_callback()
 	{
 		$settings = (array) get_option( 'brickset-api-settings' );
		$transient = isset( $settings['transient'] ) ? esc_attr( $settings['transient'] ) : '';
		
		echo "<input type='radio' class='radio' name='brickset-api-settings[transient]' value='day' ".checked( $transient, 'day', false )."/><label for='brickset-api-settings[transient]'>".__( 'One Day', 'bs_api' )."</label>";
		echo "<br><input type='radio' class='radio' name='brickset-api-settings[transient]' value='week' ".checked( $transient, 'week', false )." /><label for='brickset-api-settings[transient]'>".__( 'One Week', 'bs_api' )."</label>";
		echo "<br><input type='radio' class='radio' name='brickset-api-settings[transient]' value='month' ".checked( $transient, 'month', false )." /><label for='brickset-api-settings[transient]'>".__( 'One Month', 'bs_api' )."</label>";
		echo "<br><br><span>".__( 'The plugin uses the WordPress Transient API to store the data returned from Brickset to reduce page load times. By increasing the time the data is stored it requires fewer requests to Brickset. However, if the data changes frequently then your site will display out-of-date data until the cache expires.', 'bs_api' )."</span>";
 	}
}

$brickset_settings = new BricksetAPISettingsPage();