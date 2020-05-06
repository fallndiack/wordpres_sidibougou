<?php
/**
 * Shortcode class.
 *
 * @since 1.0.0
 *
 * @package Envira_Videos
 * @author  Envira Team
 */

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
class Envira_Videos_Shortcode {

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
	 * Holds an array of video gallery item IDs.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $videos;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Init classes and vars.
		$this->base   = Envira_Videos::get_instance();
		$this->common = Envira_Videos_Common::get_instance();
		$this->videos = array();

		// Link.
		add_action( 'envira_link_before_output', array( $this, 'gallery_output_css_js' ) );

		// Gallery.
		add_action( 'envira_gallery_before_output', array( $this, 'gallery_output_css_js' ) );

		add_filter( 'envira_gallery_output_before_image', array( $this, 'change_gallery_output_before_image' ), 10, 5 );
		add_filter( 'envira_gallery_output_image', array( $this, 'change_gallery_image' ), 10, 5 );
		add_filter( 'envira_gallery_output_item_data', array( $this, 'change_gallery_link' ), 10, 4 );
		add_filter( 'envira_gallery_output_link_attr', array( $this, 'change_gallery_link_attr' ), 10, 5 );
		add_filter( 'envira_gallery_create_link', array( $this, 'remove_in_gallery_link' ), 10, 6 );

		// Albums.
		add_action( 'envira_albums_before_output', array( $this, 'gallery_output_css_js' ) );
		add_filter( 'envira_albums_output_image', array( $this, 'change_album_image' ), 10, 6 );

