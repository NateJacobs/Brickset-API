<?php

class BricksetAPIUtilities
{
	/** 
	 *	Remote Request
	 *
	 *	Send the api request to Brickset. Returns an XML formatted response.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 *	@updated	1.0
	 *
	 *	@param		string	$extra_url (url needed after base url)
	 *	@param		string	$params (query parameters)
	 *
	 *	@return		object	WP_Error
	 *	@return		array	$response_body
	 */
	protected function remote_request( $type, $extra_url, $params = '' )
	{
		$api_url = 'http://www.brickset.com/webservices/brickset.asmx';	

		if( 'get' == $type )
		{
//wp_die( $api_url.'/'.$extra_url.'?'.$params );
			$response = wp_remote_get( $api_url.'/'.$extra_url.'?'.$params );
		}
		elseif( 'post' == $type )
		{
			$response = wp_remote_post( $api_url.'/'.$extra_url, $params );
		}
		else
		{
			return new WP_Error( 'no-type-specified', __( 'Specify a type of request: get or post', 'bs_api') );
		}
		
		// Did the HTTP request fail?
		if( is_wp_error( $response ) )
			return $response;
		
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_message = wp_remote_retrieve_response_message( $response );
		$response_body = wp_remote_retrieve_body( $response );

		if( 200 != $response_code && ! empty( $response_message ) )
		{
			return new WP_Error( $response_code, __( 'Don\'t Panic! Something went wrong and Brickset didn\'t reply.', 'bs_api' ) );
		}
		elseif( 200 != $response_code )
		{
			return new WP_Error( $response_code, __( 'Unknown error occurred', 'bs_api') );
		}
		elseif( $extra_url != 'login' && 300 > strlen( $response_body ) && $type == 'get' )
		{
				return new WP_Error( 'brickset-no-data', __( 'Sorry, no sets were found for that query', 'bs_api' ) );
		}
		else
		{
			return $response_body;
		}
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
	 *	@updated	1.0
	 *
	 *	@param	int 	$user_id
	 *	@param	string 	$username
	 *	@param	string	$password
	 *
	 *	@return	array	$response (if there is an error, a WP_Error array is returned)
	 */
	protected function brickset_login( $user_id, $username, $password )
	{
		// Which user is this?
		$user = get_userdata( $user_id );
		
		// Build the parameters
		$params = 'u='.$username.'&p='.$password;
		
		// Send it off
		$response = $this->remote_request( 'get', 'login', $params );
		
		if( is_wp_error( $response ) )
		{
			return $response;
		}
		else
		{
			$user_hash = new SimpleXMLElement( $response );

			update_user_meta( $user->ID, 'brickset_user_hash',  (string) $user_hash[0] );
		}
	}

	protected function get_api_key()
	{
		$settings = (array) get_option( 'brickset-api-settings' );
		
		return (isset( $settings['api_key'] ) ? $settings['api_key'] : '');
	}
	/** 
	*	Get UserHash
	*
	*	Returns the Brickset userHash from user_meta
	*
	*	@author		Nate Jacobs
	*	@date		2/9/13
	*	@since		1.0
	*
	*	@param		int	$user_id
	*
	*	@return		string	$user_hash
	*/
	protected function get_user_hash( $user_id )
	{
		return get_user_meta( $user_id, 'brickset_user_hash', true );
	}
		
	/** 
	*	Build Brickset Query
	*
	*	Takes an array of search criteria and returns a urlencoded query string
	*
	*	@author		Nate Jacobs
	*	@date		2/22/13
	*	@since		1.0
	*
	*	@param		array	$args
	*
	*	@return		array	$params
	*/
	protected function build_bs_query( $args = '' )
	{
		$defaults = array(
			'user_id'	=>	'',
			'query'		=>	'',
			'theme'		=>	'',
			'subtheme'	=>	'',
			'set_number'=>	'',
			'year'		=>	'',
			'owned'		=>	'',
			'wanted'	=>	''
		);
				
		$args = wp_parse_args( $args, $defaults );
		
		extract( $args, EXTR_SKIP );
		
		$params = build_query( 
			urlencode_deep( 
				array( 
					'apiKey' 	=> 	self::get_api_key(),
					'query'		=>	$query,
					'theme'		=>	$theme,
					'subtheme'	=>	$subtheme,
					'setNumber'	=>	$set_number,
					'year'		=>	$year,
					'owned'		=>	$owned,
					'wanted'	=>	$wanted
				) 
			)
		);
		
		$params = str_replace( '%2C', ',', $params );
		return $params.'&userHash='.self::get_user_hash( $user_id );
	}
	
	/** 
	*	Validate Set Number
	*
	*	Checks if the set number passed has a variant, if not, one is added
	*	The search query requires sets in the format of 9999-9
	*
	*	@author		Nate Jacobs
	*	@date		2/9/13
	*	@since		1.0
	*
	*	@param		string	$set_number
	*
	*	@return		string	$set_number
	*/
	protected function validate_set_number( $set_number )
	{
		// If no set is passed, get out
		if( empty( $set_number ) )
			return '';
		
		// Get set numbers into an array
		$set_numbers = explode( ',', $set_number );

		// Holding container
		$sets = '';
		
		foreach( $set_numbers as $set )
		{
			$number_check = explode( '-', $set );
			
			if( !is_numeric( $number_check[0] ) )
				return new WP_Error( 'invalid-set-number', __( 'One of the sets requested is not a number.', 'bs_api' ) );
			
			// No variant present, add the -1	
			if( empty( $number_check[1] ) )
			{
				$sets .= $number_check[0].'-1,';
			}
			else
			{
				$sets .= $set.',';
			}
		}
		// Get rid of the space between commas
		return substr(str_replace(' ','',$sets), 0, -1);
	}
		
	/** 
	*	Validate User ID
	*
	*	Takes a user ID and determines if it is an integer and is a valid user in the site
	*
	*	@author		Nate Jacobs
	*	@date		2/22/13
	*	@since		1.0
	*
	*	@param		int	$user_id
	*
	*	@return		object	WP_Error (if not a user or an int)
	*	@return		bool	true (if a valid user and an int)
	*/
	protected function validate_user( $user_id )
	{
		// Is there a user?
		if( empty( $user_id ) )
			return new WP_Error( 'no-user-specified', __( 'No user specified.', 'bs_api' ) );
			
		// Is it an integer?
		if( !is_int( $user_id ) )
			return new WP_Error( 'no-user-specified', __( 'No user specified.', 'bs_api' ) );
		
		// Does the user_id specified exist on this site?
		if( !get_user_by( 'id', $user_id ) )
			return new WP_Error( 'not-valid-user', __( 'The user ID passed is not a valid user.', 'bs_api' ) );
		
		$user_hash = $this->get_user_hash( $user_id );
			
		if( empty( $user_hash ) )
			return new WP_Error( 'no-user-hash', __( 'The user ID passed does not have a Brickset API identifier on file.', 'bs_api' ) );	
	}
	
	/** 
	*	Validate Owned and Wanted
	*
	*	Determines if the owned and wanted passed values are true or false
	*
	*	@author		Nate Jacobs
	*	@date		2/22/13
	*	@since		1.0
	*
	*	@param		bool	$owned
	*	@param		bool	$wanted
	*
	*	@return		object	WP_Error
	*/
	protected function validate_owned_wanted( $owned = false, $wanted = false )
	{
		if( !is_bool( $owned ) )
			return new WP_Error( 'no-boolean', __( 'Owned is not a true or false value.', 'bs_api' ) );
			
		if( !is_bool( $wanted ) )
			return new WP_Error( 'no-boolean', __( 'Wanted is not a true or false value.', 'bs_api' ) );
	}
	
	/** 
	*	Validate Theme or Subtheme
	*
	*	Checks and ensures the theme or subtheme passed is a valid string
	*
	*	@author		Nate Jacobs
	*	@date		2/22/13
	*	@since		1.0
	*
	*	@param		string	$string
	*
	*	@return		object	WP_Error
	*/
	protected function validate_theme_subtheme( $theme = '', $subtheme = '' )
	{
		if( !is_string( $theme ) || !is_string( $subtheme ) )
			return new WP_Error( 'invalid-string', __( 'The theme or subtheme requested is not a valid string.', 'bs_api' ) );
	}
	
	/** 
	*	Validate Set ID
	*
	*	Ensures the string passed is numeric and is not empty
	*
	*	@author		Nate Jacobs
	*	@date		3/24/13
	*	@since		1.1
	*
	*	@param		string	$set_id
	*
	*	@return		object	WP_Error
	*/
	protected function validate_set_id( $set_id )
	{
		// Is there a setID?
		if( empty( $set_id ) )
			return new WP_Error( 'no-set-id', __( 'No set ID requested.', 'bs_api' ) );
		
		// Is the string numeric	
		if( false === is_numeric( $set_id ) )
			return new WP_Error( 'set-id-not-valid', __( 'The set ID requested is not numeric.', 'bs_api' ) );
	}
	
	/** 
	*	Validate Year
	*
	*	Checks if the year passed as a string is a valid year
	*
	*	@author		Nate Jacobs
	*	@date		2/22/13
	*	@since		1.0
	*
	*	@param		string|int	$year
	*/
	protected function validate_year( $years )
	{
		// Get set numbers into an array
		$years = explode( ',', $years );

		// Holding container
		$total_years = '';
		
		foreach( $years as $year )
		{
			if( !is_numeric( $year ) || strlen( $year )!=4 )
				return new WP_Error( 'invalid-year', __( 'The year requested is not a valid year.', 'bs_api' ) );

			// Check if year is between 1950 and current year +1
			if( $year < '1950' || $year > date( 'Y', strtotime( '+1 year' ) ) )
				return new WP_Error( 'year-out-range', __( 'The year is not in the accepted range, 1950 to year +1.', 'bs_api' ) );
			
			$total_years .= $year.',';
		}
		// Get rid of the space between commas
		return substr(str_replace(' ','',$total_years), 0, -1);	
	}
}