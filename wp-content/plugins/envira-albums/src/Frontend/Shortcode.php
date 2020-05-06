<?php
/**
 * Shortcode class.
 *
 * @since 1.6.0
 *
 * @package Envira Gallery
 * @subpackage Envira Albums
 * @author Envira Gallery Team <support@enviragallery.com>
 */

namespace Envira\Albums\Frontend;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Envira Albums Shortcode.
 *
 * @since 1.6.0
 */
class Shortcode {

	/**
	 * Holds the unfiltered album data.
	 *
	 * @since 1.3.0.4
	 *
	 * @var array
	 */
	public $unfiltered_albums;

	/**
	 * Holds the album data.
	 *
	 * @since 1.6.0
	 *
	 * @var array
	 */
	public $data;

	/**
	 * Holds gallery IDs for init firing checks.
	 *
	 * @since 1.6.0
	 *
	 * @var array
	 */
	public $done = array();

	/**
	 * Iterator for galleries on the page.
	 *
	 * @since 1.6.0
	 *
	 * @var int
	 */
	public $counter = 1;

	/**
	 * Array of gallery ids on the page.
	 *
	 * @since 1.6.0
	 *
	 * @var array
	 */
	public $album_ids = array();

	/**
	 * Array of gallery item ids on the page.
	 *
	 * @since 1.6.0
	 *
	 * @var array
	 */
	public $album_item_ids = array();

	/**
	 * Holds image URLs for indexing.
	 *
	 * @since 1.6.0
	 *
	 * @var array
	 */
	public $index = array();

	/**
	 * Is mobile
	 *
	 * @var mixed
	 * @access public
	 */
	public $is_mobile;

	/**
	 * Holds album markup.
	 *
	 * @var string
	 */
	public $album_markup;

	/**
	 * Holds the sort order of the gallery for addons like Pagination
	 *
	 * @since 1.5.6
	 *
	 * @var array
	 */
	public $album_sort = array();

	/**
	 * Holds the album data.
	 *
	 * @since 1.5.6
	 *
	 * @var array
	 */
	public $album_data = array();


	/**
	 * Primary class constructor.
	 *
	 * @since 1.6.0
	 */
	public function __construct() {

		$this->is_mobile = envira_mobile_detect()->isMobile();

		// Load hooks and filters.
		add_action( 'init', array( &$this, 'register_scripts' ) );

		add_shortcode( 'envira-album', array( $this, 'shortcode' ) );

		add_filter( 'widget_text', 'do_shortcode' );
		add_filter( 'envira_gallery_output_before_container', array( $this, 'maybe_add_back_link_prepend' ), 10, 2 );
		add_filter( 'envira_gallery_output_end', array( $this, 'maybe_add_back_link_append' ), 10, 2 );

		add_filter( 'envira_albums_output_before_container', array( $this, 'output_album_breadcrumbs' ), 10, 2 );
		add_filter( 'envira_gallery_output_before_container', array( $this, 'output_gallery_breadcrumbs' ), 10, 2 );
		add_filter( 'wpseo_breadcrumb_links', array( &$this, 'change_wpseo_breadcrumb_links' ) );

		add_filter( 'envira_album_should_cache', array( $this, 'maybe_change_cache' ), 10, 2 );

	}

	/**
	 * Register scripts.
	 *
	 * @since 1.0.0
	 */
	public function register_scripts() {
		wp_register_script( ENVIRA_ALBUMS_SLUG . '-script', plugins_url( 'assets/js/min/envira-albums-min.js', ENVIRA_ALBUMS_FILE ), array( 'jquery' ), ENVIRA_ALBUMS_VERSION, true );
		wp_register_style( ENVIRA_ALBUMS_SLUG . '-style', plugins_url( 'assets/css/albums-style.css', ENVIRA_ALBUMS_FILE ), array(), ENVIRA_ALBUMS_VERSION );
	}

