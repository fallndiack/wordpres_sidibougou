<?php
/**
 * Videos Admin Container Class
 *
 * @package Envira Videos
 */

namespace Envira\Videos\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Videos Admin Container Class
 */
class Settings {

	/**
	 * Allowed HTML
	 *
	 * @var mixed
	 * @access public
	 */
	public $wp_kses_allowed_html = array(
		'a'      => array(
			'href'                => array(),
			'class'               => array(),
			'title'               => array(),
			'data-status'         => array(),
			'data-envira-tooltip' => array(),
			'data-id'             => array(),
		),
		'br'     => array(),
		'img'    => array(
			'src'   => array(),
			'class' => array(),
			'alt'   => array(),
		),
		'div'    => array(
			'class' => array(),
		),
		'li'     => array(
			'id'                              => array(),
			'class'                           => array(),
			'data-envira-gallery-image'       => array(),
			'data-envira-gallery-image-model' => array(),
		),
		'em'     => array(),
		'span'   => array(
			'class' => array(),
		),
		'strong' => array(),
	);

	/**
	 * Primary class constructor.
	 *
	 * @since 1.3.0
	 */
	public function __construct() {

		// Load the base class object.
		// $this->base = Envira_Gallery::get_instance();
		// $this->common = Envira_Social_Common::get_instance();
		// Actions.
		add_filter( 'envira_gallery_settings_tab_nav', array( $this, 'tabs' ) );
		add_action( 'envira_gallery_tab_settings_video', array( $this, 'settings' ) );
		add_action( 'init', array( $this, 'save' ) );

	}

	/**
	 * Add a tab to the Envira Gallery Settings screen
	 *
	 * @since 1.0.0
	 *
	 * @param array $tabs Existing tabs.
	 * @return array New tabs
	 */
	public function tabs( $tabs ) {

		$tabs['video'] = __( 'Video', 'envira-video' );

		return $tabs;

	}

	/**
	 * Outputs settings screen for the Video Tab.
	 *
	 * @since 1.0.0
	 */
	public function settings() {

		// Get settings.
		$youtube_api_key = envira_video_get_setting( 'youtube_api_key' );
		?>
		<div id="envira-settings-video">
			<?php
			// Output notice.
			do_action( 'envira_gallery_settings_video_tab_notice' );
			?>

			<table class="form-table">
				<tbody>
					<form action="edit.php?post_type=envira&amp;page=envira-gallery-settings#!envira-tab-video" method="post">
						<tr id="envira-video-facebook-api-id-box">
							<th scope="row">
								<label for="envira-video-youtube-api-id"><?php esc_html_e( 'Youtube API Key', 'envira-video' ); ?></label>
							</th>
							<td>
								<input name="envira-video-youtube-api-id" id="envira-video-youtube-api-id" value="<?php echo esc_html( ( ! $youtube_api_key ? '' : $youtube_api_key ) ); ?>" />
								<p class="description">
									<strong><?php esc_html_e( 'Required For YouTube Playlist Functionality: ', 'envira-video' ); ?></strong>

									<?php
									/* translators: %s */
									echo sprintf( wp_kses( __( 'Visit <a target="_blank" href="https://console.developers.google.com">console.developers.google.com</a>, and register a new project that has access to the YouTube API.  Refer to our <a href="http://enviragallery.com/docs/video-addon">Documentation</a> for full instructions.', 'envira-video' ), $this->wp_kses_allowed_html ) );

									?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php submit_button( __( 'Save', 'envira-video' ), 'primary', 'envira-gallery-verify-submit', false ); ?></th>
							<td><?php wp_nonce_field( 'envira-video-nonce', 'envira-video-nonce' ); ?></td>
						</tr>
					</form>
				</tbody>
			</table>
		</div>
		<?php

	}

	/**
	 * Saves settings if POSTed
	 *
	 * @since 1.0.0
	 */
	public function save() {

		// Check we saved some settings.
		if ( ! isset( $_POST ) ) {
			return;
		}

		// Check nonce exists.
		if ( ! isset( $_POST['envira-video-nonce'] ) ) {
			return;
		}

		// Check nonce is valid.
		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['envira-video-nonce'] ) ), 'envira-video-nonce' ) ) {
			add_action( 'envira_gallery_settings_video_tab_notice', array( $this, 'notice_nonce' ) );
			return;
		}

		// Save.
		$settings_sanitized = array_map( 'sanitize_text_field', wp_unslash( $_POST ) );
		$settings           = array(
			'youtube_api_key' => sanitize_text_field( wp_unslash( $settings_sanitized['envira-video-youtube-api-id'] ) ),
		);
		update_option( 'envira-video', $settings );

		// Show confirmation that settings saved.
		add_action( 'envira_gallery_settings_video_tab_notice', array( $this, 'notice_saved' ) );

	}

	/**
	 * Outputs a WordPress style notification message to tell the user that the nonce field is invalid
	 *
	 * @since 1.0.0
	 */
	public function notice_nonce() {

		?>
		<div class="notice error below-h2">
			<p><?php esc_html_e( 'The nonce field is invalid.', 'envira-video' ); ?></p>
		</div>
		<?php

	}

	/**
	 * Outputs a WordPress style notification message to tell the user that the settings have been saved
	 *
	 * @since 1.0.0
	 */
	public function notice_saved() {

		?>
		<div class="notice updated below-h2">
			<p><?php esc_html_e( 'Video settings saved!', 'envira-video' ); ?></p>
		</div>
		<?php

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Video_Settings object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Video_Settings ) ) {
			self::$instance = new Envira_Video_Settings();
		}

		return self::$instance;

	}

}
