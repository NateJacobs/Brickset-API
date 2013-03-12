<?php

/** 
*	Brickset Oembed
*
*	Add oembed support for Brickset theme and set links
*	Thanks to Lee Willis for the tutorial and code base to work from
*	https://github.com/leewillis77/wp-wpdotorg-embed
*
*	@author		Nate Jacobs
*	@date		3/9/13
*	@since		1.0
*/
class BricksetOembed extends BricksetAPIFunctions
{
	/** 
	*	Construct Method
	*
	*	Start things off
	*
	*	@author		Nate Jacobs
	*	@date		3/9/13
	*	@since		1.0
	*
	*	@param		null
	*/
	public function __construct()
	{
		add_action( 'init', array ( $this, 'register_oembed' ) );
		add_action( 'init', array ( $this, 'maybe_handle_oembed' ) );
	}

	/** 
	*	Register Oembed
	*
	*	Register the two URLS that support will be created for
	*
	*	@author		Nate Jacobs
	*	@date		3/9/13
	*	@since		1.0
	*
	*	@param		null
	*/
	public function register_oembed()
	{
		$oembed_url = home_url();
		$key = $this->get_key();
		// Create our own oembed url
		$oembed_url = add_query_arg ( array ( 'brickset_oembed' => $key ), $oembed_url);
		
		wp_oembed_add_provider( '#https?://(www\.)?brickset.com/detail/\?Set\=.*/?#i', $oembed_url, true );
		wp_oembed_add_provider( '#https?://(www\.)?brickset.com/browse/themes/\?theme\=.*/?#i', $oembed_url, true );
	}
	
	/** 
	*	Get Key
	*
	*	Create a random key to prevent hijacking
	*
	*	@author		Nate Jacobs
	*	@date		3/10/13
	*	@since		1.0
	*
	*	@param		null
	*
	*	@return		string	$key
	*/
	private function get_key() 
	{

		$key = get_option ( 'brickset_oembed_key' );

		if ( !$key ) {
			$key = md5 ( time() . rand ( 0,65535 ) );
			add_option ( 'brickset_oembed_key', $key, '', 'yes' );
		}

		return $key;

	}
	
	/** 
	*	Maybe Handle Oembed
	*
	*	Test if the correct key is present in the URL passed
	*
	*	@author		Nate Jacobs
	*	@date		3/10/13
	*	@since		1.0
	*
	*	@param		null
	*/
	public function maybe_handle_oembed() 
	{
		// If the query argument is there hand
		if ( isset ( $_GET['brickset_oembed'] ) ) 
		{
			// Hand it off to the handle_oembed function
			return $this->handle_oembed();
		}
	}
	
	/** 
	*	Handle Oembed
	*
	*	Takes care of formating the URL to call the Brickset API
	*
	*	@author		Nate Jacobs
	*	@date		3/10/13
	*	@since		1.0
	*
	*	@param		null
	*/	
	public function handle_oembed() 
	{  
		// Did we get here by mistake?
	    if ( ! isset ( $_GET['brickset_oembed'] ) ) 
	    {  
	    	// If so, get out of here
	        return;  
	    }  
	    // Check this request is valid  
	    if ( $_GET['brickset_oembed'] != $this->get_key() ) 
	    {  
	        header ( 'HTTP/1.0 403 Forbidden' );  
	        die ( 'Forbidden.' );  
	    }  
	    
	    // Check we have the required information  
	    $url = isset ( $_REQUEST['url'] ) ? $_REQUEST['url'] : null;  
	    $format = isset ( $_REQUEST['format'] ) ? $_REQUEST['format'] : null;
	    
	    if( !empty ( $format ) && $format != 'json' ) 
	    {
			header( 'HTTP/1.0 501 Not implemented' );
			die( 'Only json allowed' );
		}
	    
	    // Check if URL passed contains the string 'detail/?Set='
	    if( false !== strpos( $url, 'detail/?Set=' ) )
	    {
	    	// If it does, separate on this string
	    	$set_id = explode( '?Set=', $url );
	    	
	    	// Is there additional arguments passed, if so only get what is before the & 
	    	if( false !== strpos( $url, '&' ) )
	    		$set_id[1] = strstr( $set_id[1], '&', true );
	    		
	    	// Send it on to the Set method 
	    	$this->oembed_set( $set_id[1] );
	    }
	    // Now we are checking if the URL passed contains the string 'browse/themes/?theme='
	    elseif( false !== strpos( $url, 'browse/themes/?theme=' ) )
	    {
	    	// If it does, separate on this string
	    	$theme_name = explode( '?theme=', $url );
	    	
	    	// Is there additional arguments passed, if so only get what is before the & 
	    	if( false !== strpos( $url, '&' ) )
	    	 	$theme_name[1] = strstr( $theme_name[1], '&', true );
    
	    	// Convert %20 and other urlencoded strings to non urlencoded
	    	// The build_query method encodes them later 
	    	$theme = urldecode( $theme_name[1] );
    	 	
    	 	// Send it on to the set method
    	 	$this->oembed_theme( $theme );
	    }
	    else
	    {
		    header ( 'HTTP/1.0 404 Not Found' );
			die( 'Invalid oembed' );
	    }
    }
    
