<?php
/**
 * Common class.
 *
 * @since 1.0.1
 *
 * @package Envira_Password_Protection
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Common class.
 *
 * @since 1.0.1
 *
 * @package Envira_Password_Protection
 * @author  Envira Team
 */
class Envira_Password_Protection_Common {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.1
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.0.1
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.1
	 */
	public function __construct() {

		add_filter( 'envira_gallery_defaults', array( $this, 'defaults' ), 10, 2 );

	}

	/**
	 * Adds the default settings for this addon.
	 *
	 * @since 1.0.1
	 *
	 * @param array $defaults  Array of default config values.
	 * @param int   $post_id     The current post ID.
	 * @return array $defaults Amended array of default config values.
	 */
	public function defaults( $defaults, $post_id ) {

		// Password Protection.
		$defaults['password_protection_email'] = '';

		// .
		return $defaults;

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.1
	 *
	 * @return object The Envira_Password_Protection_Common object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Password_Protection_Common ) ) {
			self::$instance = new Envira_Password_Protection_Common();
		}

		return self::$instance;

	}

}

// Load the common class.
$envira_password_protection_common = Envira_Password_Protection_Common::get_instance();
