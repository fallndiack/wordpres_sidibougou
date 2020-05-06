<?php
/**
 * Settings class.
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
 * Settings class.
 *
 * @since 1.0.0
 *
 * @package Envira_Zoom
 * @author  David Bisset
 */
class Envira_Zoom_Settings {

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
	 * Holds the common class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $common;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		/* Currently this addon does not need to be on the general settings page. */

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Zoom_Settings object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Zoom_Settings ) ) {
			self::$instance = new Envira_Zoom_Settings();
		}

		return self::$instance;

	}

}

// Load the settings class.
$envira_zoom_settings = Envira_Zoom_Settings::get_instance();
