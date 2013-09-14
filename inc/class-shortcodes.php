<?php

class BricksetAPIShortcode extends BricksetAPISearch
{
	/** 
	 *	Start things off right
	 *
	 *	@author		Nate Jacobs
	 *	@date		2/15/13
	 *	@since		1.0
	 */
	public function __construct()
	{
		add_shortcode( 'bs_set', array( $this, 'get_set' ) );
		//add_shortcode( 'bs_my_wanted', array( $this, 'my_wanted' ) );
		//add_shortcode( 'bs_my_owned', array( $this, 'my_owned' ) );
	}
	
	/** 
	 *	Get Set
	 *
	 *	Displays details for the sets specified.
	 *	More than one set can be specified by seperating set numbers by a comma.
	 *	e.g. 1380,10240
	 *
	 *	@author		Nate Jacobs
	 *	@date		2/15/13
	 *	@since		1.0
	 *
	 *	@param		array
	 */
	public function get_set( $atts )
	{
		extract( shortcode_atts( array( 
			'number' 		=> '',
		), $atts, 'bs_set' ) );

		$brickset = parent::get_by_number( $number );

		if( is_wp_error( $brickset ) )
		{
			return $brickset->get_error_message();
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
			
			$return = '';
			
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
			
				$return .= '<img src="'.$result->imageURL.'"><br>';
				$return .= '<strong>'.__( 'Set Name', 'bs_api' ).': </strong>'.sanitize_text_field( $result->setName ).'<br>';
				$return .= '<strong>'.__( 'Set Number', 'bs_api' ).': </strong>'.$number.'-'.$numberVariant.'<br>';
				$return .= '<strong>'.__( 'Year', 'bs_api' ).': </strong>'.sanitize_text_field( $result->year ).'<br>';
				$return .= '<strong>'.__( 'Theme', 'bs_api' ).': </strong>'.sanitize_text_field( $result->theme ).'<br>';
				$return .= '<strong>'.__( 'Subtheme', 'bs_api' ).': </strong>'.sanitize_text_field( $result->subtheme ).'<br>';
				$return .= '<strong>'.sprintf( __( '%s Retail Price', 'bs_api' ), $settings['currency'] ).': </strong>'.$settings['currency_symbol'].sanitize_text_field( $result->$settings['currency_key'] ).'<br>';
				$return .= '<strong>'.__( 'Pieces', 'bs_api' ).': </strong>'.sanitize_text_field( $result->pieces ).'<br>';
				$return .= '<strong>'.__( 'Minifigs', 'bs_api' ).': </strong>'.sanitize_text_field( $result->minifigs ).'<br>';
				$return .= '<strong>'.__( 'Set Guide', 'bs_api' ).': </strong><a href='.esc_url( $result->bricksetURL ).'>Brickset</a><br>';
				$return .= $bricklink;
			}
			return $return;

		}
	}
	
	/** 
	 *	My Wanted
	 *
	 *	Returns a table with the post authors wanted sets
	 *	Not functional yet.
	 *
	 *	@author		Nate Jacobs
	 *	@date		2/16/13
	 *	@since		1.0
	 */
	public function my_wanted()
	{
		global $post;
		$user_id = $post->post_author;
		
		parent::get_wanted( $user_id );
		
		$return = '<h2>My Wanted List</h2>';
		$return .= '<table><th>Image</th><th>Set Name</th><th>Set Number</th><th>Pieces</th>';	
		foreach ( $this->results as $result )
		{
			$return .= '<tr>';
				$return .= '<td><img src="'.$result->thumbnailURL.'"></td>';
				$return .= '<td>'.$result->setName.'</td>';
				$return .= '<td>'.$result->number.'</td>';
				$return .= '<td>'.$result->pieces.'</td>';
			$return .= '</tr>';
		}
		$return .= '</table>';		
		
		return $return;
	}
	
	/** 
	 *	My Owned
	 *
	 *	Returns a table with the post authors owned sets
	 *	Not functional yet.
	 *
	 *	@author		Nate Jacobs
	 *	@date		2/16/13
	 *	@since		1.0
	 */
	public function my_owned()
	{
		global $post;
		$user_id = $post->post_author;
		
		parent::get_owned( $user_id );

		$return = '<h2>My Set List</h2>';
		$return .= '<table><th>Image</th><th>Set Name</th><th>Set Number</th><th>Pieces</th><th>Quantity</th>';	
		foreach ( $this->results as $result )
		{
			$return .= '<tr>';
				$return .= '<td><img src="'.$result->thumbnailURL.'"></td>';
				$return .= '<td>'.$result->setName.'</td>';
				$return .= '<td>'.$result->number.'</td>';
				$return .= '<td>'.$result->pieces.'</td>';
				$return .= '<td>'.$result->qtyOwned.'</td>';
			$return .= '</tr>';
		}
		$return .= '</table>';		
		
		return $return;
	}
}
$brickset_shortcodes = new BricksetAPIShortcode;