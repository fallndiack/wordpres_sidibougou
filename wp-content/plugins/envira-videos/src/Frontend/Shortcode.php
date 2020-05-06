<?php
/**
 * Shortcode class.
 *
 * @since 1.0.0
 *
 * @package Envira_Videos
 * @author  Envira Team
 */

namespace Envira\Videos\Frontend;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode class.
 *
 * @since 1.0.0
 *
 * @package Envira_Videos
 * @author  Envira Team
 */
class Shortcode {

	/**
	 * Holds an array of video gallery item IDs.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $videos;

	/**
	 * Let's us know if it's been inserted so we don't do this more than once on a page
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $fb_script_inserted = false;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Init classes and vars.
		$this->videos = array();
		$this->init();

	}

	/**
	 * Init things.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}

		// Filters.
		add_filter( 'wp_enqueue_scripts', array( $this, 'load_css' ) );

		// CSS.
		add_action( 'envira_gallery_before_output', array( $this, 'gallery_output_css_js' ) );
		add_action( 'envira_link_before_output', array( $this, 'gallery_output_css_js' ) );

		// Gallery.
		add_action( 'envira_images_pre_data', array( $this, 'envira_images_pre_data' ), 10, 2 );
		add_filter( 'envira_gallery_output_before_image', array( $this, 'change_gallery_output_before_image' ), 10, 5 );
		add_filter( 'envira_gallery_output_image', array( $this, 'change_gallery_image' ), 10, 5 );
		add_filter( 'envira_gallery_output_item_data', array( $this, 'change_gallery_link' ), 10, 4 );
		add_filter( 'envira_gallery_output_link_attr', array( $this, 'change_gallery_link_attr' ), 10, 5 );
		add_action( 'envira_gallery_api_lightbox_image_attributes', array( $this, 'add_lightbox_image_attributes' ), 10, 4 );
		add_action( 'envira_gallery_api_before_show', array( $this, 'maybe_resize_lightbox' ) );
		add_filter( 'envira_gallery_create_link', array( $this, 'remove_in_gallery_link' ), 10, 6 );
		add_filter( 'envira_gallery_output_link_attr', array( $this, 'insert_play_icon_thumbnail' ), 10, 5 );

		// Albums.
		add_action( 'envira_albums_before_output', array( $this, 'gallery_output_css_js' ) );
		add_filter( 'envira_albums_output_image', array( $this, 'change_album_image' ), 10, 6 );

		// Third-Party Script Inserts.
		add_action( 'envira_gallery_before_output', array( $this, 'insert_gallery_facebook_script' ), 10 );
		add_action( 'envira_gallery_link_before_output', array( $this, 'insert_gallery_facebook_script' ), 10 );
		add_action( 'envira_albums_before_output', array( $this, 'insert_album_facebook_script' ), 10 );

		// Enqueue WP mediaplayerelement.
		wp_enqueue_script( 'wp-mediaelement' );
		wp_enqueue_style( 'wp-mediaelement' );
	}
	/**
	 * Automatically add the facebook embed script if a facebook video is detected in galleries
	 *
	 * @since 1.0.0
	 *
	 * @param array $gallery_data Gallery Data.
	 * @return null
	 */
	public function insert_gallery_facebook_script( $gallery_data ) {

		// don't insert more than once.
		if ( true === $this->fb_script_inserted ) {
			return;
		}

		$output_fb_script = false;

		// check for facebook videos.
		if ( ! empty( $gallery_data['gallery'] ) ) {
			foreach ( $gallery_data['gallery'] as $gallery ) {
				if ( isset( $gallery['link'] ) && strpos( $gallery['link'], 'facebook/videos' ) !== false ) {
					$output_fb_script = true;
				}
			}
		}

		if ( false === $output_fb_script ) {
			return;
		}

		$this->envira_output_facebook_script();

		$this->fb_script_inserted = true;
	}

