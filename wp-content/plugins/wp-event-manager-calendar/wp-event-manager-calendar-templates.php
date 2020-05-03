<?php

/**
* add the calendar icon next to the box and line icon
*Add Calendar icon beside Line and Box icon at [events] shortcode page 
*/
function add_event_listing_calendar_layout_icon()
{
	wp_enqueue_style('wp-event-manager-calendar-frontend');
	wp_enqueue_script('wp-event-manager-calendar-event-calender-ajax-filters');
  echo '<div class="wpem-event-layout-icon wpem-event-calendar-layout" id="wpem-event-calendar-layout"><i class="wpem-icon-calendar"></i></div>';
}


function add_calendar_class_default_listing_layout($layout_class,$layout_type){
    if($layout_type == 'calendar' )
        $layout_class = 'wpem-row wpem-event-listing-calendar-view';
    return $layout_class;
}
add_filter('wpem_default_listing_layout_class','add_calendar_class_default_listing_layout',20,2);

/**
 * Show event calendar layout with line and box layout
 */    
function events_calendar_layout() 
{  
	       ob_start();
	       
	       // default month and year
	       $time  = current_time('timestamp');
	       $day   = date('j', $time);	       
	       $current_month = date('n', $time);
	       $current_year  = date('Y', $time);
	       $today = date('j', $time);
	       $today_month = date('m', $time);
	       $today_year = date('Y', $time);	
	       $template='events-calendar-layout-month.php';
	      
	       $search_location   = isset( $_REQUEST['search_location'] ) ? sanitize_text_field( stripslashes( $_REQUEST['search_location'] ) ) : '';
		   $search_keywords   = isset( $_REQUEST['search_keywords'] ) ? sanitize_text_field( stripslashes( $_REQUEST['search_keywords'] ) ) : '';
		   $search_datetimes= isset( $_REQUEST['search_datetimes'] ) ? $_REQUEST['search_datetimes'] : '';
		   $search_categories = isset( $_REQUEST['search_categories'] ) ? $_REQUEST['search_categories'] : '';
		   $search_event_types= isset( $_REQUEST['search_event_types'] ) ? $_REQUEST['search_event_types'] : '';
		   $search_ticket_prices= isset( $_REQUEST['search_ticket_prices'] ) ? $_REQUEST['search_ticket_prices'] : '';	
		   $search_tags = isset( $_REQUEST['search_tags'] ) ? $_REQUEST['search_tags'] : '';		
		
		if ( is_array( $search_datetimes) ) {

			$search_datetimes= array_filter( array_map( 'sanitize_text_field', array_map( 'stripslashes', $search_datetimes) ) );

		} else {

			$search_datetimes= array_filter( array( sanitize_text_field( stripslashes( $search_datetimes) ) ) );
		}

		if ( is_array( $search_categories ) ) {

			$search_categories = array_filter( array_map( 'sanitize_text_field', array_map( 'stripslashes', $search_categories ) ) );

		} else {

			$search_categories = array_filter( array( sanitize_text_field( stripslashes( $search_categories ) ) ) );
		}

		if ( is_array( $search_event_types) ) {

			$search_event_types= array_filter( array_map( 'sanitize_text_field', array_map( 'stripslashes', $search_event_types) ) );

		} else {

			$search_event_types= array_filter( array( sanitize_text_field( stripslashes( $search_event_types) ) ) );
		}		

		if ( is_array( $search_ticket_prices) ) {

			$search_ticket_prices= array_filter( array_map( 'sanitize_text_field', array_map( 'stripslashes', $search_ticket_prices) ) );

		} else {

			$search_ticket_prices= array_filter( array( sanitize_text_field( stripslashes( $search_ticket_prices) ) ) );
		}
		if ( is_array( $search_tags ) ) {
		    
		    $search_tags = array_filter( array_map( 'sanitize_text_field', array_map( 'stripslashes', $search_tags ) ) );
		    
		} else {
		    
		    $search_tags = array_filter( array( sanitize_text_field( stripslashes( $search_tags ) ) ) );
		}
		$args = array(

			'search_location'    => $search_location,
			'search_keywords'    => $search_keywords,
			'search_datetimes'  => $search_datetimes,
			'search_categories'  => $search_categories,
			'search_event_types'  => $search_event_types,
			'search_ticket_prices'  => $search_ticket_prices,
		    'search_tags'  => $search_tags
		);

		 $events = get_event_listings( $args  );
		 $calender_filter_form = FALSE;
		
		  //for selection of the template and layout structure
		  if (!empty($search_datetimes))
		  {
			   if($search_datetimes[0] == 'datetime_today' || $search_datetimes[0] == 'datetime_tomorrow' || $search_datetimes[0] == 'datetime_thisweek' ||
			   $search_datetimes[0] == 'datetime_thisweekend' || $search_datetimes[0] == 'datetime_thismonth' ||
			   $search_datetimes[0] == 'datetime_nextweek' || $search_datetimes[0] == 'datetime_nextweekend' || $search_datetimes[0] == 'datetime_nextmonth')
			   {
			     
			      if ($search_datetimes[0] == 'datetime_nextmonth')		      
			          $current_month=$current_month+1;
			      
			   }		   
			   else if ($search_datetimes[0] == 'datetime_nextyear' || $search_datetimes[0] == 'datetime_thisyear' )
			   {
			      if ($search_datetimes[0] == 'datetime_nextyear')
			          $current_year=$current_year+1;
			      $calender_filter_form = TRUE;
			      $template='events-calendar-layout-year.php';
			   }
		   }
		   else 
		   {
		       //$calender_filter_form = TRUE;
		     array_push($search_datetimes,'datetime_any');
		   }
		
		  $week_days_name = array(
					0 => __('Sunday', 'wp-event-manager-calendar'),  //start day of week : 0
					1 => __('Monday', 'wp-event-manager-calendar'),
					2 => __('Tuesday', 'wp-event-manager-calendar'),
					3 => __('Wednesday', 'wp-event-manager-calendar'),
					4 => __('Thursday', 'wp-event-manager-calendar'),
					5 => __('Friday', 'wp-event-manager-calendar'),
					6 => __('Saturday', 'wp-event-manager-calendar') //last day of week : 6
				  );
				  
		$months_name = array(
					1 => __('January', 'wp-event-manager-calendar'),  
					2 => __('February', 'wp-event-manager-calendar'),
					3 => __('March', 'wp-event-manager-calendar'),
					4 => __('April', 'wp-event-manager-calendar'),
					5 => __('May', 'wp-event-manager-calendar'),
					6 => __('June', 'wp-event-manager-calendar'),
					7 => __('July', 'wp-event-manager-calendar'),
					8 => __('August', 'wp-event-manager-calendar'),
					9 => __('September', 'wp-event-manager-calendar'),
					10 => __('October', 'wp-event-manager-calendar'),
					11 => __('November', 'wp-event-manager-calendar'),
					12 => __('December', 'wp-event-manager-calendar')
					 
				  );


	        //for applied filter value, get starting week day name for the first week of the selected month.
		//find first starting week day like sunday or monday for the month so we can start to draw calendar from  this week day.
		$first_day_of_first_week_of_selected_month= date( 'w', mktime( 0, 0, 0, $current_month, 1, $current_year ) );	
	      
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
		
	        $total_days_of_the_selected_month = date( 't', mktime( 0, 0, 0, $current_month, 1, $current_year ) );
		
	        if($calender_filter_form===TRUE){
	           get_event_manager_template($template, array(
	        								'today'                         => $today,
	        								'today_month'                   => $today_month,
	        								'today_year'                    => $today_year,
    										'current_selected_filter'       => WP_Event_Manager_Filters::get_datetime_value($search_datetimes[0]),																				
    										'week_days_name'  		=> $week_days_name,
    										'months_name'			=> $months_name,
    										'show_empty_gray_background_untill' => $show_empty_gray_background_untill,
    										'total_days_of_the_selected_month' => $total_days_of_the_selected_month,
    										'events'			=> $events
												                		
								     ), 'wp-event-manager-calendar', EVENT_MANAGER_CALENDAR_PLUGIN_DIR . '/templates/' );
		
	        }else{
	            echo do_shortcode("[events_calendar month=".$current_month." year=". $current_year." ]");
	        }
		   $event_listings_output = apply_filters( 'event_manager_event_listings_output', ob_get_clean() );
		   wp_send_json($event_listings_output);
    	      
}    	 