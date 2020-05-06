<?php
/**
 * AJAX class.
 *
 * @since 1.0.3
 *
 * @package Envira_Defaults
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX class.
 *
 * @since 1.0.3
 *
 * @package Envira_Defaults
 * @author  Envira Team
 */
class Envira_Defaults_Ajax {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.3
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.0.3
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Holds the base class object.
	 *
	 * @since 1.0.3
	 *
	 * @var object
	 */
	public $base;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.3
	 */
	public function __construct() {

		add_action( 'wp_ajax_envira_defaults_gallery_config_modal', array( $this, 'gallery_config_modal' ) );
		add_action( 'wp_ajax_envira_defaults_album_config_modal', array( $this, 'album_config_modal' ) );
		add_action( 'wp_ajax_envira_defaults_gallery_apply_modal', array( $this, 'gallery_apply_modal' ) );
		add_action( 'wp_ajax_envira_defaults_album_apply_modal', array( $this, 'album_apply_modal' ) );
		add_action( 'wp_ajax_envira_defaults_apply', array( $this, 'apply' ) );

	}

	/**
	 * The markup to display in the Thickbox modal when a user clicks 'Add New'
	 * Allows the user to choose which Gallery, if any, to inherit the configuration from
	 * when creating a new Gallery.
	 *
	 * @since 1.0.3
	 */
	public function gallery_config_modal() {

		$base = Envira_Gallery::get_instance();

		// Get galleries.
		$galleries = $base->get_galleries();
		?>
		<div class="wrap">
			<form action="" method="get" id="envira-defaults-config">
				<label for="gallery_id"><?php esc_html_e( 'Inherit Config from:', 'envira-defaults' ); ?></label>
				<select name="gallery_id" size="1" id="gallery_id">
				<?php /* translators: %s: term name */ ?>
				<?php $text = sprintf( __( '(Use %s Default Settings)', 'envira-defaults' ), ( apply_filters( 'envira_whitelabel', false ) ? apply_filters( 'envira_album_whitelabel_name', false ) : 'Envira' ) ); ?>
					<option value="<?php echo esc_html( get_option( 'envira_default_gallery' ) ); ?>"><?php echo esc_html( $text ); ?></option>
					<?php
					foreach ( (array) $galleries as $gallery ) {
						// Get title.
						$title = $gallery['config']['title'];
						?>
						<option value="<?php echo intval( $gallery['id'] ); ?>"><?php echo esc_html( $title ); ?></option>
						<?php
					}
					?>
				</select>
				<input type="submit" name="submit" value="<?php esc_html_e( 'Create Gallery', 'envira-defaults' ); ?>" class="button button-primary" />
			</form>
		</div>
		<?php

		die();

	}

	/**
	 * The markup to display in the Thickbox modal when a user clicks 'Add New'
	 * Allows the user to choose which Gallery, if any, to inherit the configuration from
	 * when creating a new Gallery.
	 *
	 * @since 1.0.3
	 */
	public function album_config_modal() {

		// Get instances.
		$base = Envira_Albums::get_instance();

		// Get albums.
		$albums = $base->get_albums();
		?>
		<div class="wrap">
			<form action="" method="get" id="envira-defaults-config">
				<label for="album_id"><?php esc_html_e( 'Inherit Config from:', 'envira-defaults' ); ?></label>
				<select name="album_id" size="1" id="album_id">
				<?php /* translators: %s: term name */ ?>
				<?php $text = sprintf( __( '(Use %s Default Settings)', 'envira-defaults' ), ( apply_filters( 'envira_whitelabel', false ) ? apply_filters( 'envira_album_whitelabel_name', false ) : 'Envira' ) ); ?>
					<option value="<?php echo esc_html( get_option( 'envira_default_album' ) ); ?>"><?php echo esc_html( $text ); ?></option>
					<?php
					foreach ( (array) $albums as $album ) {
						// Get title.
						$title = $album['config']['title'];
						?>
						<option value="<?php echo intval( $album['id'] ); ?>"><?php echo esc_html( $title ); ?></option>
						<?php
					}
					?>
				</select>
				<input type="submit" name="submit" value="<?php esc_html_e( 'Create Album', 'envira-defaults' ); ?>" class="button button-primary" />
			</form>
		</div>
		<?php

		die();

	}

	/**
	 * The markup to display in the Thickbox modal when a user clicks 'Apply Defaults'
	 * Allows the user to choose which Gallery, if any, to apply the configuration from
	 * when bulk updating galleries
	 *
	 * @since 1.0.6
	 */
	public function gallery_apply_modal() {

		// Get instances.
		$base = Envira_Gallery::get_instance();

		// Get galleries.
		$galleries = $base->get_galleries();
		?>
		<div class="wrap">
			<form action="" method="get" id="envira-defaults-apply-config" data-post-type="envira">
				<label for="gallery_id"><?php esc_html_e( 'Apply Config from:', 'envira-defaults' ); ?></label>
				<select name="gallery_id" size="1" id="gallery_id">
				<?php /* translators: %s: term name */ ?>
				<?php $text = sprintf( __( '(Use %s Default Settings)', 'envira-defaults' ), ( apply_filters( 'envira_whitelabel', false ) ? apply_filters( 'envira_album_whitelabel_name', false ) : 'Envira' ) ); ?>
					<option value="<?php echo esc_html( get_option( 'envira_default_gallery' ) ); ?>"><?php echo esc_html( $text ); ?></option>
					<?php
					foreach ( (array) $galleries as $gallery ) {
						// Get title.
						$title = $gallery['config']['title'];
						?>
						<option value="<?php echo intval( $gallery['id'] ); ?>"><?php echo esc_html( $title ); ?></option>
						<?php
					}
					?>
				</select>
				<input type="submit" name="submit" value="<?php esc_html_e( 'Apply Config to Selected Galleries', 'envira-defaults' ); ?>" class="button button-primary" />
			</form>
		</div>
		<?php

		die();

	}

