<?php

/** 
*	Brickset API User Profile
*
*	Display necessary fields on the user profile page to allow a user to authenticate themselves with Brickset.
*	Take the user hash and save it in the usermeta table.
*
*	@author		Nate Jacobs
*	@date		2/2/13
*	@since		1.0
*/
class BricksetAPIUserProfile extends BricksetAPIFunctions
{
	/** 
	*	Construct Method
	*
	*	
	*
	*	@author		Nate Jacobs
	*	@date		2/2/13
	*	@since		1.0
	*
	*	@param		
	*/
	public function __construct()
	{
		add_action( 'show_user_profile', array( __CLASS__, 'add_user_profile_fields' ) );
		add_action( 'edit_user_profile', array( __CLASS__, 'add_user_profile_fields' ) );
		add_action( 'personal_options_update', array( __CLASS__, 'save_user_profile_fields' ) );
		add_action( 'edit_user_profile_update', array( __CLASS__, 'save_user_profile_fields' ) );
	}

	/** 
	 *	Login Service Method
	 *
	 *	Authenticates a user with Brickset and returns a hash.
	 *	The hash is then stored as a meta value with the key of 'brickset_user_hash'
	 *	in the *_usersmeta table.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 *
	 *	@param	int 	$user_id
	 *	@param	string 	$username
	 *	@param	string	$password
	 */
	private function brickset_login( $user_id, $username, $password )
	{
		$user = get_userdata( $user_id );
		
		$params = 'u='.$username.'&p='.$password;
	
		parent::remote_request( 'login', $params );
		$user_hash = $this->results;
		
		try
		{
			if ( $this->httpcode != 200 )
				throw new Exception ( $this->error_msg );
			update_user_meta( $user->ID, 'brickset_user_hash',  $user_hash );
		}
		catch ( Exception $e ) 
		{
			echo $e->getMessage();
		}
	}
}