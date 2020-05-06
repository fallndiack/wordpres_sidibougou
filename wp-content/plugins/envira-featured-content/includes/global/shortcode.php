<?php
/**
 * Shortcode class.
 *
 * @since 1.0.0
 *
 * @package Envira_Featured_Content_Shortcode
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
 * @package Envira_Featured_Content_Shortcode
 * @author  Envira Team
 */
class Envira_Featured_Content_Shortcode {

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
	 * Holds the shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $shortcode;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Get base instance.
		$this->base = Envira_Featured_Content::get_instance();
		if ( ! class_exists( 'Envira_Gallery_Shortcode' ) ) {
			return;
		}
		$this->shortcode = Envira_Gallery_Shortcode::get_instance();

		// Inject Images into Albums Admin
		// This allows the user to choose a cover image.
		add_filter( 'envira_albums_metaboxes_get_gallery_data', array( $this, 'inject_images' ), 10, 2 );

		// Inject Images into Album Lightbox.
		add_filter( 'envira_albums_shortcode_gallery', array( $this, 'inject_images' ), 10, 2 );

		// Actions and filters.
		add_filter( 'envira_gallery_output_classes', array( $this, 'output_classes' ), 10, 2 );
		add_filter( 'envira_gallery_pre_data', array( $this, 'inject_images' ), 10, 2 );
		add_filter( 'envira_images_pre_data', array( $this, 'inject_images' ), 10, 2 );
		add_filter( 'envira_gallery_output_item_classes', array( $this, 'output_item_classes' ), 10, 4 );
		add_filter( 'envira_gallery_link_href', array( $this, 'change_gallery_src' ), 10, 6 );
		add_filter( 'envira_gallery_item_before_link', array( $this, 'change_item_data' ), 10, 5 );
		add_filter( 'envira_gallery_item_before_link', array( $this, 'change_link_new_window' ), 10, 5 );
		add_filter( 'envira_featured_content_truncate_HTML', array( $this, 'envira_featured_content_truncate_html_fix_characters' ), 10, 3 );

	}

	/**
	 * Add a filter to control the HTML filtering
	 *
	 * @since 1.0.0
	 *
	 * @param string $html  The HTML.
	 * @param int    $limit Character limit.
	 * @param string $ellipsis Character.
	 * @return string $html
	 */
	public function envira_featured_content_truncate_html_fix_characters( $html, $limit, $ellipsis ) {

		$html = str_replace( '£', '&pound;', $html );

		return $html;

	}

	/**
	 * Disables lightbox link if 'link image to post URL' is checked
	 *
	 * @since 1.0.0
	 *
	 * @param array  $item  Item.
	 * @param array  $data  Galery data.
	 * @param string $id  ID.
	 * @param int    $i  Item.
	 * @param array  $is_mobile  Is Mobile.
	 * @return array            Dynamic Gallery Types
	 */
	public function change_link_new_window( $item, $data, $id, $i, $is_mobile ) {

		if ( 'fc' !== $data['config']['type'] ) {
			return $item;
		}

		$fc_post_types = envira_get_config( 'fc_post_types', $data );

		if ( empty( $fc_post_types ) || envira_get_config( 'fc_post_url', $data ) === 0 || envira_get_config( 'fc_post_new_window', $data ) === 0 ) {
			return $item;
		}

		$item['link_new_window'] = 1;

		return $item;

	}


	/**
	 * Disables lightbox link if 'link image to post URL' is checked
	 *
	 * @since 1.0.0
	 *
	 * @param array  $item  Item.
	 * @param array  $data  Galery data.
	 * @param string $id  ID.
	 * @param int    $i  Item.
	 * @param array  $is_mobile  Is Mobile.
	 * @return array            Dynamic Gallery Types
	 */
	public function change_item_data( $item, $data, $id, $i, $is_mobile ) {

		if ( 'fc' !== $data['config']['type'] ) {
			return $item;
		}

		$fc_post_types = envira_get_config( 'fc_post_types', $data );

		if ( empty( $fc_post_types ) || envira_get_config( 'fc_post_url', $data ) === 0 ) {
			return $item;
		}

		$item['link_type'] = 'external'; // make this external, overriding the default.

		return $item;

	}

	/**
	 * Changes URL from image to post URL if the user has the setting active (fc_post_url)
	 *
	 * @since 1.0.0
	 *
	 * @param string  $item_src           Item Src.
	 * @param array   $data             Gallery Data.
	 * @param int     $id   Image ID.
	 * @param array   $item   Item Data.
	 * @param int     $i   Index.
	 * @param boolean $is_mobile   Is Mobile.
	 * @return array            Dynamic Gallery Types
	 */
	public function change_gallery_src( $item_src, $data, $id, $item, $i, $is_mobile ) {

		if ( 1 !== $this->shortcode->get_config( 'fc_post_url', $data ) ) {
			return $item_src;
		}

		if ( ! empty( $item['link'] ) ) {
			$item_src = esc_url( $item['link'] );
		}

		return $item_src;

	}

	/**
	 * Adds shortcode and function support for [envira_dynamic id="fc-name"]
	 *
	 * @since 1.0.0
	 *
	 * @param array $types  Dynamic Gallery Types.
	 * @return array            Dynamic Gallery Types
	 */
	public function register_dynamic_gallery_types( $types ) {

		$types['envira_dynamic_get_fc_images'] = '#^fc-#';

		return $types;

	}

	/**
	 * Changes the Dynamic Gallery to an FC Gallery if the Dynamic Gallery's ID
	 * matches that of an Envira Featured Content Gallery
	 *
	 * Also allows the Dynamic Addon shortcode to override configuration settings
	 * for a specific Featured Content Gallery
	 *
	 * @param array $data           Dynamic Gallery Config.
	 * @param int   $id             FC Gallery ID.
	 * @param array $dynamic_data   Images (will be empty).
	 * @return array                Featured Content Gallery Config
	 */
	public function change_gallery_type( $data, $id, $dynamic_data ) {

		// Get Gallery ID.
		$gallery_id = explode( '-', $id );
		if ( count( $gallery_id ) === 1 ) {
			return $data;
		}
		$id = $gallery_id[1];

		// Check ID is an integer.
		if ( ! is_numeric( $id ) ) {
			return $data;
		}

		// Get Gallery Data.
		$gallery_data = apply_filters( 'envira_dynamic_get_fc_image_data', Envira_Gallery::get_instance()->get_gallery( $id ), $id );
		if ( ! $gallery_data ) {
			return $data;
		}

		// Replace config options in $gallery_data with $data.
		$ignored_config_keys = array(
			'title',
			'slug',
			'classes',
			'type',
			'dynamic',
		);
		foreach ( $data['config'] as $key => $value ) {
			// Skip ignored config keys.
			if ( in_array( $key, $ignored_config_keys, true ) ) {
				continue;
			}

			// Replace $gallery_data['config'][$key]
			// Some FC keys need to be arrays.
			switch ( $key ) {
				case 'fc_post_types':
				case 'fc_terms':
				case 'fc_inc_ex':
					// Value needs to be an array.
					$gallery_data['config'][ $key ] = array(
						$data['config'][ $key ],
					);
					break;
				default:
					// Value can be anything.
					$gallery_data['config'][ $key ] = $data['config'][ $key ];
					break;
			}
		}

		// Return.
		return $gallery_data;

	}

	/**
	 * Adds a custom gallery class to denote a featured content gallery.
	 *
	 * @since 1.0.0
	 *
	 * @param array $classes  Array of gallery classes.
	 * @param array $data     Array of gallery data.
	 * @return array $classes Amended array of gallery classes.
	 */
	public function output_classes( $classes, $data ) {

		if ( 'fc' !== $this->shortcode->get_config( 'type', $data ) ) {
			return $classes;
		}

		// Add custom FC class.
		$classes[] = 'envira-gallery-featured-content-gallery';
		return $classes;

	}

	/**
	 * Adds Post and Taxonomy Term classes to each slider item
	 *
	 * @since 1.0.0
	 *
	 * @param array $classes    CSS Classes.
	 * @param array $item       Image.
	 * @param int   $i          Index.
	 * @param array $data       Gallery Config.
	 * @return array             CSS Classes
	 */
	public function output_item_classes( $classes, $item, $i, $data ) {

		// Check if any classes are defined for the image.
		if ( ! isset( $item['classes'] ) ) {
			return $classes;
		}

		// Append classes array to the existing classes.
		$classes = array_merge( $classes, $item['classes'] );

		// Return.
		return $classes;

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

		// Return early if not a Featured Content gallery.
		if ( 'fc' !== $this->shortcode->get_config( 'type', $data ) ) {
			return $data;
		}

		// Grab the FC data.
		$fc_images = $this->get_fc_data( $id, $data );
		if ( ! $fc_images ) {
			return $data;
		}

		// Insert images into gallery.
		$data['gallery'] = $fc_images;

		return $data;

	}

	/**
	 * Attempts to get just the total count of Featured Content images
	 *
	 * If transient does not exist, performs a live query and caches the results
	 *
	 * @since 1.0.0
	 *
	 * @param int   $id     Gallery ID.
	 * @param array $data   Gallery Data.
	 * @return array            Featured Content Images
	 */
	public function get_fc_data_total( $id, $data ) {

		// Prepare and run the query for grabbing our featured content.
		$images = $this->get_fc_data( $id, $data );
		return count( $images );

	}

	/**
	 * Attempts to get Featured Content image data from transient/cache
	 *
	 * If transient does not exist, performs a live query and caches the results
	 *
	 * @since 1.0.0
	 *
	 * @param int   $id     Gallery ID.
	 * @param array $data   Gallery Data.
	 * @return array            Featured Content Images
	 */
	public function get_fc_data( $id, $data ) {

		// Prepare and run the query for grabbing our featured content.
		$query = $this->prepare_query( $id, $data );
		$posts = $this->get_data( $query, $id, $data );

		// If there was an error with the query, simply return default data.
		if ( ! $posts ) {
			return $data;
		}

		// Build an array of images for this Gallery.
		$images = array();

		// Loop through and insert the Featured Content data.
		foreach ( $posts as $i => $post ) {

			// Prepare variables.
			$id             = ! empty( $post->ID ) ? $post->ID : $i;
			$prep           = array();
			$prep['status'] = 'active';
			$prep['src']    = $this->get_featured_image( $post, $data );
			$prep['title']  = $post->post_title;
			$prep['link']   = $this->shortcode->get_config( 'fc_post_url', $data ) ? get_permalink( $post->ID ) : $prep['src'];
			$prep['alt']    = $post->post_title;
			// determine if user wants post_excerpt, post_content, or title for caption.
			switch ( $this->shortcode->get_config( 'fc_content_type', $data ) ) {
				case 'post_excerpt':
					$prep['caption'] = $post->post_excerpt;
					break;
				case 'post_content':
					$pcontent = strip_shortcodes( $post->post_content );
					// Strip images.
					$pattern         = '/<img[^>]*src="([^"]*)[^>]*>/i';
					$pcontent        = preg_replace( $pattern, '', $pcontent );
					$pcontent        = Envira_Featured_Content_Truncate_HTML::truncate_words( $pcontent, $this->shortcode->get_config( 'fc_content_length', $data ), ( $this->shortcode->get_config( 'fc_content_ellipses', $data ) ? '...' : '' ) );
					$prep['caption'] = $pcontent;
					break;
				case 'post_title':
					$prep['caption'] = $post->post_title;
					break;
				default:
					$prep['caption'] = '';
					break;
			}
			$prep['thumb']  = $prep['src'];
			$prep['width']  = 0;
			$prep['height'] = 0;

			/* if the user is requesting a crop, maybe use the width/height they entered */

			if ( $this->shortcode->get_config( 'crop', $data ) && $this->shortcode->get_config( 'crop_width', $data ) && $this->shortcode->get_config( 'image_size', $data ) !== 'full' ) {
				$prep['width'] = $this->shortcode->get_config( 'crop_width', $data );
			} elseif ( $this->shortcode->get_config( 'columns', $data ) !== 0 && $this->shortcode->get_config( 'image_size', $data ) && $this->shortcode->get_config( 'image_size', $data ) !== 'full' && $this->shortcode->get_config( 'crop_width', $data ) && $this->shortcode->get_config( 'crop_height', $data ) ) {
				$prep['width'] = $this->shortcode->get_config( 'crop_width', $data );
			}

			if ( $this->shortcode->get_config( 'crop', $data ) && $this->shortcode->get_config( 'crop_width', $data ) && $this->shortcode->get_config( 'image_size', $data ) !== 'full' ) {
				$prep['height'] = $this->shortcode->get_config( 'crop_height', $data );
			} elseif ( $this->shortcode->get_config( 'columns', $data ) !== 0 && $this->shortcode->get_config( 'image_size', $data ) && $this->shortcode->get_config( 'image_size', $data ) !== 'full' && $this->shortcode->get_config( 'crop_width', $data ) && $this->shortcode->get_config( 'crop_height', $data ) ) {
				$prep['height'] = $this->shortcode->get_config( 'crop_height', $data );
			}

			if ( isset( $data['config']['image_size'] ) ) {
				$image_data = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), $data['config']['image_size'] );
			} else {
				$image_data = false;
			}

			if ( 0 === $prep['width'] && 0 === $prep['height'] && ! $image_data && ! empty( $prep['src'] ) ) {
				/* since many featured images are pulled from the post_content, we have NO - ZERO - NADA info, so by full image dimensions if we have too */
				$sizes = getimagesize( $prep['src'] );
				if ( $sizes ) {
					$prep['width']  = $sizes[0];
					$prep['height'] = $sizes[1];
				} else {
					continue;
				}
			} elseif ( $image_data ) {
				$prep['width']  = $image_data[1];
				$prep['height'] = $image_data[2];
			}

			// If there is no src - even a default one - then we don't add this post.
			if ( ! $prep['src'] ) {
				continue;
			}

			// Add some CSS classes.
			$prep['classes'] = get_post_class( '', $id );

			// Prepend 'envira-gallery-' to each CSS class, so we don't start apply theme styling.
			if ( is_array( $prep['classes'] ) ) {
				foreach ( $prep['classes'] as $key => $class ) {
					$prep['classes'][ $key ] = 'envira-gallery-' . $class;
				}
			}

			// Allow image to be filtered for each image.
			$prep = apply_filters( 'envira_featured_content_image', $prep, $posts, $data, $post );

			// Insert the image into the slider.
			$images[ $id ] = $prep;
		}

		return $images;

	}

	/**
	 * Get size information for all currently-registered image sizes.
	 *
	 * @global $_wp_additional_image_sizes
	 * @uses   get_intermediate_image_sizes()
	 * @return array $sizes Data for all currently-registered image sizes.
	 */
	public function get_image_sizes() {
		global $_wp_additional_image_sizes;

		$sizes = array();

		foreach ( get_intermediate_image_sizes() as $_size ) {
			if ( in_array( $_size, array( 'thumbnail', 'medium', 'medium_large', 'large' ), true ) ) {
				$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
				$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
				$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
				$sizes[ $_size ] = array(
					'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
					'height' => $_wp_additional_image_sizes[ $_size ]['height'],
					'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
				);
			}
		}

		return $sizes;
	}

	/**
	 * Get size information for a specific image size.
	 *
	 * @uses   get_image_sizes()
	 * @param  string $size The image size for which to retrieve data.
	 * @return bool|array $size Size data about an image size or false if the size doesn't exist.
	 */
	public function get_image_size( $size ) {
		$sizes = $this->get_image_sizes();

		if ( isset( $sizes[ $size ] ) ) {
			return $sizes[ $size ];
		}

		return false;
	}

	/**
	 * Prepares the query args for the featured content query.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $id   The current gallery ID.
	 * @param array $data Array of gallery data.
	 * @return array      Array of query args for the featured content gallery.
	 */
	private function prepare_query( $id, $data ) {

		// Prepare vairables.
		$query_args = array();

		// Set any default query args that are not appropriate for our query.
		$query_args['post_parent']    = null;
		$query_args['post_mime_type'] = null;
		$query_args['cache_results']  = false;
		$query_args['no_found_rows']  = true;

		// Set our user defined query args.
		$query_args['post_type']      = (array) $this->shortcode->get_config( 'fc_post_types', $data );
		$query_args['posts_per_page'] = $this->shortcode->get_config( 'fc_number', $data );
		$query_args['orderby']        = $this->shortcode->get_config( 'fc_orderby', $data );
		$query_args['order']          = $this->shortcode->get_config( 'fc_order', $data );
		$query_args['offset']         = $this->shortcode->get_config( 'fc_offset', $data );
		$query_args['post_status']    = $this->shortcode->get_config( 'fc_status', $data );

		// Set meta_key if sorting by meta_value or meta_value_num.
		if ( 'meta_value' === $query_args['orderby'] || 'meta_value_num' === $query_args['orderby'] ) {
			$query_args['meta_key'] = $this->shortcode->get_config( 'fc_meta_key', $data ); // @codingStandardsIgnoreLine
		}

		// Set post__in and/or post__not_in query params.
		$include_posts = $this->shortcode->get_config( 'fc_include_posts', $data );
		if ( ! empty( $include_posts ) ) {
			$query_args['post__in'] = array_map( 'absint', (array) $include_posts );
		}
		$exclude_posts = $this->shortcode->get_config( 'fc_exclude_posts', $data );
		if ( ! empty( $exclude_posts ) ) {
			$query_args['post__not_in'] = array_map( 'absint', (array) $exclude_posts );
		}

		// Set our custom taxonomy query parameters if necessary.
		$terms = $this->shortcode->get_config( 'fc_terms', $data );

		if ( ! is_array( $terms ) ) {
			$temp = array();
			if ( strtolower( $terms ) === 'array' ) { // this fixes legacy on testing.
				$terms = array();
			}
			$temp[0] = $terms;
			$terms   = $temp;
		}
		$terms    = array_filter( $terms );
		$operator = $this->shortcode->get_config( 'fc_terms_relation', $data );

		if ( ! empty( $terms ) ) {
			// Set our taxonomy relation parameter.
			$relation['relation'] = 'AND';

			// Loop through each term and parse out the data.
			foreach ( $terms as $term ) {
				$term_data    = explode( '|', $term );
				$taxonomies[] = $term_data[0];
				$terms[]      = $term_data;
			}

			$taxonomies = array_filter( $taxonomies );

			// if the array is empty, clear it.
			if ( ! empty( $taxonomies ) && ! empty( $taxonomies[0] ) ) {

				// Loop through each taxonony and build out the taxonomy query.
				foreach ( array_unique( $taxonomies ) as $tax ) {
					$tax_terms = array();
					foreach ( $terms as $term ) {
						if ( ! empty( $term ) && $tax === $term[0] && isset( $term[2] ) ) {
							$tax_terms[] = $term[2];
						}
					}

					$relation[] = array(
						'taxonomy'         => $tax,
						'field'            => 'slug',
						'terms'            => $tax_terms,
						'operator'         => is_array( $tax_terms ) && empty( $operator ) ? 'IN' : $operator, /* if $tax_terms is an array, and operator is empty, force operator to 'IN' */
						'include_children' => false,
					);
				}
				$query_args['tax_query'] = $relation; // @codingStandardsIgnoreLine

			}
		}

		// Allow dev to optionally allow query filters.
		$query_args['suppress_filters'] = apply_filters( 'envira_featured_content_suppress_filters', true, $query_args, $id, $data );

		// Filter and return the query args.
		return apply_filters( 'envira_featured_content_query_args', $query_args, $id, $data );

	}

	/**
	 * Runs and caches the query to grab featured content data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $query Array of query args.
	 * @param mixed $id   The current slider ID.
	 * @param array $data Array of slider data.
	 * @return bool|array False if no items founds, array of data on success.
	 */
	public function get_data( $query, $id, $data ) {

		// If using a random selection for posts, or accessing through the WordPress Admin, don't cache the query.
		if ( is_admin() || ( isset( $query['orderby'] ) && 'rand' === $query['orderby'] ) ) {
			$fc_data = $this->_get_data(
				$query,
				$id,
				$data
			);
			return maybe_unserialize( $fc_data );
		}

		// Attempt to return the transient first, otherwise generate the new query to retrieve the data.
		// if ( false === ( $fc_data = get_transient( '_envira_featured_content_' . $id ) ) ) {.
			$fc_data = $this->_get_data( $query, $id, $data );
		if ( $fc_data ) {
			set_transient( '_envira_featured_content_' . $id, maybe_serialize( $fc_data ), DAY_IN_SECONDS );
		}
		// }
		// Return the slider data.
		return maybe_unserialize( $fc_data );

	}

	/**
	 * Performs the custom query to grab featured content if the transient doesn't exist.
	 *
	 * @since 1.0.0
	 *
	 * @param array $query Array of query args.
	 * @param mixed $id   The current gallery ID.
	 * @param array $data Array of gallery data.
	 * @return array|bool Array of data on success, false on failure.
	 */
	public function _get_data( $query, $id, $data ) { // @codingStandardsIgnoreLine

		// Get posts.
		$posts = get_posts( $query );

		// If sticky posts are enabled, re-query with sticky post IDs prepending
		// above $posts IDs.  Note that get_posts does not automatically prepend
		// sticky posts to the resultset, so we do this manually now.
		$sticky          = $this->shortcode->get_config( 'fc_sticky', $data );
		$sticky_post_ids = get_option( 'sticky_posts' );

		if ( $sticky && is_array( $sticky_post_ids ) && count( $sticky_post_ids ) > 0 && count( $posts ) > 0 ) {
			// Get all Post IDs from above query.
			$post_ids = array();
			foreach ( $posts as $post ) {
				$post_ids[] = $post->ID;
			}

			// Rerun get_posts query to get posts by ID (sticky post IDs first) - this ensures
			// sticky posts appear first.
			$final_query             = $query;
			$final_query['orderby']  = 'post__in';
			$final_query['post__in'] = array_merge( $sticky_post_ids, $post_ids );
			$posts                   = get_posts( $final_query );
		}

		// If there is an error or no posts are returned, return false.
		if ( ! $posts || empty( $posts ) ) {
			return false;
		}

		// Return the post data.
		return apply_filters( 'envira_featured_content_post_data', $posts, $query, $id, $data );

	}



	/**
	 * Retrieves the featured image for the specified post.
	 *
	 * @since 1.0.0
	 *
	 * @param array $post Post data.
	 * @param array $data   Gallery data.
	 * @return string The featured image URL to use for the slide.
	 */
	public function get_featured_image( $post, $data ) {

		// Attempt to grab the featured image for the post.
		$thumb_id = apply_filters( 'envira_featured_content_thumbnail_id', get_post_thumbnail_id( $post->ID ), $post, $data );
		$src      = '';

		// If we have been able to get the featured image ID, return the image based on that.
		if ( $thumb_id ) {
			$size  = $this->shortcode->get_config( 'size', $data );
			$image = wp_get_attachment_image_src( $thumb_id, ( 'default' !== $size ? $size : 'full' ) );
			if ( ! $image || empty( $image[0] ) ) {
				$fallback = $this->shortcode->get_config( 'fc_fallback', $data );
				if ( ! empty( $fallback ) ) {
					$src = esc_url( $fallback );
				} else {
					$src = '';
				}
			} else {
				$src = $image[0];
			}
		} else {
			// Attempt to grab the first image from the post if no featured image is set.
			preg_match_all( '|<img.*?src=[\'"](.*?)[\'"].*?>|i', get_post_field( 'post_content', $post->ID ), $matches );

			// If we have found an image, use that image, otherwise attempt the fallback URL.
			if ( ! empty( $matches[1][0] ) ) {
				$src = esc_url( $matches[1][0] );
			} else {
				$fallback = $this->shortcode->get_config( 'fc_fallback', $data );
				if ( ! empty( $fallback ) ) {
					$src = esc_url( $fallback );
				} else {
					return false;
				}
			}
		}

		// Return the image and allow filtering of the URL.
		return apply_filters( 'envira_featured_content_image_src', $src, $post, $data );

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Featured_Content_Shortcode object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Featured_Content_Shortcode ) ) {
			self::$instance = new Envira_Featured_Content_Shortcode();
		}

		return self::$instance;

	}

}

// Load the metabox class.
$envira_featured_content_shortcode = Envira_Featured_Content_Shortcode::get_instance();
