<?php
/**
 * Common class.
 *
 * @since 1.0.0
 *
 * @package Envira_Pagination
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Common class.
 *
 * @since 1.1.3
 *
 * @package Envira_Pagination
 * @author  Envira Team
 */
class Envira_Pagination_Common {

	/**
	 * Holds the class object.
	 *
	 * @since 1.1.3
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.1.3
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
	 * @since 1.1.3
	 */
	public function __construct() {

		// Gallery.
		add_filter( 'envira_gallery_defaults', array( $this, 'defaults' ), 10, 2 );
		add_filter( 'envira_gallery_get_config_mobile_keys', array( $this, 'mobile_config_keys' ) );

		// Albums.
		add_filter( 'envira_albums_defaults', array( $this, 'defaults' ), 10, 2 );
		add_filter( 'envira_albums_get_config_mobile_keys', array( $this, 'mobile_config_keys' ) );

	}

	/**
	 * Adds the default settings for this addon.
	 *
	 * @since 1.1.3
	 *
	 * @param array $defaults  Array of default config values.
	 * @param int   $post_id     The current post ID.
	 * @return array $defaults Amended array of default config values.
	 */
	public function defaults( $defaults, $post_id ) {

		// Add Pagination default settings to main defaults array.
		$defaults['pagination']                 = 0;
		$defaults['pagination_position']        = 'below';
		$defaults['pagination_images_per_page'] = 9;
		$defaults['pagination_prev_next']       = 0;
		$defaults['pagination_prev_text']       = __( '« Previous', 'envira-pagination' );
		$defaults['pagination_next_text']       = __( 'Next »', 'envira-pagination' );
		$defaults['pagination_scroll']          = 0;
		$defaults['pagination_ajax_load']       = 0;
		$defaults['pagination_button_text']     = __( 'Load More', 'envira-pagination' );

		// Lightbox.
		$defaults['pagination_lightbox_display_all_images'] = 0;

		// Mobile.
		$defaults['mobile_pagination_images_per_page'] = 9;
		$defaults['mobile_pagination_prev_next']       = 0;

		// Return.
		return $defaults;

	}

	/**
	 * Returns config to mobile config key mappings for this Addon
	 *
	 * Used by Envira_Gallery_Shortcode::get_config() when on a mobile device,
	 * to use mobile-specific settings instead of Gallery settings
	 *
	 * @since 1.1.7
	 *
	 * @param   array $mobile_keys    Mobile Keys.
	 * @return  array                   Mobile Keys
	 */
	public function mobile_config_keys( $mobile_keys ) {

		$mobile_keys['pagination_images_per_page'] = 'mobile_pagination_images_per_page';
		$mobile_keys['pagination_prev_next']       = 'mobile_pagination_prev_next';

		return $mobile_keys;

	}

	/**
	 * Helper method for retrieving pagination gallery refresh options.
	 *
	 * @since 1.1.7
	 *
	 * @return array Array of social networks.
	 */
	public function get_refresh_options() {

		// Keys are specific because 1.1.6 just had a checkbox 0/1 option to disable AJAX loading or enable on scroll.
		// 1.1.7 introduces.
		$options = array(
			0 => __( 'On Pagination Click, reloading Page', 'envira-pagination' ),
			1 => __( 'On Scroll (Lazy Loading)', 'envira-pagination' ),
			2 => __( 'On Pagination Click (AJAX)', 'envira-pagination' ),
			3 => __( 'On Button Click (AJAX)', 'envira-pagination' ),
		);

		return apply_filters( 'envira_pagination_get_refresh_options', $options );

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.1.3
	 *
	 * @return object The Envira_Pagination_Common object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Pagination_Common ) ) {
			self::$instance = new Envira_Pagination_Common();
		}

		return self::$instance;

	}

}

// Load the common class.
$envira_pagination_common = Envira_Pagination_Common::get_instance();
