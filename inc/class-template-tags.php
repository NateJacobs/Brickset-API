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
	$brickset = new BricksetAPIFunctions();
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
 */
function brickset_subthemes( $theme )
{
	$brickset = new BricksetAPIFunctions();
	$brickset = $brickset->get_subthemes( $theme );

	if( is_wp_error( $brickset ) )
	{
		echo $brickset->get_error_message();
	}
	else
	{
		echo '<h2 class="brickset-theme-name">'.$brickset->theme.'</h2>';
		echo '<table class="brickset-subtheme"><th>Subtheme</th><th>Set Count</th><th>First Year</th><th>Last Year</th>';		
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
 */
function brickset_theme_years( $theme )
{
	$brickset = new BricksetAPIFunctions();
	$brickset = $brickset->get_theme_years( $theme );
	
	if( is_wp_error( $brickset ) )
	{
		echo $brickset->get_error_message();
	}
	else
	{
		echo '<h2 class="brickset-theme-name">'.$brickset->yearData->theme.'</h2>';
		echo '<table class="brickset-theme"><th>Year</th><th>Set Count</th>';			
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
	$brickset = new BricksetAPIFunctions();
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
 *	@author		Nate Jacobs
 *	@date		2/2/13
 *	@since		1.0
 */	
function brickset_updated_since( $date )
{
	$brickset = new BricksetAPIFunctions();
	$brickset = $brickset->get_updated_since( $date );

	if( is_wp_error( $brickset ) )
	{
		echo $brickset->get_error_message();
	}
	else
	{
		echo '<table class="brickset-updated-since"><th>Image</th><th>Set Name</th><th>Set Number</th>';	
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
*	@param		string	$set
*/
function brickset_set_number( $set, $args = '' )
{
	$brickset = new BricksetAPIFunctions();
	$brickset = $brickset->get_by_number( $set, $args );

	if( is_wp_error( $brickset ) )
	{
		echo $brickset->get_error_message();
	}
	else
	{
		foreach( $brickset as $result )
		{
			echo '<img src="'.$result->imageURL.'"><br>';
			echo '<strong>'.__( 'Set Name', 'bs_api' ).': </strong>'.$result->setName.'<br>';
			echo '<strong>'.__( 'Set Number', 'bs_api' ).': </strong>'.$result->number.'-'.$result->numberVariant.'<br>';
			echo '<strong>'.__( 'Year', 'bs_api' ).': </strong>'.$result->year.'<br>';
			echo '<strong>'.__( 'Theme', 'bs_api' ).': </strong>'.$result->theme.'<br>';
			echo '<strong>'.__( 'Subtheme', 'bs_api' ).': </strong>'.$result->subtheme.'<br>';
			echo '<strong>'.__( 'US Retail Price', 'bs_api' ).': </strong>$'.$result->USRetailPrice.'<br>';
			echo '<strong>'.__( 'Pieces', 'bs_api' ).': </strong>'.$result->pieces.'<br>';
			echo '<strong>'.__( 'Minifigs', 'bs_api' ).': </strong>'.$result->minifigs.'<br>';
			echo '<p><a href='.$result->bricksetURL.'>Brickset</a> <a href=http://www.bricklink.com/catalogItem.asp?S='.$result->number.'-'.$result->numberVariant.'>BrickLink</a></p><hr>';
		}
	}
}