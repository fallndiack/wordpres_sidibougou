<?php
/**
 * Common class.
 *
 * @since 1.0.8
 *
 * @package Envira_Slideshow
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Common class.
 *
 * @since 1.0.8
 *
 * @package Envira_Slideshow
 * @author  Envira Team
 */
class Envira_Slideshow_Common {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.8
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.0.8
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.8
	 */
	public function __construct() {

		add_filter( 'envira_gallery_defaults', array( $this, 'defaults' ), 10, 2 );
		add_filter( 'envira_albums_defaults', array( $this, 'defaults' ), 10, 2 );

	}

	/**
	 * Adds the default settings for this addon.
	 *
	 * @since 1.0.8
	 *
	 * @param array $defaults  Array of default config values.
	 * @param int   $post_id     The current post ID.
	 * @return array $defaults Amended array of default config values.
	 */
	public function defaults( $defaults, $post_id ) {

		$defaults['slideshow']       = 0;
		$defaults['autoplay']        = 0;
		$defaults['ss_speed']        = 5000;
		$defaults['slideshow_hover'] = 0;
		return $defaults;

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.8
	 *
	 * @return object The Envira_Slideshow_Common object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Slideshow_Common ) ) {
			self::$instance = new Envira_Slideshow_Common();
		}

		return self::$instance;

	}

}

// Load the common class.
$envira_slideshow_common = Envira_Slideshow_Common::get_instance();