	/**
	 * Creates the shortcode for the plugin.
	 *
	 * @since 1.6.0
	 *
	 * @global object $post The current post object.
	 *
	 * @param array $atts Array of shortcode attributes.
	 * @return string     The gallery output.
	 */
	public function shortcode( $atts ) {

		global $post, $wp_current_filter;

		$album_id = false;

		// Don't do anything for excerpts (this helps prevent issues with third-party plugins ).
		if ( in_array( 'get_the_excerpt', (array) $wp_current_filter, true ) ) {
			return false;
		}

		if ( isset( $atts['id'] ) ) {

			$album_id = (int) $atts['id'];
			$data     = is_preview() ? _envira_get_album( $album_id ) : envira_get_album( $album_id );

		} elseif ( isset( $atts['slug'] ) ) {

			$album_id = $atts['slug'];
			$data     = is_preview() ? _envira_get_album_by_slug( $album_id ) : envira_get_album_by_slug( $album_id );

		} else {

			// A custom attribute must have been passed. Allow it to be filtered to grab data from a custom source.
			$data = apply_filters( 'envira_albums_custom_gallery_data', false, $atts, $post );

		}

		if ( empty( $data['id'] ) ) {
			return;
		}

		// Check if we've passed the cahce atts.
		$should_cache = isset( $atts['cache'] ) ? filter_var( $atts['cache'], FILTER_VALIDATE_BOOLEAN ) : true;

		// Don't cache if limit is set.
		if ( isset( $atts['limit'] ) ) {
			$should_cache = false;
		}

		$this->album_data = $data;

		// Get Envira Albums Dynamic ID.
		$album_dynamic_id = get_option( 'envira_dynamic_album' );
		$album_is_dynamic = ( isset( $data['config']['type'] ) && 'dynamic' === $data['config']['type'] ) ? true : false;
		$main_id          = $album_is_dynamic ? $album_dynamic_id : $data['id'];

		// This filter detects if something needs to be displayed BEFORE a gallery is displayed, such as a password form.
		$pre_album_html = apply_filters( 'envira_abort_album_output', false, $data, $album_id, $atts );

		if ( false !== $pre_album_html ) {

			// If there is HTML, then we stop trying to display the gallery and return THAT HTML.
			return apply_filters( 'envira_gallery_output', $pre_album_html, $data );

		}

		// Lets check if this gallery has already been output on the page.
		$this->album_data['album_id'] = $this->album_data['id'];

		if ( ! empty( $atts['counter'] ) ) {
			// we are forcing a counter so lets force the object in the album_ids.
			$this->counter     = $atts['counter'];
			$this->album_ids[] = $this->album_data['id'];
		}

		if ( ! in_array( $this->album_data['id'], $this->album_ids, true ) ) {
			$this->album_ids[] = $this->album_data['id'];
		} elseif ( $this->counter > 1 ) {
			$this->album_data['id'] = $this->album_data['id'] . '_' . $this->counter;
		}

		if ( empty( $atts['presorted'] ) ) {
			$this->album_sort[ $this->album_data['id'] ] = false; // reset this to false, otherwise multiple galleries on the same page might get other ids, or other wackinesses.
		}

		// If this is a dynamic gallery and there are no gallery IDs and the user is requesting "all", then let's grab all eligable ones.
		if ( ( ! isset( $this->album_data['galleryIDs'] ) || empty( $this->album_data['galleryIDs'] ) && 'all' !== $this->album_data['galleries'] && 'dynamic' !== $this->album_data['type'] ) ) {

			if ( class_exists( 'Envira_Dynamic_Album_Shortcode' ) ) {
				$galleries = \ Envira_Dynamic_Album_Shortcode::get_instance()->get_galleries( $this->album_data, $this->album_data['id'], $this->album_data, null );
			} else {
				// bail if dynamic isnt installed.
				return;
			}

			$this->album_data['galleryIDs'] = $galleries['galleryIDs'];
			$this->album_data['galleries']  = $galleries['galleries'];

		}

		if ( ! empty( $this->album_data['galleryIDs'] ) ) {
			foreach ( $this->album_data['galleryIDs'] as $key => $id ) {

				// Lets check if this gallery has already been output on the page.
				if ( ! in_array( $id, $this->album_item_ids, true ) ) {
					$this->album_item_ids[] = $id;
				} else {
					$this->album_data['galleries'][ $id . '_' . $this->counter ] = $this->album_data['galleries'][ $id ];
					unset( $this->album_data['galleries'][ $id ] );

					$id                               = $id . '_' . $this->counter;
					$this->album_data['galleryIDs'][] = $id;
					unset( $this->album_data['galleryIDs'][ $key ] );

				}
			}
		}

		// Store the unfiltered Album in the class array.
		// This can be used in the Lightbox later on to build the Galleries and Images to display.
		$this->unfiltered_albums[ $this->album_data['id'] ] = $this->album_data;

		// Pagination requires sorting prior to envira_albums_pre_data filter. GH #3088.
		if ( isset( $this->album_data['config']['pagination'] ) && 1 === $this->album_data['config']['pagination'] && ! empty( $this->album_data['config']['sorting'] ) ) {
			$this->album_data = $this->maybe_sort_album( $this->album_data, $album_id );
		}

		// Allow the data to be filtered before it is stored and used to create the album output.
		$this->album_data = apply_filters( 'envira_albums_pre_data', $this->album_data, $album_id );

		// Change the album order, if specified. Moving this BELOW the envira_albums_pre_data filter so things can get sorted, in theory.
		$this->album_data = $this->maybe_sort_album( $this->album_data, $album_id );

		// If there is no data to output or the gallery is inactive, do nothing.
		if ( ! $this->album_data || empty( $this->album_data['galleryIDs'] ) ) {
			return;
		}

		// Get rid of any external plugins trying to jack up our stuff where a gallery is present.
		$this->plugin_humility();

		// Prepare variables.
		$this->index[ $this->album_data['id'] ] = array();
		$album                                  = '';
		$i                                      = 1;
		$this->album_markup                     = '';

		// If this is a feed view, customize the output and return early.
		if ( is_feed() ) {
			return $this->do_feed_output( $this->album_data );
		}

		// Load scripts and styles.
		wp_enqueue_style( ENVIRA_SLUG . '-style' );

		wp_enqueue_style( ENVIRA_SLUG . '-jgallery' );
		wp_enqueue_style( ENVIRA_ALBUMS_SLUG . '-style' );

		wp_enqueue_script( ENVIRA_SLUG . '-script' );
		wp_enqueue_script( ENVIRA_ALBUMS_SLUG . '-script' );

		wp_localize_script(
			ENVIRA_SLUG . '-script',
			'envira_gallery',
			array(
				'debug'      => ( defined( 'ENVIRA_DEBUG' ) && ENVIRA_DEBUG ? true : false ),
				'll_delay'   => isset( $this->album_data['config']['lazy_loading_delay'] ) ? intval( $this->album_data['config']['lazy_loading_delay'] ) : 500,
				'll_initial' => 'false',
				'll'         => envira_albums_get_config( 'lazy_loading', $data ) === 1 ? 'true' : 'false',
				'mobile'     => $this->is_mobile,

			)
		);

		// Load custom gallery themes if necessary.
		if ( 'base' !== envira_albums_get_config( 'gallery_theme', $this->album_data ) ) {
			envira_load_gallery_theme( envira_albums_get_config( 'gallery_theme', $this->album_data ) );
		}

		// Load custom lightbox themes if necessary, don't load if user hasn't enabled lightbox.
		if ( envira_albums_get_config( 'lightbox', $this->album_data ) ) {
			envira_load_lightbox_theme( envira_albums_get_config( 'lightbox_theme', $this->album_data ) );
		}

		// Run a hook before the gallery output begins but after scripts and inits have been set.
		do_action( 'envira_albums_before_output', $this->album_data );

		// Allow caching to be filtered here, some addons might force caching off regardless of any other situations.
		$should_cache = apply_filters( 'envira_album_should_cache', $should_cache, $this->album_data );

		if ( $this->is_mobile ) {

			$markup = apply_filters( 'envira_albums_get_transient_markup_mobile', get_transient( '_eg_fragment_albums_mobile_' . $this->album_data['id'] ), $this->album_data );

		} else {

			$markup = apply_filters( 'envira_albums_get_transient_markup', get_transient( '_eg_fragment_albums_' . $this->album_data['id'] ), $this->album_data );

		}

		if ( $markup && $should_cache && ( ! defined( 'ENVIRA_DEBUG' ) || ! ENVIRA_DEBUG ) ) {

			$this->album_markup = $markup;

		} else {

			// Apply a filter before starting the gallery HTML.
			// Note: the below should be depreciated, since it should be 'album' and not 'gallery'.
			$this->album_markup = apply_filters( 'envira_gallery_output_start', $this->album_markup, $this->album_data );
			// We should be using this instead (one use case is the CSS addon).
			$this->album_markup = apply_filters( 'envira_albums_output_start', $this->album_markup, $this->album_data );
			// Build out the album HTML.
			$this->album_markup .= '<div id="envira-gallery-wrap-' . sanitize_html_class( $this->album_data['id'] ) . '" class="envira-album-wrap ' . $this->get_album_classes( $this->album_data ) . '" ' . $this->get_custom_width( $this->album_data ) . '>';
			$this->album_markup  = apply_filters( 'envira_albums_output_before_container', $this->album_markup, $this->album_data );

			// Description.
			if ( isset( $this->album_data['config']['description_position'] ) && 'above' === $this->album_data['config']['description_position'] ) {
				$this->album_markup = $this->description( $this->album_markup, $this->album_data );
			}

			// add justified CSS?
			$extra_css = 'envira-gallery-justified-public';

			if ( envira_albums_get_config( 'columns', $this->album_data ) > 0 ) {
				$extra_css = false;
			}

			// add a CSS class for lazy-loading.
			$extra_css            .= envira_albums_get_config( 'lazy_loading', $data ) === 1 ? ' envira-lazy ' : ' envira-no-lazy ';
			$album_config          = "data-album-config='" . envira_get_album_config( $main_id, false, $album_is_dynamic, $data['id'] ) . "'";
			$album_lightbox_config = " data-lightbox-theme='" . htmlentities( envira_album_load_lightbox_config( $main_id ) ) . "'";
			$album_galleries_json  = " data-album-galleries='" . envira_get_album_galleries( $this->album_data['album_id'] ) . "'";
			$row_height            = ! $this->is_mobile ? envira_albums_get_config( 'justified_row_height', $this->album_data ) : envira_albums_get_config( 'mobile_justified_row_height', $this->album_data );
			$this->album_markup   .= '<div ' . $album_config . $album_lightbox_config . $album_galleries_json . ' id="envira-gallery-' . sanitize_html_class( $this->album_data['id'] ) . '" class="envira-album-public ' . $extra_css . ' envira-gallery-' . sanitize_html_class( envira_albums_get_config( 'columns', $this->album_data ) ) . '-columns envira-clear' . ( envira_albums_get_config( 'isotope', $this->album_data ) && envira_albums_get_config( 'columns', $this->album_data ) > 0 ? ' enviratope' : '' ) . '" data-envira-columns="' . envira_albums_get_config( 'columns', $this->album_data ) . '" data-row-height="' . $row_height . '">';

			foreach ( $this->album_data['galleryIDs'] as $key => $id ) {

				// Skip gallery if its not published.
				if ( get_post_status( $id ) !== 'publish' ) {
					continue;
				}

				// Add the album item to the markup.
				$this->album_markup = $this->generate_album_item_markup( $this->album_markup, $this->album_data, $id, $i );

				// Increment the iterator.
				$i++;

			}

			$this->album_markup .= '</div>';

			// Description.
			if ( isset( $this->album_data['config']['description_position'] ) && 'below' === $this->album_data['config']['description_position'] ) {
				$this->album_markup = $this->description( $this->album_markup, $this->album_data );
			}

			$this->album_markup  = apply_filters( 'envira_albums_output_after_container', $this->album_markup, $this->album_data );
			$this->album_markup .= '</div>';
			$this->album_markup  = apply_filters( 'envira_albums_output_end', $this->album_markup, $this->album_data );

			// Increment the counter.
			$this->counter++;

			// Add no JS fallback support.
			$no_js = $this->get_indexable_images( $this->album_data['id'] );

			if ( $no_js ) {
				$no_js = '<noscript>' . $no_js . '</noscript>';
			}
			$this->album_markup .= $no_js;

			if ( $this->is_mobile ) {

				$transient = set_transient( '_eg_fragment_albums_mobile_' . $this->album_data['id'], $this->album_markup, DAY_IN_SECONDS );

			} else {

				$transient = set_transient( '_eg_fragment_albums_' . $this->album_data['id'], $this->album_markup, DAY_IN_SECONDS );

			}
		}

		$this->data[ $this->album_data['id'] ] = $this->album_data;

		// Return the album HTML.
		return apply_filters( 'envira_albums_output', $this->album_markup, $this->album_data );

	}

