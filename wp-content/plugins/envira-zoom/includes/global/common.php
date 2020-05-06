<?php
/**
 * Common class.
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
 * Common class.
 *
 * @since 1.0.0
 *
 * @package Envira_Zoom
 * @author  David Bisset
 */
class Envira_Zoom_Common {

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

		// Gallery defaults.
		$defaults['zoom_hover']              = 0;
		$defaults['zoom_size']               = 200;
		$defaults['zoom_type']               = 'basic';
		$defaults['zoom_effect']             = 'fade-in';
		$defaults['zoom_position']           = 'upper-left';
		$defaults['zoom_lens_shape']         = 'round';
		$defaults['zoom_window_size']        = 'medium';
		$defaults['zoom_tint_color']         = '';
		$defaults['zoom_tint_color_opacity'] = 25;
		$defaults['zoom_mousewheel']         = 0;
		$defaults['zoom_lens_cursor']        = 1;

		// Return.
		return $defaults;

	}

	/**
	 * Returns config to mobile config key mappings for this Addon
	 *
	 * Used by Envira_Gallery_Shortcode::get_config() when on a mobile device,
	 * to use mobile-specific settings instead of Gallery settings
	 *
	 * @since 1.0.9
	 *
	 * @param   array   $mobile_keys    Mobile Keys
	 * @return  array                   Mobile Keys
	 */
	// public function mobile_config_keys( $mobile_keys ) {
	// When on mobile, use the mobile_social option to determine social sharing button output
	// $mobile_keys['social']            = 'mobile_social';
	// $mobile_keys['social_lightbox']   = 'mobile_social';
	// return $mobile_keys;
	// }.

	/**
	 * Helper method for retrieving window positions.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of positions.
	 */
	public function get_window_positions() {

		$window_positions = array(
			'upper-left'  => __( 'Upper Left', 'envira-zoom' ),
			'upper-right' => __( 'Upper Right', 'envira-zoom' ),
			'lower-left'  => __( 'Lower Left', 'envira-zoom' ),
			'lower-right' => __( 'Lower Right', 'envira-zoom' ),
		);

		return apply_filters( 'envira_zoom_window_positions', $window_positions );

	}

	/**
	 * Helper method for retrieving zoom types.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of types.
	 */
	public function get_types() {

		$types = array(
			'basic' => __( 'Basic', 'envira-zoom' ),
			'inner' => __( 'Inner', 'envira-zoom' ),
			'lens'  => __( 'Lens', 'envira-zoom' ),
		);

		return apply_filters( 'envira_zoom_types', $types );

	}

	/**
	 * Helper method for retrieving effects.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of effects.
	 */
	public function get_effects() {

		$effects = array(
			'no-effect' => __( 'No Effect', 'envira-zoom' ),
			'fade-in'   => __( 'Fade In', 'envira-zoom' ),
			'fade-out'  => __( 'Fade Out', 'envira-zoom' ),
			'easing'    => __( 'Easing', 'envira-zoom' ),
		);

		return apply_filters( 'envira_zoom_effects', $effects );

	}

	/**
	 * Helper method for retrieving lens shapes.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of effects.
	 */
	public function get_lens_shapes() {

		$effects = array(
			'round'  => __( 'Round', 'envira-zoom' ),
			'square' => __( 'Square', 'envira-zoom' ),
		);

		return apply_filters( 'envira_zoom_len_shapes', $effects );

	}

	/**
	 * Helper method for retrieving window sizes.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of effects.
	 */
	public function get_window_sizes() {

		$effects = array(
			'small'   => __( 'Small', 'envira-zoom' ),
			'medium'  => __( 'Medium', 'envira-zoom' ),
			'large'   => __( 'Large', 'envira-zoom' ),
			'x-large' => __( 'Extra Large', 'envira-zoom' ),
		);

		return apply_filters( 'envira_window_sizes', $effects );

	}

	/**
	 * Helper function to retrieve a Setting
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Setting.
	 * @return array Settings
	 */
	public function get_setting( $key ) {

		// Get settings.
		$settings = self::get_instance()->get_settings();

		// Check setting exists.
		if ( ! is_array( $settings ) ) {
			return false;
		}
		if ( ! array_key_exists( $key, $settings ) ) {
			return false;
		}

		$setting = apply_filters( 'envira_zoom_setting', $settings[ $key ] );
		return $setting;

	}

	/**
	 * Helper function to retrieve Settings
	 *
	 * @since 1.0.0
	 *
	 * @return array Settings
	 */
	public function get_settings() {

		$settings = get_option( 'envira-zoom' );
		$settings = apply_filters( 'envira_zoom_settings', $settings );
		return $settings;

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Zoom_Common object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Zoom_Common ) ) {
			self::$instance = new Envira_Zoom_Common();
		}

		return self::$instance;

	}

}

// Load the common class.
$envira_zoom_common = Envira_Zoom_Common::get_instance();
