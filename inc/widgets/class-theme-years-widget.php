<?php

//hook into the 'widgets_init' action to load the widget
add_action( 'widgets_init', 'brickset_register_theme_years_widget' );

/** 
 *	Registers the widget
 *
 *	@author		Nate Jacobs
 *	@date		09/13/13
 *	@since		1.4
 */
function brickset_register_theme_years_widget()
{
	register_widget( 'BricksetThemeYearsWidget' );
}

/** 
 *	Creates a widget that generates a list of years a particular theme was available. 
 *	The list is returned as links pointing back to the year/theme page on Brickset.com.
 *
 *	@author		Nate Jacobs
 *	@date		09/13/13
 *	@since		1.4
 */
class BricksetThemeYearsWidget extends WP_Widget
{
	/** 
	 *	Generate options for the widget display in the admin dashboard.
	 *
	 *	@author		Nate Jacobs
	 *	@date		09/13/13
	 *	@since		1.4
	 */
	public function __construct()
	{
		$options = array( 'description' => __( 'A listing of all years a particular theme was available with links to the year/theme page on Brickset.com.', 'bs_api' ), 'classname' => 'brickset_theme_years' );
		parent::__construct('brickset_theme_years_widget', $name = __( 'Brickset Theme Years', 'bs_api' ), $options );
	}
	
	/** 
	 *	Create the necessary form to customize the widget.
	 *
	 *	@uses		title	@since 0.1
	 *
	 *	@author		Nate Jacobs
	 *	@date		09/13/13
	 *	@since		1.4
	 *
	 *	@param		array
	 */
	public function form( $instance )
	{
		$instance = wp_parse_args( ( array ) $instance, array( 'themeName' => '' ) );
		$themeName = esc_attr( $instance['themeName'] );
		
		//call functions class and use get method to retrieve list of themes
		$brickset = new BricksetAPISearch;
		$themes = $brickset->get_themes();
		
		
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'themeName' ); ?>"><?php _e( 'Theme Name', 'bs_api' ); ?>:</label>
			<?php
				// check for errors
				if( is_wp_error( $brickset ) )
				{
					echo $brickset->get_error_message();
				}
				else
				{
					?>
					<select class="widefat" id="<?php echo $this->get_field_id( 'themeName' ); ?>" name="<?php echo $this->get_field_name( 'themeName' ); ?>">
					<?php
					foreach ( $themes as $theme )
					{
						?>
						<option value="<?php echo $theme->theme; ?>" <?php selected( $themeName, $theme->theme ); ?>><?php echo $theme->theme; ?></option>
						<?php
					}
					?>
					</select>
					<?php
				}
			?>
		</p>
		<?php
	}
	
	/** 
	 *	Updates any options used to customize the widget.
	 *
	 *	@author		Nate Jacobs
	 *	@date		09/13/13
	 *	@since		1.4
	 *
	 *	@param		array
	 *	@param		array
	 */
	public function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['themeName'] = strip_tags( $new_instance['themeName'] );
		return $instance;
	}
	
	/** 
	 *	Generates the widget that displays the themes as a list of links.
	 *
	 *	@author		Nate Jacobs
	 *	@date		09/13/13
	 *	@since		1.4
	 *
	 *	@param		array
	 *	@param		array
	 */
	public function widget( $args, $instance )
	{
		extract( $args );
		$themeName = apply_filters('widget_title', $instance['themeName'] );

		echo $before_widget;
		if ( $themeName )
			echo $before_title . $themeName . ' - '. __( ' Years Available', 'bs_api' ) . $after_title;
		//call functions class and use get method to retrieve list of years the theme was available
		$brickset = new BricksetAPISearch;
		$years = $brickset->get_theme_years($themeName);
		
		// check for errors
		if( is_wp_error( $brickset ) )
		{
			echo $brickset->get_error_message();
		}
		else
		{
			foreach ( $years as $year )
			{
				$url = "http://brickset.com/browse/years/?year=$year->year&Theme=".urlencode($year->theme);
				
				echo __( 'Year', 'bs_api' ).': '.$year->year;
				echo '<br>';
				echo __( 'Number of Sets', 'bs_api' ).': <a href='.$url.'>'.$year->setCount.'</a>';
				echo '<hr>';
			}
				
			echo $after_widget;
		}
	}
}