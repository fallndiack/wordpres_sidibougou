<?php
/**
 * Shortcode class.
 *
 * @since 1.7.0
 *
 * @package Envira_Gallery
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode class.
 *
 * @since 1.3.0
 *
 * @package Envira_Tags
 * @author  Envira Team
 */
class Envira_Tags_Shortcode {

	/**
	 * Holds the class object.
	 *
	 * @since 1.3.0
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.3.0
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Gallery Info.
	 *
	 * @since 1.3.0
	 *
	 * @var string
	 */
	public $gallery;

	/**
	 * Gallery shortcode.
	 *
	 * @since 1.3.0
	 *
	 * @var string
	 */
	public $gallery_shortcode;

	/**
	 * Is Mobile
	 *
	 * @var mixed
	 * @access public
	 */
	public $is_mobile;

	/**
	 * Gallery Slug.
	 *
	 * @since 1.3.0
	 *
	 * @var string
	 */
	public $slug_name = 'envira-tag';

	/**
	 * Primary class constructor.
	 *
	 * @since 1.3.0
	 */
	public function __construct() {

		if ( ! class_exists( 'Envira_Gallery' ) ) {
			return;
		}
		$this->gallery           = Envira_Gallery::get_instance();
		$this->gallery_shortcode = Envira_Gallery_Shortcode::get_instance();
		$this->is_mobile         = envira_mobile_detect()->isMobile();

		// Load the base class object.
		$this->base = Envira_Tags::get_instance();

		$version = ( defined( 'ENVIRA_DEBUG' ) && 'true' === ENVIRA_DEBUG ) ? $version = time() . '-' . ENVIRA_VERSION : ENVIRA_VERSION;

		// Register JS.
		wp_register_script( $this->base->plugin_slug . '-script', plugins_url( 'assets/js/min/envira-tags-min.js', $this->base->file ), array( 'jquery' ), $version, true );

		// Potential Whitelabeling.
		if ( apply_filters( 'envira_whitelabel', false ) ) {
			$this->slug_name = apply_filters( 'envira_whitelabel_envira_tag_slug', $this->slug_name );
		}

		// Gallery Items.
		add_action( 'envira_gallery_before_output', array( $this, 'gallery_output_css_js' ) );
		add_action( 'envira_link_before_output', array( $this, 'gallery_output_css_js' ) );

		add_filter( 'envira_images_pre_data', array( $this, 'gallery_maybe_filter_by_tag' ), 1, 2 );
		add_filter( 'envira_gallery_pre_data', array( $this, 'gallery_maybe_filter_by_tag' ), 1, 2 );
		add_filter( 'envira_gallery_output_before_container', array( $this, 'gallery_filter_links_top' ), 1, 2 );
		add_filter( 'envira_gallery_output_after_container', array( $this, 'gallery_filter_links_bottom' ), 1, 2 );
		add_filter( 'envira_gallery_output_item_data', array( $this, 'gallery_item_data' ), 1, 4 );
		add_filter( 'envira_gallery_output_item_classes', array( $this, 'gallery_filter_classes' ), 10, 4 );

		// Album.
		add_action( 'envira_albums_before_output', array( $this, 'gallery_output_css_js' ) );

		add_filter( 'envira_albums_custom_gallery_data', array( $this, 'albums_data' ), 10, 3 );
		add_filter( 'envira_albums_pre_data', array( $this, 'albums_maybe_filter_by_tag' ), 10, 2 );
		add_filter( 'envira_albums_output_before_container', array( $this, 'albums_filter_links_top' ), 1, 2 );
		add_filter( 'envira_albums_output_after_container', array( $this, 'albums_filter_links_bottom' ), 1, 2 );
		add_filter( 'envira_albums_output_item_data', array( $this, 'albums_item_data' ), 1, 4 );
		add_filter( 'envira_albums_output_item_classes', array( $this, 'albums_filter_classes' ), 10, 4 );

		// Cache.
		add_filter( 'envira_gallery_get_transient_markup', array( $this, 'envira_maybe_clear_cache' ), 1, 2 );
		add_filter( 'envira_albums_get_transient_markup', array( $this, 'envira_maybe_clear_cache' ), 1, 2 );

	}

	/**
	 * Enqueue CSS and JS if Social Sharing is enabled
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Gallery Data.
	 */
	public function gallery_output_css_js( $data ) {

		// Enqueue CSS + JS.
		wp_enqueue_style( $this->base->plugin_slug . '-style' );
		wp_enqueue_script( $this->base->plugin_slug . '-script' );

	}

	/**
	 * Adds needed JavaScript For Initiation For Filter
	 *
	 * @since 1.6.2
	 *
	 * @param string $_transient   Boolean for determining custom gallery data.
	 * @param array  $data         Gallery Data.
	 * @return bool     False if transient is not to be used
	 */
	public function envira_maybe_clear_cache( $_transient, $data ) {
		if ( envira_get_config( 'tags', $data ) ) {
			return false;
		}
		return $_transient;
	}

