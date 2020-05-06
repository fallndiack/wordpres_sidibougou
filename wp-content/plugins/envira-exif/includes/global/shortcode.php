<?php
/**
 * Shortcode class.
 *
 * @since 1.0.0
 *
 * @package Envira_Featured_Content
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
 * @package Envira_Pagination
 * @author  Envira Team
 */
class Envira_Exif_Shortcode {

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
	 * Holds gallery IDs for init firing checks.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $enabled = array();

	/**
	 * Allowed HTML
	 *
	 * @var mixed
	 * @access public
	 */
	public $wp_kses_allowed_html = array(
		'a'      => array(
			'href'                => array(),
			'target'              => array(),
			'class'               => array(),
			'title'               => array(),
			'data-status'         => array(),
			'data-envira-tooltip' => array(),
			'data-id'             => array(),
		),
		'br'     => array(),
		'img'    => array(
			'src'   => array(),
			'class' => array(),
			'alt'   => array(),
		),
		'div'    => array(
			'class' => array(),
		),
		'li'     => array(
			'id'                              => array(),
			'class'                           => array(),
			'data-envira-gallery-image'       => array(),
			'data-envira-gallery-image-model' => array(),
		),
		'em'     => array(),
		'span'   => array(
			'class' => array(),
		),
		'strong' => array(),
	);

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Load the base class object.
		$this->base = Envira_Exif::get_instance();

		// Register CSS.
		wp_register_style( $this->base->plugin_slug . '-style', plugins_url( 'assets/css/exif-style.css', $this->base->file ), array(), $this->base->version );

		// Register JS.
		wp_register_script( $this->base->plugin_slug . '-script', plugins_url( 'assets/js/min/envira-exif-min.js', $this->base->file ), array( 'jquery' ), time(), true );

		// Gallery: EXIF.
		add_action( 'envira_gallery_before_output', array( $this, 'output_css' ) );
		add_action( 'envira_link_before_output', array( $this, 'output_css' ) );
		add_filter( 'envira_gallery_output_after_link', array( $this, 'gallery_build_exif_html' ), 10, 5 );
		add_filter( 'envira_gallery_output_image_attr', array( $this, 'gallery_build_exif_lightbox_data' ), 10, 5 );
		add_action( 'envira_images_pre_data', array( $this, 'gallery_envira_images_pre_data_exif' ), 10, 2 );

		// Lightbox.
		add_filter( 'envira_gallery_output_link_attr', array( $this, 'gallery_envira_lightbox_capture_time' ), 10, 5 );

		// Gallery Tags, if Tags Addon is enabled.
		add_filter( 'envira_tags_filter_markup', array( $this, 'tags_filter_markup' ), 1, 3 );
		add_filter( 'envira_tags_item_data', array( $this, 'tags_item_data' ), 1, 4 );
		add_filter( 'envira_tags_filter_classes', array( $this, 'tags_filter_classes' ), 10, 4 );
		add_filter( 'envira_gallery_pre_data', array( $this, 'gallery_maybe_filter_by_tag' ), 10, 2 );
		add_filter( 'envira_tags_to_filter', array( $this, 'tags_to_filter' ), 10, 2 );

