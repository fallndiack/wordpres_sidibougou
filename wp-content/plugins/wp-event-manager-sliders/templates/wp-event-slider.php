<div class="wpem-prime-event-slider-wrapper wpem-main">
  <?php while ( $events->have_posts() ) : $events->the_post();?>		
	<div class="wpem-prime-event-slider-item">
  		<div class="wpem-prime-event-slider-content">
  			<div class="wpem-prime-event-slider-image">
  				<a href="<?php the_permalink(); ?>">
  					<?php display_event_banner(); ?>
  				</a>
  			</div>
  			<div class="wpem-prime-event-slider-description">
  				<div class="wpem-event-details">
	                <div class="wpem-event-title">
	                	<h3 class="wpem-heading-text"><a href="<?php the_permalink(); ?>"><?php echo substr(the_title(),0,32); ?></a></h3>
	                </div>
	                <div class="wpem-event-organizer">
	                	<div class="wpem-event-organizer-name">
	                		<a href="#wpem_organizer_profile">by <?php display_organizer_name(); ?></a>
	                	</div>
	                </div>
		          <?php if(get_event_ticket_option()){  ?>
	              <div class="wpem-event-ticket-type" class="wpem-event-ticket-type-text"><span class="wpem-event-ticket-type-text"><?php echo '#'.get_event_ticket_option(); ?></span></div>
	              <?php } ?>
	            </div>
	            <div class="wpem-event-description">
	            	<div class="wpem-event-description-content">
		            	<?php 
						       $organizerDescription= str_replace( '[nl]', "\n", sanitize_text_field( str_replace( "\n", '[nl]', strip_tags( stripslashes(get_event_description() ) ) ) ) );
						       echo substr($organizerDescription,0,50); 
						 ?>
					</div>
					<div class="wpem-event-description-url">
						<a class="smartex" href="<?php the_permalink(); ?>"><?php _e( 'Read More', 'wp-event-manage-sliders' ); ?> </a> 
					</div>
	            </div>
  			</div>
  		</div>
  	</div>
	<?php endwhile; ?>
</div>
<?php 
$dots = is_bool($dots) ? $dots : 0;
$navigation = is_bool($navigation) ? $navigation : 0;?>
<script>
jQuery(document).ready(function(){
	jQuery('.wpem-prime-event-slider-wrapper').slick({
		navigation	: <?php echo $navigation;?>,
        dots        : true,
        prevArrow   : '<div class="slick-prev"><i class="wpem-icon-arrow-left2"></i></div>',
		nextArrow   : '<div class="slick-next"><i class="wpem-icon-arrow-right2"></i></div>',
		 adaptiveHeight: false
		
	});	
});

</script>
