<?php
/**
 * Envira Gallery Rest Class.
 *
 * @since 1.8.5
 *
 * @package Envira Gallery
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

namespace Envira\Frontend;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

use Envira\Frontend\Shortcode;

/**
 * Rest Class for envira.
 */
class Rest {

	/**
	 * Class Constructor
	 *
	 * @since 1.8.5
	 */
	public function __construct() {

		add_action( 'rest_api_init', array( $this, 'register_post_meta' ) );

	}

	/**
	 * Helper Method to register Envira gallery Meta
	 *
	 * @since 1.8.5
	 *
	 * @return void
	 */
	public function register_post_meta() {

		register_rest_field(
			'envira',
			'gallery_data',
			array(
				'get_callback'    => array( $this, 'get_gallery_data' ),
				'update_callback' => array( $this, 'update_gallery_data' ),
			)
		);

	}

	/**
	 * Rest API callback to get gallery data.
	 *
	 * @param [type] $object Post Object.
	 * @param [type] $field_name Rest Field Name.
	 * @param [type] $request Rest Request.
	 * @return array
	 */
	public function get_gallery_data( $object, $field_name, $request ) {

		$data = get_post_meta( $object['id'], '_eg_gallery_data', true );

		if ( ! is_array( $data ) ) {
			$data = array();
		}

		$i      = 0;
		$images = array();

		if ( isset( $data['gallery'] ) && is_array( $data['gallery'] ) ) {

			foreach ( $data['gallery'] as $id => $item ) {

				// Skip over images that are pending (ignore if in Preview mode).
				if ( isset( $item['status'] ) && 'pending' === $item['status'] && ! is_preview() ) {
					continue;
				}

				$width    = null;
				$height   = null;
				$imagesrc = envira_get_image_src( $id, $item, $data, false, false );

				// Get the image file path.
				$urlinfo       = wp_parse_url( $imagesrc );
				$wp_upload_dir = wp_upload_dir();

				// Interpret the file path of the image.
				if ( preg_match( '/\/[0-9]{4}\/[0-9]{2}\/.+$/', $urlinfo['path'], $matches ) ) {

					$file_path = $wp_upload_dir['basedir'] . $matches[0];

				} else {

					$content_dir = defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR : '/wp-content/';
					$uploads_dir = is_multisite() ? '/files/' : $content_dir;
					$file_path   = trailingslashit( $wp_upload_dir['basedir'] ) . basename( $urlinfo['path'] );
					$file_path   = preg_replace( '/(\/\/)/', '/', $file_path );

				}

				if ( file_exists( $file_path ) ) {
					list( $width, $height ) = getimagesize( $file_path );
				}

				$item['src']    = $imagesrc;
				$item['id']     = $id;
				$item['height'] = intval( $height );
				$item['width']  = intval( $width );
				$images[ $i ]   = $item;

				$i++;
			}
			$data['gallery'] = $images;

		}

		if ( ! isset( $data['config'] ) || ! is_array( $data['config'] ) ) {
			$data['config'] = array();
		}

		$data['config']['title'] = wp_strip_all_tags( get_the_title( $object['id'] ) );

		// Allow the data to be filtered before it is stored and used to create the gallery output.
		$data = apply_filters( 'envira_gallery_pre_data', $data, $object['id'] );

		return $data;

	}

	/**
	 * Rest API updater callback.
	 *
	 * @since 1.8.5
	 *
	 * @param array  $value Value to update.
	 * @param object $object Post Object.
	 * @param string $field_name Meta field name.
	 * @return array
	 */
	public function update_gallery_data( $value, $object, $field_name ) {

		$gallery_data = get_post_meta( $object->ID, '_eg_gallery_data', true );

		// If Gallery Data is emptyy prepare it.
		if ( ! is_array( $gallery_data ) ) {
			$gallery_data = array();
		}

		if ( ! is_array( $gallery_data['config'] ) ) {
			// Loop through the defaults and prepare them to be stored.
			$defaults = envira_get_config_defaults( $object->ID );

			foreach ( $defaults as $key => $default ) {

				$gallery_data['config'][ $key ] = $default;

			}
		}

		// Update Fields.
		$gallery_data['id']              = $object->ID;
		$gallery_data['config']['title'] = $object->title;

		if ( isset( $value['config'] ) ) {
			$gallery_data['config'] = wp_parse_args( $value['config'], $gallery_data['config'] );
		}

		if ( isset( $value['remove_image'] ) ) {
			$in_gallery  = get_post_meta( $object->ID, '_eg_in_gallery', true );
			$has_gallery = get_post_meta( $value['attach_id'], '_eg_has_gallery', true );

			// Unset the image from the gallery, in_gallery and has_gallery checkers.
			unset( $gallery_data['gallery'][ $value['attach_id'] ] );

			$key = array_search( $value['attach_id'], (array) $in_gallery, true );

			if ( false !== $key ) {
				unset( $in_gallery[ $key ] );
			}

			$has_key = array_search( $object->ID, (array) $has_gallery, true );

			if ( false !== $has_key ) {
				unset( $has_gallery[ $has_key ] );
			}
		}

		if ( isset( $value['update_image'] ) ) {

			$attach_id    = $value['attach_id'];
			$update_image = $value['updated_image'];

			if ( isset( $update_image['title'] ) ) {
				$gallery_data['gallery'][ $attach_id ]['title'] = trim( $update_image['title'] );
			}
			if ( isset( $update_image['caption'] ) ) {
				$gallery_data['gallery'][ $attach_id ]['caption'] = trim( $update_image['caption'] );
			}
		}
		if ( isset( $value['gallery'] ) ) {

			foreach ( (array) $value['gallery'] as $i => $image ) {
				$gallery_data = envira_prepare_gallery_data( $gallery_data, $image['id'] );
			}
		}

		// Flush gallery cache.
		envira_flush_gallery_caches( $object->ID );

		return update_post_meta( $object->ID, '_eg_gallery_data', $gallery_data );

	}

}
