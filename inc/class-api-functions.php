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
		$result = wp_remote_get( $this->api_url.'/'.$extra_url.'?'.$params );
		$this->httpcode = $result['response']['code'];
		$this->results = new SimpleXMLElement( $result['body'] );
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
	*	Checks if the set number has a variant, if not one is added
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
	 *
	 *	@param	int 	$user_id
	 *	@param	string 	$username
	 *	@param	string	$password
	 */
	public function brickset_login( $user_id, $username, $password )
	{
		$user = get_userdata( $user_id );
		
		$params = 'u='.$username.'&p='.$password;
	
		$this->remote_request( 'login', $params );
		$user_hash = $this->results;
		
		try
		{
			if ( $this->httpcode != 200 )
				throw new Exception ( $this->error_msg );
			update_user_meta( $user->ID, 'brickset_user_hash',  (string) $user_hash[0] );
		}
		catch ( Exception $e ) 
		{
			echo $e->getMessage();
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
	 *
	 *	@return		array	$themes
	 */
	public function get_themes()
	{
		$this->remote_request( 'listThemes' );
		try
		{
			if ( $this->httpcode != 200 )
				throw new Exception ( $this->error_msg );
			$themes = $this->results;
			return $themes;
		}
		catch ( Exception $e ) 
		{
			echo $e->getMessage();
		}
	}
	
	/** 
	 *	Get a list of all subthemes for a given theme
	 *
	 *	Brickset returns the subthemeData response.
	 *	See webservice-definition.json for all the fields returned.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 *
	 *	@param		string	$theme
	 *	@return		array	$subthemes
	 */
	public function get_subthemes( $theme )
	{
		$params = 'theme='.$theme;
		$this->remote_request( 'listSubthemes', $params );
		try
		{
			if ( $this->httpcode != 200 )
				throw new Exception ( $this->error_msg );
			$subthemes = $this->results;
			return $subthemes;
		}
		catch ( Exception $e ) 
		{
			echo $e->getMessage();
		}
	}
	
	/** 
	 *	Get a list of years a theme was available
	 *
	 *	Brickset returns the yearData response.
	 *	See webservice-definition.json for all the fields returned.	
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 *
	 *	@param		string	$theme
	 *	@return		array	$years
	 */
	public function get_theme_years( $theme )
	{
		$params = 'theme='.$theme;
		$this->remote_request( 'listYears', $params );
		try
		{
			if ( $this->httpcode != 200 )
				throw new Exception ( $this->error_msg );
			$years = $this->results;
			return $years;
		}
		catch ( Exception $e ) 
		{
			echo $e->getMessage();
		}
	}
	
	/** 
	 *	Get a list of the most searched for terms
	 *
	 *	Brickset returns the searchData response
	 *	See webservice-definition.json for all the fields returned.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 *
	 *	@return		array	$searches
	 */
	public function get_popular_searches()
	{
		$this->remote_request( 'popularSearches' );
		
		try
		{
			if ( $this->httpcode != 200 )
				throw new Exception ( $this->error_msg );
			$searches = $this->results;
			return $searches;
		}
		catch ( Exception $e ) 
		{
			echo $e->getMessage();
		}
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
	 *	@return		array	$updated
	 */
	public function get_updated_since( $date )
	{
		$this->get_api_key();
		
		$params = 'apiKey='.$this->api_key.'&sinceDate='.$date;
		$this->remote_request( 'updatedSince', $params );
		
		try
		{
			if ( $this->httpcode != 200 )
				throw new Exception ( $this->error_msg );
			$updated = $this->results;
			return $updated;
		}
		catch ( Exception $e ) 
		{
			echo $e->getMessage();
		}
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
	 *	@return		array 	$setData
	 */
	public function get_by_number( $number = '', $user_id = '', $wanted = '', $owned = '' )
	{
		$this->get_user_hash( $user_id );
		$this->get_api_key();
		
		$number = $this->set_number_check( $number );
		
		$params = 'apiKey='.$this->api_key.'&userHash='.$this->user_hash.'&query=&theme=&subtheme=&setNumber='.$number.'&year=&owned='.$owned.'&wanted='.$wanted;

		$this->remote_request( 'search', $params );
		
		try
		{
			if ( $this->httpcode != 200 )
				throw new Exception ( $this->error_msg );
				
			if ( empty( $this->results ) )
				throw new Exception( $this->no_results_error );
				
			return $this->results;
		}
		catch ( Exception $e ) 
		{
			echo $e->getMessage();
		}
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
	 *	@return		array 	$setData
	 */
	public function get_wanted( $user_id = '' )
	{
		$this->get_user_hash( $user_id );
		$this->get_api_key();
		
		$params = 'apiKey='.$this->api_key.'&userHash='.$this->user_hash.'&query=&theme=&subtheme=&setNumber=&year=&owned=&wanted=1';

		$this->remote_request( 'search', $params );
		
		try
		{
			if ( $this->httpcode != 200 )
				throw new Exception ( $this->error_msg );
				
			if ( empty( $this->results ) )
				throw new Exception( $this->no_results_error );
				
			return $this->results;
		}
		catch ( Exception $e ) 
		{
			echo $e->getMessage();
		}
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
	 *	@return		array 	$setData
	 */
	public function get_owned( $user_id = '' )
	{
		$this->get_user_hash( $user_id );
		$this->get_api_key();
		
		$params = 'apiKey='.$this->api_key.'&userHash='.$this->user_hash.'&query=&theme=&subtheme=&setNumber=&year=&owned=1&wanted=';

		$this->remote_request( 'search', $params );
		
		try
		{
			if ( $this->httpcode != 200 )
				throw new Exception ( $this->error_msg );
				
			if ( empty( $this->results ) )
				throw new Exception( $this->no_results_error );
				
			return $this->results;
		}
		catch ( Exception $e ) 
		{
			echo $e->getMessage();
		}
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
	 *	@return		array 	$setData
	 */
	public function get_by_themes( $theme = '', $user_id = '', $owned = '', $wanted = '' )
	{
		$this->get_user_hash( $user_id );
		$this->get_api_key();
		
		$params = 'apiKey='.$this->api_key.'&userHash='.$this->user_hash.'&query=&theme='.$theme.'&subtheme=&setNumber=&year=&owned='.$owned.'&wanted='.$wanted;

		$this->remote_request( 'search', $params );
		
		try
		{
			if ( $this->httpcode != 200 )
				throw new Exception ( $this->error_msg );
				
			if ( empty( $this->results ) )
				throw new Exception( $this->no_results_error );
				
			return $this->results;
		}
		catch ( Exception $e ) 
		{
			echo $e->getMessage();
		}
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
	 *	@return		array	$setData
	 */
	public function get_by_subtheme( $subtheme = '', $user_id = '', $owned = '', $wanted = '' )
	{
		$this->get_user_hash( $user_id );
		$this->get_api_key();
		
		$params = 'apiKey='.$this->api_key.'&userHash='.$this->user_hash.'&query=&theme=&subtheme='.$subtheme.'&setNumber=&year=&owned='.$owned.'&wanted='.$wanted;

		$this->remote_request( 'search', $params );
		
		try
		{
			if ( $this->httpcode != 200 )
				throw new Exception ( $this->error_msg );
				
			if ( empty( $this->results ) )
				throw new Exception( $this->no_results_error );
				
			return $this->results;
		}
		catch ( Exception $e ) 
		{
			echo $e->getMessage();
		}
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
	 *	@return		array 	$setData
	 */
	public function get_by_year( $year = '', $user_id = '', $owned = '', $wanted = '' )
	{
		$this->get_user_hash( $user_id );
		$this->get_api_key();
		
		$params = 'apiKey='.$this->api_key.'&userHash='.$this->user_hash.'&query=&theme=&subtheme=&setNumber=&year='.$year.'&owned='.$owned.'&wanted='.$wanted;

		$this->remote_request( 'search', $params );
		
		try
		{
			if ( $this->httpcode != 200 )
				throw new Exception ( $this->error_msg );
				
			if ( empty( $this->results ) )
				throw new Exception( $this->no_results_error );
				
			return $this->results;
		}
		catch ( Exception $e ) 
		{
			echo $e->getMessage();
		}
	}
	
	/** 
	 *	Search the Brickset DB with a given set of criteria
	 *
	 *	Protected function only for use within this class to power the other functions.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 */
	protected function brickset_search( $args )
	{
		$theme		= isset( $args['theme'] ) ? $args['theme'] : '';
		$subtheme 	= isset( $args['subtheme'] ) ? $args['subtheme'] : '';
		$number 	= isset( $args['number'] ) ? $args['number'] : '';
		$year		= isset( $args['year'] ) ? $args['year'] : '';
		$owned	 	= isset( $args['owned'] ) ? $args['owned'] : '';
		$wanted	 	= isset( $args['wanted'] ) ? $args['wanted'] : '';
		$query	 	= isset( $args['query'] ) ? $args['query'] : '';
		$single	 	= isset( $args['single'] ) ? $args['single'] : '';
		$user_id 	= isset( $args['user_id'] ) ? $args['user_id'] : '';
		$user_hash 	= '';

		if( !empty( $number ) )
		{
			$number_check = explode( '-', $number );
			
			if( empty( $number_check[1] ) )
			{
				$number = $number_check[0].'-1';
			}
		}
		
		if ( !empty( $user_id ) )
			$user_hash = get_user_meta( $user_id, 'brickset_user_hash', true );
		
		$this->get_api_key();
		
		$params = 'apiKey='.$this->api_key.'&userHash='.$user_hash.'&query='.$query.'&theme='.$theme.'&subtheme='.$subtheme.'&setNumber='.$number.'&year='.$year.'&owned='.$owned.'&wanted='.$wanted;

		$this->remote_request( 'search', $params );
		
		try
		{
			if ( $this->httpcode != 200 )
				throw new Exception ( $this->error_msg );
			
			if ( $single )
			{
				$sets = $this->results->setData;
				return $sets;
			}
			else
			{	
				$sets = $this->results;
				return $sets;
			}
			if ( empty( $this->results ) )
				throw new Exception( $this->no_results_error );
		}
		catch ( Exception $e ) 
		{
			echo $e->getMessage();
		}
	}
}