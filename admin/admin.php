<?php

// 'brickset_apikey'

class BricksetAPIAdmin extends BricksetAPIFunctions
{
	public function __construct()
	{
		add_action( 'admin_menu', array( __CLASS__, 'add_brickset_submenu' ) );
		add_action( 'show_user_profile', array( __CLASS__, 'add_user_profile_fields' ) );
		add_action( 'edit_user_profile', array( __CLASS__, 'add_user_profile_fields' ) );
		add_action( 'personal_options_update', array( __CLASS__, 'save_user_profile_fields' ) );
		add_action( 'edit_user_profile_update', array( __CLASS__, 'save_user_profile_fields' ) );
	}
	/** 
	 *	Login Service Method
	 *
	 *	Authenticates a user with Brickset and returns a hash.
	 *	The hash is then stored as a meta value with the key of 'brickset_hash'
	 *	in the *_usersmeta table.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 *
	 *	@param	int 	$user_id
	 *	@param	string 	$username
	 *	@param	string	$password
	 */
	public function brickset_login( $user_id, $username, $password )
	{
		$user = get_userdata( $user_id );
		
		$params = 'u='.$username.'&p='.$password;
	
		parent::remote_request( 'login', $params );
		$user_hash = $this->results;
		
		try
		{
			if ( $this->httpcode != 200 )
				throw new Exception ( $this->error_msg );
			update_user_meta( $user->ID, 'brickset_hash',  $user_hash );
		}
		catch ( Exception $e ) 
		{
			echo $e->getMessage();
		}
	}
	
	/** 
	 *	
	 *
	 *
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.3
	 */
	public function user_profile_login()
	{
		
	}
	
	/** 
	 *	
	 *
	 *
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.3
	 */
	public function add_brickset_submenu()
	{
		add_submenu_page(
			'options-general.php',
			__( 'Brickset API' ),
			__( 'Brickset API' ),
			'manage_options',
			'bs_api_options',
			array( __CLASS__, 'bs_api_options_page' )
		);
	}
	
	/** 
	 *	
	 *
	 *
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.3
	 */
	public function bs_api_options_page()
	{
		?>
		<div class="wrap">
			<?php settings_errors(); ?>
			<div class="icon32" id="icon-options-general"><br></div>
			<h2><?php __( 'Unofficial Brickset API Settings', 'bs_api' ); ?></h2>
			<form method="post" action="options.php">
				<?php settings_fields( 'user_access_expire_options' ); ?>
				<?php do_settings_sections( __FILE__ ); ?>
				<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>" />
				</p>
			</form>
		</div>
		<?php
	}
}
$brickset_adimin = new BricksetAPIAdmin();