	/**
	 * Maybe add a back to Album link on a Gallery, if the user navigated from an Album and that Album
	 * has this functionality enabled
	 *
	 * @since 1.1.0.1
	 *
	 * @param string $gallery Gallery HTML.
	 * @param array  $data Gallery Data.
	 * @return string Gallery HTML
	 */
	public function maybe_add_back_link_prepend( $gallery, $data ) {

		// Check if the user was referred from an Album.
		if ( ! isset( $_SERVER['HTTP_REFERER'] ) && ! isset( $_REQUEST['album_id'] ) ) { // @codingStandardsIgnoreLine
			return $gallery;
		}

		$gallery_backup    = $gallery; // save a copy of $gallery.
		$referer_url       = false;
		$referer_url_parts = array();

		if ( isset( $_SERVER['HTTP_REFERER'] ) && ! isset( $_REQUEST['album_id'] ) ) { // @codingStandardsIgnoreLine

			// If first part of referrer URL matches the Envira Album slug, the visitor clicked on a gallery from an album.
			$referer_url       = str_replace( get_bloginfo( 'url' ), '', wp_unslash( $_SERVER['HTTP_REFERER'] ) ); // @codingStandardsIgnoreLine
			$referer_url_parts = array_values( array_filter( explode( '/', $referer_url ) ) );

			if ( ! is_array( $referer_url_parts ) || count( $referer_url_parts ) < 1 ) { // why was it 2 before?
				return $gallery;
			}

			$args             = array(
				'name'        => end( $referer_url_parts ),
				'post_type'   => array( 'page', 'post', 'envira_album' ),
				'post_status' => 'publish',
				'numberposts' => 1,
			);
			$maybe_album_page = get_posts( $args );

			if ( ! $maybe_album_page ) {
				// Giving up, if there is a page it's not published.
				return $gallery;
			}
		}

		$slug = envira_standalone_get_the_slug( 'albums' );
		if ( ( ! empty( $referer_url_parts ) && $referer_url_parts[0] !== $slug ) || ( isset( $_REQUEST['album_id'] ) ) ) { // @codingStandardsIgnoreLine

			// This might be a regular WordPress page the user has embedded an album into, so let's check.
			if ( isset( $_REQUEST['album_id'] ) ) { // @codingStandardsIgnoreLine
				$album_id = intval( $_REQUEST['album_id'] ); // @codingStandardsIgnoreLine

				$args             = array(
					'ID'          => $album_id,
					'post_type'   => array( 'page', 'post', 'envira_album' ),
					'post_status' => 'publish',
					'numberposts' => 1,
				);
				$maybe_album_page = get_posts( $args );
			} else {
				$args             = array(
					'name'        => end( $referer_url_parts ),
					'post_type'   => array( 'page', 'post' ),
					'post_status' => 'publish',
					'numberposts' => 1,
				);
				$maybe_album_page = get_posts( $args );
			}

			if ( ! $maybe_album_page ) {
				// Giving up, if there is a page it's not published.
				return $gallery;
			}

			// If it's an album standalone, we move on.
			if ( ( 'page' === $maybe_album_page[0]->post_type || 'post' === $maybe_album_page[0]->post_type ) && ! has_shortcode( $maybe_album_page[0]->post_content, 'envira-album' ) ) {
				// no shortcode, so this won't get a back link
				// update: adding a filter because if the album page doesn't have a shortcode, it might be added another way (say by Fusion Builder) so allow the user to override this.
				$maybe_album_page_override = apply_filters( 'envira_albums_is_album_page', false, $maybe_album_page[0] );
				if ( false === $maybe_album_page_override ) {
					return $gallery;
				}
			}

			if ( 'page' === $maybe_album_page[0]->post_type || 'post' === $maybe_album_page[0]->post_type ) {

				// If there is a shortcode, parse it for the album ID and get the album data from that.
				$regex_pattern = get_shortcode_regex();
				$album_data    = false;
				preg_match_all( '/' . $regex_pattern . '/s', $maybe_album_page[0]->post_content, $regex_matches );

				// Parse the attributes.
				$defaults = array(
					'preview' => '1',
				);

				$matched = false;

				foreach ( $regex_matches[2] as $individual_match_index => $individual_match ) {

					if ( strpos( 'envira-album', $individual_match ) !== false ) {
						$attribute_str = str_replace( ' ', '&', trim( $regex_matches[3][ $individual_match_index ] ) );
						$attribute_str = str_replace( '"', '', $attribute_str );
						$attributes    = wp_parse_args( $attribute_str, $defaults );
						if ( isset( $attributes['id'] ) ) {
							$album_data = _envira_get_album( $attributes['id'] );
							$matched    = true;
						} elseif ( isset( $attributes['slug'] ) ) {
							$album_data = envira_get_album_by_slug( $attributes['slug'] );
							$matched    = true;
						}
						$individual_match_index++;
					}
				}

				if ( false === $matched ) {
					return $gallery;
				}

				if ( ! envira_albums_get_config( 'back_location', $album_data ) || envira_albums_get_config( 'back_location', $album_data ) === 'above' || envira_albums_get_config( 'back_location', $album_data ) === 'above-below' ) {

					// Prepend Back to Album Button.
					$gallery = '<a href="' . esc_url( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) . '" title="' . envira_albums_get_config( 'back_label', $album_data ) . '" class="envira-back-link">' . envira_albums_get_config( 'back_label', $album_data ) . '</a>' . $gallery; // @codingStandardsIgnoreLine

				}
			} elseif ( 'envira_album' === $maybe_album_page[0]->post_type ) {

				$album_data = _envira_get_album( $album_id );

				if ( ! $album_data ) {
					return $gallery;
				}

				if ( ! empty( $album_data ) && ( ! envira_albums_get_config( 'back_location', $album_data ) || envira_albums_get_config( 'back_location', $album_data ) === 'above' || envira_albums_get_config( 'back_location', $album_data ) === 'above-below' ) ) {
					// Prepend Back to Album Button.
					$gallery = '<a href="' . get_permalink( $album_id ) . '" title="' . envira_albums_get_config( 'back_label', $album_data ) . '" class="envira-back-link">' . envira_albums_get_config( 'back_label', $album_data ) . '</a>' . $gallery;
				}
			}
		} else {
			// Referred from an Envira Album
			// Check that Album exists.
			$album_data = envira_get_album_by_slug( $referer_url_parts[1] );
			if ( ! $album_data ) {
				return $gallery;
			}

			$album_id = $album_data['id'];

			if ( ! envira_albums_get_config( 'back_location', $album_data ) || envira_albums_get_config( 'back_location', $album_data ) === 'above' || envira_albums_get_config( 'back_location', $album_data ) === 'above-below' ) {
				// Prepend Back to Album Button.
				$gallery = '<a href="' . get_permalink( $album_id ) . '" title="' . envira_albums_get_config( 'back_label', $album_data ) . '" class="envira-back-link">' . envira_albums_get_config( 'back_label', $album_data ) . '</a>' . $gallery;
			}
		}

		// Check that Album has "Back to Album" functionality enabled.
		if ( isset( $album_data ) && ! envira_albums_get_config( 'back', $album_data ) ) {
			return $gallery_backup;
		}

		return $gallery;

	}

	/**
	 * Maybe add a back to Album link on a Gallery, if the user navigated from an Album and that Album
	 * has this functionality enabled
	 *
	 * @since 1.1.0.1
	 *
	 * @param string $gallery Gallery HTML.
	 * @param array  $data Gallery Data.
	 * @return string Gallery HTML
	 */
	public function maybe_add_back_link_append( $gallery, $data ) {

		// Check if the user was referred from an Album.
		if ( ! isset( $_SERVER['HTTP_REFERER'] ) && ! isset( $_REQUEST['album_id'] ) ) { // @codingStandardsIgnoreLine
			return $gallery;
		}

		$gallery_backup    = $gallery; // save a copy of $gallery.
		$referer_url       = false;
		$referer_url_parts = array();

		if ( isset( $_SERVER['HTTP_REFERER'] ) && ! isset( $_REQUEST['album_id'] ) ) { // @codingStandardsIgnoreLine

			// If first part of referrer URL matches the Envira Album slug, the visitor clicked on a gallery from an album.
			$referer_url       = str_replace( get_bloginfo( 'url' ), '', wp_unslash( $_SERVER['HTTP_REFERER'] ) ); // @codingStandardsIgnoreLine
			$referer_url_parts = array_values( array_filter( explode( '/', $referer_url ) ) );

			if ( ! is_array( $referer_url_parts ) || count( $referer_url_parts ) < 1 ) { // why was it 2 before?
				return $gallery;
			}

			$args             = array(
				'name'        => end( $referer_url_parts ),
				'post_type'   => array( 'page', 'post', 'envira_album' ),
				'post_status' => 'publish',
				'numberposts' => 1,
			);
			$maybe_album_page = get_posts( $args );

			if ( ! $maybe_album_page ) {
				// Giving up, if there is a page it's not published.
				return $gallery;
			}
		}

		$slug = envira_standalone_get_the_slug( 'albums' );
		if ( ( ! empty( $referer_url_parts ) && $referer_url_parts[0] !== $slug ) || ( isset( $_REQUEST['album_id'] ) ) ) { // @codingStandardsIgnoreLine

			// This might be a regular WordPress page the user has embedded an album into, so let's check.
			if ( isset( $_REQUEST['album_id'] ) ) { // @codingStandardsIgnoreLine
				$album_id = intval( $_REQUEST['album_id'] ); // @codingStandardsIgnoreLine

				$args             = array(
					'ID'          => $album_id,
					'post_type'   => array( 'page', 'post', 'envira_album' ),
					'post_status' => 'publish',
					'numberposts' => 1,
				);
				$maybe_album_page = get_posts( $args );
			} else {
				$args             = array(
					'name'        => end( $referer_url_parts ),
					'post_type'   => array( 'page', 'post' ),
					'post_status' => 'publish',
					'numberposts' => 1,
				);
				$maybe_album_page = get_posts( $args );
			}

			if ( ! $maybe_album_page ) {
				// Giving up, if there is a page it's not published.
				return $gallery;
			}

			// If it's an album standalone, we move on.
			if ( ( 'page' === $maybe_album_page[0]->post_type || 'post' === $maybe_album_page[0]->post_type ) && ! has_shortcode( $maybe_album_page[0]->post_content, 'envira-album' ) ) {
				// no shortcode, so this won't get a back link
				// update: adding a filter because if the album page doesn't have a shortcode, it might be added another way (say by Fusion Builder) so allow the user to override this.
				$maybe_album_page_override = apply_filters( 'envira_albums_is_album_page', false, $maybe_album_page[0] );
				if ( false === $maybe_album_page_override ) {
					return $gallery;
				}
			}

			if ( 'page' === $maybe_album_page[0]->post_type || 'post' === $maybe_album_page[0]->post_type ) {

				// If there is a shortcode, parse it for the album ID and get the album data from that.
				$regex_pattern = get_shortcode_regex();
				preg_match( '/' . $regex_pattern . '/s', $maybe_album_page[0]->post_content, $regex_matches );

				if ( 'envira-album' === $regex_matches[2] ) :
					// Found the album, now need to find out the ID
					// Turn the attributes into a URL parm string.
					$attribure_str = str_replace( ' ', '&', trim( $regex_matches[3] ) );
					$attribure_str = str_replace( '"', '', $attribure_str );

					// Parse the attributes.
					$defaults   = array(
						'preview' => '1',
					);
					$attributes = wp_parse_args( $attribure_str, $defaults );
					if ( isset( $attributes['id'] ) ) {
						$album_data = _envira_get_album( $attributes['id'] );
					} elseif ( isset( $attributes['slug'] ) ) {
						$album_data = envira_get_album_by_slug( $attributes['slug'] );
					} else {
						return $gallery;
					}

					// Ok, determine if the current gallery is IN the album... if not, then return.
					if ( isset( $data['id'] ) && ! array_key_exists( $data['id'], $album_data['galleries'] ) ) {
						return $gallery;
					}

				endif;

				if ( ! empty( $album_data ) && ( envira_albums_get_config( 'back_location', $album_data ) === 'below' || envira_albums_get_config( 'back_location', $album_data ) === 'above-below' ) ) {
						// Append Back to Album Button.
						$gallery = $gallery . '<a href="' . get_permalink( $album_id ) . '" title="' . envira_albums_get_config( 'back_label', $album_data ) . '" class="envira-back-link">' . envira_albums_get_config( 'back_label', $album_data ) . '</a>';
				}
			} elseif ( 'envira_album' === $maybe_album_page[0]->post_type ) {

				$album_data = _envira_get_album( $album_id );

				if ( ! $album_data ) {
					return $gallery;
				}

				if ( envira_albums_get_config( 'back_location', $album_data ) === 'below' || envira_albums_get_config( 'back_location', $album_data ) === 'above-below' ) {
						// Append Back to Album Button.
						$gallery = $gallery . '<a href="' . get_permalink( $album_id ) . '" title="' . envira_albums_get_config( 'back_label', $album_data ) . '" class="envira-back-link">' . envira_albums_get_config( 'back_label', $album_data ) . '</a>';
				}
			}
		} else {
			// Referred from an Envira Album
			// Check that Album exists.
			$album_data = envira_get_album_by_slug( $referer_url_parts[1] );
			if ( ! $album_data ) {
				return $gallery;
			}

			$album_id = $album_data['id'];

			if ( envira_albums_get_config( 'back_location', $album_data ) === 'below' || envira_albums_get_config( 'back_location', $album_data ) === 'above-below' ) {
					// Append Back to Album Button.
					$gallery = $gallery . '<a href="' . get_permalink( $album_id ) . '" title="' . envira_albums_get_config( 'back_label', $album_data ) . '" class="envira-back-link">' . envira_albums_get_config( 'back_label', $album_data ) . '</a>';
			}
		}

		// Check that Album has "Back to Album" functionality enabled.
		if ( isset( $album_data ) && ! envira_albums_get_config( 'back', $album_data ) ) {
			return $gallery_backup;
		}

		return $gallery;

	}

