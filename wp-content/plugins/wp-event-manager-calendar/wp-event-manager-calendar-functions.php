<?php

/**
* Month Number To Name
*
* Takes a month number and returns the first three letter name of the month.
* 
*/
function get_month_name_from_month_number($n) 
{
   $timestamp = mktime( 0, 0, 0, $n, 1, 2005 ); 
   return date_i18n( 'M', $timestamp);
}

/**
 * current date event via ajax
 * 
*/
function widget_current_event_detail() {
    
    $event_id=$_POST['event_id'];
    get_event_manager_template( 'content-widget-event_listing.php', array('event_id'=>$event_id), 'wp-event-manager-calendar', EVENT_MANAGER_CALENDAR_PLUGIN_DIR. '/templates/' );
    wp_die();
}


