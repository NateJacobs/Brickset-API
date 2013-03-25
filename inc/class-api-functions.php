<?php

class BricksetAPIFunctions
{	
	/** 
	*	Construct
	*
	*	Start things off when class is instantiated
	*	Get the API Key from the options table
	*
	*	@author		Nate Jacobs
	*	@date		2/22/13
	*	@since		1.0
	*
	*	@param		null
	*/
	public function __construct()
	{
		$settings = (array) get_option( 'brickset-api-settings' );
		
		if( isset( $settings['api_key'] ) )
		{
			$this->api_key = $settings['api_key'];
		}
		else
		{
			$this->api_key = '';
		}
		
		add_filter ( 'http_request_timeout', array ( $this, 'http_request_timeout' ) );
	}

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
	protected function remote_request( $extra_url, $params = '' )
	{
		$api_url = 'http://www.brickset.com/webservices/brickset.asmx';	
//wp_die( $api_url.'/'.$extra_url.'?'.$params );
		$response = wp_remote_get( $api_url.'/'.$extra_url.'?'.$params );

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
		elseif( $extra_url != 'login' && 300 > strlen( $response_body ) )
		{
			return new WP_Error( 'brickset-no-data', __( 'Sorry, no sets were found for that query', 'bs_api' ) );
		}
		else
		{
			return $response_body;
		}
	}
	