	/**
	 * Outputs an individual album item in the grid
	 *
	 * @since 1.2.5.0
	 *
	 * @param    string $album      Album HTML.
	 * @param    array  $data       Album Config.
	 * @param    int    $id         Album Gallery ID.
	 * @param    int    $i          Index.
	 * @return   string              Album HTML
	 */
	public function generate_album_item_markup( $album, $data, $id, $i ) {

		// Skip blank entries.
		if ( empty( $id ) ) {

			return $album;

		}

		$gallery_data = envira_get_gallery( $id );

		// Get some config values that we'll reuse for each gallery.
		$padding = absint( round( envira_albums_get_config( 'gutter', $data ) / 2 ) );

		// Get Gallery.
		$item = $data['galleries'][ $id ];
		$item = apply_filters( 'envira_albums_output_item_data', $item, $id, $data, $i );

		// Get image.
		$imagesrc         = $this->get_image_src( $item['cover_image_id'], $item, $data );
		$image_src_retina = $this->get_image_src( $item['cover_image_id'], $item, $data, false, true ); // copied from gallery shortcode.
		$placeholder      = wp_get_attachment_image_src( $item['cover_image_id'], 'medium' ); // $placeholder is null because $id is 0 for instagram? // copied from gallery shortcode

		// Get Link New Window Only When Lightbox Isn't Available For The Album.
		$link_new_window = false;

		if ( empty( $data['gallery_lightbox'] ) && ! empty( $item['link_new_window'] ) ) {
			$link_new_window = $item['link_new_window'];
		}

		$gallery_theme_name = envira_albums_get_config( 'gallery_theme', $data );

		$album = apply_filters( 'envira_albums_output_before_item', $album, $id, $item, $data, $i );

		$output = '<div id="envira-gallery-item-' . sanitize_html_class( $id ) . '" class="' . $this->get_gallery_item_classes( $item, $i, $data ) . '" style="padding-left: ' . $padding . 'px; padding-bottom: ' . envira_albums_get_config( 'margin', $data ) . 'px; padding-right: ' . $padding . 'px;" ' . apply_filters( 'envira_albums_output_item_attr', '', $id, $item, $data, $i ) . '>';

		// Display Gallery Description (Above).
		if ( isset( $data['config']['gallery_description_display'] ) && 'display-above' === $data['config']['gallery_description_display'] && 0 !== (int) $data['config']['columns'] && isset( $item['id'] ) ) {
			$output = apply_filters( 'envira_albums_output_before_gallery_description', $output, $id, $item, $data, $i );

			// Get description.
			if ( isset( $gallery_data['config']['description'] ) && $gallery_data['config']['description'] ) {

				$gallery_description = wp_kses( $gallery_data['config']['description'], envira_get_allowed_tags() );
				$output             .= '<div class="envira-album-gallery-description">' . apply_filters( 'envira_albums_output_gallery_description', $gallery_description, $id, $item, $data, $i ) . '</div>';
			}
			$output = apply_filters( 'envira_albums_output_before_gallery_description', $output, $id, $item, $data, $i );
		}

		// Display Title.
		// Note: We added the ability to add titles ABOVE in addition to below, but we still need to honor the deprecated setting.
		if ( isset( $data['config']['display_titles'] ) && 'above' === $data['config']['display_titles'] && 0 !== (int) $data['config']['columns'] ) {

			$new_window = $link_new_window ? 'target="_blank" ' : '';

			$album_title = ( ! empty( $item['link_title_gallery'] ) && 1 === intval( $item['link_title_gallery'] ) ) ? '<a ' . $new_window . ' href="' . get_permalink( $id ) . '">' . htmlspecialchars_decode( $item['title'] ) . '</a>' : htmlspecialchars_decode( $item['title'] );

			$album_title = apply_filters( 'envira_albums_album_title', $album_title, $id, $item, $data, $i );

			if ( ! empty( $item['title'] ) ) {
				$output .= '<div class="envira-album-title">' . $album_title . '</div>';
			}

			$output = apply_filters( 'envira_albums_output_after_title', $output, $id, $item, $data, $i );

		}

			$output .= '<div class="envira-gallery-item-inner">';
			$output  = apply_filters( 'envira_albums_output_before_link', $output, $id, $item, $data, $i );

			// Top Left box.
			$css_class = false; // no css classes yet.
			$css_class = apply_filters( 'envira_albums_output_dynamic_position_css', $css_class, $output, $id, $item, $data, $i, 'top-left' );

			$output .= '<div class="envira-gallery-position-overlay ' . $css_class . ' envira-gallery-top-left">';
			$output  = apply_filters( 'envira_albums_output_dynamic_position', $output, $id, $item, $data, $i, 'top-left' );
			$output .= '</div>';

			// Top Right box.
			$css_class = false; // no css classes yet.
			$css_class = apply_filters( 'envira_albums_output_dynamic_position_css', $css_class, $output, $id, $item, $data, $i, 'top-right' );

			$output .= '<div class="envira-gallery-position-overlay ' . $css_class . ' envira-gallery-top-right">';
			$output  = apply_filters( 'envira_albums_output_dynamic_position', $output, $id, $item, $data, $i, 'top-right' );
			$output .= '</div>';

			// Bottom Left box.
			$css_class = false; // no css classes yet.
			$css_class = apply_filters( 'envira_albums_output_dynamic_position_css', $css_class, $output, $id, $item, $data, $i, 'bottom-left' );

			$output .= '<div class="envira-gallery-position-overlay ' . $css_class . ' envira-gallery-bottom-left">';
			$output  = apply_filters( 'envira_albums_output_dynamic_position', $output, $id, $item, $data, $i, 'bottom-left' );
			$output .= '</div>';

			// Bottom Right box.
			$css_class = false; // no css classes yet.
			$css_class = apply_filters( 'envira_albums_output_dynamic_position_css', $css_class, $output, $id, $item, $data, $i, 'bottom-right' );

			$output .= '<div class="envira-gallery-position-overlay ' . $css_class . ' envira-gallery-bottom-right">';
			$output  = apply_filters( 'envira_albums_output_dynamic_position', $output, $id, $item, $data, $i, 'bottom-right' );
			$output .= '</div>';

			$create_link = apply_filters( 'envira_albums_output_create_link', true, $id, $item, $data, $i );

			$new_window          = $link_new_window ? 'target="_blank" ' : '';
			$gallery_images_data = envira_get_gallery_images( $id, null, $data, true, true );
			$gallery_images      = $gallery_images_data['gallery_images'];
			$sorted_ids          = $gallery_images_data['sorted_ids'];

		if ( $create_link ) {

			$css                               = isset( $item['gallery_lightbox'] ) && 1 !== intval( $item['gallery_lightbox'] ) ? '' : 'envira-gallery-link'; // check for override (located in modal).
			$css                               = envira_albums_get_config( 'lightbox', $data ) === 0 ? '' : $css; // diable if the entire lightbox for albums is deactivated, regardless of the override.
			$gallery_images_attribute          = "data-gallery-images='" . $gallery_images . "' ";
			$gallery_images_sort_ids_attribute = "data-gallery-sort-ids='" . $sorted_ids . "' ";

			if ( strpos( $gallery_images, 'cdninstagram' ) !== false ) {
				// todo: we need a better check for instagram but since album data is saved in the database without hooks, this is the best way for backwards compataiblity.
				$gallery_images_array = json_decode( $gallery_images, true );
				if ( ( empty( $gallery_images_array ) ) ) {
					$gallery_images_attribute = false;
					$css                      = false;
				} else {
					$first_element = reset( $gallery_images_array );
					if ( ( empty( $first_element ) || empty( $first_element['link'] ) ) ) {
						// checks to see if this is an instagram gallery and if there's a link in the first element (if not, likely user has selected 'no link' in the gallery settings).
						$gallery_images_attribute = false;
						$css                      = false;
					}
					$first_element = false;
				}
			}

			$output .= '<a ' . $new_window . 'href="' . apply_filters( 'envira_albums_output_gallery_href', get_permalink( $id ), $id, $item, $data, $i ) . '" ' . $gallery_images_attribute . ' ' . $gallery_images_sort_ids_attribute . ' class="envira-album-gallery-' . $id . ' ' . $css . '" title="' . wp_strip_all_tags( htmlspecialchars_decode( $item['title'] ) ) . '" ' . apply_filters( 'envira_gallery_output_link_attr', '', $id, $item, $data, $i ) . '>';

		}

		// Image.
		$output        = apply_filters( 'envira_albums_output_before_image', $output, $id, $item, $data, $i );
		$gallery_theme = envira_albums_get_config( 'columns', $data ) === 0 ? ' envira-' . envira_albums_get_config( 'justified_gallery_theme', $data ) : '';

		// Captions (for automatic layout).
		$item_caption = false;

		// Don't assume there is one.
		if ( empty( $item['caption'] ) ) {
			$item['caption'] = ''; }

		// If the user has choosen to display Gallery Description, then it's a complete override.
		if ( isset( $data['config']['gallery_description_display'] ) && $data['config']['gallery_description_display'] && 0 === (int) $data['config']['columns'] && ! empty( $gallery_data['config']['description'] ) && isset( $item['id'] ) ) {

				$item_caption = sanitize_text_field( $gallery_data['config']['description'] );

		} else {

			$caption_array = array();
			if ( envira_albums_get_config( 'display_titles', $data ) && isset( $item['title'] ) ) {
				$caption_array[] = htmlspecialchars_decode( $item['title'] );
			}
			if ( envira_albums_get_config( 'display_captions', $data ) && isset( $item['caption'] ) ) {
				$caption_array[] = esc_attr( $item['caption'] );
			}

			// Remove any empty elements.
			$caption_array = array_filter( $caption_array );

			// Seperate.
			$item_caption_seperator = apply_filters( 'envira_albums_output_seperator', ' - ', $data );
			$item_caption           = implode( $item_caption_seperator, $caption_array );

			// Add Image Count To Captions (for automatic layout).
			if ( isset( $data['config']['display_image_count'] ) && 1 === $data['config']['display_image_count'] && 0 === (int) $data['config']['columns'] ) {

				// Note: We are providing a unique filter here just for automatic layout.
				$item_caption = apply_filters( 'envira_albums_output_automatic_before_image_count', $item_caption, $id, $item, $data, $i );

				// Get count.
				if ( 'fc' !== $data['config']['type'] ) {
					$count = envira_get_gallery_image_count( str_replace( $id . '_' . $this->counter, '', $id ) );
				} elseif ( 'fc' === $data['config']['type'] ) {
					$fc    = \ Envira_Featured_Content_Shortcode::get_instance();
					$count = $fc->get_fc_data_total( $id, $data );
				}

				// Filter count label.
				$label = '(' . $count . ' ' . _n( 'Photo', 'Photos', $count, 'envira-albums' ) . ')';
				// Add a space?
				if ( strlen( $item_caption ) > 0 ) {
					$item_caption .= ' ';
				}

				$item_caption .= '<span class="envira-album-image-count">' . apply_filters( 'envira_albums_output_automatic_image_count', $label, $count ) . '</span>';

				$item_caption = apply_filters( 'envira_albums_output_automatic_after_image_count', $item_caption, $id, $item, $data, $i );

			}
		}

			// Allow HTML tags w/o issues.
			$item_caption = htmlspecialchars( $item_caption );

			// Build the image and allow filtering.
			// Update: how we build the html depends on the lazy load script.
			// Check if user has lazy loading on - if so, we add the css class.
			$envira_lazy_load = envira_albums_get_config( 'lazy_loading', $data ) === 1 ? 'envira-lazy' : '';

			// Determine/confirm the width/height of the immge.
			// $placeholder should hold it but not for instagram.
		if ( envira_albums_get_config( 'crop', $data ) ) { // the user has selected the image to be cropped.
			$output_src = $imagesrc;
		} elseif ( envira_albums_get_config( 'image_size', $data ) !== 'full' ) { // use the image being provided thanks to the user selecting a unique image size.
			$output_src = $imagesrc;
		} elseif ( ! empty( $item['src'] ) ) {
			$output_src = $item['src'];
		} elseif ( ! empty( $placeholder[0] ) ) {
			$output_src = $placeholder[0];
		} elseif ( ! empty( $item['cover_image_url'] ) ) {
			$output_src = $item['cover_image_url'];
		} else {
			$output_src = false;
		}

		if ( envira_albums_get_config( 'crop', $data ) && envira_albums_get_config( 'crop_width', $data ) ) {

			$output_width = envira_albums_get_config( 'crop_width', $data );
		} elseif ( envira_albums_get_config( 'image_size', $data ) === 'default' && envira_albums_get_config( 'crop_width', $data ) && envira_albums_get_config( 'crop_height', $data ) ) {
			$output_width = envira_albums_get_config( 'crop_width', $data );
		} elseif ( ! empty( $item['width'] ) ) {
			$output_width = $item['width'];
		} elseif ( ! empty( $placeholder[1] ) ) {
			$output_width = $placeholder[1];
		} elseif ( ! empty( $item['cover_image_url'] ) && strpos( $item['cover_image_url'], 'cdninstagram' ) !== false ) {
			// if this is an instagram image, @getimagesize might not work
			// therefore we should try to extract the size from the url itself.
			if ( strpos( $item['cover_image_url'], '150x150' ) ) {
				$output_width = '150';
			} else {
				$output_width = '150';
			}
		} else {

			$output_width = envira_albums_get_config( 'crop_width', $data ) ? envira_albums_get_config( 'crop_width', $data ) : false;

		}

		if ( envira_albums_get_config( 'crop', $data ) && envira_albums_get_config( 'crop_height', $data ) ) {
			$output_height = envira_albums_get_config( 'crop_height', $data );
		} elseif ( envira_albums_get_config( 'image_size', $data ) === 'default' && envira_albums_get_config( 'crop_width', $data ) && envira_albums_get_config( 'crop_height', $data ) ) {
			$output_height = envira_albums_get_config( 'crop_height', $data );
		} elseif ( ! empty( $placeholder[2] ) ) {
			$output_height = $placeholder[2];
		} elseif ( ! empty( $item['height'] ) ) {
			$output_height = $item['height'];
		} else {
			$output_height = envira_albums_get_config( 'justified_row_height', $data ) ? envira_albums_get_config( 'justified_row_height', $data ) : 150;
		}

		if ( intval( envira_albums_get_config( 'columns', $data ) ) === 0 ) {

			// Automatic.
			$output_item = '<img id="envira-gallery-image-' . sanitize_html_class( $id ) . '" class="envira-gallery-image envira-gallery-image-' . $i . $gallery_theme . ' ' . $envira_lazy_load . '" src="' . esc_url( $imagesrc ) . '" width="' . envira_albums_get_config( 'crop_width', $data ) . '" height="' . envira_albums_get_config( 'crop_height', $data ) . '" data-envira-width="' . $output_width . '" data-envira-height="' . $output_height . '" data-envira-src="' . esc_url( $output_src ) . '" data-caption="' . htmlentities( $item_caption ) . '" data-envira-item-id="' . $id . '" data-automatic-caption="' . $item_caption . '" data-envira-album-id="' . $data['id'] . '" data-envira-gallery-id="' . sanitize_html_class( $id ) . '" alt="' . esc_attr( $item['alt'] ) . '" title="' . wp_strip_all_tags( htmlspecialchars_decode( $item['title'] ) ) . '" ' . apply_filters( 'envira_albums_output_image_attr', '', $item['cover_image_id'], $item, $data, $i ) . ' srcset="' . ( ( $envira_lazy_load ) ? 'data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==' : esc_url( $image_src_retina ) . ' 2x' ) . '" data-safe-src="' . ( ( $envira_lazy_load ) ? 'data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==' : esc_url( $output_src ) ) . '" />';

		} else {

			// Legacy.
			$output_item = false;

			if ( $envira_lazy_load ) {

				if ( $output_height > 0 && $output_width > 0 ) {
					$padding_bottom = ( $output_height / $output_width ) * 100;
				} else {
					// this shouldn't be happening, but this avoids a debug message.
					$padding_bottom = 100;
				}
				if ( $padding_bottom > 100 ) {
					$padding_bottom = 100;
				}

				$envira_lazy_class = ( false !== strpos( $gallery_images, 'cdninstagram' ) ) ? 'envira-lazy envira-lazy-instagram-album' : 'envira-lazy';
				$output_item      .= '<div class="' . $envira_lazy_class . '" style="padding-bottom:' . $padding_bottom . '%;">';

			}

			$output_item .= '<img id="envira-gallery-image-' . sanitize_html_class( $id ) . '" class="envira-gallery-image envira-gallery-image-' . $i . $gallery_theme . '" data-envira-index="' . $i . '" src="' . esc_url( $output_src ) . '" width="' . envira_albums_get_config( 'crop_width', $data ) . '" height="' . envira_albums_get_config( 'crop_height', $data ) . '" data-envira-src="' . esc_url( $output_src ) . '" data-envira-album-id="' . $data['id'] . '" data-envira-gallery-id="' . sanitize_html_class( $id ) . '" data-envira-item-id="' . $id . '" data-caption="' . $item_caption . '" alt="' . esc_attr( $item['alt'] ) . '" title="' . wp_strip_all_tags( htmlspecialchars( $item['title'] ) ) . '" ' . apply_filters( 'envira_albums_output_image_attr', '', $item['cover_image_id'], $item, $data, $i ) . ' data-envira-srcset="' . esc_url( $output_src ) . ' 400w,' . esc_url( $output_src ) . ' 2x" srcset="' . ( ( $envira_lazy_load ) ? 'data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==' : esc_url( $image_src_retina ) . ' 2x' ) . '" />';

			if ( $envira_lazy_load ) {

				$output_item .= '</div>';

			}
		}

			$output_item = apply_filters( 'envira_albums_output_image', $output_item, $id, $item, $data, $i, $album );

			// Add image to output.
			$output .= $output_item;
			$output  = apply_filters( 'envira_albums_output_after_image', $output, $id, $item, $data, $i );

		if ( $create_link ) {
			$output .= '</a>';
		}
			$output = apply_filters( 'envira_albums_output_after_link', $output, $id, $item, $data, $i );

			// Display Title For Legacy.
			// Note: We added the ability to add titles ABOVE in addition to below, but we still need to honor the deprecated setting.
		if ( isset( $data['config']['display_titles'] ) && ( 1 === $data['config']['display_titles'] || 'below' === $data['config']['display_titles'] ) && 0 !== (int) $data['config']['columns'] ) {
			$output      = apply_filters( 'envira_albums_output_before_title', $output, $id, $item, $data, $i );
			$album_title = ( ! empty( $item['link_title_gallery'] ) && 1 === intval( $item['link_title_gallery'] ) ) ? '<a ' . $new_window . ' href="' . get_permalink( $id ) . '">' . htmlspecialchars_decode( $item['title'] ) . '</a>' : htmlspecialchars_decode( $item['title'] );
			$album_title = apply_filters( 'envira_albums_album_title', $album_title, $id, $item, $data, $i );

			if ( ! empty( $item['title'] ) ) {
				$output .= '<div class="envira-album-title">' . $album_title . '</div>';
			}

			$output = apply_filters( 'envira_albums_output_after_title', $output, $id, $item, $data, $i );
		}

			// Display Caption For Legacy.
		if ( isset( $data['config']['display_captions'] ) && 1 === $data['config']['display_captions'] && 0 !== (int) $data['config']['columns'] ) {
			$output        = apply_filters( 'envira_albums_output_before_caption', $output, $id, $item, $data, $i );
			$gallery_theme = envira_albums_get_config( 'gallery_theme', $data );

			// add a <br> if there's a line break.
			$item['caption'] = str_replace(
				'
	',
				'<br/>',
				( $item['caption'] )
			);

			$output .= '<div class="envira-album-caption">' . $item['caption'] . '</div>';
			$output  = apply_filters( 'envira_albums_output_after_caption', $output, $id, $item, $data, $i );
		}

			$output .= '</div>';

			// Display Gallery Description (Below).
		if ( isset( $data['config']['gallery_description_display'] ) && 'display-below' === $data['config']['gallery_description_display'] && 0 !== (int) $data['config']['columns'] && isset( $item['id'] ) ) {
			$output = apply_filters( 'envira_albums_output_before_gallery_description', $output, $id, $item, $data, $i );

			// Extract description from gallery.
			// Note that this doesn't care if the gallery is enabled to display on the gallery or not.
			$gallery_data = envira_get_gallery( $item['id'] );
			// Get description.
			if ( isset( $gallery_data['config']['description'] ) && $gallery_data['config']['description'] ) {

				$gallery_description = wp_kses( $gallery_data['config']['description'], envira_get_allowed_tags() );
				$output             .= '<div class="envira-album-gallery-description">' . apply_filters( 'envira_albums_output_gallery_description', $gallery_description, $id, $item, $data, $i ) . '</div>';
			}
			$output = apply_filters( 'envira_albums_output_before_gallery_description', $output, $id, $item, $data, $i );
		}

			// Display Image Count.
		if ( isset( $data['config']['display_image_count'] ) && 1 === $data['config']['display_image_count'] && 0 !== intval( $data['config']['columns'] ) ) {
			$output = apply_filters( 'envira_albums_output_before_image_count', $output, $id, $item, $data, $i );

			// Get count.
			if ( 'fc' !== $data['config']['type'] ) {
				$count = envira_get_gallery_image_count( $id );
			} elseif ( 'fc' === $data['config']['type'] && class_exists( 'Envira_Featured_Content_Shortcode' ) ) {
				$fc    = \ Envira_Featured_Content_Shortcode::get_instance();
				$count = $fc->get_fc_data_total( $id, $data );
			}

			// Filter count label.
			$label   = $count . ' ' . _n( 'Photo', 'Photos', $count, 'envira-albums' );
			$output .= '<div class="envira-album-image-count">' . apply_filters( 'envira_albums_output_image_count', $label, $count ) . '</div>';

			$output = apply_filters( 'envira_albums_output_after_image_count', $output, $id, $item, $data, $i );
		}

		$output .= '</div>';
		$output  = apply_filters( 'envira_albums_output_single_item', $output, $id, $item, $data, $i );

		// Append Album to the output.
		$album .= $output;

		// Filter the output.
		$album = apply_filters( 'envira_albums_output_after_item', $album, $id, $item, $data, $i );

		return $album;

	}

