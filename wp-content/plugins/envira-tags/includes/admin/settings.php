<?php
/**
 * Settings class.
 *
 * @since 1.3.1
 *
 * @package Envira_Tags_Settings
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings class.
 *
 * @since 1.3.1
 *
 * @package Envira_Tags_Settings
 * @author  Envira Team
 */
class Envira_Tags_Settings {

	/**
	 * Holds the class object.
	 *
	 * @since 1.3.1
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.3.1
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Holds the base class object.
	 *
	 * @since 1.3.1
	 *
	 * @var object
	 */
	public $base;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.3.1
	 */
	public function __construct() {

		// Base and Common Classes.
		$this->base = Envira_Tags::get_instance();

		// Tab in Settings.
		add_filter( 'envira_gallery_settings_tab_nav', array( $this, 'settings_tabs' ) );
		add_action( 'envira_gallery_tab_settings_tags', array( $this, 'settings_screen' ) );
		add_action( 'init', array( $this, 'settings_save' ) );

	}


	/**
	 * Add a tab to the Envira Gallery Settings screen
	 *
	 * @since 1.3.1
	 *
	 * @param array $tabs Existing tabs.
	 * @return array New tabs
	 */
	public function settings_tabs( $tabs ) {

		$tabs['tags'] = __( 'Tags', 'envira-tags' );

		return $tabs;

	}

	/**
	 * Callback for displaying the UI for standalone settings tab.
	 *
	 * @since 1.3.1
	 */
	public function settings_screen() {

		// Get settings.
		$settings = Envira_Tags_Common::get_instance()->get_settings();
		?>
		<div id="envira-settings-tags">
			<?php
			// Output notice.
			do_action( 'envira_gallery_settings_tags_tab_notice' );
			?>

			<table class="form-table">
				<tbody>
					<form action="edit.php?post_type=envira&amp;page=envira-gallery-settings#!envira-tab-tags" method="post">

						<tr id="envira-settings-imagga-enabled-box">
							<th scope="row">
								<label for="envira-tags-imagga-enabled"><?php esc_html_e( 'Imagga: Enable Auto Tagging?', 'envira-tags' ); ?></label>
							</th>
							<td>
								<input type="checkbox" name="envira-tags-imagga-enabled" id="envira-tags-imagga-enabled" value="1"<?php checked( $settings['imagga_enabled'], 1 ); ?> />
								<p class="description">

								<?php esc_html_e( 'Imagga will read each image you upload, and automatically tag it. API v2 support only.', 'envira-tags' ); ?> <a href="https://imagga.com/" target="_blank"><?php esc_html_e( 'Find out more', 'envira-tags' ); ?></a></p>
							</td>
						</tr>

						<tr id="envira-settings-imagga-enabled-box">
							<th scope="row">
								<label for="envira-tags-imagga-retag"><?php esc_html_e( 'Imagga: Retag?', 'envira-tags' ); ?></label>
							</th>
							<td>
								<input type="checkbox" name="envira-tags-imagga-retag" id="envira-tags-imagga-retag" value="1"<?php checked( $settings['imagga_retag'], 1 ); ?> />
								<p class="description">

								<?php esc_html_e( 'When auto-tagging is ON, Imagga will use an API call to retag images added to a gallery (unchecking this will ignore images already tagged and save API calls).', 'envira-tags' ); ?> </p>
							</td>
						</tr>

						<tr id="envira-settings-imagga-authorization-box">
							<th scope="row">
								<label for="envira-tags-imagga-authorization-code"><?php esc_html_e( 'Imagga: Authorization Code', 'envira-tags' ); ?></label>
							</th>
							<td>
								<input type="text" name="envira-tags-imagga-authorization-code" id="envira-tags-imagga-authorization-code" value="<?php echo esc_html( $settings['imagga_authorization_code'] ); ?>" />
								<p class="description"><a href="https://imagga.com/auth/signup/hacker" target="_blank"><?php esc_html_e( 'Sign up for the Imagga API', 'envira-tags' ); ?></a>,<?php esc_html_e( ' and make a note of your Authorization code once completed. Enter the code here.', 'envira-tags' ); ?></p>
							</td>
						</tr>

						<tr id="envira-settings-imagga-confidence-box">
							<th scope="row">
								<label for="envira-tags-imagga-confidence"><?php esc_html_e( 'Imagga: Minimum Confidence', 'envira-tags' ); ?></label>
							</th>
							<td>
								<input type="number" name="envira-tags-imagga-confidence" id="envira-tags-imagga-confidence" value="<?php echo esc_html( $settings['imagga_confidence'] ); ?>" />
								<span class="envira-unit">%</span>
								<p class="description"><?php esc_html_e( 'If specified, only adds tags to images where Imagga matches or exceeds the above confidence percentage rating. A lower confidence means it is more likely less accurate tags will be included in an image.', 'envira-tags' ); ?></p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<?php
								wp_nonce_field( 'envira-tags-nonce', 'envira-tags-nonce' );
								submit_button( __( 'Save', 'envira-tag' ), 'primary', 'envira-gallery-verify-submit', false );
								?>
							</th>
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
	 * @since 1.3.1
	 */
	public function settings_save() {

		// Check we saved some settings.
		if ( ! isset( $_REQUEST ) ) {
			return;
		}

		// Check nonce is valid.
		if ( ! isset( $_REQUEST['envira-tags-nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_key( $_REQUEST['envira-tags-nonce'] ), 'envira-tags-nonce' ) ) {
			add_action( 'envira_gallery_settings_tags_tab_notice', array( $this, 'notice_nonce' ) );
			return;
		}

		// Get existing settings.
		$instance = Envira_Tags_Common::get_instance();
		$settings = $instance->get_settings();

		// Build settings array.
		$settings_sanitized                    = array_map( 'sanitize_text_field', wp_unslash( $_POST ) );
		$settings['imagga_enabled']            = ( isset( $_POST['envira-tags-imagga-enabled'] ) ? true : false );
		$settings['imagga_retag']              = ( isset( $_POST['envira-tags-imagga-retag'] ) ? true : false );
		$settings['imagga_authorization_code'] = sanitize_text_field( wp_unslash( $settings_sanitized['envira-tags-imagga-authorization-code'] ) );
		$settings['imagga_confidence']         = sanitize_text_field( wp_unslash( $settings_sanitized['envira-tags-imagga-confidence'] ) );

		// Save settings.
		$instance->save_settings( $settings );

		// Output success notice.
		add_action( 'envira_gallery_settings_tags_tab_notice', array( $this, 'notice_success' ) );

	}

	/**
	 * Outputs a message to tell the user that the nonce field is invalid
	 *
	 * @since 1.3.1
	 */
	public function notice_nonce() {

		?>
		<div class="notice error below-h2">
			<p><?php esc_html_e( 'The nonce field is invalid.', 'envira-tags' ); ?></p>
		</div>
		<?php

	}

	/**
	 * Outputs a message to tell the user that settings are saved
	 *
	 * @since 1.3.1
	 */
	public function notice_success() {

		?>
		<div class="notice updated below-h2">
			<p><?php esc_html_e( 'Tags settings updated successfully!', 'envira-tags' ); ?></p>
		</div>
		<?php

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.3.1
	 *
	 * @return object The Envira_Tags_Settings object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Tags_Settings ) ) {
			self::$instance = new Envira_Tags_Settings();
		}

		return self::$instance;

	}

}

// Load the metabox class.
$envira_tags_settings = Envira_Tags_Settings::get_instance();