		// Albums.
		add_action( 'envira_albums_before_output', array( $this, 'output_css' ) );
		add_filter( 'envira_albums_lightbox_template', array( $this, 'output_exif_lightbox_template' ), 10, 2 );

	}

	/**
	 * Enqueue CSS if EXIF data is enabled
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Gallery Data.
	 */
	public function output_css( $data ) {

		// Check if EXIF data output is enabled.
		if ( ! $this->get_config( 'exif', $data ) && ! $this->get_config( 'exif_lightbox', $data ) ) {
			return;
		}

		// Enqueue.
		wp_enqueue_style( $this->base->plugin_slug . '-style' );
		wp_enqueue_script( $this->base->plugin_slug . '-script' );

	}

	/**
	 * Inserts EXIF Image data so they can later appear in the JS Object
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Gallery Data.
	 * @param int   $gallery_id Gallery ID.
	 * @return array $data Gallery Data
	 */
	public function gallery_envira_images_pre_data_exif( $data, $gallery_id ) {

		if ( empty( $data['gallery'] ) || ( empty( $data['config']['exif'] ) && empty( $data['config']['exif_lightbox'] ) ) || ( ! ( $data['config']['exif'] ) && ! ( $data['config']['exif_lightbox'] ) ) ) {
			return $data;
		}

		foreach ( $data['gallery'] as $image_id => $image_data ) {
			$exif_data = $this->get_exif_data( $image_id, $data );
			if ( ! empty( $exif_data ) ) {
				$data['gallery'][ $image_id ]['image_meta'] = $this->envira_santitize_exif_metadata( $exif_data, $image_id );
			}
		}

		return $data;

	}

	/**
	 * Santitize EXIF Metadata Strings As They Are Requested
	 *
	 * @since 1.4.2
	 *
	 * @access public
	 * @param array $meta_data Meta data.
	 * @param int   $image_id Image ID.
	 * @return array
	 */
	public function envira_santitize_exif_metadata( $meta_data, $image_id = false ) {

		if ( empty( $meta_data ) ) {
			return $meta_data;
		}

		if ( is_array( $meta_data ) && ! empty( $meta_data ) ) {
			foreach ( $meta_data as $key => $data ) {
				if ( 'caption' === $key ) {
					$meta_data[ $key ] = $this->envira_santitize_exif_caption( $data, $image_id );
				} else {
					$meta_data[ $key ] = $this->envira_santitize_exif_string( $data );
				}
			}
		} else {
			$meta_data = $this->envira_santitize_exif_string( $meta_data );
		}

		// Check for CaptureTime, since it might be not be present in the meta data stored in the DB.
		$meta_data['CaptureTime'] = ( ! empty( $meta_data['CaptureTime'] ) ) ? $meta_data['CaptureTime'] : $meta_data['created_timestamp'];

		return $meta_data;

	}

	/**
	 * Santitize EXIF Data Strings As They Are Requested
	 *
	 * @since 1.4.2
	 *
	 * @access public
	 * @param string $text Text.
	 * @return void
	 */
	public function envira_santitize_exif_string( $text ) {

		if ( empty( $text ) || is_array( $text ) ) {
			return;
		}

		$encoding          = ( function_exists( 'mb_detect_encoding' ) && mb_detect_encoding( $text ) !== 'ASCII' ) ? mb_detect_encoding( $text ) : 'utf-8';
		$santitized_string = htmlentities( $text, ENT_QUOTES, $encoding );
		return $santitized_string;

	}

	/**
	 * Santitize EXIF Data Strings As They Are Requested
	 *
	 * @since 1.4.2
	 *
	 * @access public
	 * @param string $text Text.
	 * @param int    $image_id Image ID.
	 * @return void
	 */
	public function envira_santitize_exif_caption( $text, $image_id = false ) {

		if ( empty( $text ) || is_array( $text ) ) {
			return;
		}

		$encoding          = ( function_exists( 'mb_detect_encoding' ) && mb_detect_encoding( $text ) !== 'ASCII' ) ? mb_detect_encoding( $text ) : 'utf-8';
		$text              = str_replace( '"', '', $text );
		$santitized_string = htmlentities( $text, ENT_QUOTES, $encoding );
		return $santitized_string;

	}

	/**
	 * Outputs EXIF Image data for the gallery thumbnail if enabled
	 *
	 * @since 1.0.0
	 *
	 * @param string $output HTML Output.
	 * @param int    $id Attachment ID.
	 * @param array  $item Image Item.
	 * @param array  $data Gallery Config.
	 * @param int    $i Image number in gallery.
	 * @return string HTML Output
	 */
	public function gallery_build_exif_html( $output, $id, $item, $data, $i ) {

		// Check if EXIF data output is enabled.
		if ( ! $this->get_config( 'exif', $data ) ) {
			return $output;
		}

		// Get EXIF data.
		$exif_data = $this->get_exif_data( $id, $data );
		if ( ! $exif_data ) {
			return $output;
		}

		// Build EXIF output.
		$exif_html = '<div class="envira-exif">';
		$exif      = false;

		// Make & Model.
		if ( $this->get_config( 'exif_make', $data ) || $this->get_config( 'exif_model', $data ) ) {
			$exif .= '<div class="model"><span>';

			// Make.
			if ( $this->get_config( 'exif_make', $data ) && isset( $exif_data['Make'] ) ) {
				$exif .= $exif_data['Make'];
			}

			// Model.
			if ( $this->get_config( 'exif_model', $data ) && isset( $exif_data['Model'] ) ) {
				$exif .= ' ' . $exif_data['Model'];
			}

			$exif .= '</span></div>';
		}

		// Aperture.
		if ( $this->get_config( 'exif_aperture', $data ) && isset( $exif_data['Aperture'] ) ) {
			$exif .= '<div class="aperture"><span>f/' . $exif_data['Aperture'] . '</span></div>';
		}

		// Shutter speed.
		if ( $this->get_config( 'exif_shutter_speed', $data ) && isset( $exif_data['ShutterSpeed'] ) ) {
			$exif .= '<div class="shutter-speed"><span>' . $exif_data['ShutterSpeed'] . '</span></div>';
		}

		// Focal length.
		if ( $this->get_config( 'exif_focal_length', $data ) && isset( $exif_data['FocalLength'] ) ) {
			$exif .= '<div class="focal-length"><span>' . $exif_data['FocalLength'] . '</span></div>';
		}

		// ISO.
		if ( $this->get_config( 'exif_iso', $data ) && isset( $exif_data['iso'] ) ) {
			$exif .= '<div class="iso"><span>' . $exif_data['iso'] . '</span></div>';
		}

		// Capture Time.
		if ( $this->get_config( 'exif_capture_time', $data ) && isset( $exif_data['CaptureTime'] ) ) {
			$capture_time_format = ( ! empty( trim( $this->get_config( 'exif_capture_time_format', $data ) ) ) && $this->get_config( 'exif_capture_time_format', $data ) !== false ) ? trim( $this->get_config( 'exif_capture_time_format', $data ) ) : 'F j, Y';
			$exif               .= '<div class="capture-time"><span>' . gmdate( $capture_time_format, $exif_data['CaptureTime'] ) . '</span></div>';
		}

		if ( $exif ) {
			// there is actually something to display so assume w/ the parent div.
			$exif = $exif_html . $exif . '</div>';
		}

		// Return Output with EXIF output.
		return $output . $exif;

	}

	/**
	 * Outputs EXIF Image data in JSON format for the gallery thumbnail. Data is then transported to the lightbox
	 * when the image is clicked
	 *
	 * @since 1.0.0
	 *
	 * @param   string $output     HTML Output.
	 * @param   int    $id         Attachment ID.
	 * @param   array  $item       Image Item.
	 * @param   array  $data       Gallery Config.
	 * @param   int    $i          Image number in gallery.
	 * @return  string              HTML Output
	 */
	public function gallery_build_exif_lightbox_data( $output, $id, $item, $data, $i ) {

		// Check if EXIF lightbox data output is enabled.
		if ( ! $this->get_config( 'exif_lightbox', $data ) ) {
			return $output;
		}

		// Define array to be JSON-encoded.
		$exif = array();

		// Get EXIF data.
		if ( $this->get_config( 'exif_lightbox', $data ) ) {
			$exif_data = $this->get_exif_data( $id, $data );

			// Build EXIF output for use on lightbox
			// Make.
			if ( $this->get_config( 'exif_lightbox_make', $data ) && isset( $exif_data['Make'] ) ) {
				$exif['Make'] = $exif_data['Make'];
			}

			// Model.
			if ( $this->get_config( 'exif_lightbox_model', $data ) && isset( $exif_data['Model'] ) ) {
				$exif['Model'] = $exif_data['Model'];
			}

			// Aperture.
			if ( $this->get_config( 'exif_lightbox_aperture', $data ) && isset( $exif_data['Aperture'] ) ) {
				$exif['Aperture'] = $exif_data['Aperture'];
			}

			// Shutter speed.
			if ( $this->get_config( 'exif_lightbox_shutter_speed', $data ) && isset( $exif_data['ShutterSpeed'] ) ) {
				$exif['ShutterSpeed'] = $exif_data['ShutterSpeed'];
			}

			// Focal length.
			if ( $this->get_config( 'exif_lightbox_focal_length', $data ) && isset( $exif_data['FocalLength'] ) ) {
				$exif['FocalLength'] = $exif_data['FocalLength'];
			}

			// ISO.
			if ( $this->get_config( 'exif_lightbox_iso', $data ) && isset( $exif_data['iso'] ) ) {
				$exif['iso'] = $exif_data['iso'];
			}
		}

		// Return.
		$output .= " data-envira-data='" . wp_json_encode( $exif ) . "'";
		return $output;

	}

	/**
	 * Outputs a new template for the Lightbox
	 *
	 * @param string $template Template HTML.
	 * @param array  $data Gallery Data.
	 * @return string Template HTML
	 */
	public function output_exif_lightbox_template( $template, $data ) {

		// Check if EXIF data output is enabled.
		if ( ! $this->get_config( 'exif_lightbox', $data ) ) {
			return $template;
		}

		// We have to modify the $template - remove last three <divs>.
		$ending_tags = '</div></div></div>';
		$length      = strlen( $ending_tags );
		if ( substr( $template, -$length ) === $ending_tags ) {
			// the ending tags exist, so we need to remove them
			// in the end, we are pushing the "envirabox-exit" <div> into the correct location.
			$template = substr( $template, 0, strlen( $template ) - $length );
		}

		// Return the amended markup.
		return $template . '<div class="envirabox-exif"></div></div></div></div>';

	}

	/**
	 * Album: Prepare EXIF Lightbox data for a Gallery
	 *
	 * @since 1.0.0
	 *
	 * @param array $image      Gallery Image.
	 * @param array $gallery    Gallery Data.
	 * @param int   $image_id   Image ID.
	 * @param array $data       Album Data.
	 */
	public function album_output_exif_data( $image, $gallery, $image_id, $data ) {

		// Check if EXIF data output is enabled.
		if ( ! $this->get_album_config( 'exif_lightbox', $data ) ) {
			return;
		}

		// Get EXIF data.
		$exif_data = $this->get_exif_data( $image_id, $data );
		if ( ! $exif_data ) {
			return;
		}

		// Define the CSS classes for positioning the EXIF metadata.
		$css_class  = ! empty( $this->get_config( 'exif_lightbox_position', $data ) ) ? 'position-' . $this->get_config( 'exif_lightbox_position', $data ) : 'position-top-left';
		$css_class .= ( $this->get_config( 'exif_lightbox_outside', $data ) ? ' outside' : '' );

		// Build EXIF output for use on gallery and/or lightbox.
		$exif_html = '<div class="envira-exif ' . $css_class . '">';
		$exif      = false;

		// Make & Model.
		if ( $this->get_album_config( 'exif_lightbox_make', $data ) || $this->get_album_config( 'exif_lightbox_model', $data ) ) {
			$exif .= '<div class="model"><span>';

			// Make.
			if ( $this->get_album_config( 'exif_lightbox_make', $data ) && isset( $exif_data['Make'] ) ) {
				$exif .= $exif_data['Make'];
			}

			// Model.
			if ( $this->get_album_config( 'exif_lightbox_model', $data ) && isset( $exif_data['Model'] ) ) {
				$exif .= ' ' . $exif_data['Model'];
			}

			$exif .= '</span></div>';
		}

		// Aperture.
		if ( $this->get_album_config( 'exif_lightbox_aperture', $data ) && isset( $exif_data['Aperture'] ) ) {
			$exif .= '<div class="aperture"><span>f/' . $exif_data['Aperture'] . '</span></div>';
		}

		// Shutter speed.
		if ( $this->get_album_config( 'exif_lightbox_shutter_speed', $data ) && isset( $exif_data['ShutterSpeed'] ) ) {
			$exif .= '<div class="shutter-speed"><span>' . $exif_data['ShutterSpeed'] . '</span></div>';
		}

		// Focal length.
		if ( $this->get_album_config( 'exif_lightbox_focal_length', $data ) && isset( $exif_data['FocalLength'] ) ) {
			$exif .= '<div class="focal-length"><span>' . $exif_data['FocalLength'] . '</span></div>';
		}

		// ISO.
		if ( $this->get_album_config( 'exif_lightbox_iso', $data ) && isset( $exif_data['iso'] ) ) {
			$exif .= '<div class="iso"><span>' . $exif_data['iso'] . '</span></div>';
		}

		// Capture Time.
		if ( $this->get_album_config( 'exif_capture_time', $data ) && isset( $exif_data['CaptureTime'] ) ) {
			$capture_time_format = ( ! empty( trim( $this->get_album_config( 'exif_capture_time_format', $data ) ) ) && $this->get_album_config( 'exif_capture_time_format', $data ) !== false ) ? trim( $this->get_album_config( 'exif_capture_time_format', $data ) ) : 'F j, Y';
			$exif               .= '<div class="capture-time"><span>' . gmdate( $capture_time_format, $exif_data['CaptureTime'] ) . '</span></div>';
		}

		if ( $exif ) {
			// there is actually something to display so assume w/ the parent div.
			$exif = $exif_html . $exif . '</div>';
		}

		?>
		, exif_html: '<?php echo wp_kses( $exif, $this->wp_kses_allowed_html ); ?>'
		<?php

	}

	/**
	 * Outputs EXIF Lightbox data when a lightbox image is displayed from an Album
	 *
	 * @param array $data Gallery Data.
	 * @return JS
	 */
	public function album_output_exif_lightbox_data( $data ) {

		// Check if EXIF data output is enabled.
		if ( ! $this->get_config( 'exif_lightbox', $data ) ) {
			return;
		}

		?>
		$('div.envirabox-exif').hide().html( this.exif_html ).fadeIn(300);
		<?php

	}

	/**
	 * Maybe filter by tag.
	 *
	 * @param array  $data Gallery Data.
	 * @param string $gallery_id Gallery ID.
	 * @return JS
	 */
	public function gallery_maybe_filter_by_tag( $data, $gallery_id ) {

		// Check a tag exists.
		$tag = get_query_var( 'envira-exif-manufacturer' );
		if ( empty( $tag ) ) {
			return $data;
		}

		// Filter data by that tag.
		foreach ( $data['gallery'] as $attachment_id => $item ) {
			if ( ! has_term( $tag, 'envira-exif-manufacturer', $attachment_id ) ) {
				unset( $data['gallery'][ $attachment_id ] );
				continue;
			}
		}

		return $data;

	}

	/**
	 * Determines what tags to filter.
	 *
	 * @param string $tags Tags.
	 * @param array  $data Gallery Data.
	 * @return JS
	 */
	public function tags_to_filter( $tags, $data ) {

		// If tag filtering is not enabled, return early.
		if ( ! $this->get_config( 'exif_tags', $data ) ) {
			return $tags;
		}

		// Now we need to ensure that we actually have tags to process. If we have no tags, return early.
		$gallery_tags = Envira_Tags_Shortcode::get_instance()->get_tags_from_gallery( $data, 'envira-exif-manufacturer' );

		if ( empty( $gallery_tags ) ) {
			$gallery_tags = array();
		} else {
			foreach ( $gallery_tags as $index => $gallery_tag ) {
				$gallery_tags[ 'envira-exif-manufacturer-' . $index ] = $gallery_tag;
				unset( $gallery_tags[ $index ] );
			}
		}
		if ( empty( $tags ) ) {
			$tags = array();
		}

		$tags = array_merge( $tags, $gallery_tags );

		// Remove duplicates.
		$tags = array_unique( $tags );

		if ( empty( $tags ) ) {
			return false;
		}

		return $tags;

	}

	/**
	 * Outputs the tag filter links at the top of the gallery.
	 *
	 * @since 1.0.0
	 *
	 * @param string $markup    The HTML output for the gallery.
	 * @param array  $tags       Tags.
	 * @param array  $data       Data for the Envira gallery.
	 * @return string           Amended gallery HTML.
	 */
	public function tags_filter_markup( $markup, $tags, $data ) {

		global $post;

		// If tag filtering is not enabled, return early.
		if ( ! $this->get_config( 'exif_tags', $data ) ) {
			return $markup;
		}

		// Now we need to ensure that we actually have tags to process. If we have no tags, return early.
		$instance     = Envira_Tags_Shortcode::get_instance();
		$gallery_tags = $instance->get_tags_from_gallery( $data, 'envira-exif-manufacturer' );
		if ( ! $tags || ! $gallery_tags ) {
			return $markup;
		}

		// Remove trailing </ul>.
		$markup = str_replace( '</ul>', '', $markup );

		// Loop through the tags and add them to the filter list.
		foreach ( $tags as $i => $tag ) {

			if ( is_array( $gallery_tags ) && in_array( $tag, $gallery_tags, true ) ) {
				continue;
			}

			// Build non-JS URL.
			$url = add_query_arg(
				array(
					'envira-exif-manufacturer' => sanitize_html_class( $i ),
				),
				get_permalink( $post->ID )
			);

			// Append anchor to the URL if scroll to gallery is enabled.
			if ( $this->get_config( 'tags_scroll', $data ) ) {
				$url .= '#envira-gallery-wrap-' . $data['id'];
			}

			$markup .= '<li id="envira-exif-manufacturer-filter-' . sanitize_html_class( $tag ) . '" class="envira-tags-filter">';
				/* translators: %s */
				$markup     .= '<a href="' . $url . '" class="envira-tags-filter-link" title="' . sprintf( __( 'Filter by %s', 'envira-tags' ), $tag ) . '" data-envira-filter=".envira-exif-manufacturer-' . sanitize_html_class( $tag ) . '">';
					$markup .= $tag;
				$markup     .= '</a>';
			$markup         .= '</li>';
		}

		// Add </ul> back.
		$markup .= '</ul>';

		// Return the amended gallery HTML.
		return $markup;

	}

	/**
	 * Adds taxonomy terms to $item, so envira_tags_filter_classes can
	 * output taxonomy term classes against the $item
	 *
	 * @since 1.0.5
	 * @param array $item     Array of item data.
	 * @param int   $id         Item ID.
	 * @param array $data     Array of gallery data.
	 * @param int   $i          The current position in the gallery.
	 * @return array $item Amended item.
	 */
	public function tags_item_data( $item, $id, $data, $i ) {

		// If no more tags, return the classes.
		$terms = wp_get_object_terms( $id, 'envira-exif-manufacturer' );
		if ( count( $terms ) === 0 ) {
			return $item;
		}

		// Loop through tags and output them as custom classes.
		foreach ( $terms as $term ) {
			// Set new array key if it doesn't exist.
			if ( ! isset( $item['exif_tags'] ) ) {
				$item['exif_tags'] = array();
			}

			// Add term to array key.
			$item['exif_tags'][ $term->term_id ] = $term->name;
		}

		return $item;

	}

	/**
	 * Outputs the filter classes on the gallery item.
	 *
	 * @since 1.0.0
	 *
	 * @param array $classes  Current item classes.
	 * @param array $item     Array of item data.
	 * @param int   $i          The current position in the gallery.
	 * @param array $data     Array of gallery data.
	 * @return array $classes Amended item classes.
	 */
	public function tags_filter_classes( $classes, $item, $i, $data ) {

		// If filtering is not enabled, do nothing.
		if ( ! $this->get_config( 'exif_tags', $data ) ) {
			return $classes;
		}

		// If no more tags, return the classes.
		if ( ! isset( $item['exif_tags'] ) || count( $item['exif_tags'] ) === 0 ) {
			return $classes;
		}

		// Loop through tags and output them as custom classes.
		foreach ( $item['exif_tags'] as $term_id => $term_name ) {
			$classes[] = 'envira-tag-envira-exif-manufacturer-' . sanitize_title( $term_name );
		}

		return $classes;

	}

	/**
	 * Helper method for retrieving gallery config values.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The config key to retrieve.
	 * @param array  $data The gallery data to use for retrieval.
	 * @return string     Key value on success, default if not set.
	 */
	public function get_config( $key, $data ) {

		return Envira_Gallery_Shortcode::get_instance()->get_config( $key, $data );

	}

	/**
	 * Helper method for retrieving album config values.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The config key to retrieve.
	 * @param array  $data The gallery data to use for retrieval.
	 * @return string     Key value on success, default if not set.
	 */
	public function get_album_config( $key, $data ) {

		return Envira_Albums_Shortcode::get_instance()->get_config( $key, $data );

	}

	/**
	 * Helper method for displaying capture time in attr so JS in Lightbox can pick it up
	 *
	 * @since 1.4.3
	 *
	 * @param string $attr     Attribute.
	 * @param int    $id       Item ID.
	 * @param array  $item     Array of item data.
	 * @param array  $data     Array of gallery data.
	 * @param int    $i        The current position in the gallery.
	 * @return string     Key value on success, default if not set.
	 */
	public function gallery_envira_lightbox_capture_time( $attr, $id, $item, $data, $i ) {

		$exif_data = $this->get_exif_data( $id, $data );

		if ( empty( $exif_data ) || empty( $exif_data['CaptureTime'] ) ) {
			return $attr;
		}

		$capture_time_format = ( ! empty( trim( $this->get_config( 'exif_capture_time_format', $data ) ) ) && $this->get_config( 'exif_capture_time_format', $data ) !== false ) ? trim( $this->get_config( 'exif_capture_time_format', $data ) ) : 'F j, Y';

		$capture_time_display = gmdate( $capture_time_format, $exif_data['CaptureTime'] );

		if ( $capture_time_display ) {
			$attr .= 'data-capture-time-display="' . $capture_time_display . '"';
		}

		return $attr;
	}

	/**
	 * Helper method for retrieving EXIF data from an image.
	 *
	 * @since 1.0.0
	 *
	 * @param   int   $id     Attachment ID.
	 * @param   array $gallery_data     Gallery Data.
	 * @return  array           EXIF data
	 */
	public function get_exif_data( $id, $gallery_data = false ) {

		if ( false !== strpos( $id, '_folder' ) ) {
			// this is a dynamic gallery isn't it?
			if ( ! empty( $gallery_data['gallery'][ $id ] ) ) {
				// Work towards geting the file path.

				// Get folder.
				$folder_parts = explode( '_', $gallery_data['gallery_id'] );

				// Remove the first element, which should be 'folder', then put the array back together.
				unset( $folder_parts[0] );
				if ( empty( $folder_parts ) ) {
					return false;
				}
				$folder = implode( apply_filters( 'envira_get_exif_data_folder_character', '-' ), $folder_parts );

				$folder_path = trailingslashit( WP_CONTENT_DIR ) . $folder;
				$folder_url  = trailingslashit( WP_CONTENT_URL ) . $folder;
				$file_name   = $folder_path . '/' . basename( $gallery_data['gallery'][ $id ]['src'] );

				if ( ! file_exists( $folder_path ) ) {
					// TO-DO -> Return A Message.
					return false;
				}
				$data = Envira_Exif_Parser::get_instance()->get_exif_data( $id, $file_name );
			}
		} else {
			$data = Envira_Exif_Parser::get_instance()->get_exif_data( $id );
		}

		/* Check data exists - if the below array keys are blank, there isn't any EXIF data available. */
		if ( empty( $data['Make'] ) &&
			empty( $data['Model'] ) &&
			empty( $data['Aperture'] ) &&
			empty( $data['ShutterSpeed'] ) &&
			empty( $data['FocalLength'] ) &&
			empty( $data['iso'] ) ) {

			return false;

		}

		return $data;

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Pagination_Shortcode object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Exif_Shortcode ) ) {
			self::$instance = new Envira_Exif_Shortcode();
		}

		return self::$instance;

	}

}

// Load the shortcode class.
$envira_exif_shortcode = Envira_Exif_Shortcode::get_instance();
