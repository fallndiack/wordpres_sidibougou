<?php
/**
 * Settings class.
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
 * Settings class.
 *
 * @since 1.0.0
 *
 * @package Envira_Exif
 * @author  Envira Team
 */
class Envira_Exif_Settings {

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

		// Tab in Settings.
		add_filter( 'envira_gallery_settings_tab_nav', array( $this, 'settings_tabs' ) );
		add_action( 'envira_gallery_tab_settings_exif', array( $this, 'settings_screen' ) );
		add_action( 'init', array( $this, 'settings_save' ) );

	}

	/**
	 * Add a tab to the Envira Gallery Settings screen
	 *
	 * @since 1.0.0
	 *
	 * @param array $tabs Existing tabs.
	 * @return array New tabs
	 */
	public function settings_tabs( $tabs ) {

		$tabs['exif'] = __( 'EXIF', 'envira-exif' );

		return $tabs;

	}

	/**
	 * Callback for displaying the UI for standalone settings tab.
	 *
	 * @since 1.0.0
	 */
	public function settings_screen() {

		// Get settings.
		$exif_tags = get_option( 'envira_exif_tags' );
		?>
		<div id="envira-settings-standalone">
			<?php
			// Output notices.
			do_action( 'envira_gallery_settings_exif_tab_notice' );
			?>

			<table class="form-table">
				<tbody>
					<form action="edit.php?post_type=envira&page=envira-gallery-settings#!envira-tab-exif" method="post">
						<tr id="envira-settings-exif-tags-box">
							<th scope="row">
								<label for="envira-gallery-slug"><?php esc_html_e( 'Store EXIF Keywords in Tags Addon?', 'envira-exif' ); ?></label>
							</th>
							<td>
								<input type="checkbox" name="envira-exif-tags" id="envira-exif-tags" value="1" <?php checked( $exif_tags, 1 ); ?> />
								<?php wp_nonce_field( 'envira-exif-nonce', 'envira-exif-nonce' ); ?>
								<p class="description"><?php esc_html_e( 'If enabled, automatically stores any EXIF keywords as Tags when uploading an image.', 'envira-exif' ); ?></p>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php submit_button( __( 'Save', 'envira-exif' ), 'primary', 'envira-gallery-verify-submit', false ); ?></th>
							<td>&nbsp;</td>
						</tr>
					</form>
				</tbody>
			</table>
		</div>
		<?php

	}

	/**
	 * Callback for saving the settings
	 *
	 * @since 1.0.0
	 */
	public function settings_save() {

		// Check we saved some settings.
		if ( ! isset( $_POST ) ) {
			return;
		}

		// Check nonce exists.
		if ( ! isset( $_POST['envira-exif-nonce'] ) ) {
			return;
		}

		// Check nonce is valid.
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['envira-exif-nonce'] ) ), 'envira-exif-nonce' ) ) {
			return;
		}

		// Update settings.
		$exif_tags = ( isset( $_POST['envira-exif-tags'] ) ? true : false );
		update_option( 'envira_exif_tags', $exif_tags );

		// Output success notice.
		add_action( 'envira_gallery_settings_exif_tab_notice', array( $this, 'notice_success' ) );

	}

	/**
	 * Outputs a message to tell the user that settings are saved
	 *
	 * @since 1.0.0
	 */
	public function notice_success() {

		?>
		<div class="notice updated below-h2">
			<p><?php esc_html_e( 'Settings updated successfully!', 'envira-exif' ); ?></p>
		</div>
		<?php

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Exif_Settings object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Exif_Settings ) ) {
			self::$instance = new Envira_Exif_Settings();
		}

		return self::$instance;

	}

}

// Load the settings class.
$envira_exif_settings = Envira_Exif_Settings::get_instance();
