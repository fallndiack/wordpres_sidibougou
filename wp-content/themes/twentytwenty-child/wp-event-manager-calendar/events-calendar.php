<?php
global $post;
do_action('before_calendar');
$color_setting = get_option('event_manager_calendar_background_color');

?>

 <div id="calendar-container" class="wpem-main">
 	<div class="wpem-calendar-view-container">

  <div id="calendar-filters-container" class="calendar-filters-container">   
    <h2 class="calendar-title wpem-heading-text"><?php  echo empty($message) ? esc_html( get_month_name_from_month_number($selected_month) . ' ' . $selected_year ) :  $message; ?></h2>

    <div id="calendar-loader-container"></div>

    <div class="calendar-filters-form-wrapper wpem-form-wrapper">

    	<form id="calendar-filters-form" class="calendar-filters-form">
		    <div class="wpem-row">
		    	<div class="wpem-col-sm-6 wpem-col-md-7">
		    		<div class="wpem-calendar-month-filter">
		    			<div class="wpem-form-group">
			    			<select id="calendar_month">
					         <?php
					              for($month=1;  $month<=12; $month++ ) {
					                echo '<option value="' . absint( $month) . '" ' . selected( $month, $selected_month, false ) . '>'. esc_attr( get_month_name_from_month_number($month) ) .'</option>';
					              }
					          ?>
					        </select>
					    </div>
		    		</div>
		    		<div class="wpem-calendar-year-filter">
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
		    		</div>
		    		<div class="wpem-calendar-filter-button">
		    			<input class="wpem-cfilter-button" type="button" id="event_calendar_filters_button"  value="<?php _e('OK', 'wp-event-manager-calendar'); ?>"/>        
	         			<input type="hidden" id="events_calendar_nonce"  value="<?php echo wp_create_nonce('events_calendar_nonce') ?>" />
		    		</div>
		    	</div>

		    	<div class="wpem-col-sm-6 wpem-col-md-5">
		    		
		    		<div id="calendar-filters-navigation" class="calendar-filters-navigation">

			    		<div class="wpem-calendar-left-nav">
			    			<form id="calendar-filters-navigation-previous" class="calendar-filters-navigation-previous"  method="POST" action="#events_calendar_<?php echo uniqid(); ?>">
								<input type="hidden" id="calendar_month" value="<?php echo absint( $prev_month ); ?>">
								<input type="hidden" id="calendar_year" value="<?php echo absint( $prev_year ); ?>">
								<input type="hidden" id="calendar_navigation_month" value="<?php echo absint( $selected_month ); ?>">
								<input class="wpem-cp-button" type="button" id="calendar_navigation_previous" value="<?php _e('Precedent', 'wp-event-manager-calendar'); ?>"/>
								<input type="hidden" id="events_calendar_nonce" value="<?php echo wp_create_nonce( 'events_calendar_nonce' ) ?>" />			
							</form>
			    		</div>

			    		<div class="wpem-calendar-right-nav">
			    			<form id="calendar-filters-navigation-next" class="calendar-filters-navigation-next" method="POST" action="#events_calendar_<?php echo uniqid(); ?>">
								<input type="hidden" id="calendar_month" class="month" value="<?php echo absint( $next_month ); ?>">
								<input type="hidden" id="calendar_year" class="year" value="<?php echo absint( $next_year ); ?>">
								<input type="hidden" id="calendar_navigation_month" value="<?php echo absint( $selected_month ); ?>">
								<input class="wpem-cp-button" type="button" id="calendar_navigation_next" value="<?php _e( 'Suivant', 'wp-event-manager-calendar' ); ?>"/>
								<input type="hidden" id="events_calendar_nonce" value="<?php echo wp_create_nonce( 'events_calendar_nonce' ) ?>" />
							</form>
			    		</div>

		    		</div>	

		    	</div>
		    </div>
		</form>

    </div>

  </div> <!-- end .calendar-filter-container -->
    
  <div id="calendar-contents-container" class="calendar-contents-container">
     
      <table cellpadding="0" cellspacing="0" class="calendar">
      
         <tr>
           <?php  for( $week_day = 0; $week_day <= 6; $week_day++ ) { ?> <th class="weekday-name-column"> <?php echo $week_days_name[$week_day]; } ?> </th>
         </tr>
         
         <tr>          
         <?php for( $start= 0; $start < $show_empty_gray_background_untill; $start++ ) { ?> <td class="empty-gray-background" valign="top"></td> <?php } ?>
           
            <?php                
              $count_day_box=$show_empty_gray_background_untill;              
              for( $start_day= 1; $start_day <= $total_days_of_the_selected_month; $start_day++ ) { $today_class = ( $today == $start_day && $today_month == $selected_month && $today_year == $selected_year ) ? 'today' : ''; ?> 
             
              <td class="calendar-day-container <?php echo $today_class; ?>" valign="top">
                
                  <div class="day-number"><?php echo $start_day;?></div>
                  <?php                 		 
				 
		   while ( $events->have_posts() ) : $events->the_post(); 
		   
                        //event id & title
                        $event_id=get_the_ID();
                        $event_title=get_the_title(); 
                        $color_class = '';
                        //timestamp for start date
			$event_start_date 	= get_post_meta($event_id,'_event_start_date',true);
			$event_end_date 	= get_post_meta($event_id,'_event_end_date',true);
			$timestamp = strtotime($event_start_date );
			
			//define start date
			$event_start_day 	= date( 'j', $timestamp );
			$event_start_month 	= date( 'n', $timestamp );
			$event_start_year 	= date( 'Y', $timestamp );
			
			//we check if any events exists on current iteration
			//if yes, return the link to event
			if($start_day==$event_start_day && $selected_month==$event_start_month &&  $selected_year ==$event_start_year)
			{
				if($color_setting == 'event_category_colors'){
					
					$event_catgegory = get_event_category( $post );
					if(!empty($event_catgegory))
					foreach ($event_catgegory as $value) {
						$color_class =' event-category '.$value->slug;
					}
				}
				else{
					$event_type = get_event_type( $post );
					if(!empty($event_type))
					foreach ($event_type as $value) {
						$color_class =' event-type  '.$value->slug;
					}
				}
			   ?>
			     <a href="<?php echo display_event_permalink(); ?>" id="<?php echo  $event_id; ?>" class="calendar-event-details-link <?php echo $color_class;?>">
			     <?php the_title(); ?>
			      <div id="pop_up_<?php echo  $event_id; ?>" class="calendar-tooltip-box popper">
    			        <div class="calendar-tooltip ">
    			           <div class="calendar-tooltip-banner" style="background-image: url('<?php $banner= get_event_banner(); if(is_array($banner)) echo $banner[0];else echo $banner; ?>')"></div>
                           <div class="calendar-tooltip-title wpem-heading-text"><?php the_title(); ?></div>
                           <div class="calendar-tooltip-content">
                           <p><?php _e( 'Start Date', 'wp-event-manager-calendar' ); ?>: <?php echo $event_start_date; ?><br/>
                           <?php _e( 'End Date', 'wp-event-manager-calendar' ); ?>: <?php echo $event_end_date; ?></p>
                           <p><?php printf( __('%s', 'wp-event-manager-calendar'), wp_strip_all_tags(substr(get_the_content(),0,100))); ?></p>
                           </div>                                     
                        </div>
                      </div>
                     </a><br/> 			     
		  <?php	}
                  endwhile;                   
                  ?>
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
