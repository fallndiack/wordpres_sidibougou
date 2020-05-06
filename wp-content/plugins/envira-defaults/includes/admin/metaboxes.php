<?php
/**
 * Common class.
 *
 * @since 1.0.0
 *
 * @package Envira_Defaults
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
 * @package Envira_Defaults
 * @author  Envira Team
 */
class Envira_Defaults_Metaboxes {

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
	 * Holds the Envira Gallery Default ID.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public $gallery_default_id;

	/**
	 * Holds the Envira Album Default ID.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public $album_default_id;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Load the base class object.
		$this->base = Envira_Defaults::get_instance();

		// Get Envira Gallery and Albums Default IDs.
		$this->gallery_default_id = get_option( 'envira_default_gallery' );
		$this->album_default_id   = get_option( 'envira_default_album' );

		// Hide Slug Box.
		add_filter( 'envira_gallery_metabox_styles', array( $this, 'maybe_hide_slug_box' ) );
		add_filter( 'envira_album_metabox_styles', array( $this, 'maybe_hide_slug_box' ) );

		// Actions and Filters: Galleries.
		add_filter( 'envira_gallery_types', array( $this, 'add_default_type' ), 9999, 2 );
		add_action( 'envira_gallery_display_defaults', array( $this, 'images_display' ) );
		add_filter( 'envira_gallery_save_settings', array( $this, 'gallery_settings_save' ), 10, 2 );

		// Actions and Filters: Albums.
		add_filter( 'envira_albums_types', array( $this, 'add_default_type' ), 9999, 2 );
		add_action( 'envira_albums_display_defaults', array( $this, 'images_display' ) );
		add_filter( 'envira_albums_save_settings', array( $this, 'albums_settings_save' ), 10, 2 );
	}

	/**
	 * Removes the slug metabox if we are on a Default Gallery or Album
	 *
	 * @since 1.0.0
	 */
	public function maybe_hide_slug_box() {

		if ( ! isset( $_GET['post'] ) ) { // @codingStandardsIgnoreLine - nonce?
			return;
		}

		// Check if we are viewing a Dynamic Gallery or Album.
		if ( $_GET['post'] !== $this->gallery_default_id && $_GET['post'] !== $this->album_default_id ) { // @codingStandardsIgnoreLine - nonce?
			return;
		}

		?>
		<style type="text/css"> #edit-slug-box, #post-preview { display: none; } #delete-action, .misc-pub-section.misc-pub-post-status, .misc-pub-section.misc-pub-visibility, .misc-pub-section.misc-pub-curtime { display: none !important;} </style>
		<?php

	}

	/**
	 * Changes the available Gallery Type to Default if the user is editing
	 * the Envira Default Post
	 *
	 * @since 1.0.0
	 *
	 * @param array   $types Gallery Types.
	 * @param WP_Post $post WordPress Post.
	 * @return array Gallery Types
	 */
	public function add_default_type( $types, $post ) {

		// Check Post = Default.
		switch ( get_post_type( $post ) ) {
			case 'envira':
				if ( $post->ID !== $this->gallery_default_id ) {
					return $types;
				}
				break;
			case 'envira_album':
				if ( $post->ID !== $this->album_default_id ) {
					return $types;
				}
				break;
			default:
				// Not an Envira CPT.
				return $types;
		}

		// Change Types = Default only.
		$types = array(
			'defaults' => __( 'Default Settings', 'envira-defaults' ),
		);

		return $types;

	}

	/**
	 * Display output for the Images Tab
	 *
	 * @since 1.0.0
	 * @param WP_Post $post WordPress Post.
	 */
	public function images_display( $post ) {

		?>
		<div id="envira-defaults">
			<p class="envira-intro">
				<?php
				switch ( get_post_type( $post ) ) {
					case 'envira':
						esc_html_e( 'Default Gallery Settings', 'envira-defaults' );
						break;
					case 'envira_album':
						esc_html_e( 'Default Album Settings', 'envira-defaults' );
						break;
				}
				?>
			</p>
			<p>
				<?php
				switch ( get_post_type( $post ) ) {
					case 'envira':
						esc_html_e( 'This gallery and its settings will be used as defaults for any new gallery you create on this site. Any of these settings can be overwritten on an individual gallery basis via template tag arguments or shortcode parameters.', 'envira-defaults' );
						break;
					case 'envira_album':
						esc_html_e( 'This album and its settings will be used as defaults for any new albums you create on this site. Any of these settings can be overwritten on an individual album basis via template tag arguments or shortcode parameters.', 'envira-defaults' );
						break;
				}
				?>
			</p>

			<div class="envira-video-help">
				<iframe src="https://www.youtube.com/embed/UIxWoLLBOvo?rel=0" width="600" height="338" frameborder="0" allowfullscreen></iframe>
			</div>

			<p>
				<a href="http://enviragallery.com/docs/defaults-addon/" title="Click here for Defaults Addon documentation." target="_blank" class="button button-primary envira-button-primary">
					<?php esc_html_e( 'Click here for Defaults Addon Documentation', 'envira-defaults' ); ?>
				</a>
			</p>
		</div>
		<?php

	}

	/**
	 * Save Album Settings
	 *
	 * @since 1.0.0
	 * @param array $settings Settings.
	 * @param int   $post_id Post ID.
	 * @return array The settings
	 */
	public function albums_settings_save( $settings, $post_id ) {

		if ( $post_id === $this->album_default_id ) {
			$settings['config']['type'] = 'defaults';

		}

		return $settings;

	}

	/**
	 * Save Gallery Settings
	 *
	 * @since 1.0.0
	 * @param array $settings Settings.
	 * @param int   $post_id Post ID.
	 * @return array The settings
	 */
	public function gallery_settings_save( $settings, $post_id ) {

		if ( $post_id === $this->gallery_default_id ) {
			$settings['config']['type'] = 'defaults';
		}

		return $settings;

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Defaults_Metaboxes object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Defaults_Metaboxes ) ) {
			self::$instance = new Envira_Defaults_Metaboxes();
		}

		return self::$instance;

	}

}

// Load the metaboxes class.
$envira_defaults_metaboxes = Envira_Defaults_Metaboxes::get_instance();
