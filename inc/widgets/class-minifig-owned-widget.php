<?php

//hook into the 'widgets_init' action to load the widget
add_action( 'widgets_init', 'brickset_register_minifig_owned_widget' );

/** 
 *	Registers the widget
 *
 *	@author		Nate Jacobs
 *	@date		9/12/13
 *	@since		1.4
 */
function brickset_register_minifig_owned_widget()
{
	register_widget( 'BricksetMinifigOwnedWidget' );
}

/** 
 *	Creates a widget that displays the total count of minifigs owned by the specified user.
 *
 *	@author		Nate Jacobs
 *	@since		1.4
 */
class BricksetMinifigOwnedWidget extends WP_Widget
{
	/** 
	 *	Generate options for the widget display in the admin dashboard.
	 *
	 *	@author		Nate Jacobs
	 *	@date		9/12/13
	 *	@since		1.4
	 */
	public function __construct()
	{
		$options = array( 'description' => __( 'A count of all the minifigs owned from Brickset for a user.', 'bs_api' ), 'classname' => 'brickset_owned_minifigs' );
		parent::__construct('brickset_minifig_owned_widget', $name = __( 'Brickset Owned Minifigs', 'bs_api' ), $options );
	}
	
	/** 
	 *	Create the necessary form to customize the widget.
	 *
	 *	@author		Nate Jacobs
	 *	@date		9/12/13
	 *	@since		1.4
	 *
	 *	@param		array
	 */
	public function form( $instance )
	{
		$instance = wp_parse_args( ( array ) $instance, array( 'title' => __( 'Owned Minifigs', 'bs_api' ) ) );
		$title = esc_attr( $instance['title'] );
		$userID = isset( $instance['user_id'] ) ? esc_attr( $instance['user_id'] ) : 'Pick a user';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'bs_api' ); ?>:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'user_id' ); ?>"><?php _e( 'Which user to display minifigs for?', 'bs_api' ); ?>:</label>
			<?php wp_dropdown_users( array( 'id' => $this->get_field_id( 'user_id' ), 'name' => $this->get_field_name( 'user_id' ), 'class' => 'widefat', 'selected' => $userID, 'show_option_none' => 'Pick a user' ) ); ?>
		</p>
		<?php
	}
	
	/** 
	 *	Updates any options used to customize the widget.
	 *
	 *	@author		Nate Jacobs
	 *	@date		9/12/13
	 *	@since		1.4
	 *
	 *	@param		array
	 *	@param		array
	 */
	public function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['user_id'] = strip_tags( $new_instance['user_id'] );
		return $instance;
	}
	
	/** 
	 *	Generates the widget that displays a count of total owned minifigs.
	 *
	 *	@author		Nate Jacobs
	 *	@date		9/12/13
	 *	@since		1.4
	 *
	 *	@param		array
	 *	@param		array
	 */
	public function widget( $args, $instance )
	{
		extract( $args );
		$title = apply_filters('widget_title', $instance['title'] );

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
		//call functions class and use get method to retrieve list of minifigs
		$brickset = new BricksetAPISearch;
		$minifigs = $brickset->get_minifig_collection( (int) $instance['user_id'], array( 'owned' => true ) );
		
		$user = get_userdata( (int) $instance['user_id']);
		
		// check for errors
		if( is_wp_error( $brickset ) )
		{
			echo $brickset->get_error_message();
		}
		else
		{
			$count = 0;
			foreach ( $minifigs as $minifig )
			{
				$count += $minifig->ownedTotal;
			}
			echo $user->display_name .' - '. $count;	
			echo $after_widget;
		}
	}
}
