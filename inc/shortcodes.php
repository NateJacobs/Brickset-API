<?php

new BricksetAPIShortcode;

class BricksetAPIShortcode
{
	public function __construct()
	{
		add_shortcode( 'bs_set', array( __CLASS__, 'get_set' ) );
		add_shortcode( 'bs_my_wanted', array( __CLASS__, 'my_wanted' ) );
	}
	
	public function get_set( $atts )
	{
		extract( shortcode_atts( array( 
			'number' 		=> '',
		), $atts ) );
		
		$brickset = new BricksetAPIFunctions();
		$result = $brickset->get_by_number( $number );
		
		$return = '<img src="'.$result->thumbnailURL.'"><br>';
		$return .= '<strong>Set Name: </strong>'.$result->setName.'<br>';
		$return .= '<strong>Set Number: </strong>'.$result->number.'<br>';
		$return .= '<strong>Year: </strong>'.$result->year.'<br>';
		$return .= '<strong>Theme: </strong>'.$result->theme.'<br>';
		$return .= '<strong>Subtheme: </strong>'.$result->subtheme.'<br>';
		$return .= '<strong>Pieces: </strong>'.$result->pieces.'<br>';
		
		return $return;
	}
	
	public function my_wanted()
	{
		global $current_user;
		get_currentuserinfo();
		$user_id = $current_user->ID;
		$brickset = new BricksetAPIFunctions();
		$results = $brickset->get_wanted( $user_id );
		
		$return = '<h2>My Wanted List</h2>';
		$return .= '<table><th>Image</th><th>Set Name</th><th>Set Number</th><th>Pieces</th>';	
		foreach ( $results as $result )
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
}