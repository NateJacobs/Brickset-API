<?php

new BricksetAPIShortcode;

class BricksetAPIShortcode
{
	public function __construct()
	{
		add_shortcode( 'bs_set', array( __CLASS__, 'get_set' ) );
		add_shortcode( 'bs_my_wanted', array( __CLASS__, 'my_wanted' ) );
		add_shortcode( 'bs_my_owned', array( __CLASS__, 'my_owned' ) );
	}
	
	public function get_set( $atts )
	{
		extract( shortcode_atts( array( 
			'number' 		=> '',
		), $atts ) );
		
		$brickset = new BricksetAPIFunctions();
		$result = $brickset->get_by_number( $number );
		
		$return = '<img src="'.$result->thumbnailURL.'"><br>';
		$return .= '<strong>'.__( 'Set Name:', 'bs_api' ).'</strong>'.$result->setName.'<br>';
		$return .= '<strong>'.__( 'Set Number:', 'bs_api' ).'</strong>'.$result->number.'<br>';
		$return .= '<strong>'.__( 'Year:', 'bs_api' ).'</strong>'.$result->year.'<br>';
		$return .= '<strong>'.__( 'Theme:', 'bs_api' ).'</strong>'.$result->theme.'<br>';
		$return .= '<strong>'.__( 'Subtheme:', 'bs_api' ).'</strong>'.$result->subtheme.'<br>';
		$return .= '<strong>'.__( 'Pieces:', 'bs_api' ).'</strong>'.$result->pieces.'<br>';
		
		return $return;
	}
	
	public function my_wanted()
	{
		global $post;
		$user_id = $post->post_author;
		
		$brickset = new BricksetAPIFunctions();
		$results = $brickset->get_wanted( $user_id );
		
		$return = '<h2>'.__( 'My Wanted List', 'bs_api' ).'</h2>';
		$return .= '<table><th>'.__( 'Image', 'bs_api' ).'</th><th>'.__( 'Set Name', 'bs_api' ).'</th><th>'.__( 'Set Number', 'bs_api').'</th><th>'.__( 'Pieces', 'bs_api' ).'</th>';	
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
	
	public function my_owned()
	{
		global $post;
		$user_id = $post->post_author;
		
		$brickset = new BricksetAPIFunctions();
		$results = $brickset->get_owned( $user_id );
		
		$return = '<h2>'.__( 'My Set List', 'bs_api' ).'</h2>';
		$return .= '<table><th>'.__( 'Image', 'bs_api' ).'</th><th>'.__( 'Set Name', 'bs_api' ).'</th><th>'.__( 'Set Number', 'bs_api').'</th><th>'.__( 'Pieces', 'bs_api' ).'</th>';		
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