<?php
/**
 * Ajax class.
 *
 * @since 1.0.0
 *
 * @package Envira_Gallery
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Envira\Frontend\Shortcode as Shortcode;
/**
 * Ajax class.
 *
 * @since 1.1.3
 *
 * @package Envira_Pagination
 * @author  Envira Team
 */
class Envira_Pagination_AJAX {

	/**
	 * Holds the class object.
	 *
	 * @since 1.1.3
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.1.3
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
	 * Primary class constructor.
	 *
	 * @since 1.1.3
	 */
	public function __construct() {

		// Get Gallery/Album Items.
		add_action( 'wp_ajax_envira_pagination_get_items', array( $this, 'get_items' ) );
		add_action( 'wp_ajax_nopriv_envira_pagination_get_items', array( $this, 'get_items' ) );

		// Get Gallery/Album Page.
		add_action( 'wp_ajax_envira_pagination_get_page', array( $this, 'get_page' ) );
		add_action( 'wp_ajax_nopriv_envira_pagination_get_page', array( $this, 'get_page' ) );

	}

	/**
	 * Returns HTML markup for the required Gallery ID / Album ID and Page
	 *
	 * @since 1.1.3
	 */
	public function get_items() {

		// Check nonce.
		check_ajax_referer( 'envira-pagination', 'nonce' );

		// Prepare variables.
		$tags    = false;
		$dynamic = false;

		$page    = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : false;
		$type    = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : false;
		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : false;

		// If this is a dynamic gallery generated by tags, extract the tag so we can get the gallery photos.
		if ( substr( $post_id, 0, strlen( $post_id ) ) === 'tags_' ) {
			$post_id = explode( '_', $post_id );
			if ( ! empty( $post_id[1] ) ) {
				$tags    = sanitize_text_field( $post_id[1] );
				$dynamic = true;
				$type    = 'dynamic-' . $type;
			}
		} elseif ( substr( $type, 0, 3 ) === 'fc_' ) {
			$type    = 'fc';
			$post_id = array_map( 'absint', explode( '_', $post_id ) );
		} else {
			// this was the former logic, not allowing for dynamic on it's own.
			$post_id = array_map( 'absint', explode( '_', $post_id ) );
		}

		if ( empty( $post_id ) ) {
			wp_send_json_error( __( 'No Gallery or Album ID Specified.', 'envira-pagination' ) );
		}
		if ( empty( $page ) ) {
			wp_send_json_error( __( 'No page parameter specified.', 'envira-pagination' ) );
		}
		if ( empty( $type ) ) {
			wp_send_json_error( __( 'No type parameter specified.', 'envira-pagination' ) );
		}

		// Depending on the type, get the subset of data we need.
		switch ( $type ) {

			/**
			* Featured Content (Addon)
			*/
			case 'fc':
				$data = Envira_Gallery::get_instance()->get_gallery( $post_id[0] );

				if ( ! $data ) {
					wp_send_json_error();
				}
				$fc_images = Envira_Featured_Content_Shortcode::get_instance()->get_fc_data( $post_id[0], $data );

				if ( ! $fc_images ) {
					wp_send_json_error();
				}

				// Get gallery shortcode class instance.
				$instance = Envira_Gallery_Shortcode::get_instance();

				// Insert images into gallery.
				$data['gallery'] = $fc_images;

				// Get some gallery configuration.
				$images_per_page = absint( $instance->get_config( 'pagination_images_per_page', $data ) );

				$sorting_method = (string) $instance->get_config( 'random', $data );
				// Determine which page we are on, and define the start index from a zero based index.
				if ( ( empty( $sorting_method ) ) || ( ! empty( $sorting_method ) && '1' !== $sorting_method ) ) {
					$start = ( $page - 1 ) * $images_per_page;
				} else {
					// If it's all random, we don't really want to page through, just select the next available non-exceptions.
					$start = 0;
				}

				// Get the subset of images.
				$data['gallery'] = array_slice( $data['gallery'], $start, $images_per_page, true );

				// For each image, build the HTML markup we want to append to the existing gallery.
				$html = '';
				$i    = ( $start + 1 );
				foreach ( $data['gallery'] as $id => $image ) {
					$html = $instance->generate_gallery_item_markup( $html, $data, $image, $id, $i );
					$i++;
				}

				break;

			/**
			* Album
			*/
			case 'album':
				// Get Album.
				$data = Envira_Albums::get_instance()->get_album( $post_id[0] );
				if ( ! $data ) {
					wp_send_json_error();
				}

				// Get album shortcode class instance.
				$instance = Envira_Albums_Shortcode::get_instance();

				// Get some album configuration.
				$galleries_per_page = absint( $instance->get_config( 'pagination_images_per_page', $data ) );

				// Determine which page we are on, and define the start index from a zero based index.
				$start = ( ( $page - 1 ) * $galleries_per_page );

				// Get the subset of galleries (the value was gallery_ids before, changed to galleryIDs).
				$gallery_ids        = array_slice( $data['galleryIDs'], $start, $galleries_per_page, true );
				$data['galleryIDs'] = $gallery_ids;

				// For each image, build the HTML markup we want to append to the existing album.
				$html = '';
				$i    = ( $start + 1 );
				foreach ( $data['galleryIDs'] as $id ) {
					$html = $instance->generate_album_item_markup( $html, $data, $id, $i );
					$i++;
				}
				break;

			/**
			* Gallery
			*/
			case 'gallery':
				// Get gallery.
				$data_transient = get_transient( '_eg_fragment_gallery_random_sort_' . $post_id[0] );
				$data           = ! empty( $data_transient ) ? $data_transient : Envira_Gallery::get_instance()->get_gallery( $post_id[0] );
				if ( ! $data ) {
					wp_send_json_error();
				}

				// Get gallery shortcode class instance.
				$instance = Envira_Gallery_Shortcode::get_instance();

				// Get any passed in exclusions.
				$exclusions = false;
				if ( isset( $_POST['exclusions'] ) ) {
					$exclusions = sanitize_text_field( wp_unslash( $_POST['exclusions'] ) );
				}

				// Unless we sort the gallery, we might see duplicate photos and other wierd things
				// Exception: random galleries that are transient cached, because of pagination reasons
				// Added: Exclusions is an array of IDs not to bring back (because, for example, we might have already displayed them in a random pagination ).
				if ( intval( $data['config']['random'] ) !== 1 ) {
					$data = $instance->maybe_sort_gallery( $data, $post_id[0], $exclusions );
				}

				if ( ! $data ) {
					wp_send_json_error();
				}

				// Get some gallery configuration.
				$images_per_page = absint( $instance->get_config( 'pagination_images_per_page', $data ) );

				$sorting_method = (string) $instance->get_config( 'random', $data );
				// Determine which page we are on, and define the start index from a zero based index.
				if ( ( empty( $sorting_method ) ) || ( ! empty( $sorting_method ) && '1' !== $sorting_method ) ) {
					$start = ( $page - 1 ) * $images_per_page;
				} else {
					// If it's all random, we don't really want to page through, just select the next available non-exceptions.
					$start = ( $page - 1 ) * $images_per_page;
				}

				// Get the subset of images.
				$data['gallery'] = array_slice( $data['gallery'], $start, $images_per_page, true );

				if ( ! $data['gallery'] ) {
					echo false;
					exit;
				}

				// For each image, build the HTML markup we want to append to the existing gallery.
				$html = '';
				$i    = ( $start + 1 );
				foreach ( $data['gallery'] as $id => $image ) {
					$html = $instance->generate_gallery_item_markup( $html, $data, $image, $id, $i );
					$i++;
				}

				break;

			/**
			* Dynamic Gallery
			*/
			case 'dynamic-gallery':
				// We need to grab the data from the dynamic settings.
				$dynamic_id = Envira_Dynamic_Common::get_instance()->get_gallery_dynamic_id();
				$defaults   = get_post_meta( $dynamic_id, '_eg_gallery_data', true );

				// double check to make sure $tags is an array.
				if ( ! is_array( $tags ) ) {
					$tags = array( $tags );
				}

				// next we get the gallery, passing the $tags we grabbed, the $defaults, and the $post_id (which acts as the id)
				// if $post_id is an array, we need to make it a string to pass into the get_gallery_by_tags.
				if ( is_array( $post_id ) ) {
					$post_id_to_pass = implode( '_', $post_id );
				} else {
					$post_id_to_pass = $post_id;
				}

				$tags_shortcode_instance = Envira_Tags_Shortcode::get_instance();
				$data                    = $tags_shortcode_instance->get_gallery_by_tags( $tags, $defaults['config'], $post_id_to_pass ); /* was post_id */

				if ( ! $data ) {
					wp_send_json_error();
				}

				// Get gallery shortcode class instance.
				$instance = Envira_Gallery_Shortcode::get_instance();

				// Unless we sort the gallery, we might see duplicate photos and other wierd things.
				$data = $instance->maybe_sort_gallery( $data, $post_id[0] );

				// Get some gallery configuration.
				$images_per_page = absint( $instance->get_config( 'pagination_images_per_page', $data ) );

				// Determine which page we are on, and define the start index from a zero based index.
				$start = ( ( $page - 1 ) * $images_per_page );

				// Get the subset of images.
				$data['gallery'] = array_slice( $data['gallery'], $start, $images_per_page, true );

				// For each image, build the HTML markup we want to append to the existing gallery.
				$html = '';
				$i    = ( $start + 1 );
				foreach ( $data['gallery'] as $id => $image ) {
					$html = $instance->generate_gallery_item_markup( $html, $data, $image, $id, $i );
					$i++;
				}
				break;

			/**
			* Instagram
			*/
			case 'instagram':
				$data = isset( $_POST['envira_post_id'] ) ? Envira_Gallery::get_instance()->get_gallery( intval( $_POST['envira_post_id'] ) ) : false;
				if ( ! $data ) {
					wp_send_json_error();
				}
				$data['gallery'] = isset( $_POST['envira_post_id'] ) ? Envira_Instagram_Shortcode::get_instance()->_get_instagram_data( intval( $_POST['envira_post_id'] ), $data ) : false;
				if ( ! $data['gallery'] ) {
					$html = '';
					break;
				}

				// Get gallery shortcode class instance.
				$instance = Envira_Gallery_Shortcode::get_instance();

				// Unless we sort the gallery, we might see duplicate photos and other wierd things.
				$data = $instance->maybe_sort_gallery( $data, intval( $_POST['envira_post_id'] ) );

				// Get some gallery configuration.
				$images_per_page = absint( $instance->get_config( 'pagination_images_per_page', $data ) );

				// Determine which page we are on, and define the start index from a zero based index.
				$start = ( ( $page - 1 ) * $images_per_page );

				// Get the subset of images.
				$data['gallery'] = array_slice( $data['gallery'], $start, $images_per_page, true );

				// For each image, build the HTML markup we want to append to the existing gallery.
				$html = '';
				$i    = ( $start + 1 );
				foreach ( $data['gallery'] as $id => $image ) {
					$html = $instance->generate_gallery_item_markup( $html, $data, $image, $id, $i );
					$i++;
				}
				break;

		}

		// Output HTML.
		echo $html; // @codingStandardsIgnoreLine - Generating Unpredictable Markup From Other Envira Functions
		die();

	}