	/**
	 * Possibly retrieves a custom gallery based on tags.
	 *
	 * @since 1.0.0
	 *
	 * @param bool   $bool   Boolean for determining custom gallery data.
	 * @param array  $atts  Array of shortcodes attributes.
	 * @param object $post The current post object.
	 * @return bool|array  False if no custom data is to be loaded, custom data otherwise.
	 */
	public function gallery_data( $bool, $atts, $post ) {

		$dynamic_gallery = false;

		if ( isset( $atts['dynamic'] ) && strpos( $atts['dynamic'], 'tags-' ) !== false && strpos( $atts['dynamic'], ',' ) === false ) {
			$dynamic_gallery = true;
		} elseif ( ! isset( $atts['tags'] ) && ! isset( $atts['tags_id'] ) ) { // If our custom attributes do not exist, return early.
			return $bool;
		}

		// Since this is a dynamic gallery. If there is no gallery set as a default for config, use the first gallery returned.
		$config = array();
		if ( isset( $atts['config'] ) ) {
			$gallery = $this->gallery->get_gallery( (int) $atts['config'] );
			if ( ! $gallery ) {
				return $bool;
			} else {
				$config = $gallery['config'];
			}
		} else {

			$dynamic_id = Envira_Dynamic_Common::get_instance()->get_gallery_dynamic_id();
			$gallery    = get_post_meta( $dynamic_id, '_eg_gallery_data', true );

			if ( $gallery ) {
				$config = $gallery['config'];
			} elseif ( ! $config ) {
				return $bool;
			}
		}

		// If the config is still empty, return.
		if ( empty( $config ) ) {
			return $bool;
		}

		// Check tags comparison operator.
		if ( isset( $atts['operator'] ) ) {
			$config['tags_operator'] = $atts['operator'];
		}

		// If tags is *, get all tags.
		if ( ( isset( $atts['tags'] ) && '*' === $atts['tags'] ) || 'tags-*' === $atts['dynamic'] ) {
			$tags  = array();
			$terms = get_terms( 'envira-tag' );
			foreach ( $terms as $term ) {
				$tags[] = $term->slug;
			}
		} elseif ( isset( $atts['dynamic'] ) && strpos( $atts['dynamic'], 'tags-' ) !== false ) {
			$tags = array( str_replace( 'tags-', '', $atts['dynamic'] ) );
		} else {
			$tags = explode( ',', (string) $atts['tags'] );
		}

		// Now that we know we want to grab a gallery based on tags, lets do that now.
		if ( isset( $atts['tags_id'] ) ) {
			$id = str_replace( '-', '_', $atts['tags_id'] );
		} elseif ( isset( $atts['dynamic'] ) ) {
			$id = str_replace( '-', '_', $atts['dynamic'] );
		} else {
			// there is no $id, prevent PHP Notices.
			return $bool;
		}

		$data                 = $this->get_gallery_by_tags( $tags, $config, $id );
		$data['config']['id'] = $id;

		if ( $dynamic_gallery ) {
			$data['config']['type'] = 'dynamic';
		}

		/* if there's pagination for a dynamic gallery, then we need to replace $data['config'] with dynamic $data['config'] values */

		if ( $dynamic_gallery ) {

			$dynamic_id       = Envira_Dynamic_Common::get_instance()->get_gallery_dynamic_id();
			$dynamic_defaults = get_post_meta( $dynamic_id, '_eg_gallery_data', true );

			if ( ! empty( $dynamic_defaults['config']['pagination'] ) ) {

				if ( isset( $dynamic_defaults['config']['pagination'] ) ) {
					$data['config']['pagination'] = $dynamic_defaults['config']['pagination'];
				}
				if ( isset( $dynamic_defaults['config']['pagination_images_per_page'] ) ) {
					$data['config']['pagination_images_per_page'] = $dynamic_defaults['config']['pagination_images_per_page'];
				}
				if ( isset( $dynamic_defaults['config']['pagination_position'] ) ) {
					$data['config']['pagination_position'] = $dynamic_defaults['config']['pagination_position'];
				}
				if ( isset( $dynamic_defaults['config']['pagination_prev_next'] ) ) {
					$data['config']['pagination_prev_next'] = $dynamic_defaults['config']['pagination_prev_next'];
				}
				if ( isset( $dynamic_defaults['config']['pagination_prev_text'] ) ) {
					$data['config']['pagination_prev_text'] = $dynamic_defaults['config']['pagination_prev_text'];
				}
				if ( isset( $dynamic_defaults['config']['pagination_next_text'] ) ) {
					$data['config']['pagination_next_text'] = $dynamic_defaults['config']['pagination_next_text'];
				}
				if ( isset( $dynamic_defaults['config']['pagination_scroll'] ) ) {
					$data['config']['pagination_scroll'] = $dynamic_defaults['config']['pagination_scroll'];
				}
				if ( isset( $dynamic_defaults['config']['pagination_ajax_load'] ) ) {
					$data['config']['pagination_ajax_load'] = $dynamic_defaults['config']['pagination_ajax_load'];
				}
				if ( isset( $dynamic_defaults['config']['pagination_button_text'] ) ) {
					$data['config']['pagination_button_text'] = $dynamic_defaults['config']['pagination_button_text'];
				}
				if ( isset( $dynamic_defaults['config']['pagination_lightbox_display_all_images'] ) ) {
					$data['config']['pagination_lightbox_display_all_images'] = $dynamic_defaults['config']['pagination_lightbox_display_all_images'];
				}
				if ( isset( $dynamic_defaults['config']['mobile_pagination_images_per_page'] ) ) {
					$data['config']['mobile_pagination_images_per_page'] = $dynamic_defaults['config']['mobile_pagination_images_per_page'];
				}
				if ( isset( $dynamic_defaults['config']['mobile_pagination_prev_next'] ) ) {
					$data['config']['mobile_pagination_prev_next'] = $dynamic_defaults['config']['mobile_pagination_prev_next'];
				}
			}
		} /* end pagination logic */

		// If our data is not returned, return our boolean value, otherwise return the data.
		if ( ! $data ) {
			return $bool;
		} else {
			return apply_filters( 'envira_tags_custom_gallery_data', $data, $atts, $post, $tags, $config, $id );
		}

	}

	/**
	 * Maybe filter the Gallery Data by a Tag, if the Tag is present in the URL
	 *
	 * @since 1.1.1
	 *
	 * @param array $data Gallery Data.
	 * @param int   $gallery_id Gallery ID.
	 * @return array Gallery Data
	 */
	public function gallery_maybe_filter_by_tag( $data, $gallery_id ) {

		// Check a tag exists in the query string.
		$tag = get_query_var( $this->slug_name ) ? get_query_var( $this->slug_name ) : false;
		$tag = ( ! empty( $_REQUEST[ $this->slug_name ] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $this->slug_name ] ) ) : $tag; // @codingStandardsIgnoreLine - no nonce in querystring
		// If there is nothing in the query string, check the 'tag_display' option, which comes secondary.
		$tag = ( empty( $tag ) && ! empty( $this->gallery_shortcode->get_config( 'tags_display', $data ) ) ) ? $this->gallery_shortcode->get_config( 'tags_display', $data ) : $tag;
		/* check if this is a column layout and the isotope is turned on - if so, return */
		if ( 0 !== intval( $this->gallery_shortcode->get_config( 'columns', $data ) ) && 0 !== intval( $this->gallery_shortcode->get_config( 'isotope', $data ) ) ) {
			return $data;
		}
		if ( empty( $tag ) || 'all' === strtolower( $tag ) ) {
			return $data;
		}
		// Filter data by that tag.
		$sanitized_tag_array = array();

		$terms = get_terms(
			'envira-tag',
			array(
				'hide_empty' => false,
			)
		);

		foreach ( $terms as $term ) {
			if ( $this->sanitize_tag( $term->name ) === $tag ) {
				$sanitized_tag_array[ $term->term_id ] = $this->sanitize_tag( $term->name );
			}
		}

