<?php
/**
 * AJAX class.
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
 * AJAX class.
 *
 * @since 1.0.0
 *
 * @package Envira_Dropbox_Importer
 * @author  Envira Team
 */
class Envira_Dropbox_Importer_Ajax {

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
	 * Stores the last cursor obtained from the API
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $last_cursor = false;

	/**
	 * Stores the last path obtained from the API
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $last_path = false;

	/**
	 * Stores the has_more value obtained from the API
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $has_more = false;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		add_action( 'wp_ajax_envira_dropbox_importer_get_files_folders', array( $this, 'get_files_folders' ) );
		add_action( 'wp_ajax_envira_dropbox_importer_search_files_folders', array( $this, 'search_files_folders' ) );
		add_action( 'wp_ajax_envira_dropbox_importer_insert_images', array( $this, 'insert_images' ) );

	}

	/**
	 * Outputs HTML Markup for a paginated set of Dropbox Images
	 * Used in the Modal window
	 *
	 * @since 1.0.0
	 */
	public function get_files_folders() {

		// Run a security check first.
		check_admin_referer( 'envira-gallery-media-insert', 'nonce' );

		// Prepare variables.
		$folder = ( isset( $_POST['path'] ) ? sanitize_text_field( wp_unslash( $_POST['path'] ) ) : '' );
		$offset = ( isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0 );

		// Get instances.
		$dropbox        = Envira_Dropbox_Importer_Dropbox::get_instance();
		$data_results   = array();
		$this->has_more = true;

		// Go through the entire Dropbox folder (if there's a has_more we can get more items with the cursor ).
		while ( $this->has_more ) {
			$results = $dropbox->get_files_folders( $folder, false, $this->last_cursor );

			// Check result.
			if ( is_wp_error( $results ) ) {
				// Return error.
				wp_send_json_error( $results->get_error_message() );
			}

			$this->last_cursor = $results['last_cursor'];
			$this->last_path   = $results['last_path'];
			$this->has_more    = $results['has_more'];
			$data_results      = array_merge( $data_results, $results['data'] );
		}

		// Return success with results.
		wp_send_json_success( $data_results );
		die();

	}

	/**
	 * Outputs HTML Markup for a paginated set of Dropbox Images
	 * Used in the Modal window
	 *
	 * @since 1.0.0
	 */
	public function search_files_folders() {

		// Run a security check first.
		check_admin_referer( 'envira-gallery-media-insert', 'nonce' );

		// Prepare variables.
		$folder = ( isset( $_POST['path'] ) ? sanitize_text_field( wp_unslash( $_POST['path'] ) ) : '' );
		$offset = ( isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0 );
		$search = ( isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '' );

		// Get instances.
		$dropbox = Envira_Dropbox_Importer_Dropbox::get_instance();

		// Query Dropbox.
		$results = $dropbox->search_files_folders( $folder, $search );

		// Check result.
		if ( is_wp_error( $results ) ) {
			// Return error.
			wp_send_json_error( $results->get_error_message() );
		}

		// Return success with results.
		wp_send_json_success( $results );
		die();

	}

