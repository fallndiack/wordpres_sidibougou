<?php
/**
 * Common class.
 *
 * @since 1.0.0
 *
 * @package Envira_Social
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
 * @package Envira_Social
 * @author  Envira Team
 */
class Envira_Social_Common {

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

		// Add default settings to main defaults array.
		$defaults['social']                  = 0;
		$defaults['social_facebook']         = 0;
		$defaults['social_twitter']          = 0;
		$defaults['social_pinterest']        = 0;
		$defaults['social_email']            = 0;
		$defaults['social_facebook_message'] = '[caption]';
		$defaults['social_twitter_message']  = '[caption]';
		$defaults['social_position']         = 'top-left';
		$defaults['social_orientation']      = 'vertical';

		// Facebook.
		$defaults['social_facebook_show_option_optional_text'] = 0;
		$defaults['social_facebook_show_option_tags']          = 0;
		$defaults['social_facebook_show_option_caption']       = 0;
		$defaults['social_facebook_show_option_quote']         = 0;
		$defaults['social_facebook_text']                      = '';
		$defaults['social_facebook_tag_options']               = 'manual';
		$defaults['social_facebook_tags_manual']               = '';
		$defaults['social_facebook_quote']                     = '';

		// Twitter.
		$defaults['social_twitter_sharing_method']    = '';
		$defaults['social_twitter_summary_card_site'] = '';
		$defaults['social_twitter_text']              = '';
		$defaults['social_twitter_summary_card_desc'] = '';

		// Pinterest.
		$defaults['social_pinterest_title'] = 'caption';
		$defaults['social_pinterest_type']  = 'pin-one';
		$defaults['social_pinterest_rich']  = 0;

		// Email.
		$defaults['social_email_image_size'] = 'full';
		$defaults['social_email_subject']    = __( 'Sharing From: {title}', 'envira-social' );
		$defaults['social_email_message']    = __( 'URL: {url}\n\nPhoto: {photo_url}', 'envira-social' );

		// Mobile defaults.
		$defaults['mobile_social']           = 0;
		$defaults['mobile_social_facebook']  = 0;
		$defaults['mobile_social_twitter']   = 0;
		$defaults['mobile_social_pinterest'] = 0;
		$defaults['mobile_social_email']     = 0;

		$defaults['mobile_social_lightbox']           = 0;
		$defaults['mobile_social_lightbox_facebook']  = 0;
		$defaults['mobile_social_lightbox_twitter']   = 0;
		$defaults['mobile_social_lightbox_pinterest'] = 0;
		$defaults['mobile_social_lightbox_email']     = 0;

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
	 * @param   array $mobile_keys    Mobile Keys.
	 * @return  array                   Mobile Keys
	 */
	public function mobile_config_keys( $mobile_keys ) {

		// When on mobile, use the mobile_social option to determine social sharing button output.
		$mobile_keys['social']          = 'mobile_social';
		$mobile_keys['social_lightbox'] = 'mobile_social';

		return $mobile_keys;

	}

	/**
	 * Helper method for retrieving social networks for non-mobile
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of social networks.
	 */
	public function get_networks() {

		$networks = array(
			'facebook'  => __( 'Facebook', 'envira-social' ),
			'twitter'   => __( 'Twitter', 'envira-social' ),
			'pinterest' => __( 'Pinterest', 'envira-social' ),
			'linkedin'  => __( 'LinkedIn', 'envira-social' ),
			'email'     => __( 'Email', 'envira-social' ),
		);

		return apply_filters( 'envira_social_networks', $networks );

	}

	/**
	 * Helper method for retrieving social networks for mobile
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of social networks.
	 */
	public function get_networks_mobile() {

		$networks = array(
			'facebook'  => __( 'Facebook', 'envira-social' ),
			'twitter'   => __( 'Twitter', 'envira-social' ),
			'pinterest' => __( 'Pinterest', 'envira-social' ),
			'whatsapp'  => __( 'WhatsApp', 'envira-social' ),
			'linkedin'  => __( 'LinkedIn', 'envira-social' ),
			'email'     => __( 'Email', 'envira-social' ),
		);

		return apply_filters( 'envira_social_networks', $networks );

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
			'top-left'     => __( 'Top Left', 'envira-social' ),
			'top-right'    => __( 'Top Right', 'envira-social' ),
			'bottom-left'  => __( 'Bottom Left', 'envira-social' ),
			'bottom-right' => __( 'Bottom Right', 'envira-social' ),
		);