	/** 
	*	HTTP Request Timeout
	*
	*	Sometimes requests take longer than 5 seconds
	*
	*	@author		Nate Jacobs
	*	@date		3/13/13
	*	@since		1.0
	*
	*	@param		int	$seconds
	*/
	function http_request_timeout ( $seconds ) 
	{
		return $seconds < 10 ? 15 : $seconds;
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
	*	Set Number Check
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
	protected function set_number_check( $set_number )
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
	private function build_bs_query( $args = '' )
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
		
		if( !isset( $this->api_key ) )
		{
			$settings = (array) get_option( 'brickset-api-settings' );
			$this->api_key = $settings['api_key'];
		}
		
		$params = build_query( 
			urlencode_deep( 
				array( 
					'apiKey' 	=> 	$this->api_key,
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
		return $params.'&userHash='.$this->get_user_hash( $user_id );
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
	private function validate_user( $user_id )
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
	private function validate_owned_wanted( $owned, $wanted )
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
	private function validate_theme_subtheme( $theme = '', $subtheme = '' )
	{
		if( !is_string( $theme ) || !is_string( $subtheme ) )
			return new WP_Error( 'invalid-string', __( 'The theme or subtheme requested is not a valid string.', 'bs_api' ) );
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
	private function validate_year( $years )
	{
		//if( !is_numeric( $year ) || strlen( $year )!=4 )
		//	return new WP_Error( 'invalid-year', __( 'The year requested is not a valid year.', 'bs_api' ) );
		
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
	public function brickset_login( $user_id, $username, $password )
	{
		// Which user is this?
		$user = get_userdata( $user_id );
		
		// Build the parameters
		$params = 'u='.$username.'&p='.$password;
		
		// Send it off
		$response = $this->remote_request( 'login', $params );
		
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
	
	/** 
	 *	Get a list of all themes
	 *
	 *	Brickset returns the themeData response.
	 *	See webservice-definition.json for all the fields returned.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 *	@updated	1.0
	 *
	 *	@return		object	$themes
	 */
	public function get_themes()
	{
		$transient = 'bs_theme_list';
	
		// Have we stored a transient?
		if( false === get_transient( $transient ) )
		{
			$response = $this->remote_request( 'listThemes' );
			
			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( $transient, $response, DAY_IN_SECONDS );
		}
		
		// Return a SimpleXML object
		return new SimpleXMLElement( get_transient( $transient ) );		
		
	}

	/** 
	 *	Get a list of all subthemes for a given theme
	 *
	 *	Brickset returns the subthemeData response.
	 *	See webservice-definition.json for all the fields returned.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 *	@updated	1.0
	 *
	 *	@param		string	$theme
	 *
	 *	@return		object	$subthemes
	 */
	public function get_subthemes( $theme )
	{
		// Is it a valid string	
		if( is_wp_error( $validate_theme = $this->validate_theme_subtheme( $theme ) ) )	
			return $validate_theme;
		
		$theme = sanitize_text_field( strtolower( $theme ) );
		$transient_theme = str_replace( " ", "", $theme );
		
		$transient = 'bs_'.$transient_theme.'_subthemes';
		
		// Have we stored a transient?
		if( false === get_transient( $transient ) )
		{
			$args = array( 'theme' => $theme );
			
			$params = $this->build_bs_query( $args );
			$response = $this->remote_request( 'listSubthemes', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( $transient, $response, DAY_IN_SECONDS );
		}
		
		// Get it and return a SimpleXML object
		return new SimpleXMLElement( get_transient( $transient ) );
	}
	
	/** 
	 *	Get a list of years a theme was available
	 *
	 *	Brickset returns the yearData response.
	 *	See webservice-definition.json for all the fields returned.	
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 *	@updated	1.0
	 *
	 *	@param		string	$theme
	 *
	 *	@return		object	$years
	 */
	public function get_theme_years( $theme )
	{	
		// Is it a valid string	
		if( is_wp_error( $validate_theme = $this->validate_theme_subtheme( $theme ) ) )	
			return $validate_theme;
		
		$theme = sanitize_text_field( strtolower( $theme ) );
		$transient_theme = str_replace( " ", "", $theme );
		
		$transient = 'bs_'.$transient_theme.'_years';
		
		// Have we stored a transient?
		if( false === get_transient( $transient ) )
		{
			$args = array( 'theme' => $theme );

			$params = $this->build_bs_query( $args );
			$response = $this->remote_request( 'listYears', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( $transient, $response, DAY_IN_SECONDS );
		}
		
		// Get it and return a SimpleXML object
		return new SimpleXMLElement( get_transient( $transient ) );
		
	}
	
	/** 
	 *	Get a list of the most searched for terms
	 *
	 *	Brickset returns the searchData response
	 *	See webservice-definition.json for all the fields returned.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 *	@updated	1.0
	 *
	 *	@return		object	$searches
	 */
	public function get_popular_searches()
	{
		$transient = 'bs_popular_searches';
		
		// Have we stored a transient?
		if( false === get_transient( $transient ) )
		{
			$response = $this->remote_request( 'popularSearches' );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( $transient, $response, HOUR_IN_SECONDS );
		}
		
		// Get it and return a SimpleXML object
		return new SimpleXMLElement( get_transient( $transient ) );
	}
	
	/** 
	 *	Get all sets updated since a given date
	 *
 	 *	Brickset returns the setData response.
	 *	See webservice-definition.json for all the fields returned.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 *	@updated	1.0
	 *
	 *	@param		string	$date (use format of 'mm/dd/yyyy')
	 *
	 *	@return		object	$updated
	 */
	public function get_updated_since( $date )
	{
		$exploded_date = explode( '/', $date );

		// Is this a date in the correct format?
		if( false === checkdate( $exploded_date[0], $exploded_date[1], $exploded_date[2] ) )
			return new WP_Error( 'not-a-date-format', __( 'The date is not formatted correctly.', 'bs_api' ) );
		
		// Is it a valid year	
		if( is_wp_error( $validate_year = $this->validate_year( $exploded_date[2] ) ) )	
			return $validate_year;
		
		$transient_date = str_replace( '/', '', $date );
		
		$transient = 'bs_updated_since_'.$transient_date;
		
		// Have we stored a transient?
		if( false === get_transient( $transient ) )
		{
			$params = 'apiKey='.$this->api_key.'&sinceDate='.$date;
			$response = $this->remote_request( 'updatedSince', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( $transient, $response, DAY_IN_SECONDS );
		}
		
		// Get it and return a SimpleXML object
		return new SimpleXMLElement( get_transient( $transient ) );
	}
	
	/** 
	 *	Get Set Info by Number
	 *
	 *	Pass a set number and get all the information about that set.
	 *	Returns the setData response.
	 *	See webservice-definition.json for all the fields returned.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 *	@updated	1.0
	 *
	 *	@param		string	$number (set number)
	 *	@param		int 	$user_id (user_id)
	 *	@param		bool	$wanted (true = return wanted)
	 *	@param		bool	$owned (true = return owned)
	 *
	 *	@return		object 	$setData
	 */
	public function get_by_number( $number = '', $args = '' )
	{
		$defaults = array(
			'owned' 	=> false,
			'wanted' 	=> false,
			'user_id' 	=> ''
		);
		
		// Is there a number?
		if( empty( $number ) )
			return new WP_Error( 'no-set-number', __( 'No set number requested.', 'bs_api' ) );
		
		// Check on the number for variants
		if( is_wp_error( $sets = $this->set_number_check( $number ) ) )
			return $sets;
		
		$args['set_number'] = $sets;
		
		$args = wp_parse_args( $args, $defaults );

		return $this->search( $args );
	}
	
	/** 
	 *	Get Wanted Sets
	 *
	 *	Get all the wanted sets by the specified user.
	 *	Returns the setData response.
	 *	See webservice-definition.json for all the fields returned.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.2
	 *	@updated	1.0
	 *
	 *	@param		int 	$user_id (user_id)
	 *
	 *	@return		object 	$setData
	 */
	public function get_wanted( $user_id = '' )
	{
		// Is there a user?
		if( empty( $user_id ) )
			return new WP_Error( 'no-user-specified', __( 'No user specified.', 'bs_api' ) );
		
		$args['user_id'] = $user_id;
		$args['wanted'] = true;
		
		return $this->search( $args );
	}
	
	/** 
	 *	Get Owned Sets
	 *
	 *	Get all the sets owned by the specified user.
	 *	Returns the setData resposne.
	 *	See webservice-definition.json for all the fields returned.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.3
	 *	@updated	1.0
	 *
	 *	@param		int 	$user_id (user_id)
	 *
	 *	@return		object 	$setData
	 */
	public function get_owned( $user_id = '' )
	{
		// Is there a user?
		if( empty( $user_id ) )
			return new WP_Error( 'no-user-specified', __( 'No user specified.', 'bs_api' ) );
		
		$args['user_id'] = $user_id;
		$args['owned'] = true;
		
		return $this->search( $args );
	}
	
	
	/** 
	 *	Get Set Info by Theme
	 *
	 *	Pass a theme and get all the information about the sets in that theme.
	 *	Returns the setData response.
	 *	See webservice-definition.json for all the fields returned.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 *	@updated	1.0
	 *
	 *	@param		string	$theme
	 *	@param		array	$args (user_id, owned, wanted)
	 *	@param		int 	$user_id
	 *	@param		bool	$owned
	 *	@param		bool	$wanted
	 *
	 *	@return		object 	$setData
	 */
	public function get_by_theme( $theme = '', $args = '' )
	{
		$defaults = array(
			'owned' 	=> false,
			'wanted' 	=> false,
			'user_id' 	=> ''
		);
		
		// Is there a theme?
		if( empty( $theme ) )
			return new WP_Error( 'no-theme', __( 'No theme requested.', 'bs_api' ) );
		
		$args['theme'] = $theme;
		
		$args = wp_parse_args( $args, $defaults );

		return $this->search( $args );
	}
	
	/** 
	 *	Get Set Info by Subtheme
	 *
	 *	Pass a subtheme and get all the information about the sets in that subtheme.
	 *	Returns the setData response.
	 *	See webservice-definition.json for all the fields returned.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 *	@updated	1.0
	 *
	 *	@param		int		$subtheme
	 *	@param		array	$args (user_id, owned, wanted)
	 *	@param		int 	$user_id
	 *	@param		int		$owned
	 *	@param		int		$wanted
	 *
	 *	@return		object	$setData
	 */
	public function get_by_subtheme( $subtheme = '', $args = '' )
	{
		$defaults = array(
			'owned' 	=> false,
			'wanted' 	=> false,
			'user_id' 	=> ''
		);
		
		// Is there a subtheme?
		if( empty( $subtheme ) )
			return new WP_Error( 'no-subtheme', __( 'No subtheme requested.', 'bs_api' ) );
		
		$args['subtheme'] = $subtheme;
		
		$args = wp_parse_args( $args, $defaults );

		return $this->search( $args );
	}
	
	/** 
	 *	Get Set Info by Year
	 *
	 *	Pass a year and get all the information about the sets produced that year.
	 *	Returns the setData response
	 *	See webservice-definition.json for all the fields returned.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 *	@updated	1.0
	 *
	 *	@param		int	$year
	 *	@param		array	$args (user_id, owned, wanted)
	 *	@param		int 	$user_id
	 *	@param		int		$owned
	 *	@param		int		$wanted
	 *
	 *	@return		object 	$setData
	 */
	public function get_by_year( $year = '', $args = '' )
	{
		$defaults = array(
			'owned' 	=> false,
			'wanted' 	=> false,
			'user_id' 	=> ''
		);
		
		// Is there a year?
		if( empty( $year ) )
			return new WP_Error( 'no-year', __( 'No year requested.', 'bs_api' ) );
		
		$args['year'] = $year;
		
		$args = wp_parse_args( $args, $defaults );

		return $this->search( $args );	
	}
	
	/** 
	 *	Search the Brickset DB with a given set of criteria
	 *
	 *	Provides method for searching Brickset's set database
	 *	Returns the setData response
	 *	See webservice-definition.json for all the fields returned.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 */
	public function search( $args = '' )
	{
		$defaults = array(
			'theme' 	=> '',
			'subtheme' 	=> '',
			'set_number'=> '',
			'year' 		=> '',
			'owned' 	=> false,
			'wanted' 	=> false,
			'query' 	=> '',
			'user_id' 	=> ''
		);
		
		// Is there any criteria to search?
		if( empty( $args ) )
			return new WP_Error( 'no-search-criteria', __( 'No search criteria specified.', 'bs_api' ) );
		
		$args = wp_parse_args( $args, $defaults );
		
		// Is it a valid year	
		if( !empty( $args['year'] ) )
		{
			if( is_wp_error( $validate_year = $this->validate_year( $args['year'] ) ) )	
				return $validate_year;
		}
		
		// Is it a valid user_id?
		if( !empty( $args['user_id'] ) )
		{
			if( is_wp_error( $validate_user = $this->validate_user( $args['user_id'] ) ) )
				return $validate_user;
		}
		
		// Is it a valid string
		if( !empty( $args['theme'] ) || !empty( $args['subtheme'] ) )
		{	
			if( is_wp_error( $validate_string = $this->validate_theme_subtheme( $args['theme'], $args['subtheme'] ) ) )	
				return $validate_string;
		}
		
		// Is it a valid string?
		if( !empty( $args['query'] ) )
		{
			if( !is_string( $args['query'] ) )
				return new WP_Error( 'not-valid-query', __( 'The query requested is not a valid string.', 'bs_api' ) );
		}
		
		// Was a true or false passed for owned and wanted?
		if( is_wp_error( $validate_owned_wanted = $this->validate_owned_wanted( $args['owned'], $args['wanted'] ) ) )
			return $validate_owned_wanted;
		
		if( is_wp_error( $sets = $this->set_number_check( $args['set_number'] ) ) )
			return $sets;
			
		$args['set_number'] = $sets;

		$args['theme'] = strtolower( $args['theme'] );
		$args['subtheme'] = strtolower( $args['subtheme'] );
		$args['query'] = strtolower( $args['query'] );

		$transient_sets = str_replace( array( ',', '-' ), '', $args['set_number'] );
		$transient_year = str_replace( ",", '', $args['year'] );
		$transient_theme = str_replace( array( ',', '-', " " ), '', $args['theme'] );
		$transient_subtheme = str_replace( array( ',', '-', " " ), '', $args['subtheme'] );
		$transient_query = str_replace( array( ',', '-', " " ), '', $args['query'] );

		$transient = 'bs_search_'.$transient_theme.$transient_subtheme.$transient_sets.$transient_year.$transient_query.'_user-'.$args['user_id'].'_want-'.$args['wanted'].'_own-'.$args['owned'];

		if( false === get_transient( $transient ) )
		{
			$params = $this->build_bs_query( $args );
			$response = $this->remote_request( 'search', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( $transient, $response, DAY_IN_SECONDS );
		}
		
		return new SimpleXMLElement( get_transient( $transient ) );
	}
	
	/** 
	*	List Instructions
	*
	*	Retrieve instructions link by Brickset set ID
	*
	*	@author		Nate Jacobs
	*	@date		3/22/13
	*	@since		1.1
	*
	*	@param		string	$set_id
	*
	*	@return		object 	$instructionData
	*/
	public function list_instructions( $set_id = '' )
	{
		// Is there a setID?
		if( empty( $set_id ) )
			return new WP_Error( 'no-set-id', __( 'No set ID requested.', 'bs_api' ) );
		
		if( false === is_numeric( $set_id ) )
			return new WP_Error( 'set-id-not-valid', __( 'The set ID requested is not numeric.', 'bs_api' ) );
		
		$transient = 'bs_instructions'.$set_id;
		
		// Have we stored a transient?
		if( false === get_transient( $transient ) )
		{
			$params = 'setID='.$set_id;
			$response = $this->remote_request( 'listInstructions', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( $transient, $response, DAY_IN_SECONDS );
		}
		
		// Get it and return a SimpleXML object
		return new SimpleXMLElement( get_transient( $transient ) );
	}
	
	/** 
	*	Get by Set ID
	*
	*	Retrieves a single set by Brickset internal set ID
	*
	*	@author		Nate Jacobs
	*	@date		3/24/13
	*	@since		1.1
	*
	*	@param		string	$set_id
	*	
	*	@return		object 	$setData
	*/
	public function get_by_set_id( $set_id )
	{
		// Is there a setID?
		if( empty( $set_id ) )
			return new WP_Error( 'no-set-id', __( 'No set ID requested.', 'bs_api' ) );
		
		if( false === is_numeric( $set_id ) )
			return new WP_Error( 'set-id-not-valid', __( 'The set ID requested is not numeric.', 'bs_api' ) );
			
		$transient = 'bs_set_id_search_'.$set_id;
		
		// Have we stored a transient?
		if( false === get_transient( $transient ) )
		{
			$params = 'setID='.$set_id;
			$response = $this->remote_request( 'searchBySetID', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( $transient, $response, DAY_IN_SECONDS );
		}
		
		// Get it and return a SimpleXML object
		return new SimpleXMLElement( get_transient( $transient ) );
	}
}