	/**
	 * Returns HTML markup for the required Gallery ID / Album ID Page
	 *
	 * @since 1.1.7
	 */
	public function get_page() {

		if ( empty( $_POST['post_id'] ) ) {
			return;
		}

		// Check nonce.
		check_ajax_referer( 'envira-pagination', 'nonce' );

		$post_id_temp = sanitize_text_field( wp_unslash( $_POST['post_id'] ) );
		$dynamic      = false;
		$gallery_sort = false;

		if ( substr( $post_id_temp, 0, 7 ) === 'custom_' ) {
			// since this ID starts off with 'custom', we know it's a dynamic gallery
			// sanitize_html_class converted.
			$post_id_temp = str_replace( '_', '-', $post_id_temp );
		}

		// Prepare variables.
		$post_id_array  = array_map( 'absint', explode( '_', $post_id_temp ) );
		$post_type      = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : false;
		$post_id        = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : false;
		$page           = isset( $_POST['page'] ) ? sanitize_text_field( wp_unslash( $_POST['page'] ) ) : false;
		$gallery_sort   = isset( $_POST['gallery_sort'] ) ? sanitize_text_field( wp_unslash( $_POST['gallery_sort'] ) ) : false;
		$envira_post_id = isset( $_POST['envira_post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['envira_post_id'] ) ) : false;
		$tag            = isset( $_POST['envira-tag'] ) ? sanitize_text_field( wp_unslash( $_POST['envira-tag'] ) ) : false;

		if ( substr( $post_type, 0, 3 ) === 'fc_' ) {
			$post_id = array_map( 'absint', explode( '_', $post_id ) );
		} elseif ( ! is_int( $post_id_array ) && 0 === $post_id_array[0] ) { // if the first element in the array is NOT a number or is zero, then it is probably a dynamic id.
			$post_id = explode( '_', $post_id_temp );
			if ( isset( $_POST['gallery_sort'] ) ) {
				$gallery_sort = implode( ',', $gallery_sort );
			}
			$dynamic = true;
		} elseif ( isset( $_POST['gallery_sort'] ) && is_array( $_POST['gallery_sort'] ) ) {
			$post_id      = $post_id_array;
			$gallery_sort = array_map( 'absint', $_POST['gallery_sort'] );
		} elseif ( 'instagram' === $post_type ) {
			$data = Envira_Gallery::get_instance()->_get_gallery( intval( $envira_post_id ) );
		} elseif ( $post_id ) {
			$post_id = array( esc_html( $post_id ) );
		}

		$page = absint( $page );
		if ( substr( $post_type, 0, 3 ) === 'fc_' ) {
			$type = 'fc';
		} else {
			$type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : false;
		}

		if ( empty( $post_id ) && 'instagram' !== $type ) {
			wp_send_json_error( __( 'No Gallery or Album ID Specified.', 'envira-pagination' ) );
		}
		if ( empty( $page ) && 'instagram' !== $type ) {
			wp_send_json_error( __( 'No page parameter specified.', 'envira-pagination' ) );
		}
		if ( empty( $type ) ) {
			wp_send_json_error( __( 'No type parameter specified.', 'envira-pagination' ) );
		}

		// Depending on the type, get the subset of data we need.
		switch ( $type ) {

			/**
			* Featured Content (Addon)
			*/
			case 'fc':
				$data = Envira_Gallery::get_instance()->get_gallery( $post_id[0] );

				if ( ! $data ) {
					wp_send_json_error();
				}

				$fc_images = Envira_Featured_Content_Shortcode::get_instance()->get_fc_data( $post_id[0], $data );

				if ( ! $fc_images ) {
					wp_send_json_error();
				}

				// Get gallery shortcode class instance.
				$instance = Envira_Gallery_Shortcode::get_instance();

				// Insert images into gallery.
				$data['gallery'] = $fc_images;

				// Get some gallery configuration.
				$images_per_page = absint( $instance->get_config( 'pagination_images_per_page', $data ) );

				$sorting_method = (string) $instance->get_config( 'random', $data );
				// Determine which page we are on, and define the start index from a zero based index.
				if ( ( empty( $sorting_method ) ) || ( ! empty( $sorting_method ) && '1' !== $sorting_method ) ) {
					$start = ( $page - 1 ) * $images_per_page;
				} else {
					// If it's all random, we don't really want to page through, just select the next available non-exceptions.
					$start = 0;
				}

				// Get the subset of images.
				$data['gallery'] = array_slice( $data['gallery'], $start, $images_per_page, true );

				$array_to_pass = array(
					'id'        => intval( $_POST['post_id'] ),
					'presorted' => true,
					'counter'   => ! empty( $post_id[1] ) ? $post_id[1] : 1,
					'images'    => $fc_images,
				);

				$markup = Envira_Gallery_Shortcode::get_instance()->shortcode( $array_to_pass );

				break;

			/**
			* Album
			*/
			case 'album':
				$array_to_pass = array(
					'id'        => $post_id[0],
					'presorted' => true,
					'counter'   => ! empty( $post_id[1] ) ? $post_id[1] : 1,
				);

				if ( $dynamic ) {
					$array_to_pass['dynamic'] = $post_id[0];
				}

				if ( $gallery_sort ) {
					$array_to_pass['images'] = $gallery_sort;
				}

				Envira_Albums_Shortcode::get_instance()->gallery_sort[ ! empty( $post_id[1] ) ? $post_id[0] . '_' . $post_id[1] : $post_id[0] ] = $gallery_sort;
				$markup = Envira_Albums_Shortcode::get_instance()->shortcode( $array_to_pass );
				break;

			/**
			* Gallery
			*/
			case 'gallery':
				$shortcode     = new Shortcode();
				$array_to_pass = array(
					'id'        => $post_id[0],
					'presorted' => false,
					'counter'   => ! empty( $post_id[1] ) ? $post_id[1] : 1,
				);

				if ( $dynamic ) {
					$array_to_pass['dynamic'] = $post_id[0];
				}

				if ( $gallery_sort ) {
					$array_to_pass['images'] = $gallery_sort;
				}
				$markup = $shortcode->shortcode( $array_to_pass );
				break;

			/**
			* Instagram
			*/
			case 'instagram':
				$array_to_pass = array(
					'id'        => ( ! empty( $_POST['envira_post_id'] ) ) ? intval( $_POST['envira_post_id'] ) : false,
					'presorted' => true,
					'counter'   => ! empty( $post_id[1] ) ? $post_id[1] : 1,
				);

				if ( $dynamic ) {
					$array_to_pass['dynamic'] = $post_id[0];
				}

				// Grab the Instagram data (sadly can't access the cache data?).
				$instagram_images = Envira_Instagram_Shortcode::get_instance()->_get_instagram_data( intval( $_POST['envira_post_id'] ), $data );
				if ( ! $instagram_images ) {
					$markup = '';
					break;
				}

				if ( $instagram_images ) {
					$array_to_pass['images'] = $instagram_images;
				}

				$markup = Envira_Gallery_Shortcode::get_instance()->shortcode( $array_to_pass );
				break;

		}

		// Output HTML.
		echo $markup; // @codingStandardsIgnoreLine - Generating Unpredictable Markup From Other Envira Functions
		die();

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.1.3
	 *
	 * @return object The Envira_Pagination_AJAX object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Pagination_AJAX ) ) {
			self::$instance = new Envira_Pagination_AJAX();
		}

		return self::$instance;

	}

}

// Load the ajax class.
$envira_pagination_ajax = Envira_Pagination_AJAX::get_instance();