	/**
	 * Maybe sort the album galleries, if specified in the config
	 *
	 * @since 1.2.4.4
	 *
	 * @param   array $data       Album Config.
	 * @param   int   $album_id   Album ID.
	 * @return  array               Album Config
	 */
	public function maybe_sort_album( $data, $album_id ) {

		if ( isset( $data['galleries'] ) && ! is_array( $data['galleries'] ) ) {
			return $data;
		}

		// return if there's already a sorting method being passed into the shortcode (see dynamic addon - shortcode-album.php).
		if ( ! empty( $data['config']['shortcode_orderby'] ) || ! empty( $data['config']['shortcode_order'] ) ) {
			return $data;
		}

		// Get sorting method.
		$sorting_method    = (string) envira_albums_get_config( 'sorting', $data );
		$sorting_direction = envira_albums_get_config( 'sorting_direction', $data );

		// Sort images based on method.
		switch ( $sorting_method ) {
			/**
			* Random
			*/
			case '1':
			case 'random':
				// Shuffle keys.
				$keys = array_keys( $data['galleries'] );
				shuffle( $keys );

				// Rebuild array in new order.
				$new = array();
				foreach ( $keys as $key ) {
					$new[ $key ] = $data['galleries'][ $key ];
				}

				// Assign back to gallery.
				$data['galleries'] = $new;
				break;

			/**
			* Gallery Metadata
			*/
			case 'title':
			case 'caption':
			case 'alt':
			case 'date':
			case 'publish_date':
				// Get metadata.
				$keys = array();
				if ( empty( $data['galleries'] ) ) {
					break;
				}
				foreach ( $data['galleries'] as $id => $item ) {
					/**
					* If no title or publish date is specified, get it now
					* The image's title / publish date are populated on an Album save, but if the user upgraded
					* to the latest version of this Addon and hasn't saved their Album, this data might not be available yet
					*/
					if ( ! isset( $item[ $sorting_method ] ) || empty( $item[ $sorting_method ] ) ) {
						if ( 'title' === $sorting_method ) {
							$item[ $sorting_method ] = get_the_title( $id );
						}
						if ( 'publish_date' === $sorting_method || 'date' === $sorting_method ) {
							$item[ $sorting_method ] = get_the_date( 'Y-m-d', $id );
						}
					}

					// Sort.
					$keys[ $id ] = wp_strip_all_tags( $item[ $sorting_method ] );
				}

				// Sort titles / captions.
				if ( 'ASC' === $sorting_direction ) {
					asort( $keys );
				} else {
					arsort( $keys );
				}

				// Iterate through sorted items, rebuilding gallery.
				$new = array();
				foreach ( $keys as $key => $title ) {
					$new[ $key ] = $data['galleries'][ $key ];
				}

				// Assign back to gallery.
				$data['galleries'] = $new;
				break;

			/**
			* None
			* - Do nothing
			*/
			case '0':
			case '':
				break;

			/**
			* If developers have added their own sort options, let them run them here
			*/
			default:
				$data = apply_filters( 'envira_albums_sort_album', $data, $sorting_method, $album_id );
				break;

		}

		// Rebuild the galleryIDs array so it matches the new sort order.
		$data['galleryIDs'] = array();

		if ( ! empty( $data['galleries'] ) ) {
			foreach ( $data['galleries'] as $gallery_id => $gallery ) {
				$data['galleryIDs'][] = $gallery_id;
			}
		}

		return $data;

	}

