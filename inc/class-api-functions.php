<?php

class BricksetAPIFunctions
{	
	/** 
	*	Construct
	*
	*	
	*
	*	@author		Nate Jacobs
	*	@date		2/22/13
	*	@since		1.0
	*
	*	@param		
	*/
	public function __construct()
	{
		$settings = (array) get_option( 'brickset-api-settings' );
		$this->api_key = $settings['api_key'];
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
//wp_die( $params );
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
	private function get_user_hash( $user_id )
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
	*	
	*
	*	@author		Nate Jacobs
	*	@date		2/22/13
	*	@since		1.0
	*
	*	@param		array	$args
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
		
		$params = build_query( 
			urlencode_deep( 
				array( 
					'apiKey' 	=> $this->api_key,
					'userHash'	=> $this->get_user_hash( $user_id ),
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
		
		return $params;
	}
	
	/** 
	*	Validate User ID
	*
	*	
	*
	*	@author		Nate Jacobs
	*	@date		2/22/13
	*	@since		1.0
	*
	*	@param		
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
	*	Check Owned and Wanted
	*
	*	
	*
	*	@author		Nate Jacobs
	*	@date		2/22/13
	*	@since		1.0
	*
	*	@param		
	*/
	private function validate_owned_wanted( $owned, $wanted )
	{
		if( !is_bool( $owned ) || !is_bool( $wanted ) )
			return new WP_Error( 'no-boolean', __( 'Owned or wanted is not a true or false value.', 'bs_api' ) );
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
		// Have we stored a transient?
		if( false === get_transient( 'bs_theme_list' ) )
		{
			$response = $this->remote_request( 'listThemes' );
			
			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( 'bs_theme_list', $response, DAY_IN_SECONDS );
		}
		
		// Return a SimpleXML object
		return new SimpleXMLElement( get_transient( 'bs_theme_list' ) );		
		
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
		// Check if it is a string
		if( false === is_string( $theme ) )
			return new WP_Error( 'not-a-string', __( 'The theme entered is not a valid string.', 'bs_api' ) );

		// Lower it
		$theme = sanitize_text_field( strtolower( $theme ) );
		
		// Have we stored a transient?
		if( false === get_transient( 'bs_'.$theme.'_subthemes' ) )
		{
			$params = 'theme='.$theme;
			$response = $this->remote_request( 'listSubthemes', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( 'bs_'.$theme.'_subthemes', $response, DAY_IN_SECONDS );
		}
		
		// Get it and return a SimpleXML object
		return new SimpleXMLElement( get_transient( 'bs_'.$theme.'_subthemes' ) );
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
		// Check if it is a string
		if( false === is_string( $theme ) )
			return new WP_Error( 'not-a-string', __( 'The theme entered is not a valid string.', 'bs_api' ) );
	
		// Lower the string
		$theme = urlencode( sanitize_text_field( strtolower( $theme ) ) );
		
		// Have we stored a transient?
		if( false === get_transient( 'bs_'.$theme.'_years' ) )
		{
			$params = 'theme='.$theme;
			$response = $this->remote_request( 'listYears', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( 'bs_'.$theme.'_years', $response, DAY_IN_SECONDS );
		}
		
		// Get it and return a SimpleXML object
		return new SimpleXMLElement( get_transient( 'bs_'.$theme.'_years' ) );
		
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
		// Have we stored a transient?
		if( false === get_transient( 'bs_popular_searches' ) )
		{
			$response = $this->remote_request( 'popularSearches' );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( 'bs_popular_searches', $response, HOUR_IN_SECONDS );
		}
		
		// Get it and return a SimpleXML object
		return new SimpleXMLElement( get_transient( 'bs_popular_searches' ) );
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
		
		$api_key = $this->get_api_key();
		
		$transient_date = str_replace( '/', '', $date );
		
		// Have we stored a transient?
		if( false === get_transient( 'bs_updated_since_'.$transient_date ) )
		{
			$params = 'apiKey='.$api_key.'&sinceDate='.$date;
			$response = $this->remote_request( 'updatedSince', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( 'bs_updated_since_'.$transient_date, $response, DAY_IN_SECONDS );
		}
		
		// Get it and return a SimpleXML object
		return new SimpleXMLElement( get_transient( 'bs_updated_since_'.$transient_date ) );
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
		
		$args = wp_parse_args( $args, $defaults );
		
		extract( $args, EXTR_SKIP );
			
		// Get the stuff we need	
		$user_hash = $this->get_user_hash( $user_id );
		$api_key = $this->get_api_key();
		
		// Check on the number for variants
		$sets = $this->set_number_check( $number );
		
		// Get rid of all punctuation to store in db as part of transient name
		$transient_sets = str_replace( array( ',', '-' ), '', $sets );
		
		// Have we stored a transient?
		if( false === get_transient( 'bs_'.$transient_sets.$user_id.$wanted.$owned ) )
		{
			$params = 'apiKey='.$api_key.'&userHash='.$user_hash.'&query=&theme=&subtheme=&setNumber='.$sets.'&year=&owned='.$owned.'&wanted='.$wanted;
			$response = $this->remote_request( 'search', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( 'bs_'.$transient_sets.$user_id.$wanted.$owned, $response, DAY_IN_SECONDS );
		}
		
		// Get it and return a SimpleXML object
		return new SimpleXMLElement( get_transient( 'bs_'.$transient_sets.$user_id.$wanted.$owned ) );
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
		// Is it an integer?
		if( !is_int( $user_id ) )
			return new WP_Error( 'no-user-specified', __( 'No user specified.', 'bs_api' ) );
	
		// Does the user_id specified exist on this site?
		if( !get_user_by( 'id', $user_id ) )
			return new WP_Error( 'not-valid-user', __( 'The user ID passed is not a valid user.', 'bs_api' ) );
		
		// Get the stuff we need
		$user_hash = $this->get_user_hash( $user_id );
		$api_key = $this->get_api_key();
		
		// Have we stored a transient?
		if( false === get_transient( 'bs_wanted'.$user_id ) )
		{
			$params = 'apiKey='.$api_key.'&userHash='.$user_hash.'&query=&theme=&subtheme=&setNumber=&year=&owned=&wanted=1';
			$response = $this->remote_request( 'search', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( 'bs_wanted'.$user_id, $response, DAY_IN_SECONDS );
		}
		
		// Get it and return a SimpleXML object
		return new SimpleXMLElement( get_transient( 'bs_wanted'.$user_id ) );
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
		// Is it an integer?
		if( !is_int( $user_id ) )
			return new WP_Error( 'no-user-specified', __( 'No user specified.', 'bs_api' ) );
	
		// Does the user_id specified exist on this site?
		if( !get_user_by( 'id', $user_id ) )
			return new WP_Error( 'not-valid-user', __( 'The user ID passed is not a valid user.', 'bs_api' ) );	
	
		// Get the stuff we need
		$user_hash = $this->get_user_hash( $user_id );
		$api_key = $this->get_api_key();
		
		// Have we stored a transient?
		if( false === get_transient( 'bs_owned'.$user_id ) )
		{
			$params = 'apiKey='.$api_key.'&userHash='.$user_hash.'&query=&theme=&subtheme=&setNumber=&year=&owned=1&wanted=';
			$response = $this->remote_request( 'search', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( 'bs_owned'.$user_id, $response, DAY_IN_SECONDS );
		}
		
		// Get it and return a SimpleXML object
		return new SimpleXMLElement( get_transient( 'bs_owned'.$user_id ) );
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
		
		// Is there a theme and is it a string?
		if( empty( $theme ) || !is_string( $theme ) )
			return new WP_Error( 'no-theme', __( 'No theme requested.', 'bs_api' ) );
		
		$args = wp_parse_args( $args, $defaults );
		
		// Is it a valid user_id?
		if( !empty( $args['user_id'] ) )
		{
			if( is_wp_error( $validate_user = $this->validate_user( $args['user_id'] ) ) )
				return $validate_user;
		}
		
		// Was a true or false passed for owned and wanted?
		if( is_wp_error( $validate_owned_wanted = $this->validate_owned_wanted( $args['owned'], $args['wanted'] ) ) )
			return $validate_owned_wanted;
		
		$args['theme'] = sanitize_text_field( strtolower( $theme ) );
		$transient_theme = str_replace( " ", "", $args['theme'] );
		
		// Have we stored a transient?
		if( false === get_transient( 'bs_sets_by_'.$transient_theme.$args['user_id'].$args['wanted'].$args['owned'] ) )
		{
			$params = $this->build_bs_query( $args );
			$response = $this->remote_request( 'search', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( 'bs_sets_by_'.$transient_theme.$args['user_id'].$args['wanted'].$args['owned'], $response, DAY_IN_SECONDS );
		}
		
		// Get it and return a SimpleXML object
		return new SimpleXMLElement( get_transient( 'bs_sets_by_'.$transient_theme.$args['user_id'].$args['wanted'].$args['owned'] ) );
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
	 *	@param		int	$subtheme
	 *	@param		int $user_id (user_id)
	 *	@param		int	$owned
	 *	@param		int	$wanted
	 *
	 *	@return		object	$setData
	 */
	public function get_by_subtheme( $subtheme = '', $user_id = '', $wanted = '', $owned = '' )
	{
		$user_hash = $this->get_user_hash( $user_id );
		$api_key = $this->get_api_key();
		
		$subtheme = strtolower( $subtheme );
		
		if( false === get_transient( 'bs_sets_by_'.$subtheme.$user_id.$wanted.$owned ) )
		{
			$params = build_query( 
				urlencode_deep( 
					array( 
						'apiKey' 	=> $api_key,
						'userHash'	=> $user_hash,
						'query'		=>	'',
						'theme'		=>	$theme,
						'subtheme'	=>	'',
						'setNumber'	=>	'',
						'year'		=>	'',
						'owned'		=>	$owned,
						'wanted'	=>	$wanted
					) 
				)
			);
			
			$params = 'apiKey='.$api_key.'&userHash='.$user_hash.'&query=&theme=&subtheme='.$subtheme.'&setNumber=&year=&owned='.$owned.'&wanted='.$wanted;
			$response = $this->remote_request( 'search', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( 'bs_sets_by_'.$subtheme.$user_id.$wanted.$owned, $response, DAY_IN_SECONDS );
		}
		
		return new SimpleXMLElement( get_transient( 'bs_sets_by_'.$subtheme.$user_id.$wanted.$owned ) );
	}
	
	/** 
	 *	Get Set Info by Year
	 *
	 *	Pass a year and get all the information about that set.
	 *	Returns the setData response
	 *	See webservice-definition.json for all the fields returned.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 *	@updated	1.0
	 *
	 *	@param		int	$year
	 *	@param		int	$user_id
	 *	@param		int	$owned
	 *	@param		int	$wanted
	 *
	 *	@return		object 	$setData
	 */
	public function get_by_year( $year = '', $user_id = '', $owned = '', $wanted = '' )
	{
		$user_hash = $this->get_user_hash( $user_id );
		$api_key = $this->get_api_key();
		
		if( false === get_transient( 'bs_sets_by_year_'.$year.$user_id.$wanted.$owned ) )
		{
			$params = 'apiKey='.$api_key.'&userHash='.$user_hash.'&query=&theme=&subtheme=&setNumber=&year='.$year.'&owned='.$owned.'&wanted='.$wanted;
			$response = $this->remote_request( 'search', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( 'bs_sets_by_year_'.$year.$user_id.$wanted.$owned, $response, DAY_IN_SECONDS );
		}
		
		return new SimpleXMLElement( get_transient( 'bs_sets_by_year_'.$year.$user_id.$wanted.$owned ) );
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
	public function search( $args )
	{
		$defaults = array(
			'theme' 	=> '',
			'subtheme' 	=> '',
			'number' 	=> '',
			'year' 		=> '',
			'owned' 	=> '',
			'wanted' 	=> '',
			'query' 	=> '',
			'user_id' 	=> ''
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		extract( $args, EXTR_SKIP );
		
		$user_hash = $this->get_user_hash( $user_id );
		$api_key = $this->get_api_key();
		
		$sets = $this->set_number_check( $number );
		$transient_sets = str_replace( array( ',', '-' ), '', $sets );

		$theme = strtolower( $theme );
		$subtheme = strtolower( $subtheme );
		$query = strtolower( $query );

		if( false === get_transient( 'bs_search_'.$theme.$subtheme.$transient_sets.$year.$query.$user_id.$wanted.$owned ) )
		{
			$params = 'apiKey='.$api_key.'&userHash='.$user_hash.'&query='.$query.'&theme='.$theme.'&subtheme='.$subtheme.'&setNumber='.$sets.'&year='.$year.'&owned='.$owned.'&wanted='.$wanted; 
			$response = $this->remote_request( 'search', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( 'bs_search_'.$theme.$subtheme.$transient_sets.$year.$query.$user_id.$wanted.$owned, $response, DAY_IN_SECONDS );
		}
		
		return new SimpleXMLElement( get_transient( 'bs_search_'.$theme.$subtheme.$transient_sets.$year.$query.$user_id.$wanted.$owned ) );
	}
}