	/**
	 * The markup to display in the Thickbox modal when a user clicks 'Apply Defaults'
	 * Allows the user to choose which Album, if any, to inherit the configuration from
	 * when bulk updating albums
	 *
	 * @since 1.0.3
	 */
	public function album_apply_modal() {

		// Get instances.
		$base = Envira_Albums::get_instance();

		// Get albums.
		$albums = $base->get_albums();
		?>
		<div class="wrap">
			<form action="" method="get" id="envira-defaults-apply-config" data-post-type="envira_album">
				<label for="album_id"><?php esc_html_e( 'Inherit Config from:', 'envira-defaults' ); ?></label>
				<select name="album_id" size="1" id="album_id">
				<?php /* translators: %s: term name */ ?>
				<?php $text = sprintf( __( '(Use %s Default Settings)', 'envira-defaults' ), ( apply_filters( 'envira_whitelabel', false ) ? apply_filters( 'envira_album_whitelabel_name', false ) : 'Envira' ) ); ?>
					<option value="<?php echo esc_html( get_option( 'envira_default_album' ) ); ?>"><?php echo esc_html( $text ); ?></option>
					<?php
					foreach ( (array) $albums as $album ) {
						// Get title.
						$title = $album['config']['title'];
						?>
						<option value="<?php echo intval( $album['id'] ); ?>"><?php echo esc_html( $title ); ?></option>
						<?php
					}
					?>
				</select>
				<input type="submit" name="submit" value="<?php esc_html_e( 'Apply Config to Selected Albums', 'envira-defaults' ); ?>" class="button button-primary" />
			</form>
		</div>
		<?php

		die();

	}

	/**
	 * Applies configuration settings to the POSTed Galleries/Albums based on the chosen
	 * Gallery/Album
	 *
	 * @since 1.0.6
	 */
	public function apply() {

		// Run a security check first.
		check_admin_referer( 'envira-defaults', 'nonce' );

		// Check for required vars.
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : false;

		if ( ! empty( $_POST['post_ids'] ) ) {
			$post_ids_sanitized = array_map( 'sanitize_text_field', wp_unslash( $_POST['post_ids'] ) );
		} else {
			// Send error message.
			wp_send_json_error();
		}

		$post_ids  = isset( $post_ids_sanitized ) ? $post_ids_sanitized : false;
		$post_type = isset( $_POST['post_type'] ) ? ( sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) ) : false;
		// Get the config for the chosen Gallery/Album.
		switch ( $post_type ) {

			/**
			* Gallery
			*/
			case 'gallery':
				// Get config, and unset some parameters we don't want to map to the chosen galleries.
				$gallery = envira_get_gallery( $id );
				$config  = $gallery['config'];
				unset( $config['type'] );
				unset( $config['title'] );
				unset( $config['slug'] );
				unset( $config['classes'] );

				// Iterate through chosen Galleries, updating config with the above gallery config.
				foreach ( (array) $post_ids as $post_id ) {
					// Get post meta.
					$gallery = get_post_meta( $post_id, '_eg_gallery_data', true );

					foreach ( $config as $key => $value ) {
						$gallery['config'][ $key ] = $value;
					}

					// Update.
					update_post_meta( $post_id, '_eg_gallery_data', $gallery );

					// Flush cache.
					envira_flush_gallery_caches( $post_id, $gallery['config']['slug'] );
				}

				break;

			/**
			* Album
			*/
			case 'album':
				// Get config, and unset some parameters we don't want to map to the chosen albums.
				$album  = envira_get_album( $id );
				$config = $album['config'];
				unset( $config['type'] );
				unset( $config['title'] );
				unset( $config['slug'] );
				unset( $config['classes'] );

				// Iterate through chosen Albums, updating config with the above album config.
				foreach ( (array) $post_ids as $post_id ) {
					// Get post meta.
					$album = get_post_meta( $post_id, '_eg_album_data', true );

					foreach ( $config as $key => $value ) {
						$album['config'][ $key ] = $value;
					}

					// Update.
					update_post_meta( $post_id, '_eg_album_data', $album );

					// Flush cache.
					envira_flush_gallery_caches( $post_id, $album['config']['slug'] );
				}
				break;

		}

		// Send success message.
		wp_send_json_success();

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Defaults_Ajax object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Defaults_Ajax ) ) {
			self::$instance = new Envira_Defaults_Ajax();
		}

		return self::$instance;

	}

}

// Load the AJAX class.
$envira_defaults_ajax = Envira_Defaults_Ajax::get_instance();
