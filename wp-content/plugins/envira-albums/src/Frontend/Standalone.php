<?php
/**
 * Standalone class.
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
 * Envira Albums Standalone
 *
 * @since 1.6.0
 */
class Standalone {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_action( 'pre_get_posts', array( $this, 'standalone_pre_get_posts' ) );
		add_action( 'wp_head', array( $this, 'standalone_maybe_insert_shortcode' ) );
		add_filter( 'single_template', array( $this, 'standalone_get_custom_template' ), 99 );

	}

	/**
	 * Overrides the template for the 'envira' custom post type if user has requested a different template in settings
	 *
	 * @since 1.3.1.3
	 *
	 * @param string $single_template Template to override.
	 */
	public function standalone_get_custom_template( $single_template ) {

		if ( ! get_option( 'envira_gallery_standalone_enabled' ) ) {
			return $single_template;
		}

		global $post;

		if ( 'envira_album' !== $post->post_type ) {
			return $single_template; }

		// check settings, if the user hasn't selected a custom template to override single.php, then go no further.
		$data = get_post_meta( $post->ID, '_eg_album_data', true );

		if ( ! $data ) {
			return apply_filters( 'envira_standalone_get_custom_template_album', $single_template, $data, $post );
		}

		if ( ! empty( $data['config']['standalone_template'] ) ) {
			$user_template = $data['config']['standalone_template'];
			// get path to current folder.
			$new_template = locate_template( $user_template );
			if ( ! file_exists( $new_template ) ) :
				// if it does not exist, then let's keep the default.
				return apply_filters( 'envira_standalone_get_custom_template_album', $single_template, $data, $post );
			endif;
		} else {
			return apply_filters( 'envira_standalone_get_custom_template_album', $single_template, $data, $post );
		}

		return apply_filters( 'envira_standalone_get_custom_template_album', $new_template, $data, $post );
	}

		/**
		 * Run Album Query if on an Envira Gallery or Album
		 *
		 * @since 1.3.0.11
		 *
		 * @param object $query The query object passed by reference.
		 * @return null         Return early if in admin or not the main query or not a single post.
		 */
	public function standalone_pre_get_posts( $query ) {

		// Return early if in the admin, not the main query or not a single post.
		if ( ! get_option( 'envira_gallery_standalone_enabled' ) || is_admin() || ! $query->is_main_query() || ! $query->is_single() ) {
			return;
		}

		// If not the proper post type (Envira), return early.
		$post_type = get_query_var( 'post_type' );

		if ( 'envira_album' === $post_type ) {
			do_action( 'envira_standalone_album_pre_get_posts', $query );
		}

	}
	/**
	 * Maybe inserts the Envira shortcode into the content for the page being viewed.
	 *
	 * @since 1.3.0.11
	 *
	 * @return null         Return early if in admin or not the main query or not a single post.
	 */
	public function standalone_maybe_insert_shortcode() {

		// Check we are on a single Post.
		if ( ! get_option( 'envira_gallery_standalone_enabled' ) || ! is_singular() ) {
			return;
		}

		// If not the proper post type (Envira), return early.
		$post_type = get_query_var( 'post_type' );

		if ( 'envira_album' === $post_type ) {
			add_filter( 'the_content', array( $this, 'envira_standalone_insert_album_shortcode' ) );
		}

	}

	/**
	 * Inserts the Envira Album shortcode into the content for the page being viewed.
	 *
	 * @since 1.3.0.11
	 *
	 * @global object $wp_query The current query object.
	 * @param string $content   The content to be filtered.
	 * @return string $content  Amended content with our gallery shortcode prepended.
	 */
	public function envira_standalone_insert_album_shortcode( $content ) {

		// Display the album based on the query var available.
		$id = get_query_var( 'p' );
		if ( empty( $id ) ) {
			/**
			* _get_album_by_slug() performs a LIKE search, meaning if two or more
			* Envira Albums contain the slug's word in *any* of the metadata, the first
			* is automatically assumed to be the 'correct' album
			* For standalone, we already know precisely which album to display, so
			* we can use its post ID.
			*/
			global $post;
			$id = $post->ID;
		}

		$shortcode = '[envira-album id="' . $id . '"]';

		return $shortcode . $content;

	}
}
