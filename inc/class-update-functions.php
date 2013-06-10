<?php

class BricksetAPIUpdate extends BricksetAPIUtilities
{
	/** 
	 *	Get things started
	 *
	 *	@author		Nate Jacobs
	 *	@date		3/27/13
	 *	@since		1.1
	 */
	public function __construct()
	{
		
	}
	/** 
	 *	Update Owned Set
	 *
	 *	Takes a setID, a userHash, and a 1 or 0 to specify whether the user owns the set or not. 
	 *	If successful a 1 is returned.
	 *
	 *	@author		Nate Jacobs
	 *	@date		3/24/13
	 *	@since		1.1
	 *
	 *	@param		string
	 *	@param		int
	 *	@param		bool
	 *
	 *	@return		bool|object	true|WP_Error
	 */
	public function update_own( $set_id, $user_id, $own )
	{
		// Is it a valid user?
		if( is_wp_error( $validate_user = $this->validate_user( $user_id ) ) )	
			return $validate_user;
		
		// Is the string numeric?
		if( is_wp_error( $validate_set_id = $this->validate_set_id( $set_id ) ) )	
			return $validate_set_id;

		// Is it a valid boolean?
		if( is_wp_error( $validate_own = $this->validate_owned_wanted( $own ) ) )
			return $validate_own;
		
		$params = array( 'body' => array( 'userHash' => $this->get_user_hash( $user_id ), 'setID' => $set_id, 'own' => $own ) );
		
		$response = $this->remote_request( 'post', 'updateOwn', $params );
		
		$response = (array) simplexml_load_string( $response );

		if( is_wp_error( $response ) )
		{
			return $response;
		}
		elseif( $response[0] === '1' )
		{
			return true;
		}
	}
	
	/** 
	 *	Update Want
	 *
	 *	Takes a setID, a userHash, and a 1 or 0 to specify whether the user wants the set or not. 
	 *	If successful a 1 is returned.
	 *
	 *	@author		Nate Jacobs
	 *	@date		3/27/13
	 *	@since		1.1
	 *
	 *	@param		string
	 *	@param		int
	 *	@param		bool
	 *
	 *	@return		bool|object	true|WP_Error		
	 */
	public function update_want( $set_id, $user_id, $want )
	{
		// Is it a valid user?
		if( is_wp_error( $validate_user = $this->validate_user( $user_id ) ) )	
			return $validate_user;
		
		// Is the string numeric?
		if( is_wp_error( $validate_set_id = $this->validate_set_id( $set_id ) ) )	
			return $validate_set_id;
		
		// Is it a valid boolean?
		if( is_wp_error( $validate_want = $this->validate_owned_wanted( $want ) ) )
			return $validate_want;
		
		$params = array( 'body' => array( 'userHash' => $this->get_user_hash( $user_id ), 'setID' => $set_id, 'want' => $want ) );
		
		$response = $this->remote_request( 'post', 'updateWant', $params );
		
		$response = (array) simplexml_load_string( $response );

		if( is_wp_error( $response ) )
		{
			return $response;
		}
		elseif( $response[0] === '1' )
		{
			return true;
		}
	}

	/** 
	 *	Update Quantity
	 *
	 *	Takes a setID, a userHash, and a integer value to specify how many of the set the user owns. 
	 *	If successful a 1 is returned.
	 *
	 *	@author		Nate Jacobs
	 *	@date		3/27/13
	 *	@since		1.1
	 *
	 *	@param		string
	 *	@param		int
 	 *	@param		int
	 *
	 *	@return		bool|object	true|WP_Error		
	 */
	public function update_quantity( $set_id, $user_id, $quantity )
	{
		// Is it a valid user?
		if( is_wp_error( $validate_user = $this->validate_user( $user_id ) ) )	
			return $validate_user;
		
		// Is the string numeric?
		if( is_wp_error( $validate_set_id = $this->validate_set_id( $set_id ) ) )	
			return $validate_set_id;
		
		// Is a quantity value present?
		if( !isset( $quantity ) )
			return new WP_Error( 'no-quantity-specified', __( 'No quantity specified.', 'bs_api' ) );
		
		// Is quantity an integer?
		if( !is_int( $quantity ) )
			return new WP_Error( 'quantity-not-integer', __( 'The quantity is not an integer.', 'bs_api' ) );
		
		$params = array( 'body' => array( 'userHash' => $this->get_user_hash( $user_id ), 'setID' => $set_id, 'qty' => $quantity ) );
		
		$response = $this->remote_request( 'post', 'updateQtyOwned', $params );
		
		$response = (array) simplexml_load_string( $response );

		if( is_wp_error( $response ) )
		{
			return $response;
		}
		elseif( $response[0] === '1' )
		{
			return true;
		}
	}

