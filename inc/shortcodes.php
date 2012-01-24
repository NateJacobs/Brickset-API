<?php

$brickset_shortcode = new BricksetAPIShortcode();

class BricksetAPIShortcode
{
	public function __construct()
	{
		add_shortcode( 'bs_set', array( __CLASS__, 'get_set' ) );
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
}