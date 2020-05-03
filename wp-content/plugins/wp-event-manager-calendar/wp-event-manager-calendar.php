<?php
/**
	Plugin Name: WP Event Manager - Calendar
	Plugin URI: http://www.wp-eventmanager.com/plugins/
	Description: You display calendar of the events. It is not just a grid, it is filled with neat features that makes it an amazing grid calendar.
	Author: WP Event Manager 
	Author URI: http://www.wp-eventmanager.com/
	Text Domain: wp-event-manager-calendar
	Domain Path: /languages
	Version: 1.4.6
	Since: 1.0
	Requires WordPress Version at least: 4.1
	
	Copyright: 2018 WP Event Manager
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/
	
    // Exit if accessed directly
    if ( ! defined( 'ABSPATH' ) )
    	exit;
    
    if ( ! class_exists( 'GAM_Updater' ) ) {
    	include( 'autoupdater/gam-plugin-updater.php' );
    }	
	
function pre_check_before_installing_calendar() 
{
	/*
	 * Check weather WP Event Manager is installed or not
 	 */
	if (! in_array( 'wp-event-manager/wp-event-manager.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) 
	{
	        global $pagenow;
	    	if( $pagenow == 'plugins.php' )
	    	{
	            echo '<div id="error" class="error notice is-dismissible"><p>';
	            echo __( 'WP Event Manager is require to use WP Event Manager - Calendar' , 'wp-event-manager-calendar');
	            echo '</p></div>';		
	    	}
    	    		
	}
}
add_action( 'admin_notices', 'pre_check_before_installing_calendar' );	
/**
 * WP_Event_Manager_Calendar class.
 */
class WP_Event_Manager_Calendar extends GAM_Updater {
	
	/**
	 * Constructor
	 */
	public function __construct() 
	{
		// Define constants
		define( 'EVENT_MANAGER_CALENDAR_VERSION', '1.4.6' );
		define( 'EVENT_MANAGER_CALENDAR_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'EVENT_MANAGER_CALENDAR_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );	
		
		// Add actions
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );	
		
		//include files
		include('wp-event-manager-calendar-functions.php');
		include('wp-event-manager-calendar-templates.php');
		include('shortcodes/wp-event-manager-calendar-shortcodes.php');

		//external 
		include('external/external.php');
		
		if(is_admin()){
			include('admin/wp-event-manager-calendar-settings.php');
		}
		//init widget
		add_action( 'widgets_init', array( $this, 'widgets_init' ) );		
		
		if(get_option('event_manager_calendar_layout',true) == true) {
			//adde calendar layout icon near to box and line layout
			add_action('end_event_listing_layout_icon','add_event_listing_calendar_layout_icon');
		}
		
		//event calendar layout show with line and box layout
	     add_action( 'event_manager_ajax_events_calendar_layout', 'events_calendar_layout'  );	
	     
	     // Init updates
	     $this->init_updates( __FILE__ ); 	
	}
	
	/**
	 * Localisation
	 */
	public function load_plugin_textdomain() {
		$domain = 'wp-event-manager-calendar';       
                $locale = apply_filters('plugin_locale', get_locale(), $domain);
		load_textdomain( $domain, WP_LANG_DIR . "/wp-event-manager-calendar/".$domain."-" .$locale. ".mo" );
		load_plugin_textdomain($domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
    
	
    /**
	 * Register and enqueue scripts and css
	 */
	public function frontend_scripts() 
	{	
	        $ajax_url         = WP_Event_Manager_Ajax::get_endpoint();
		wp_register_style('wp-event-manager-calendar-frontend', EVENT_MANAGER_CALENDAR_PLUGIN_URL . '/assets/css/frontend.min.css');		
		
		wp_register_script( 'wp-event-manager-calendar-event-calender-ajax-filters', EVENT_MANAGER_CALENDAR_PLUGIN_URL . '/assets/js/event-calendar-ajax-filters.min.js', 
					array('jquery','wp-event-manager-common','wp-event-manager-content-event-listing'), EVENT_MANAGER_CALENDAR_VERSION);		
		wp_localize_script('wp-event-manager-calendar-event-calender-ajax-filters', 'event_manager_calendar_event_calendar_ajax_filters', array(
			'ajax_url' => $ajax_url
			)
		);
	}
	
	/**
	 * Widgets init
	 * Add the registered widget .
	 */
	public function widgets_init() 
	{
	  include_once( 'widgets/wp-event-manager-calendar-widgets.php' );
	}
}
$GLOBALS['event_manager_calendar'] = new WP_Event_Manager_Calendar();
?>
