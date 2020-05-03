<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WP_Event_Manager_Slider_Shortcodes class.
 */
class WP_Event_Manager_Sliders_Shortcodes {
	
	/**
	 * Constructor
	 */
	public function __construct() {
		
		add_shortcode( 'events_slider', array( $this, 'output_events_slider' ) );
		
	}
	
	/**
	 * output_events_slider
	 * @since 3.1.6
	 */
	public function output_events_slider($atts)
	{
		
		extract( shortcode_atts( array(
				'featured'          => false,
				'limit'             => 5,
				'orderby'			=> 'rand',
				'navigation'	    => true,
				'dots'              => false,
				'infinite'		 	=> false,
				
				
		), $atts ) );
		
		wp_localize_script( 'wp-event-manager-events-slider', 'wp_event_manager_event_slider',
				array(
						'navigation' => $navigation,
						'infinite' => $infinite,
						'dots' => $dots
				)
				);
		
		wp_enqueue_style( 'wp-event-manager-slick-style');
		wp_enqueue_style( 'wp-event-manager-sliders-frontend');
		wp_enqueue_script( 'wp-event-manager-slick-script');
		
		
		ob_start();
		$events   = get_event_listings(apply_filters( 'wp_event_manager_get_slider_listing_arg', array(
				'posts_per_page'    => $limit,
				'featured'          => $featured,
				'orderby'           => $orderby,
				'order'             => 'DESC',
				'meta_query' 		=> array(
						'key' => '_event_banner',
						'compare' => 'EXISTS',
				)
				
		) )
				);
		get_event_manager_template( 'wp-event-slider.php', array('events' => $events ,'navigation' => $navigation,'dots'=>$dots  ), 'wp-event-manager-sliders', EVENT_MANAGER_SLIDER_PLUGIN_DIR . '/templates/'  );
		
		wp_reset_postdata();
		
		return ob_get_clean();
	}
}
new WP_Event_Manager_Sliders_Shortcodes();
