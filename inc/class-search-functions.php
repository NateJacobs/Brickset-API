<?php

class BricksetAPISearch
{	
	/** 
	*	Construct
	*
	*	Start things off when class is instantiated
	*
	*	@author		Nate Jacobs
	*	@date		2/22/13
	*	@since		1.0
	*
	*	@param		null
	*/
	public function __construct()
	{
		
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
	protected function remote_request( $type, $extra_url, $params = '' )
	{
		$api_url = 'http://www.brickset.com/webservices/brickset.asmx';	
//wp_die( $api_url.'/'.$extra_url.'?'.$params );
		if( 'get' == $type )
		{
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
	public function brickset_login( $user_id, $username, $password )
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
			$response = $this->remote_request( 'get', 'listThemes' );
			
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
		if( is_wp_error( $validate_theme = BrickSetAPIUtilities::validate_theme_subtheme( $theme ) ) )	
			return $validate_theme;
		
		$theme = sanitize_text_field( strtolower( $theme ) );
		$transient_theme = str_replace( " ", "", $theme );
		
		$transient = 'bs_'.$transient_theme.'_subthemes';
		
		// Have we stored a transient?
		if( false === get_transient( $transient ) )
		{
			$args = array( 'theme' => $theme );
			
			$params = BrickSetAPIUtilities::build_bs_query( $args );
			$response = $this->remote_request( 'get', 'listSubthemes', $params );

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
		if( is_wp_error( $validate_theme = BrickSetAPIUtilities::validate_theme_subtheme( $theme ) ) )	
			return $validate_theme;
		
		$theme = sanitize_text_field( strtolower( $theme ) );
		$transient_theme = str_replace( " ", "", $theme );
		
		$transient = 'bs_'.$transient_theme.'_years';
		
		// Have we stored a transient?
		if( false === get_transient( $transient ) )
		{
			$args = array( 'theme' => $theme );

			$params = BrickSetAPIUtilities::build_bs_query( $args );
			$response = $this->remote_request( 'get', 'listYears', $params );

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
			$response = $this->remote_request( 'get', 'popularSearches' );

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
		if( is_wp_error( $validate_year = BrickSetAPIUtilities::validate_year( $exploded_date[2] ) ) )	
			return $validate_year;
		
		$transient_date = str_replace( '/', '', $date );
		
		$transient = 'bs_updated_since_'.$transient_date;
		
		// Have we stored a transient?
		if( false === get_transient( $transient ) )
		{
			$params = 'apiKey='.BrickSetAPIUtilities::get_api_key().'&sinceDate='.$date;
			$response = $this->remote_request( 'get', 'updatedSince', $params );

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
		if( is_wp_error( $sets = BrickSetAPIUtilities::validate_set_number( $number ) ) )
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
	*	Get Instructions
	*
	*	Retrieve instructions link by Brickset set ID.
	*	Returns the instructionsData response
	*	See webservice-definition.json for all the fields returned.
	*
	*	@author		Nate Jacobs
	*	@date		3/22/13
	*	@since		1.1
	*
	*	@param		string	$set_id
	*
	*	@return		object 	$instructionData
	*/
	public function get_instructions( $set_id = '' )
	{
		// Is there a setID?
		if( empty( $set_id ) )
			return new WP_Error( 'no-set-id', __( 'No set ID requested.', 'bs_api' ) );
		
		// Is the string numeric
		if( is_wp_error( $validate_set_id = BrickSetAPIUtilities::validate_set_id( $set_id ) ) )	
			return $validate_set_id;
		
		$transient = 'bs_instructions'.$set_id;
		
		// Have we stored a transient?
		if( false === get_transient( $transient ) )
		{
			$params = 'setID='.$set_id;
			$response = $this->remote_request( 'get', 'listInstructions', $params );

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
	*	Returns the setData response
	*	See webservice-definition.json for all the fields returned.
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
		
		// Is the string numeric
		if( is_wp_error( $validate_set_id = BrickSetAPIUtilities::validate_set_id( $set_id ) ) )	
			return $validate_set_id;
			
		$transient = 'bs_set_id_search_'.$set_id;
		
		// Have we stored a transient?
		if( false === get_transient( $transient ) )
		{
			$params = 'setID='.$set_id;
			$response = $this->remote_request( 'get', 'searchBySetID', $params );

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
			if( is_wp_error( $validate_year = BrickSetAPIUtilities::validate_year( $args['year'] ) ) )	
				return $validate_year;
		}
		
		// Is it a valid user_id?
		if( !empty( $args['user_id'] ) )
		{
			if( is_wp_error( $validate_user = BrickSetAPIUtilities::validate_user( $args['user_id'] ) ) )
				return $validate_user;
		}
		
		// Is it a valid string
		if( !empty( $args['theme'] ) || !empty( $args['subtheme'] ) )
		{	
			if( is_wp_error( $validate_string = BrickSetAPIUtilities::validate_theme_subtheme( $args['theme'], $args['subtheme'] ) ) )	
				return $validate_string;
		}
		
		// Is it a valid string?
		if( !empty( $args['query'] ) )
		{
			if( !is_string( $args['query'] ) )
				return new WP_Error( 'not-valid-query', __( 'The query requested is not a valid string.', 'bs_api' ) );
		}
		
		// Was a true or false passed for owned and wanted?
		if( is_wp_error( $validate_owned_wanted = BrickSetAPIUtilities::validate_owned_wanted( $args['owned'], $args['wanted'] ) ) )
			return $validate_owned_wanted;
		
		if( is_wp_error( $sets = BrickSetAPIUtilities::validate_set_number( $args['set_number'] ) ) )
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
			$params = BrickSetAPIUtilities::build_bs_query( $args );
			$response = $this->remote_request( 'get', 'search', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( $transient, $response, DAY_IN_SECONDS );
		}

		return new SimpleXMLElement( get_transient( $transient ) );
	}
}