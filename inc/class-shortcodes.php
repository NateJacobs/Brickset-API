<?php

class BricksetAPIShortcode extends BricksetAPIFunctions
{
	public function __construct()
	{
		add_shortcode( 'bs_set', array( $this, 'get_set' ) );
		add_shortcode( 'bs_my_wanted', array( $this, 'my_wanted' ) );
		add_shortcode( 'bs_my_owned', array( $this, 'my_owned' ) );
	}
	
	public function get_set( $atts )
	{
		extract( shortcode_atts( array( 
			'number' 		=> '',
		), $atts ) );

		parent::get_by_number( $number );
		
		$return = '<img src="'.$this->results->setData->imageURL.'"><br>';
		$return .= '<strong>Set Name: </strong>'.$this->results->setData->setName.'<br>';
		$return .= '<strong>Set Number: </strong>'.$this->results->setData->number.'-'.$this->results->setData->numberVariant.'<br>';
		$return .= '<strong>Year: </strong>'.$this->results->setData->year.'<br>';
		$return .= '<strong>Theme: </strong>'.$this->results->setData->theme.'<br>';
		$return .= '<strong>Subtheme: </strong>'.$this->results->setData->subtheme.'<br>';
		$return .= '<strong>US Retail Price: </strong>$'.$this->results->setData->USRetailPrice.'<br>';
		$return .= '<strong>Pieces: </strong>'.$this->results->setData->pieces.'<br>';
		$return .= '<strong>Minifigs: </strong>'.$this->results->setData->minifigs.'<br>';
		$return .= '<strong>Set Guide: </strong><a href='.$this->results->setData->bricksetURL.'>Brickset</a><br><br>';
		
		return $return;
	}
	
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
new BricksetAPIShortcode;