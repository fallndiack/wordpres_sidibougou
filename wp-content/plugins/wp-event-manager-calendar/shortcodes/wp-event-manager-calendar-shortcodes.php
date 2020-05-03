<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * WP_Event_Manager_Calendar_Shortcodes class.
 */

class WP_Event_Manager_Calendar_Shortcodes {
    
    /**
	 * Constructor
	 */
	public function __construct() 
	{
	    //shortcodes
	    add_shortcode( 'events_calendar', array($this, 'events_calendar') );	    
	    add_shortcode('events_calendar_widget', array($this, 'events_calendar_widget') );
	    
	    //End points for ajax call when calendar filter apply
	    add_action( 'event_manager_ajax_events_calendar', array( $this, 'events_calendar' ) );	    
	    add_action( 'event_manager_ajax_events_calendar_widget', array( $this, 'events_calendar_widget' ) );
	}
	
	 /**
	 * Event Calendar shortcode
	 */    
	public  function events_calendar($atts) 
	 {  
	 	if(!wp_style_is('wp-event-manager-calendar-frontend','enqueued'))
	 		wp_enqueue_style('wp-event-manager-calendar-frontend');
	 		
	 	if(!wp_script_is('wp-event-manager-calendar-event-calender-ajax-filters','enqueued'))
	 		wp_enqueue_script('wp-event-manager-calendar-event-calender-ajax-filters');
	 	 	
	       ob_start();
	       
	       if(isset($_GET['date'])){
	           $timestamp = strtotime($_GET['date']);
	           //define start date
	           $day 	= date( 'j', $timestamp );
	           $month 	= date( 'n', $timestamp );
	           $year 	= date( 'Y', $timestamp );
	       }else{
	           // default month and year
	           $time  = current_time('timestamp');
	           $day   = date('j', $time);
	           $month = date('n', $time);
	           $year  = date('Y', $time);
	       }
	       
	       //For Event Calender Layout
	       extract( shortcode_atts( apply_filters('event_manager_event_calender_default', array('month'  => $month, 'year' => $year,'category'=>'','event_type' => '') ), $atts ));	       
	       
	       //check filter applied or not
	       if(isset($_REQUEST['events_calendar_nonce']) && wp_verify_nonce($_REQUEST['events_calendar_nonce'], 'events_calendar_nonce')) 
	       {
    		    $month		= isset( $_REQUEST['calendar_month'] )         ? absint( $_REQUEST['calendar_month'] )         : date( 'n' );
    		    $year 		= isset($_REQUEST['calendar_year']  )          ? absint( $_REQUEST['calendar_year']  )          : date( 'Y' );
    		    $calendar_navigation_month= isset(  $_REQUEST['calendar_navigation_month']) ? absint( $_REQUEST['calendar_navigation_month']) : date( 'n' );
    		    
    		    if( isset( $_REQUEST['calendar_navigation'] ) &&  ( $_REQUEST['calendar_navigation'] == 'calendar_navigation_previous')) 
    		    {
        			if($calendar_navigation_month== 1)
        			{
        			   $year=$year -1;
        			   $month=12;
        			}
        			else
        			{
        			   $month=$month-1;
        			}
    		    } 
    		    elseif( isset( $_REQUEST['calendar_navigation'] ) && ( $_REQUEST['calendar_navigation'] == 'calendar_navigation_next')) 
    		    {
        			if($calendar_navigation_month== 12)
        			{
        			   $year=$year +1;
        			   $month=1;
        			}
        			else
        			{
        			   $month=$month+1;			   
        			}
		        }	    

		        wp_send_json( WP_Event_Manager_Calendar_Shortcodes::events_calendar_data('events-calendar.php', $month,$year,FALSE,$category,$event_type ) );
    		}
    		else
    		{ 
    			return WP_Event_Manager_Calendar_Shortcodes::events_calendar_data('events-calendar.php', $month,$year,FALSE,$category,$event_type );
    		}
    	      
     }    	 
    	
     
     /**
	 * Event Calendar widget shortcode
	 */   
	 public  function events_calendar_widget( ) 
	 {  
	 	if(!wp_style_is('wp-event-manager-calendar-frontend','enqueued'))
	 		wp_enqueue_style('wp-event-manager-calendar-frontend');
	 		
	 	if(!wp_script_is('wp-event-manager-calendar-event-calender-ajax-filters','enqueued'))
	 		wp_enqueue_script('wp-event-manager-calendar-event-calender-ajax-filters');
	 			
	       ob_start();
	       
	       if(isset($_GET['date'])){
	           $time = strtotime($_GET['date']);
	           //define start date
	           $day 	= date( 'j', $time );
	           $month 	= date( 'n', $time );
	           $year 	= date( 'Y', $time );
	       }else{
	           // default month and year
	           $time  = current_time('timestamp');
	           $day   = date('j', $time);
	           $month = date('n', $time);
	           $year  = date('Y', $time);
	       }
	      
	       
	       //check filter applied or not
	       if(isset($_REQUEST['events_calendar_widget_nonce']) && wp_verify_nonce($_REQUEST['events_calendar_widget_nonce'], 'events_calendar_widget_nonce')) 
	       {
		    $month		= isset( $_REQUEST['calendar_month'] )         ? absint( $_REQUEST['calendar_month'] )         : date( 'n' );
		    $year 		= isset($_REQUEST['calendar_year']  )          ? absint( $_REQUEST['calendar_year']  )          : date( 'Y' );
		    $calendar_navigation_month= isset(  $_REQUEST['calendar_navigation_month']) ? absint( $_REQUEST['calendar_navigation_month']) : date( 'n' );
		    
		    if( isset( $_REQUEST['calendar_navigation'] ) &&  ( $_REQUEST['calendar_navigation'] == 'calendar_widget_navigation_previous')) 
		    {
			if($calendar_navigation_month== 1)
			{
			   $year=$year -1;
			   $month=12;
			}
			else
			{
			   $month=$month-1;
			}
		    } 
		    elseif( isset( $_REQUEST['calendar_navigation'] ) && ( $_REQUEST['calendar_navigation'] == 'calendar_widget_navigation_next')) 
		    {
			if($calendar_navigation_month== 12)
			{
			   $year=$year +1;
			   $month=1;
			}
			else
			{
			   $month=$month+1;			   
			}
		    }	    
		    
		    wp_send_json( WP_Event_Manager_Calendar_Shortcodes::events_calendar_data('events-calendar-widget.php', $month,$year,TRUE) );
		}	
		else
		{	
	           return WP_Event_Manager_Calendar_Shortcodes::events_calendar_data('events-calendar-widget.php', $month,$year,TRUE);
	        }
    	   
        }      
        
        
        public function events_calendar_data($template,$month,$year,$isWidget, $category ='',$event_type = '' )
        {
                $time  = current_time('timestamp');
                $today = date('j', $time);
	        $today_month = date('m', $time);
	        $today_year = date('Y', $time);
	        $message='';
	       
                $prev_month = $month- 1;
		$prev_month = $prev_month < 1 ? 12 : $prev_month;
		$prev_year  = $prev_month < 1 ? $year - 1 : $year ;		
		
		$next_month = $month + 1;
		$next_month = $next_month > 12 ? 1 : $next_month;
		$next_year  = $next_month > 12 ? $year + 1 : $year ;
		
		//For events_calendar_data need full name of the week day.
		//For events_calendar_widget need just short name of the week day.
		if($isWidget == TRUE )
		{
		    	$week_days_name = array(
					0 => __('Sun', 'wp-event-manager-calendar'),  //start day of week : 0
					1 => __('Mon', 'wp-event-manager-calendar'),
					2 => __('Tue', 'wp-event-manager-calendar'),
					3 => __('Wed', 'wp-event-manager-calendar'),
					4 => __('Thu', 'wp-event-manager-calendar'),
					5 => __('Fri', 'wp-event-manager-calendar'),
					6 => __('Sat', 'wp-event-manager-calendar') //last day of week : 6
				  );
		}
		else
		{
		
		$week_days_name = array(
					0 => __('Sunday', 'wp-event-manager-calendar'),  //start day of week : 0
					1 => __('Monday', 'wp-event-manager-calendar'),
					2 => __('Tuesday', 'wp-event-manager-calendar'),
					3 => __('Wednesday', 'wp-event-manager-calendar'),
					4 => __('Thursday', 'wp-event-manager-calendar'),
					5 => __('Friday', 'wp-event-manager-calendar'),
					6 => __('Saturday', 'wp-event-manager-calendar') //last day of week : 6
				  );
		}


	        //for applied filter value, get starting week day name for the first week of the selected month.
		//find first starting week day like sunday or monday for the month so we can start to draw calendar from  this week day.
		$first_day_of_first_week_of_selected_month= date( 'w', mktime( 0, 0, 0, $month, 1, $year ) );	
	      
		//$week_days_name array contains starting value sunday
		//At calendar view, we have to show starting week day as monday so monday index must start with 0
		//monday week day will show first element in the calendar the week days display header row
		$start_day_of_week = $week_days_name[1];
		//array shift will shift first element of the array and it return shifted value.
		$sunday=array_shift($week_days_name);
		$week_days_name[] = $sunday ;	
		
		//if first week day of first week of the month is sunday : 0
		//then we have to show empty box in calendar untill reach sunday, means untill 6 value we have to show empty box.
	        if($first_day_of_first_week_of_selected_month== 0) 
	        {
	            $show_empty_gray_background_untill = 6;
	        }
	        else 
	        {
	           $show_empty_gray_background_untill = $first_day_of_first_week_of_selected_month- 1;
	        }	
		
	        $total_days_of_the_selected_month = date( 't', mktime( 0, 0, 0, $month, 1, $year ) );	
	        
	        $args = array(
	            'post_type'   => 'event_listing',
	            'post_status' => 'publish',
	            'posts_per_page' => -1
	        );
	        $args['meta_query'] =array(
	            
	            'meta_query' => array(
	                'relation' => 'AND',
	                
	                array(
	                    
	                    'key'     => '_event_start_date',
	                    'value'   => $month,
	                    'compare' => 'LIKE'
	                    
	                ),
	                array (
	                    
	                    'key'     => '_event_start_date',
	                    'value'   => $year,
	                    'compare' => 'LIKE'
	                )
	            )
	        ) ;
	        if(!empty($event_type)){
	        	$args['tax_query'][]  = array (
	        			'taxonomy' => 'event_listing_type',
	        			'field' => 'slug',
	        			'terms' => $event_type,
	        	);
	        }
	        if(!empty($category)){
	        	$args['tax_query'][]  = array(
	        			'taxonomy' => 'event_listing_category',
	        			'field' => 'slug',
	        			'terms' => $category,
	        	);
	        }
	        $events = new WP_Query( $args );
	        
	        wp_enqueue_style('wp-event-manager-calendar-frontend');
	        wp_enqueue_script('wp-event-manager-calendar-event-calender-ajax-filters');
	        
	        get_event_manager_template($template, array(
	        								'today'                         => $today,
	        								'today_month'                   => $today_month,
	        								'today_year'                    => $today_year,
										'selected_month'                => $month,
										'selected_year'                 => $year,
										'prev_month'			=> $prev_month,
										'prev_year'			=> $prev_year,
										'next_month'			=> $next_month,
										'next_year'			=> $next_year,
										'message'			=> $message,
										'week_days_name'  		=> $week_days_name,
										'show_empty_gray_background_untill' => $show_empty_gray_background_untill,
										'total_days_of_the_selected_month' => $total_days_of_the_selected_month,
										'events'			=> $events
												                		
								     ), 'wp-event-manager-calendar', EVENT_MANAGER_CALENDAR_PLUGIN_DIR . '/templates/' );	

		
	    
	        wp_reset_query();
	    return ob_get_clean();
        }
        
        
}    

new WP_Event_Manager_Calendar_Shortcodes();