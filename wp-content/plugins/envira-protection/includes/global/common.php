<?php
/**
 * Common class.
 *
 * @since 1.0.9
 *
 * @package Envira_Protection
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Common class.
 *
 * @since 1.0.9
 *
 * @package Envira_Protection
 * @author  Envira Team
 */
class Envira_Protection_Common {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.9
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.0.9
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.9
	 */
	public function __construct() {

		add_filter( 'envira_gallery_defaults', array( $this, 'defaults' ), 10, 2 );
		add_filter( 'envira_albums_defaults', array( $this, 'defaults' ), 10, 2 );

	}

	/**
	 * Applies a default to the addon setting.
	 *
	 * @since 1.0.9
	 *
	 * @param array $defaults  Array of default config values.
	 * @param int   $post_id     The current post ID.
	 * @return array $defaults Amended array of default config values.
	 */
	public function defaults( $defaults, $post_id ) {

		// Enabled by default.
		$defaults['protection'] = 1;

		return $defaults;

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.9
	 *
	 * @return object The Envira_Tags_Common object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;

	}

}

// Load the common class.
$envira_protection_common = Envira_Protection_Common::get_instance();
