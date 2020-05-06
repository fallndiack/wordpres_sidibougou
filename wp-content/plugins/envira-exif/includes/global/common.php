<?php
/**
 * Common class.
 *
 * @since 1.0.0
 *
 * @package Envira_Featured_Content
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
 * @package Envira_Exif
 * @author  Envira Team
 */
class Envira_Exif_Common {

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

		// Defaults.
		add_filter( 'envira_gallery_defaults', array( $this, 'defaults' ), 10, 2 );
		add_filter( 'envira_albums_defaults', array( $this, 'defaults' ), 10, 2 );
		add_filter( 'envira_gallery_get_config_mobile_keys', array( $this, 'mobile_config_keys' ) );

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

		// Gallery: EXIF.
		$defaults['exif']                     = 0;
		$defaults['exif_make']                = 0;
		$defaults['exif_model']               = 0;
		$defaults['exif_aperture']            = 0;
		$defaults['exif_shutter_speed']       = 0;
		$defaults['exif_focal_length']        = 0;
		$defaults['exif_iso']                 = 0;
		$defaults['exif_capture_time']        = 0;
		$defaults['exif_capture_time_format'] = 'F j, Y';

		// Lightbox: EXIF.
		$defaults['exif_lightbox']                     = 0;
		$defaults['exif_lightbox_make']                = 0;
		$defaults['exif_lightbox_model']               = 0;
		$defaults['exif_lightbox_aperture']            = 0;
		$defaults['exif_lightbox_shutter_speed']       = 0;
		$defaults['exif_lightbox_focal_length']        = 0;
		$defaults['exif_lightbox_iso']                 = 0;
		$defaults['exif_lightbox_position']            = 'bottom-right';
		$defaults['exif_lightbox_outside']             = 0;
		$defaults['exif_lightbox_capture_time']        = 0;
		$defaults['exif_lightbox_capture_time_format'] = 'F j, Y';

		// Mobile.
		$defaults['mobile_exif']          = 0;
		$defaults['mobile_exif_lightbox'] = 0;

		// Tags.
		$defaults['exif_tags'] = 0;

		// Return.
		return $defaults;

	}

	/**
	 * Returns config to mobile config key mappings for this Addon
	 *
	 * Used by Envira_Gallery_Shortcode::get_config() when on a mobile device,
	 * to use mobile-specific settings instead of Gallery settings
	 *
	 * @since 1.1.0
	 *
	 * @param   array $mobile_keys    Mobile Keys.
	 * @return  array                   Mobile Keys
	 */
	public function mobile_config_keys( $mobile_keys ) {

		// When on mobile, use the mobile_exif option to determine EXIF output.
		$mobile_keys['exif']          = 'mobile_exif';
		$mobile_keys['exif_lightbox'] = 'mobile_exif_lightbox';

		return $mobile_keys;

	}

	/**
	 * Helper method for retrieving positions.
	 *
	 * @since 1.0.8
	 *
	 * @return array Array of positions.
	 */
	public function get_positions() {

		$positions = array(
			'top-left'     => __( 'Top Left', 'envira-exif' ),
			'top-right'    => __( 'Top Right', 'envira-exif' ),
			'bottom-left'  => __( 'Bottom Left', 'envira-exif' ),
			'bottom-right' => __( 'Bottom Right', 'envira-exif' ),
		);

		return apply_filters( 'envira_exif_positions', $positions );

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Exif_Common object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Exif_Common ) ) {
			self::$instance = new Envira_Exif_Common();
		}

		return self::$instance;

	}

}

// Load the common class.
$envira_exif_common = Envira_Exif_Common::get_instance();
