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
		echo '<h2 class="brickset-theme-name">'.$brickset->theme.'</h2>';
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
		echo '<table class="brickset-popular-searches"><th>Search Term</th><th>Weight of Search</th>';	
		foreach ( $brickset as $search )
		{
			echo '<tr>';
				echo '<td>'.$search->searchTerm.'</td>';
				echo '<td>'.$search->count.'</td>';		
			echo '</tr>';
		}
		echo '</table>';
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