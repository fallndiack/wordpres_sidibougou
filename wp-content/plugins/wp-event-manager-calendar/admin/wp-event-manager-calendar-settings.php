<?php
/*
* This file use for setings at admin site for calendar settings.
* This setting to show and hide calendar layout at events page.
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WP_Event_Manager_Sell_Tickets_Settings class.
 */
class WP_Event_Manager_Calendar_Settings {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() 
    {		
		add_filter( 'event_manager_settings', array( $this, 'event_calendar_settings' ) );
		add_filter( 'wp_event_manager_colors_settings', array( $this, 'wp_event_calendar_colors_settings' ) );
	}
	
	
	/**
	 * export settings function.
	 *
	 * @access public
	 * @return void
	 */
	public function event_calendar_settings($settings) {
		$settings[ 'event_calendar' ] =  array(
                        __( 'Calendar', 'wp-event-manager-calendar' ),
                        array(
                            array(
									'name'       => 'event_manager_calendar_layout',
		
									'std'        => '1',
									
									'cb_label'   => __( '', 'wp-event-manager-calendar' ),
		
									'label'      => __( 'Show calendar layout', 'wp-event-manager-calendar' ),
		
									'desc'       => __( 'You can show or hide calendar layout from [events] page.', 'wp-event-manager-calendar' ),
		
									'type'       => 'checkbox'
                            ),	
                        )
				 );   
		
		$settings['event_pages'][1][] = array(
												'name' 		=> 'event_manager_calendar_page_id',
												'std' 		=> '',
												'label' 	=> __( 'Calendar Page', 'wp-event-manager-calendar' ),
												'desc'		=> __( 'Set calendar page to link calendar shortcode, you must select the page where you have placed the [events_calendar] shortcode.', 'wp-event-manager-calendar' ),
												'type'      => 'page'
										);
         return $settings;		                                                          
	}
	
	/**
	 * wp_event_calendar_settings function.
	 *
	 * @access public
	 * @return void
	 */
	public function wp_event_calendar_colors_settings($fields)
	{
		$fields[] = array(
				'name' 		  => 'event_manager_calendar_background_color',
				'std' 		  => '',
				'placeholder' => '#',
				'type' 		  => 'select',
				'label' 	  => __('Apply colors to Calendar','wp-event-manager-calendar'),
				'desc'		  => __( 'Hex value for the Background color of event link on calendar.', 'wp-event-manager-calendar' ),
				'options'  => array(
						'event_type_colors' 		=> __('Event type colors','wp-event-manager-calendar'),
						'event_category_colors'     => __('Event category colors','wp-event-manager-calendar')
				)
		);
		return $fields;
	}
}
new WP_Event_Manager_Calendar_Settings();