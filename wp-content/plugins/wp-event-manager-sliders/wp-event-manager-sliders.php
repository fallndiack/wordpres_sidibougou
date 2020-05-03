<?php
/*
Plugin Name: WP Event Manager - Sliders
Plugin URI: http://www.wp-eventmanager.com/product-category/plugins/
Description: Use slick sliders for your listings.

Author: WP Event Manager
Author URI: http://www.wp-eventmanager.com/
Text Domain: wp-event-manager-sliders
Domain Path: /languages
Version: 1.2.2
Since: 1.0.0
Requires WordPress Version at least: 4.1

Copyright: 2020 WP Event Manager
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GAM_Updater' ) ) {
	include( 'autoupdater/gam-plugin-updater.php' );
}

function pre_check_before_installing_sliders() 
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
                echo __( 'WP Event Manager is require to use WP Event Manager - Sliders' , 'wp-event-manager-sliders');
                echo '</p></div>';		
        	}
             		
    }
}
add_action( 'admin_notices', 'pre_check_before_installing_sliders' );
/**
 * WP_Event_Manager_Sliders class.
 */
class WP_Event_Manager_Sliders extends GAM_Updater {

	/**
	 * Constructor
	 */
	public function __construct() {
		
		// Define constants
		define( 'EVENT_MANAGER_SLIDER_VERSION', '1.2.2' );
		define( 'EVENT_MANAGER_SLIDER_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'EVENT_MANAGER_SLIDER_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
        
         //shortcodes
		include( 'shortcodes/wp-event-manager-sliders-shortcodes.php' );
		//external
		include('external/external.php');
		// Add actions
		add_action( 'init', array( $this, 'load_plugin_textdomain' ), 12 );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
		
		// Init updates
		$this->init_updates( __FILE__ );
	}
	
	/**
	 * Localisation
	 */
	public function load_plugin_textdomain() {
		$domain = 'wp-event-manage-sliders';       
        $locale = apply_filters('plugin_locale', get_locale(), $domain);
		load_textdomain( $domain, WP_LANG_DIR . "/wp-event-manage-sliders/".$domain."-" .$locale. ".mo" );
		load_plugin_textdomain($domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
	
	/**
	 * frontend_scripts function.
	 *
	 * @access public
	 * @return void
	 */
	public function frontend_scripts() {  
        wp_register_style( 'wp-event-manager-sliders-frontend', EVENT_MANAGER_SLIDER_PLUGIN_URL . '/assets/css/frontend.min.css'); 
	}
}
$GLOBALS['event_manager_sliders'] = new WP_Event_Manager_Sliders();