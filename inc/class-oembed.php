<?php

/** 
*	Brickset Oembed
*
*	
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
	*	@param		
	*/
	public function __construct()
	{
		add_action( 'init', array ( $this, 'register_oembed' ) );
		add_action( 'init', array ( $this, 'maybe_handle_oembed' ) );
	}

	/** 
	*	Register Oembed
	*
	*	
	*
	*	@author		Nate Jacobs
	*	@date		3/9/13
	*	@since		1.0
	*
	*	@param		
	*/
	public function register_oembed()
	{
		$oembed_url = home_url();
		$key = $this->get_key();
		$oembed_url = add_query_arg ( array ( 'brickset_oembed' => $key ), $oembed_url);
		
		wp_oembed_add_provider( '#https?://(www\.)?brickset.com/detail/\?Set\=.*/?#i', $oembed_url, true );
		wp_oembed_add_provider( '#https?://(www\.)?brickset.com/browse/themes/\?theme\=.*/?#i', $oembed_url, true );
	}
	
	private function get_key() 
	{

		$key = get_option ( 'brickset_oembed_key' );

		if ( ! $key ) {
			$key = md5 ( time() . rand ( 0,65535 ) );
			add_option ( 'brickset_oembed_key', $key, '', 'yes' );
		}

		return $key;

	}
	
	public function maybe_handle_oembed() 
	{
			if ( isset ( $_GET['brickset_oembed'] ) ) 
			{
				return $this->handle_oembed();
			}
	}
		
	public function handle_oembed() 
	{  
  
	    if ( ! isset ( $_GET['brickset_oembed'] ) ) 
	    {  
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
	    
	    if( false !== strpos( $url, 'detail/?Set=' ) )
	    {
	    	 $set_id = explode( '?Set=', $url );
	    
	    	 if( false !== strpos( $url, '&' ) )
	    	 	$set_id[1] = strstr( $set_id[1], '&', true );
	    	 
	    	 $this->oembed_set( $set_id[1] );
	    }
	    elseif( false !== strpos( $url, 'browse/themes/?theme=' ) )
	    {
	    	$theme_name = explode( '?theme=', $url );
	    
	    	 if( false !== strpos( $url, '&' ) )
	    	 	$theme_name[1] = strstr( $theme_name[1], '&', true );
    	 	
    	 	$theme = urldecode( $theme_name[1] );
    	 	
		   	$this->oembed_theme( $theme );
	    }
	    else
	    {
		    header ( 'HTTP/1.0 404 Not Found' );
			die( 'Invalid oembed' );
	    }
    }
    
    public function oembed_set( $set_number )
    {
    	$brickset = parent::get_by_number( $set_number );
    	
	    $response = new stdClass();  
		$response->type = 'rich';  
		$response->width = '10';  
		$response->height = '10';  
		$response->version = '1.0';  
		$response->title = $brickset->setData->setName;  
		$response->html = '<div class="brickset-oembed-set">';
		
		if( is_wp_error( $brickset ) )
		{
			$response->html .= '<p>'.$brickset->get_error_message().'</p>';
		}
		else
		{
			//$return = '';
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
    *	Oembed Theme
    *
    *	
    *
    *	@author		Nate Jacobs
    *	@date		3/10/13
    *	@since		1.0
    *
    *	@param		
    */
    public function oembed_theme( $theme_name )
    {
		
		$brickset = parent::get_by_theme( $theme_name );   
    
    	$response = new stdClass();  
		$response->type = 'rich';  
		$response->width = '10';  
		$response->height = '10';  
		$response->version = '1.0';  
		$response->title = 'Theme List';  
		$response->html = '<div class="brickset-oembed-theme">';
			
		if( is_wp_error( $brickset ) )
		{
			$response->html .= '<p>'.$brickset->get_error_message().'</p>';
		}
		else
		{

			$response->html .= '<table class="brickset-updated-since"><th>'. __( 'Image', 'bs_api' ).'</th><th>'. __( 'Set Name', 'bs_api' ).'</th><th>'. __( 'Set Number', 'bs_api' ).'</th><th>'. __( 'Year', 'bs_api' ).'</th><th>'. __( 'Pieces', 'bs_api' ).'</th>';	

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