<?php
/**
 * Shortcode class.
 *
 * @since 1.0.0
 *
 * @package Envira_Pagination
 * @author  Envira Gallery Team <support@enviragallery.com>
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
class Envira_Pagination_Shortcode {

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
	 * Standalone: Holds the gallery/album data.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $data;

	/**
	 * Standalone: Current Page for a Gallery/Album
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public $current_page = 1;

	/**
	 * Standalone: Total Pages for a Gallery/Album
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public $total_pages = 1;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Load base class.
		$this->base = Envira_Pagination::get_instance();

		$version = ( defined( 'ENVIRA_DEBUG' ) && 'true' === ENVIRA_DEBUG ) ? $version = time() . '-' . $this->base->version : $this->base->version;

		// Register script.
		wp_register_script( $this->base->plugin_slug . '-script', plugins_url( 'assets/js/min/envira-pagination-min.js', $this->base->file ), array( 'jquery' ), $version, true );

		wp_localize_script(
			$this->base->plugin_slug . '-script',
			'envira_pagination',
			array(
				'ajax'  => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'envira-pagination' ),
			)
		);

		// Standalone: Add rel prev/next links to Galleries and Albums.
		add_action( 'envira_standalone_gallery_pre_get_posts', array( $this, 'maybe_rel_gallery' ) );
		add_action( 'envira_standalone_album_pre_get_posts', array( $this, 'maybe_rel_album' ) );

		// Output Pagination.
		add_filter( 'envira_gallery_pre_data', array( $this, 'paginate_gallery' ), 10, 2 );
		add_filter( 'envira_albums_pre_data', array( $this, 'paginate_album' ), 10, 2 );
		add_filter( 'envira_gallery_get_transient_markup', array( $this, 'maybe_fragement_cache_gallery' ), 10, 2 );
		add_filter( 'envira_albums_get_transient_markup', array( $this, 'maybe_fragement_cache_album' ), 10, 2 );
		add_filter( 'envira_gallery_output_image_attr', array( $this, 'gallery_build_data_envira_item_src' ), 10, 5 );

		// Gallery: Display all images in Lightbox.
		add_filter( 'envira_load_all_images_lightbox', array( $this, 'maybe_display_all_images_in_lightbox' ), 10, 2 );

		add_action( 'wp_head', array( $this, 'nocache' ), 9 );

	}

	/**
	 * Outputs an extra data attribute which is needed for 'display all images' pagination
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
	public function gallery_build_data_envira_item_src( $output, $id, $item, $data, $i ) {

		// bail if pagination not enabled.
		if ( ! envira_get_config( 'pagination', $data ) || ! envira_get_config( 'pagination_lightbox_display_all_images', $data ) ) {
			return $output;
		}

		$output .= ' data-envira-item-src="' . $item['src'] . '" ';

		return $output;

	}

	/**
	 * Disable cache?
	 *
	 * @since 1.0.0
	 */
	public function nocache() {

		if ( ! is_page() || ! is_single() ) {
			return false;
		}

		$post = get_post();

		if ( get_post_type( $post->ID ) === 'envira' ) {

			$gallery_data = envira_get_gallery( $post->ID );
			$pagination   = envira_get_config( 'pagination', $gallery_data );

			if ( $pagination && ! headers_sent() ) {

				nocache_headers();
				return true;

			}
		} else {

			// Check content for shortcodes.
			if ( ! has_shortcode( $post->post_content, 'envira-gallery' ) ) {
				return false;
			}

			// Content has Envira shortcode(s)
			// Extract them to get Gallery IDs.
			$pattern = '\[(\[?)(envira\-gallery)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
			if ( ! preg_match_all( '/' . $pattern . '/s', $post->post_content, $matches ) ) {
				return false;
			}
			if ( ! is_array( $matches[3] ) ) {
				return false;
			}

			// Iterate through shortcode matches, extracting the gallery ID and storing it in the meta.
			$gallery_ids = array();
			foreach ( $matches[3] as $shortcode ) {
				// Grab ID.
				$gallery_ids[] = preg_replace( '/[^0-9]/', '', $shortcode );
			}

			// Check we found gallery IDs.
			if ( ! $gallery_ids ) {
				return false;
			}

			// Iterate through each gallery.
			foreach ( $gallery_ids as $gallery_id ) {
				$gallery_data = envira_get_gallery( $gallery_id );
				$pagination   = envira_get_config( 'pagination', $gallery_data );

				if ( $pagination && ! headers_sent() ) {

					nocache_headers();
					return true;

				}
			}
		}

	}

	/**
	 * Temporary function that will not cache certain pagination based on settings
	 *
	 * @since 1.5.0
	 *
	 * @param string $transient Transient data.
	 * @param array  $album_data Album data.
	 */
	public function maybe_fragement_cache_album( $transient, $album_data ) {

		$pagination = envira_get_config( 'pagination', $album_data );

		if ( ! $pagination ) {
			return $transient;
		}

		return false;

	}

	/**
	 * Temporary function that will not cache certain pagination based on settings
	 *
	 * @since 1.5.0
	 *
	 * @param string $transient Transient data.
	 * @param array  $gallery_data Album data.
	 */
	public function maybe_fragement_cache_gallery( $transient, $gallery_data ) {

		$pagination = envira_get_config( 'pagination', $gallery_data );

		if ( ! $pagination ) {
			return $transient;
		}

		return false;

	}

	/**
	 * Called if the Standalone Addon is going to load a Gallery
	 *
	 * @since 1.0.4
	 *
	 * @param object $query WP_Query.
	 */
	public function maybe_rel_gallery( $query ) {

		if ( ! isset( $query->query['name'] ) ) {
			return;
		}

		// Check if Pagination is enabled on this Standalone Gallery
		// If so, add rel next/prev to the <head> of the site.
		$data          = envira_get_gallery_by_slug( $query->query['name'] );
		$active_images = array();

		// Check we found a valid Gallery.
		if ( ! $data || ! is_array( $data ) ) {
			return;
		}

		// Get gallery config to see if pagination is enabled.
		$paginate        = absint( envira_get_config( 'pagination', $data ) );
		$position        = envira_get_config( 'pagination_position', $data );
		$images_per_page = absint( envira_get_config( 'pagination_images_per_page', $data ) );

		// If images per page is less than 1, force the value so there's no division by zero error.
		if ( $images_per_page < 1 ) {
			$images_per_page = 1;
		}

		// Bail if pagination disabled.
		if ( ! $paginate ) {
			return;
		}

		// !!Tod refactor this look
		// Count active images.
		if ( ! empty( $data['gallery'] ) ) {
			foreach ( $data['gallery'] as $image ) {
				if ( 'active' === $image['status'] ) {
					$active_images[] = $image;
				}
			}
		}

		// Bail if the number of images are less than or equal to the number of images per page.
		if ( ! isset( $data['gallery'] ) || count( $active_images ) <= $images_per_page ) {
			return;
		}

		// Determine which page we are on and the total number of pages available in this Gallery.
		$this->data         = $data;
		$this->current_page = $this->get_pagination_page();
		$this->total_pages  = ceil( count( $active_images ) / $images_per_page );

		// Check we have at least one page.
		if ( $this->total_pages < 2 ) {
			return;
		}

		// Add wp_head action to add rel links to the header of the site.
		add_action( 'wp_head', array( $this, 'add_rel_links' ) );

	}

	/**
	 * Called if the Standalone Addon is going to load an Album
	 *
	 * @since 1.0.4
	 *
	 * @param object $query WP_Query.
	 */
	public function maybe_rel_album( $query ) {

		if ( isset( $query->query['name'] ) ) {
			$data = envira_get_album_by_slug( $query->query['name'] );
		} else {
			return;
		}

		// Check we found a valid Gallery.
		if ( ! $data || ! is_array( $data ) ) {
			return;
		}

		// Get gallery config to see if pagination is enabled.
		$paginate           = absint( $this->get_album_config( 'pagination', $data ) );
		$position           = $this->get_album_config( 'pagination_position', $data );
		$galleries_per_page = absint( $this->get_album_config( 'pagination_images_per_page', $data ) );

		// Bail if pagination disabled.
		if ( ! $paginate ) {
			return;
		}

		if ( ! isset( $data['gallery_ids'] ) ) {
			return $data;
		}

		// Bail if the number of galleries are less than or equal to the number of galleries per page.
		if ( count( $data['gallery_ids'] ) <= $galleries_per_page ) {
			return $data;
		}

		// Determine which page we are on and the total number of pages available in this Album.
		$this->data         = $data;
		$this->current_page = $this->get_pagination_page();
		$this->total_pages  = ceil( count( $data['gallery_ids'] ) / $galleries_per_page );

		// Check we have at least one page.
		if ( $this->total_pages < 2 ) {
			return;
		}

		// Add wp_head action to add rel links to the header of the site.
		add_action( 'wp_head', array( $this, 'add_rel_links' ) );

	}

	/**
	 * Add link rel prev/next to the header of the WordPress site
	 */
	public function add_rel_links() {

		// Previous.
		if ( $this->current_page > 1 ) {
			$url = add_query_arg(
				array(
					'page' => ( $this->current_page - 1 ),
				),
				get_permalink( $this->data['id'] )
			);

			echo '<link rel="prev" href="' . esc_url( $url ) . '" />';
		}

		// Next.
		if ( $this->current_page < $this->total_pages ) {
			$url = add_query_arg(
				array(
					'page' => ( $this->current_page + 1 ),
				),
				get_permalink( $this->data['id'] )
			);

			echo '<link rel="next" href="' . esc_url( $url ) . '" />';
		}

	}


	/**
	 * Paginate images, if pagination is enabled on this gallery
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Gallery Data.
	 * @param int   $gallery_id Gallery ID.
	 * @return array Modified Gallery Data
	 */
	public function paginate_gallery( $data, $gallery_id ) {

		// @codingStandardsIgnoreStart - No Nonce because this is passed in querystring?

		// Get config.
		$paginate        = absint( envira_get_config( 'pagination', $data ) );
		$position        = envira_get_config( 'pagination_position', $data );
		$images_per_page = absint( envira_get_config( 'pagination_images_per_page', $data ) );
		$active_images   = array();

		// Don't modify gallery data if pagination is disabled.
		if ( ! $paginate ) {
			return $data;
		}

		// Having to resort gallery here... pagination page>1 gets sorting right but the initial pagination doesn't sort (ex: title DESC) GH 2787
		// $data = envira_sort_gallery( $data, $data['config']['sort_order'], $data['config']['sorting_direction'] ); // @codingStandardsIgnoreLine

		$data = ! empty( get_transient( '_eg_fragment_gallery_random_sort_' . $data['id'] ) ) ? get_transient( '_eg_fragment_gallery_random_sort_' . $data['id'] ) : envira_sort_gallery( $data, $data['config']['sort_order'], $data['config']['sorting_direction'] );

		if ( class_exists( 'Envira_Instagram_Shortcode' ) && 'instagram' === $data['config']['type'] ) {
			$data = Envira_Instagram_Shortcode::get_instance()->inject_images( $data, intval( $gallery_id ) );
		}

		// Count active images.
		if ( ! empty( $data['gallery'] ) ) {
			foreach ( $data['gallery'] as $image_id => $image ) {
				// Look for both active and published statuses - published was added because of dynamic.
				if ( isset( $image['status'] ) && in_array( $image['status'], array ( 'active', 'published' ) ) ) {
					$image['id'] = $image_id; // we might need the id later, and can't rely on the array key for the image id with pagination. GH 3054.
					$active_images[$image_id] = $image;
				}
			}
		}

		// Don't modify gallery data if the number of images are less than or equal to the number of images per page.
		if ( isset( $data['gallery'] ) && count( $active_images ) <= $images_per_page ) {
			return $data;
		}

		// Determine which page we are on, and define the start index from a zero based index.
		$start = ( ( $this->get_pagination_page() - 1 ) * $images_per_page );

		// If an envira_id is specified in the URL, don't set the start point on every gallery to be the same.
		if ( ! is_singular( array( 'envira', 'envira_album' ) ) ) {

			if ( isset( $_GET['envira_id'] ) && intval( $data['id'] ) !== intval( $_GET['envira_id'] ) ) { // codingStandardsIgnoreLine
				// This gallery isn't being paginated, but is being displayed
				// Set the start to zero.
				$start = 0;
			}
		}

		// @codingStandardsIgnoreEnd - No Nonce because this is passed in querystring?
		// Store the original total number of pages available - this allows paginate_gallery_markup() to know
		// how many links to output for the pagination.
		if ( $images_per_page > 0 && ! empty( $active_images ) ) {
			$data['config']['pagination_total_pages'] = ceil( count( $active_images ) / $images_per_page );
		}

		// Ensure total_pages is populated.
		$data['config']['pagination_total_pages'] = ! empty( $data['config']['pagination_total_pages'] ) ? intval( $data['config']['pagination_total_pages'] ) : 5;

		// Extract subset of images, and apply them back to the $data
		// This means the gallery will only output the specified number of images
		// based on the page index we are on.
		if ( ! empty( $active_images ) ) {
			$images          = array_slice( $active_images, $start, $images_per_page, true );
			$data['gallery'] = $images;
		}

		// Enable pagination display before images, after images or both.
		if ( 'above' === $position || 'both' === $position ) {
			add_filter( 'envira_gallery_output_before_container', array( $this, 'paginate_gallery_markup' ), 1, 2 );
		}
		if ( 'below' === $position || 'both' === $position ) {
			add_filter( 'envira_gallery_output_after_container', array( $this, 'paginate_gallery_markup' ), 1, 2 );
		}

		// Load JS if ajax loading is enabled.
		if ( envira_get_config( 'pagination_ajax_load', $data ) ) {
			wp_enqueue_script( $this->base->plugin_slug . '-script' );
		}

		// Return.
		return $data;

	}

	/**
	 * Paginate galleries, if pagination is enabled on this album
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Album Data.
	 * @param int   $album_id Album ID.
	 * @return array Modified Album Data
	 */
	public function paginate_album( $data, $album_id ) {

		// Get config.
		$paginate           = absint( $this->get_album_config( 'pagination', $data ) );
		$position           = $this->get_album_config( 'pagination_position', $data );
		$galleries_per_page = absint( $this->get_album_config( 'pagination_images_per_page', $data ) );

		// Don't modify gallery data if pagination is disabled.
		if ( ! $paginate ) {
			return $data;
		}

		// Don't modify gallery data if the number of images are less than or equal to the number of images per page.
		if ( ! isset( $data['galleryIDs'] ) || count( $data['galleryIDs'] ) <= $galleries_per_page ) {
			return $data;
		}

		// @codingStandardsIgnoreStart

		// Determine which page we are on, and define the start index from a zero based index.
		$start = ( ( $this->get_pagination_page() - 1 ) * $galleries_per_page );

		// Store the original total number of pages available - this allows paginate_gallery_markup() to know
		// how many links to output for the pagination.
		$data['config']['pagination_total_pages'] = ceil( count( $data['galleries'] ) / $galleries_per_page );

		// If an envira_id is specified in the URL, don't set the start point on every album to be the same
		// Note: This breaks pagination on dynamic albums currently, so a $this->get_pagination_page() check was added.
		if ( ! is_singular( array( 'envira', 'envira_album' ) ) ) {
			if ( isset( $_GET['envira_id'] ) && $album_id != $_GET['envira_id'] && ( ! $this->get_pagination_page() || $this->get_pagination_page() <= 1 ) ) {
				// This album isn't being paginated, but is being displayed
				// Set the start to zero.
				$start = 0;
			}
		}

		// @codingStandardsIgnoreEnd

		// Extract subset of galleries, and apply them back to the $data
		// This means the album will only output the specified number of galleries
		// based on the page index we are on.
		$gallery_ids        = array_slice( $data['galleryIDs'], $start, $galleries_per_page, true );
		$data['galleryIDs'] = $gallery_ids;

		$galleries         = array_slice( $data['galleries'], $start, $galleries_per_page, true );
		$data['galleries'] = $galleries;

		// Enable pagination display before galleries, after galleries or both.
		if ( 'above' === $position || 'both' === $position ) {
			add_filter( 'envira_albums_output_before_container', array( $this, 'paginate_album_markup' ), 1, 2 );
		}
		if ( 'below' === $position || 'both' === $position ) {
			add_filter( 'envira_albums_output_after_container', array( $this, 'paginate_album_markup' ), 1, 2 );
		}

		// Load JS if ajax loading is enabled.
		if ( $this->get_album_config( 'pagination_ajax_load', $data ) ) {
			wp_enqueue_script( $this->base->plugin_slug . '-script' );
		}

		// Return.
		return $data;

	}

	/**
	 * Append the pagination markup to the end of the gallery
	 *
	 * @since 1.0.0
	 *
	 * @param string $html HTML Markup.
	 * @param array  $data Gallery Data.
	 * @return string Modified HTML Markup
	 */
	public function paginate_gallery_markup( $html, $data ) {

		// @codingStandardsIgnoreStart - No Nonce because this is passed in querystring?

		global $post;

		// Don't output any markup if pagination is disabled on this Gallery.
		$paginate = absint( envira_get_config( 'pagination', $data ) );
		if ( ! $paginate ) {
			return $html;
		}

		$album_id     = false;
		$gallery_page = false;

		$album_id = get_query_var( 'album_id' );

		// Determine if there's an album id we can/should extract.
		if ( isset( $_REQUEST['album_id'] ) ) {
			$album_id = intval( $_REQUEST['album_id'] );
		} elseif ( isset( $data['id'] ) && ! isset( $data['gallery_id'] ) ) {
			$album_id = $data['id'];
		} elseif ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			// If first part of referrer URL matches the Envira Album slug, the visitor clicked on a gallery from an album.
			$referer_url       = str_replace( trailingslashit( get_bloginfo( 'url' ) ), '', trailingslashit( sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) ) );
			$referer_url_parts = array_values( array_filter( explode( '/', $referer_url ) ) );
			if ( ! $referer_url ) {
				// referer must of been the homepage.
				$referer_url       = trailingslashit( get_bloginfo( 'url' ) );
				$referer_url_parts = false;
			} elseif ( isset( $referer_url_parts[0] ) && envira_standalone_get_the_slug( 'gallery' ) === $referer_url_parts[0] ) {
				// the referring item was a gallery.
				$gallery_page = true;
			} else {
				$referer_url_parts = array_values( array_filter( explode( '/', $referer_url ) ) );
			}
			if ( ! $gallery_page ) {

				$args = array(
					'post_type'   => array( 'page', 'post', 'envira', 'envira_album' ),
					'post_status' => 'publish',
					'numberposts' => 1,
				);

				if ( is_array( $referer_url_parts ) ) {
					$args['name'] = end( $referer_url_parts );
				}

				$maybe_album_page = get_posts( $args );

				if ( ! $maybe_album_page ) {
					// don't give up, grab the $data id if it exists.
					if ( isset( $data['id'] ) ) {
						$album_id = $data['id'];
					}
				} else {
					$album_id = $maybe_album_page[0]->ID;
				}
			} /* if not a gallery page */

		}

		/**
		* If Pagination is used on a Standalone Gallery, in an AJAX request, or on a Gallery within a Page,
		* the pagination's base arguments, format, current page and base link need to be obtained in
		* slightly different ways.
		*/
		if ( is_singular( array( 'envira' ) ) || defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			// Standalone Gallery or AJAX Request.
			$base_args = array(
				'page' => '%#%',
			);
			$format    = '?page=%#%';

			if ( $album_id ) {
				$base_args['album_id'] = $album_id;
				// add album id to querystring.
				$format .= '&album_id=' . $album_id;
			}

			$current = $this->get_pagination_page();
			$url     = add_query_arg( $base_args, get_permalink( $data['id'] ) );
		} else {
			// Gallery within Page
			// The base_args and format allow multiple Galleries to be embedded in a single Page, and
			// when pagination is used, it only affects a single Gallery.
			// UPDATE: gallery id could be passed via the social addon...
			// ...so that takes priority over the previous methods. the values in the querystring is envira_social_gallery_id.
			$envira_id = false;

			if ( isset( $_GET['envira_social_gallery_id'] ) ) {
				$envira_id = intval( $_GET['envira_social_gallery_id'] );
			}

			if ( ! $envira_id ) {
				$envira_id = $data['id'];
			}

			$base_args = array(
				'envira_id' => $envira_id,
				'page'      => '%#%',
			);
			$format    = '?envira_id=' . $envira_id . '&page=%#%';

			// We only set the current page to the paged argument if we're generating markup
			// for the gallery we're currently paginating.
			$current = 1;
			if ( isset( $_GET['envira_id'] ) && $data['id'] === $_GET['envira_id'] ) {
				$current = $this->get_pagination_page();
			} elseif ( isset( $_GET['envira_social_gallery_id'] ) && $data['id'] === $_GET['envira_social_gallery_id'] ) {
				$current = $this->get_pagination_page();
			} else {
				$current = $this->get_pagination_page();
			}
			$url = add_query_arg( $base_args, get_permalink( $post->ID ) );
		}

		// determine the prev/next/page_number values based on album settings.
		$before_page_number       = false;
		$after_page_number        = false;
		$pagination_links_setting = envira_get_config( 'pagination_prev_next', $data );
		if ( ! $pagination_links_setting || 'numbered' === $pagination_links_setting ) {
			// note: with updates to pagination link options, no value likely means
			// this was an existing gallery and user selected no prev/next links.
			$pagination_links = false;
		} else {
			$pagination_links = true;
		}

		if ( 'previous_next' === $pagination_links_setting ) {
			// normally switch to posts_nav_link() but we aren't paginated normal WP pages, so
			// we'll simply comment out the page numbers for now. TODO: Out As Array, Not Plain, And Clean Out Numbers?
			$before_page_number = '<!--';
			$after_page_number  = '-->';
		}

		// Ensure total_pages is populated.
		$data['config']['pagination_total_pages'] = ! empty( $data['config']['pagination_total_pages'] ) ? $data['config']['pagination_total_pages'] : 10;

		// Build pagination.
		$pagination_args = array(
			'base'               => $url . ( ( isset( $data['config']['pagination_scroll'] ) && 1 === intval( $data['config']['pagination_scroll'] ) ) ? '#envira-gallery-wrap-' . $data['id'] : '' ),
			'format'             => $format,
			'total'              => $data['config']['pagination_total_pages'],
			'current'            => $current,
			'show_all'           => false,
			'end_size'           => 1,
			'mid_size'           => 2,
			'prev_next'          => (bool) $pagination_links,
			'prev_text'          => envira_get_config( 'pagination_prev_text', $data ),
			'next_text'          => envira_get_config( 'pagination_next_text', $data ),
			'type'               => 'plain',
			'add_args'           => false,
			'add_fragment'       => '',
			'before_page_number' => $before_page_number,
			'after_page_number'  => $after_page_number,
		);

		// Filter pagination args.
		$pagination_args = apply_filters( 'envira_pagination_link_args', $pagination_args, $html, $data );

		// Build CSS classes for the pagination container.
		$pagination_css_classes = array();
		if ( intval( envira_get_config( 'pagination_ajax_load', $data ) ) === 1 ) {
			$pagination_css_classes[] = 'envira-pagination-lazy-load';
		}
		if ( intval( envira_get_config( 'pagination_ajax_load', $data ) ) === 2 ) {
			$pagination_css_classes[] = 'envira-pagination-ajax-load';
		}
		if ( intval( envira_get_config( 'pagination_ajax_load', $data ) ) === 3 ) {
			$pagination_css_classes[] = 'envira-pagination-ajax-load-more';
		}
		if ( 'previous_next' === $pagination_links_setting ) {
			$pagination_css_classes[] = 'envira-pagination-previous-next-only';
		}

		// Get Button Text - There Should Always Be Wording, Revert To "Load More".
		$load_more_text = __( 'Load More', 'envira-pagination' );
		if ( envira_get_config( 'pagination_button_text', $data ) ) {
			$load_more_text = envira_get_config( 'pagination_button_text', $data );
		}

		// Get Envira Post ID To Pass.
		$envira_post_id = isset( $post->ID ) ? $post->ID : false;

		// Get 'Type' To Pass.
		$envira_type = ( 'instagram' === $data['config']['type'] ? 'instagram' : 'gallery' );
		$envira_type = ( 'fc' === $data['config']['type'] ? 'fc_gallery' : $envira_type );

		// Filter pagination classes.
		$pagination_css_classes = apply_filters( 'envira_pagination_css_classes', $pagination_css_classes, $html, $data );

		if ( envira_get_config( 'pagination_ajax_load', $data ) === 3 ) {

			// Output pagination.
			$pagination = '<div class="envira-pagination ' . implode( ' ', $pagination_css_classes ) . '" data-envira-post-id="' . $envira_post_id . '" data-type="' . $envira_type . '" data-page="' . $current . '" data-per-page="' . envira_get_config( 'pagination_images_per_page', $data ) . '" data-max-pages="' . $data['config']['pagination_total_pages'] . '"><a href="javascript:void(0);" class="envira-pagination-load-more">' . $load_more_text . '</a></div>';

		} else {

			// Output pagination.
			$pagination = '<div class="envira-pagination ' . implode( ' ', $pagination_css_classes ) . '"  data-envira-post-id="' . $envira_post_id . '" data-type="' . $envira_type . '" data-page="' . $current . '" data-per-page="' . envira_get_config( 'pagination_images_per_page', $data ) . '" data-max-pages="' . $data['config']['pagination_total_pages'] . '">' . paginate_links( $pagination_args ) . '</div>';
			// Modify the pagination HTML.
			$pagination = str_replace( '<a class="prev', '<a rel="prev" class="prev', $pagination );
			$pagination = str_replace( '<a class="next', '<a rel="next" class="next', $pagination );

		}

		// @codingStandardsIgnoreEnd

		// Return.
		return $html . $pagination;

	}

	/**
	 * Append the pagination markup to the end of the gallery
	 *
	 * @since 1.1.3
	 *
	 * @param string $html HTML Markup.
	 * @param array  $data Gallery Data.
	 * @return string Modified HTML Markup
	 */
	public function paginate_album_markup( $html, $data ) {

		global $post;

		// Don't output any markup if pagination is disabled on this Album.
		$paginate = absint( $this->get_album_config( 'pagination', $data ) );
		if ( ! $paginate ) {
			return $html;
		}

		// @codingStandardsIgnoreStart - No Nonce because this is passed in querystring?

		/**
		* If Pagination is used on a Standalone Gallery, in an AJAX request, or on a Gallery within a Page,
		* the pagination's base arguments, format, current page and base link need to be obtained in
		* slightly different ways.
		*/
		if ( is_singular( array( 'envira_album' ) ) || defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			// Standalone Gallery or AJAX Request.
			$base_args = array(
				'page' => '%#%',
			);
			$format    = '?page=%#%';
			$current   = $this->get_pagination_page();
			$url       = add_query_arg( $base_args, get_permalink( $data['id'] ) );

		} else {
			// Gallery within Page
			// The base_args and format allow multiple Galleries to be embedded in a single Page, and
			// when pagination is used, it only affects a single Gallery.
			$base_args = array(
				'envira_id' => $data['id'],
				'page'      => '%#%',
			);
			$format    = '?envira_id=' . $data['id'] . '&page=%#%';

			// We only set the current page to the paged argument if we're generating markup
			// for the gallery we're currently paginating.
			$current = 1;
			if ( isset( $_GET['envira_id'] ) && $data['id'] === $_GET['envira_id'] ) {
				$current = $this->get_pagination_page();
			} else if ( ! empty( $_SERVER['REQUEST_URI'] ) && ! empty( $_GET['envira_id'] ) ) {
				global $wp_query;
				$first_array  = explode( "/", $_SERVER['REQUEST_URI'] );
				$second_array = explode( "?", $first_array[sizeof($first_array)-1], 2);
				$paged        = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
				$current      = ( empty( $wp_query->query['page'] ) ) ? max( 1, intval( $second_array[0] ) ) : $wp_query->query['page'];
			}
			$url = add_query_arg( $base_args, get_permalink( $post->ID ) );
		}

		// @codingStandardsIgnoreEnd - No Nonce because this is passed in querystring?

		// determine the prev/next/page_number values based on album settings.
		$before_page_number       = false;
		$after_page_number        = false;
		$pagination_links_setting = $this->get_album_config( 'pagination_prev_next', $data );

		if ( ! $pagination_links_setting || 'numbered' === $pagination_links_setting ) {
			// note: with updates to pagination link options, no value likely means
			// this was an existing gallery and user selected no prev/next links.
			$pagination_links = false;
		} else {
			$pagination_links = true;
		}

		if ( 'previous_next' === $pagination_links_setting ) {
			$before_page_number = '<!--';
			$after_page_number  = '-->';
		}

		// Build pagination.
		$pagination_args = array(
			'base'               => $url . ( ( isset( $data['config']['pagination_scroll'] ) && '1' === $data['config']['pagination_scroll'] ) ? '#envira-gallery-wrap-' . $data['id'] : '' ),
			'format'             => $format,
			'total'              => $data['config']['pagination_total_pages'],
			'current'            => $current,
			'show_all'           => false,
			'end_size'           => 1,
			'mid_size'           => 2,
			'prev_next'          => (bool) $pagination_links,
			'prev_text'          => $this->get_album_config( 'pagination_prev_text', $data ),
			'next_text'          => $this->get_album_config( 'pagination_next_text', $data ),
			'type'               => 'plain',
			'add_args'           => false,
			'add_fragment'       => '',
			'before_page_number' => $before_page_number,
			'after_page_number'  => $after_page_number,
		);

		// Filter pagination args.
		$pagination_args = apply_filters( 'envira_pagination_link_args', $pagination_args, $html, $data );

		// Build CSS classes for the pagination container.
		$pagination_css_classes = array();
		if ( $this->get_album_config( 'pagination_ajax_load', $data ) === 1 ) {
			$pagination_css_classes[] = 'envira-pagination-lazy-load';
		}
		if ( $this->get_album_config( 'pagination_ajax_load', $data ) === 2 ) {
			$pagination_css_classes[] = 'envira-pagination-ajax-load';
		}
		if ( $this->get_album_config( 'pagination_ajax_load', $data ) === 3 ) {
			$pagination_css_classes[] = 'envira-pagination-ajax-load-more';
		}
		if ( 'previous_next' === $pagination_links_setting ) {
			$pagination_css_classes[] = 'envira-pagination-previous-next-only';
		}

		// Get Button Text - There Should Always Be Wording, Revert To "Load More".
		$load_more_text = __( 'Load More', 'envira-pagination' );
		if ( $this->get_album_config( 'pagination_button_text', $data ) ) {
			$load_more_text = $this->get_album_config( 'pagination_button_text', $data );
		}

		// Filter pagination classes.
		$pagination_css_classes = apply_filters( 'envira_pagination_css_classes', $pagination_css_classes, $html, $data );

		if ( $this->get_album_config( 'pagination_ajax_load', $data ) === 3 ) {

			// Output pagination.
			$pagination = '<div class="envira-pagination ' . implode( ' ', $pagination_css_classes ) . '" data-type="album" data-page="' . $current . '"><a href="javascript:void(0);" class="envira-pagination-load-more">' . $load_more_text . '</a></div>';

		} else {

			// Output pagination.
			$pagination = '<div class="envira-pagination ' . implode( ' ', $pagination_css_classes ) . '"  data-envira-post-id="' . $post->ID . '" data-type="album" data-page="' . $current . '" data-per-page="' . $this->get_album_config( 'pagination_images_per_page', $data ) . '">' . paginate_links( $pagination_args ) . '</div>';

			// Modify the pagination HTML.
			$pagination = str_replace( '<a class="prev', '<a rel="prev" class="prev', $pagination );
			$pagination = str_replace( '<a class="next', '<a rel="next" class="next', $pagination );

		}

		// Return.
		return $html . $pagination;

	}

	/**
	 * If the specified Gallery requires that all images be available for display
	 * in the Lightbox, regardless of which page the user is on, load
	 * the images into a JS array and assign it to the Lightbox when it opens
	 *
	 * @since 1.1.7
	 *
	 * @param   string $value  Value.
	 * @param   array  $data   Gallery Data.
	 */
	public function maybe_display_all_images_in_lightbox( $value, $data ) {

		// bail if pagination not enabled.
		if ( ! envira_get_config( 'pagination', $data ) ) {
			return false;
		}

		if ( envira_get_config( 'pagination_lightbox_display_all_images', $data ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Changes the link AND src attribute of an image, if the Lightbox config
	 * requires a different sized image to be displayed and pagination has 'display all images' selected
	 *
	 * @since 1.3.6
	 *
	 * @param int   $id      The image attachment ID to use.
	 * @param array $item  Gallery item data.
	 * @param array $data  The gallery data to use for retrieval.
	 * @return array       Image array
	 */
	public function maybe_change_link_lightbox( $id, $item, $data ) {

		// Check gallery config.
		$image_size = envira_get_config( 'lightbox_image_size', $data );

		// Check if the url is a valid image if not return it.
		if ( ! envira_is_image( $item['link'] ) ) {
			return $item;
		}

		// Get media library attachment at requested size.
		$image = wp_get_attachment_image_src( $id, $image_size );

		if ( ! is_array( $image ) ) {
			return $item;
		}

		// Inject new image size into $item.
		$item['link'] = $image[0];
		// Inject new image as src into $item
		// This way lighbox can display proper sized images.
		$item['src'] = $image[0];

		// Return.
		return $item;

	}

	/**
	 * Helper method for retrieving config values for an Album
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
	 * Helper method for retrieving the current page number a visitor is viewing
	 * within a paginated gallery or album.
	 *
	 * @since 1.0.0
	 *
	 * @return int Page Number
	 */
	public function get_pagination_page() {

		// The page we're requesting can be provided via a normal HTTP request,
		// or via AJAX.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_REQUEST['page'] ) ) { // @codingStandardsIgnoreLine - rewrite to ajax request
			// Gallery is being requested via AJAX, so check to see if the request includes a page parameter.
			$page = absint( $_REQUEST['page'] ); // @codingStandardsIgnoreLine - nonce
		} else {
			// Gallery is being requested normally, use get_query_var.
			$page = absint( str_replace( '/', '', get_query_var( 'page' ) ) );
		}

		if ( $page < 1 ) {
			$page = 1;
		}

		return $page;

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Pagination_Shortcode object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Pagination_Shortcode ) ) {
			self::$instance = new Envira_Pagination_Shortcode();
		}

		return self::$instance;

	}

}

// Load the shortcode class.
$envira_pagination_shortcode = Envira_Pagination_Shortcode::get_instance();
