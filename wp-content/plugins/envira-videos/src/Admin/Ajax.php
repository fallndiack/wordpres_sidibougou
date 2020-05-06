<?php
/**
 * Ajax class.
 *
 * @since 1.0.0
 *
 * @package Envira_Videos
 * @author  Envira Team
 */

namespace Envira\Videos\Admin;

use Envira\Admin\Metaboxes;
use Envira\Utils\Import;
use Envira\Videos\Admin\Vimeo;

/**
 * Ajax class.
 *
 * @since 1.0.0
 *
 * @package Envira_Videos
 * @author  Envira Team
 */
class Ajax {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		add_action( 'wp_ajax_envira_videos_is_hosted_video', array( $this, 'is_hosted_video' ) );
		add_action( 'wp_ajax_envira_videos_insert_videos', array( $this, 'insert_videos' ) );
		add_action( 'envira_gallery_ajax_save_meta', array( $this, 'save_meta' ), 10, 4 );
		add_filter( 'envira_gallery_ajax_save_bulk_meta', array( $this, 'save_bulk' ), 10, 4 );

	}

	/**
	 * Called by the media view when the video URL input is changed
	 * Checks if the supplied video URL is a locally hosted video URL or not
	 *
	 * @since 1.1.1
	 *
	 * @return void
	 */
	public function is_hosted_video() {

		// Run a security check first.
		check_ajax_referer( 'envira-videos-media-view-nonce', 'nonce' );

		// Setup vars.
		$video_url = ( isset( $_POST['video_url'] ) ? sanitize_text_field( wp_unslash( $_POST['video_url'] ) ) : '' );

		// Check a URL was defined.
		if ( empty( $video_url ) ) {
			wp_send_json_error( __( 'No video URL was defined', 'envira-videos' ) );
			die();
		}

		// Get video type.
		$video_type = envira_video_get_video_type( $video_url, array(), array(), true );

		// Depending on the video type, return true or false to determine whether it's a self hosted video.
		$is_hosted_video = false;
		switch ( $video_type ) {
			case 'youtube':
			case 'youtube_playlist':
			case 'vimeo':
			case 'wistia':
			case 'dailymotion':
			case 'metacafe':
			case 'instagram':
			case 'facebook':
			case 'instagram_tv':
			case 'twitch':
			case 'videopress':
				$is_hosted_video = false;
				break;

			case 'mp4':
			case 'flv':
			case 'ogv':
			case 'webm':
				$is_hosted_video = true;
				break;

			default:
				// Allow addons to define whether the video type is hosted or third party.
				$is_hosted_video = apply_filters( 'envira_videos_is_hosted_video', $is_hosted_video, $video_type );
				break;
		}

		// Return.
		wp_send_json_success( $is_hosted_video );
		die();

	}

	/**
	 * Called by Envira Gallery when inserting media (images or videos).
	 * Checks if videos were specified, and if so grabs the plaecholder images and adds them as images to Envira
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function insert_videos() {

		// Run a security check first.
		check_ajax_referer( 'envira-videos-media-view-nonce', 'nonce' );

		// Setup vars.
		$videos  = ( isset( $_POST['videos'] ) ? wp_unslash( $_POST['videos'] ) : '' ); // @codingStandardsIgnoreLine - we are santitizing below
		$post_id          = ( isset( $_POST['post_id'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) ) : false );
		$sanitized_videos = array();

		if ( empty( $videos ) || empty( $videos ) ) {
			wp_send_json_error( __( 'No videos or Gallery ID were specified', 'envira-videos' ) );
			die();
		}

		foreach ( (array) $videos as $i => $video ) {
			foreach ( $video as $index => $video_data ) {
				$sanitized_videos[ $i ][ $index ] = ( ( $video_data ) );
			}
		}

		// Swap video vars.
		$videos = $sanitized_videos;

		// Get in gallery and gallery data meta.
		$gallery_data = get_post_meta( $post_id, '_eg_gallery_data', true );
		$in_gallery   = get_post_meta( $post_id, '_eg_in_gallery', true );

		// If this is a new post, potentially no meta, so setup defaults.
		if ( empty( $gallery_data ) ) {
			$gallery_data            = array();
			$gallery_data['gallery'] = array();
		}
		if ( empty( $in_gallery ) ) {
			$in_gallery = array();
		}

		// Get helpers.
		$metaboxes = new Metaboxes();

		// Loop through the videos and add them to the gallery.
		foreach ( (array) $videos as $i => $video ) {
			// Pass over if the main items necessary for the video are not set.
			if ( ! isset( $video['link'] ) ) {
				continue;
			}

			$video['link'] = str_replace( 'youtube.com/embed/', 'youtube.com/watch?v=', $video['link'] );

			// Get video type and ID.
			$result = envira_video_get_video_type( $video['link'], $video, $gallery_data );
			if ( ! $result ) {
				continue;
			}

			// Get the image depending on the video type.
			switch ( $result['type'] ) {
				case 'youtube':
					$video['src'] = $this->get_youtube_thumbnail_url( $result['video_id'] );
					break;

				case 'youtube_playlist':
					$video['src'] = $this->get_youtube_playlist_thumbnail_url( $result['video_id'] );
					break;

				case 'vimeo':
					$video['src'] = $this->get_vimeo_thumbnail_url( $result['video_id'] );
					break;

				case 'wistia':
					$video['src'] = $this->get_wistia_thumbnail_url( $video['link'] ); // Deliberate; Wistia doesn't need a video ID.
					break;

				case 'dailymotion':
					$video['src'] = $this->get_dailymotion_thumbnail_url( $result['video_id'] );
					break;

				case 'metacafe':
					$video['src'] = $this->get_metacafe_thumbnail_url( $result['video_id'], $result['slug'] );
					break;

				case 'instagram':
					$instagram_type = ( strpos( $video['link'], '/tv/' ) !== false ) ? 'tv' : 'p';
					$video['src']   = $this->get_instagram_thumbnail_url( $result['video_id'], $instagram_type );
					break;

				case 'instagram_tv':
					$instagram_type = ( strpos( $video['link'], '/tv/' ) !== false ) ? 'tv' : 'p';
					$video['src']   = $this->get_instagram_thumbnail_url( $result['video_id'], $instagram_type );
					break;

				case 'facebook':
					$video['src'] = $this->get_facebook_thumbnail_url( $result['video_id'] );
					break;

				case 'twitch':
					$video['src'] = $this->get_twitch_thumbnail_url( $result['video_id'] );
					break;

				case 'videopress':
					$video['src'] = $this->get_videopress_thumbnail_url( $result['video_id'] );
					break;

				case 'mp4':
				case 'flv':
				case 'ogv':
				case 'webm':
					$video['src'] = $video['image'];
					break;

				default:
					// Allow devs and custom addons to get the thumbnail for their custom video type.
					$video['src'] = apply_filters( 'envira_videos_get_thumbnail_url', '', $result, $video );
					break;
			}

			// Check $video['src'] now exists - if not, discard this video.
			if ( ! isset( $video['src'] ) ) {
				continue;
			}

			// Get remote image into local filesystem, allow unfiltered because some src are horrible like Instagram.
			$old_value_unfiltered_uploads = defined( 'ALLOW_UNFILTERED_UPLOADS' ) ? ALLOW_UNFILTERED_UPLOADS : false;
			if ( defined( 'ALLOW_UNFILTERED_UPLOADS' ) ) {
				// already defined, set to true.
				define( 'ALLOW_UNFILTERED_UPLOADS', true );
			} else {
				// define the global if it's not defined.
				define( 'ALLOW_UNFILTERED_UPLOADS', true );
			}
			$stream = $this->get_remote_thumbnail( $video['src'], $gallery_data, $video, $post_id );
			define( 'ALLOW_UNFILTERED_UPLOADS', $old_value_unfiltered_uploads );

			// Check for errors.
			if ( is_wp_error( $stream ) ) {
				/* translators: %1 %2 */
				wp_send_json_error( sprintf( __( 'Video #%1$s Image Error: %2$s', 'envira-videos' ), ( $i + 1 ), $stream->get_error_message() ) );
				die();
			}
			if ( ! empty( $stream['error'] ) ) {
				/* translators: %1 %2 */
				wp_send_json_error( sprintf( __( 'Video #%1$s Image Error: %2$s', 'envira-videos' ), ( $i + 1 ), $stream['error'] ) );
				die();
			}

			// Add video to gallery.
			$attachment_id = $stream['attachment_id'];
			$video_to_add  = array(
				'status'  => 'active',
				'src'     => ( isset( $stream ) ? $stream['url'] : '' ), // Image URL.
				'title'   => $video['title'],
				'link'    => $video['link'], // Video URL.
				'alt'     => $video['alt'],
				'caption' => $video['caption'],
				'thumb'   => '',
			);

			// If gallery data is not an array (i.e. we have no images), just add the image to the array.
			if ( ! isset( $gallery_data['gallery'] ) || ! is_array( $gallery_data['gallery'] ) ) {

				$gallery_data['gallery']                   = array();
				$gallery_data['gallery'][ $attachment_id ] = $video_to_add;

			} else {

				// Add this image to the start or end of the gallery, depending on the setting.
				$media_position = envira_get_setting( 'media_position' );

				switch ( $media_position ) {

					case 'before':
						// Add image to start of images array
						// Store copy of images, reset gallery array and rebuild.
						$videos                                    = $gallery_data['gallery'];
						$gallery_data['gallery']                   = array();
						$gallery_data['gallery'][ $attachment_id ] = $video_to_add;

						foreach ( $videos as $old_image_id => $old_image ) {
							$gallery_data['gallery'][ $old_image_id ] = $old_image;
						}

						break;
					case 'after':
					default:
						// Add image, this will default to the end of the array.
						$gallery_data['gallery'][ $attachment_id ] = $video_to_add;

						break;
				}
			}

			// Add gallery ID to video attachment ID.
			$has_gallery = get_post_meta( $attachment_id, '_eg_has_gallery', true );
			if ( empty( $has_gallery ) ) {
				$has_gallery = array();
			}
			$has_gallery[] = $post_id;
			update_post_meta( $attachment_id, '_eg_has_gallery', $has_gallery );

			// Add video to in_gallery.
			$in_gallery[] = $attachment_id;
		}

		// Update the gallery data.
		update_post_meta( $post_id, '_eg_in_gallery', $in_gallery );
		update_post_meta( $post_id, '_eg_gallery_data', $gallery_data );

		// Get instances
		// $common = Envira_Gallery_Common::get_instance();
		// If the thumbnails option is checked and the image isn't already, crop images accordingly.
		if ( isset( $gallery_data['config']['thumbnails'] ) && $gallery_data['config']['thumbnails'] ) {
			$args = array(
				'position' => 'c',
				'width'    => ( isset( $gallery_data['config']['thumbnails_width'] ) ? $gallery_data['config']['thumbnails_width'] : envira_get_config_default( 'thumbnails_width' ) ),
				'height'   => ( isset( $gallery_data['config']['thumbnails_height'] ) ? $gallery_data['config']['thumbnails_height'] : envira_get_config_default( 'thumbnails_width' ) ),
				'quality'  => 100,
				'retina'   => false,
			);
			$args = apply_filters( 'envira_gallery_crop_image_args', $args );
			envira_crop_images( $post_id );
		}

		// Flush the gallery cache.
		envira_flush_gallery_caches( $post_id );

		// Return a HTML string comprising of all gallery images, so the UI can be updated.
		$html = '';
		foreach ( (array) $gallery_data['gallery'] as $id => $data ) {
			$html .= $metaboxes->get_gallery_item( $id, $data, $post_id );
		}

		// Return success with gallery grid HTML.
		wp_send_json_success( $html );
		die;

	}



	/**
	 * Attempts to get a HD thumbnail URL for the given YouTube video ID.
	 * If a 120x90 grey placeholder image is returned, the video isn't HD, so
	 * the function will return the SD thumbnail URL
	 *
	 * @since 1.0.0
	 *
	 * @param string $video_src Video Src.
	 * @param array  $gallery_data Gallery Data.
	 * @param string $video Video.
	 * @param int    $post_id Post ID.
	 * @return string HD or SD Thumbnail URL
	 */
	public function get_remote_thumbnail( $video_src, $gallery_data, $video, $post_id ) {

		global $wpdb;

		$attachment_id = false;
		$file_location = false;

		// remove everything up through /uploads/.
		$upload_dir = wp_upload_dir();
		// if ( strpos($video_src, $upload_dir['baseurl'] === true ) ) {
			// is video already in the WP media library?
			$stripped_src  = str_replace( $upload_dir['baseurl'] . '/', '', $video_src );
			$attachment_id = $this->get_post_id_from_guid( $stripped_src );
		// }
		if ( ! $attachment_id ) {
			// doesnt' exist.
			$envira_gallery_import = new Import();
			$stream                = $envira_gallery_import->import_remote_image( $video_src, $gallery_data, $video, $post_id, 0, true );
		} else {
			$stream                  = array();
			$stream['attachment_id'] = $attachment_id;
			$stream['url']           = wp_get_attachment_url( $attachment_id );
		}

		return $stream;
	}

	/**
	 * Get Post ID from GUID
	 *
	 * @since 1.0.0
	 *
	 * @param string $guid GUID.
	 * @return string
	 */
	public function get_post_id_from_guid( $guid ) {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_wp_attached_file' AND meta_value=%s", $guid ) ); // @codingStandardsIgnoreLine

	}

	/**
	 * Attempts to get a HD thumbnail URL for the given YouTube video ID.
	 * If a 120x90 grey placeholder image is returned, the video isn't HD, so
	 * the function will return the SD thumbnail URL
	 *
	 * @since 1.0.0
	 *
	 * @param string $video_id YouTube Video ID.
	 * @return string HD or SD Thumbnail URL
	 */
	public function get_youtube_thumbnail_url( $video_id ) {

		// Determine video URL.
		$prefix   = is_ssl() ? 'https' : 'http';
		$base_url = $prefix . '://img.youtube.com/vi/' . $video_id . '/';
		$hd_url   = $base_url . 'maxresdefault.jpg'; // 1080p or 720p
		$sd_url   = $base_url . '0.jpg'; // 480x360

		// Get HD image from YouTube.
		$image_data = wp_remote_get(
			$hd_url,
			array(
				'timeout' => 10,
			)
		);

		// Check request worked.
		if ( is_wp_error( $image_data ) || ! isset( $image_data['body'] ) ) {
			// Failed - fallback to SD Thumbnail.
			return $sd_url;
		}

		// Get image size.
		if ( ! function_exists( 'getimagesizefromstring' ) ) {
			// PHP 5.3-.
			$uri        = 'data://application/octet-stream;base64,' . base64_encode( $image_data['body'] );
			$image_size = getimagesize( $image_data['body'] );
		} else {
			// PHP 5.4+.
			$image_size = getimagesizefromstring( $image_data['body'] );
		}

		// Check request worked.
		if ( ! is_array( $image_size ) ) {
			// Failed - fallback to SD Thumbnail.
			return $sd_url;
		}

		// Check image size isn't 120x90.
		if ( 120 === $image_size[0] && 90 === $image_size[1] ) {
			// Failed - fallback to SD Thumbnail.
			return $sd_url;
		}

		// Image is a valid YouTube HD thumbnail.
		return $hd_url;

	}

	/**
	 * Attempts to get a HD thumbnail URL for the given YouTube video ID.
	 * If a 120x90 grey placeholder image is returned, the video isn't HD, so
	 * the function will return the SD thumbnail URL
	 *
	 * @since 1.0.0
	 *
	 * @param string $video_id YouTube Video ID.
	 * @return string HD or SD Thumbnail URL
	 */
	public function get_youtube_playlist_thumbnail_url( $video_id ) {

		// Only way to get a YouTube video playlist thumbnail url is via the YouTube API
		// If the user hasn't setup a API w/ a key, then we revert to a generic image?
		$default_url = apply_filters( 'envira_default_youtube_playlist_thumbnail_url', 'https://i.ytimg.com/vi/000/maxresdefault.jpg', $video_id );
		$key         = esc_html( envira_video_get_setting( 'youtube_api_key' ) );

		if ( $video_id && $key ) {

			$api_url = 'https://www.googleapis.com/youtube/v3/playlists?part=snippet&id=' . $video_id . '&key=' . $key;

			// Get HD image from YouTube.
			$image_data = wp_remote_get(
				$api_url,
				array(
					'timeout' => 10,
				)
			);

			// Check request worked.
			if ( is_wp_error( $image_data ) || ! isset( $image_data['body'] ) ) {
				// Failed - fallback to SD Thumbnail.
				return $default_url;
			} else {
				$response  = wp_remote_retrieve_body( $image_data );
				$response  = json_decode( $response );
				$thumb_url = ( isset( $response->items[0]->snippet->thumbnails->standard->url ) ) ? esc_url( $response->items[0]->snippet->thumbnails->standard->url ) : esc_url( $response->items[0]->snippet->thumbnails->standard->default );
				if ( $thumb_url ) {
					return $thumb_url;
				} else {
					return $default_url;
				}
			}
		} else {

			return $default_url;

		}

	}

	/**
	 * Attempts to get the highest resolution thumbnail URL for the given Vimeo video ID.
	 *
	 * @since 1.0.0
	 *
	 * @param string $video_id Vimeo Video ID.
	 * @return string Best resolution URL
	 */
	public function get_vimeo_thumbnail_url( $video_id ) {

		// Get existing access token.
		$vimeo_access_token = get_option( 'envira_videos_vimeo_access_token' );

		// Load Vimeo API.
		$vimeo = new Vimeo( '5edbf52df73b6834db186409f88d2108df6a3d7f', '54e233c7ec90b22ad7cc77875b9a5a9d3083fa08' );
		$vimeo->setToken( $vimeo_access_token );

		// Attempt to get video.
		$response = $vimeo->request( '/videos/' . $video_id . '/pictures' );

		// Check response.
		if ( 200 !== $response['status'] ) {
			// May need a new access token
			// Clear old token + request a new one.
			$vimeo->setToken( '' );
			$token              = $vimeo->clientCredentials();
			$vimeo_access_token = $token['body']['access_token'];
			$vimeo->setToken( $vimeo_access_token );

			// Store new token in options data.
			update_option( 'envira_videos_vimeo_access_token', $vimeo_access_token );

			// Run request again.
			$response = $vimeo->request( '/videos/' . $video_id . '/pictures' );
		}

		// Check response.
		if ( 200 !== $response['status'] ) {
			// Really a failure!
			return false;
		}

		// If here, we got the video details
		// Check thumbnails are in the response.
		if ( ! isset( $response['body']['data'] ) || ! isset( $response['body']['data'][0] ) || ! isset( $response['body']['data'][0]['sizes'] ) ) {
			return false;
		}

		// Get last item from the array index, as this is the highest resolution thumbnail.
		$thumbnail = end( $response['body']['data'][0]['sizes'] );

		// Check thumbnail URL exists.
		if ( ! isset( $thumbnail['link'] ) ) {
			return false;
		}

		// Cleanup.
		unset( $vimeo );

		// Remove some args and return.
		return strtok( $thumbnail['link'], '?' );

	}

	/**
	 * Attempts to get the highest resolution thumbnail URL for the given Wistia video link.
	 *
	 * @since 1.0.0
	 *
	 * @param string $video_link Wistia Video Link.
	 * @return string Thumbnail URL
	 */
	public function get_wistia_thumbnail_url( $video_link ) {

		$res = wp_remote_get( 'http://fast.wistia.net/oembed?url=' . rawurlencode( $video_link ) );
		$bod = wp_remote_retrieve_body( $res );
		$api = json_decode( $bod, true );
		if ( ! empty( $api['thumbnail_url'] ) ) {
			return remove_query_arg( 'image_crop_resized', $api['thumbnail_url'] );
		}

		return '';

	}

	/**
	 * Attempts to get the highest resolution thumbnail URL for the given dailymotion video ID.
	 *
	 * @since 1.0.0
	 *
	 * @param string $video_id Vimeo Video ID.
	 * @return string Best resolution URL
	 */
	public function get_dailymotion_thumbnail_url( $video_id ) {

		// Determine video URL.
		$thumbnail_url = 'https://api.dailymotion.com/video/' . $video_id . '?fields=thumbnail_large_url,thumbnail_medium_url,thumbnail_small_url';

		// Get HD image from DailyMotion.
		$image_data = wp_remote_get(
			$thumbnail_url,
			array(
				'timeout' => 10,
			)
		);

		// Check request worked.
		if ( is_wp_error( $image_data ) || ! isset( $image_data['body'] ) ) {
			// Failed - fallback to SD Thumbnail.
			return $sd_url;
		}

		// Returns JSON, so parse.
		$thumbnails = json_decode( $image_data['body'], true );

		if ( isset( $thumbnails['thumbnail_large_url'] ) ) {
			return esc_html( $thumbnails['thumbnail_large_url'] );
		} elseif ( isset( $thumbnails['thumbnail_medium_url'] ) ) {
			return esc_html( $thumbnails['thumbnail_medium_url'] );
		} else {
			// Failed.
			return false;
		}

	}

	/**
	 * Attempts to get the highest resolution thumbnail URL for the given metacafe video ID.
	 *
	 * @since 1.0.0
	 *
	 * @param string $video_id Vimeo Video ID.
	 * @param string $slug Vimeo Video Slug.
	 * @return string Best resolution URL
	 */
	public function get_metacafe_thumbnail_url( $video_id, $slug ) {

		// Determine video URL.
		$prefix = is_ssl() ? 'https' : 'http';
		$url    = $prefix . '://www.metacafe.com/watch/' . $video_id . '/' . $slug;

		// Get page data from metacafe to find mp4.
		$page_data = wp_remote_get(
			$url,
			array(
				'timeout' => 10,
			)
		);

		$doc = new \ DOMDocument();
		$doc->loadHTML( $page_data['body'] );
		$metas = $doc->getElementsByTagName( 'meta' );

		for ( $i = 0; $i < $metas->length; $i++ ) {
			$meta = $metas->item( $i );
			if ( $meta->getAttribute( 'property' ) === 'og:image' ) {
				$thumbnail_url = $meta->getAttribute( 'content' );
			}
		}

		if ( ! $thumbnail_url ) {
			return false;
		}

		// Check request worked.
		if ( filter_var( $thumbnail_url, FILTER_VALIDATE_URL ) === false ) {
			// Failed - fallback to SD Thumbnail.
			return false;
		}

		// Image is a valid YouTube HD thumbnail.
		return $thumbnail_url;

	}

	/**
	 * Attempts to get Instagram thumbnail.
	 *
	 * @since 1.0.0
	 *
	 * @param string $video_id Vimeo Video ID.
	 * @param string $instagram_type Type.
	 * @return string|boolean URI.
	 */
	public function get_instagram_thumbnail_url( $video_id, $instagram_type = 'p' ) {

		// Determine video URL.
		$prefix   = is_ssl() ? 'https' : 'http';
		$base_url = $prefix . '://www.instagram.com/' . $instagram_type . '/' . $video_id;

		// Get HD image from YouTube.
		$page_data = wp_remote_get(
			$base_url,
			array(
				'timeout' => 10,
			)
		);

		$doc = new \ DOMDocument();
		$doc->loadHTML( $page_data['body'] );
		$metas = $doc->getElementsByTagName( 'meta' );

		for ( $i = 0; $i < $metas->length; $i++ ) {
			$meta = $metas->item( $i );
			if ( $meta->getAttribute( 'property' ) === 'og:image' ) {
				$thumbnail_url = $meta->getAttribute( 'content' );
			}
		}

		if ( ! $thumbnail_url ) {
			return false;
		} else {
			return $thumbnail_url;
		}

	}

	/**
	 * Attempts to get Facebook thumbnail.
	 *
	 * @since 1.0.0
	 *
	 * @param string $video_id Vimeo Video ID.
	 * @return string|boolean URI.
	 */
	public function get_facebook_thumbnail_url( $video_id ) {

		$video_id = str_replace( '/', '', $video_id );

		// Determine video URL.
		$prefix   = is_ssl() ? 'https' : 'http';
		$base_url = $prefix . '://graph.facebook.com/' . $video_id . '/picture/';

		return $base_url;

	}

	/**
	 * Attempts to get the highest resolution thumbnail URL for the given twitch video ID.
	 *
	 * @since 1.0.0
	 *
	 * @param string $video_id Vimeo Video ID.
	 * @return string Best resolution URL
	 */
	public function get_twitch_thumbnail_url( $video_id ) {

		// Determine video URL.
		$thumbnail_url = 'https://api.twitch.tv/helix/videos?id=' . $video_id;

		$image_data          = wp_remote_get(
			$thumbnail_url,
			array(
				'headers' => array( 'Client-ID' => 'iz3cdw5gp6malgnae09s0h372mp1mr' ),
			)
		);
		$image_data_response = wp_remote_retrieve_response_code( $image_data );

		// Check request worked.
		if ( 404 === $image_data_response || is_wp_error( $image_data ) || ! isset( $image_data['body'] ) ) {
			// Failed - fallback to SD Thumbnail.
			return false;
		}

		if ( is_wp_error( $image_data ) || ! isset( $image_data['body'] ) ) {
			// Failed - fallback to SD Thumbnail.
			return false;
		}

		// Returns JSON, so parse.
		$thumbnails = json_decode( $image_data['body'], true );

		if ( isset( $thumbnails['data'][0]['thumbnail_url'] ) ) {
			$thumbnail = esc_html( $thumbnails['data'][0]['thumbnail_url'] );
			$thumbnail = str_replace( '%{width}', '640', $thumbnail );
			$thumbnail = str_replace( '%{height}', '480', $thumbnail );
			return $thumbnail;
		} else {
			// Failed.
			return false;
		}

	}

	/**
	 * Attempts to get the highest resolution thumbnail URL for the given twitch video ID.
	 *
	 * @since 1.0.0
	 *
	 * @param string $video_id Vimeo Video ID.
	 * @return string Best resolution URL
	 */
	public function get_videopress_thumbnail_url( $video_id ) {

		// Determine video URL.
		$data_url = 'https://videopress.com/v/' . $video_id;

		// Get page data from VideoPress to find mp4.
		$page_data = wp_remote_get(
			$data_url,
			array(
				'timeout' => 10,
			)
		);

		$doc = new \ DOMDocument();
		$doc->loadHTML( $page_data['body'] );
		$metas = $doc->getElementsByTagName( 'meta' );

		for ( $i = 0; $i < $metas->length; $i++ ) {
			$meta = $metas->item( $i );
			if ( $meta->getAttribute( 'property' ) === 'og:image' ) {
				$thumbnail_url = $meta->getAttribute( 'content' );
			}
		}

		if ( ! $thumbnail_url ) {
			return false;
		}

		// Check request worked.
		if ( filter_var( $thumbnail_url, FILTER_VALIDATE_URL ) === false ) {
			// Failed.
			return false;
		}

		return $thumbnail_url;

	}

	/**
	 * Saves Video-specific options when editing an existing image within the modal window.
	 *
	 * @since 1.1.6
	 *
	 * @param   array $gallery_data   Gallery Data.
	 * @param   array $meta           Meta.
	 * @param   int   $attach_id      Attachment ID.
	 * @param   int   $post_id        Post (Gallery) ID.
	 * @return  array                   Gallery Data
	 */
	public function save_meta( $gallery_data, $meta, $attach_id, $post_id ) {

		$gallery_data['gallery'][ $attach_id ]['video_aspect_ratio'] = ( isset( $meta['video_aspect_ratio'] ) ? sanitize_text_field( $meta['video_aspect_ratio'] ) : '' );
		$gallery_data['gallery'][ $attach_id ]['video_width']        = ( isset( $meta['video_width'] ) ? sanitize_text_field( $meta['video_width'] ) : '' );
		$gallery_data['gallery'][ $attach_id ]['video_height']       = ( isset( $meta['video_height'] ) ? sanitize_text_field( $meta['video_height'] ) : '' );
		$gallery_data['gallery'][ $attach_id ]['video_in_gallery']   = ( isset( $meta['video_in_gallery'] ) ? absint( $meta['video_in_gallery'] ) : '' );
		$gallery_data['gallery'][ $attach_id ]['src']                = ( isset( $meta['src'] ) ? sanitize_text_field( $meta['src'] ) : '' );
		// this is where the final magic happens, need to replace thumbnail.
		if ( isset( $meta['thumbnail_id'] ) && intval( $meta['thumbnail_id'] ) > 0 ) {
			$_wp_attached_file       = get_post_meta( intval( $meta['thumbnail_id'] ), '_wp_attached_file', true );
			$_wp_attachment_metadata = get_post_meta( intval( $meta['thumbnail_id'] ), '_wp_attachment_metadata', true );
			update_post_meta( $attach_id, '_wp_attached_file', $_wp_attached_file );
			update_post_meta( $attach_id, '_wp_attachment_metadata', $_wp_attachment_metadata );
			$gallery_data['gallery'][ $attach_id ]['src'] = ( isset( $meta['thumbnail_url'] ) ? sanitize_text_field( $meta['thumbnail_url'] ) : '' );
		}
		// $common = Envira_Gallery_Common::get_instance();
		// Flush the gallery cache.
		envira_flush_gallery_caches( $post_id );
		return $gallery_data;
	}

	/**
	 * Saves Video-specific options when editing bulk
	 *
	 * @since 1.1.6
	 *
	 * @param   array $gallery_data   Gallery Data.
	 * @param   array $meta           Meta.
	 * @param   int   $attach_id      Attachment ID.
	 * @param   int   $post_id        Post (Gallery) ID.
	 * @return  array                   Gallery Data
	 */
	public function save_bulk( $gallery_data, $meta, $attach_id, $post_id ) {

		$gallery_data['gallery'][ $attach_id ]['video_aspect_ratio'] = ( isset( $meta['video_aspect_ratio'] ) ? sanitize_text_field( $meta['video_aspect_ratio'] ) : $gallery_data['gallery'][ $attach_id ]['video_aspect_ratio'] );

		$gallery_data['gallery'][ $attach_id ]['video_width'] = ( isset( $meta['video_width'] ) ? sanitize_text_field( $meta['video_width'] ) : $gallery_data['gallery'][ $attach_id ]['video_width'] );

		$gallery_data['gallery'][ $attach_id ]['video_height'] = ( isset( $meta['video_height'] ) ? sanitize_text_field( $meta['video_height'] ) : $gallery_data['gallery'][ $attach_id ]['video_height'] );

		$gallery_data['gallery'][ $attach_id ]['video_in_gallery'] = ( isset( $meta['video_in_gallery'] ) ? absint( $meta['video_in_gallery'] ) : $gallery_data['gallery'][ $attach_id ]['video_in_gallery'] );

		$gallery_data['gallery'][ $attach_id ]['src'] = ( isset( $meta['src'] ) ? sanitize_text_field( $meta['src'] ) : $gallery_data['gallery'][ $attach_id ]['src'] );

		return $gallery_data;

	}


}
