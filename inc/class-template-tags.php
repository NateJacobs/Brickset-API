<?php

/** 
 *	Display a list of all themes
 *
 *	Brickset returns the themeData response.
 *	See webservice-definition.json for all the fields returned.
 *
 *	@author		Nate Jacobs
 *	@date		2/2/13
 *	@since		1.0
 */
function brickset_themes()
{
	$brickset = new BricksetAPISearch();
	$brickset = $brickset->get_themes();

	if( is_wp_error( $brickset ) )
	{
		echo $brickset->get_error_message();
	}
	else
	{
		foreach ( $brickset as $theme )
		{
			echo '<p class="brickset-theme-list">'.$theme->theme.'</p>';
		}
	}
}

/** 
 *	Display a table of all subthemes for a given theme
 *
 *	Brickset returns the subthemeData response.
 *	See webservice-definition.json for all the fields returned.
 *
 *	@author		Nate Jacobs
 *	@date		2/2/13
 *	@since		1.0
 *
 *	@param		string
 */
function brickset_subthemes( $theme )
{
	$brickset = new BricksetAPISearch();
	$brickset = $brickset->get_subthemes( $theme );

	if( is_wp_error( $brickset ) )
	{
		echo $brickset->get_error_message();
	}
	else
	{
		echo '<h2 class="brickset-theme-name">'.$brickset->theme.'</h2>';
		echo '<table class="brickset-subtheme"><th>'. __( 'Subtheme', 'bs_api' ).'</th><th>'. __( 'Set Count', 'bs_api' ).'</th><th>'. __( 'First Year', 'bs_api' ).'</th><th>'. __( 'Last Year', 'bs_api' ).'</th>';		
		foreach ( $brickset as $subtheme )
		{
			echo '<tr>';
				echo '<td>'.$subtheme->subtheme.'</td>'; 
				echo '<td>'.$subtheme->setCount.'</td>';
				echo '<td>'.$subtheme->yearFrom.'</td>';
				echo '<td>'.$subtheme->yearTo.'</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
}

/** 
 *	Display a table of years a theme was available
 *
 *	Brickset returns the yearData response.
 *	See webservice-definition.json for all the fields returned.
 *
 *	@author		Nate Jacobs
 *	@date		2/2/13
 *	@since		1.0
 *
 *	@param		string
 */
function brickset_theme_years( $theme )
{
	$brickset = new BricksetAPISearch();
	$brickset = $brickset->get_theme_years( $theme );
	
	if( is_wp_error( $brickset ) )
	{
		echo $brickset->get_error_message();
	}
	else
	{
		echo '<h2 class="brickset-theme-name">'.$brickset->yearData->theme.'</h2>';
		echo '<table class="brickset-theme"><th>'. __( 'Year', 'bs_api' ).'</th><th>'. __( 'Set Count', 'bs_api' ).'</th>';			
		foreach ( $brickset as $year )
		{
				echo '<tr>';
					echo '<td>'.$year->year.'</td>';
					echo '<td>'.$year->setCount.'</td>';		
				echo '</tr>';
		}
		echo '</table>';
	}
}

/** 
 *	Display a table of the most searched for terms
 *
 *	Brickset returns searchData response.
 *	See webservice-definition.json for all the fields returned.
 *
 *	@author		Nate Jacobs
 *	@date		2/2/13
 *	@since		1.0
 */
function brickset_popular_searches()
{
	$brickset = new BricksetAPISearch();
	$brickset = $brickset->get_popular_searches();
	
	if( is_wp_error( $brickset ) )
	{
		echo $brickset->get_error_message();
	}
	else
	{
		echo '<h2 class="brickset-popular-header">'.__( 'Popular Searches on Brickset', 'bs_api' ).'</h2>';
		foreach ( $brickset as $search )
		{
			echo '<p class="brickset-popular-searches">'.$search->searchTerm.'</p>';
		}
	}
}

/** 
 *	Display a list of all sets updated since a given date
 *
 *	Brickset returns the setData response.
 *	See webservice-definition.json for all the fields returned.
 *
 *	use format of 'mm/dd/yyyy'
 *
 *	@author		Nate Jacobs
 *	@date		2/2/13
 *	@since		1.0
 *
 *	@param		string
 */	
function brickset_updated_since( $date )
{
	$brickset = new BricksetAPISearch();
	$brickset = $brickset->get_updated_since( $date );

	if( is_wp_error( $brickset ) )
	{
		echo $brickset->get_error_message();
	}
	else
	{
		echo '<table class="brickset-updated-since"><th>'. __( 'Image', 'bs_api' ).'</th><th>'. __( 'Set Name', 'bs_api' ).'</th><th>'. __( 'Set Number', 'bs_api' ).'</th>';	
		foreach ( $brickset as $updated )
		{
			echo '<tr>';
				echo '<td><img src="'.$updated->thumbnailURL.'"></td>';
				echo '<td>'.$updated->setName.'</td>';
				echo '<td>'.$updated->number.'-'.$updated->numberVariant.'</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
}

/** 
 *	Display a list of all sets specified
 *
 *	Brickset returns the setData response.
 *	See webservice-definition.json for all the fields returned.
 *
 *	@author		Nate Jacobs
 *	@date		2/15/13
 *	@since		1.0
 *
 *	@param		string	the set number	
 *	@param		array	[owned, wanted, user_id] pass a 1 for the owned or wanted value to return the owned or wanted sets for the specified owner
 */
function brickset_set_number( $set, $args = '' )
{
	$brickset = new BricksetAPISearch();
	$brickset = $brickset->get_by_number( $set, $args );

	if( is_wp_error( $brickset ) )
	{
		echo $brickset->get_error_message();
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
			
			echo '<img src="'.$result->imageURL.'"><br>';
			echo '<strong>'.__( 'Set Name', 'bs_api' ).': </strong>'.sanitize_text_field( $result->setName ).'<br>';
			echo '<strong>'.__( 'Set Number', 'bs_api' ).': </strong>'.$number.'-'.$numberVariant.'<br>';
			echo '<strong>'.__( 'Year', 'bs_api' ).': </strong>'.sanitize_text_field( $result->year ).'<br>';
			echo '<strong>'.__( 'Theme', 'bs_api' ).': </strong>'.sanitize_text_field( $result->theme ).'<br>';
			echo '<strong>'.__( 'Subtheme', 'bs_api' ).': </strong>'.sanitize_text_field( $result->subtheme ).'<br>';
			echo '<strong>'.sprintf( __( '%s Retail Price', 'bs_api' ), $settings['currency'] ).': </strong>'.$settings['currency_symbol'].sanitize_text_field( $result->$settings['currency_key'] ).'<br>';
			echo '<strong>'.__( 'Pieces', 'bs_api' ).': </strong>'.sanitize_text_field( $result->pieces ).'<br>';
			echo '<strong>'.__( 'Minifigs', 'bs_api' ).': </strong>'.sanitize_text_field( $result->minifigs ).'<br>';
			echo '<strong>'.__( 'Set Guide', 'bs_api' ).': </strong><a href='.esc_url( $result->bricksetURL ).'>Brickset</a><br>';
			echo $bricklink;
		}
	}
}