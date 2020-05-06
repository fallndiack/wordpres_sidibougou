<?php
/**
 * Settings class.
 *
 * @since 1.1.2
 *
 * @package Envira_Defaults
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings class.
 *
 * @since 1.1.2
 *
 * @package Envira_Defaults
 * @author  Envira Team
 */
class Envira_Defaults_Settings {

	/**
	 * Holds the class object.
	 *
	 * @since 1.1.2
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.1.2
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.1.2
	 */
	public function __construct() {

		add_filter( 'envira_gallery_settings_tab_nav', array( $this, 'tabs' ) );
		add_action( 'envira_gallery_tab_settings_defaults', array( $this, 'settings' ) );
		add_action( 'init', array( $this, 'save' ) );

	}

	/**
	 * Add a tab to the Envira Gallery Settings screen
	 *
	 * @since 1.1.2
	 *
	 * @param array $tabs Existing tabs.
	 * @return array New tabs
	 */
	public function tabs( $tabs ) {

		$tabs['defaults'] = __( 'Defaults', 'envira-defaults' );

		return $tabs;

	}

	/**
	 * Outputs settings screen for the Defaults Tab.
	 *
	 * @since 1.1.2
	 */
	public function settings() {

		// Get settings.
		$disable_modal = Envira_Defaults_Common::get_instance()->get_setting( 'disable_modal' );
		?>
		<div id="envira-settings-defaults">
			<?php
			// Output notice.
			do_action( 'envira_gallery_settings_defaults_tab_notice' );
			?>

			<table class="form-table">
				<tbody>
					<form action="edit.php?post_type=envira&amp;page=envira-gallery-settings#!envira-tab-defaults" method="post">
						<tr id="envira-defaults-disable-modal-box">
							<th scope="row">
								<label for="envira-defaults-disable-modal"><?php esc_html_e( 'Disable Gallery / Album Selection Modal', 'envira-defaults' ); ?></label>
							</th>
							<td>
								<input type="checkbox" name="envira-defaults-disable-modal" id="envira-defaults-disable-modal" value="1"<?php checked( $disable_modal, 1 ); ?> />
								<p class="description">
									<?php esc_html_e( 'If checked, prevents the Gallery / Album selection modal window from displaying when any user clicks on Add New for a Gallery or Album.  Galleries / Albums will just inherit the Default Gallery / Album configuration.', 'envira-defaults' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php submit_button( __( 'Save', 'envira-defaults' ), 'primary', 'envira-gallery-verify-submit', false ); ?></th>
							<td><?php wp_nonce_field( 'envira-defaults-nonce', 'envira-defaults-nonce' ); ?></td>
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
		if ( ! isset( $_POST['envira-defaults-nonce'] ) ) {
			return;
		}

		// Check nonce is valid.
		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['envira-defaults-nonce'] ) ), 'envira-defaults-nonce' ) ) {
			add_action( 'envira_gallery_settings_defaults_tab_notice', array( $this, 'notice_nonce' ) );
			return;
		}

		// Save.
		$settings = array(
			'disable_modal' => ( isset( $_POST['envira-defaults-disable-modal'] ) ? 1 : 0 ),
		);
		update_option( 'envira-defaults', $settings );

		// Show confirmation that settings saved.
		add_action( 'envira_gallery_settings_defaults_tab_notice', array( $this, 'notice_saved' ) );

	}

	/**
	 * Outputs a WordPress style notification message to tell the user that the nonce field is invalid
	 *
	 * @since 1.0.0
	 */
	public function notice_nonce() {

		?>
		<div class="notice error below-h2">
			<p><?php echo ( esc_html__( 'The nonce field is invalid.', 'envira-defaults' ) ); ?></p>
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
			<p><?php echo ( esc_html__( 'Defaults settings saved!', 'envira-defaults' ) ); ?></p>
		</div>
		<?php

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Defaults_Settings object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Defaults_Settings ) ) {
			self::$instance = new Envira_Defaults_Settings();
		}

		return self::$instance;

	}

}

// Load the settings class.
$envira_defaults_settings = Envira_Defaults_Settings::get_instance();
