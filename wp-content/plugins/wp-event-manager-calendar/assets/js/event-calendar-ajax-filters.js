var EventCalendarAjaxFilter = function(){
	
	 /// <summary>Constructor function of the event EventCalendarAjaxFilters class.</summary>
    /// <returns type="Home" />  
	return{
		
		    ///<summary>
            ///Initializes the event calendar ajax filters.  
            ///</summary>     
            ///<returns type="initialization filters" />   
            /// <since>1.0.0</since> 
			init: function() 
			{
			    Common.logInfo("EventCalendarAjaxFilter.init...");	
			    	
				    if(jQuery('#search_datetimes').length > 0 ){
				        jQuery(document).delegate('#search_datetimes','change',EventCalendarAjaxFilter.actions.filtersValueChanged);  
				    }
				    if(jQuery('#search_location').length > 0 ){
				       jQuery(document).delegate('#search_location','change',EventCalendarAjaxFilter.actions.filtersValueChanged); 
				    }
				    if(jQuery('#search_keywords').length > 0 ){
				       jQuery(document).delegate('#search_keywords','change',EventCalendarAjaxFilter.actions.filtersValueChanged); 
				    }    		    
	    		    if(jQuery('#search_categories').length > 0 ){
	    		         jQuery(document).delegate('#search_categories','change',EventCalendarAjaxFilter.actions.filtersValueChanged); 
	    		    }
	    		    if(jQuery('#search_event_types').length > 0 ){
	    		        jQuery(document).delegate('#search_event_types','change',EventCalendarAjaxFilter.actions.filtersValueChanged); 
	    		    }
	    		    if(jQuery('#search_ticket_prices').length > 0 ){
	    		        jQuery(document).delegate('#search_ticket_prices','change',EventCalendarAjaxFilter.actions.filtersValueChanged);
	    		    }
	    		 
				    //events calendar layout actions
				    jQuery(document).delegate('#wpem-event-calendar-layout','click', EventCalendarAjaxFilter.actions.calendarLayoutIconClick); 
				    jQuery(document).delegate('#wpem-event-list-layout','click',EventCalendarAjaxFilter.actions.calendarLayoutHide);
				    jQuery(document).delegate('#wpem-event-box-layout','click',EventCalendarAjaxFilter.actions.calendarLayoutHide);
				    jQuery('#event_filters').on('change',EventCalendarAjaxFilter.actions.filtersValueChanged);
				    jQuery('.event_filters').on('click', '.reset', EventCalendarAjaxFilter.actions.filtersValueChanged);
				    jQuery('.filter_by_tag_cloud').on('click', EventCalendarAjaxFilter.actions.filtersValueChanged);
				    if(jQuery('.wpem-event-listing-calendar-view').length > 0 ){
				    jQuery(window).on('load', EventCalendarAjaxFilter.actions.filtersValueChanged);
				    }
				    //events calendar shortcode actions
				   	jQuery('body').on('click', '#event_calendar_filters_button',EventCalendarAjaxFilter.actions.getFilteredCalendarData);
				   	jQuery('body').on('click', '#calendar_navigation_previous',EventCalendarAjaxFilter.actions.getFilteredCalendarData);
				   	jQuery('body').on('click', '#calendar_navigation_next',EventCalendarAjaxFilter.actions.getFilteredCalendarData);
				   	
				   	//events calendar widget actions
				   	jQuery('body').on('click', '#event_calendar_widget_filters_button',EventCalendarAjaxFilter.actions.getFilteredWidgetCalendarData);
				   	jQuery('body').on('click', '#calendar_widget_navigation_previous',EventCalendarAjaxFilter.actions.getFilteredWidgetCalendarData);
				   	jQuery('body').on('click', '#calendar_widget_navigation_next',EventCalendarAjaxFilter.actions.getFilteredWidgetCalendarData);
				   	
				    EventCalendarAjaxFilter.actions.eventDetailsPopup();
				    
				    EventCalendarAjaxFilter.actions.showWidgetEventsDetails();
				    
			},
			actions:
			{
			    ///<summary>
                ///When page refresh or window load and calendar layout is active then show data according to filters value               
                ///</summary>     
                ///<returns type="initialization filters" />   
                /// <since>1.0.0</since> 
    		    windowLoad: function (event)
    	        {  
    	            Common.logInfo("EventCalendarAjaxFilter.actions.windowLoad..."); 
    	            if(jQuery('.wpem-event-listing-calendar-view').length > 0 ){
    	                
    	                EventCalendarAjaxFilter.actions.filtersValueChanged(event);
    	            }
    	        },
    	        
    	        ///<summary>
                ///When filter value changed then refresh data for the calendar layout     
                ///When page refresh or window load and calendar layout is active then show data according to filters value        
                ///</summary>     
                ///<returns type="initialization filters" />   
                /// <since>1.0.0</since> 
    		    filtersValueChanged: function (event)
    	        {
    	            Common.logInfo("EventCalendarAjaxFilter.actions.filtersValueChanged...");
    	            
    	            if(localStorage.getItem("layout")=="calendar-layout" )
    	             {
    	                  //Only for year not show calendar on time, when select filter this year or next year.
    	                  var search_datetimes = jQuery('#search_datetimes').val();
    	                  
    	                  //wait untill completely loaded box and line layout data.
        	              setTimeout(function(){
                                EventCalendarAjaxFilter.actions.calendarLayoutIconClick(event);
                           }, 1000);               
    	             }
    	        },	
			
			    ///<summary>
                ///When click on line or box layout then hide calendar layout and data                
                ///</summary>     
                ///<returns type="initialization filters" />   
                /// <since>1.0.0</since> 
				calendarLayoutHide : function (event) 
				{
				    Common.logInfo("EventCalendarAjaxFilter.actions.calendarLayoutHide..."); 
				   	jQuery("#wpem-event-calendar-layout").removeClass("wpem-active-layout");
				   	jQuery(".wpem-event-listings").removeClass("wpem-event-listing-calendar-view");
    			  	jQuery( "#calendar-layout-view-container" ).remove();
    			   
    			 /* if(jQuery('.load_previous').length > 0 ){ jQuery('.load_previous').show();}
    			  else if(jQuery('#load_more_events').length > 0){ jQuery('#load_more_events').show(); } */
    			  	
    				if(jQuery('#load_more_events').length > 0 && localStorage.getItem("total_event_page")>localStorage.getItem("current_event_page"))
    					jQuery('#load_more_events').show();
    				else
    					jQuery('#load_more_events').hide();		
    				
    			  jQuery('.event-manager-pagination').show();

    			  event.preventDefault();
				},	
			    
			    ///<summary>
                ///When click on calendar layout icon near to box and line icon
                ///Show calendar data
                ///</summary>     
                ///<returns type="initialization filters" />   
                /// <since>1.0.0</since> 
    		    calendarLayoutIconClick: function (event)
    	        {
    	              Common.logInfo("EventCalendarAjaxFilter.actions.calendarLayoutIconClick...");     	              

    		       	
    		       	jQuery('.wpem-event-layout-action').find('.wpem-active-layout').removeClass('wpem-active-layout');
    		       	jQuery('#wpem-event-calendar-layout').addClass("wpem-active-layout");

    		       	jQuery(".wpem-event-box-col").hide();
			        jQuery("#calendar-layout-view-container").show();

			        jQuery(".wpem-event-listings").removeClass("wpem-event-listing-list-view");
			        jQuery(".wpem-event-listings").removeClass("wpem-event-listing-box-view");
			        if(!jQuery(".wpem-event-listings").hasClass('wpem-row'))
			            jQuery(".wpem-event-listings").addClass('wpem-row');
			            
			        jQuery(".wpem-event-listings").addClass("wpem-event-listing-calendar-view");

			        if(jQuery('.load_previous').length > 0 ) jQuery('.load_previous').hide();
    			  	else if(jQuery('#load_more_events').length > 0){
    			  	 	jQuery('#load_more_events').hide();
    			  	} 
    			  
    			  jQuery('.event-manager-pagination').hide();

	                localStorage.setItem("layout", "calendar-layout");
	                EventCalendarAjaxFilter.actions.getCalendarLayoutData();  	   					  
			        event.preventDefault();      
    		    },
    		    
			    ///<summary>
                ///Show event details popup when user wll mouser over event link in calendar day box.
                ///Show Event detail tooltip whene hover on event title
                ///</summary>     
                ///<returns type="initialization filters" />   
                /// <since>1.0.0</since> 
				eventDetailsPopup: function() 
				{
				    	jQuery(".calendar-event-details-link").mouseover(function(event) {   
                         var temp_id =jQuery('#'+event.target.id).children("div").attr('id'); 
                         jQuery('#'+temp_id).fadeIn("fast");
                          }).mouseout(function (event){
                         var temp_id =jQuery('#'+event.target.id).children("div").attr('id'); 
                         jQuery('#'+temp_id).fadeOut('fast');
                       });
                       
//                       jQuery(".calendar-widget-event-details-link").mouseover(function(event) {   
//                         var temp_id =jQuery('#'+event.target.id).children("div").attr('id'); 
//                         jQuery('#'+temp_id).fadeIn("fast");
//                          }).mouseout(function (event){
//                         var temp_id =jQuery('#'+event.target.id).children("div").attr('id'); 
//                         jQuery('#'+temp_id).fadeOut('fast');
//                       });
//                       
                       
                                               
                       
				},				
				
				
				///<summary>
                ///Show widget events details when hover on day box of the widget calendar               
                ///</summary>     
                ///<returns type="initialization filters" />   
                /// <since>1.0.0</since> 
				showWidgetEventsDetails: function() 
				{
				    jQuery('.current_date').on('click',EventCalendarAjaxFilter.actions.ajaxCalanderwidgetCurrentEvent);
				},	
			    
			    ///<summary>
                ///Get filtered calendar data
                ///</summary>     
                ///<returns type="initialization filters" />   
                /// <since>1.0.0</since> 
				getFilteredCalendarData: function(event) 
				{
				       Common.logInfo("EventCalendarAjaxFilter.actions.getFilteredCalendarData...");  
				       
                       var month = jQuery('#calendar_month').val();
                       var year = jQuery('#calendar_year').val();
                       var calendar_navigation_month = jQuery('#calendar_navigation_month').val();
                       var events_calendar_nonce=jQuery('#events_calendar_nonce').val();                          
                       var  data = {                            
                            calendar_month: month,
                            calendar_year: year,
                            calendar_navigation_month: calendar_navigation_month,
                            calendar_navigation: event.target.id,                           
                            events_calendar_nonce:events_calendar_nonce
                       }
                       
    				   jQuery.ajax({
									  type: 'POST',
									  url: event_manager_calendar_event_calendar_ajax_filters.ajax_url.toString().replace("%%endpoint%%", "events_calendar"),
									  data : data,									  
									  beforeSend: function() 
									  {
										   jQuery('#calendar-container').addClass('wpem-loading');
									       jQuery("#wpem-event-list-layout").prop('disabled',true);
									       jQuery("#wpem-event-box-layout").prop('disabled',true);
									  },
									  success: function(data)
									  {	
									     var parent=jQuery('#calendar-container').parent();
									     jQuery('#calendar-container').remove();
									     jQuery(parent).html(data); 
									     jQuery('#calendar-container').removeClass('wpem-loading');
									  },
									  error: function(jqXHR, textStatus, errorThrown) 
									  { 		           
								        Common.logInfo("Error:" +textStatus); 
								        jQuery('#calendar-container').removeClass('wpem-loading');
									  },
									  complete: function (jqXHR, textStatus) 
									  {
									     EventCalendarAjaxFilter.actions.eventDetailsPopup();
									     jQuery('#calendar-container').removeClass('wpem-loading');
									  }
    					          });
    					          jQuery("#wpem-event-list-layout").prop('disabled',false);
								  jQuery("#wpem-event-box-layout").prop('disabled',false);
    					        
    		      event.preventDefault();
    		    
				},
				
				
				///<summary>
                ///Get filtered widget calendar data
                ///</summary>     
                ///<returns type="initialization filters" />   
                /// <since>1.0.0</since> 
				getFilteredWidgetCalendarData: function(event) 
				{
				       Common.logInfo("EventCalendarAjaxFilter.actions.getFilteredWidgetCalendarData...");  
				       
                       var month = jQuery('#calendar_month').val();
                       var year = jQuery('#calendar_year').val();
                       var calendar_navigation_month = jQuery('#calendar_navigation_month').val();
                       var events_calendar_widget_nonce=jQuery('#events_calendar_widget_nonce').val();                          
                       var  data = {                            
                            calendar_month: month,
                            calendar_year: year,
                            calendar_navigation_month: calendar_navigation_month,
                            calendar_navigation: event.target.id,                           
                            events_calendar_widget_nonce:events_calendar_widget_nonce
                       }
                       
    				   jQuery.ajax({
									  type: 'POST',
									  url: event_manager_calendar_event_calendar_ajax_filters.ajax_url.toString().replace("%%endpoint%%", "events_calendar_widget"),
									  data : data,									  
									  beforeSend: function() 
									  {
										  jQuery('#calendar-widget-container').addClass('wpem-loading');
									  },
									  success: function(data)
									  {	
									     var parent=jQuery('#calendar-widget-container').parent();			
									     var indexOfCalendarWidget=jQuery('#calendar-widget-container').index();
									     jQuery('#calendar-widget-container').remove();	
									     //used extend jquery method for insert at, defined below in this file.
									     jQuery(parent).insertAt(indexOfCalendarWidget,data);
									     jQuery('#calendar-widget-container').removeClass('wpem-loading');
									  },
									  error: function(jqXHR, textStatus, errorThrown) 
									  { 		           
								        Common.logInfo("Error:" +textStatus); 
									  },
									  complete: function (jqXHR, textStatus) 
									  {		
									      EventCalendarAjaxFilter.actions.eventDetailsPopup();
									      EventCalendarAjaxFilter.actions.showWidgetEventsDetails();
									      jQuery('#calendar-widget-container').removeClass('wpem-loading');
									  }
    					          });
    					        
    		      event.preventDefault();
    		    
				},
				
				///<summary>
                ///Get filtered calendar data
                ///</summary>     
                ///<returns type="initialization filters" />   
                /// <since>1.0.0</since> 
				getCalendarLayoutData: function() 
				{
				       Common.logInfo("EventCalendarAjaxFilter.actions.getCalendarLayoutData...");  
				       
                       var search_keywords = jQuery('#search_keywords').val();
                       var search_location = jQuery('#search_location').val();                      
                       var search_datetimes = jQuery('#search_datetimes').val();                       
                       var search_categories=jQuery('#search_categories').val();  
                       var search_event_types=jQuery('#search_event_types').val();  
                       var search_ticket_prices=jQuery('#search_ticket_prices').val();  
                       if(jQuery("input[name='event_tag[]']").length){
						    var search_tags = jQuery("input[name='event_tag[]']")
							  .map(function(){return jQuery(this).val();}).get(); 
						}else{
							var search_tags ='';
						}
                       var  data = {                            
                            search_keywords: search_keywords,
                            search_location: search_location,
                            search_datetimes: search_datetimes,
                            search_categories: search_categories,                           
                            search_event_types:search_event_types,
                            search_ticket_prices:search_ticket_prices,
                            search_tags:search_tags
                       }
                      
    				   jQuery.ajax({
									  type: 'POST',
									  url: event_manager_calendar_event_calendar_ajax_filters.ajax_url.toString().replace("%%endpoint%%", "events_calendar_layout"),
									  data : data,									  
									  beforeSend: function() 
									  {	
									      jQuery("#wpem-event-box-layout").prop('disabled',true);
									      jQuery("#wpem-event-list-layout").prop('disabled',true);
									  },
									  success: function(data)
									  {	
									      jQuery('.event-manager-pagination').hide();									      
				                          jQuery('#event-listing-view').after().append("<div id='calendar-layout-view-container'>");
                                          jQuery('#calendar-layout-view-container').html(data); 
                                          
                                          jQuery("#wpem-event-box-layout").prop('disabled',false);
									      jQuery("#wpem-event-list-layout").prop('disabled',false);
									  },
									  error: function(jqXHR, textStatus, errorThrown) 
									  { 		           
								        Common.logInfo("Error: " +textStatus); 
									  },
									  complete: function (jqXHR, textStatus) 
									  {
									     EventCalendarAjaxFilter.actions.eventDetailsPopup();									     
									  }
    					          });
    		    
				},
				
			
				///<summary>
                ///load  the event calendar current date.  
                ///</summary>     
                ///<returns type="initialization filters" />   
                /// <since>1.0.0</since> 
				ajaxCalanderwidgetCurrentEvent: function(event) 
				{
				    if(jQuery(this).children('.current_date_event').val().length > 0){
				        var event_id = jQuery(this).children('.current_date_event').val();
				        }
				        else
				        {
				            var event_id = '';    
				        }
				    	jQuery.ajax({
									 type: 'POST',
									 url: event_manager_calendar_event_calendar_ajax_filters.ajax_url,
									 data: 
									 {
									    'action': 'widget_current_event_detail',
									    'event_id': event_id,
									     
									 },
									beforeSend: function(jqXHR, settings) 
									{
									    Common.logInfo("Before send called...");
									    
									  	 
									},
									success: function(data)
									{
								        jQuery('#current_event_container').html(data); 
									},
									error: function(jqXHR, textStatus, errorThrown) 
									{ 		           
								        Common.logInfo("Error in..."); 
									},
									complete: function (jqXHR, textStatus) 
									{
									}
					        });
				    
				    event.preventDefault();

				 }	
			}
		};
	};
	EventCalendarAjaxFilter = EventCalendarAjaxFilter();
	
jQuery(document).ready(function(jQuery) 
{
	EventCalendarAjaxFilter.init();
});

jQuery.fn.insertAt = function(index, element) {
  var lastIndex = this.children().size()
  if (index < 0) {
    index = Math.max(0, lastIndex + 1 + index)
  }
  this.append(element)
  if (index < lastIndex) {
    this.children().eq(index).before(this.children().last())
  }
  return this;
}