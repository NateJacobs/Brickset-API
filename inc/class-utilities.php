<?php

class BrickSetAPIUtilities
{
	public function get_api_key()
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
	public function get_user_hash( $user_id )
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
	public function build_bs_query( $args = '' )
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
	public function validate_set_number( $set_number )
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
	public function validate_user( $user_id )
	{
		// Is it an integer?
		if( !is_int( $user_id ) )
		{
			return new WP_Error( 'no-user-specified', __( 'No user specified.', 'bs_api' ) );
		}
		// Does the user_id specified exist on this site?
		elseif( !get_user_by( 'id', $user_id ) )
		{
			return new WP_Error( 'not-valid-user', __( 'The user ID passed is not a valid user.', 'bs_api' ) );
		}
		else
		{
			return true;
		}
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
	public function validate_owned_wanted( $owned = '', $wanted = '' )
	{
		if( !is_bool( $owned ) || !is_bool( $wanted ) )
			return new WP_Error( 'no-boolean', __( 'Owned or wanted is not a true or false value.', 'bs_api' ) );
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
	public function validate_theme_subtheme( $theme = '', $subtheme = '' )
	{
		if( !is_string( $theme ) || !is_string( $subtheme ) )
			return new WP_Error( 'invalid-string', __( 'The theme or subtheme requested is not a valid string.', 'bs_api' ) );
	}
	
	/** 
	*	Validate Set ID
	*
	*	Ensures the string passed is numeric
	*
	*	@author		Nate Jacobs
	*	@date		3/24/13
	*	@since		1.1
	*
	*	@param		string	$set_id
	*
	*	@return		object	WP_Error
	*/
	private function validate_set_id( $set_id )
	{
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
	public function validate_year( $years )
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