		foreach ( $data['gallery'] as $attachment_id => $item ) {
			foreach ( $sanitized_tag_array as $term_id_to_check => $santitied_term_tag ) {
				if ( ! has_term( $term_id_to_check, 'envira-tag', $attachment_id ) ) {
					unset( $data['gallery'][ $attachment_id ] );
				}
			}
		}

		return $data;

	}

	/**
	 * Outputs the tag filter links at the top of the gallery.
	 *
	 * @since 1.0.0
	 *
	 * @param string $gallery  The HTML output for the gallery.
	 * @param array  $data      Data for the Envira gallery.
	 * @return string $gallery Amended gallery HTML.
	 */
	public function gallery_filter_links_top( $gallery, $data ) {

		// If tag filtering is not enabled, return early.
		if ( ! $this->gallery_shortcode->get_config( 'tags', $data ) || ( $this->is_mobile && 0 === $this->gallery_shortcode->get_config( 'tags_mobile', $data ) ) ) {
			return $gallery;
		}

		$position = $this->gallery_shortcode->get_config( 'tags_position', $data );
		if ( 'below' === $position ) {
			return $gallery;
		}

		// Now we need to ensure that we actually have tags to process. If we have no tags, return early.
		$tags = $this->get_tags_from_gallery( $data );
		$tags = apply_filters( 'envira_tags_to_filter', $tags, $data );
		if ( ! $tags ) {
			return $gallery;
		}

		// Append the tag filter markup.
		$gallery .= $this->gallery_get_filter_markup( $tags, $data );

		// Filter to allow other addons to add their own filtering.
		$gallery = apply_filters( 'envira_tags_filter_links', $gallery, $data );

		// Return the amended gallery HTML.
		return $gallery;

	}

	/**
	 * Outputs the tag filter links at the bottom of the gallery.
	 *
	 * @since 1.0.0
	 *
	 * @param string $gallery  The HTML output for the gallery.
	 * @param array  $data      Data for the Envira gallery.
	 * @return string $gallery Amended gallery HTML.
	 */
	public function gallery_filter_links_bottom( $gallery, $data ) {

		// If tag filtering is not enabled, return early.
		if ( ! $this->gallery_shortcode->get_config( 'tags', $data ) || ( ( 1 === $this->is_mobile ) && 0 === $this->gallery_shortcode->get_config( 'tags_mobile', $data ) ) ) {
			return $gallery;
		}

		$position = $this->gallery_shortcode->get_config( 'tags_position', $data );
		if ( 'below' !== $position ) {
			return $gallery; }

		// Now we need to ensure that we actually have tags to process. If we have no tags, return early.
		$tags = $this->get_tags_from_gallery( $data );
		$tags = apply_filters( 'envira_tags_to_filter', $tags, $data );
		if ( ! $tags ) {
			return $gallery;
		}

		// Append the tag filter markup.
		$gallery .= $this->gallery_get_filter_markup( $tags, $data );

		// Filter to allow other addons to add their own filtering.
		$gallery = apply_filters( 'envira_tags_filter_links', $gallery, $data );

		// Return the amended gallery HTML.
		return $gallery;

	}

	/**
	 * Adds taxonomy terms to $item, so envira_tags_filter_classes can
	 * output taxonomy term classes against the $item
	 *
	 * @since 1.0.5
	 * @param array $item     Array of item data.
	 * @param int   $id       Item ID.
	 * @param array $data     Array of gallery data.
	 * @param int   $i        The current position in the gallery.
	 * @return array $item Amended item.
	 */
	public function gallery_item_data( $item, $id, $data, $i ) {

		// Filter to allow other addons to add their own taxonomy terms.
		$item = apply_filters( 'envira_tags_item_data', $item, $id, $data, $i );

		// If no more tags, return the classes.
		$terms = wp_get_object_terms( $id, 'envira-tag' );
		if ( 0 === count( $terms ) ) {
			return $item;
		}

		// Loop through tags and output them as custom classes.
		foreach ( $terms as $term ) {
			// Set new array key if it doesn't exist.
			if ( ! isset( $item['tags'] ) ) {
				$item['tags'] = array();
			}

			// Add term to array key.
			$item['tags'][ $term->term_id ] = $this->sanitize_tag( $term->name );
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
	 * @param int   $i        The current position in the gallery.
	 * @param array $data     Array of gallery data.
	 * @return array $classes Amended item classes.
	 */
	public function gallery_filter_classes( $classes, $item, $i, $data ) {

		// If filtering is not enabled, do nothing.
		if ( ! $this->gallery_shortcode->get_config( 'tags', $data ) ) {
			return $classes;
		}

		// All items need to have envira-tag-all for filtering, even if no classes are attached to the item.
		$classes[] = 'envira-tag-all';

		// Filter to allow other addons to add their own class terms.
		$classes = apply_filters( 'envira_tags_filter_classes', $classes, $item, $i, $data );

		// If no more tags, return the classes.
		if ( ! isset( $item['tags'] ) || 0 === count( $item['tags'] )
			&& ( ! isset( $item['exif_tags'] ) || 0 === count( $item['exif_tags'] ) ) ) {
			return $classes;
		}

		// Loop through tags and output them as custom classes.
		foreach ( $item['tags'] as $term_id => $term_name ) {
			$tag_class_slug = $this->sanitize_tag( $term_name );

			// Get term by name.
			if ( strpos( $term_name, 'envira-exif' ) !== false ) {
				$term = (object) array(
					'name' => $term_name,
					'slug' => $tag_class_slug,
				);

				$tag_class_slug = $this->sanitize_tag( $term->slug );
			} else {
				$tag_class_slug = $this->sanitize_tag( $term_name );
				$term           = get_term_by( 'term_id', $term_id, 'envira-tag' );
			}

			if ( ! $term ) {
				continue;
			}

			$classes[] = 'envira-tag-' . $tag_class_slug;
		}

		if ( isset( $item['exif_tags'] ) && count( $item['exif_tags'] ) > 0 ) {
			foreach ( $item['exif_tags'] as $term_id => $term_name ) {
				// Get term by name.
				if ( strpos( $term_name, 'envira-exif' ) !== false ) {
					$term = (object) array(
						'name' => $term_name,
						'slug' => $term_name,
					);
				} else {
					$term = get_term_by( 'slug', $term_name, 'envira-tag' );
				}

				if ( ! $term ) {
					continue;
				}

				$classes[] = 'envira-tag-' . $term->slug;
			}
		}

		return $classes;

	}

	/**
	 * Queries a custom gallery set based on tags.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $tags     Array of tags to use for querying the gallery.
	 * @param array  $config   Array of gallery config to use.
	 * @param string $tags_id Custom ID for this gallery.
	 * @return bool|array     False if fails to get data, array of data otherwise.
	 */
	public function get_gallery_by_tags( $tags, $config, $tags_id ) {

		$gallery = $this->_get_gallery_by_tags( $tags, $config, $tags_id );

		// Return the gallery data.
		return $gallery;

	}

	/**
	 * Internal function that queries a custom gallery set based on tags.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $tags     Array of tags to use for querying the gallery.
	 * @param array  $config   Array of gallery config to use.
	 * @param string $tags_id Custom ID for this gallery.
	 * @return bool|array     False if fails to get data, array of data otherwise.
	 */
	public function _get_gallery_by_tags( $tags, $config, $tags_id ) { // @codingStandardsIgnoreLine

		// Retrieve galleries.
		$galleries = $this->gallery->get_galleries();
		if ( ! $galleries ) {
			return false;
		}

		// Get comparison operator.
		$operator = ( isset( $config['tags_operator'] ) ? $config['tags_operator'] : 'OR' );

		// Loop through the galleries and pluck out any images that match our tag selection.
		$images = array();
		foreach ( (array) $galleries as $i => $gallery ) {
			foreach ( (array) $gallery['gallery'] as $id => $item ) {
				// If there are no tags, keep going.
				$terms = wp_get_object_terms( $id, 'envira-tag' );

				if ( 0 === count( $terms ) ) {
					continue;
				}

				// Loop through the tags to see if we have a match.
				switch ( $operator ) {
					/**
					* Image must have all tags
					*/
					case 'AND':
						$matched = true;

						// Build array of terms.
						$terms_arr = array();
						foreach ( $terms as $term ) {
							$terms_arr[] = $term->slug;
						}

						// Iterate through requested tags.
						foreach ( $tags as $tag ) {
							// Does this tag exist in this image?
							if ( ! in_array( $tag, $terms_arr, true ) ) {
								// No, it doesn't - bail.
								$matched = false;
								break;
							}
						}

						// If here and $matched, all tags exist in this image.
						if ( $matched ) {
							$images[ $id ] = $galleries[ $i ]['gallery'][ $id ];
						}

						break;

					/**
					* Image can have any tag(s)
					*/
					case 'OR':
					default:
						foreach ( $terms as $term ) {

							if ( in_array( $term->name, $tags, true ) || in_array( $term->slug, $tags, true ) ) {

								$images[ $id ] = $galleries[ $i ]['gallery'][ $id ];
								break; // Break the foreach.

							} else {

								// check for special non-English characters only if they exist as a last resort.
								$revised_tags_array = array();

								foreach ( $tags as $tag ) {
									$revised_tags_array[] = $this->sanitize_tag( $tag );
								}
								$new_name = $this->sanitize_tag( $term->name );

								if ( in_array( $new_name, $revised_tags_array, true ) ) {
									$images[ $id ] = $galleries[ $i ]['gallery'][ $id ];
								}
							}
						}
						break; // Break the switch.
				}
			}
		}

		// If the images array is still empty, return false, otherwise return the images.
		if ( empty( $images ) ) {
			return false;
		} else {
			// We are good to go. Prepare the data and return it with a filter.
			$data['id']      = $tags_id;
			$data['config']  = $config;
			$data['gallery'] = $images;

			return apply_filters( 'envira_tags_get_gallery_by_tags', $data, $config, $tags_id );
		}

	}

	/**
	 * Retrieves a unique list of tags for a gallery.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $gallery_data   Array of gallery data to use.
	 * @param string $taxonomy      Taxonomy to check.
	 * @return bool|array           False if no tags are found, array of tags otherwise.
	 */
	public function get_tags_from_gallery( $gallery_data, $taxonomy = 'envira-tag' ) {

		// Loop through the images in the gallery and grab tags.
		$tags     = array();
		$has_tags = false;

		// Make sure we grab the tags from unfiltered data.
		$gallery_data = get_post_meta( $gallery_data['gallery_id'], '_eg_gallery_data', true );

		foreach ( (array) $gallery_data['gallery'] as $id => $item ) {
			// If there are no tags, keep going.
			$terms = wp_get_object_terms( $id, $taxonomy );
			if ( 0 === count( $terms ) ) {
				continue;
			}

			// Store the tags and set our flag to true.
			foreach ( $terms as $term ) {
				$tags[ $this->sanitize_tag_slug( $term->slug ) ] = str_replace( '&amp;', '&', $term->name );
			}

			$has_tags = true;
		}

		// If we have no tags, return false.
		if ( ! $has_tags ) {
			return false;
		}

		// If the gallery specifies the "Tags to include in Filtering" option (tags_filter), only return those tags in the tag list.
		if ( 'envira-tag' === $taxonomy && isset( $gallery_data['config']['tags_filter'] ) && ! empty( $gallery_data['config']['tags_filter'] ) ) {
			// Get filtered tags and check we have at least one tag specified.
			$filtered_tags = explode( ',', $gallery_data['config']['tags_filter'] );

			if ( count( $filtered_tags ) > 0 ) {

				$tags = array();

				// Iterate through filtered tags and check if each tag exists in $image_tags array
				// If so, add to our final $tags array.
				foreach ( $filtered_tags as $tag ) {
					$tag = get_term_by( 'name', trim( $tag ), $taxonomy );
					if ( ! empty( $tag->name ) && ! empty( $tag->slug ) && ! in_array( $tag->slug, $tags, true ) ) {

						$tags[ $tag->slug ] = $tag->name;

					}
				}
			}
		}

		// Depending on the sort order, rearrange the tags now.
		$tags_sorting = $this->gallery_shortcode->get_config( 'tags_sorting', $gallery_data );
		switch ( $tags_sorting ) {

			/**
			* Manual
			*/
			case 'manual':
				// Get manual sorting order.
				$custom_tag_array    = array();
				$tags_manual_sorting = $this->gallery_shortcode->get_config( 'tags_manual_sorting', $gallery_data );
				if ( ! empty( $tags_manual_sorting ) ) {
					foreach ( $tags_manual_sorting as $tag_manual_sorting ) {
						if ( array_key_exists( $tag_manual_sorting, $tags ) ) {
							$custom_tag_array[ $tag_manual_sorting ] = $tags[ $tag_manual_sorting ];
						}
					}
				}
				$tags = array_unique( $custom_tag_array );
				break;

			/**
			* Descending
			*/
			case 'desc':
				arsort( $tags );
				break;

			/**
			* Ascending
			*/
			default:
			case '':
				asort( $tags );
				break;

		}

		// Return filtered tags.
		return apply_filters( 'envira_tags_gallery_tags', array_unique( $tags ), $gallery_data );

	}

	/**
	 * Sanitize Tag Slug (foreign)
	 *
	 * @since 1.0.0
	 *
	 * @param string $tag Tag.
	 * @return string Tag.
	 */
	public function sanitize_tag_slug( $tag ) {

		$tag = sanitize_title( $tag );

		// @codingStandardsIgnoreStart
		return preg_replace( '/[^A-Za-z0-9 -]/', '', $tag );
		// @codingStandardsIgnoreEnd

	}

	/**
	 * Get Tag Totals From Gallery
	 *
	 * @since 1.0.0
	 *
	 * @param string $data Gallery data.
	 * @return string Tag.
	 */
	public function get_tag_total_from_gallery_id( $data ) {

		/* note we are doing this because pagination limits the images in $data at this point */

		$gallery_data = $this->gallery->get_gallery( (int) $data['gallery_id'] );

		$tags = array();

		foreach ( $gallery_data['gallery'] as $image_id => $image_data ) {
			$tags_temp = get_the_terms( $image_id, 'envira-tag' );
			if ( ! empty( $tags_temp ) ) {
				foreach ( $tags_temp as $tag_temp_info ) {
					if ( empty( $tags[ $tag_temp_info->slug ] ) ) {
						$tags[ $tag_temp_info->slug ] = 1;
					} else {
						$tags[ $tag_temp_info->slug ] = $tags[ $tag_temp_info->slug ] + 1;
					}
				}
			}
		}

		return $tags;

	}

	/**
	 * Retrieves the custom markup for the tag filter list.
	 *
	 * @since 1.0.0
	 *
	 * @param array $tags Array of tags to use for filtering.
	 * @param array $data Array of gallery data.
	 * @return string     Custom markup for the tag filter list.
	 */
	private function gallery_get_filter_markup( $tags, $data ) {

		global $post;

		// If the user has dynamically added a 'tag-filer=no', then don't display the filter.
		if ( isset( $data['config']['tag-filter'] ) && 'no' === $data['config']['tag-filter'] ) {
			return false;
		}

		$markup = '<ul id="envira-tags-filter-list-' . sanitize_html_class( $data['id'] ) . '" class="envira-tags-filter-list envira-clear">';

		// Add the 'All' tag if enabled.
		if ( $this->gallery_shortcode->get_config( 'tags_all_enabled', $data ) ) {

			if ( isset( $_REQUEST[ $this->slug_name ] ) && sanitize_text_field( wp_unslash( $_REQUEST[ $this->slug_name ] ) ) === 'all' ) { // @codingStandardsIgnoreLine - no nonce in querystring
				$css_active = 'envira-tags-filter-active';
			} else {
				$css_active = false;
			}

			$permalink   = empty( $data['config']['tags_display'] ) ? get_permalink( $post->ID ) : add_query_arg( array( $this->slug_name => 'all' ), get_permalink( $post->ID ) );
			$markup     .= '<li id="envira-tag-filter-all" class="envira-tags-filter">';
				$markup .= '<a href="' . $permalink . '" class="envira-tags-filter-link envira-tags-filter-active' . $css_active . ' " title="' . __( 'Filter by All', 'envira-tags' ) . '" data-envira-filter=".envira-tag-all">' . $this->gallery_shortcode->get_config( 'tags_all', $data ) . '</a>';
			$markup     .= '</li>';
		}

		$tag_totals = $this->get_tag_total_from_gallery_id( $data );

		// Loop through the tags and add them to the filter list.
		foreach ( $tags as $index => $slug ) {
			// Get the tag's name by slug.
			if ( strpos( $index, 'envira-exif' ) !== false ) {
				$tag = (object) array(
					'name' => $slug,
					'slug' => $index,
				);
			} else {
				$tag = get_term_by( 'slug', $slug, 'envira-tag' );
			}
			// Bail if we can't find the term.
			if ( ! $tag ) {
				continue;
			}

			$tag_class_slug = $this->sanitize_tag( $tag->slug );

			// Build non-JS URL.
			if ( isset( $post->ID ) ) {
				$url = add_query_arg( array( $this->slug_name => $tag_class_slug ), get_permalink( $post->ID ) );
			}

			if ( ( isset( $_REQUEST[ $this->slug_name ] ) && sanitize_text_field( wp_unslash( $_REQUEST[ $this->slug_name ] ) ) === $slug ) || ( isset( $_REQUEST['envira-category'] ) && sanitize_text_field( wp_unslash( $_REQUEST['envira-category'] ) ) ) ) { // @codingStandardsIgnoreLine - no nonce in querystring
				$css_active = 'envira-tags-filter-active';
			} else {
				$css_active = false;
			}

			// Append anchor to the URL if scroll to gallery is enabled.
			if ( $this->gallery_shortcode->get_config( 'tags_scroll', $data ) ) {
				$url .= '#envira-gallery-wrap-' . $data['id'];
			}

			// Output list item.
			$markup .= '<li id="envira-tag-filter-' . $tag_class_slug . '" class="envira-tags-filter">';
			/* translators: %s */
			$markup     .= '<a href="' . $url . '" class="envira-tags-filter-link ' . $css_active . '" title="' . sprintf( __( 'Filter by %s', 'envira-tags' ), $tag->name ) . '" data-envira-filter=".envira-tag-' . $tag_class_slug . '">';
				$markup .= $tag->name;
			$markup     .= '</a>';
			if ( isset( $data['config']['tags_count'] ) && 1 === $data['config']['tags_count'] && ! empty( $tag_totals[ $tag->slug ] ) ) {
				$markup .= ' (' . intval( $tag_totals[ $tag->slug ] ) . ') ';
			}
			$markup .= '</li>';
		}

		// Close up the markup.
		$markup .= '</ul>';

		return apply_filters( 'envira_tags_filter_markup', $markup, $tags, $data );

	}

	/**
	 * Albums
	 */

	/**
	 * Possibly retrieves a custom Album based on tags.
	 *
	 * @since 1.4.1
	 *
	 * @param   bool   $bool   Boolean for determining custom album data.
	 * @param   array  $atts   Array of shortcodes attributes.
	 * @param   object $post   The current post object.
	 * @return  bool|array          False if no custom data is to be loaded, custom data otherwise.
	 */
	public function albums_data( $bool, $atts, $post ) {

		// If our custom attributes do not exist, return early.
		if ( ! isset( $atts['tags'] ) && ! isset( $atts['tags_id'] ) ) {
			return $bool;
		}

		// Since this is a dynamic album. If there is no album set as a default for config, use the first album returned.
		$config = array();
		if ( isset( $atts['config'] ) ) {
			$album = Envira_Albums::get_instance()->get_album( (int) $atts['config'] );
			if ( ! $album ) {
				return $bool;
			} else {
				$config = $album['config'];
			}
		} else {
			$albums = Envira_Albums::get_instance()->get_albums();

			// If we have an album, use that album config. Otherwise, return false.
			if ( ! empty( $albums[0] ) && isset( $albums[0]['id'] ) ) {
				$album  = Envira_Albums::get_instance()->get_album( $album[0]['id'] );
				$config = $album['config'];
			} else {
				return $bool;
			}
		}

		// If the config is still empty, return.
		if ( empty( $config ) ) {
			return $bool;
		}

		// Check tags comparison operator.
		if ( isset( $atts['operator'] ) ) {
			$config['tags_operator'] = $atts['operator'];
		}

		// If tags is *, get all tags.
		if ( '*' === $atts['tags'] ) {
			$tags  = array();
			$terms = get_terms( 'envira-category' );
			foreach ( $terms as $term ) {
				$tags[] = $term->slug;
			}
		} else {
			$tags = explode( ',', (string) $atts['tags'] );
		}

		// Now that we know we want to grab a gallery based on tags, lets do that now.
		if ( isset( $atts['tags_id'] ) ) {
			$id = str_replace( '-', '_', $atts['tags_id'] );
		} elseif ( isset( $atts['dynamic'] ) ) {
			$id = str_replace( '-', '_', $atts['dynamic'] );
		}

		$data                 = $this->get_album_by_tags( $tags, $config, $id );
		$data['config']['id'] = $id;

		// If our data is not returned, return our boolean value, otherwise return the data.
		if ( ! $data ) {
			return $bool;
		} else {
			return apply_filters( 'envira_tags_custom_album_data', $data, $atts, $post, $tags, $config, $id );
		}

	}

	/**
	 * Maybe filter the Album Data by a Tag, if the Tag is present in the URL
	 *
	 * @since 1.4.1
	 *
	 * @param   array $data       Album Data.
	 * @param   int   $album_id   Album ID.
	 * @return  array             Album Data.
	 */
	public function albums_maybe_filter_by_tag( $data, $album_id ) {

		// Check a tag exists.
		$tag = get_query_var( 'envira-category' );
		if ( empty( $tag ) ) {
			return $data;
		}

		// Filter data by that tag.
		foreach ( $data['galleryIDs'] as $gallery_id ) {
			if ( ! has_term( $tag, 'envira-category', $gallery_id ) ) {
				$key = array_search( $gallery_id, $data['galleryIDs'], true );
				unset( $data['galleryIDs'][ $key ] );
				unset( $data['galleries'][ $gallery_id ] );
				continue;
			}
		}

		return $data;

	}

	/**
	 * Outputs the tag filter links at the top of the album.
	 *
	 * @since 1.4.1
	 *
	 * @param   string $album      The HTML output for the Album.
	 * @param   array  $data       Data for the Envira Album.
	 * @return  string  $album      Amended album HTML.
	 */
	public function albums_filter_links_top( $album, $data ) {

		// If tag filtering is not enabled, return early.
		if ( ! Envira_Albums_Shortcode::get_instance()->get_config( 'tags', $data ) || ( ( 1 === $this->is_mobile || true === $this->is_mobile ) && 0 === ( Envira_Albums_Shortcode::get_instance()->get_config( 'tags_mobile', $data ) ) ) ) {
			return $album;
		}

		$position = $this->gallery_shortcode->get_config( 'tags_position', $data );
		if ( 'below' === $position ) {
			return $album; }

		// Now we need to ensure that we actually have tags to process. If we have no tags, return early.
		$tags = $this->get_tags_from_album( $data );
		if ( ! $tags ) {
			return $album;
		}

		// Append the tag filter markup.
		$album .= $this->albums_get_filter_markup( $tags, $data );

		// Filter to allow other addons to add their own filtering.
		$album = apply_filters( 'envira_tags_album_filter_links', $album, $data );

		// Return the amended album HTML.
		return $album;

	}

	/**
	 * Outputs the tag filter links at the bottom of the album.
	 *
	 * @since 1.4.1
	 *
	 * @param   string $album      The HTML output for the Album.
	 * @param   array  $data       Data for the Envira Album.
	 * @return  string  $album      Amended album HTML.
	 */
	public function albums_filter_links_bottom( $album, $data ) {

		// If tag filtering is not enabled, return early.
		if ( ! Envira_Albums_Shortcode::get_instance()->get_config( 'tags', $data ) || ( ( 1 === $this->is_mobile || true === $this->is_mobile ) && 0 === ( Envira_Albums_Shortcode::get_instance()->get_config( 'tags_mobile', $data ) ) ) ) {
			return $album;
		}

		$position = Envira_Albums_Shortcode::get_instance()->get_config( 'tags_position', $data );
		if ( 'below' !== $position ) {
			return $album; }

		// Now we need to ensure that we actually have tags to process. If we have no tags, return early.
		$tags = $this->get_tags_from_album( $data );
		if ( ! $tags ) {
			return $album;
		}

		// Append the tag filter markup.
		$album .= $this->albums_get_filter_markup( $tags, $data );

		// Filter to allow other addons to add their own filtering.
		$album = apply_filters( 'envira_tags_album_filter_links', $album, $data );

		// Return the amended album HTML.
		return $album;

	}

	/**
	 * Adds taxonomy terms to $item, so envira_tags_filter_classes can
	 * output taxonomy term classes against the $item
	 *
	 * @since 1.4.1
	 *
	 * @param array $item       Array of Gallery data.
	 * @param int   $id         Gallery ID.
	 * @param array $data       Array of gallery data.
	 * @param int   $i          The current position in the album.
	 * @return array    $item       Amended item.
	 */
	public function albums_item_data( $item, $id, $data, $i ) {

		// If no more tags, return the classes.
		$terms = wp_get_object_terms( $id, 'envira-category' );
		if ( 0 === count( $terms ) ) {
			return $item;
		}

		// Loop through tags and output them as custom classes.
		foreach ( $terms as $term ) {
			// Set new array key if it doesn't exist.
			if ( ! isset( $item['tags'] ) ) {
				$item['tags'] = array();
			}

			// Add term to array key.
			$item['tags'][ $term->term_id ] = $term->name;
		}

		// Filter to allow other addons to add their own taxonomy terms.
		$item = apply_filters( 'envira_tags_albums_item_data', $item, $id, $data, $i );

		return $item;

	}

	/**
	 * Outputs the filter classes on the album gallery.
	 *
	 * @since 1.4.1
	 *
	 * @param array $classes    Current item classes.
	 * @param array $item       Array of item data.
	 * @param int   $i          The current position in the album.
	 * @param array $data       Array of album data.
	 * @return array    $classes    Amended item classes.
	 */
	public function albums_filter_classes( $classes, $item, $i, $data ) {

		// If filtering is not enabled, do nothing.
		if ( ! Envira_Albums_Shortcode::get_instance()->get_config( 'tags', $data ) ) {
			return $classes;
		}

		// All items need to have envira-tag-all for filtering, even if no classes are attached to the item.
		$classes[] = 'envira-tag-all';

		// If no more tags, return the classes.
		if ( ! isset( $item['tags'] ) || 0 === count( $item['tags'] ) ) {
			return $classes;
		}

		// Loop through tags and output them as custom classes.
		foreach ( $item['tags'] as $term_id => $term_name ) {
			// Get term by name.
			$term = get_term_by( 'name', $term_name, 'envira-category' );
			if ( ! $term ) {
				continue;
			}

			$classes[] = 'envira-category-' . $term->slug;
		}

		// Filter to allow other addons to add their own class terms.
		$classes = apply_filters( 'envira_tags_albums_filter_classes', $classes, $item, $i, $data );

		return $classes;

	}

	/**
	 * Queries a custom album set based on tags.
	 *
	 * @since 1.4.1
	 *
	 * @param array  $tags       Array of tags to use for querying the album.
	 * @param array  $config     Array of album config to use.
	 * @param string $tags_id    Custom ID for this album.
	 * @return bool|array               False if fails to get data, array of data otherwise.
	 */
	public function get_album_by_tags( $tags, $config, $tags_id ) {

		$album = get_transient( '_eg_tags_' . $tags_id );

		// Attempt to return the transient first, otherwise generate the new query to retrieve the data.
		if ( false === $album ) {
			$album = $this->_get_album_by_tags( $tags, $config, $tags_id );
			if ( $album ) {
				$expiration = Envira_Gallery_Common::get_instance()->get_transient_expiration_time( 'envira-tags' );
				set_transient( '_eg_tags_' . $tags_id, $album, $expiration );
			}
		}

		// Return the album data.
		return $album;

	}

	/**
	 * Internal function that queries a custom album set based on tags.
	 *
	 * @since 1.4.0
	 *
	 * @param   array  $tags       Array of tags to use for querying the album.
	 * @param   array  $config     Array of album config to use.
	 * @param   string $tags_id    Custom ID for this album.
	 * @return  bool|array              False if fails to get data, array of data otherwise.
	 */
	public function _get_album_by_tags( $tags, $config, $tags_id ) { // @codingStandardsIgnoreLine

		// Retrieve albums.
		$albums = Envira_Albums::get_instance()->get_albums();
		if ( ! $albums ) {
			return false;
		}

		// Get comparison operator.
		$operator = ( isset( $config['tags_operator'] ) ? $config['tags_operator'] : 'OR' );

		// Loop through the albums and pluck out any galleries that match our tag selection.
		$galleries = array();
		foreach ( (array) $albums as $i => $album ) {
			foreach ( (array) $album['galleryIDs'] as $gallery_id ) {
				// If there are no tags, keep going.
				$terms = wp_get_object_terms( $gallery_id, 'envira-category' );

				if ( 0 === count( $terms ) ) {
					continue;
				}

				// Loop through the tags to see if we have a match.
				switch ( $operator ) {
					/**
					* Image must have all tags
					*/
					case 'AND':
						$matched = true;

						// Build array of terms.
						$terms_arr = array();
						foreach ( $terms as $term ) {
							$terms_arr[] = $term->slug;
						}

						// Iterate through requested tags.
						foreach ( $tags as $tag ) {
							// Does this tag exist in this image?
							if ( ! in_array( $tag, $terms_arr, true ) ) {
								// No, it doesn't - bail.
								$matched = false;
								break;
							}
						}

						// If here and $matched, all tags exist in this gallery.
						if ( $matched ) {
							// HERE.
							$galleries[ $gallery_id ] = $albums[ $i ]['gallery'][ $gallery_id ];
						}

						break;

					/**
					* Image can have any tag(s)
					*/
					case 'OR':
					default:
						foreach ( $terms as $term ) {

							if ( in_array( $term->name, $tags, true ) || in_array( $term->slug, $tags, true ) ) {

								$images[ $gallery_id ] = $galleries[ $i ]['gallery'][ $gallery_id ];
								break; // Break the foreach.

							} else {

								// check for special non-English characters only if they exist as a last resort.
								$revised_tags_array = array();

								foreach ( $tags as $tag ) {
									$revised_tags_array[] = sanitize_tag( $tag );
								}
								$new_name = sanitize_tag( $term->name );

								if ( in_array( $new_name, $revised_tags_array, true ) ) {
									$images[ $gallery_id ] = $galleries[ $i ]['gallery'][ $gallery_id ];
								}
							}
						}
						break; // Break the switch.
				}
			}
		}

		// If the images array is still empty, return false, otherwise return the images.
		if ( empty( $images ) ) {
			return false;
		} else {
			// We are good to go. Prepare the data and return it with a filter.
			$data['id']      = $tags_id;
			$data['config']  = $config;
			$data['gallery'] = $images;

			return apply_filters( 'envira_tags_get_gallery_by_tags', $data, $config, $tags_id );
		}

	}

	/**
	 * Retrieves a unique list of tags for an album.
	 *
	 * @since 1.4.1
	 *
	 * @param   array  $album_data     Array of album data to use.
	 * @param   string $taxonomy      Taxonomy to check.
	 * @return  bool|array            False if no tags are found, array of tags otherwise.
	 */
	private function get_tags_from_album( $album_data, $taxonomy = 'envira-category' ) {

		// Loop through the images in the gallery and grab tags.
		$tags     = array();
		$has_tags = false;

		foreach ( (array) $album_data['galleryIDs'] as $gallery_id ) {
			// If there are no tags, keep going.
			$terms = wp_get_object_terms( $gallery_id, $taxonomy );
			if ( 0 === count( $terms ) ) {
				continue;
			}

			// Store the tags and set our flag to true.
			foreach ( $terms as $term ) {
				if ( ! empty( $term->name ) ) {
					$tags[ $term->slug ] = str_replace( '&amp;', '&', $term->name );
				}
			}

			$has_tags = true;
		}

		// If we have no tags, return false.
		if ( ! $has_tags ) {
			return false;
		}

		// If the album specifies the "Tags to include in Filtering" option (tags_filter), only return those tags in the tag list.
		if ( 'envira-category' === $taxonomy && isset( $album_data['config']['tags_filter'] ) && ! empty( $album_data['config']['tags_filter'] ) ) {
			// Get filtered tags and check we have at least one tag specified.
			$filtered_tags = explode( ',', $album_data['config']['tags_filter'] );

			if ( count( $filtered_tags ) > 0 ) {
				$tags = array();

				// Iterate through filtered tags and check if each tag exists in $image_tags array.
				// If so, add to our final $tags array.
				foreach ( $filtered_tags as $tag ) {

					$tag = get_term_by( 'name', trim( $tag ), $taxonomy );
					if ( $tag && isset( $tag->slug ) && ! in_array( $tag->slug, $tags, true ) ) {

						$tags[ $tag->slug ] = $tag->name;

					}
				}
			}
		}

		// Depending on the sort order, rearrange the tags now.
		$tags_sorting = Envira_Albums_Shortcode::get_instance()->get_config( 'tags_sorting', $album_data );
		switch ( $tags_sorting ) {

			/**
			* Manual
			*/
			case 'manual':
				// Get manual sorting order.
				$tags_manual_sorting = Envira_Albums_Shortcode::get_instance()->get_config( 'tags_manual_sorting', $album_data );
				if ( ! empty( $tags_manual_sorting ) ) {
					$tags = $tags_manual_sorting;
				}
				break;

			/**
			* Descending.
			*/
			case 'desc':
				arsort( $tags );
				break;

			/**
			* Ascending.
			*/
			default:
			case '':
				asort( $tags );
				break;

		}

		// Return filtered tags.
		return apply_filters( 'envira_tags_album_tags', array_unique( $tags ), $album_data );

	}

	/**
	 * Retrieves the custom markup for the tag filter list.
	 *
	 * @since 1.4.1
	 *
	 * @param array $tags   Array of tags to use for filtering.
	 * @param array $data   Array of gallery data.
	 * @return string           Custom markup for the tag filter list.
	 */
	private function albums_get_filter_markup( $tags, $data ) {

		global $post;

		// Get instance.
		$instance = Envira_Albums_Shortcode::get_instance();

		$markup = '<ul id="envira-tags-filter-list-' . sanitize_html_class( $data['id'] ) . '" class="envira-tags-filter-list envira-clear">';

		// Add the 'All' tag if enabled.
		if ( $instance->get_config( 'tags_all_enabled', $data ) ) {
			$markup     .= '<li id="envira-tag-filter-all" class="envira-tags-filter">';
				$markup .= '<a href="' . get_permalink( $post->ID ) . '" class="envira-tags-filter-link envira-tags-filter-active" title="' . __( 'Filter by All', 'envira-tags' ) . '" data-envira-filter=".envira-tag-all">' . $instance->get_config( 'tags_all', $data ) . '</a>';
			$markup     .= '</li>';
		}

		// Loop through the tags and add them to the filter list.
		foreach ( $tags as $index => $slug ) {
			// Get the tag's name by slug.
			$tag = get_term_by( 'slug', $slug, 'envira-category' );

			// Bail if we can't find the term.
			if ( ! $tag ) {
				continue;
			}

			// Build non-JS URL.
			$url = add_query_arg(
				array(
					'envira-category' => $tag->slug, // sanitize_html_class used to be here, but kills non-english.
				),
				get_permalink( $post->ID )
			);

			if ( ( isset( $_REQUEST[ $this->slug_name ] ) && sanitize_text_field( wp_unslash( $_REQUEST[ $this->slug_name ] ) ) === $slug ) || ( isset( $_REQUEST['envira-category'] ) && sanitize_text_field( wp_unslash( $_REQUEST['envira-category'] ) ) ) ) { // @codingStandardsIgnoreLine - no nonce in querystring
				$css_active = 'envira-tags-filter-active';
			} else {
				$css_active = false;
			}

			// Append anchor to the URL if scroll to gallery is enabled.
			if ( $this->gallery_shortcode->get_config( 'tags_scroll', $data ) ) {
				$url .= '#envira-gallery-wrap-' . $data['id'];
			}

			// Output list item.
			$markup .= '<li id="envira-tag-filter-' . sanitize_html_class( $tag->slug ) . '" class="envira-tags-filter">';
				// translators: %s.
				$markup     .= '<a href="' . $url . '" class="envira-tags-filter-link ' . $css_active . '" title="' . sprintf( __( 'Filter by %s', 'envira-tags' ), $tag->name ) . '" data-envira-filter=".envira-category-' . sanitize_html_class( $tag->slug ) . '">';
					$markup .= $tag->name;
				$markup     .= '</a>';
			$markup         .= '</li>';
		}

		// Close up the markup.
		$markup .= '</ul>';

		return apply_filters( 'envira_tags_album_filter_markup', $markup, $tags, $data );

	}

	/**
	 * Returns special santization of tag, which might need tweaking as foreign characters are tested.
	 *
	 * @since 1.3.0
	 *
	 * @param string $tag   Un-sanitized string.
	 * @return string Tag
	 */
	public function sanitize_tag( $tag ) {
		$tag = sanitize_title( $tag );

		// @codingStandardsIgnoreStart
		return preg_replace( '/[^A-Za-z0-9 -]/', '', $tag );
		// @codingStandardsIgnoreEnd

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.3.0
	 *
	 * @return object The Envira_Tags_Shortcode object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Tags_Shortcode ) ) {
			self::$instance = new Envira_Tags_Shortcode();
		}

		return self::$instance;

	}

}

// Load the shortcode class.
$envira_tags_shortcode = Envira_Tags_Shortcode::get_instance();
