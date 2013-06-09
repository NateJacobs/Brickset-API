<?php

/** 
 *	Display necessary fields on the user profile page to allow a user to authenticate themselves with Brickset.
 *	Take the user hash and save it in the usermeta table.
 *
 *	@author		Nate Jacobs
 *	@date		2/2/13
 *	@since		1.0
 */
class BricksetAPIUserProfile extends BricksetAPIUtilities
{
	/** 
	 *	Start things off
	 *
	 *	@author		Nate Jacobs
	 *	@date		2/2/13
	 *	@since		1.0
	 */
	public function __construct()
	{
		add_action( 'show_user_profile', array( $this, 'add_user_profile_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'add_user_profile_fields' ) );
		add_action( 'personal_options_update', array( $this, 'set_brickset_user_hash' ) );
		add_action( 'edit_user_profile_update', array( $this, 'set_brickset_user_hash' ) );
	}

	/** 
	 *	Add Brickset username, password, and userHash fields to the profile page.
	 *
	 *	@author		Nate Jacobs
	 *	@date		2/3/13
	 *	@since		1.0
	 *
	 *	@param		object	WP_User
	 */
	public function add_user_profile_fields( $user)
	{
		$user_hash = $this->get_user_hash( $user->ID );
		?>
		<h3><?php _e( 'Brickset Login Information', 'bs_api' ); ?></h3>
		<span><?php _e( 'If the Brickset Identifier is filled you do not need to add your username and password unless you have changed your password on Brickset.', 'bs_api' ); ?></span>
		<table class="form-table">
		<tr>
			<th><label for="bs_user_name"><?php _e( 'Brickset Username', 'bs_api' ); ?></label></th>
			<td><input type="text" name="bs_user_name" id="bs_user_name" value="" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="bs_password"><?php _e( 'Brickset Password', 'bs_api' ); ?></label></th>
			<td><input type="password" name="bs_password" id="bs_password" value="" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="bs_user_hash"><?php _e( 'Brickset Identifier', 'bs_api' ); ?></label></th>
			<td><input type="text" readonly name="bs_user_hash" id="bs_user_hash" value="<?php echo $user_hash; ?>" class="regular-text" /></td>
		</tr>
		</table>
		<?php
	}
	
	/** 
	 *	Takes the entered Brickset username and password and gets the userHash.
	 *
	 *	@author		Nate Jacobs
	 *	@date		2/6/13
	 *	@since		1.0
	 *
	 *	@param		int	
	 */
	public function set_brickset_user_hash( $user_id )
	{
		if ( !current_user_can( 'edit_user' ) )
			return false;
		
		if( !empty( $_POST['bs_user_name'] ) && !empty( $_POST['bs_password'] ) )
		{
			$response = $this->brickset_login( $user_id, sanitize_text_field( $_POST['bs_user_name'] ), $_POST['bs_password'] );
			
			if( is_wp_error( $response ) )
			{
				wp_die( $response->get_error_message(), 'brickset-login-error', array( 'back_link' => true ) );
			}
		}
	}
}

$brickset_user_profile = new BricksetAPIUserProfile;