	/**
	 * Builds HTML for the Album Description
	 *
	 * @since 1.6.0
	 *
	 * @param string $album Album HTML.
	 * @param array  $data Data.
	 * @return HTML
	 */
	public function description( $album, $data ) {
		$album    .= '<div class="envira-gallery-description envira-gallery-description-above">';
			$album = apply_filters( 'envira_albums_output_before_description', $album, $data );

			// Get description.
			$description = $data['config']['description'];

			// If the WP_Embed class is available, use that to parse the content using registered oEmbed providers.
		if ( isset( $GLOBALS['wp_embed'] ) ) {
			$description = $GLOBALS['wp_embed']->autoembed( $description );
		}

			// Get the description and apply most of the filters that apply_filters( 'the_content' ) would use
			// We don't use apply_filters( 'the_content' ) as this would result in a nested loop and a failure.
			$description = wptexturize( $description );
			$description = convert_smilies( $description );
			$description = wpautop( $description );
			$description = prepend_attachment( $description );

			// Requires WordPress 4.4+.
		if ( function_exists( 'wp_make_content_images_responsive' ) ) {
			$description = wp_make_content_images_responsive( $description );
		}

			// Append the description to the gallery output.
			$album .= $description;

			$album = apply_filters( 'envira_albums_output_after_description', $album, $data );
		$album    .= '</div>';

		return $album;
	}