	/**
	 * Called by the media view when the "Insert into Gallery" button is pressed
	 * Checks if Dropbox images were specified, and if so imports them to the
	 * Media Library before adding them as images to the Envira Gallery
	 *
	 * @since 1.0.0
	 */
	public function insert_images() {

		// Run a security check first.
		check_admin_referer( 'envira-gallery-media-insert', 'nonce' );

		// Setup vars.
		$settings_sanitized = array_map( 'sanitize_text_field', wp_unslash( $_POST ) );

		$images  = ( isset( $_POST['images'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['images'] ) ) : '' );
		$post_id = isset( $_POST['post_id'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) ) : false;

		if ( empty( $images ) || empty( $post_id ) ) {
			wp_send_json_error( __( 'No images or Gallery ID were specified', 'envira-dropbox-importer' ) );
			die();
		}

		// Get in gallery and gallery data meta.
		$gallery_data = get_post_meta( $post_id, '_eg_gallery_data', true );
		$in_gallery   = get_post_meta( $post_id, '_eg_in_gallery', true );

		if ( ! $in_gallery || empty( $in_gallery ) ) {
			$in_gallery = array();
		}

		// Get helpers.
		$dropbox   = Envira_Dropbox_Importer_Dropbox::get_instance();
		$import    = Envira_Gallery_Import::get_instance();
		$common    = Envira_Gallery_Common::get_instance();
		$metaboxes = Envira_Gallery_Metaboxes::get_instance();

		// Loop through the Dropbox Images and add them to the gallery.
		foreach ( (array) $images as $image ) {

			// Remove any backslash, which Dropbox does not allow...
			if ( strpos( $image, "\'" ) !== false ) {
				$image = str_replace( "\'", "'", $image );
			}

			// Get remote image into local filesystem's wp-content/uploads folder.
			$result = $dropbox->download_file( $image );

			// Check result for errors.
			if ( is_wp_error( $result ) || ! $result ) {
				// Failed - discard this image.
				$error_string = $result->get_error_message();
				if ( defined( 'ENVIRA_DEBUG' ) && ENVIRA_DEBUG ) {
					error_log( 'dropbox download failed:', 0 ); // @codingStandardsIgnoreLine
					error_log( print_r( $result, true ), 0 ); // @codingStandardsIgnoreLine
				}
				if ( $error_string ) {
					echo esc_html( $error_string );
				}
				continue;
			}

			if ( defined( 'ENVIRA_DEBUG' ) && ENVIRA_DEBUG ) {
				error_log( '$image:', 0 ); // @codingStandardsIgnoreLine
				error_log( print_r( $image, true ), 0 ); // @codingStandardsIgnoreLine
			}
			if ( defined( 'ENVIRA_DEBUG' ) && ENVIRA_DEBUG ) {
				error_log( '$result:', 0 ); // @codingStandardsIgnoreLine
				error_log( print_r( $result, true ), 0 ); // @codingStandardsIgnoreLine
			}
			if ( defined( 'ENVIRA_DEBUG' ) && ENVIRA_DEBUG ) {
				error_log( '$gallery_data:', 0 ); // @codingStandardsIgnoreLine
				error_log( print_r( $gallery_data, true ), 0 ); // @codingStandardsIgnoreLine
			}
			if ( defined( 'ENVIRA_DEBUG' ) && ENVIRA_DEBUG ) {
				error_log( '$post_id:', 0 ); // @codingStandardsIgnoreLine
				error_log( print_r( $post_id, true ), 0 ); // @codingStandardsIgnoreLine
			}

			// Now get the local image into Media Library the WP way.
			$stream = $import->import_remote_image( $result['local_file_url'], $gallery_data, array(), $post_id, 0, true );
			if ( defined( 'ENVIRA_DEBUG' ) && ENVIRA_DEBUG ) {
				error_log( '$stream:', 0 ); // @codingStandardsIgnoreLine
				error_log( print_r( $stream, true ), 0 ); // @codingStandardsIgnoreLine
			}
			if ( is_wp_error( $stream ) ) {
				// Failed.
				if ( defined( 'ENVIRA_DEBUG' ) && ENVIRA_DEBUG ) {
					error_log( 'local image into media library failed:', 0 ); // @codingStandardsIgnoreLine
					error_log( print_r( $stream, true ), 0 ); // @codingStandardsIgnoreLine
				}
				wp_send_json_error( $stream->get_error_message() );
				die();
			}
			if ( ! empty( $stream['error'] ) ) {
				// Failed.
				wp_send_json_error( $stream['error'] );
				die();
			}

			// Get the image ID.
			$image_id = $stream['attachment_id'];

			// Update the attachment image post meta first.
			$has_gallery = get_post_meta( $image_id, '_eg_has_gallery', true );
			if ( empty( $has_gallery ) ) {
				$has_gallery = array();
			}

			$has_gallery[] = $post_id;
			update_post_meta( $image_id, '_eg_has_gallery', $has_gallery );

			// Now add the image to the gallery for this particular post.
			$in_gallery[] = $image_id;
			$gallery_data = envira_gallery_ajax_prepare_gallery_data( $gallery_data, $image_id );
		}

		// Update the gallery data.
		update_post_meta( $post_id, '_eg_in_gallery', $in_gallery );
		update_post_meta( $post_id, '_eg_gallery_data', $gallery_data );

		// Flush the gallery cache.
		$common->flush_gallery_caches( $post_id );

		// Return a HTML string comprising of all gallery images, so the UI can be updated.
		$html = '';
		if ( isset( $gallery_data['gallery'] ) ) {
			foreach ( (array) $gallery_data['gallery'] as $id => $data ) {
				$html .= $metaboxes->get_gallery_item( $id, $data, $post_id );
			}
		}

		if ( $html ) {
			// Return success with gallery grid HTML.
			wp_send_json_success( $html );
		} else {
			wp_send_json_error();
		}
		die();

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Dropbox_Importer_Ajax object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Dropbox_Importer_Ajax ) ) {
			self::$instance = new Envira_Dropbox_Importer_Ajax();
		}

		return self::$instance;

	}

}

// Load the AJAX class.
$envira_dropbox_importer_ajax = Envira_Dropbox_Importer_Ajax::get_instance();
