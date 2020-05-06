<?php
/**
 * Envira_Lightroom_API class.
 *
 * @since 2.2.0
 *
 * @package Envira_Gallery
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Envira_Lightroom_API' ) ) :

	/**
	 * Envira_Lightroom_API class.
	 *
	 * @since 1.7.0
	 *
	 * @package Envira_Gallery
	 * @author  Envira Gallery Team <support@enviragallery.com>
	 */
	final class Envira_Lightroom_API {

		/**
		 * Holds the instance.
		 *
		 * @since 2.2.0
		 *
		 * @var array
		 */
		public static $instance = null;

		/**
		 * Holds the instance.
		 *
		 * @since 2.2.0
		 *
		 * @var array
		 */
		public $domain = 'envira-lightroom';

		/**
		 * Holds the version.
		 *
		 * @since 2.2.0
		 *
		 * @var array
		 */
		public $version = 'v3';

		/**
		 * Holds the base.
		 *
		 * @since 2.2.0
		 *
		 * @var array
		 */
		public $base;

		/**
		 * Holds the core var.
		 *
		 * @since 2.2.0
		 *
		 * @var array
		 */
		public $core;

		/**
		 * Holds the request.
		 *
		 * @since 2.2.0
		 *
		 * @var array
		 */
		private $request;

		/**
		 *  Class constructor.
		 *
		 * @access public
		 * @return void
		 */
		public function __construct() {

			$this->base = Envira_Lightroom::get_instance();
			$this->core = Envira_Gallery::get_instance();

			if ( ! class_exists( 'Envira_Gallery_Metaboxes' ) ) {
				require plugin_dir_path( $this->core->file ) . 'src/Legacy/class-envira-gallery-metaboxes.php';
				$this->metaboxes = Envira_Gallery_Metaboxes::get_instance();

			}

			add_action( 'rest_api_init', array( $this, 'register_routes' ) );

		}

		/**
		 * Register Envira-Lightroom API Routes.
		 *
		 * @access public
		 * @return void
		 */
		public function register_routes() {

			// Register route at /wp-json/envira/v3/authenticate.
			register_rest_route(
				$this->domain . '/' . $this->version,
				'/authenticate',
				array(
					'methods'  => 'POST',
					'callback' => array( $this, 'authenticate' ),
				)
			);

			// Register route at /wp-json/envira/v3/version-check.
			register_rest_route(
				$this->domain . '/' . $this->version,
				'/version-check',
				array(
					'methods'  => 'POST',
					'callback' => array( $this, 'request_version' ),
				)
			);

			// Register route at /wp-json/envira/v3/insert-gallery.
			register_rest_route(
				$this->domain . '/' . $this->version,
				'/insert-gallery',
				array(
					'methods'  => 'POST',
					'callback' => array( $this, 'maybe_update_gallery' ),
				)
			);

			// Register route at /wp-json/envira/v3/update-gallery.
			register_rest_route(
				$this->domain . '/' . $this->version,
				'/update-gallery',
				array(
					'methods'  => 'POST',
					'callback' => array( $this, 'maybe_update_gallery' ),
				)
			);

			// Register route at /wp-json/envira/v3/delete-gallery.
			register_rest_route(
				$this->domain . '/' . $this->version,
				'/delete-gallery',
				array(
					'methods'  => 'POST',
					'callback' => array( $this, 'delete_gallery' ),
				)
			);

			// Register route at /wp-json/envira/v3/insert-image.
			register_rest_route(
				$this->domain . '/' . $this->version,
				'/insert-image',
				array(
					'methods'  => 'POST',
					'callback' => array( $this, 'maybe_update_image' ),
				)
			);

			// Register route at /wp-json/envira/v3/delete-image.
			register_rest_route(
				$this->domain . '/' . $this->version,
				'/delete-image',
				array(
					'methods'  => 'POST',
					'callback' => array( $this, 'delete_image' ),
				)
			);

		}

		/**
		 * Authenticate API from the Lightroom Plugin.
		 *
		 * @access public
		 * @param WP_REST_Request $request Array of shortcode attributes.
		 * @return array
		 */
		public function authenticate( WP_REST_Request $request ) {

			$this->request = $request;

			$result = $this->validate_request();

			// If Access Token auth failed, return the WP_Error object.
			if ( is_wp_error( $result ) ) {

				$response = array(
					'state' => false,
					'ID'    => $result,
				);

					return wp_send_json( $response );
			}

			// If here, access token is valid
			// Get User ID.
			$user_id = get_option( 'envira_lightroom_user_id' );

			$response = array(
				'state' => true,
				'ID'    => get_user_by( 'id', $user_id ),
			);

			return wp_send_json( $response );

		}

		/**
		 * Version request function.
		 *
		 * @access public
		 * @param WP_REST_Request $request Array of shortcode attributes.
		 * @return array
		 */
		public function request_version( WP_REST_Request $request ) {

			$this->request = $request;

			$is_valid = $this->validate_request();

			if ( is_wp_error( $is_valid ) ) {

				return $is_valid;

			}

			$response = array(
				'version'  => $this->base->version,
				'required' => $this->base->required,
			);

			return wp_send_json( $response );

		}
		/**
		 * Gallery Endpoint: Returns the Gallery ID.
		 *
		 * @access public
		 * @param WP_REST_Request $request Array of shortcode attributes.
		 * @return string
		 */
		public function maybe_update_gallery( WP_REST_Request $request ) {

			$this->request = $request;

			$is_valid = $this->validate_request();

			if ( is_wp_error( $is_valid ) ) {

				return $is_valid;

			}

			$body = json_decode( $this->request->get_body() );

			$common = Envira_Gallery_Common::get_instance();

			// Update the gallery if we already have one.
			if ( isset( $body->gallery_id ) ) {

				$remote_id = intval( preg_replace( '/[^0-9]/', '', $body->gallery_id ) );

				if ( get_post_type( $remote_id ) === 'envira' ) {

					// Update Title If Lightroom Published Collection Title Was Changed.

					$gallery_data = array(
						'ID'         => $remote_id,
						'post_title' => $body->title,
					);

					wp_update_post( $gallery_data );

					$response = array(
						'id' => $remote_id,
					);

					return wp_send_json( $response );

				}
			} else {

				$defaults = array(
					'id'          => 0,
					'post_type'   => 'envira',
					'post_status' => 'publish',
					'post_title'  => '',
				);

				$gallery_data = array(
					'post_title' => $body->title,
				);

				// Assign author.
				$author_id = intval( get_option( 'envira_lightroom_user_id' ) );
				if ( $author_id <= 0 ) {
					// we want an admin, so let's determine this based on admin email in settings.
					$admin_email = is_multisite() ? get_site_option( 'admin_email' ) : get_option( 'admin_email' );
					// get the user.
					$admin_user = get_user_by( 'email', $admin_email );
					// assign the user's admin to be the author_id.
					if ( ! empty( $admin_user ) ) {
						$author_id = $admin_user->ID;
					} else {
						$author_id = 1;
					}
				}

				if ( user_can( $author_id, 'create_envira_galleries' ) ) {
					$gallery_data['post_author'] = apply_filters( 'envira_lightroom_author_id', $author_id );
				}

				// Combine default + data and insert the post.
				$post_args = wp_parse_args( $gallery_data, $defaults );

				$post = wp_insert_post( $post_args );

				// Get post meta.
				$gallery_data = get_post_meta( $post, '_eg_gallery_data', true );

				// If Gallery Data is emptyy prepare it.
				if ( empty( $gallery_data ) ) {

					$gallery_data = array();
				}

				// Loop through the defaults and prepare them to be stored.
				$defaults = $common->get_config_defaults( $post );

				foreach ( $defaults as $key => $default ) {

					$gallery_data['config'][ $key ] = $default;

				}

				// Update Fields.
				$gallery_data['id']              = $post;
				$gallery_data['config']['title'] = $body->title;
				$gallery_data['gallery']         = array();

				// Update.
				update_post_meta( $gallery_data['id'], '_eg_gallery_data', $gallery_data );

			}
			envira_flush_gallery_caches( $gallery_data['id'] );
			$response = array(
				'id' => $post,
			);

			return wp_send_json( $response );

		}

		/**
		 * Delete Gallery function.
		 *
		 * @access public
		 * @param WP_REST_Request $request Array of shortcode attributes.
		 * @return array
		 */
		public function delete_gallery( WP_REST_Request $request ) {

			$this->request = $request;

			$is_valid = $this->validate_request();

			if ( is_wp_error( $is_valid ) ) {

				return $is_valid;

			}

			$body = json_decode( $this->request->get_body() );

			// Check that $post_id is a envira post_type.
			if ( get_post_type( $body->gallery_id ) !== 'envira' ) {

				return false;

			}

			$response = array(
				'message' => __( 'Your Gallery has been deleted', 'envira-lightroom' ),
			);

			wp_delete_post( $body->gallery_id );

			return wp_send_json( $response );
		}

		/**
		 * Insert images function.
		 *
		 * @access public
		 * @param WP_REST_Request $request Array of shortcode attributes.
		 * @return string
		 */
		public function maybe_update_image( WP_REST_Request $request ) {

			$this->request = $request;

			$is_valid = $this->validate_request();

			if ( is_wp_error( $is_valid ) ) {

				return $is_valid;

			}

			$body = json_decode( $this->request->get_body() );

			$post_id = intval( preg_replace( '/[^0-9]/', '', $body->gallery_id ) );

			// Check that $post_id is a envira post_type.
			if ( get_post_type( $post_id ) !== 'envira' ) {

				return false;

			}

			$in_gallery = get_post_meta( $post_id, '_eg_in_gallery', true );

			if ( empty( $in_gallery ) ) {

				$in_gallery = array();

			}

			$gallery_data = get_post_meta( $post_id, '_eg_gallery_data', true );
			$caption      = trim( $body->caption, '"' );

			// If Gallery Data is emptyy prepare it.
			if ( empty( $gallery_data ) ) {

				$gallery_data = array();
			}

			// Require if the function doesnt exist.
			if ( ! function_exists( 'wp_handle_sideload' ) ) {

				require_once ABSPATH . 'wp-admin/includes/file.php';

			}
			// Make sure the wp_generate_attachment_metadata function is available.
			if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
				include ABSPATH . 'wp-admin/includes/image.php';
			}

			// Set the File name from the API.
			$new_attachment = $body->filename;

			// Grab the Upload Directory.
			$upload_dir = wp_upload_dir();

			// Set the upload path.
			$upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;

			// Decode the returned image.
			$base64_image = base64_decode( $body->file_data );
			$image_upload = file_put_contents( $upload_path . $new_attachment . '-temp', $base64_image ); // @codingStandardsIgnoreLine - WPFileSystem?

			// Prep the new file.
			$file             = array();
			$file['error']    = '';
			$file['tmp_name'] = $upload_path . $new_attachment . '-temp';
			$file['name']     = $new_attachment;
			$file['type']     = 'image/jpeg';
			$file['size']     = filesize( $upload_path . $new_attachment . '-temp' );

			$file_return = wp_handle_sideload( $file, array( 'test_form' => false ) );

			// Setup the Attachment Data.
			$attachment = array(
				'guid'           => $upload_dir['url'] . '/' . basename( $new_attachment ),
				'post_type'      => 'attachment',
				'post_mime_type' => 'image/jpeg',
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $body->title ) ),
				'post_excerpt'   => $caption,
				'post_status'    => 'inherit',
				'post_parent'    => $post_id,
			);

			// If we already have an image we're updating it, if not add a new one.
			if ( ! empty( $body->remote_id ) ) {

				// Store the attachment id for reference.
				$attachment_id = intval( preg_replace( '/[^0-9]/', '', $body->remote_id ) );
				$old_metadata  = wp_get_attachment_metadata( $attachment_id );
				$backup_sizes  = get_post_meta( $attachment_id, '_wp_attachment_backup_sizes', true );
				$old_file      = get_attached_file( $attachment_id );
				$delete_old    = $this->get_setting( 'lightroom_delete' );
				$new_meta      = array(
					'file' => $file_return['file'],
				);

				$new_url = update_post_meta( $attachment_id, '_wp_attached_file', $file_return['file'] );

				$data = wp_parse_args( $new_meta, $old_metadata );

				$meta_data   = wp_generate_attachment_metadata( $attachment_id, $file_return['file'] );
				$update_meta = wp_update_attachment_metadata( $attachment_id, $meta_data );

				if ( $delete_old ) {

					$uploadpath = wp_get_upload_dir();

					if ( ! empty( $old_metadata['thumb'] ) ) {
						// Don't delete the thumb if another attachment uses it.
						if ( ! $wpdb->get_row( $wpdb->prepare( "SELECT meta_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attachment_metadata' AND meta_value LIKE %s AND post_id <> %d", '%' . $wpdb->esc_like( $old_metadata['thumb'] ) . '%', $post_id ) ) ) { // @codingStandardsIgnoreLine
							$thumbfile = str_replace( basename( $old_file ), $old_metadata['thumb'], $file );
							/** This filter is documented in wp-includes/functions.php */
							$thumbfile = apply_filters( 'wp_delete_file', $thumbfile );
							unlink( path_join( $uploadpath['basedir'], $thumbfile ) );
						}
					}

						// Remove intermediate and backup images if there are any.
					if ( isset( $old_metadata['sizes'] ) && is_array( $old_metadata['sizes'] ) ) {
						foreach ( $old_metadata['sizes'] as $size => $sizeinfo ) {
							$intermediate_file = str_replace( basename( $old_file ), $sizeinfo['file'], $old_file );
							/** This filter is documented in wp-includes/functions.php */
							$intermediate_file = apply_filters( 'wp_delete_file', $intermediate_file );
							unlink( path_join( $uploadpath['basedir'], $intermediate_file ) );
						}
					}

					if ( is_array( $backup_sizes ) ) {
						foreach ( $backup_sizes as $size ) {
							$del_file = path_join( dirname( $old_metadata['file'] ), $size['file'] );
							/** This filter is documented in wp-includes/functions.php */
							$del_file = apply_filters( 'wp_delete_file', $del_file );
							unlink( path_join( $uploadpath['basedir'], $del_file ) );
						}
					}

						wp_delete_file( $old_file );

				}
			} else {

				// Insert new attachment - check.
				$attachment_id = wp_insert_attachment( $attachment, $file_return['file'], $post_id );

				// Generate Attachment Metadata.
				$meta_data = wp_generate_attachment_metadata( $attachment_id, $file_return['file'] );

				// Update Attachments metadata.
				$update_data = wp_update_attachment_metadata( $attachment_id, $meta_data );

			}

			// Update the attachment image post meta first.
			$has_gallery = get_post_meta( $attachment_id, '_eg_has_gallery', true );

			if ( empty( $has_gallery ) ) {

				$has_gallery = array();

			}
			// Replace line breaks with html tag.
			$url = wp_get_attachment_image_src( $attachment_id, 'full' );

			$has_gallery[] = $post_id;
			$image         = array(
				'status'  => 'active',
				'src'     => isset( $url[0] ) ? esc_url( $url[0] ) : '',
				'title'   => ( false === mb_detect_encoding( $body->title, 'UTF-8', true ) ) ? utf8_encode( $body->title ) : $body->title,
				'link'    => isset( $url[0] ) ? esc_url( $url[0] ) : '',
				'alt'     => $body->title,
				'caption' => $caption,
				'thumb'   => '',
				'tags'    => $body->keywords,
			);

			// Explode the tag list and save.
			if ( ! empty( $body->keywords ) && class_exists( 'Envira_Tags' ) ) {

				// Delete old tags first.
				wp_delete_object_term_relationships( $attachment_id, 'envira-tag' );

				$tags = explode( ',', $image['tags'] );

				if ( is_array( $tags ) ) {

					// Store tags in taxonomy.
					wp_set_object_terms( $attachment_id, $tags, 'envira-tag' );

				}
			}

			// Now add the image to the slider for this particular post.
			$in_gallery[] = $attachment_id;

			$gallery_data = $this->prepare_gallery_data( $gallery_data, $attachment_id, $image );

			// Update the slider data.
			update_post_meta( $attachment_id, '_eg_has_gallery', $has_gallery );
			update_post_meta( $post_id, '_eg_in_gallery', $in_gallery );
			update_post_meta( $post_id, '_eg_gallery_data', $gallery_data );

			$response = array(
				'image_id' => intval( preg_replace( '/[^0-9]/', '', $attachment_id ) ),
			);

			envira_crop_images( $post_id );

			// Flush the gallery cache.
			envira_flush_gallery_caches( $post_id );

			return wp_send_json( $response );

		}

		/**
		 * Delete an image.
		 *
		 * @access public
		 * @param WP_REST_Request $request The request.
		 * @return string
		 */
		public function delete_image( WP_REST_Request $request ) {

			$this->request = $request;

			$is_valid = $this->validate_request();

			if ( is_wp_error( $is_valid ) ) {

				return $is_valid;

			}

			$body = json_decode( $this->request->get_body() );

			if ( isset( $body->image_id ) ) {

				// Get all Envira Galleries that might contain this image.
				$gallery_ids = get_post_meta( $body->image_id, '_eg_has_gallery', true );

				// Iterate through each Gallery, getting its metadata and removing the image
				// if it's there.
				foreach ( (array) $gallery_ids as $gallery_id ) {

					// Get gallery data.
					$data = get_post_meta( $gallery_id, '_eg_gallery_data', true );

					// Skip if no images in this gallery.
					if ( ! isset( $data['gallery'] ) ) {
						continue;
					}

					// If here, the image exists in the Gallery. Remove it.
					unset( $data['gallery'][ $body->image_id ] );

					update_post_meta( $gallery_id, '_eg_gallery_data', $data );

					envira_flush_gallery_caches( $gallery_id );

				}

				wp_delete_attachment( $body->image_id );

			}

			$response = array(
				'message' => __( 'Gallery has been deleted', 'envira-lightroom' ),
			);

			return wp_send_json( $response );

		}

		/**
		 * Validate API Requests.
		 *
		 * @access private
		 * @return object
		 */
		private function validate_request() {

			$token        = $this->request->get_header( 'X-Envira-Lightroom-Access-Token' );
			$access_token = get_option( 'envira_lightroom_access_token' );
			$user_id      = get_option( 'envira_lightroom_user_id' );

			if ( empty( $user_id ) ) {
				// No need to authenticate.
				return new WP_Error( 'no_user', __( 'No valid user has been set. Please double check your settings, which can be found in your WordPress web site\'s Administration Interface > Envira > Settings > Lightroom', 'envira-lightroom' ), array( 'status' => 403 ) );
			}
			if ( ! isset( $token ) ) {
				// No need to authenticate.
				return new WP_Error( 'invalid_token', __( 'The Access Token supplied is invalid. Please double check your access token, which can be found in your WordPress web site\'s  Administration Interface > Envira > Settings > Lightroom', 'envira-lightroom' ), array( 'status' => 403 ) );

			}

			// Bail if there is no access token.
			if ( empty( $access_token ) ) {
				return new WP_Error( 'invalid_token', __( 'The Access Token supplied is invalid. Please double check your access token, which can be found in your WordPress web site\'s  Administration Interface > Envira > Settings > Lightroom', 'envira-lightroom' ), array( 'status' => 403 ) );
			}

			// If here, request included X-Envira-Lightroom-Access-Token
			// Validate the access token.
			if ( base64_encode( $access_token ) !== $token ) {
				// This message is displayed in the Lightroom Dialog Box.
				return new WP_Error( 'invalid_token', __( 'The Access Token supplied is invalid. Please double check your access token, which can be found in your WordPress web site\'s  Administration Interface > Envira > Settings > Lightroom', 'envira-lightroom' ), array( 'status' => 403 ) );
			}

		}

		/**
		 * Helper function to prepare the metadata for an image in a gallery.
		 *
		 * @since 1.0.0
		 *
		 * @param array $gallery_data   Array of data for the gallery.
		 * @param int   $id             The attachment ID to prepare data for.
		 * @param array $image          Attachment image. Populated if inserting from the Media Library.
		 * @return array $gallery_data Amended gallery data with updated image metadata.
		 */
		public function prepare_gallery_data( $gallery_data, $id, $image = false ) {

			// Get attachment.
			$attachment = get_post( $id );

			$url      = wp_get_attachment_image_src( $id, 'full' );
			$alt_text = isset( $image['title'] ) ? $image['title'] : get_the_title( $id );

			$new_image = array(
				'status'  => 'active',
				'src'     => isset( $url[0] ) ? esc_url( $url[0] ) : '',
				'title'   => isset( $image['title'] ) ? $image['title'] : get_the_title( $id ),
				'link'    => ( isset( $url[0] ) ? esc_url( $url[0] ) : '' ),
				'alt'     => ! empty( $alt_text ) ? $alt_text : '',
				'caption' => isset( $image['caption'] ) ? $image['caption'] : $attachment->post_excerpt,
				'thumb'   => '',
			);

			// Allow Addons to possibly add metadata now.
			$image = apply_filters( 'envira_gallery_ajax_prepare_gallery_data_item', $new_image, $image, $id, $gallery_data );

			// If gallery data is not an array (i.e. we have no images), just add the image to the array.
			if ( ! isset( $gallery_data['gallery'] ) || ! is_array( $gallery_data['gallery'] ) ) {
				$gallery_data['gallery']        = array();
				$gallery_data['gallery'][ $id ] = $image;
			} else {
				// Add this image to the start or end of the gallery, depending on the setting.
				$media_position = $this->get_setting( 'media_position' );

				switch ( $media_position ) {
					case 'before':
						// Add image to start of images array
						// Store copy of images, reset gallery array and rebuild.
						$images                         = $gallery_data['gallery'];
						$gallery_data['gallery']        = array();
						$gallery_data['gallery'][ $id ] = $image;
						foreach ( $images as $old_image_id => $old_image ) {
							if ( $old_image_id === $id ) {
								continue;
							}
							$gallery_data['gallery'][ $old_image_id ] = $old_image;
						}
						break;
					case 'after':
					default:
						// Add image, this will default to the end of the array.
						$gallery_data['gallery'][ $id ] = $image;
						break;
				}
			}

			// Filter and return.
			$gallery_data = apply_filters( 'envira_gallery_ajax_item_data', $gallery_data, $attachment, $id, $image );

			return $gallery_data;

		}

		/**
		 * Helper method for getting a setting's value. Falls back to the default
		 * setting value if none exists in the options table.
		 *
		 * @since 2.0.0
		 *
		 * @param string $key   The setting key to retrieve.
		 * @return string       Key value on success, false on failure.
		 */
		public function get_setting( $key ) {

			// Prefix the key.
			$prefixed_key = 'envira_gallery_' . $key;

			// Get the option value.
			$value = get_option( $prefixed_key );

			// If no value exists, fallback to the default.
			if ( ! isset( $value ) ) {
				$value = $this->get_setting_default( $key );
			}

			// Allow devs to filter.
			$value = apply_filters( 'envira_gallery_get_setting', $value, $key, $prefixed_key );

			return $value;

		}

		/**
		 * Get request headers.
		 *
		 * @access private
		 * @return string
		 */
		private function get_headers() {

			$headers = array();

			foreach ( $_SERVER as $name => $value ) {
				if ( substr( $name, 0, 5 ) === 'HTTP_' ) {
					$headers[ str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $name, 5 ) ) ) ) ) ] = $value;
				}
			}

			return $headers;

		}

		/**
		 * Singleton.
		 *
		 * @access public
		 * @return object
		 */
		public static function get_instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Lightroom_API ) ) {
				self::$instance = new Envira_Lightroom_API();
			}

			return self::$instance;
		}

	}

	$envira_lr_api = Envira_Lightroom_API::get_instance();

endif;
