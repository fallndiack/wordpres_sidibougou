<?php
/**
 * Shortcode class.
 *
 * @since 1.0.0
 *
 * @package Envira_Instagram
 * @author  Envira Team
 */

namespace Envira\Instagram\Frontend;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Envira\Admin\Metaboxes;

/**
 * Shortcode class.
 *
 * @since 1.0.0
 *
 * @package Envira_Instagram
 * @author  Envira Team
 */
class Shortcode {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Inject Images into Albums Admin. This allows the user to choose a cover image.
		add_filter( 'envira_albums_metaboxes_get_gallery_data', array( $this, 'inject_images' ), 10, 2 );

		// Inject Images into Frontend Gallery.
		add_filter( 'envira_gallery_pre_data', array( $this, 'inject_images' ), 10, 2 );
		add_filter( 'envira_images_pre_data', array( $this, 'inject_images' ), 10, 2 );

		// Inject Images into Album Lightbox.
		add_filter( 'envira_albums_shortcode_gallery', array( $this, 'inject_images' ), 10, 2 );

		// Decide If A Link to Gallery Images Should Be Created.
		add_filter( 'envira_gallery_create_link', array( $this, 'create_link' ), 10, 6 );

		// Remove Lightbox CSS class if user wants to directly link IG image.
		add_filter( 'envira_gallery_output_before_image', array( $this, 'gallery_output_before_image' ), 10, 5 );