	/**
	 * If the Gallery Lightbox config requires a different sized image to be displayed,
	 * return that image URL.
	 *
	 * @since ???
	 *
	 * @param int   $id      The image attachment ID to use.
	 * @param array $item  Gallery item data.
	 * @param array $data  The gallery data to use for retrieval.
	 * @return array       Image
	 */
	public function get_lightbox_src( $id, $item, $data ) {

		// Check gallery config.
		$image_size = envira_albums_get_config( 'lightbox_image_size', $data );

		// check if the url is a valid image if not return it.
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

		// Return.
		return $item;

	}

	/**
	 * Helper method for adding custom album classes.
	 *
	 * @since 1.1.1
	 *
	 * @param array $data The album data to use for retrieval.
	 * @return string     String of space separated album classes.
	 */
	public function get_album_classes( $data ) {

		// Set default class.
		$classes   = array();
		$classes[] = 'envira-gallery-wrap';

		// Add custom class based on data provided.
		$classes[] = 'envira-gallery-theme-' . envira_albums_get_config( 'gallery_theme', $data );
		$classes[] = 'envira-lightbox-theme-' . envira_albums_get_config( 'lightbox_theme', $data );

		// If we have custom classes defined for this gallery, output them now.
		foreach ( (array) envira_albums_get_config( 'classes', $data ) as $class ) {
			$classes[] = $class;
		}

		// If the gallery has RTL support, add a class for it.
		if ( envira_albums_get_config( 'rtl', $data ) ) {
			$classes[] = 'envira-gallery-rtl';
		}

		// If the user has selected an alignment for this gallery, add a class for it.
		if ( envira_albums_get_config( 'album_alignment', $data ) ) {
			$classes[] = 'envira-gallery-align-' . envira_albums_get_config( 'album_alignment', $data );
		}

		// If the user has overrided the default width, add a class for it.
		if ( envira_albums_get_config( 'album_width', $data ) && envira_albums_get_config( 'album_width', $data ) !== 100 ) {
			$classes[] = 'envira-gallery-width-' . envira_albums_get_config( 'album_width', $data );
		}

		// Allow filtering of classes and then return what's left.
		$classes = apply_filters( 'envira_albums_output_classes', $classes, $data );
		return trim( implode( ' ', array_map( 'trim', array_map( 'sanitize_html_class', array_unique( $classes ) ) ) ) );

	}

	/**
	 * Helper method for adding custom width.
	 *
	 * @since 1.1.1
	 *
	 * @param array $data The album data to use for retrieval.
	 * @return string     String of style attr.
	 */
	public function get_custom_width( $data ) {

		$html = false;

		if ( envira_albums_get_config( 'album_width', $data ) && envira_albums_get_config( 'album_width', $data ) !== 100 ) {
			$html = 'style="width:' . intval( envira_albums_get_config( 'album_width', $data ) ) . '%"';
		}

		// Allow filtering of this style.
		return apply_filters( 'envira_albums_output_style', $html, $data );

	}

	/**
	 * Helper method for adding custom gallery classes.
	 *
	 * @since 1.0.4
	 *
	 * @param array $item Array of item data.
	 * @param int   $i      The current position in the gallery.
	 * @param array $data The gallery data to use for retrieval.
	 * @return string     String of space separated gallery item classes.
	 */
	public function get_gallery_item_classes( $item, $i, $data ) {

		// Set default class.
		$classes   = array();
		$classes[] = 'envira-gallery-item';
		$classes[] = 'enviratope-item';
		$classes[] = 'envira-gallery-item-' . $i;

		// Allow filtering of classes and then return what's left.
		$classes = apply_filters( 'envira_albums_output_item_classes', $classes, $item, $i, $data );
		return trim( implode( ' ', array_map( 'trim', array_map( 'sanitize_html_class', array_unique( $classes ) ) ) ) );

	}

	/**
	 * Helper method to retrieve the proper image src attribute based on gallery settings.
	 *
	 * @since 1.6.0
	 *
	 * @param int   $id      The image attachment ID to use.
	 * @param array $item  Gallery item data.
	 * @param array $data  The gallery data to use for retrieval.
	 * @param bool  $mobile Whether or not to retrieve the mobile image.
	 * @return string      The proper image src attribute for the image.
	 */
	public function get_image_src( $id, $item, $data, $mobile = false ) {

		// Detect if user is on a mobile device - if so, override $mobile flag which may be manually set
		// by out of date addons or plugins.
		$type = envira_mobile_detect()->isMobile() && envira_albums_get_config( 'mobile', $data ) ? 'mobile' : 'crop'; // 'crop' is misleading here - it's the key that stores the thumbnail width + height
		// Get the full image src. If it does not return the data we need, return the image link instead.
		$image = ( isset( $item['cover_image_url'] ) ? $item['cover_image_url'] : '' );

		// Fallback to image ID.
		if ( empty( $image ) ) {
			$src   = wp_get_attachment_image_src( $id, 'full' );
			$image = ! empty( $src[0] ) ? $src[0] : false;
		}

		// Fallback to item source.
		if ( ! $image ) {
			$image = ! empty( $item['src'] ) ? $item['src'] : false;
			if ( ! $image ) {
				return apply_filters( 'envira_album_no_image_src', $id, $item, $data );
			}
		}

		$crop = envira_albums_get_config( 'crop', $data );

		if ( $crop || 'mobile' === $type ) {

			$args = apply_filters(
				'envira_gallery_crop_image_args',
				array(
					'position' => 'c',
					'width'    => envira_albums_get_config( $type . '_width', $data ),
					'height'   => envira_albums_get_config( $type . '_height', $data ),
					'quality'  => 100,
					'retina'   => false,
				)
			);

			// Filter.
			$args = apply_filters( 'envira_gallery_crop_image_args', $args );

			// Make sure we're grabbing the full image to crop.
			$src = apply_filters( 'envira_gallery_crop_image_src', wp_get_attachment_image_src( $id, 'full' ), $id, $item, $data, $this->is_mobile );

			$common = \ Envira_Gallery_Common::get_instance();

			$resized_image = envira_resize_image( $image, $args['width'], $args['height'], envira_albums_get_config( 'crop', $data ), $args['position'], $args['quality'], $args['retina'], $data );

			// If there is an error, possibly output error message and return the default image src.
			if ( ! is_wp_error( $resized_image ) ) {

				return apply_filters( 'envira_albums_image_src', $resized_image, $id, $item, $data );

			}
		}
		// return full image.
		return apply_filters( 'envira_albums_image_src', $image, $id, $item, $data );

	}

	/**
	 * I'm sure some plugins mean well, but they go a bit too far trying to reduce
	 * conflicts without thinking of the consequences.
	 *
	 * 1. Prevents Foobox from completely borking envirabox as if Foobox rules the world.
	 *
	 * @since 1.6.0
	 */
	public function plugin_humility() {

		if ( class_exists( 'fooboxV2' ) ) {
			remove_action( 'wp_footer', array( $GLOBALS['foobox'], 'disable_other_lightboxes' ), 200 );
		}

	}

	/**
	 * Outputs only the first gallery of the album inside a regular <div> tag
	 * to avoid styling issues with feeds.
	 *
	 * @since 1.0.5
	 *
	 * @param array $data      Array of album data.
	 * @return string $gallery Custom album output for feeds.
	 */
	public function do_feed_output( $data ) {

		// Check the album has galleries.
		if ( ! isset( $data['galleries'] ) || count( $data['galleries'] ) === 0 ) {
			return '';
		}

		// Iterate through albums, getting the first image of the first gallery.
		$gallery = '<div class="envira-gallery-feed-output">';
		foreach ( $data['galleries'] as $id => $item ) {
			$imagesrc = $this->get_image_src( $item['cover_image_id'], $item, $data );
			$gallery .= '<img class="envira-gallery-feed-image" src="' . esc_url( $imagesrc ) . '" title="' . trim( htmlspecialchars_decode( $item['title'] ) ) . '" alt="' . trim( esc_html( $item['alt'] ) ) . '" />';
			break;
		}
		$gallery .= '</div>';

		return apply_filters( 'envira_gallery_feed_output', $gallery, $data );

	}

	/**
	 * Returns a set of indexable image links to allow SEO indexing for preloaded images.
	 *
	 * @since 1.6.0
	 *
	 * @param mixed $id       The slider ID to target.
	 * @return string $images String of indexable image HTML.
	 */
	public function get_indexable_images( $id ) {

		// If there are no images, don't do anything.
		$images = '';
		$i      = 1;
		if ( empty( $this->index[ $id ] ) ) {
			return $images;
		}

		foreach ( (array) $this->index[ $id ] as $attach_id => $data ) {
			$images .= '<img src="' . esc_url( $data['src'] ) . '" alt="' . esc_attr( $data['alt'] ) . '" />';
			$i++;
		}

		return apply_filters( 'envira_gallery_indexable_images', $images, $this->index, $id );

	}

