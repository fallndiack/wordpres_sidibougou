<?php
/**
 * Settings class.
 *
 * @since 1.0.0
 *
 * @package Envira_Lightroom
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Envira_Lightroom_Settings class.
 *
 * @since 1.0.0
 *
 * @package Envira_Lightroom
 * @author  Envira Team
 */
class Envira_Lightroom_Settings {

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
	 * Holds the common class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $common;

	/**
	 * Holds settings.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $settings;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Load the base class object.
		$this->base     = Envira_Gallery::get_instance();
		$this->settings = Envira_Gallery_Settings::get_instance();

		// Actions.
		add_action( 'init', array( $this, 'scripts' ) );
		add_filter( 'envira_gallery_settings_tab_nav', array( $this, 'tabs' ) );
		add_action( 'envira_gallery_tab_settings_lightroom', array( $this, 'settings' ) );
		add_action( 'init', array( $this, 'save' ) );

	}

	/**
	 * Loads necessary settings scripts.
	 *
	 * @since 1.0.6
	 */
	public function scripts() {

		// Load necessary settings scripts.
		wp_register_script( $this->base->plugin_slug . '-clipboard-script', plugins_url( 'assets/js/min/clipboard-min.js', $this->base->file ), array( 'jquery' ), $this->base->version, false );
		wp_enqueue_script( $this->base->plugin_slug . '-clipboard-script' );

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

		$tabs['lightroom'] = __( 'Lightroom', 'envira-lightroom' );

		return $tabs;

	}