		return apply_filters( 'envira_social_positions', $positions );

	}

	/**
	 * Helper method for twitter sharing methods.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of positions.
	 */
	public function get_twitter_sharing_methods() {

		$methods = array(
			''           => __( 'No Summary Card', 'envira-social' ),
			'card'       => __( 'Summary Card + Thumbnail', 'envira-social' ),
			'card-photo' => __( 'Summary Card + Large Image', 'envira-social' ),
		);

		return apply_filters( 'envira_social_twitter_sharing_methods', $methods );

	}

	/**
	 * Helper method for facebook sharing methods.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of positions.
	 */
	public function get_facebook_show_options() {

		$methods = array(
			'optional_text' => __( 'Optional Text', 'envira-social' ),
			'tags'          => __( 'Tags', 'envira-social' ),
			'caption'       => __( 'Caption', 'envira-social' ),
			'quote'         => __( 'Quote', 'envira-social' ),
		);

		return apply_filters( 'envira_social_facebook_show_options', $methods );

	}

	/**
	 * Helper method for linkedin sharing methods.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of positions.
	 */
	public function get_linkedin_show_options() {

		$methods = array(
			'title'   => __( 'Title', 'envira-social' ),
			'summary' => __( 'Description (Caption)', 'envira-social' ),
			'source'  => __( 'Source', 'envira-social' ),
		);

		return apply_filters( 'envira_social_linkedin_show_options', $methods );

	}

	/**
	 * Helper method for facebook sharing methods.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of positions.
	 */
	public function get_facebook_show_tag_options() {

		$methods = array(
			'manual' => __( 'Manual', 'envira-social' ),
		);

		if ( class_exists( 'Envira_Tags' ) ) {
			$methods['envira-tags'] = __( 'Envira Tags', 'envira-social' );
		}

		return apply_filters( 'get_facebook_show_tag_options', $methods );

	}

	/**
	 * Helper method for pinterest sharing methods.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of positions.
	 */
	public function get_pinterest_share_options() {

		$methods = array(
			'pin-one' => __( 'Pin One', 'envira-social' ),
			'pin-all' => __( 'Pin All', 'envira-social' ),
		);

		return apply_filters( 'get_pinterest_share_options', $methods );

	}

	/**
	 * Helper method for pinterest title options.
	 *
	 * @since 1.5.4
	 *
	 * @return array Array of positions.
	 */
	public function get_pinterest_title_options() {

		$methods = array(
			'title'   => __( 'Image Title', 'envira-social' ),
			'caption' => __( 'Image Caption', 'envira-social' ),
		);

		return apply_filters( 'get_pinterest_title_options', $methods );

	}

	/**
	 * Helper method for pinterest title options for albums.
	 *
	 * @since 1.5.4
	 *
	 * @return array Array of positions.
	 */
	public function get_pinterest_title_album_options() {

		$methods = array(
			'title'   => __( 'Album / Image Title', 'envira-social' ),
			'caption' => __( 'Image Caption', 'envira-social' ),
		);

		return apply_filters( 'get_pinterest_title_options', $methods );

	}

	/**
	 * Helper method for email image sharing methods.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of positions.
	 */
	public function get_email_image_sizes() {

		$instance_common = Envira_Gallery_Common::get_instance();

		$image_sizes = $instance_common->get_image_sizes( true );

		$sizes = array(
			'full' => __( 'Fullsize', 'envira-social' ),
		);

		if ( ! empty( $image_sizes ) ) {
			foreach ( $image_sizes as $image_size ) {
				$sizes[ $image_size['value'] ] = $image_size['name'];
			}
		}

		return apply_filters( 'get_email_image_sizes', $sizes );

	}


	/**
	 * Helper method for retrieving orientations.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of positions.
	 */
	public function get_orientations() {

		$orientations = array(
			'horizontal' => __( 'Horizontal', 'envira-social' ),
			'vertical'   => __( 'Vertical', 'envira-social' ),
		);

		return apply_filters( 'envira_social_orientations', $orientations );

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
		$settings = $this->get_settings();

		// Check setting exists.
		if ( ! is_array( $settings ) ) {
			return false;
		}
		if ( ! array_key_exists( $key, $settings ) ) {
			return false;
		}

		$setting = apply_filters( 'envira_social_setting', $settings[ $key ] );
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

		$settings = get_option( 'envira-social' );
		$settings = apply_filters( 'envira_social_settings', $settings );
		return $settings;

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Social_Common object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Social_Common ) ) {
			self::$instance = new Envira_Social_Common();
		}

		return self::$instance;

	}

}

// Load the common class.
$envira_social_common = Envira_Social_Common::get_instance();
