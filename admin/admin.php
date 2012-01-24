<?php

// 'brickset_apikey'

class BricksetAPIAdmin extends BricksetAPIFunctions
{
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
}
$brickset_adimin = new BricksetAPIAdmin();