<?php

class BricksetAPIFunctions
{
	protected $api_key;
	protected $user_hash;
	protected $api_url 			= 'http://www.brickset.com/webservices/brickset.asmx';
	protected $error_msg		= "<strong>Don't Panic!</strong> Something went wrong, and Brickset didn't reply correctly.";
	protected $no_results_error = "<strong>No results.</strong> Sorry, no sets were found for that query.";
	
	/** 
	 *	Remote Request
	 *
	 *	Send the api request to Brickset. Returns an XML formatted response.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 */
	protected function remote_request( $extra_url, $params = '' )
	{
//wp_die( $this->api_url.'/'.$extra_url.'?'.$params );	
		$response = wp_remote_get( $this->api_url.'/'.$extra_url.'?'.$params );
		
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
	 *	Get Brickset API Key
	 *
	 *	This method will retrieve the api key from the options table.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 *	@updated	1.0
	 *
	 *	@return		string	$this->api_key
	 */
	protected function get_api_key()
	{
		$settings = (array) get_option( 'brickset-api-settings' );
		$this->api_key = $settings['api_key'];
		return $this->api_key;
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
	*	@return		string	$this->user_hash
	*/
	protected function get_user_hash( $user_id )
	{
		$this->user_hash = get_user_meta( $user_id, 'brickset_user_hash', true );
		return $this->user_hash;
	}
	
	/** 
	*	Set Number Check
	*
	*	Checks if the set number has a variant, if not, one is added
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
		if( empty( $set_number ) )
			return '';
		
		$set_numbers = explode( ',', $set_number );

		$sets = '';
		
		foreach( $set_numbers as $set )
		{
			$number_check = explode( '-', $set );
			
			if( empty( $number_check[1] ) )
			{
				$sets .= $number_check[0].'-1,';
			}
			else
			{
				$sets .= $set.',';
			}
		}
		return substr(str_replace(' ','',$sets), 0, -1);
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
	 *	@return	array	$response (if there is an error, an WP_Error array is returned)
	 */
	public function brickset_login( $user_id, $username, $password )
	{
		$user = get_userdata( $user_id );
		
		$params = 'u='.$username.'&p='.$password;
	
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
		if( false === get_transient( 'bs_theme_list' ) )
		{
			$response = $this->remote_request( 'listThemes' );
			
			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( 'bs_theme_list', $response, DAY_IN_SECONDS );
		}
		
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
		$theme = strtolower( $theme );
		
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
		$theme = strtolower( $theme );
		
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
		if( false === get_transient( 'bs_popular_searches' ) )
		{
			$response = $this->remote_request( 'popularSearches' );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( 'bs_popular_searches', $response, HOUR_IN_SECONDS );
		}
		
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
		$this->get_api_key();
		
		$transient_date = str_replace( '/', '', $date );
		
		if( false === get_transient( 'bs_updated_since_'.$transient_date ) )
		{
			$params = 'apiKey='.$this->api_key.'&sinceDate='.$date;
			$response = $this->remote_request( 'updatedSince', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( 'bs_updated_since_'.$transient_date, $response, DAY_IN_SECONDS );
		}
		
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
	 *	@param		int		$number (set number)
	 *	@param		int 	$user_id (user_id)
	 *	@param		int		$wanted (1 = return wanted)
	 *	@param		int		$owned (1 = return owned)
	 *
	 *	@return		object 	$setData
	 */
	public function get_by_number( $number = '', $user_id = '', $wanted = '', $owned = '' )
	{
		$this->get_user_hash( $user_id );
		$this->get_api_key();
		
		$sets = $this->set_number_check( $number );
		$transient_sets = str_replace( array( ',', '-' ), '', $sets );
		
		if( false === get_transient( 'bs_'.$transient_sets.$user_id.$wanted.$owned ) )
		{
			$params = 'apiKey='.$this->api_key.'&userHash='.$this->user_hash.'&query=&theme=&subtheme=&setNumber='.$sets.'&year=&owned='.$owned.'&wanted='.$wanted;
			$response = $this->remote_request( 'search', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( 'bs_'.$transient_sets.$user_id.$wanted.$owned, $response, DAY_IN_SECONDS );
		}
		
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
		$this->get_user_hash( $user_id );
		$this->get_api_key();
		
		if( false === get_transient( 'bs_wanted'.$user_id ) )
		{
			$params = 'apiKey='.$this->api_key.'&userHash='.$this->user_hash.'&query=&theme=&subtheme=&setNumber=&year=&owned=&wanted=1';
			$response = $this->remote_request( 'search', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( 'bs_wanted'.$user_id, $response, DAY_IN_SECONDS );
		}
		
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
		$this->get_user_hash( $user_id );
		$this->get_api_key();
		
		if( false === get_transient( 'bs_owned'.$user_id ) )
		{
			$params = 'apiKey='.$this->api_key.'&userHash='.$this->user_hash.'&query=&theme=&subtheme=&setNumber=&year=&owned=1&wanted=';
			$response = $this->remote_request( 'search', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( 'bs_owned'.$user_id, $response, DAY_IN_SECONDS );
		}
		
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
	 *	@param		int	$theme 
	 *	@param		int $user_id
	 *	@param		int	$owned
	 *	@param		int	$wanted
	 *
	 *	@return		object 	$setData
	 */
	public function get_by_theme( $theme = '', $user_id = '', $wanted = '', $owned = '' )
	{
		$this->get_user_hash( $user_id );
		$this->get_api_key();
		
		$theme = strtolower( $theme );
		
		if( false === get_transient( 'bs_sets_by_'.$theme.$user_id.$wanted.$owned ) )
		{
			$params = 'apiKey='.$this->api_key.'&userHash='.$this->user_hash.'&query=&theme='.$theme.'&subtheme=&setNumber=&year=&owned='.$owned.'&wanted='.$wanted;
			$response = $this->remote_request( 'search', $params );

			if( is_wp_error( $response ) )
			{
				return $response;
			}
			set_transient( 'bs_sets_by_'.$theme.$user_id.$wanted.$owned, $response, DAY_IN_SECONDS );
		}
		
		return new SimpleXMLElement( get_transient( 'bs_sets_by_'.$theme.$user_id.$wanted.$owned ) );
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
		$this->get_user_hash( $user_id );
		$this->get_api_key();
		
		$subtheme = strtolower( $subtheme );
		
		if( false === get_transient( 'bs_sets_by_'.$subtheme.$user_id.$wanted.$owned ) )
		{
			$params = 'apiKey='.$this->api_key.'&userHash='.$this->user_hash.'&query=&theme=&subtheme='.$subtheme.'&setNumber=&year=&owned='.$owned.'&wanted='.$wanted;
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
		$this->get_user_hash( $user_id );
		$this->get_api_key();
		
		if( false === get_transient( 'bs_sets_by_year_'.$year.$user_id.$wanted.$owned ) )
		{
			$params = 'apiKey='.$this->api_key.'&userHash='.$this->user_hash.'&query=&theme=&subtheme=&setNumber=&year='.$year.'&owned='.$owned.'&wanted='.$wanted;
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
		$this->get_api_key();
		
		$sets = $this->set_number_check( $number );
		$transient_sets = str_replace( array( ',', '-' ), '', $sets );

		$theme = strtolower( $theme );
		$subtheme = strtolower( $subtheme );
		$query = strtolower( $query );

		if( false === get_transient( 'bs_search_'.$theme.$subtheme.$transient_sets.$year.$query.$user_id.$wanted.$owned ) )
		{
			$params = 'apiKey='.$this->api_key.'&userHash='.$user_hash.'&query='.$query.'&theme='.$theme.'&subtheme='.$subtheme.'&setNumber='.$sets.'&year='.$year.'&owned='.$owned.'&wanted='.$wanted; 
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