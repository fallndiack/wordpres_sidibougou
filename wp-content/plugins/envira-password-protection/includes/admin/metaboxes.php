<?php
/**
 * Metabox class.
 *
 * @since 1.0.0
 *
 * @package Envira_Password_Protection
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Metabox class.
 *
 * @since 1.0.0
 *
 * @package Envira_Password_Protection
 * @author  Envira Team
 */
class Envira_Password_Protection_Metaboxes {

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

		// Load the base class object.
		$this->base = Envira_Password_Protection::get_instance();

		// Galleries and Albums.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 100 );

		// Galleries.
		add_filter( 'envira_gallery_metabox_ids', array( $this, 'pass_over' ), 10, 1 );
		add_action( 'envira_gallery_metabox_styles', array( $this, 'meta_box_styles' ), 99 );
		add_action( 'envira_gallery_metabox_scripts', array( $this, 'meta_box_scripts' ), 99 );
		add_filter( 'envira_gallery_save_settings', array( $this, 'save_gallery' ), 10, 2 );

		// Albums.
		add_filter( 'envira_albums_metabox_ids', array( $this, 'pass_over' ), 10, 1 );
		add_action( 'envira_albums_metabox_styles', array( $this, 'meta_box_styles' ), 99 );
		add_action( 'envira_albums_metabox_scripts', array( $this, 'meta_box_scripts' ), 99 );
		add_filter( 'envira_albums_save_settings', array( $this, 'save_album' ), 10, 2 );
		add_filter( 'admin_body_class', array( $this, 'admin_body_class' ), 12, 1 );
	}

	/**
	 * Add a body class to show password protection
	 *
	 * @return array $classes
	 * @param string $classes Classes.
	 */
	public function admin_body_class( $classes ) {

		$current_screen = get_current_screen();

		if ( empty( $current_screen ) ) {
			return $classes;
		}

		if ( 'envira' === $current_screen->id || 'envira_album' === $current_screen->id ) {
			return $classes . ' envira-protection';
		}
		return $classes;

	}
	/**
	 * Register metabox with Envira, to ensure it is not removed
	 *
	 * @since 1.0.1
	 *
	 * @param array $metaboxes Metaboxes.
	 * @return array Metaboxes
	 */
	public function register_meta_boxes( $metaboxes ) {

		$metaboxes[] = 'envira-password-protection';
		return $metaboxes;

	}

	/**
	 * Creates a metabox for additional Password Protection options
	 *
	 * @since 1.0.1
	 */
	public function add_meta_boxes() {

		// Add metabox to Envira CPT.
		add_meta_box( 'envira-password-protection', __( 'Envira: Password Protection', 'envira-password-protection' ), array( $this, 'meta_box_callback' ), 'envira', 'side', 'low' );
		add_meta_box( 'envira-password-protection', __( 'Envira: Password Protection', 'envira-password-protection' ), array( $this, 'meta_box_callback' ), 'envira_album', 'side', 'low' );
	}

	/**
	 * Pass over.
	 *
	 * @since 1.0.1
	 * @param array $pass_over_defaults $defaults.
	 */
	public function pass_over( $pass_over_defaults ) {

		$pass_over_defaults[] = 'envira-password-protection';

		return $pass_over_defaults;

	}

	/**
	 * Callback for displaying content in the registered metabox.
	 *
	 * @since 1.0.1
	 *
	 * @param object $post The current post object.
	 */
	public function meta_box_callback( $post ) {

		// Depending on the post type, define the key and instance.
		switch ( $post->post_type ) {
			/**
			* Gallery
			*/
			case 'envira':
				$key      = '_envira_gallery';
				$instance = Envira_Gallery_Metaboxes::get_instance();
				$term     = 'gallery';
				break;

			/**
			* Album
			*/
			case 'envira_album':
				$key      = '_eg_album_data[config]';
				$instance = Envira_Albums_Metaboxes::get_instance();
				$term     = 'album';
				break;
		}

		wp_nonce_field( 'envira_password_protection_save_settings', 'envira_password_protection_nonce' );

		?>
		<div>
		<p><label for="envira-password-protection-email"><strong><?php esc_html_e( 'Email Address / Username', 'envira-password-protection' ); ?></strong></label>
		<input id="envira-password-protection-email" type="text" name="<?php echo esc_html( $key ); ?>[password_protection_email]" value="<?php echo esc_html( $instance->get_config( 'password_protection_email', $instance->get_config_default( 'password_protection_email' ) ) ); ?>" /><br />
		<span class="description"><?php esc_html_e( 'Optionally specify an email address or username. If defined, this will be required as well as the password to access this ', 'envira-password-protection' ); ?><?php echo esc_html( $term ) . '.'; ?></span></p>
		</div>
		<div>
		<p><label for="envira-password-protection-message"><strong><?php esc_html_e( '"Password Required" Message', 'envira-password-protection' ); ?></strong></label>
		<textarea id="envira-password-protection-message" placeholder="This content is password protected. To view it please enter your password below:" name="<?php echo esc_html( $key ); ?>[password_protection_message]"><?php echo esc_html( $instance->get_config( 'password_protection_message', $instance->get_config_default( 'password_protection_message' ) ) ); ?></textarea><br/>
		<span class="description"><?php esc_html_e( 'Override the default message that is displayed when a password is required for this ', 'envira-password-protection' ); ?><?php echo esc_html( $term ) . '.'; ?></span></p>
		</div>
		<div>
		<p><label for="envira-wrong-password-message"><strong><?php esc_html_e( '"Wrong Password" Message', 'envira-password-protection' ); ?></strong></label>
		<textarea id="envira-wrong-password-message" placeholder="Sorry, your password is wrong." name="<?php echo esc_html( $key ); ?>[wrong_password_message]"><?php echo esc_html( $instance->get_config( 'wrong_password_message', $instance->get_config_default( 'wrong_password_message' ) ) ); ?></textarea><br/>
		<span class="description"><?php esc_html_e( 'Override the default message that is displayed when an entered password is incorrect ', 'envira-password-protection' ); ?><?php echo esc_html( $term ) . '.'; ?></span></p>
		</div>

		<?php

	}

	/**
	 * Repeatable Meta Box Display.
	 *
	 * @since 1.0.1
	 */
	public function hhs_repeatable_meta_box_display() {
		global $post;

		$envira_private_links = get_post_meta( $post->ID, 'envira_private_links', true );

		wp_nonce_field( 'envira_repeatable_meta_box_private_links_nonce', 'envira_repeatable_meta_box_private_links_nonce' );
		?>
		<script type="text/javascript">
		jQuery(document).ready(function( $ ){
			$( '.new-link-code-box #add-row' ).on('click', function(e) {
			e.preventDefault();

			/* check and make sure code isn't already in the list/exists */
			var new_code = $('.new-link-code').val(),
				the_button = $(this),
				found = false,
				button_clicked = false;

			if ( button_clicked ) {
				return;
			}

			/* disable link button temp */
			button_clicked = true;
			the_button.css('opacity','0.5');
			$('.new-link-code-box .spinner').attr('style', 'display:inline-block').css('visibility', 'visible');
			$('.new-link-code').css('max-width', '125px');
			$('input[name="envira_private_link_code[]"').each(function(index) {
					var $this = $(this);
					if ( $this.val() == new_code ) {
						found = true;
					}
			});

			if ( found !== false ) {
				alert('You have already entered this code.');
				button_clicked = false;
				the_button.css('opacity','1');
				$('.new-link-code-box .spinner').attr('style', 'display:none').css('visibility', 'hidden');
				$('.new-link-code').css('max-width', '100%');
				return;
			}

			/* proceed with updating the visible UI */
			$('#envira-repeatable-fieldset-private-links-header').show();

				var row = $( '.empty-row.screen-reader-text' ).clone(true).hide(),
					rowCount = $('#envira-repeatable-fieldset-private-links tr').length + 1;

				if ( $('.new-link-code').val() != '' ) {
					row.find('code').attr('id', 'envira_password_code_' + rowCount).html( $('.new-link-code').val() );
					row.find('a.envira-clipboard').attr('data-clipboard-target', '#envira_password_code_' + rowCount);
					row.find('input[name="envira_private_link_code[]"]').val( new_code );
					row.removeClass( 'empty-row screen-reader-text' );
					row.insertBefore( '#envira-repeatable-fieldset-private-links tbody>tr:first' ).fadeIn();
					$('.new-link-code').val('');

			}

			/* prepare data, then submit ajax to update */
			var link_data = $("form#post #envira-repeatable-fieldset-private-links input").serialize();

			/* update the settings */
			$.ajax( {
				type:   'POST',
				url:    envira_password_protection.ajax,
				data:   {
					action:      'envira_password_protection_update_private_links',
					nonce:       envira_password_protection.nonce,
					post_id:     <?php echo intval( $post->ID ); ?>,
					link_data:   link_data,
				}
			} ).done( function( response ) {

				if ( response.success == true ) {


				} else {

					alert('An error has occured. Try a different code.');

				}

				/* enable link button temp */
				button_clicked = false;
				the_button.css('opacity','1');
				$('.new-link-code-box .spinner').attr('style', 'display:none').css('visibility', 'hidden');
				$('.new-link-code').css('max-width', '100%');

			} ).fail( function( response ) {
				/* Something went wrong - either a real error, or we've reached the end of the gallery*/
				/* Don't change the flag, so we don't make any more requests*/

				/* Fire an event for third party plugins to use */
				$( document ).trigger( {
					type:       'envira_password_protection_ajax_load_error',
					data:       $("form#envira-repeatable-fieldset-private-links").serialize(),
					response:   response,                   /* may give a clue as to the error from the AJAX request*/
				} );

				/* disable link button temp */
				button_clicked = false;
				the_button.css('opacity','1');
				$('.new-link-code-box .spinner').attr('style', 'display:none').css('visibility', 'hidden');
				$('.new-link-code').css('max-width', '100%');
			} );

			return false;

		});

		$( '.remove-row' ).on('click', function(e) {
			e.preventDefault();

			if (window.confirm("Are you sure you want to remove code '" + $(this).parent().find('input').val() + "'?")) {

				/* check and make sure code isn't already in the list/exists */
				var the_button = $(this),
					button_clicked = false;

				if ( button_clicked ) {
					return;
				}

				$(this).parents('tr').fadeOut(500, function() {

					$(this).remove();

					var rowCount = $('#envira-repeatable-fieldset-private-links tr').length;
					if ( rowCount == 2 ) {
						$('#envira-repeatable-fieldset-private-links-header').hide();
					}

					/* prepare data, then submit ajax to update */
					var link_data = $("form#post #envira-repeatable-fieldset-private-links input").serialize();

					/* update the settings */
					$.ajax( {
						type:   'POST',
						url:    envira_password_protection.ajax,
						data:   {
							action:      'envira_password_protection_update_private_links',
							nonce:       envira_password_protection.nonce,
							post_id:     <?php echo intval( $post->ID ); ?>,
							link_data:   link_data,
					}
					} ).done( function( response ) {

					if ( response.success == true ) {


					} else {

						alert('An error has occured. Please refresh the page and try again.');

					}

					/* enable link button temp */
					button_clicked = false;
					the_button.css('opacity','1');
					$('.new-link-code-box .spinner').attr('style', 'display:none').css('visibility', 'hidden');
					$('.new-link-code').css('max-width', '100%');

					} ).fail( function( response ) {
					/* Something went wrong - either a real error, or we've reached the end of the gallery*/
					/* Don't change the flag, so we don't make any more requests*/
					/* Fire an event for third party plugins to use */
					$( document ).trigger( {
						type:       'envira_password_protection_ajax_load_error',
						data:       $("form#envira-repeatable-fieldset-private-links").serialize(),
						response:   response,                   /* may give a clue as to the error from the AJAX request*/
					} );

					/* disable link button temp */
					button_clicked = false;
					the_button.css('opacity','1');
					$('.new-link-code-box .spinner').attr('style', 'display:none').css('visibility', 'hidden');
					$('.new-link-code').css('max-width', '100%');

					} );



				});


			}
			return false;
		});
	});
	</script>

	<div class="new-link-code-box">
		<input type="text" autocomplete="false" class="new-link-code" maxlength="20" />
		<a id="add-row" class="button button-primary button-large" href="#">Add Link</a>
		<span class="spinner" style="display: none;"></span>
	</div>

	<table id="envira-repeatable-fieldset-private-links" width="100%" class="envira-private-links">

	<tbody>
		<?php

		if ( $envira_private_links ) :
			?>

	<thead id="envira-repeatable-fieldset-private-links-header">
		<tr>
			<th width="70%">Code</th>
			<th width="25%" class="used">Used</th>
			<th></th>
		</tr>
	</thead>

			<?php

			$counter = 0;

			foreach ( $envira_private_links as $envira_private_link_code => $code_info ) {

				?>
	<tr>

		<td><div class="envira-code envira-password-code"><?php echo '<code style="display: inline-block;" id="envira_password_code_' . esc_attr( $counter ) . '">' . esc_attr( $envira_private_link_code ) . '</code><a href="#" title="copy code" data-clipboard-target="#envira_password_code_' . esc_attr( $counter ) . '" class="dashicons dashicons-clipboard envira-clipboard"><span></span></td>'; ?></div></td>

		<td class="total"><?php echo esc_html( $code_info['used'] ); ?></td>

		<td>
			<a class="remove-row" href="#">X</a>
			<input type="hidden" name="envira_private_link_code[]" value="
				<?php
				if ( '' !== $envira_private_link_code ) {
					echo esc_attr( $envira_private_link_code );}
				?>
				" />
		</td>
	</tr>
				<?php

				$counter++;

			}
		else :
			// show a blank one.
			?>
			<thead id="envira-repeatable-fieldset-private-links-header" style="display:none;">
				<tr>
					<th width="60%">Code</th>
					<th width="30%" class="used">Used</th>
				</tr>
			</thead>

		<?php endif; ?>
		<!-- empty hidden one for jQuery -->
		<tr class="empty-row screen-reader-text">
			<td><div class="envira-code envira-password-code"><?php echo '<code style="display: inline-block;" id="envira_password_code_"></code><a href="#" title="copy code" data-clipboard-target="#envira_password_code_" class="dashicons dashicons-clipboard envira-clipboard"><span></span></td>'; ?></div></td>
			<td class="total">0</td>
			<td>
				<a class="remove-row" href="#">X</a>
				<input type="hidden" name="envira_private_link_code[]" value="" />
			</td>
		</tr>
		</tbody>
		</table>

		<?php
	}


	/**
	 * Callback for displaying content in the registered metabox.
	 *
	 * @since 1.0.1
	 *
	 * @param object $post The current post object.
	 */
	public function meta_box_private_links_callback( $post ) {

		// Depending on the post type, define the key and instance.
		switch ( $post->post_type ) {
			/**
			* Gallery
			*/
			case 'envira':
				$key      = '_envira_gallery';
				$instance = Envira_Gallery_Metaboxes::get_instance();
				break;

			/**
			* Album
			*/
			case 'envira_album':
				$key      = '_eg_album_data[config]';
				$instance = Envira_Albums_Metaboxes::get_instance();
				break;
		}
		?>
		<div>
			<span class="description"><?php esc_html_e( 'Generate unique links to share privately with individuals.', 'envira-password-protection' ); ?></span>
		</div>

		<?php

	}

	/**
	 * Loads styles for our metaboxes.
	 *
	 * @since 1.0.0
	 *
	 * @return void Return early if not on the proper screen.
	 */
	public function meta_box_styles() {

		// Load necessary metabox styles.
		wp_register_style( $this->base->plugin_slug . '-metabox-style', plugins_url( 'assets/css/pp-admin.css', $this->base->file ), array(), $this->base->version );
		wp_enqueue_style( $this->base->plugin_slug . '-metabox-style' );

	}

	/**
	 * Loads scripts for our metaboxes.
	 *
	 * @since 1.0.1
	 *
	 * @return void Return early if not on the proper screen.
	 */
	public function meta_box_scripts() {

		// Load necessary metabox styles.
		wp_enqueue_script( $this->base->plugin_slug . '-metabox-script', plugins_url( 'assets/js/metabox.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );
		wp_localize_script(
			$this->base->plugin_slug . '-metabox-script',
			'envira_password_protection',
			array(
				'ajax'  => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'envira-password-protection' ),
			)
		);
	}

	/**
	 * Saves the addon's settings for Galleries.
	 *
	 * @since 1.0.1
	 *
	 * @param array $settings  Array of settings to be saved.
	 * @param int   $post_id     The current post ID.
	 * @return array $settings Amended array of settings to be saved.
	 */
	public function save_gallery( $settings, $post_id ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if (
			! isset( $_POST['_envira_gallery'], $_POST['envira_password_protection_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['envira_password_protection_nonce'] ), 'envira_password_protection_save_settings' )
		) {
			return $settings;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$old   = get_post_meta( $post_id, 'envira_private_links', true );
		$new   = array();
		$codes = isset( $_POST['envira_private_link_code'] ) ? sanitize_text_field( wp_unslash( $_POST['envira_private_link_code'] ) ) : false;
		$count = ( $codes ) ? count( $codes ) : 0;

		if ( $codes > 0 ) {

			foreach ( $codes as $code ) {
				if ( '' !== trim( $code ) ) :
					$used = ( ! empty( intval( $old[ $code ]['used'] ) ) ) ? intval( $old[ $code ]['used'] ) : 0;
					$new[ stripslashes( wp_strip_all_tags( $code ) ) ] = array( 'used' => $used );
			endif;
			}
		}

		if ( ! empty( $new ) && $new !== $old ) {
			update_post_meta( $post_id, 'envira_private_links', $new );
		} elseif ( empty( $new ) && $old ) {
			delete_post_meta( $post_id, 'envira_private_links', $old );
		}

		// Settings.
		if ( isset( $_POST['_envira_gallery']['password_protection_email'] ) ) {
			$settings['config']['password_protection_email'] = sanitize_text_field( wp_unslash( $_POST['_envira_gallery']['password_protection_email'] ) );
		} else {
			$settings['config']['password_protection_email'] = false;
		}
		if ( isset( $_POST['_envira_gallery']['password_protection_message'] ) ) {
			$settings['config']['password_protection_message'] = sanitize_text_field( wp_unslash( $_POST['_envira_gallery']['password_protection_message'] ) );
		} else {
			$settings['config']['password_protection_message'] = false;
		}
		if ( isset( $_POST['_envira_gallery']['wrong_password_message'] ) ) {
			$settings['config']['wrong_password_message'] = sanitize_text_field( wp_unslash( $_POST['_envira_gallery']['wrong_password_message'] ) );
		} else {
			$settings['config']['wrong_password_message'] = false;
		}

		return $settings;

	}

	/**
	 * Saves the addon's settings for Albums.
	 *
	 * @since 1.0.1
	 *
	 * @param array $settings  Array of settings to be saved.
	 * @param int   $post_id     The current post ID.
	 * @return array $settings Amended array of settings to be saved.
	 */
	public function save_album( $settings, $post_id ) {

		if (
			! isset( $_POST['_eg_album_data'], $_POST['envira_password_protection_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['envira_password_protection_nonce'] ), 'envira_password_protection_save_settings' )
		) {
			return $settings;
		}

		// Settings.
		if ( isset( $_POST['_eg_album_data']['config']['password_protection_email'] ) ) {
			$settings['config']['password_protection_email'] = sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['password_protection_email'] ) );
		} else {
			$settings['config']['password_protection_email'] = false;
		}
		if ( isset( $_POST['_eg_album_data']['config']['password_protection_message'] ) ) {
			$settings['config']['password_protection_message'] = sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['password_protection_message'] ) );
		} else {
			$settings['config']['password_protection_message'] = false;
		}
		if ( isset( $_POST['_eg_album_data']['config']['wrong_password_message'] ) ) {
			$settings['config']['wrong_password_message'] = sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['wrong_password_message'] ) );
		} else {
			$settings['config']['wrong_password_message'] = false;
		}

		return $settings;

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Pagination_Metaboxes object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Password_Protection_Metaboxes ) ) {
			self::$instance = new Envira_Password_Protection_Metaboxes();
		}

		return self::$instance;

	}

}

// Load the metabox class.
$envira_password_protection_metaboxes = Envira_Password_Protection_Metaboxes::get_instance();
