<?php

class BricksetAPIFunctions
{
	protected $api_key			= '';
	protected $api_url 			= 'http://www.brickset.com/webservices/brickset.asmx/';
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
	 */
	protected function get_apikey()
	{
		$this->api_key = get_option( 'brickset_apikey' );
		return $this->api_key;
	}
	
	/** 
	 *	Get a list of all themes
	 *
	 *	Brickset returns the following fields in an array
	 *	themeData
	 *		theme 		- string
	 *		setCount 	- int
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
	 *	Brickset returns the following fields in an array
	 *	subthemeData
	 *		theme		- string
	 *		subtheme	- string
	 *		setCount	- int
	 *		yearFrom	- int
	 *		yearTo		- int
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
	 *	Brickset returns the following fields in an array
	 *	yearData
	 *		theme		- string
	 *		year		- string
	 *		setCount	- int
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
	 *	Brickset returns the following fields in an array
	 *	searchData
	 *		searchTerm	- string
	 *		count		- int
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
 	 *	Brickset returns the following fields in an array
	 *	setData
	 *		setID			- int
	 *		number			- string
	 *		numberVariant 	- int
	 *		setName			- string
	 *		year			- string
	 *		theme			- string
	 *		subtheme		- string
	 *		pieces			- string
	 *		thumbnailURL	- string
	 *		imageUrl		- string
	 *		bricksetURL		- string
	 *		own				- boolean
	 *		want			- boolean
	 *		qtyOwned		- int
	 *		lastUpdated		- dateTime
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 *
	 *	@param		string	$date (use format of 'mm/dd/yyyy')
	 *	@return		array	$updated
	 */
	public function get_updated_since( $date )
	{
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
	 *	setData
	 *		setID			- int
	 *		number			- string
	 *		numberVariant 	- int
	 *		setName			- string
	 *		year			- string
	 *		theme			- string
	 *		subtheme		- string
	 *		pieces			- string
	 *		thumbnailURL	- string
	 *		imageUrl		- string
	 *		bricksetURL		- string
	 *		own				- boolean
	 *		want			- boolean
	 *		qtyOwned		- int
	 *		lastUpdated		- dateTime
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 *
	 *	@param		int		$number (set number)
	 *	@param		int 	$user_id (user_id)
	 *	@param		int		$wanted (1 = return wanted)
	 *	@param		int		$owned (1 = return owned)
	 *	@return		array 	$setData
	 */
	public function get_by_number( $number = '', $user_id = '', $wanted = '', $owned = '' )
	{
		$setData = $this->brickset_search( array( 'number' => $number, 'user_id' => $user_id, 'wanted' => $wanted, 'owned' => $owned, 'single' => true ) );
		return $setData;
	}
	
	/** 
	 *	Get Wanted Sets
	 *
	 *	Get all the wanted sets by the specified user
	 *	setData
	 *		setID			- int
	 *		number			- string
	 *		numberVariant 	- int
	 *		setName			- string
	 *		year			- string
	 *		theme			- string
	 *		subtheme		- string
	 *		pieces			- string
	 *		thumbnailURL	- string
	 *		imageUrl		- string
	 *		bricksetURL		- string
	 *		own				- boolean
	 *		want			- boolean
	 *		qtyOwned		- int
	 *		lastUpdated		- dateTime
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.2
	 *
	 *	@param		int 	$user_id (user_id)
	 *	@return		array 	$setData
	 */
	public function get_wanted( $user_id = '' )
	{
		$setData = $this->brickset_search( array( 'user_id' => $user_id, 'wanted' => '1', 'single' => true ) );
		return $setData;
	}
	
	/** 
	 *	Get Owned Sets
	 *
	 *	Get all the sets owned by the specified user
	 *	setData
	 *		setID			- int
	 *		number			- string
	 *		numberVariant 	- int
	 *		setName			- string
	 *		year			- string
	 *		theme			- string
	 *		subtheme		- string
	 *		pieces			- string
	 *		thumbnailURL	- string
	 *		imageUrl		- string
	 *		bricksetURL		- string
	 *		own				- boolean
	 *		want			- boolean
	 *		qtyOwned		- int
	 *		lastUpdated		- dateTime
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.3
	 *
	 *	@param		int 	$user_id (user_id)
	 *	@return		array 	$setData
	 */
	public function get_owned( $user_id = '' )
	{
		$setData = $this->brickset_search( array( 'user_id' => $user_id, 'owned' => '1', 'single' => true ) );
		return $setData;
	}
	
	/** 
	 *	Get Set Info by Theme
	 *
	 *	Pass a theme and get all the information about the sets in that theme.
	 *	setData
	 *		setID			- int
	 *		number			- string
	 *		numberVariant 	- int
	 *		setName			- string
	 *		year			- string
	 *		theme			- string
	 *		subtheme		- string
	 *		pieces			- string
	 *		thumbnailURL	- string
	 *		imageUrl		- string
	 *		bricksetURL		- string
	 *		own				- boolean
	 *		want			- boolean
	 *		qtyOwned		- int
	 *		lastUpdated		- dateTime
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 *
	 *	@param		int		$number (set number)
	 *	@param		int 	$user_id (user_id)
	 *	@return		array 	$setData
	 */
	public function get_by_themes( $theme = '', $user_id = '' )
	{
		$theme = $this->brickset_search( array( 'theme' => $theme, 'user_id' => $user_id ) );
		return $theme->setData;
	}
	
	/** 
	 *	Get Set Info by Subtheme
	 *
	 *	Pass a subtheme and get all the information about the sets in that subtheme
	 *	setData
	 *		setID			- int
	 *		number			- string
	 *		numberVariant 	- int
	 *		setName			- string
	 *		year			- string
	 *		theme			- string
	 *		subtheme		- string
	 *		pieces			- string
	 *		thumbnailURL	- string
	 *		imageUrl		- string
	 *		bricksetURL		- string
	 *		own				- boolean
	 *		want			- boolean
	 *		qtyOwned		- int
	 *		lastUpdated		- dateTime
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 *
	 *	@param		int		$subtheme
	 *	@param		int 	$user_id (user_id)
	 *	@return		array 	$setData
	 */
	public function get_by_subtheme( $subtheme = '', $user_id = '' )
	{
		$subtheme = $this->brickset_search( array( 'subtheme' => $subtheme, 'user_id' => $user_id ) );
		return $subtheme->setData;
	}
	
	/** 
	 *	Get Set Info by Year
	 *
	 *	Pass a year and get all the information about that set.
	 *	setData
	 *		setID			- int
	 *		number			- string
	 *		numberVariant 	- int
	 *		setName			- string
	 *		year			- string
	 *		theme			- string
	 *		subtheme		- string
	 *		pieces			- string
	 *		thumbnailURL	- string
	 *		imageUrl		- string
	 *		bricksetURL		- string
	 *		own				- boolean
	 *		want			- boolean
	 *		qtyOwned		- int
	 *		lastUpdated		- dateTime
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 *
	 *	@param		int		$year
	 *	@param		int 	$user_id (user_id)
	 *	@return		array 	$setData
	 */
	public function get_by_year( $year = '', $user_id = '' )
	{
		$year = $this->brickset_search( array( 'year' => $year, 'user_id' => $user_id ) );
		return $year->setData;
	}
	
	/** 
	 *	Search the Brickset DB with a give set of criteria
	 *
 	 *	Brickset returns the following fields in an array
	 *	setData
	 *		setID			- int
	 *		number			- string
	 *		numberVariant 	- int
	 *		setName			- string
	 *		year			- string
	 *		theme			- string
	 *		subtheme		- string
	 *		pieces			- string
	 *		thumbnailURL	- string
	 *		imageUrl		- string
	 *		bricksetURL		- string
	 *		own				- boolean
	 *		want			- boolean
	 *		qtyOwned		- int
	 *		lastUpdated		- dateTime
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
		
		if ( !empty( $user_id ) )
			$user_hash = get_user_meta( $user_id, 'brickset_hash', true );
		
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
$brickset_functions = new BricksetAPIFunctions();