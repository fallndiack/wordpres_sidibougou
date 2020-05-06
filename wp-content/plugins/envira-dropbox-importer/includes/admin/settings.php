<?php
/**
 * Settings class.
 *
 * @since 1.0.0
 *
 * @package Envira_Dropbox_Importer
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
 * @package Envira_Dropbox_Importer
 * @author  Envira Team
 */
class Envira_Dropbox_Importer_Settings {

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

		// Base and Common Classes.
		$this->base = Envira_Dropbox_Importer::get_instance();

		// Scripts.
		add_action( 'envira_gallery_settings_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'envira_gallery_metabox_styles', array( $this, 'styles' ) );

		// Tab in Settings.
		add_filter( 'envira_gallery_settings_tab_nav', array( $this, 'settings_tabs' ) );
		add_action( 'envira_gallery_tab_settings_dropbox', array( $this, 'settings_screen' ) );
		add_action( 'init', array( $this, 'settings_save' ) );
		add_action( 'init', array( $this, 'settings_save_settings' ) );

	}

	/**
	 * Enqueues scripts for the Settings screen for this Addon
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		wp_register_script( $this->base->plugin_slug . '-settings-script', plugins_url( 'assets/js/min/settings-min.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );
		wp_enqueue_script( $this->base->plugin_slug . '-settings-script' );
		wp_localize_script(
			$this->base->plugin_slug . '-settings-script',
			'envira_dropbox_importer_settings',
			array(
				'unlink' => __( 'Are you sure you want to unlink your Dropbox account? Existing images will not be affected, but you won\'t be able to import Dropbox images until you re-link your account.', 'envira-dropbox-importer' ),
			)
		);

	}

	/**
	 * Enqueues css scripts for particular admin screens
	 *
	 * @since 1.0.0
	 */
	public function styles() {

		$version = ( defined( 'ENVIRA_DEBUG' ) && ENVIRA_DEBUG === 'true' ) ? $version = time() . '-' . ENVIRA_VERSION : ENVIRA_VERSION;

		// Enqueue featured content styles.
		wp_enqueue_style( $this->base->plugin_slug . '-style', plugins_url( 'assets/css/dropbox-admin.css', $this->base->file ), array(), $version );

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

		$tabs['dropbox'] = __( 'Dropbox', 'envira-dropbox-importer' );

		return $tabs;

	}

