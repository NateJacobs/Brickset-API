<?php

$brickset_shortcode = new BricksetAPIShortcode();

class BricksetAPIShortcode
{
	public function __construct()
	{
		add_shortcode( 'get_set', array( __CLASS__, 'get_set' ) );
	}
	
	public function get_set( $atts )
	{
		extract( shortcode_atts( array( 
			'set' 		=> '',
			'wanted'	=> '',
			'owned'		=> ''
		), $atts ) );
		
		$wanted = strtolower( $wanted );
		$owned	= strtolower( $owned );
		if( $wanted = 'yes' )
			$wanted = '1';
			
		if( $owned = 'yes' )
			$owned = '1';

		$brickset = new BricksetAPIFunctions();
		$result = $brickset->get_by_number( $set, $wanted, $owned );
		
		echo '<img src="'.$result->thumbnailURL.'"><br>';
		echo '<strong>Set Name: </strong>'.$result->setName.'<br>';
		echo '<strong>Set Number: </strong>'.$result->number.'<br>';
		echo '<strong>Year: </strong>'.$result->year.'<br>';
		echo '<strong>Theme: </strong>'.$result->theme.'<br>';
		echo '<strong>Subtheme: </strong>'.$result->subtheme.'<br>';
		echo '<strong>Pieces: </strong>'.$result->pieces.'<br>';
	}
}