    /** 
    *	Oembed Set URL
    *
    *	Takes a set number from the Brickset URL and displays information about the set
    *
    *	@author		Nate Jacobs
    *	@date		3/10/13
    *	@since		1.0
    *
    *	@param		string	$set_number
    */
    public function oembed_set( $set_number )
    {
    	// Call the API function
    	$brickset = parent::get_by_number( $set_number );
    	
    	// Build the Oembed class
	    $response = new stdClass();  
		$response->type = 'rich';  
		$response->width = '10';  
		$response->height = '10';  
		$response->version = '1.0';  
		$response->title = $brickset->setData->setName;  
		$response->html = '<div class="brickset-oembed-set">';
		
		// Check for errors
		if( is_wp_error( $brickset ) )
		{
			$response->html .= '<p>'.$brickset->get_error_message().'</p>';
		}
		else
		{
			// Loop through and display the set information
			foreach( $brickset as $result )
			{
				$response->html .= '<img src="'.$result->imageURL.'"><br>';
				$response->html .= '<strong>'.__( 'Set Name', 'bs_api' ).': </strong>'.$result->setName.'<br>';
				$response->html .= '<strong>'.__( 'Set Number', 'bs_api' ).': </strong>'.$result->number.'-'.$result->numberVariant.'<br>';
				$response->html .= '<strong>'.__( 'Year', 'bs_api' ).': </strong>'.$result->year.'<br>';
				$response->html .= '<strong>'.__( 'Theme', 'bs_api' ).': </strong>'.$result->theme.'<br>';
				$response->html .= '<strong>'.__( 'Subtheme', 'bs_api' ).': </strong>'.$result->subtheme.'<br>';
				$response->html .= '<strong>'.__( 'US Retail Price', 'bs_api' ).': </strong>$'.$result->USRetailPrice.'<br>';
				$response->html .= '<strong>'.__( 'Pieces', 'bs_api' ).': </strong>'.$result->pieces.'<br>';
				$response->html .= '<strong>'.__( 'Minifigs', 'bs_api' ).': </strong>'.$result->minifigs.'<br>';
				$response->html .= '<strong>'.__( 'Set Guide', 'bs_api' ).': </strong><a href='.$result->bricksetURL.'>Brickset</a><br>';
				$response->html .= '<strong>'.__( 'BrickLink', 'bs_api' ).': </strong><a href=http://www.bricklink.com/catalogItem.asp?S='.$result->number.'-'.$result->numberVariant.'>BrickLink</a><br><hr>';
			}
		}
		
		$response->html .= '</div>';
		  
		header ( 'Content-Type: application/json' );  
		echo json_encode ( $response );  
		die();
    }
    
    /** 
    *	Oembed Theme URL
    *
    *	Takes a theme name from the Brickset URL and displays information about all the sets in that theme in a table
    *
    *	@author		Nate Jacobs
    *	@date		3/10/13
    *	@since		1.0
    *
    *	@param		string	$theme_name
    */
    public function oembed_theme( $theme_name )
    {
		// Call the API function
		$brickset = parent::get_by_theme( $theme_name );   
    
		// Build the Oembed class
    	$response = new stdClass();  
		$response->type = 'rich';  
		$response->width = '10';  
		$response->height = '10';  
		$response->version = '1.0';  
		$response->title = 'Theme List';  
		$response->html = '<div class="brickset-oembed-theme">';

		// Check for errors
		if( is_wp_error( $brickset ) )
		{
			$response->html .= '<p>'.$brickset->get_error_message().'</p>';
		}
		else
		{
			// Loop through and display the info about the sets in the theme
			$response->html .= '<table class="brickset-theme-sets"><th>'. __( 'Image', 'bs_api' ).'</th><th>'. __( 'Set Name', 'bs_api' ).'</th><th>'. __( 'Set Number', 'bs_api' ).'</th><th>'. __( 'Year', 'bs_api' ).'</th><th>'. __( 'Pieces', 'bs_api' ).'</th>';	

			foreach ( $brickset as $updated )
			{
				$response->html .= '<tr>';
					$response->html .= '<td><img src="'.$updated->thumbnailURL.'"></td>';
					$response->html .= '<td>'.$updated->setName.'</td>';
					$response->html .= '<td>'.$updated->number.'-'.$updated->numberVariant.'</td>';
					$response->html .= '<td>'.$updated->year.'</td>';
					$response->html .= '<td>'.$updated->pieces.'</td>';
				$response->html .= '</tr>';
			}

			$response->html .= '</table>';
		}
		
		$response->html .= '</div>';
			  
		header ( 'Content-Type: application/json' );  
		echo json_encode ( $response );  
		die();
	}
}
$brickset_oembed = new BricksetOembed();