		// Change link href to full sized IG image if user wants.
		add_filter( 'envira_gallery_link_href', array( $this, 'gallery_link_href' ), 10, 6 );

	}

	/**
	 * Change link href to full sized IG image if user wants
	 *
	 * @since 1.2.1
	 *
	 * @param array   $link Create link.
	 * @param array   $data Gallery data.
	 * @param string  $id The ID.
	 * @param array   $item Item Data.
	 * @param int     $i Counter.
	 * @param boolean $is_mobile Is mobile.
	 */
	public function gallery_link_href( $link, $data, $id, $item, $i, $is_mobile ) {

		if ( isset( $data['config']['instagram_link'] ) && 'instagram_image' === $data['config']['instagram_link'] && ! empty( $item['instagram_high_res'] ) ) {
			return $item['src']; // used to be item['instagram_high_res'];.
		} elseif ( isset( $data['config']['instagram_link'] ) && 'instagram_page' === $data['config']['instagram_link'] && ! empty( $item['instagram_link'] ) ) {
			return $item['instagram_link'];
		} elseif ( ! empty( $link ) ) {
			return $link;
		} elseif ( ! empty( $item['src'] ) ) {
			return $item['src'];
		}

	}

	/**
	 * Check on adding a create link for galleries, hooking into shortcode.php
	 *
	 * @since 1.2.1
	 *
	 * @param array  $output Output.
	 * @param string $id The ID.
	 * @param array  $item Item Data.
	 * @param int    $data Gallery data.
	 * @param int    $i Counter.
	 */
	public function gallery_output_before_image( $output, $id, $item, $data, $i ) {

		if ( isset( $data['config']['instagram_link'] ) && 'instagram_image' === $data['config']['instagram_link'] ) {

			$output = str_replace( 'envira-gallery-9160 envira-gallery-link', '', $output );

		}

		return $output;

	}

	/**
	 * Check on adding a create link for galleries, hooking into shortcode.php
	 *
	 * @since 1.2.1
	 *
	 * @param array   $create_link Create link.
	 * @param array   $data Gallery data.
	 * @param string  $id The ID.
	 * @param array   $item Item Data.
	 * @param int     $i Counter.
	 * @param boolean $is_mobile Is mobile.
	 */
	public function create_link( $create_link, $data, $id, $item, $i, $is_mobile ) {

		if ( isset( $data['config']['type'] ) && 'instagram' === $data['config']['type'] && ! $data['config']['instagram_link'] ) {
			$create_link = false;
		}

		return $create_link;

	}

	/**
	 * Injects gallery images into the given $data array, using the $data settings
	 *
	 * @since 1.0.0
	 *
	 * @param array $data  Gallery Config.
	 * @param int   $id      The gallery ID.
	 * @return array $data Amended array of gallery config, with images.
	 */
	public function inject_images( $data, $id ) {

		// Return early if not an Instagram gallery.
		if ( 'instagram' !== envira_get_config( 'type', $data ) ) {
			return $data;
		}

		// Return early if pagination is activated on this instagram gallery.
		if ( class_exists( 'Envira_Pagination' ) && 1 === intval( envira_get_config( 'pagination', $data ) ) ) {
			return $data;
		}

		// Grab the Instagram data from cache or live.
		$instagram_images = ( envira_get_config( 'instagram_cache', $data ) ? $this->get_instagram_data( $id, $data ) : $this->_get_instagram_data( $id, $data ) );
		if ( ! $instagram_images ) {
			return $data;
		}

		// Insert data into gallery.
		$data['gallery'] = $instagram_images;

		return $data;

	}

	/**
	 * Attempts to get Instagram image data from transient/cache
	 *
	 * If transient does not exist, performs a live query and caches the results
	 *
	 * @since 1.0.0
	 *
	 * @param int   $id Gallery ID.
	 * @param array $data Gallery Data.
	 * @return array Instagram Images
	 */
	public function get_instagram_data( $id, $data ) {

		// Attempt to return the transient first, otherwise generate the new query to retrieve the data.
		$instagram_images = get_transient( '_envira_instagram_' . $id );
		if ( false === $instagram_images ) {
			$instagram_images = $this->_get_instagram_data( $id, $data );
			if ( $instagram_images ) {
				$expiration = envira_get_transient_expiration_time( 'envira-instagram' );
				set_transient( '_envira_instagram_' . $id, maybe_serialize( $instagram_images ), $expiration );
			}
		}

		// Return the slider data.
		return maybe_unserialize( $instagram_images );

	}

	/**
	 * Queries Instagram for image data
	 *
	 * @since 1.0.0
	 *
	 * @param int   $id Gallery ID.
	 * @param array $data Gallery Data.
	 * @return mixed false|Image Array
	 */
	public function _get_instagram_data( $id, $data ) { // @codingStandardsIgnoreLine

		// With addition of multiple instagram accounts, make sure to get the slot.
		$slot        = envira_get_config( 'instagram_account', $data );
		$slot_number = ( false !== $slot ) ? $slot : 1;

		// Grab the Instagram auth data.
		$auth = envira_instagram_get_instagram_auth( $slot_number );

		if ( empty( $auth['token'] ) || empty( $auth['id'] ) ) {
			return false;
		}

		// Ping Instagram to retrieve the proper data.
		$count = envira_get_config( 'instagram_number', $data );

		if ( ! is_admin() && $count > 33 ) {

			// How many times to we need to paginate.
			$loop = ceil( $count / 33 ) - 1;

			$response = wp_remote_get( esc_url_raw( 'https://api.instagram.com/v1/users/' . $auth['id'] . '/media/recent/?access_token=' . $auth['token'] . '&count=' . envira_get_config( 'instagram_number', $data ) ) );

			// If there is an error with the request, return false.
			if ( is_wp_error( $response ) ) {
				return false;
			}

			$body = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( isset( $body['pagination']['next_max_id'] ) ) {
				$next = $body['pagination']['next_max_id'];
			} else {
				$next = false;
			}

			for ( $i = 0; $i < $loop; $i++ ) {

				$count = $count - 33;

				$page             = wp_remote_get( esc_url_raw( 'https://api.instagram.com/v1/users/' . $auth['id'] . '/media/recent/?access_token=' . $auth['token'] . '&count=' . $count . '&max_id=' . $next ) );
				$response_code    = wp_remote_retrieve_response_code( $page );
				$response_message = wp_remote_retrieve_response_message( $page );
				$response_body    = json_decode( wp_remote_retrieve_body( $page ) );

				// If there is an error with the request, return false.
				if ( is_wp_error( $page ) ) {
					return false;
				}

				// If there is an invalid response code, return false.
				if ( 200 !== $response_code && ! empty( $response_message ) ) {
					if ( defined( 'ENVIRA_DEBUG' ) && 'true' === ENVIRA_DEBUG ) {
						error_log( 'Envira (Instagram): Invalid Response Code ' . $response_code ); // @codingStandardsIgnoreLine
						error_log( print_r( $response_message, true ) ); // @codingStandardsIgnoreLine
						error_log( print_r( $response_body->meta->error_message, true ) ); // @codingStandardsIgnoreLine
					}
					return false;
				} elseif ( 200 !== $response_code ) {
					if ( defined( 'ENVIRA_DEBUG' ) && 'true' === ENVIRA_DEBUG ) {
						error_log( 'Envira (Instagram): Invalid Response Code ' . $response_code ); // @codingStandardsIgnoreLine
						error_log( print_r( $response_message, true ) ); // @codingStandardsIgnoreLine 
						error_log( print_r( $response_body->meta->error_message, true ) ); // @codingStandardsIgnoreLine
					}
					return false;
				}

				$paged_response = json_decode( wp_remote_retrieve_body( $page ), true );

				$body['data'] = wp_parse_args( $paged_response['data'], $body['data'] );

				if ( ! isset( $paged_response['data'] ) ) {
					// there was an error, something wrong happened.
					error_log( 'Instagram Returned Page:' . PHP_EOL . print_r( $page, true ) ); // @codingStandardsIgnoreLine
					error_log( 'Instagram Paged Response:' . PHP_EOL . print_r( $paged_response, true ) ); // @codingStandardsIgnoreLine
				}

				$next = isset( $paged_response['pagination']['next_max_id'] ) ? $paged_response['pagination']['next_max_id'] : '';

			}
		} else {

			$response = wp_remote_get( esc_url_raw( 'https://api.instagram.com/v1/users/' . $auth['id'] . '/media/recent/?access_token=' . $auth['token'] . '&count=' . envira_get_config( 'instagram_number', $data ) ) );

			// If there is an error with the request, return false.
			if ( is_wp_error( $response ) ) {
				return false;
			}

			$body = json_decode( wp_remote_retrieve_body( $response ), true );

		}

		if ( is_null( $body ) || empty( $body['data'] ) ) {
			if ( ! empty( $body['error_type'] ) && ( defined( 'ENVIRA_DEBUG' ) && ENVIRA_DEBUG ) ) {
				// there was an error, usually a "exceeded the maximum number of requests per hour" error.
				error_log( 'Instagram Returned Error:' . PHP_EOL . print_r( $body, true ) ); // @codingStandardsIgnoreLine
			}
			return false;
		}

		// Loop through the response data and remove any emoticons that can't be stored in the DB.
		$instagram_data = array();
		$image_ids      = array();
		$res            = envira_get_config( 'instagram_res', $data );
		foreach ( $body['data'] as $i => $image ) {

			// Make sure this image isn't already in the gallery.
			if ( in_array( $image['id'], $image_ids, true ) ) {
				continue;
			}

			// Determine link.
			if ( envira_get_config( 'instagram_link', $data ) === 'instagram_page' && ! empty( $image['link'] ) ) {
				// Add a new value to pass to $instagram_data linking to Instagram page containing image.
				$link           = ( ! empty( $image['link'] ) ? esc_url( $image['link'] ) : '' );
				$instagram_link = $link;
			} elseif ( envira_get_config( 'instagram_link', $data ) === 'instagram_image' ) {
				// Link to large Instagram image.
				$link           = ( ! empty( $image['images']['standard_resolution']['url'] ) ? esc_url( $image['images']['standard_resolution']['url'] ) : '' );
				$instagram_link = $link;
			} else {
				$link           = false;
				$instagram_link = false;
			}

			// Determine target.
			if ( envira_get_config( 'instagram_link_target', $data ) && ! empty( $image['link'] ) ) {
				// Determine if the link to Instagram page opens in a new window.
				$target = '_blank';
			} else {
				$target = false;
			}

			// Attempt to get caption.
			$caption = ( envira_get_config( 'instagram_caption', $data ) && ! empty( $image['caption']['text'] ) ? esc_attr( $image['caption']['text'] ) : '' );

			// Limit caption length, if required.
			$caption_length = envira_get_config( 'instagram_caption_length', $data );
			if ( ! empty( $caption ) && $caption_length > 0 ) {
				$caption_words       = explode( ' ', $caption );
				$caption_words_limit = array_slice( $caption_words, 0, $caption_length );
				$caption             = implode( ' ', $caption_words_limit );
				$caption             = htmlspecialchars_decode( $caption ); // fixes a 'cannot use in in operator' JS error when attempting to open gallery in an album.
			}

			// grab the "hacked" way to get the full/non-cropped image.
			$starting_image       = $image['images']['thumbnail']['url'];
			$starting_image       = preg_replace( '/\?.*/', '', $starting_image );
			$starting_image_array = explode( '/', $starting_image );

			// remove the un-needed parts of this supplied url.
			if ( count( $starting_image_array ) === 8 ) {
				unset( $starting_image_array[6] );
			}

			// full doesn't appear to be available anymore.
			unset( $starting_image_array[0] );
			unset( $starting_image_array[1] );
			unset( $starting_image_array[2] );
			unset( $starting_image_array[4] );

			// put the url back together.
			$instagram_high_res = implode( '/', $starting_image_array );
			$instagram_high_res = 'https://scontent.cdninstagram.com/' . $instagram_high_res;
			$width              = false;
			$height             = false;

			// it looks like instagram doesn't all full at the moment,
			// so make 'full' into 'standard_resolution' (and we will remove the setting).
			if ( 'full' === $res ) {
				$res = 'standard_resolution';
			}

			$src = ( ! empty( $image['images'][ $res ]['url'] ) ? esc_url( $image['images'][ $res ]['url'] ) : '' );

			// Get the width/height from Instagram, we don't need to determine it.
			if ( ! empty( $image['images'][ $res ]['width'] ) ) {
				$width = intval( $image['images'][ $res ]['width'] );
			}
			if ( ! empty( $image['images'][ $res ]['width'] ) ) {
				$height = intval( $image['images'][ $res ]['height'] );
			}

			/*
			Attempt to get width/height of image.
			if ( ! empty( $images['standard_resolution'] ) ) {

			}
			*/

			// Build array of instagram data for this image.
			$instagram_data[ $i ] = array(
				'status'             => 'published',
				'src'                => $src,
				'title'              => $caption,
				'width'              => $width,
				'height'             => $height,
				'link'               => $link,
				'instagram_link'     => $instagram_link,
				'instagram_high_res' => $instagram_high_res,
				'alt'                => '',
				'target'             => $target,
				'caption'            => $caption,
				'thumb'              => ( ! empty( $image['images']['thumbnail']['url'] ) ? esc_url( $image['images']['thumbnail']['url'] ) : '' ),
				'link_new_window'    => 0,
			);

			$image_ids[] = $image['id'];
		}

		// Return the Instagram data, compatible for the Envira Gallery.
		return apply_filters( 'envira_instagram_get_instagram_data', $instagram_data, $body['data'], $id, $data );

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Instagram_Shortcode object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Instagram_Shortcode ) ) {
			self::$instance = new Envira_Instagram_Shortcode();
		}

		return self::$instance;

	}

}
