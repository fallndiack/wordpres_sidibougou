<?php
/**
 * Common class.
 *
 * @since 1.3.0
 *
 * @package Envira_Tags
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Common class.
 *
 * @since 1.3.0
 *
 * @package Envira_Tags
 * @author  Envira Team
 */
class Envira_Tags_Common {

	/**
	 * Holds the class object.
	 *
	 * @since 1.3.0
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.3.0
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.3.0
	 */
	public function __construct() {

		add_filter( 'envira_gallery_defaults', array( $this, 'defaults' ), 10, 2 );

		// Lightroom Addon.
		add_filter( 'envira_lightroom_prepare_gallery_data', array( $this, 'lightroom_save' ), 10, 4 );

	}

	/**
	 * Applies a default to the addon setting.
	 *
	 * @since 1.0.0
	 *
	 * @param array $defaults  Array of default config values.
	 * @param int   $post_id     The current post ID.
	 * @return array $defaults Amended array of default config values.
	 */
	public function defaults( $defaults, $post_id ) {

		// Disable filtering by default.
		$defaults['tags']                  = 0;
		$defaults['tags_filter']           = '';
		$defaults['tags_position']         = 'top';
		$defaults['tags_all_enabled']      = 1;
		$defaults['tags_all']              = __( 'All', 'envira-tags' );
		$defaults['tags_sorting']          = '';
		$defaults['tags_manual_sorting']   = array();
		$defaults['tags_display']          = '';
		$defaults['tags_scroll']           = 0;
		$defaults['tags_limit']            = 0;
		$defaults['tags_manual_blacklist'] = '';
		$defaults['tags_mobile']           = 1;
		$defaults['tags_count']            = 0;

		return $defaults;

	}

	/**
	 * Helper method for retrieving display sorting options.
	 *
	 * @since 1.3.3
	 *
	 * @return array Array of sorting options
	 */
	public function get_sorting_options() {

		$options = array(
			array(
				'name'  => __( 'Ascending (A-Z)', 'envira-tags' ),
				'value' => '', // Deliberate, as this is the default.
			),
			array(
				'name'  => __( 'Descending (Z-A)', 'envira-tags' ),
				'value' => 'desc',
			),
			array(
				'name'  => __( 'Manual', 'envira-tags' ),
				'value' => 'manual',
			),
		);

		return apply_filters( 'envira_tags_sorting_options', $options );

	}

	/**
	 * Helper method for retrieving tag position options.
	 *
	 * @since 1.3.3
	 *
	 * @return array Array of sorting options
	 */
	public function get_position_options() {

		$options = array(
			array(
				'name'  => __( 'Above Images', 'envira-tags' ),
				'value' => 'above',
			),
			array(
				'name'  => __( 'Below Images', 'envira-tags' ),
				'value' => 'below',
			),
		);

		return apply_filters( 'envira_tags_position_options', $options );

	}

	/**
	 * Returns an array of settings
	 *
	 * @since 1.3.1
	 */
	public function get_settings() {

		// Get settings.
		$settings = get_option( 'envira-tags' );

		// If no settings exist, create a blank array for them.
		if ( ! is_array( $settings ) ) {
			$settings = array(
				'imagga_enabled'            => false,
				'imagga_retag'              => false,
				'imagga_authorization_code' => '',
				'imagga_confidence'         => 40,
			);
		} else {
			$settings['imagga_enabled']            = ! empty( $settings['imagga_enabled'] ) ? $settings['imagga_enabled'] : false;
			$settings['imagga_retag']              = ! empty( $settings['imagga_retag'] ) ? $settings['imagga_retag'] : false;
			$settings['imagga_authorization_code'] = ! empty( $settings['imagga_authorization_code'] ) ? $settings['imagga_authorization_code'] : false;
			$settings['imagga_confidence']         = ! empty( $settings['imagga_confidence'] ) ? $settings['imagga_confidence'] : 40;
		}

		return $settings;

	}

	/**
	 * Updates settings with the given key/value pairs
	 *
	 * @since 1.3.1
	 *
	 * @param   array $settings Settings.
	 */
	public function save_settings( $settings ) {

		// If the auth code starts with 'Basic ', which it does if copied from Imagga using the copy button,
		// strip this part.
		if ( isset( $settings['imagga_authorization_code'] ) ) {
			$settings['imagga_authorization_code'] = str_replace( 'Basic ', '', $settings['imagga_authorization_code'] );
		}

		// Cast some value.
		$settings['imagga_confidence'] = absint( $settings['imagga_confidence'] );

		// Save.
		update_option( 'envira-tags', $settings );

	}

	/**
	 * Saves any specified tags to the Tags Taxonomy when uploaded through the Lightroom Addon
	 *
	 * @since 1.3.4
	 *
	 * @param   array  $image          Image.
	 * @param   array  $gallery_data   Gallery Config.
	 * @param   int    $attach_id      Image Attachment ID.
	 * @param   string $tags           Comma separated list of tags (or blank).
	 * @return  array                  Image.
	 */
	public function lightroom_save( $image, $gallery_data, $attach_id, $tags ) {

		// Explode the tag list and save.
		if ( ! empty( $tags ) ) {
			$tags = explode( ',', $tags );

			// Store tags in taxonomy.
			wp_set_object_terms( $attach_id, $tags, 'envira-tag' );

			// If this is being converted from the old style tags in meta to the new style tags in a taxonomy, blank the old style meta, as we no longer use it.
			unset( $image['tags'] );
		}

		return $image;

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.3.0
	 *
	 * @return object The Envira_Tags_Common object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Tags_Common ) ) {
			self::$instance = new Envira_Tags_Common();
		}

		return self::$instance;

	}

}

// Load the common class.
$envira_tags_common = Envira_Tags_Common::get_instance();
