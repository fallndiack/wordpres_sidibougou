<?php do_action('before_calendar'); ?>

<div id="calendar-widget-container" class="wpem-main wpem-calendar-widget-wrapper" >
  <div id="calendar-filters-container" class="calendar-filters-container">   
    <h2 class="wpem-heading-text calendar-title"><?php  echo empty($message) ? esc_html( get_month_name_from_month_number($selected_month) . ' ' . $selected_year ) :  $message;  ?></h2>
    
    <form id="calendar-filters-form" class="wpem-form-wrapper calendar-filters-form" >
	    <div class="wpem-form-group">
	       <select id="calendar_month">
	         <?php
	              for($month=1;  $month<=12; $month++ ) {
	                echo '<option value="' . absint( $month) . '" ' . selected( $month, $selected_month, false ) . '>'. esc_attr( get_month_name_from_month_number($month) ) .'</option>';
	              }
	          ?>
	        </select>
		</div>
	    <div class="wpem-form-group">
	        <select id="calendar_year">
	          <?php
	                $start_year = date('Y') - 3;
	                $end_year = $start_year + 10;
	                $years = range($start_year, $end_year, 1);
	                foreach( $years as $year ) {
	                    echo '<option value="'. absint( $year ) .'" '. selected( $year, $selected_year, false ) .'>'. esc_attr( $year ) .'</option>';
	                }
	           ?>
	         </select>
	     </div>
	     <div class="wpem-form-group">
		    <input type="button" id="event_calendar_widget_filters_button" class="wpem-theme-button" value="<?php _e('OK', 'wp-event-manager-calendar'); ?>"/>
		    <input type="hidden" id="events_calendar_widget_nonce"  value="<?php echo wp_create_nonce('events_calendar_widget_nonce') ?>" />
		  </div>
    </form>

    <div id="calendar-filters-navigation" class="calendar-filters-navigation">		
		<form id="calendar-filters-navigation-previous" class="calendar-filters-arrow calendar-filters-navigation-previous"  method="POST" action="#events_calendar_<?php echo uniqid(); ?>">
			<input type="hidden" id="calendar_month" value="<?php echo absint( $prev_month ); ?>">
			<input type="hidden" id="calendar_year" value="<?php echo absint( $prev_year ); ?>">
			<input type="hidden" id="calendar_navigation_month" value="<?php echo absint( $selected_month ); ?>">
			<input type="button" id="calendar_widget_navigation_previous" class="calendar-nav-arrow wpem-filters-navigation-previous" value="<?php _e('Precedant', 'wp-event-manager-calendar'); ?>"/>
			<input type="hidden" id="events_calendar_widget_nonce" value="<?php echo wp_create_nonce( 'events_calendar_widget_nonce' ) ?>" />			
		</form>
		<form id="calendar-filters-navigation-next" class="calendar-filters-arrow calendar-filters-navigation-next" method="POST" action="#events_calendar_<?php echo uniqid(); ?>">
			<input type="hidden" id="calendar_month" class="month" value="<?php echo absint( $next_month ); ?>">
			<input type="hidden" id="calendar_year" class="year" value="<?php echo absint( $next_year ); ?>">
			<input type="hidden" id="calendar_navigation_month" value="<?php echo absint( $selected_month ); ?>">
			<input type="button" id="calendar_widget_navigation_next" class="calendar-nav-arrow wpem-filters-navigation-next" value="<?php _e( 'Suivant', 'wp-event-manager-calendar' ); ?>"/>
			<input type="hidden" id="events_calendar_widget_nonce" value="<?php echo wp_create_nonce( 'events_calendar_widget_nonce' ) ?>" />
		</form>
   </div>
  <div id="calendar-loader-container"></div>
  </div> <!-- end .calendar-filter-container -->
   
  <div class="wpem-calendar-view-container"> 
  <div id="calendar-contents-container" class="calendar-contents-container">
      
      <table cellpadding="0" cellspacing="0" class="calendar">
      <thead>
      	<tr>
           <?php  for( $week_day = 0; $week_day <= 6; $week_day++ ) { ?> <th class="weekday-name-column"> <?php echo $week_days_name[$week_day]; } ?> </th>
         </tr>
      </thead>
        <tr>          
        <?php for( $start= 0; $start < $show_empty_gray_background_untill; $start++ ) { ?> <td class="empty-gray-background" valign="top"></td> <?php } ?>   
            <?php                
              $count_day_box=$show_empty_gray_background_untill;              
              for( $start_day= 1; $start_day <= $total_days_of_the_selected_month; $start_day++ ) { $today_class = ( $today == $start_day && $today_month == $selected_month && $today_year == $selected_year ) ? 'today' : ''; ?> 
             
              <td class="calendar-day-container <?php echo $today_class; ?>" valign="top">
                
                  <div class="day-number">
                  <?php
                  
                     $count=0;
                
		   while ( $events->have_posts() ) : $events->the_post(); 
		   
                        //event id & title
                        $event_id=get_the_ID();
                        $event_title=get_the_title(); 
                        
                        //timestamp for start date
			$event_start_date = get_event_start_date();
			$event_end_date = get_event_end_date();
			$timestamp = strtotime($event_start_date );
			
			//define start date
			$event_start_day 	= date( 'j', $timestamp );
			$event_start_month 	= date( 'n', $timestamp );
			$event_start_year 	= date( 'Y', $timestamp );
			
			//we check if any events exists on current iteration
			//if yes, return the link to event
			if($start_day==$event_start_day && $selected_month==$event_start_month &&  $selected_year ==$event_start_year)
			{			     
			    $calendar_page_url=get_page_link(get_option('event_manager_calendar_page_id', true));
                  ?>
                    <a href="<?php echo add_query_arg('date', get_event_start_date(), $calendar_page_url); ?>" class="calendar-widget-event-details-link">
                        <?php if($count==0) { echo $start_day; } $count++; ?>     
                    </a>			     
		  <?php	
 		  	break;
			}
      	  endwhile;                   
                 
                 if($count==0) 
                     echo $start_day;
                    ?>
                </div>
              </td>
           
	       <?php 
	         //start new row for every monday week day
	         if($count_day_box==6) 
	         { 	            
	              ?>
	             </tr>
	              <?php
	              
	               if( $start_day!=$total_days_of_the_selected_month)
	               { ?>
	                 <tr>
	             <?php } 
	             
	              $count_day_box=0; 
	              
	          }
	          else
	          {
	            $count_day_box++;  	          
	          }          
         
	        
	        } ?> <!-- end start_day loop -->
	        
	      <!-- show empty gray background for filling whole month calendar space --> 
	      <?php for( $start= $count_day_box; $start<=6; $start++ ) { ?> <td class="empty-gray-background" valign="top"></td> <?php } ?>
	     </tr>
       </table>
  	</div>	
  </div>
</div> <!-- calendar-container -->

<?php do_action('after_calendar');  ?>
