<?php

/** 
 *	Brick Theme
 *
 *	Documented in inc/display.php: list_themes()
 *
 *	@author		Nate Jacobs
 *	@since		0.2
 */
function brick_themes()
{
	$brickset = new BricksetAPIDisplay();
	return $brickset->list_themes();
}

/** 
 *	Brick Get Themes
 *
 *	Documented in inc/functions.php: get_themes()
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
 *	Brick Subthemes
 *
 *	Documented in inc/display.php: list_subthemes()
 *
 *	@author		Nate Jacobs
 *	@since		0.2
 *
 *	@param		string	$theme (a theme name)
 */
function brick_subthemes( $theme )
{
	$brickset = new BricksetAPIDisplay();
	return $brickset->list_subthemes( $theme );
}

/** 
 *	Brick Get Subtheme
 *
 *	Documented in inc/functions.php: get_subthemes()
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
 *	Brick Theme Years
 *
 *	Documented in inc/display.php: list_theme_years()
 *
 *	@author		Nate Jacobs
 *	@since		0.2
 *
 *	@param		string	$theme (a theme name)
 */
function brick_theme_years( $theme )
{
	$brickset = new BricksetAPIDisplay();
	return $brickset->list_theme_years( $theme );
}

/** 
 *	Brick Get Theme Years
 *
 *	Documented in inc/functions.php: get_years()
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
 *	Brick Popular Searches
 *
 *	Documented in inc/display.php: list_popular_searches()
 *
 *	@author		Nate Jacobs
 *	@since		0.2
 */
function brick_popular_searches()
{
	$brickset = new BricksetAPIDisplay();
	return $brickset->list_popular_searches();
}

/** 
 *	Brick Get Popular Searches
 *
 *	Documented in inc/functions.php: get_popular_searches()
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
 *	Brick Updated Since
 *
 *	Documented in inc/display.php: list_updated_since()
 *
 *	@author		Nate Jacobs
 *	@since		0.2
 *
 *	@param		string	$date (use format of 'mm/dd/yyyy')
 */
function brick_updated_since( $date )
{
	$brickset = new BricksetAPIDisplay();
	return $brickset->list_updated_since( $date );
}
/** 
 *	Brick Get Updated Since
 *
 *	Documented in inc/functions.php: get_updated_since();
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