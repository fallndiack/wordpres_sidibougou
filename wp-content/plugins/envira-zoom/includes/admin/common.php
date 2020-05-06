<?php
/**
 * Common admin class.
 *
 * @since 1.0.0
 *
 * @package Envira_Zoom
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Common admin class.
 *
 * @since 1.0.0
 *
 * @package Envira_Zoom
 * @author  David Bisset
 */
class Envira_Zoom_Common_Admin {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Holds the base class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $base;

	/**
	 * Holds the metabox class object.
	 *
	 * @since 1.3.1
	 *
	 * @var object
	 */
	public $metabox;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Load the base class object.
		$this->base = Envira_Zoom::get_instance();

		// Load admin assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

	}

	/**
	 * Loads styles for all Envira-based Administration Screens.
	 *
	 * @since 1.0.0
	 *
	 * @return null Return early if not on the proper screen.
	 */
	public function admin_styles() {

		// Get current screen.
		$screen = get_current_screen();

		// Bail if we're not on the Envira screen.
		if ( 'envira' !== $screen->post_type && 'envira_album' !== $screen->post_type ) {
			return;
		}

		// Load necessary admin styles.
		wp_register_style( $this->base->plugin_slug . '-admin-style', plugins_url( 'assets/css/admin.css', $this->base->file ), array(), $this->base->version );
		wp_enqueue_style( $this->base->plugin_slug . '-admin-style' );

		// Fire a hook to load in custom admin styles.
		do_action( 'envira_zoom_admin_styles' );

	}

	/**
	 * Loads scripts for all Envira-based Administration Screens.
	 *
	 * @since 1.0.0
	 *
	 * @return null Return early if not on the proper screen.
	 */
	public function admin_scripts() {

		// Get current screen.
		$screen = get_current_screen();

		// Bail if we're not on the Envira screen.
		if ( 'envira' !== $screen->post_type && 'envira_album' !== $screen->post_type ) {
			return;
		}

		// Load necessary admin scripts
		// wp_register_script( $this->base->plugin_slug . '-admin-script', plugins_url( 'assets/js/min/admin-min.js', $this->base->file ), array( 'jquery' ), $this->base->version );
		// wp_enqueue_script( $this->base->plugin_slug . '-admin-script' );
		// wp_localize_script(
		// $this->base->plugin_slug . '-admin-script',
		// 'envira_zoom_admin',
		// array(
		// 'ajax'                  => admin_url( 'admin-ajax.php' ),
		// 'dismiss_notice_nonce'  => wp_create_nonce( 'envira-zoom-dismiss-notice' ),
		// )
		// );
		// Fire a hook to load in custom admin scripts.
		do_action( 'envira_zoom_admin_scripts' );

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Zoom_Common_Admin object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Zoom_Common_Admin ) ) {
			self::$instance = new Envira_Zoom_Common_Admin();
		}

		return self::$instance;

	}

}

// Load the common admin class.
$envira_zoom_common_admin = Envira_Zoom_Common_Admin::get_instance();
