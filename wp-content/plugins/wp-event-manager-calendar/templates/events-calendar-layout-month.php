<?php 
global $post;
do_action('before_calendar');
$color_setting = get_option('event_manager_calendar_background_color');

?>

 <div id="calendar-layout-container" class="wpem-main">
  <div class="wpem-calendar-view-container">
  <div id="calendar-layout-header" class="calendar-layout-header">   
    <h2 class="calendar-title"><?php echo esc_html( $current_selected_filter ); ?></h2>
  </div> 
  <div id="calendar-contents-container" class="calendar-contents-container">
      <table cellpadding="0" cellspacing="0" class="calendar">
      
         <tr>          
         <?php for( $start= 0; $start < $show_empty_gray_background_untill; $start++ ) { ?> <td class="empty-gray-background" valign="top"></td> <?php } ?>
           
            <?php                
              $count_day_box=$show_empty_gray_background_untill;              
              for( $start_day= 1; $start_day <= $total_days_of_the_selected_month; $start_day++ ) { $today_class = ( $today == $start_day  ) ? 'today' : ''; ?> 
             
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
			if($start_day==$event_start_day )
			{
				if($color_setting == 'event_category_colors'){
					if(get_option( 'event_manager_enable_categories' )){
						$event_catgegory = get_event_category( $post );
						if(!empty($event_catgegory))
						foreach ($event_catgegory as $value) {
							$color_class =' event-category '.$value->slug;
						}
					}
				}
				else{
					if ( get_option( 'event_manager_enable_event_types' ) ) {
						$event_type = get_event_type( $post );
						if(!empty($event_type))
						foreach ($event_type as $value) {
							$color_class =' event-type  '.$value->slug;
						}
					}
				}
			   ?>
			     <a href="<?php echo display_event_permalink(); ?>" id="<?php echo  $event_id; ?>" class="calendar-event-details-link <?php echo $color_class;?>">
			     <?php echo substr($event_title,0,13); ?>
			  		<div id="pop_up_<?php echo  $event_id; ?>" class="calendar-tooltip-box">
    			        <div class="calendar-tooltip ">
    			           <div class="calendar-tooltip-banner" style="background-image: url('<?php $banner= get_event_banner(); if(is_array($banner)) echo $banner[0];else echo $banner; ?>')"></div>
                           <div class="calendar-tooltip-title wpem-heading-text"><?php echo substr($event_title,0,13); ?></div>
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