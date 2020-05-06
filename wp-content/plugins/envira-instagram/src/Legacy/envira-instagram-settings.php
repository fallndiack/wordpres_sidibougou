<?php
/**
 * Settings
 *
 * @since 1.5.0
 *
 * @package Envira_Instagram
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

if ( ! class_exists( 'Envira_Instagram_Settings' ) ) :

	/**
	 * Settings
	 *
	 * @since 1.5.0
	 *
	 * @package Envira_Instagram
	 * @author  Envira Gallery Team <support@enviragallery.com>
	 */
	class Envira_Instagram_Settings { // @codingStandardsIgnoreLine - Firing off a duplicate warning?

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
		 * Defines which hook to apply notices to
		 *
		 * @since 1.0.5
		 *
		 * @var object
		 */
		public $notice_filter;

		/**
		 * Primary class constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			// Load the base class object.
			$this->base = Envira_Gallery::get_instance();

			// Actions.
			add_filter( 'envira_gallery_settings_tab_nav', array( $this, 'tabs' ) );
			add_action( 'envira_gallery_tab_settings_instagram', array( $this, 'settings' ) );
			add_action( 'init', array( $this, 'process' ) );

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

			$tabs['instagram'] = __( 'Instagram', 'envira-instagram' );

			return $tabs;

		}

		/**
		 * Outputs settings screen for the Proofing Tab.
		 *
		 * @since 1.0.0
		 */
		public function settings() {

			// Get settings and URLs to connect/disconnect Instagram.
			$common         = Envira_Instagram_Common::get_instance();
			$auth           = array();
			$total_accounts = 3;
			$slots          = array();
			for ( $x = 1; $x <= $total_accounts; $x++ ) {
				$temp_auth = $common->get_instagram_auth( $x );
				if ( $temp_auth ) {
					$slots[] = $temp_auth;
				}
			}

			// Note: the missing 'page=envira-gallery-settings#!envira-tab-instagram' parameter is deliberate.
			// Instagram strips this URL argument in the oAuth process, and would then throw a 400 redirect_uri mismatch error.
			// Envira's API will append the 'page=envira-gallery-settings#!envira-tab-instagram' parameter on the redirect back
			// to this site, ensuring everything works correctly.
			$connect_url = $common->get_oauth_url( 'edit.php?post_type=envira' );

			$envira_label = apply_filters( 'envira_whitelabel', false ) ? apply_filters( 'envira_whitelabel_name_singular', false ) : 'Envira';

			?>
			<div id="envira-settings-instagram">

				<h2><?php esc_html_e( 'Instagram Authorization Setup', 'envira-instagram' ); ?></h2>
				<p>
					<?php esc_html_e( 'Before you can create Instagram galleries, you need to authenticate ', 'envira-instagram' ); ?>
					<?php echo esc_html( $envira_label ); ?>
					<?php esc_html_e( ' with your Instagram accounts. Envira supports authorization with up to 3 unique Instagram accounts. You currently are using ', 'envira-instagram' ); ?>
					<?php echo count( $slots ); ?>
					<?php esc_html_e( 'slots.', 'envira-instagram' ); ?>
				</p>
				<p>Need assistance? <a target="_blank" href="https://enviragallery.com/docs/instagram-addon/">Click here</a> to read further documentation.</p>

				<?php if ( count( $slots ) < $total_accounts ) { ?>

					<p class="description" style="margin-top: 20px;">
						<a href="<?php echo esc_url( $connect_url ); ?>" class="button button-secondary envira-instagram-authorize">
							<?php if ( count( $slots ) === 0 ) { ?>
								<?php esc_html_e( 'Click here to Authenticate Your First Instagram Account With ', 'envira-instagram' ); ?>
								<?php echo esc_html( $envira_label ); ?>
							<?php } else { ?>
								<?php esc_html_e( 'Click here to Authenticate Another Instagram Account With ', 'envira-instagram' ); ?>
								<?php echo esc_html( $envira_label ); ?>
							<?php } ?>
						</a>
					</p>

				<?php } ?>

				<table class="form-table" style="margin-top: 40px;">
					<tbody>

				<?php

					$account_number = 1;

				foreach ( $slots as $slot ) {

					$disconnect_url = add_query_arg(
						array(
							'post_type'                 => 'envira',
							'envira-instagram-remove'   => 'true',
							'envira-instagram-username' => ( ! empty( $slot['username'] ) ) ? $slot['username'] : false,
							'page'                      => 'envira-gallery-settings#envira-tab-instagram',
						),
						admin_url( 'edit.php' )
					);

					?>

					<?php if ( ! empty( $slot['token'] ) ) : ?>

					<tr id="envira-settings-standalone-enable">
						<td>

								<label for="envira-instagram-authenticate-<?php echo esc_html( $x ); ?>"><?php esc_html_e( 'Account #', 'envira-instagram' ); ?><?php echo ' ' . esc_html( $account_number ); ?></label>

								<?php
								if ( ! empty( $slot['profile_picture'] ) ) {
									?>
									<img width="30" height="30" src="<?php echo esc_url( $slot['profile_picture'] ); ?>" /><?php } ?>
								<?php
								if ( ! empty( $slot['username'] ) ) {
									?>
									<?php echo esc_html( $slot['username'] ); ?><?php } ?>
								<?php
								if ( ! empty( $slot['full_name'] ) ) {
									?>
									( <?php echo esc_html( $slot['full_name'] ); ?> ) <?php } ?>

								<!-- <h2><?php esc_html_e( 'Success!', 'envira-gallery' ); ?></h2> -->

								<?php esc_html_e( 'This Instagram account has been authenticated for use with ', 'envira-instagram' ); ?>
								<?php echo esc_html( $envira_label ); ?>
								<?php esc_html_e( '!', 'envira-instagram' ); ?>

								<?php esc_html_e( 'Click Here to Remove Instagram Authentication from ', 'envira-instagram' ); ?>
								<?php echo esc_html( $envira_label ); ?>
								<?php esc_html_e( '.', 'envira-instagram' ); ?>

						</td>
					</tr>

						<?php

						$account_number++;

				endif;
					?>

				<?php } ?>


						</form>
					</tbody>
				</table>

			</div>
			<?php

		}

		/**
		 * Saves or deletes auth settings, depending on the URL query arguments
		 *
		 * @since 1.0.0
		 */
		public function process() {

			// Obtain the data coming back from instagram.
			$common           = Envira_Instagram_Common::get_instance();
			$envira_instagram = false;

			if ( isset( $_GET['envira-instagram'] ) ) { // @codingStandardsIgnoreLine - add nonce?
				$envira_instagram   = sanitize_text_field( wp_unslash( $_GET['envira-instagram'] ) ); // @codingStandardsIgnoreLine - add nonce?
			} elseif ( isset( $_GET['page'] ) && $_GET['page'] == 'envira-gallery-settings' ) { // @codingStandardsIgnoreLine - add nonce?
				$incoming_url = ( isset( $_SERVER['HTTP_HOST'] ) && isset( $_SERVER['REQUEST_URI'] ) ) ? 'https://' . sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) . sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : false;
				$parts        = wp_parse_url( $incoming_url );
				parse_str( $parts['query'], $query );
				if ( ! empty( $query ) && isset( $query['envira-instagram'] ) ) {
					$envira_instagram = $query['envira-instagram'];
				}
			}

			if ( $envira_instagram ) {

				// The user has completed the oAuth process, and has come back to this site.
				$response = json_decode( stripslashes( $envira_instagram ) );

				if ( isset( $response->code ) ) {
					// Error.
					add_action( $this->notice_filter, array( $this, 'notice_oauth_error' ) );
					return;
				}

				if ( isset( $response->access_token ) ) {

					// Success
					// Update the option with the Instagram access token and user ID.
					$auth                    = Envira_Instagram_Common::get_instance()->get_instagram_auth();
					$auth['token']           = $response->access_token;
					$auth['id']              = $response->user->id;
					$auth['username']        = $response->user->username;
					$auth['profile_picture'] = $response->user->profile_picture;
					$auth['full_name']       = $response->user->full_name;

					// find an empty slot.
					$open_slot = false;
					for ( $x = 1; $x <= 3; $x++ ) {
						$test_auth = $common->get_instagram_auth( $x );
						if ( $test_auth && ! empty( $test_auth['username'] ) && ! empty( $auth['username'] ) && $test_auth['username'] === $auth['username'] ) {
							break;
						}
						if ( false === $test_auth ) {
							$open_slot = $x;
							break;
						}
					}

					if ( false === $open_slot ) {

						// Output a notice, which is called if the user authenticated via the Edit Gallery screen.
						add_action( 'envira_gallery_images_tab_notice', array( $this, 'notice_slots_full' ) );

					} else {

						if ( 1 === $open_slot ) {
							update_option( 'envira_instagram', $auth );
						} else {
							update_option( 'envira_instagram_' . $open_slot, $auth );
						}

						// Output a notice, which is called if the user authenticated via the Edit Gallery screen.
						add_action( 'envira_gallery_images_tab_notice', array( $this, 'notice_oauth_success' ) );

					}

					return;

				}
			}

			$envira_instagram_remove = false;

			if ( isset( $_GET['envira-instagram-remove'] ) ) { // @codingStandardsIgnoreLine - add nonce?
				$envira_instagram_remove   = sanitize_text_field( wp_unslash( $_GET['envira-instagram-remove'] ) ); // @codingStandardsIgnoreLine - add nonce?
				$envira_instagram_username = sanitize_text_field( wp_unslash( $_GET['envira-instagram-username'] ) ); // @codingStandardsIgnoreLine - add nonce?
			} elseif ( isset( $_GET['envira-gallery-settings'] ) ) { // @codingStandardsIgnoreLine - add nonce?
				$incoming_url = ( isset( $_SERVER['HTTP_HOST'] ) && isset( $_SERVER['REQUEST_URI'] ) ) ? 'https://' . sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) . sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : false;
				$parts        = wp_parse_url( $incoming_url );
				parse_str( $parts['query'], $query );
				if ( ! empty( $query ) && isset( $query['envira-instagram-remove'] ) ) {
					$envira_instagram_remove = $query['envira-instagram-remove'];
				}
			}

			// Disconnect.
			if ( $envira_instagram_remove ) {

				$total_accounts = 3;

				// find an empty slot.
				$open_slot = false;
				for ( $x = 1; $x <= $total_accounts; $x++ ) {
					$test_auth = $common->get_instagram_auth( $x );
					if ( $test_auth && $test_auth['username'] === $envira_instagram_username ) {
						if ( 1 === $x ) {
							delete_option( 'envira_instagram' );
						} else {
							delete_option( 'envira_instagram_' . $x );
						}
					}
				}
				return;
			}

		}

		/**
		 * Outputs a WordPress style notification message to tell the user that the settings have been saved
		 *
		 * @since 1.0.0
		 */
		public function notice_oauth_success() {
			$notices = new Envira\Admin\Notices();
			// Define the notice classes depending on which hook is used to output the notice.
			/* translators: %s: term name */
			$text = sprintf( __( 'Your Instagram account has been authenticated for use with %s!', 'envira-instagram' ), ( apply_filters( 'envira_whitelabel', false ) ? apply_filters( 'envira_album_whitelabel_name_singular', false ) : 'Envira' ) );
			$notices->display_inline_notice(
				'envira_instagram_oauth_success',
				__( 'Success!', 'envira-instagram' ),
				esc_html( $text )
			);

		}

		/**
		 * Outputs a WordPress style notification message to tell the user that all slots are full
		 *
		 * @since 1.0.0
		 */
		public function notice_slots_full() {
			$notices = new Envira\Admin\Notices();
			// Define the notice classes depending on which hook is used to output the notice.
			/* translators: %s: term name */
			$text = sprintf( __( 'Your Instagram account has been authenticated for use with %s!', 'envira-instagram' ), ( apply_filters( 'envira_whitelabel', false ) ? apply_filters( 'envira_album_whitelabel_name_singular', false ) : 'Envira' ) );
			$notices->display_inline_notice(
				'envira_instagram_oauth_failure',
				__( 'Failed All Slots Full!', 'envira-instagram' ),
				esc_html( $text )
			);

		}

		/**
		 * Outputs a WordPress style notification message to tell the user an error occured during oAuth
		 *
		 * @since 1.0.0
		 */
		public function notice_oauth_error() {

			// Get error.
			$response = json_decode( stripslashes( wp_unslash( $_GET['envira-instagram'] ) ) ); // @codingStandardsIgnoreLine - add nonce?

			// Define the notice classes depending on which hook is used to output the notice.
			$css = ( 'admin_notices' === $this->notice_filter ? 'notice error below-h2' : 'notice error below-h2' );
			?>
			<div class="<?php echo esc_attr( $css ); ?>">
				<p><?php echo esc_html( $response->code ) . ': ' . esc_html( $response->error_type ) . ' - ' . esc_html( $response->error_message ); ?></p>
			</div>
			<?php

		}

		/**
		 * Returns the singleton instance of the class.
		 *
		 * @since 1.0.0
		 *
		 * @return object The Envira_Instagram_Settings object.
		 */
		public static function get_instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Instagram_Settings ) ) {
				self::$instance = new Envira_Instagram_Settings();
			}

			return self::$instance;

		}

	}
endif;
