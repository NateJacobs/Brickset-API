<?php

/** 
 *	Add oembed support for Brickset theme and set links
 *	Thanks to Lee Willis for the tutorial and code base to work from
 *	https://github.com/leewillis77/wp-wpdotorg-embed
 *
 *	@author		Nate Jacobs
 *	@date		3/9/13
 *	@since		1.0
 */
class BricksetOembed extends BricksetAPISearch
{
	/** 
	 *	Start things off
	 *
	 *	@author		Nate Jacobs
	 *	@date		3/9/13
	 *	@since		1.0
	 */
	public function __construct()
	{
		add_action( 'init', array ( $this, 'register_oembed' ) );
		add_action( 'init', array ( $this, 'maybe_handle_oembed' ) );
	}

	/** 
	 *	Register the two URLS that support will be created for
	 *
	 *	@author		Nate Jacobs
	 *	@date		3/9/13
	 *	@since		1.0
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
	 *	Create a random key to prevent hijacking
	 *
	 *	@author		Nate Jacobs
	 *	@date		3/10/13
	 *	@since		1.0
	 *
	 *	@return		string
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
	 *	Test if the correct key is present in the URL passed
	 *
	 *	@author		Nate Jacobs
	 *	@date		3/10/13
	 *	@since		1.0
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
	 *	Takes care of formating the URL to call the Brickset API
	 *
	 *	@author		Nate Jacobs
	 *	@date		3/10/13
	 *	@since		1.0
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
     *	Takes a set number from the Brickset URL and displays information about the set
     *
     *	@author		Nate Jacobs
     *	@date		3/10/13
     *	@since		1.0
     *
     *	@param		string	the set number to display information about
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
			global $brickset_api_utilities;
			$settings = $brickset_api_utilities->get_settings_rules();
			
			$number = '';
			$numberVariant = '';
			
			if( true === $settings['bricklink'] )
			{
				$bricklink = '<strong>'.__( 'BrickLink', 'bs_api' ).': </strong><a href=http://www.bricklink.com/catalogItem.asp?S='.$number.'-'.$numberVariant.'>BrickLink</a><br><hr>';
			}
			elseif( false === $settings['bricklink'] )
			{
				$bricklink = '';
			}
			
			// Loop through and display the set information
			foreach( $brickset as $result )
			{
				$number = sanitize_text_field( $result->number );
				$numberVariant = sanitize_text_field( $result->numberVariant );
				
				if( empty( $result->$settings['currency_key'] ) && 'unk' === $settings['currency_unknown'] )
				{
					$result->$settings['currency_key'] = __( ' Unknown', 'bs_api' );
				}
				
				if( empty( $result->$settings['currency_key'] ) && 'us' === $settings['currency_unknown'] )
				{
					$settings['currency'] = 'US';
					$settings['currency_key'] = 'USRetailPrice';
					$settings['currency_symbol'] = '&#36;';
				}
				
				$response->html .= '<img src="'.$result->imageURL.'"><br>';
				$response->html .= '<strong>'.__( 'Set Name', 'bs_api' ).': </strong>'.sanitize_text_field( $result->setName ).'<br>';
				$response->html .= '<strong>'.__( 'Set Number', 'bs_api' ).': </strong>'.$number.'-'.$numberVariant.'<br>';
				$response->html .= '<strong>'.__( 'Year', 'bs_api' ).': </strong>'.sanitize_text_field( $result->year ).'<br>';
				$response->html .= '<strong>'.__( 'Theme', 'bs_api' ).': </strong>'.sanitize_text_field( $result->theme ).'<br>';
				$response->html .= '<strong>'.__( 'Subtheme', 'bs_api' ).': </strong>'.sanitize_text_field( $result->subtheme ).'<br>';
				$response->html .= '<strong>'.sprintf( __( '%s Retail Price', 'bs_api' ), $settings['currency'] ).': </strong>'.$settings['currency_symbol'].sanitize_text_field( $result->$settings['currency_key'] ).'<br>';
				$response->html .= '<strong>'.__( 'Pieces', 'bs_api' ).': </strong>'.sanitize_text_field( $result->pieces ).'<br>';
				$response->html .= '<strong>'.__( 'Minifigs', 'bs_api' ).': </strong>'.sanitize_text_field( $result->minifigs ).'<br>';
				$response->html .= '<strong>'.__( 'Set Guide', 'bs_api' ).': </strong><a href='.esc_url( $result->bricksetURL ).'>Brickset</a><br>';
				$response->html .= $bricklink;
			}
		}
		
		$response->html .= '</div>';
		  
		header ( 'Content-Type: application/json' );  
		echo json_encode ( $response );  
		die();
    }
    
    /** 
     *	Takes a theme name from the Brickset URL and displays information about all the sets in that theme in a table
     *
     *	@author		Nate Jacobs
     *	@date		3/10/13
     *	@since		1.0
     *
     *	@param		string	the theme to display
     */
    public function oembed_theme( $theme )
    {
		// Call the API function
		$brickset = parent::get_by_theme( $theme );   
    
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