	/**
	 * Outputs settings screen for the settings tab.
	 *
	 * @since 1.0.0
	 */
	public function settings() {

		// Get access token.
		$instance     = Envira_Lightroom_Common::get_instance();
		$user_id      = $instance->get_user_id();
		$access_token = $instance->get_access_token();
		$delete       = $this->settings->get_setting( 'lightroom_delete' );

		?>
		<div id="envira-settings-lightroom">
			<?php
			// Output notices.
			do_action( 'envira_gallery_settings_lightroom_tab_notice' );
			?>

			<table class="form-table">
				<tbody>
					<form action="edit.php?post_type=envira&amp;page=envira-gallery-settings#!envira-tab-lightroom" method="post">
						<tr id="envira-settings-lightroom-user-ID-box">
							<th scope="row">
								<label for="envira-lightroom-user-id"><?php esc_html_e( 'WordPress User', 'envira-lightroom' ); ?></label>
							</th>
							<td>
								<?php

								$all_users      = get_users();
								$specific_users = array();

								foreach ( $all_users as $user ) {

									if ( $user->has_cap( 'create_envira_galleries' ) ) {
										$specific_users[] = $user->ID;
									}
								}

								wp_dropdown_users(
									array(
										'selected'         => $user_id,
										'name'             => 'envira-lightroom-user-id',
										'id'               => 'envira-lightroom-user-id',
										'include'          => $specific_users,
										'show_option_none' => __( 'No user selected', 'envira-lightroom' ),
									)
								);

								wp_nonce_field( 'envira-lightroom-nonce', 'envira-lightroom-nonce' );
								?>
								<p class="description">
								<?php
									esc_html_e( 'The WordPress User that Lightroom will use to create and manage galleries. Note that this User must have the relevent role & capabilities.', 'envira-lightroom' );
								?>
								</p>
							</td>
						</tr>
						<tr id="envira-settings-lightroom-access-token-box">
							<th scope="row">
								<label for="envira-lightroom-access-token"><?php esc_html_e( 'Access Token', 'envira-lightroom' ); ?></label>
							</th>
							<td>
								<input type="text" name="envira-lightroom-token" id="envira-lightroom-access-token" value="<?php echo esc_html( $access_token ); ?>" readonly />
								<a href="#" title="<?php esc_html_e( 'Copy Shortcode to Clipboard', 'envira-lightroom' ); ?>" data-clipboard-target="#envira-lightroom-access-token" class="button dashicons dashicons-clipboard envira-clipboard"></a>

								<?php submit_button( __( 'Generate New Access Token', 'envira-lightroom' ), 'primary', 'envira-lightroom-generate-access-token', false ); ?>
								<p class="description">
								<?php esc_html_e( 'Copy this access token, and enter it into the Access Token field in Lightroom Publishing Manager > Envira Galleries', 'envira-lightroom' ); ?>
								</p>
							</td>
						</tr>
						<tr id="envira-settings-lightroom-delete-box">
							<th scope="row">
								<label for="envira-settings-lightroom-delete"><?php esc_html_e( 'Remove Images', 'envira-lightroom' ); ?></label>
							</th>
							<td>
								<p class="description">
									<label for="envira-settings-lightroom-delete">
										<input type="checkbox" name="envira-settings-lightroom-delete" id="envira-settings-lightroom-delete" value="1" <?php checked( true, $delete ); ?> />
										<?php esc_html_e( 'Remove Images from server when lightroom replaces them', 'envira-lightroom' ); ?>
									</label>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php submit_button( __( 'Save', 'envira-lightroom' ), 'primary', 'envira-lightroom-save', false ); ?></th>
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
	public function save() {

		// Check we saved some settings.
		if ( ! $_POST ) { // @codingStandardsIgnoreLine
			return;
		}

		// Check nonce is valid.
		if ( ! isset( $_POST['envira-lightroom-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['envira-lightroom-nonce'] ) ), 'envira-lightroom-nonce' ) ) {
			return;
		}

		// If here, we've requested to do something.
		if ( isset( $_POST['envira-lightroom-generate-access-token'] ) ) {
			// Generate new access token.
			Envira_Lightroom_Common::get_instance()->generate_access_token();
			add_action( 'envira_gallery_settings_lightroom_tab_notice', array( $this, 'notice_generated_access_token' ) );
		}
		if ( isset( $_POST['envira-lightroom-save'] ) ) {
			// Save User ID.
			if ( isset( $_POST['envira-lightroom-user-id'] ) ) {
				Envira_Lightroom_Common::get_instance()->update_user_id( sanitize_text_field( wp_unslash( $_POST['envira-lightroom-user-id'] ) ) );
			}
			add_action( 'envira_gallery_settings_lightroom_tab_notice', array( $this, 'notice_updated_user_id' ) );
		}

		$this->settings->update_setting( 'lightroom_delete', empty( $_POST['envira-settings-lightroom-delete'] ) ? 0 : 1 );

		if ( isset( $_POST['envira-settings-lightroom-delete'] ) ) {
			// Generate new access token.
			add_action( 'envira_gallery_settings_lightroom_tab_notice', array( $this, 'notice_update' ) );
		}
	}

	/**
	 * Outputs a message to tell the user that a new access token was generated.
	 *
	 * @since 1.0.0
	 */
	public function notice_generated_access_token() {

		?>
		<div class="notice updated below-h2">
			<p><?php esc_html_e( 'Access Token has been generated!', 'envira-lightroom' ); ?></p>
		</div>
		<?php

	}
	/**
	 * Outputs a message to tell the user that a new access token was generated.
	 *
	 * @since 1.0.0
	 */
	public function notice_update() {

		?>
		<div class="notice updated below-h2">
			<p><?php esc_html_e( 'Settings have been updated!', 'envira-lightroom' ); ?></p>
		</div>
		<?php

	}

	/**
	 * Outputs a message to tell the user that the user ID has been saved
	 *
	 * @since 1.0.0
	 */
	public function notice_updated_user_id() {

		?>
		<div class="notice updated below-h2">
			<p><?php esc_html_e( 'User ID updated successfully. Any publishing from Lightroom will honor this user ID\'s roles and capabilities.', 'envira-lightroom' ); ?></p>
		</div>
		<?php

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Lightroom_Settings object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Lightroom_Settings ) ) {
			self::$instance = new Envira_Lightroom_Settings();
		}

		return self::$instance;

	}

}

// Load the settings class.
$envira_lightroom_settings = Envira_Lightroom_Settings::get_instance();