	/**
	 * Automatically add the facebook embed script if a facebook video is detected in albums
	 *
	 * @since 1.0.0
	 *
	 * @param array $album_data Album Data.
	 * @return null
	 */
	public function insert_album_facebook_script( $album_data ) {

		// don't insert more than once.
		if ( true === $this->fb_script_inserted ) {
			return;
		}

		$output_fb_script = false;

		// NOTE: there is no GOOD way right now to determine if there's a facebook video in a gallery
		// Therefore the below isn't optimized, but eventually it SHOULD BE
		// check for facebook videos.
		if ( ! empty( $album_data['galleries'] ) ) {
			foreach ( $album_data['galleries'] as $gallery_id => $gallery ) {
				$gallery_data = envira_get_gallery_data( $gallery_id );
				if ( ! empty( $gallery_data['gallery'] ) ) {
					foreach ( $gallery_data['gallery'] as $item_id => $item ) {
						if ( strpos( $item['link'], 'facebook/videos' ) !== false ) {
							$output_fb_script = true;
						}
					}
				}
			}
		}

		if ( false === $output_fb_script ) {
			return;
		}

		$this->envira_output_facebook_script();

		$this->fb_script_inserted = true;

	}

	/**
	 * Facebook Script
	 *
	 * @since 1.0.0
	 *
	 * @return null
	 */
	public function envira_output_facebook_script() {

		if ( is_admin() ) {
			return;
		}
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST // (#1)
		|| isset( $_GET['rest_route'] ) // (#2) // @codingStandardsIgnoreLine
			&& strpos( trim( sanitize_text_field( wp_unslash( $_GET['rest_route'] ) ), '\\/' ), $prefix, 0 ) === 0 ) { // @codingStandardsIgnoreLine
				return;
		}
		?>
		<script type="text/javascript">
			(function(d, s, id) {
				var js, fjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) return;
				js = d.createElement(s); js.id = id;
				js.src = "https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.6";
				fjs.parentNode.insertBefore(js, fjs);
				}(document, 'script', 'facebook-jssdk'));
		</script>

		<?php

	}

	/**
	 * Pre data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Gallery Data.
	 * @param int   $gallery_id Gallery ID.
	 * @return null
	 */
	public function envira_images_pre_data( $data, $gallery_id ) {

		if ( empty( $data ) || empty( $data['gallery'] ) ) {
			return $data;
		}

		foreach ( $data['gallery'] as $image_id => $image_data ) {
			// try to ID video URLs.
			$parsed = isset( $data['gallery'][ $image_id ]['src'] ) ? wp_parse_url( $data['gallery'][ $image_id ]['src'] ) : false;
			$sites  = array( 'youtube', 'vimeo' ); // might need some work here - this is for gallery links.
			if ( isset( $parsed['host'] ) && count( array_intersect( array_map( 'strtolower', explode( ' ', $parsed['host'] ) ), $sites ) ) > 0 ) {
				$data['gallery'][ $image_id ]['src'] = $data['gallery'][ $image_id ]['link'];
			}
		}

		return $data;
	}

	/**
	 * Registers Addon CSS if one or more Videos are present
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Gallery Data.
	 * @return void
	 */
	public function load_css( $data ) {

		$version = ( defined( 'ENVIRA_DEBUG' ) && 'true' === ENVIRA_DEBUG ) ? $version = time() . '-' . ENVIRA_VIDEOS_VERSION : ENVIRA_VIDEOS_VERSION;

		wp_register_style( ENVIRA_VIDEOS_SLUG . '-style', plugins_url( 'assets/css/videos-style.css', ENVIRA_VIDEOS_FILE ), array(), $version );
		wp_register_script( ENVIRA_VIDEOS_SLUG . '-script', plugins_url( 'assets/js/min/envira-videos-min.js', ENVIRA_VIDEOS_FILE ), array( 'jquery' ), $version, true );

	}

	/**
	 * Enqueue CSS and JS if Social Sharing is enabled
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Gallery Data.
	 */
	public function gallery_output_css_js( $data ) {

		// Enqueue CSS.
		wp_enqueue_style( ENVIRA_VIDEOS_SLUG . '-style' );

		// Enqueue JS.
		wp_enqueue_script( ENVIRA_VIDEOS_SLUG . '-script' );

	}

	/**
	 * Change output.
	 *
	 * @since 1.0.0
	 * @param   string $output     HTML Output.
	 * @param   int    $id         Item ID.
	 * @param   array  $item       Image Data.
	 * @param   array  $data       Gallery Config.
	 * @param   int    $i          Index.
	 * @return  string              HTML Output.
	 */
	public function change_gallery_output_before_image( $output, $id, $item, $data, $i ) {

		if ( isset( $item['video'] ) ) {
			if ( 0 === $data['config']['columns'] ) {
				$output = str_replace( 'envira-gallery-link', 'envira-gallery-link envira-gallery-video envira-video-play-container', $output );
			} else {
				$output = str_replace( 'envira-gallery-link', 'envira-gallery-link envira-gallery-video', $output );
			}
		}

		return $output;

	}

	/**
	 * Remove link.
	 *
	 * @since 1.0.0
	 * @param   boolean $create_link     HTML Output.
	 * @param   array   $data         Item ID.
	 * @param   array   $id       Image Data.
	 * @param   int     $item       Item Config.
	 * @param   int     $i          Index.
	 * @param   boolean $is_mobile          Mobile.
	 * @return  string              HTML Output.
	 */
	public function remove_in_gallery_link( $create_link, $data, $id, $item, $i, $is_mobile ) {
		if ( isset( $item['video_in_gallery'] ) && 1 === $item['video_in_gallery'] ) {
			return false;
		} else {
			return $create_link;
		}
	}

	/**
	 * 1. Changes the image output to a video output if:
	 * - The item is a video,
	 * - The item has 'Display Video in Gallery' enabled.
	 *
	 * 2. Displays a play icon over the image if:
	 * - The item is a video,
	 * - The item does not have 'Display Video in Gallery' enabled
	 * - The gallery has 'Display Play Icon over Gallery Image' enabled
	 *
	 * @since 1.1.6
	 *
	 * @param   string $output     HTML Output.
	 * @param   int    $id         Item ID.
	 * @param   array  $item       Image Data.
	 * @param   array  $data       Gallery Config.
	 * @param   int    $i          Index.
	 * @return  string              HTML Output
	 */
	public function change_gallery_image( $output, $id, $item, $data, $i ) {

		// If the item does not have 'Display Video in Gallery' enabled, and the gallery has
		// 'Display Play Icon over Gallery Image' enabled, append the icon to the markup.
		if ( isset( $item['video'] ) && ( ! isset( $item['video_in_gallery'] ) || ! $item['video_in_gallery'] ) ) {
			if ( envira_get_config( 'videos_play_icon', $data ) ) {
				// Append the play icon and return.
				if ( 0 === intval( $data['config']['columns'] ) || ( $data['config']['columns'] > 0 && ! $data['config']['lazy_loading'] ) ) {
					$output .= '<div class="envira-video-play-icon">' . __( 'Play', 'envira-videos' ) . '</div>';
				} else {
					$output = str_replace( '</div>', '<div class="envira-video-play-icon">' . __( 'Play', 'envira-videos' ) . '</div></div>', $output );
				}
			}

			return $output;
		}

		// Check if the URL is a video and a supported video type.
		$result = envira_video_get_video_type( $item['link'], $item, $data );
		if ( ! $result ) {
			return $output;
		}

		// Define Video HTML.
		$html = '';

		// Enqueue scripts and generate the necessary HTML based on the video type.
		switch ( $result['type'] ) {
			// return NON SUPPORTED embed video types, so they still show up in the gallery even if the checkbox is checked.
			case 'twitch':
			case 'dailymotion':
			case 'videopress':
			case 'metacafe':
				return $output;
			case 'youtube':
				wp_enqueue_script( ENVIRA_SLUG . '-' . $result['type'], 'https://www.youtube.com/iframe_api', array(), ENVIRA_VERSION, true );
				$querystring_options = $result['args'] ? http_build_query( $result['args'], '', '&' ) : false;
				$html                = '<a href="#" class="envira-gallery-' . $data['id'] . ' envira-gallery-link envira-gallery-video"></a><iframe enablejsapi="1" src="https://youtube.com/embed/' . $result['video_id'] . '?rel=0&' . $querystring_options . '" class="envira_youtube_embed" frameborder="0" allowfullscreen></iframe>';
				break;
			case 'vimeo':
				wp_enqueue_script( ENVIRA_SLUG . '-' . $result['type'], '//player.vimeo.com/api/player.js', array(), ENVIRA_VERSION, true );
				$html = '<a href="#" class="envira-gallery-' . $data['id'] . ' envira-gallery-link envira-gallery-video"></a><div class="envira-vimeo-embed-container"><iframe src="//player.vimeo.com/video/' . $result['video_id'] . '" class="envira_vimeo_embed" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div>';
				break;
			case 'facebook':
				$html = '<a href="#" class="envira-gallery-' . $data['id'] . ' envira-gallery-link envira-gallery-video"></a><div class="envira-facebook-responsive">' . apply_filters( 'envira_videos_facebook_gallery_iframe_embed', '<iframe src="https://www.facebook.com/plugins/video.php?href=' . rawurlencode( $item['link'] ) . '&controls=true&autoplay=false" class="envira_facebook_embed" scrolling="no" frameborder="0" allowTransparency="true" allowfullscreen mozallowfullscreen webkitallowfullscreen oallowfullscreen msallowfullscreen></iframe>', $item, $result ) . '</div>';
				break;
			case 'instagram':
				$html = '<a href="#" class="envira-gallery-' . $data['id'] . ' envira-gallery-link envira-gallery-video"></a><div class="envira-instagram-responsive">' . apply_filters( 'envira_videos_facebook_gallery_iframe_embed', '<video controls><source src="' . $item['link'] . '"></source></video>', $item, $result ) . '</div>';
				break;
			case 'wistia':
				wp_enqueue_script( ENVIRA_SLUG . '-' . $result['type'], '//fast.wistia.net/assets/external/iframe-api-v1.js', array(), ENVIRA_VERSION, true );
				$base_video_iframe_url = '//fast.wistia.net/embed/iframe/' . $result['video_id'];
				$querystring_options   = '?' . rawurlencode( 'videoFoam=true&playbar=true&embedType=iframe' );
				$html                  = apply_filters( 'envira_videos_wistia_gallery_iframe_embed', '<a href="#" class="envira-gallery-' . $data['id'] . ' envira-gallery-link envira-gallery-video"></a><iframe src="' . $base_video_iframe_url . $querystring_options . '" allowtransparency="true" frameborder="0" scrolling="no" class="envira_wistia_embed" name="envira_wistia_embed" allowfullscreen mozallowfullscreen webkitallowfullscreen oallowfullscreen msallowfullscreen></iframe>', $item, $result );
				break;
			default:
				// Check if file type matches one of our self hosted file types.
				$file_types = envira_video_get_self_hosted_supported_filetypes();
				if ( in_array( $result['type'], $file_types, true ) ) {
					// Self hosted video.
					// Enqueue WP MediaElement JS.
					wp_enqueue_script( 'wp-mediaelement' );
					wp_enqueue_style( 'wp-mediaelement' );

					// Get file extension, to define the source type.
					$ext          = pathinfo( $item['link'], PATHINFO_EXTENSION );
					$content_type = '';
					switch ( $ext ) {
						case 'mp4':
							$content_type = 'video/mp4';
							break;
						case 'ogv':
							$content_type = 'video/ogg';
							break;
						case 'ogg':
							$content_type = 'application/ogg';
							break;
						case 'webm':
							$content_type = 'video/webm';
							break;
						default:
							$content_type = apply_filters( 'envira_videos_get_content_type', $content_type, $item, $ext );
							break;
					}

					$html = '<a href="#" class="envira-gallery-' . $data['id'] . ' envira-gallery-link envira-gallery-video"></a><video controls class="envira-video" preload="metadata" poster="' . $item['src'] . '"><source type="' . $content_type . '" src="' . $item['link'] . '" /></video>';

				} else {
					// Allow devs and custom addons to enqueue any scripts they need for their custom video type.
					do_action( 'envira_videos_enqueue_scripts' );
					$html = apply_filters( 'envira_videos_gallery_embed', $html, $result, $output, $id, $item, $data, $i );
				}
				break;
		}

		// Return our HTML to embed the video directly into the gallery.
		return $html;

	}

	/**
	 * 1. Changes the image output to a video output if:
	 * - The item is a video,
	 * - The item has 'Display Video in Gallery' enabled.
	 *
	 * 2. Displays a play icon over the image if:
	 * - The item is a video,
	 * - The item does not have 'Display Video in Gallery' enabled
	 * - The gallery has 'Display Play Icon over Gallery Image' enabled
	 *
	 * @since 1.1.6
	 *
	 * @param   string $output     HTML Output.
	 * @param   int    $id         Item ID.
	 * @param   array  $item       Image Data.
	 * @param   array  $data       Gallery Config.
	 * @param   int    $i          Index.
	 * @param   array  $album      Album.
	 * @return  string              HTML Output
	 */
	public function change_album_image( $output, $id, $item, $data, $i, $album ) {

		// Get galleries in this album, then determine if there are videos in those galleries.
		foreach ( $data['galleryIDs'] as $gallery_id ) {

			$gallery = envira_get_gallery( $gallery_id );

			if ( empty( $gallery['gallery'] ) ) {
				continue;
			}

			foreach ( $gallery['gallery'] as $gallery_item ) {
				// If the item does not have 'Display Video in Gallery' enabled, and the gallery has
				// 'Display Play Icon over Gallery Image' enabled, append the icon to the markup
				// if ( isset( $item['video'] ) && ( ! isset( $item['video_in_gallery'] ) || ! $item['video_in_gallery'] ) ) {
				// if ( ! envira_get_config( 'videos_play_icon', $data ) ) {
				// return $output;
				// }
				// Append the play icon and return
				// $output .= '<div class="envira-video-play-icon">' . __( 'Play', 'envira-videos' ) . '</div>';
				// return $output;
				// }
				// Check if the URL is a video and a supported video type.
				$result = envira_video_get_video_type( $gallery_item['link'], $gallery_item, $gallery );
				if ( ! $result ) {
					return $output;
				}

				// Define Video HTML.
				$html = '';

				// Enqueue scripts and generate the necessary HTML based on the video type.
				switch ( $result['type'] ) {
					case 'youtube':
						wp_enqueue_script( ENVIRA_SLUG . '-' . $result['type'], 'https://www.youtube.com/iframe_api', array(), ENVIRA_VERSION, true );
						$html = '<iframe src="https://youtube.com/embed/' . $result['video_id'] . '" frameborder="0" allowfullscreen></iframe>';
						break;
					case 'vimeo':
						wp_enqueue_script( ENVIRA_SLUG . '-' . $result['type'], '//player.vimeo.com/api/player.js', array(), ENVIRA_VERSION, true );
						$html = '<iframe src="//player.vimeo.com/video/' . $result['video_id'] . '" frameborder="0" allowfullscreen></iframe>';
						break;
					case 'wistia':
						wp_enqueue_script( ENVIRA_SLUG . '-' . $result['type'], '//fast.wistia.net/static/embed_shepherd-v1.js', array(), ENVIRA_VERSION, true );
						break;
					default:
						// Check if file type matches one of our self hosted file types.
						$file_types = envira_video_get_self_hosted_supported_filetypes();
						if ( in_array( $result['type'], $file_types, true ) ) {
							// Self hosted video
							// Enqueue WP MediaElement JS.
							wp_enqueue_script( 'wp-mediaelement' );
							wp_enqueue_style( 'wp-mediaelement' );
						}
						break;
				}
			}
		}

		// Return our HTML to embed the video directly into the gallery.
		return $output;

	}

	/**
	 * Checks if the gallery item is a video, and if so changes the URL to the embed URL.
	 * This allows the video to load in the Lightbox without any XSS restrictions
	 *
	 * @since 1.0.0
	 *
	 * @param array $item Gallery Item.
	 * @param int   $id Gallery Item ID.
	 * @param array $data Gallery Data.
	 * @param int   $i Index.
	 * @return array Gallery Item
	 */
	public function change_gallery_link( $item, $id, $data, $i ) {

		// Check if link in the item exists.
		if ( empty( $item['link'] ) ) {
			return $item;
		}

		// Check if the URL is a video and a supported video type.
		$result = envira_video_get_video_type( $item['link'], $item, $data );

		if ( ! $result ) {
			return $item;
		}

		// If the video is set to display in the gallery, we won't be displaying it in the lightbox
		// so no need to change the link.
		if ( isset( $item['video_in_gallery'] ) && $item['video_in_gallery'] ) {
			if ( 'instagram' === $result['type'] ) {
				$item['link'] = $result['embed_url'];
			}
			return $item;
		}

		// Change the URL to the embed URL, so it works in the Lightbox.
		$item['link'] = $result['embed_url'];

		// Enqueue necessary script based on the video type.
		switch ( $result['type'] ) {
			case 'youtube':
				wp_enqueue_script( ENVIRA_SLUG . '-' . $result['type'], 'https://www.youtube.com/iframe_api', array(), ENVIRA_VERSION, true );
				break;
			case 'vimeo':
				wp_enqueue_script( ENVIRA_SLUG . '-' . $result['type'], '//player.vimeo.com/api/player.js', array(), ENVIRA_VERSION, true );
				break;
			case 'wistia':
				wp_enqueue_script( ENVIRA_SLUG . '-' . $result['type'], '//fast.wistia.net/static/embed_shepherd-v1.js', array(), ENVIRA_VERSION, true );
				break;
			default:
				// Check if file type matches one of our self hosted file types.
				$file_types = envira_video_get_self_hosted_supported_filetypes();
				if ( in_array( $result['type'], $file_types, true ) ) {
					// Self hosted video
					// Enqueue WP MediaElement JS.
					wp_enqueue_script( 'wp-mediaelement' );
					wp_enqueue_style( 'wp-mediaelement' );
				} else {
					// Allow devs and custom addons to enqueue any scripts they need for their custom video type.
					do_action( 'envira_videos_enqueue_scripts' );
				}
				break;
		}

		// Add this video to the array of video links that need the data-envirabox-type attribute adding later on.
		$this->videos[] = $item['link'];

		// Add the video result to the item, so we can reference it later on.
		$item['video'] = $result;

		return $item;

	}

	/**
	 * Checks if the gallery item is a video, and if so adds some data- attributes to the link
	 * to tell the Lightbox to display a video instead of an image.
	 *
	 * @since 1.0.0
	 *
	 * @param string $atts Link Attributes.
	 * @param int    $id Gallery ID.
	 * @param array  $item Gallery Item.
	 * @param array  $data Gallery Data.
	 * @param int    $i Index.
	 * @return string Link Attributes
	 */
	public function change_gallery_link_attr( $atts, $id, $item, $data, $i ) {

		global $wpdb;

		// Check if this item's URL matches one in the videos array.
		if ( ! isset( $item['link'] ) || ! in_array( $item['link'], $this->videos, true ) ) {
			// Nothing to do here.
			return $atts;
		}

		$width  = ! empty( $item['video_width'] ) ? $item['video_width'] : '';
		$height = ! empty( $item['video_height'] ) ? $item['video_height'] : '';

		// Check if link is a URL or HTML markup.
		if ( filter_var( 'http:' . str_replace( 'http:', '', $item['link'] ), FILTER_VALIDATE_URL ) || filter_var( 'https:' . str_replace( 'https:', '', $item['link'] ), FILTER_VALIDATE_URL ) ) {
			// Add data- attributes so we load the video in the lightbox.
			$atts .= ' data-envirabox-type="iframe" data-video-width="' . $width . '" data-video-height="' . $height . '"';
		} else {
			// Self-hosted video
			// We try to get the video's width and height, so we can tell the Lightbox the maximum size to display
			// This fixes a lot of issues with videos wrongly displaying with borders / scrollbars
			// Sadly, it's not perfect - videos outside the Media Library can't be scanned, so we start with some defaults
			// Check if the video exists in the Media Library.
			$attachment = $wpdb->get_col( $wpdb->prepare( 'SELECT ID FROM ' . $wpdb->posts . ' WHERE guid=%s;', "'" . $item['link'] . "'" ) ); // @codingStandardsIgnoreLine

			if ( is_array( $attachment ) && ! empty( $attachment ) ) {
				// Attachment found.
				$attachment_id = absint( $attachment[0] );

				// Get metadata, which tells us the width and height of the video.
				$video_metadata = wp_get_attachment_metadata( $attachment_id );
				if ( is_array( $video_metadata ) ) {
					// If width and height exist, use those values instead.
					if ( isset( $video_metadata['width'] ) ) {
						$width = $video_metadata['width'];
					}
					if ( isset( $video_metadata['height'] ) ) {
						$height = $video_metadata['height'];
					}
				}
			}

			// Add data-attributes so we load the video in the lightbox with the correct dimensions.
			$atts .= ' data-envirabox-type="html" data-video-width="' . $width . '" data-video-height="' . $height . '"';

		}

		// Append the video-aspect-ratio data key/value.
		$atts .= ' data-video-aspect-ratio="' . ( isset( $item['video_aspect_ratio'] ) ? $item['video_aspect_ratio'] : '' ) . '"';

		return $atts;

	}

	/**
	 * When images are defined in a JS array during Lightbox initialization (i.e. we're using the Pagination Addon),
	 * we need to add additional image key/value pairs to tell us later on whether a video is 16:9 aspect ratio
	 * or not.
	 *
	 * @since 1.2.1
	 *
	 * @param   array $image              Image Metadata (source, title, caption etc).
	 * @param   int   $image_id           Image ID.
	 * @param   array $lightbox_images    Lightbox Images.
	 * @param   array $data               Gallery Config.
	 */
	public function add_lightbox_image_attributes( $image, $image_id, $lightbox_images, $data ) {

		?>
		, video_aspect_ratio: '<?php echo ( isset( $image['video_aspect_ratio'] ) ? esc_html( $image['video_aspect_ratio'] ) : '' ); ?>'
		<?php

		// Check if this item's URL matches one in the videos array.
		if ( ! in_array( $image['link'], $this->videos, true ) ) {
			// Nothing to do here.
			return;
		}

		// Check if link is a URL or HTML markup.
		if ( filter_var( 'http:' . str_replace( 'http:', '', $image['link'] ), FILTER_VALIDATE_URL ) || filter_var( 'https:' . str_replace( 'https:', '', $image['link'] ), FILTER_VALIDATE_URL ) ) {
			?>
			, type: 'iframe',
			href: '<?php echo esc_url( html_entity_decode( $image['link'] ) ); ?>'
			<?php
		}

	}

	/**
	 * If the element we're loading has the 16:9 flag, set the lightbox width and height to that ratio
	 *
	 * @since 1.2.1
	 *
	 * @param   array $data   Gallery Data.
	 */
	public function maybe_resize_lightbox( $data ) {

		?>
		var video_aspect_ratio;
		if ( typeof this.element === 'undefined' ) {
			if ( this.group[ this.index ].video_aspect_ratio !== 'undefined' ) {
				video_aspect_ratio = this.group[ this.index ].video_aspect_ratio;
			}
			else {
				video_aspect_ratio = '';
			}
		} else {
			video_aspect_ratio = this.element.data( 'video-aspect-ratio' );
		}
		if ( typeof video_aspect_ratio !== 'undefined' && video_aspect_ratio == '16:9' ) {
			this.width = 960;
			this.height = 540;
		}
		<?php

	}

	/**
	 * Checks if the Album Gallery item is a video, and if so changes the URL to the embed URL.
	 * This allows the video to load in the Lightbox without any XSS restrictions
	 *
	 * @since 1.0.4
	 *
	 * @param array $item       Gallery Item.
	 * @param int   $gallery    Gallery Data.
	 * @param int   $id         Gallery ID.
	 * @param array $data       Album Data.
	 * @return array            Gallery Item
	 */
	public function album_change_gallery_link( $item, $gallery, $id, $data ) {

		// Check if the URL is a video and a supported video type.
		$result = envira_video_get_video_type( $item['link'], $item, $gallery );
		if ( ! $result ) {
			return $item;
		}

		// Change the URL to the embed URL, so it works in the Lightbox.
		$item['src'] = $result['embed_url'];

		// Enqueue necessary script based on the video type.
		switch ( $result['type'] ) {
			case 'youtube':
				wp_enqueue_script( ENVIRA_SLUG . '-' . $result['type'], 'https://www.youtube.com/iframe_api', array(), ENVIRA_VERSION, true );
				break;
			case 'vimeo':
				wp_enqueue_script( ENVIRA_SLUG . '-' . $result['type'], '//player.vimeo.com/api/player.js', array(), ENVIRA_VERSION, true );
				break;
			case 'wistia':
				wp_enqueue_script( ENVIRA_SLUG . '-' . $result['type'], '//fast.wistia.net/static/embed_shepherd-v1.js', array(), ENVIRA_VERSION, true );
				break;
			default:
				// Check if file type matches one of our self hosted file types.
				$file_types = envira_video_get_self_hosted_supported_filetypes();
				if ( in_array( $result['type'], $file_types, true ) ) {
					// Self hosted video
					// Enqueue WP MediaElement JS.
					wp_enqueue_script( 'wp-mediaelement' );
					wp_enqueue_style( 'wp-mediaelement' );
				} else {
					// Allow devs and custom addons to enqueue any scripts they need for their custom video type.
					do_action( 'envira_videos_enqueue_scripts' );
				}
				break;
		}

		// Add this video to the array of video links that need the data-envirabox-type attribute adding later on.
		$this->videos[] = $item['link'];

		return $item;

	}

	/**
	 * Sets type = iframe on a gallery image if its a video
	 *
	 * @since 1.0.4
	 *
	 * @param string $attr_html  HTML.
	 * @param int    $id         Gallery ID.
	 * @param array  $item       Gallery Item.
	 * @param array  $data       Gallery Data.
	 * @param int    $i          Counter.
	 * @return string            HTML
	 */
	public function insert_play_icon_thumbnail( $attr_html, $id, $item, $data, $i ) {

		if ( '1' === envira_get_config( 'videos_play_icon_thumbnails', $data ) ) {
			$attr_html .= apply_filters( 'envira_videos_play_icon_thumbnail_html', ' data-envira-thumbnail-play-icon="1" ', $id, $item, $data );
		}

		return $attr_html;

	}

	/**
	 * Sets type = iframe on a gallery image if its a video
	 *
	 * @since 1.0.4
	 *
	 * @param array $item       Gallery Item.
	 * @param int   $gallery    Gallery Data.
	 * @param int   $id         Gallery ID.
	 * @param array $data       Album Data.
	 * @return array            Gallery Item
	 */
	public function album_set_gallery_type( $item, $gallery, $id, $data ) {

		// Check if the URL is a video and a supported video type.
		$result = envira_video_get_video_type( $item['link'], $item, $gallery );
		if ( ! $result ) {
			return;
		}

		// Is a video, so force iframe.
		?>
		, type: 'iframe'
		<?php

	}

}
