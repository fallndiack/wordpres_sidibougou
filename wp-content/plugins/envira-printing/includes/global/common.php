<?php
/**
 * Common class.
 *
 * @since 1.0.0
 *
 * @package Envira_Printing
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Common class.
 *
 * @since 1.0.0
 *
 * @package Envira_Printing
 * @author  Envira Team
 */
class Envira_Printing_Common {

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

		add_filter( 'envira_gallery_defaults', array( $this, 'defaults' ), 10, 2 );
		add_filter( 'envira_albums_defaults', array( $this, 'defaults' ), 10, 2 );

	}

	/**
	 * Adds the default settings for this addon.
	 *
	 * @since 1.0.0
	 *
	 * @param array $defaults  Array of default config values.
	 * @param int   $post_id     The current post ID.
	 * @return array $defaults Amended array of default config values.
	 */
	public function defaults( $defaults, $post_id ) {

		// Add default settings to main defaults array.
		$defaults['print']          = 0;
		$defaults['print_position'] = 'top-left';

		// Lightbox defaults.
		$defaults['print_lightbox']          = 0;
		$defaults['print_lightbox_position'] = 'top-left';

		// Return.
		return $defaults;

	}

	/**
	 * Helper method for retrieving positions.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of positions.
	 */
	public function get_positions() {

		$positions = array(
			'top-left'     => __( 'Top Left', 'envira-printing' ),
			'top-right'    => __( 'Top Right', 'envira-printing' ),
			'bottom-left'  => __( 'Bottom Left', 'envira-printing' ),
			'bottom-right' => __( 'Bottom Right', 'envira-printing' ),
		);

		return apply_filters( 'envira_printing_positions', $positions );

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Printing_Common object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Printing_Common ) ) {
			self::$instance = new Envira_Printing_Common();
		}

		return self::$instance;

	}

}

// Load the common class.
$envira_printing_common = Envira_Printing_Common::get_instance();