	/**
	 * Outputs Breadcrumb navigation on an Album, if the Album has this functionality enabled
	 *
	 * @since 1.0.0
	 *
	 * @param string $html          Album HTML.
	 * @param array  $album_data    Album Data.
	 * @return string                   Album HTML
	 */
	public function output_album_breadcrumbs( $html, $album_data ) {

		if ( ( false !== $this->is_mobile && ! envira_albums_get_config( 'breadcrumbs_enabled', $album_data ) )
			|| false === $this->is_mobile && ! envira_albums_get_config( 'breadcrumbs_enabled_mobile', $album_data ) ) {
			return $html;
		}

		// Check we're on a standalone Album (we can't display breadcrumbs for embedded Albums, as we can never
		// determine the referring Album when clicking a Gallery).
		// Check we're viewing a single gallery.
		if ( ! is_singular( 'envira_album' ) ) {
			$html .= $this->breadcrumb_html( $album_data['id'], '', envira_albums_get_config( 'breadcrumbs_separator', $album_data ) );
			return $html;
		}

		// Prepend breadcrumbs to HTML.
		$html .= $this->breadcrumb_html( $album_data['id'], '', envira_albums_get_config( 'breadcrumbs_separator', $album_data ) );

		// Return.
		return $html;

	}

	/**
	 * Outputs Breadcrumb navigation on a Gallery, if the user navigated from an Album and that Album
	 * has this functionality enabled
	 *
	 * @since 1.0.0
	 *
	 * @param string $html           Gallery HTML.
	 * @param array  $gallery_data   Gallery Data.
	 * @return string                    Gallery HTML
	 */
	public function output_gallery_breadcrumbs( $html, $gallery_data ) {

		// Check we got to this Gallery from an Album.
		if ( ! $this->referred_from_album() ) {
			return $html;
		}

		$album_slug = $this->get_album_slug_from_referrer_url();

		if ( empty( $album_slug ) ) {
			return $html;
		}

		// Get Album.
		$album_data = envira_get_album_by_slug( $album_slug );

		// Bail if no data.
		if ( ! $album_data ) {
			return $html;
		}

		// Check that Album has Breadcrumb functionality enabled.
		if ( ( false !== $this->is_mobile && ! envira_albums_get_config( 'breadcrumbs_enabled', $album_data ) )
			|| false === $this->is_mobile && ! envira_albums_get_config( 'breadcrumbs_enabled_mobile', $album_data ) ) {
			return $html;
		}

		// Make sure this is unqueued because sometimes it won't be, such as with password protected pages.
		wp_enqueue_style( ENVIRA_SLUG . '-style' );

		// Prepend breadcrumbs to HTML.
		$html .= $this->breadcrumb_html( $album_data['id'], $gallery_data['id'], envira_albums_get_config( 'breadcrumbs_separator', $album_data ) );

		// Return.
		return $html;

	}

	/**
	 * Determines whether we were referred to this gallery from an album
	 *
	 * @since 1.0
	 *
	 * @return bool Referred to Gallery from Album
	 */
	private function referred_from_album() {

		// Check we're viewing a single gallery.
		if ( ! is_singular( 'envira' ) ) {
			return false;
		}

		// Check if the user was referred from an Album.
		if ( ! isset( $_SERVER['HTTP_REFERER'] ) ) {
			return false;
		}

		// If first part of referrer URL matches the Envira Album slug, the visitor clicked on a gallery from an album.
		$referer_url       = str_replace( get_bloginfo( 'url' ), '', wp_unslash( $_SERVER['HTTP_REFERER'] ) ); // @codingStandardsIgnoreLine
		$referer_url_parts = array_values( array_filter( explode( '/', $referer_url ) ) );
		if ( ! is_array( $referer_url_parts ) || count( $referer_url_parts ) < 2 ) {
			return false;
		}

		return true;

	}

	/**
	 * Returns the Album Slug from the referrer
	 *
	 * @since 1.0
	 *
	 * @return string Album Slug
	 */
	private function get_album_slug_from_referrer_url() {

		$referer_url       = str_replace( get_bloginfo( 'url' ), '', wp_unslash( $_SERVER['HTTP_REFERER'] ) ); // @codingStandardsIgnoreLine
		$referer_url_parts = array_values( array_filter( explode( '/', $referer_url ) ) );
		$album_slug        = $referer_url_parts[ count( $referer_url_parts ) - 1 ];

		return $album_slug;

	}

	/**
	 * Returns HTML markup for breadcrumb navigation
	 *
	 * @since 1.0
	 *
	 * @param array $album_id       Album ID.
	 * @param array $gallery_id     Gallery ID.
	 * @param array $separator     Seperator.
	 * @param array $album_post_id     Album Post ID.
	 * @return string                HTML
	 */
	private function breadcrumb_html( $album_id, $gallery_id = '', $separator = '', $album_post_id = false ) {

		global $post;

		// Start HTML.
		$html = '<div class="envira-breadcrumbs">
		<span xmlns:v="http://rdf.data-vocabulary.org/#">';

		// Breadcrumbs.
		$breadcrumbs = array();

		// Home.
		$breadcrumbs[] = array(
			'title' => get_bloginfo( 'name' ),
			'url'   => get_bloginfo( 'url' ),
		);

		// Album.
		if ( $album_post_id ) {

			// display the POST the album is in, not the album directly... if there is $album_post.
			$breadcrumbs[] = array(
				'title' => get_the_title( $album_post_id ),
				'url'   => get_permalink( $album_post_id ),
				'id'    => $album_post_id,
			);

		} else {

			if ( $post && $post->ID !== $album_id && $post->ID !== $gallery_id ) {
				$breadcrumbs[] = array(
					'title' => get_the_title( $post->ID ),
					'url'   => get_permalink( $post->ID ),
					'id'    => $post->ID,
				);
			}
			$breadcrumbs[] = array(
				'title' => get_the_title( $album_id ),
				'url'   => get_permalink( $album_id ),
				'id'    => $album_id,
			);
		}

		// Gallery.
		if ( ! empty( $gallery_id ) ) {
			$breadcrumbs[] = array(
				'title' => get_the_title( $gallery_id ),
				'url'   => get_permalink( $gallery_id ),
			);
		}

		$breadcrumbs = apply_filters( 'envira_breadcrumbs_data', $breadcrumbs, $album_id, $gallery_id, $separator, $album_post_id );

		// Iterate through breadcrumbs.
		foreach ( $breadcrumbs as $index => $breadcrumb ) {
			// Create HTML based on whether this is the last breadcrumb or not.
			if ( ( count( $breadcrumbs ) - 1 === $index ) ) {
				// Last.
				$html .= ' <span class="breadcrumb_last">' . $breadcrumb['title'] . '</span>';
			} else {
				// Any other.
				$html .= ' <span class="envira-breadcrumb" typeof="v:Breadcrumb">
					<a href="' . $breadcrumb['url'] . '" rel="v:url" property="v:title">' . $breadcrumb['title'] . '</a>
				</span>' . $separator;
			}
		}

		// End HTML.
		$html .= '</span>
		</div>';

		// Return.
		return apply_filters( 'envira_breadcrumbs_html', $html, $breadcrumbs, $album_id, $gallery_id, $separator, $album_post_id );

	}

	/**
	 * When Yoast SEO / WordPress SEO Plugin outputs its breadcrumbs, if we
	 * are viewing a Gallery embedded with an Album, add the Album to the breadcrumbs
	 * - Remove the deepest / child term, so we're just left with the top level taxonomy term
	 * - Add the Advice Centre Page as a breadcrumb before the top level taxonomy term
	 *
	 * This changes Home > Gallery to
	 * Home > Album > Gallery
	 *
	 * @since 1.0
	 *
	 * @param array $crumbs  Breadcrumbs.
	 * @return array         Breadcrumbs
	 */
	public function change_wpseo_breadcrumb_links( $crumbs ) {

		// Check we got to this Gallery from an Album.
		if ( ! $this->referred_from_album() ) {
			return $crumbs;
		}

		// Get Album Slug.
		$album_slug = $this->get_album_slug_from_referrer_url();
		if ( empty( $album_slug ) ) {
			return $crumbs;
		}

		// Get Album.
		$album_data = envira_get_album_by_slug( $album_slug );

		// Check that Album has Breadcrumb functionality enabled.
		if ( ! envira_albums_get_config( 'breadcrumbs_enabled_yoast', $album_data ) ) {
			return $crumbs;
		}

		// Setup container for new breadcrumbs and add the Home Page to it.
		$new_crumbs = array( 0 => $crumbs[0] );

		// Move Gallery to last elemend.
		$new_crumbs[2] = $crumbs[1];

		// Inject Album to 1st element.
		$new_crumbs[1] = array(
			'id' => $album_data['id'],
		);

		// Sort array.
		ksort( $new_crumbs );

		// Return.
		return $new_crumbs;

	}

	/**
	 * Determines if to cache an album.
	 *
	 * @param boolean $should_cache  True/False.
	 * @param boolean $album_data  Album Data.
	 * @return boolean Should Cache.
	 */
	public function maybe_change_cache( $should_cache, $album_data = false ) {

		if ( isset( $album_data['config']['sorting'] ) && 1 === intval( $album_data['config']['sorting'] ) ) {
			return false;
		}

		return $should_cache;

	}

}