	/** 
	 *	Update Notes
	 *
	 *	Takes a setID, a userHash, and a string, which will replace the user's notes for the set. 
	 *	If successful a 1 is returned.
	 *
	 *	@author		Nate Jacobs
	 *	@date		3/27/13
	 *	@since		1.1
	 *
	 *	@param		string
	 *	@param		int
	 *	@param		string
	 *
	 *	@return		bool|object	true|WP_Error		
	 */
	public function update_notes( $set_id, $user_id, $notes = '' )
	{
		// Is it a valid user?
		if( is_wp_error( $validate_user = $this->validate_user( $user_id ) ) )	
			return $validate_user;
		
		// Is the string numeric?
		if( is_wp_error( $validate_set_id = $this->validate_set_id( $set_id ) ) )	
			return $validate_set_id;
		
		$params = array( 'body' => array( 'userHash' => $this->get_user_hash( $user_id ), 'setID' => $set_id, 'notes' => sanitize_text_field( $notes ) ) );
		
		$response = $this->remote_request( 'post', 'updateUserNotes', $params );
		
		$response = (array) simplexml_load_string( $response );

		if( is_wp_error( $response ) )
		{
			return $response;
		}
		elseif( $response[0] === '1' )
		{
			return true;
		}
	}
	
	/** 
	 *	Update Minifig Quantity
	 *
	 *	Used to set the quantity of loose minifigs a user has. If successful a 1 is returned.
	 *
	 *	@author		Nate Jacobs
	 *	@date		3/27/13
	 *	@since		1.1
	 *
	 *	@param		string
	 *	@param		int
	 *	@param		int
	 *
	 *	@return		bool|object	true|WP_Error		
	 */
	public function update_minifig_quantity( $minifig_id, $user_id, $quantity )
	{
		// Is the minifig_id a string
		if( !is_string( $minifig_id ) )
			return new WP_Error( 'no-minifig-number', __( 'No minifig number specified.', 'bs_api' ) );
			
		// Is it a valid user?
		if( is_wp_error( $validate_user = $this->validate_user( $user_id ) ) )	
			return $validate_user;
		
		// Is a quantity value present?
		if( !isset( $quantity ) )
			return new WP_Error( 'no-quantity-specified', __( 'No quantity specified.', 'bs_api' ) );
		
		 // Is quantity an integer?
		if( !is_int( $quantity ) )
			return new WP_Error( 'quantity-not-integer', __( 'The quantity is not an integer.', 'bs_api' ) );
		
		$params = array( 'body' => array( 'userHash' => $this->get_user_hash( $user_id ), 'minifigNumber' => sanitize_text_field( $minifig_id ), 'qty' => $quantity ) );
		
		$response = $this->remote_request( 'post', 'updateMinifigQtyOwned', $params );
		
		$response = (array) simplexml_load_string( $response );

		if( is_wp_error( $response ) )
		{
			return $response;
		}
		elseif( $response[0] === '1' )
		{
			return true;
		}
	}
	
	/** 
	 *	Update Minifig Want
	 *
	 *	Used to set whether the user wants a minifig. Currently users can't simultaneously own and want the same minifig, so setting a fig as wanted will clear the quantity owned. 
	 *	If successful a 1 is returned.
	 *
	 *	@author		Nate Jacobs
	 *	@date		3/27/13
	 *	@since		1.1
	 *
	 *	@param		string
	 *	@param		int
	 *	@param		bool
	 *
	 *	@return		bool|object	true|WP_Error		
	 */
	public function update_minifig_want( $minifig_id, $user_id, $want )
	{
		// Is the minifig_id a string
		if( !is_string( $minifig_id ) )
			return new WP_Error( 'no-minifig-number', __( 'No minifig number specified.', 'bs_api' ) );
			
		// Is it a valid user?
		if( is_wp_error( $validate_user = $this->validate_user( $user_id ) ) )	
			return $validate_user;
		
		// Is it a valid boolean?
		if( is_wp_error( $validate_want = $this->validate_owned_wanted( $want ) ) )
			return $validate_want;
		
		$params = array( 'body' => array( 'userHash' => $this->get_user_hash( $user_id ), 'minifigNumber' => sanitize_text_field( $minifig_id ), 'want' => $want ) );
		
		$response = $this->remote_request( 'post', 'updateMinifigWanted', $params );
		
		$response = (array) simplexml_load_string( $response );

		if( is_wp_error( $response ) )
		{
			return $response;
		}
		elseif( $response[0] === '1' )
		{
			return true;
		}
	}
}