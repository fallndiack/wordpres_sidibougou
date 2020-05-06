<?php
/**
 * Metabox class.
 *
 * @since 1.0.0
 *
 * @package Envira_Fullscreen
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Metabox class.
 *
 * @since 1.0.4
 *
 * @package Envira_Fullscreen
 * @author  Envira Team
 */
class Envira_Fullscreen_Metaboxes {

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
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Galleries.
		add_action( 'envira_gallery_lightbox_box', array( $this, 'gallery_settings' ) );
		add_filter( 'envira_gallery_save_settings', array( $this, 'gallery_save' ), 10, 2 );

		// Albums.
		add_action( 'envira_albums_lightbox_box', array( $this, 'album_settings' ) );
		add_filter( 'envira_albums_save_settings', array( $this, 'album_save' ), 10, 2 );

	}

	/**
	 * Adds addon setting to the Config tab for Galleries
	 *
	 * @since 1.0.0
	 *
	 * @param object $post The current post object.
	 */
	public function gallery_settings( $post ) {

		$instance = Envira_Gallery_Metaboxes::get_instance();
		wp_nonce_field( 'envira_fullscreen_save_settings', 'envira_fullscreen_nonce' );
		?>
		<tr id="envira-config-fullscreen-box">
			<th scope="row">
				<label for="envira-config-fullscreen"><?php esc_html_e( 'Enable Fullscreen View?', 'envira-fullscreen' ); ?></label>
			</th>
			<td>
				<input id="envira-config-fullscreen" type="checkbox" name="_envira_gallery[fullscreen]" value="<?php echo esc_html( $instance->get_config( 'fullscreen', $instance->get_config_default( 'fullscreen' ) ) ); ?>" <?php checked( $instance->get_config( 'fullscreen', $instance->get_config_default( 'fullscreen' ) ), 1 ); ?> />
				<span class="description"><?php esc_html_e( 'Enables or disables native fullscreen mode (for browsers that support it) for the gallery.', 'envira-fullscreen' ); ?></span>
			</td>
		</tr>
		<tr id="envira-config-open-fullscreen-box">
			<th scope="row">
				<label for="envira-config-open-fullscreen"><?php esc_html_e( 'Enable Fullscreen on Open?', 'envira-fullscreen' ); ?></label>
			</th>
			<td>
				<input id="envira-config-open-fullscreen" type="checkbox" name="_envira_gallery[open_fullscreen]" value="<?php echo esc_html( $instance->get_config( 'open_fullscreen', $instance->get_config_default( 'open_fullscreen' ) ) ); ?>" <?php checked( $instance->get_config( 'open_fullscreen', $instance->get_config_default( 'open_fullscreen' ) ), 1 ); ?> />
				<span class="description"><?php esc_html_e( 'Enables Lightbox opening in fullscreen mode.', 'envira-fullscreen' ); ?></span>
			</td>
		</tr>
		<?php

	}

	/**
	 * Saves the addon setting for Galleries
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings  Array of settings to be saved.
	 * @param int   $post_id     The current post ID.
	 * @return array $settings Amended array of settings to be saved.
	 */
	public function gallery_save( $settings, $post_id ) {

		if (
			! isset( $_POST['_envira_gallery'], $_POST['envira_fullscreen_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['envira_fullscreen_nonce'] ), 'envira_fullscreen_save_settings' )
		) {
			return $settings;
		}

		$settings['config']['fullscreen']      = isset( $_POST['_envira_gallery']['fullscreen'] ) ? 1 : 0;
		$settings['config']['open_fullscreen'] = isset( $_POST['_envira_gallery']['open_fullscreen'] ) ? 1 : 0;

		return $settings;

	}

	/**
	 * Adds addon setting to the Config tab for Albums
	 *
	 * @since 1.0.0
	 *
	 * @param object $post The current post object.
	 */
	public function album_settings( $post ) {

		$instance = Envira_Albums_Metaboxes::get_instance();
		wp_nonce_field( 'envira_fullscreen_save_settings', 'envira_fullscreen_nonce' );

		?>
		<tr id="envira-config-fullscreen-box">
			<th scope="row">
				<label for="envira-config-fullscreen"><?php esc_html_e( 'Enable Fullscreen View?', 'envira-fullscreen' ); ?></label>
			</th>
			<td>
				<input id="envira-config-fullscreen" type="checkbox" name="_eg_album_data[config][fullscreen]" value="<?php echo esc_html( $instance->get_config( 'fullscreen', $instance->get_config_default( 'fullscreen' ) ) ); ?>" <?php checked( $instance->get_config( 'fullscreen', $instance->get_config_default( 'fullscreen' ) ), 1 ); ?> />
				<span class="description"><?php esc_html_e( 'Enables or disables native fullscreen mode (for browsers that support it) for each gallery in the Album. Requires Lightbox to be enabled.', 'envira-fullscreen' ); ?></span>
			</td>
		</tr>
		<tr id="envira-config-open-fullscreen-box">
			<th scope="row">
				<label for="envira-config-open-fullscreen"><?php esc_html_e( 'Enable Fullscreen on Open?', 'envira-fullscreen' ); ?></label>
			</th>
			<td>
				<input id="envira-config-open-fullscreen" type="checkbox" name="_eg_album_data[config][open_fullscreen]" value="<?php echo esc_html( $instance->get_config( 'fullscreen', $instance->get_config_default( 'open_fullscreen' ) ) ); ?>" <?php checked( $instance->get_config( 'open_fullscreen', $instance->get_config_default( 'open_fullscreen' ) ), 1 ); ?> />
				<span class="description"><?php esc_html_e( 'Enables Lightbox opening in fullscreen mode.', 'envira-fullscreen' ); ?></span>
			</td>
		</tr>
		<?php

	}

	/**
	 * Saves the addon setting for Albums
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings  Array of settings to be saved.
	 * @param int   $post_id     The current post ID.
	 * @return array $settings Amended array of settings to be saved.
	 */
	public function album_save( $settings, $post_id ) {

		if (
			! isset( $_POST['_eg_album_data'], $_POST['envira_fullscreen_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['envira_fullscreen_nonce'] ), 'envira_fullscreen_save_settings' )
		) {
			return $settings;
		}

		$settings['config']['fullscreen']      = isset( $_POST['_eg_album_data']['config']['fullscreen'] ) ? 1 : 0;
		$settings['config']['open_fullscreen'] = isset( $_POST['_eg_album_data']['config']['open_fullscreen'] ) ? 1 : 0;

		return $settings;

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.4
	 *
	 * @return object The Envira_Fullscreen_Metaboxes object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Fullscreen_Metaboxes ) ) {
			self::$instance = new Envira_Fullscreen_Metaboxes();
		}

		return self::$instance;

	}

}

// Load the metabox class.
$envira_fullscreen_metaboxes = Envira_Fullscreen_Metaboxes::get_instance();
