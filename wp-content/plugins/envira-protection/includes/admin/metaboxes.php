<?php
/**
 * Metaboxes class.
 *
 * @since 1.0.0
 *
 * @package Envira_Gallery
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Metabox class.
 *
 * @since 1.0.9
 *
 * @package Envira_Protection
 * @author  Envira Team
 */
class Envira_Protection_Metaboxes {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.9
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.0.9
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
	 * @since 1.0.9
	 */
	public function __construct() {

		$this->base = Envira_Protection::get_instance();

		add_action( 'envira_gallery_metabox_scripts', array( $this, 'metabox_scripts' ) );
		add_action( 'envira_albums_metabox_scripts', array( $this, 'metabox_scripts' ) );

		// Gallery.
		add_action( 'envira_gallery_misc_box', array( $this, 'settings' ) );
		add_filter( 'envira_gallery_save_settings', array( $this, 'save_settings' ), 10, 2 );

		// Albums.
		add_action( 'envira_albums_misc_box', array( $this, 'settings' ) );
		add_filter( 'envira_albums_save_settings', array( $this, 'save_albums' ), 10, 2 );

	}

	/**
	 * Initializes scripts for the metabox admin.
	 *
	 * @since 1.0.0
	 */
	public function metabox_scripts() {
		// Conditional Fields.
		wp_register_script( $this->base->plugin_slug . '-conditional-fields-script', plugins_url( 'assets/js/min/conditional-fields-min.js', $this->base->file ), array( 'jquery', Envira_Gallery::get_instance()->plugin_slug . '-conditional-fields-script' ), $this->base->version, true );
		wp_enqueue_script( $this->base->plugin_slug . '-conditional-fields-script' );
	}

	/**
	 * Adds addon setting to the Misc tab.
	 *
	 * @since 1.0.9
	 *
	 * @param object $post The current post object.
	 */
	public function settings( $post ) {

		switch ( $post->post_type ) {
			/**
			* Gallery
			*/
			case 'envira':
				$instance = Envira_Gallery_Metaboxes::get_instance();
				$key      = '_envira_gallery';
				break;

			/**
			* Album
			*/
			case 'envira_album':
				$instance = Envira_Albums_Metaboxes::get_instance();
				$key      = '_eg_album_data[config]';
				break;
		}
		?>
		<tr class="envira-inserted-intro">
			<td colspan="2">
				<p class="envira-intro">
				<?php esc_html_e( 'Image Protection Settings', 'envira-protection' ); ?>
				</p>
			</td>
		</tr>
		<tr id="envira-config-protection">
			<th scope="row">
				<label for="envira-config-protection"><?php esc_html_e( 'Enable Image Protection?', 'envira-protection' ); ?></label>
			</th>
			<td>
				<input id="envira-config-protection" type="checkbox" name="<?php echo esc_html( $key ); ?>[protection]" value="<?php echo esc_attr( $instance->get_config( 'protection', esc_attr( $instance->get_config_default( 'protection' ) ) ) ); ?>" <?php checked( $instance->get_config( 'protection', $instance->get_config_default( 'protection' ) ), 1 ); ?> />
				<span class="description"><?php esc_html_e( 'Enables or disables protection against copying images in galleries Envira galleries or lightbox.', 'envira-protection' ); ?></span>
			</td>
		</tr>
		<tr id="envira-config-protection-popup">
			<th scope="row">
				<label for="envira-config-protection-popup"><?php esc_html_e( 'Enable Popup Alert?', 'envira-protection' ); ?></label>
			</th>
			<td>
				<input id="envira-config-protection-popup" type="checkbox" name="<?php echo esc_html( $key ); ?>[protection_popup]" value="<?php echo esc_attr( $instance->get_config( 'protection_popup', esc_attr( $instance->get_config_default( 'protection_popup' ) ) ) ); ?>" <?php checked( $instance->get_config( 'protection_popup', $instance->get_config_default( 'protection_popup' ) ), 1 ); ?> />
				<span class="description"><?php esc_html_e( 'Enables or disables a popup alert when a user attempts to copy an Envira image (gallery and lightbox).', 'envira-protection' ); ?></span>
			</td>
		</tr>
		<tr id="envira-config-protection-box-title">
			<th scope="row">
				<label for="envira-config-protection-title"><?php esc_html_e( 'Title', 'envira-protection' ); ?></label>
			</th>
			<td>
				<input id="envira-config-protection-title" name="<?php echo esc_html( $key ); ?>[protection_title]" value="<?php echo esc_attr( $instance->get_config( 'protection_title', esc_html( $instance->get_config_default( 'protection_title' ) ) ) ); ?>" />
				<p><span class="description"><?php esc_html_e( '(Optional) Headline that appears above the message. Plain text, no HTML.', 'envira-protection' ); ?></span></p>
			</td>
		</tr>
		<tr id="envira-config-protection-box-message">
			<th scope="row">
				<label for="envira-config-protection-message"><?php esc_html_e( 'Message', 'envira-protection' ); ?></label>
			</th>
			<td>
				<textarea id="envira-config-protection-message" name="<?php echo esc_html( $key ); ?>[protection_message]"><?php echo esc_attr( $instance->get_config( 'protection_message', esc_html( $instance->get_config_default( 'protection_message' ) ) ) ); ?></textarea>
				<span class="description"><?php esc_html_e( '(Required) Text displayed in the popup box. Plain text, no HTML.', 'envira-protection' ); ?></span>
			</td>
		</tr>
		<tr id="envira-config-protection-box-button">
			<th scope="row">
				<label for="envira-config-protection-message"><?php esc_html_e( 'Button Text', 'envira-protection' ); ?></label>
			</th>
			<td>
				<input id="envira-config-protection-message" name="<?php echo esc_html( $key ); ?>[protection_button_text]" value="<?php echo esc_attr( $instance->get_config( 'protection_button_text', esc_html( $instance->get_config_default( 'protection_button_text' ) ) ) ); ?>" placeholder="Ok" />
				<p><span class="description"><?php esc_html_e( '(Optional) Default text is \'Ok\'. Plain text, no HTML.', 'envira-protection' ); ?></span></p>
			</td>
		</tr>
		<?php

	}