		// Enqueue WP mediaplayerelement.
		if ( apply_filters( 'envira_unqueue_mediaelement', true ) ) {
			wp_enqueue_script( 'wp-mediaelement' );
			wp_enqueue_style( 'wp-mediaelement' );
		}

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
		wp_enqueue_style( $this->base->plugin_slug . '-style' );

	}

	/**
	 * Output before image.
	 *
	 * @since 1.0.0
	 *
	 * @param   string $output     HTML Output.
	 * @param   array  $id       Image Data.
	 * @param   array  $item       Item Data.
	 * @param   int    $data         Item ID.
	 * @param   int    $i          Index.
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
	 * Remove In Gallery Link.
	 *
	 * @since 1.0.0
	 *
	 * @param   string $create_link     HTML Output.
	 * @param   int    $data         Item ID.
	 * @param   array  $id       Image Data.
	 * @param   array  $item       Item Data.
	 * @param   int    $i          Index.
	 * @param   int    $is_mobile          Mobile.
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
			if ( Envira_Gallery_Shortcode::get_instance()->get_config( 'videos_play_icon', $data ) ) {
				// Append the play icon and return.
				if ( 0 === $data['config']['columns'] || ( $data['config']['columns'] > 0 && ! $data['config']['lazy_loading'] ) ) {
					$output .= '<div class="envira-video-play-icon">' . __( 'Play', 'envira-videos' ) . '</div>';
				} else {
					$output = str_replace( '</div>', '<div class="envira-video-play-icon">' . __( 'Play', 'envira-videos' ) . '</div></div>', $output );
				}
			}

			return $output;
		}

		// Check if the URL is a video and a supported video type.
		$result = $this->common->get_video_type( $item['link'], $item, $data );
		if ( ! $result ) {
			return $output;
		}

		// Define Video HTML.
		$html = '';

		// Enqueue scripts and generate the necessary HTML based on the video type.
		switch ( $result['type'] ) {
			case 'youtube':
				wp_enqueue_script( $this->base->plugin_slug . '-' . $result['type'], 'https://www.youtube.com/iframe_api', array(), $this->base->version, true );
				$html = '<iframe src="https://youtube.com/embed/' . $result['video_id'] . '?rel=0" frameborder="0" allowfullscreen></iframe>';
				break;
			case 'vimeo':
				wp_enqueue_script( $this->base->plugin_slug . '-' . $result['type'], '//secure-a.vimeocdn.com/js/froogaloop2.min.js', array(), $this->base->version, true );
				$html = '<div class="envira-vimeo-embed-container"><iframe src="//player.vimeo.com/video/' . $result['video_id'] . '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div>';
				break;
			case 'wistia':
				wp_enqueue_script( $this->base->plugin_slug . '-' . $result['type'], '//fast.wistia.net/static/embed_shepherd-v1.js', array(), $this->base->version, true );
				break;
			default:
				// Check if file type matches one of our self hosted file types.
				$file_types = $this->common->get_self_hosted_supported_filetypes();
				if ( in_array( $result['type'], $file_types, true ) ) {
					// Self hosted video
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

					$html = '<video controls class="envira-video" preload="metadata" poster="' . $item['src'] . '"><source type="' . $content_type . '" src="' . $item['link'] . '" /></video>';

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
	 * @param   array  $album      Album Data.
	 * @return  string              HTML Output
	 */
	public function change_album_image( $output, $id, $item, $data, $i, $album ) {

		// Get galleries in this album, then determine if there are videos in those galleries.
		foreach ( $data['galleryIDs'] as $gallery_id ) {

			$gallery = Envira_Gallery::get_instance()->get_gallery( $gallery_id );

			if ( empty( $gallery['gallery'] ) ) {
				continue;
			}

			foreach ( $gallery['gallery'] as $gallery_item ) {

				// Check if the URL is a video and a supported video type.
				$result = $this->common->get_video_type( $gallery_item['link'], $gallery_item, $gallery );
				if ( ! $result ) {
					return $output;
				}

				// Define Video HTML.
				$html = '';

				// Enqueue scripts and generate the necessary HTML based on the video type.
				switch ( $result['type'] ) {
					case 'youtube':
						wp_enqueue_script( $this->base->plugin_slug . '-' . $result['type'], 'https://www.youtube.com/iframe_api', array(), $this->base->version, true );
						$html = '<iframe src="https://youtube.com/embed/' . $result['video_id'] . '" frameborder="0" allowfullscreen></iframe>';
						break;
					case 'vimeo':
						wp_enqueue_script( $this->base->plugin_slug . '-' . $result['type'], '//secure-a.vimeocdn.com/js/froogaloop2.min.js', array(), $this->base->version, true );
						$html = '<iframe src="//player.vimeo.com/video/' . $result['video_id'] . '" frameborder="0" allowfullscreen></iframe>';
						break;
					case 'wistia':
						wp_enqueue_script( $this->base->plugin_slug . '-' . $result['type'], '//fast.wistia.net/static/embed_shepherd-v1.js', array(), $this->base->version, true );
						break;
					default:
						// Check if file type matches one of our self hosted file types.
						$file_types = $this->common->get_self_hosted_supported_filetypes();
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

		// Check if the URL is a video and a supported video type.
		$result = $this->common->get_video_type( $item['link'], $item, $data );
		if ( ! $result ) {
			return $item;
		}

		// If the video is set to display in the gallery, we won't be displaying it in the lightbox
		// so no need to change the link.
		if ( isset( $item['video_in_gallery'] ) && $item['video_in_gallery'] ) {
			return $item;
		}

		// Change the URL to the embed URL, so it works in the Lightbox.
		$item['link'] = $result['embed_url'];

		// Enqueue necessary script based on the video type.
		switch ( $result['type'] ) {
			case 'youtube':
				wp_enqueue_script( $this->base->plugin_slug . '-' . $result['type'], 'https://www.youtube.com/iframe_api', array(), $this->base->version, true );
				break;
			case 'vimeo':
				wp_enqueue_script( $this->base->plugin_slug . '-' . $result['type'], '//secure-a.vimeocdn.com/js/froogaloop2.min.js', array(), $this->base->version, true );
				break;
			case 'wistia':
				wp_enqueue_script( $this->base->plugin_slug . '-' . $result['type'], '//fast.wistia.net/static/embed_shepherd-v1.js', array(), $this->base->version, true );
				break;
			default:
				// Check if file type matches one of our self hosted file types.
				$file_types = $this->common->get_self_hosted_supported_filetypes();
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

		$width  = ! empty( $item['video_width'] ) ? $item['video_width'] : 1280;
		$height = ! empty( $item['video_height'] ) ? $item['video_height'] : 720;

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
			$attachment = $wpdb->get_col( $wpdb->prepare( 'SELECT ID FROM ' . $wpdb->posts . " WHERE guid='%s';", $item['link'] ) ); // @codingStandardsIgnoreLine

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
		$result = $this->common->get_video_type( $item['link'], $item, $gallery );
		if ( ! $result ) {
			return $item;
		}

		// Change the URL to the embed URL, so it works in the Lightbox.
		$item['src'] = $result['embed_url'];

		// Enqueue necessary script based on the video type.
		switch ( $result['type'] ) {
			case 'youtube':
				wp_enqueue_script( $this->base->plugin_slug . '-' . $result['type'], 'https://www.youtube.com/iframe_api', array(), $this->base->version, true );
				break;
			case 'vimeo':
				wp_enqueue_script( $this->base->plugin_slug . '-' . $result['type'], '//secure-a.vimeocdn.com/js/froogaloop2.min.js', array(), $this->base->version, true );
				break;
			case 'wistia':
				wp_enqueue_script( $this->base->plugin_slug . '-' . $result['type'], '//fast.wistia.net/static/embed_shepherd-v1.js', array(), $this->base->version, true );
				break;
			default:
				// Check if file type matches one of our self hosted file types.
				$file_types = $this->common->get_self_hosted_supported_filetypes();
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
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Videos_Shortcode object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Videos_Shortcode ) ) {
			self::$instance = new Envira_Videos_Shortcode();
		}

		return self::$instance;

	}

}

// Load the Shortcode class.
$envira_videos_shortcode = Envira_Videos_Shortcode::get_instance();
