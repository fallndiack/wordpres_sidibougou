<?php 
$monthlyEventsCount=array();
for( $month= 1; $month<= 12; $month++ ) {
    $monthlyEventsCount[$month]=0;
}             
while ( $events->have_posts() ) : $events->the_post(); 	   
 //timestamp for start date
 $event_start_date = get_event_start_date();
 $timestamp = strtotime($event_start_date );				
 //define start date			
 $event_start_month 	= date( 'n', $timestamp );	
 $monthlyEventsCount[$event_start_month]=$monthlyEventsCount[$event_start_month] +1;
endwhile;  
?>
<?php do_action('before_calendar'); ?>

 <div id="calendar-layout-container" class="wpem-main">
 <div class="wpem-calendar-view-container">
  <div id="calendar-layout-header" class="calendar-layout-header">   
    <h2 class="calendar-title"><?php echo esc_html( $current_selected_filter ); ?></h2>
  </div> 
    
  <div id="calendar-contents-container" class="calendar-contents-container">
      
      <table cellpadding="0" cellspacing="0" class="calendar">
         <tr>
           <?php  for( $month= 1; $month<= 6; $month++ ) { ?> <th class="weekday-name-column"> <?php echo $months_name[$month]; } ?> </th>
         </tr>
         <tr>
             <?php 
              for( $start_month= 1; $start_month <= 6; $start_month++ ) { $today_class = ( $today_month== $start_month) ? 'today' : ''; ?>              
              <td class="calendar-day-container <?php echo $today_class; ?>" valign="top">                
                  <div class="day-number"><?php echo $start_month;?></div>   
                   <span class="text-center">
                   <?php if($monthlyEventsCount[$start_month] >0) { echo $monthlyEventsCount[$start_month];}  ?>
                   </span>               
              </td>
	     <?php } ?> <!-- end start_month loop -->   
	  </tr>
	 <tr>
           <?php  for( $month= 7; $month<= 12; $month++ ) { ?> <th class="weekday-name-column"> <?php echo $months_name[$month]; } ?> </th>
     </tr>
     <tr>               
            <?php              
              for( $start_month= 7; $start_month <= 12; $start_month++ ) { $today_class = ( $today_month== $start_month) ? 'today' : ''; ?>              
              <td class="calendar-day-container <?php echo $today_class; ?>" valign="top">                
                   <div class="day-number"><?php echo $start_month;?></div>   
                   <span class="text-center">
                   <?php if($monthlyEventsCount[$start_month] >0) { echo $monthlyEventsCount[$start_month];}  ?>
                   </span>    
              </td>
	     <?php } ?> <!-- end start_month loop -->   
	 </tr>   
   </table>    
  </div>
 </div>	
</div> <!-- calendar-container -->

<?php do_action('after_calendar');  ?>