<?php

class BricksetAPISearch extends BricksetAPIUtilities
{	
	/** 
	 *	Start things off when class is instantiated
	 *
	 *	@author		Nate Jacobs
	 *	@date		2/22/13
	 *	@since		1.0
	 */
	public function __construct()
	{
		
	}
	
	/** 
	 *	Get a list of all themes
	 *
	 *	Brickset returns the themeData response.
	 *	See webservice-definition.json for all the fields returned.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 *	@updated		1.4
	 *
	 *	@return		object	a listing of all the themes
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
			set_transient( $transient, $response, apply_filters( 'bs_get_themes_transient', DAY_IN_SECONDS ) );
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
	 *	@updated		1.0
	 *
	 *	@param		string
	 *
	 *	@return		object
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
			$response = $this->remote_request( 'get', 'listSubthemes', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( $transient, $response, apply_filters( 'bs_get_subthemes_transient', DAY_IN_SECONDS ) );
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
	 *	@param		string
	 *
	 *	@return		object
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
			$response = $this->remote_request( 'get', 'listYears', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( $transient, $response, apply_filters( 'bs_get_theme_years_transient', DAY_IN_SECONDS ) );
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
	 *	@return		object
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
			set_transient( $transient, $response, apply_filters( 'bs_get_popular_searches_transient', HOUR_IN_SECONDS ) );
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
	 *	@param		string	use format of 'mm/dd/yyyy'
	 *
	 *	@return		object
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
			$params = 'apiKey='.$this->get_api_key().'&sinceDate='.$date;
			$response = $this->remote_request( 'get', 'updatedSince', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( $transient, $response, apply_filters( 'bs_get_updated_since_transient', DAY_IN_SECONDS ) );
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
	 *	@param		string	set number
	 *	@param		int 	user_id
	 *	@param		bool	true = return wanted
	 *	@param		bool	true = return owned
	 *
	 *	@return		object
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
		if( is_wp_error( $sets = $this->validate_set_number( $number ) ) )
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
	 *	@param		int 	user ID
	 *
	 *	@return		object
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
	 *	@param		int 	user_id
	 *
	 *	@return		object
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
	 *	@param		string
	 *	@param		array	user_id, owned, wanted
	 *	@param		int
	 *	@param		bool
	 *	@param		bool
	 *
	 *	@return		object
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
	 *	@param		int
	 *	@param		array	(user_id, owned, wanted)
	 *	@param		int
	 *	@param		int
	 *	@param		int
	 *
	 *	@return		object
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
	 *	@param		int
	 *	@param		array	user_id, owned, wanted
	 *	@param		int
	 *	@param		int
	 *	@param		int
	 *
	 *	@return		object
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
	 *	@param		string
	 *
	 *	@return		object
	 */
	public function get_instructions( $set_id = '' )
	{
		// Is there a setID?
		if( empty( $set_id ) )
			return new WP_Error( 'no-set-id', __( 'No set ID requested.', 'bs_api' ) );
		
		// Is the string numeric
		if( is_wp_error( $validate_set_id = $this->validate_set_id( $set_id ) ) )	
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
			set_transient( $transient, $response, apply_filters( 'bs_get_instructions_transient', DAY_IN_SECONDS ) );
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
	 *	@param		string
	 *	
	 *	@return		object
	 */
	public function get_by_set_id( $set_id )
	{
		// Is there a setID?
		if( empty( $set_id ) )
			return new WP_Error( 'no-set-id', __( 'No set ID requested.', 'bs_api' ) );
		
		// Is the string numeric
		if( is_wp_error( $validate_set_id = $this->validate_set_id( $set_id ) ) )	
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
			set_transient( $transient, $response, apply_filters( 'bs_get_by_set_id_transient', DAY_IN_SECONDS ) );
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
	 *
	 *	@param		array	theme, subtheme, set number, year, owned, wanted, query, user id
	 *
	 *	@return		object
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
		
		if( is_wp_error( $sets = $this->validate_set_number( $args['set_number'] ) ) )
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
			$response = $this->remote_request( 'get', 'search', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( $transient, $response, apply_filters( 'bs_search_transient', DAY_IN_SECONDS ) );
		}

		return new SimpleXMLElement( get_transient( $transient ) );
	}
	
	/** 
	 *	Get Minifig Collection
	 *
	 *	Retrieve a list of all minifigs owned or wanted by a user that optionally match a query. 
	 *	Leave owned and wanted blank to retrieve those owned and wanted, or set one of them to '1' to get just owned or just wanted. 
	 *	Query can be a complete minifig number (e.g. 'hp001'), or just a prefix (e.g. 'hp'). Leave blank to retrieve all.
	 *	Returns the minifigCollectionData response
	 *	See webservice-definition.json for all the fields returned.
	 *
	 *	@author		Nate Jacobs
	 *	@date		3/28/13
	 *	@since		1.1
	 *
	 *	@param		int
	 *	@param		array	query, owned, wanted
	 *
	 *	@return		object
	 */
	public function get_minifig_collection( $user_id, $args = '' )
	{
		$defaults = array(
			'query' 	=> '',
			'owned' 	=> '',
			'wanted' 	=> ''
		);
		
		if( empty( $args ) )
			return new WP_Error( 'missing-arguments', __( 'You must include at least one of the following: query, owned, or wanted.', 'bs_api' ) );
		
		$args = wp_parse_args( $args, $defaults );
		
		// Is it a valid user?
		if( is_wp_error( $validate_user = $this->validate_user( $user_id ) ) )	
			return $validate_user;
		
		// Is it a valid string?
		if( !empty( $args['query'] ) )
		{
			if( !is_string( $args['query'] ) )
				return new WP_Error( 'not-valid-query', __( 'The query requested is not a valid string.', 'bs_api' ) );
		}
		
		// Was a true or false passed for owned?
		if( !empty( $args['owned'] ) )
		{
			if( is_wp_error( $validate_owned = $this->validate_owned_wanted( $args['owned'] ) ) )
				return $validate_owned;
		}
		
		// Was a true or false passed for wanted?
		if( !empty( $args['wanted'] ) )
		{
			if( is_wp_error( $validate_wanted = $this->validate_owned_wanted( $args['wanted'] ) ) )
				return $validate_wanted;
		}
		
		$args['query'] = strtolower( $args['query'] );
			
		$transient_query = str_replace( array( ',', '-', " " ), '', $args['query'] );

		$transient = 'bs_minifig_'.$transient_query.'_user-'.$user_id.'_want-'.$args['wanted'].'_own-'.$args['owned'];

		if( false === get_transient( $transient ) )
		{
			$params = array( 
				'body' => array( 
					'userHash' => $this->get_user_hash( $user_id ), 
					'query' => sanitize_text_field( $args['query'] ), 
					'owned' => $args['owned'], 
					'wanted' => $args['wanted'] 
				)
			);
			
			$response = $this->remote_request( 'post', 'searchMinifigCollection', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( $transient, $response, apply_filters( 'bs_get_minifig_collection_transient', DAY_IN_SECONDS ) );
		}

		return new SimpleXMLElement( get_transient( $transient ) );		
	}
}