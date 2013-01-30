<?php

/** 
 *	Brick Get Themes
 *
 *	Returns the themeData response.
 *	See webservice-definition.json for all the fields returned.
 *
 *	@author		Nate Jacobs
 *	@since		0.2
 */
function brick_get_themes()
{
	$brickset = new BricksetAPIFunctions();
	return $brickset->get_themes();
}

/** 
 *	Brick Get Subtheme
 *
 *	Returns the subThemeData response.
 *	See webservice-definition.json for all the fields returned.
 *
 *	@author		Nate Jacobs
 *	@since		0.2
 *
 *	@param		string	$theme (a theme name)
 */
function brick_get_subthemes( $theme)
{
	$brickset = new BricksetAPIFunctions();
	return $brickset->get_subthemes();
}

/** 
 *	Brick Get Theme Years
 *
 *	Returns yearData response.
 *	See webservice-definition.json for all the fields returned.
 *
 *	@author		Nate Jacobs
 *	@since		0.2
 *
 *	@param		string	$theme (a theme name)
 */
function brick_get_theme_years( $theme )
{
	$brickset = new BricksetAPIFunctions();
	return $brickset->get_years( $theme );
}

/** 
 *	Brick Get Popular Searches
 *
 *	Returns searchData response.
 *	See webservice-definition.json for all the fields returned.
 *
 *	@author		Nate Jacobs
 *	@since		0.2
 */
function brick_get_popular_searches()
{
	$brickset = new BricksetAPIFunctions();
	return $brickset->get_popular_searches();
}

/** 
 *	Brick Get Updated Since
 *
 *	Returns the setData response.
 *	See webservice-definition.json for all the fields returned.
 *
 *	@author		Nate Jacobs
 *	@since		0.2
 *
 *	@param		string	$date (use format of 'mm/dd/yyyy')
 */
function brick_get_updated_since( $date )
{
	$brickset = new BricksetAPIFunctions();
	return $brickset->get_updated_since( $date );
}