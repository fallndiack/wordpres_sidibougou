<?php
/**
 * Common admin class.
 *
 * @since 1.0.0
 *
 * @package Envira_Password_Protection
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
 * @package Envira_Password_Protection
 * @author  Envira Team
 */
class Envira_Password_Protection_Common_Admin {

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
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Load the base class object.
		$this->base = Envira_Password_Protection::get_instance();

		// Load admin assets.
		add_action( 'envira_gallery_admin_styles', array( $this, 'admin_styles' ) );
		add_action( 'envira_albums_admin_styles', array( $this, 'admin_styles' ) );

	}

	/**
	 * Loads styles for our admin tables.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function admin_styles() {

		// Load necessary admin styles.
		wp_register_style( $this->base->plugin_slug . '-admin-style', plugins_url( 'assets/css/admin.css', $this->base->file ), array(), $this->base->version );
		wp_enqueue_style( $this->base->plugin_slug . '-admin-style' );

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Password_Protection_Common_Admin object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Password_Protection_Common_Admin ) ) {
			self::$instance = new Envira_Password_Protection_Common_Admin();
		}

		return self::$instance;

	}

}

// Load the common admin class.
$envira_password_protection_common_admin = Envira_Password_Protection_Common_Admin::get_instance();