	/**
	 * Saves the addon setting.
	 *
	 * @since 1.0.9
	 *
	 * @param array $settings  Array of settings to be saved.
	 * @param int   $post_id     The current post ID.
	 * @return array $settings Amended array of settings to be saved.
	 */
	public function save_settings( $settings, $post_id ) {

		// Bail out if we fail a security check.
		if ( ! isset( $_POST['envira-gallery'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['envira-gallery'] ) ), 'envira-gallery' ) || ! isset( $_POST['_envira_gallery'] ) ) {
			return;
		}

		// Gallery.
		if ( isset( $_POST['_envira_gallery'] ) ) {
			$settings['config']['protection']             = isset( $_POST['_envira_gallery']['protection'] ) ? 1 : 0;
			$settings['config']['protection_popup']       = isset( $_POST['_envira_gallery']['protection_popup'] ) ? 1 : 0;
			$settings['config']['protection_message']     = isset( $_POST['_envira_gallery']['protection_message'] ) ? wp_strip_all_tags( wp_unslash( $_POST['_envira_gallery']['protection_message'] ) ) : false;
			$settings['config']['protection_title']       = isset( $_POST['_envira_gallery']['protection_title'] ) ? wp_strip_all_tags( wp_unslash( $_POST['_envira_gallery']['protection_title'] ) ) : false;
			$settings['config']['protection_button_text'] = isset( $_POST['_envira_gallery']['protection_button_text'] ) ? wp_strip_all_tags( wp_unslash( $_POST['_envira_gallery']['protection_button_text'] ) ) : false;

		}

		return $settings;

	}

	/**
	 * Saves the album setting.
	 *
	 * @since 1.0.9
	 *
	 * @param array $settings  Array of settings to be saved.
	 * @param int   $post_id     The current post ID.
	 * @return array $settings Amended array of settings to be saved.
	 */
	public function save_albums( $settings, $post_id ) {

		// Bail out if we fail a security check.
		if ( ! isset( $_POST['envira-albums'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['envira-albums'] ) ), 'envira-albums' ) ) {
			return;
		}

		// Album.
		if ( isset( $_POST['_eg_album_data'] ) ) {
			$settings['config']['protection']             = isset( $_POST['_eg_album_data']['config']['protection'] ) ? 1 : 0;
			$settings['config']['protection_popup']       = isset( $_POST['_eg_album_data']['config']['protection_popup'] ) ? 1 : 0;
			$settings['config']['protection_message']     = isset( $_POST['_eg_album_data']['config']['protection_message'] ) ? wp_strip_all_tags( wp_unslash( $_POST['_eg_album_data']['config']['protection_message'] ) ) : '';
			$settings['config']['protection_title']       = isset( $_POST['_eg_album_data']['config']['protection_title'] ) ? wp_strip_all_tags( wp_unslash( $_POST['_eg_album_data']['config']['protection_title'] ) ) : '';
			$settings['config']['protection_button_text'] = isset( $_POST['_eg_album_data']['config']['protection_button_text'] ) ? wp_strip_all_tags( wp_unslash( $_POST['_eg_album_data']['config']['protection_button_text'] ) ) : '';

		}

		return $settings;
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.9
	 *
	 * @return object The Envira_Protection_Metaboxes object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;

	}

}

// Load the metaboxes class.
$envira_protection_metaboxes = Envira_Protection_Metaboxes::get_instance();
