<?php

class BricksetAPIDisplay extends BricksetAPIFunctions
{
	/** 
	 *	Display a list of all themes
	 *
	 *	Brickset returns the themeData response.
	 *	See webservice-definition.json for all the fields returned.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 */
	public function list_themes()
	{
		parent::get_themes();
		
		foreach ( $this->results as $theme )
		{
			echo $theme->theme.'<br>';
		}
	}
	
	/** 
	 *	Display a list of all subthemes for a given theme
	 *
	 *	Brickset returns the subthemeData response.
	 *	See webservice-definition.json for all the fields returned.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 */
	public function list_subthemes( $theme )
	{
		parent::get_subthemes( $theme );
		
		echo '<h2>'.$this->results->subthemeData->theme.'</h2>';
		echo '<table><th>Subtheme</th><th>Set Count</th><th>Start Year</th><th>End Year</th>';		
		foreach ( $this->results as $subtheme )
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

	/** 
	 *	Display a list of years a theme was available
	 *
	 *	Brickset returns the yearData response.
	 *	See webservice-definition.json for all the fields returned.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 */
	public function list_theme_years( $theme )
	{
		parent::get_theme_years( $theme );
		
		echo '<h2>'.$this->results->yearData->theme.'</h2>';
		echo '<table><th>Year</th><th>Set Count</th>';			
		foreach ( $this->results as $year )
		{
				echo '<tr>';
					echo '<td>'.$year->year.'</td>';
					echo '<td>'.$year->setCount.'</td>';		
				echo '</tr>';
		}
		echo '</table>';
	}
	
	/** 
	 *	Display a list of the most searched for terms
	 *
	 *	Brickset returns searchData response.
	 *	See webservice-definition.json for all the fields returned.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 */
	public function list_popular_searches()
	{
		parent::get_popular_searches();
		
		echo '<table><th>Search Term</th><th>Weight of Search</th>';	
		foreach ( $this->results as $search )
		{
			echo '<tr>';
				echo '<td>'.$search->searchTerm.'</td>';
				echo '<td>'.$search->count.'</td>';		
			echo '</tr>';
		}
		echo '</table>';
	}
	
	/** 
	 *	Display a list of all sets updated since a given date
	 *
	 *	Brickset returns the setData response.
	 *	See webservice-definition.json for all the fields returned.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 */	
	public function list_updated_since( $date )
	{
		parent::get_updated_since( $date );
		
		echo '<table><th>Image</th><th>Set Name</th><th>Set Number</th>';	
		foreach ( $this->results as $updated )
		{
			echo '<tr>';
				echo '<td><img src="'.$updated->thumbnailURL.'"></td>';
				echo '<td>'.$updated->setName.'</td>';
				echo '<td>'.$updated->number.'</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
}