	/**
	 * Callback for displaying the UI for standalone settings tab.
	 *
	 * @since 1.0.0
	 */
	public function settings_screen() {

		// Get settings.
		$settings = Envira_Dropbox_Importer_Common::get_instance()->get_settings();

		if ( ! empty( $settings['account_id'] ) && ( empty( $settings['display_name'] ) || empty( $settings['email'] ) ) ) {
			$account_info = Envira_Dropbox_Importer_Dropbox::get_instance()->get_account( $settings['account_id'] );
			if ( isset( $account_info->name->display_name ) ) {
				$settings['display_name'] = $account_info->name->display_name;
			}
			if ( isset( $account_info->email ) ) {
				$settings['email'] = $account_info->email;
			}
			update_option( $this->base->plugin_slug, $settings );
		}

		// Get Dropbox instance.
		$dropbox = Envira_Dropbox_Importer_Dropbox::get_instance();

		// Attempt to get auth URL to check for any errors with SDK first.
		try {
			$auth_url   = $dropbox->get_authorize_url();
			$auth_error = false;
		} catch ( Exception $e ) {
			$auth_error = $e->getMessage();
		}

		// guessing this shouldn't be here: print_r( $auth_error );.
		?>
		<div id="envira-settings-dropbox">
			<?php
			// Output notices.
			do_action( 'envira_gallery_settings_dropbox_importer_tab_notice' );
			?>

			<table class="form-table">
				<tbody>
					<form action="edit.php?post_type=envira&amp;page=envira-gallery-settings#!envira-tab-dropbox" method="post">
					<?php

					// if the following is the case, user had API v1 creds, and we'll ask them to re-connect.
					if ( ! empty( $settings['access_token'] ) && empty( $settings['account_id'] ) ) {
						?>

							<div class="error below-h2"><p><?php esc_html_e( 'You must reauthenticate with your Dropbox account. Get a new code and save below. ', 'envira-dropbox-importer' ); ?></p></div>

						<?php
					}

					// If we have a valid access token, show a notice so the user knows they've already authenticated.
					if ( ! empty( $settings['account_id'] ) ) {
						// Authenticated
						// Tell the user which Dropbox account they've linked to, with an option to unlink.
						try {
							$account = $dropbox->get_account();
							if ( is_wp_error( $account ) ) {
								$dropbox_error = false;
								if ( count( $account->get_error_message() ) >= 1 ) {
									$dropbox_error = $account->get_error_message( 'dropbox_api' );
								}

								?>
								<div class="error below-h2"><p><?php esc_html_e( 'Dropbox encountered an error: ', 'envira-dropbox-importer' ); ?><strong><?php echo esc_html( $dropbox_error ); ?></strong></p></div>
								<?php
							} else {
								?>
								<tr id="envira-dropbox-importer-box">
								<th scope="row">
									<label for="envira-dropbox-importer"><?php esc_html_e( 'Authenticated', 'envira-dropbox-importer' ); ?></label>
								</th>
								<td>
									<p class="description">
									<?php
									esc_html_e( 'Thanks - you\'ve successfully authenticated with the Dropbox account ', 'envira-dropbox-importer' );
									?>
									<?php if ( isset( $settings['display_name'] ) && isset( $settings['email'] ) ) { ?>
										<strong>
												<?php echo esc_html( $settings['display_name'] ) . ' (' . esc_html( $settings['email'] ) . ')'; ?>
											</strong>
											<?php } ?>
									</p>
								</td>
								</tr>
								<?php } ?>
							<?php
						} catch ( Exception $e ) {
							$auth_error = $e->getMessage();
							?>
							<div class="error below-h2"><p>
								<?php esc_html_e( 'Dropbox encountered an error while trying to initialize: ', 'envira-dropbox-importer' ); ?>
								<?php echo '<strong>'; ?>
								<?php esc_html( $auth_error ); ?>
								<?php echo '</strong>'; ?>
								</p></div>
							<?php
						}
						?>
						<tr id="envira-dropbox-importer-box">
							<th scope="row">
								&nbsp;
							</th>
							<td>
								<p>
									<a href="edit.php?post_type=envira&amp;action=unlink&amp;page=envira-gallery-settings#!envira-tab-dropbox" class="button envira-dropbox-importer-unlink">
									<?php esc_html_e( 'Unlink Dropbox Account', 'envira-dropbox-importer' ); ?>
									</a>
								</p>
							</td>
						</tr>
						<?php
					} elseif ( $auth_error ) {
						?>
							<div class="error below-h2"><p>
								<?php esc_html_e( 'Dropbox encountered an error while trying to initialize: ', 'envira-dropbox-importer' ); ?>
								<?php echo '<strong>'; ?>
								<?php esc_html( $auth_error ); ?>
								<?php echo '</strong>'; ?>
								</p></div>
						<?php
					} else {
						// Not Authenticated
						// Get Dropbox auth URL.
						$auth_url = $dropbox->get_authorize_url();
						?>
						<tr id="envira-settings-slug-box">
							<th scope="row">
								<label for="envira-gallery-slug"><?php esc_html_e( 'Code', 'envira-dropbox-importer' ); ?></label>
							</th>
							<td>
								<input type="text" name="envira-dropbox-importer-code" id="envira-gallery-dropbox-code" value="<?php echo esc_html( $settings['code'] ); ?>" />
								<a href="<?php echo esc_url( $auth_url ); ?>" class="button button-primary" target="_blank"><?php esc_html_e( 'Get Code', 'envira-dropbox-importer' ); ?></a>
							<?php wp_nonce_field( 'envira-dropbox-importer-nonce', 'envira-dropbox-importer-nonce' ); ?>
								<p class="description"><?php esc_html_e( 'Enter the code on the Dropbox authorization screen.', 'envira-dropbox-importer' ); ?></p>
							</td>
							</tr>

							<tr class="no-bottom-border">
								<th scope="row"><?php submit_button( __( 'Save Code', 'envira-dropbox-importer' ), 'primary', 'envira-gallery-verify-submit', false ); ?></th>
								<td>&nbsp;</td>
							</tr>
						<?php
					}
					?>


					</form>

					<form action="edit.php?post_type=envira&amp;page=envira-gallery-settings#!envira-tab-dropbox" method="post">

							<tr id="envira-dropbox-thumbnails-box">
							<th scope="row">
								Show Thumbnails
							</th>
							<td>
							<input type="checkbox" name="envira-dropbox-thumbnail-view" id="envira-dropbox-thumbnail-view" value="yes"  
							<?php
							if ( isset( $settings['thumbnail_view'] ) && 'yes' === $settings['thumbnail_view'] ) {
								?>
									checked="checked"<?php } ?> />
							<?php wp_nonce_field( 'envira-dropbox-thumbnails-nonce', 'envira-dropbox-thumbnails-nonce' ); ?>
							<span class="description"><?php esc_html_e( 'Note: Showing Thumbnails Might Slow Down Loading Of Large Dropbox Folders', 'envira-dropbox-importer' ); ?></span>
						</td>

						</tr>
						<?php
						/*
						<tr id="envira-dropbox-filename-order-box">
						<th scope="row">
							Filename Order
						</th>
						<td>
							<select name="envira-dropbox-filename-order" id="envira-dropbox-filename-order">
								<option value="" <?php if ( isset( $settings['filename_order'] ) && $settings['filename_order'] == "" ) { ?>selected="selected"<?php } ?>>No Sorting</option>
								<option value="new-to-old" <?php if ( isset( $settings['filename_order'] ) && $settings['filename_order'] == "new-to-old" ) { ?>selected="selected"<?php } ?>>Newer To Older</option>
								<option value="old-to-new" <?php if ( isset( $settings['filename_order'] ) && $settings['filename_order'] == "old-to-new" ) { ?>selected="selected"<?php } ?>>Older To Newer</option>
								</select>
							</td>

						</tr>
						*/
						?>

						<tr>
							<th scope="row"><?php submit_button( __( 'Save Settings', 'envira-gallery' ), 'primary', 'envira-gallery-verify-submit', false ); ?></th>
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
		if ( ! isset( $_REQUEST ) ) {
			return;
		}
		// Check if the users can manage options.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Unlink?
		if ( isset( $_REQUEST['action'] ) && 'unlink' === $_REQUEST['action'] ) { // @codingStandardsIgnoreLine
			delete_option( $this->base->plugin_slug );
			add_action( 'envira_gallery_settings_dropbox_importer_tab_notice', array( $this, 'notice_unlink_success' ) );
			return;
		}

		// Link Dropbox Account
		// Check nonce exists.
		if ( ! isset( $_REQUEST['envira-dropbox-importer-nonce'] ) ) {
			return;
		}

		// Check nonce is valid.
		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['envira-dropbox-importer-nonce'] ) ), 'envira-dropbox-importer-nonce' ) ) {
			add_action( 'envira_gallery_settings_dropbox_importer_tab_notice', array( $this, 'notice_nonce' ) );
			return;
		}

		// Check code exists.
		if ( empty( $_REQUEST['envira-dropbox-importer-code'] ) ) {
			return;
		}

		// Get the access token
		// $access_token = Envira_Dropbox_Importer_Dropbox::get_instance()->get_settings( $_POST['envira-dropbox-importer-code'] );
		// if ( ! is_array( $access_token ) ) {
		// add_action( 'envira_gallery_settings_dropbox_importer_tab_notice', array( $this, 'notice_code' ) );
		// return;
		// }
		// OK - save code, access token and user ID
		// Get existing settings.
		$dropbox_settings = isset( $_POST['envira-dropbox-importer-code'] ) ? Envira_Dropbox_Importer_Dropbox::get_instance()->get_access_token( sanitize_text_field( wp_unslash( $_POST['envira-dropbox-importer-code'] ) ) ) : false;

		if ( ! $dropbox_settings || ! empty( $dropbox_settings->error ) ) {
			echo '---------------------';
			echo esc_html( $dropbox_setings->error );
			return;
		}

		// Save code.
		$settings['code']         = isset( $_POST['envira-dropbox-importer-code'] ) ? sanitize_text_field( wp_unslash( $_POST['envira-dropbox-importer-code'] ) ) : false;
		$settings['access_token'] = $dropbox_settings['access_token'];
		$settings['user_id']      = $dropbox_settings['uid'];
		$settings['account_id']   = $dropbox_settings['account_id'];
		$settings['token_type']   = $dropbox_settings['token_type'];
		update_option( $this->base->plugin_slug, $settings );

		// Grab user information, so we can ID the email address associated with the account.
		$account_info = Envira_Dropbox_Importer_Dropbox::get_instance()->get_account();

		if ( ! $account_info ) {
			return;
		}

		// Save account info.
		$settings['display_name'] = $account_info->name->display_name;
		$settings['email']        = $account_info->email;
		update_option( $this->base->plugin_slug, $settings );

		// Output success notice.
		add_action( 'envira_gallery_settings_dropbox_importer_tab_notice', array( $this, 'notice_link_success' ) );

	}


	/**
	 * Callback for saving the settings
	 *
	 * @since 1.0.0
	 */
	public function settings_save_settings() {

		// Check we saved some settings.
		if ( ! isset( $_REQUEST ) ) {
			return;
		}
		// Check if the users can manage options.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check nonce exists.
		if ( ! isset( $_REQUEST['envira-dropbox-thumbnails-nonce'] ) ) {
			return;
		}

		// Check nonce is valid.
		if ( ! wp_verify_nonce( sanitize_key( $_REQUEST['envira-dropbox-thumbnails-nonce'] ), 'envira-dropbox-thumbnails-nonce' ) ) {
			add_action( 'envira_gallery_settings_dropbox_importer_tab_notice', array( $this, 'notice_nonce' ) );
			return;
		}

		// Get settings.
		$settings = Envira_Dropbox_Importer_Common::get_instance()->get_settings();

		// Save settings.
		if ( ! empty( $_POST['envira-dropbox-thumbnail-view'] ) ) {
			$settings['thumbnail_view'] = sanitize_text_field( wp_unslash( $_POST['envira-dropbox-thumbnail-view'] ) );
		} else {
			$settings['thumbnail_view'] = '';
		}
		if ( ! empty( $_POST['envira-dropbox-filename-order'] ) ) {
			$settings['filename_order'] = sanitize_text_field( wp_unslash( $_POST['envira-dropbox-filename-order'] ) );
		} else {
			$settings['filename_order'] = '';
		}
		update_option( $this->base->plugin_slug, $settings );

		// Output success notice.
		add_action( 'envira_gallery_settings_dropbox_importer_tab_notice', array( $this, 'notice_link_success' ) );

	}


	/**
	 * Outputs a message to tell the user that the nonce field is invalid
	 *
	 * @since 1.0.0
	 */
	public function notice_nonce() {

		?>
		<div class="notice error below-h2">
			<p><?php esc_html_e( 'The nonce field is invalid.', 'envira-dropbox-importer' ); ?></p>
		</div>
		<?php

	}

	/**
	 * Outputs a message to tell the user that the Dropbox Code is invalid
	 *
	 * @since 1.0.0
	 */
	public function notice_code() {

		?>
		<div class="notice error below-h2">
			<p><?php esc_html_e( 'The Dropbox code is invalid.', 'envira-dropbox-importer' ); ?></p>
		</div>
		<?php

	}

	/**
	 * Outputs a message to tell the user that settings are saved
	 *
	 * @since 1.0.0
	 */
	public function notice_link_success() {

		?>
		<div class="notice updated below-h2">
			<p><?php esc_html_e( 'Dropbox settings updated successfully!', 'envira-dropbox-importer' ); ?></p>
		</div>
		<?php

	}

	/**
	 * Outputs a message to tell the user that their Dropbox account has been unlinked
	 *
	 * @since 1.0.0
	 */
	public function notice_unlink_success() {

		?>
		<div class="notice updated below-h2">
			<p><?php esc_html_e( 'Dropbox account unlinked.', 'envira-dropbox-importer' ); ?></p>
		</div>
		<?php

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Dropbox_Importer_Settings object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Dropbox_Importer_Settings ) ) {
			self::$instance = new Envira_Dropbox_Importer_Settings();
		}

		return self::$instance;

	}

}

// Load the metabox class.
$envira_dropbox_importer_settings = Envira_Dropbox_Importer_Settings::get_instance();
