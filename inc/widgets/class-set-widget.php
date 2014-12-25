<?php

//hook into the 'widgets_init' action to load the widget
add_action( 'widgets_init', 'brickset_register_set_widget' );

/** 
 *	Registers the widget
 *
 *	@author		Nate Jacobs
 *	@date		6/9/13
 *	@since		1.3
 *
 *	@param		null
 */
function brickset_register_set_widget()
{
	register_widget( 'BricksetSetWidget' );
}

/** 
 *	Creates a widget that generates a list of themes as maintained 
 *	by Brickset.com. The list is returned as links pointing back to the theme page
 *	on Brickset.
 *
 *	@author		Nate Jacobs
 *	@date		6/9/13
 *	@since		1.3
 */
class BricksetSetWidget extends WP_Widget
{
	/** 
	 *	Generate options for the widget display in the admin dashboard.
	 *
	 *	@author		Nate Jacobs
	 *	@date		6/9/13
	 *	@since		1.3
	 */
	public function __construct()
	{
		$options = array( 'description' => __( 'A widget that display the details of a particular set from the Brickset.com database.', 'bs_api' ), 'classname' => 'brickset_set_details' );
		parent::__construct('brickset_set_widget', $name = __( 'Brickset Set Details', 'bs_api' ), $options );
	}
	
	/** 
	 *	Create the necessary form to customize the widget.
	 *
	 *	@author		Nate Jacobs
	 *	@date		6/9/13
	 *	@since		1.3
	 *
	 *	@param		array
	 */
	public function form( $instance )
	{
		$instance = wp_parse_args( ( array ) $instance, array( 'set_number' => __( 'Set Number', 'bs_api' ) ) );
		$title = esc_attr( $instance['set_number'] );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'set_number' ); ?>"><?php _e( 'Set Number', 'bs_api' ); ?>:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'set_number' ); ?>" name="<?php echo $this->get_field_name( 'set_number' ); ?>" type="text" value="<?php echo $title; ?>">
		</p>
		<?php
	}
	
	/** 
	 *	Updates any options used to customize the widget.
	 *
	 *	@author		Nate Jacobs
	 *	@date		6/9/13
	 *	@since		1.3
	 *
	 *	@param		array
	 *	@param		array
	 */
	public function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['set_number'] = strip_tags( $new_instance['set_number'] );
		return $instance;
	}
	
	/** 
	 *	Generates the widget that displays the set details.
	 *
	 *	@author		Nate Jacobs
	 *	@date		6/9/13
	 *	@since		1.0
	 *
	 *	@param		array
	 *	@param		array
	 */
	public function widget( $args, $instance )
	{
		extract( $args );
		$title = apply_filters('widget_title', $instance['set_number'] );

		echo $before_widget;
		if ( $title )
			echo $before_title . 'Set Number: '.$title . $after_title;
		//call functions class and use get method to retrieve list of themes
		$brickset = new BricksetAPISearch;
		$set = $brickset->get_by_number( $instance['set_number'] );
		
		// check for errors
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
			
			foreach( $set as $result )
			{
				$number = sanitize_text_field( $result->number );
				$numberVariant = sanitize_text_field( $result->numberVariant );
				
				if( true === $settings['bricklink'] )
				{
					$bricklink = '<strong>'.__( 'BrickLink', 'bs_api' ).': </strong><a href=http://www.bricklink.com/catalogItem.asp?S='.$number.'-'.$numberVariant.'>BrickLink</a><br><hr>';
				}
				elseif( false === $settings['bricklink'] )
				{
					$bricklink = '';
				}
				
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
				
			echo $after_widget;
		}
	}
}
