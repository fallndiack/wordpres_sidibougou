<?php
/**
 * AJAX class.
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
 * AJAX class.
 *
 * @since 1.0.0
 *
 * @package Envira_Featured_Content
 * @author  Envira Team
 */
class Envira_Featured_Content_AJAX {

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
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		add_action( 'wp_ajax_envira_featured_content_refresh_terms', array( $this, 'refresh_terms' ) );
		add_action( 'wp_ajax_envira_featured_content_refresh_posts', array( $this, 'refresh_posts' ) );

	}

	/**
	 * Refreshes the term list to show available terms for the selected post type.
	 *
	 * @since 1.0.0
	 */
	public function refresh_terms() {

		// Run a security check first.
		check_ajax_referer( 'envira-featured-content-term-refresh', 'nonce' ); // note: this was commented out - not sure why.

		// Die early if no post type is set.
		if ( empty( $_POST['post_type'] ) ) {
			echo wp_json_encode( array( 'error' => true ) );
			die;
		}

		// Prepare variables.
		$taxonomies = array();
		$instance   = Envira_Gallery_Metaboxes::get_instance();

		$post_types = array_map( 'sanitize_text_field', wp_unslash( $_POST['post_type'] ) );

		// If we have more than one post type selected, we only want to show taxonomies which exist across all Post Types.
		if ( count( $post_types ) > 1 ) {
			// Get all available taxonomies in WordPress.
			$taxonomies = get_taxonomies();

			// If no taxonomies can be found, return an error.
			if ( empty( $taxonomies ) ) {
				echo wp_json_encode( array( 'error' => true ) );
				die;
			}

			// Get available taxonomies for each WordPress Post Type.
			$post_taxonomies = array();
			foreach ( $post_types as $type ) {
				$post_taxonomies[ $type ] = get_object_taxonomies( $type );
			}

			// Loop through the taxonomies to check they exist in all Post Types.
			$shared_taxonomies = array();
			foreach ( $taxonomies as $taxonomy ) {
				// Assume the $taxonomy is shared across all Post Types, until we assert otherwise.
				$shared = true;

				foreach ( $post_taxonomies as $post_type => $post_type_taxonomies ) {
					if ( in_array( $taxonomy, $post_type_taxonomies, true ) ) {
						continue;
					}

					// If here, taxonomy does not exist in this Post Type, so it is not shared.
					$shared = false;
					break;
				}

				if ( $shared ) {
					$shared_taxonomies[] = $taxonomy;
				}
			}

			// If no shared taxonomies can be found, return an error.
			if ( empty( $shared_taxonomies ) || count( $shared_taxonomies ) === 0 ) {
				echo wp_json_encode( array( 'error' => true ) );
				die;
			}

			// Loop through shared taxonomies to build taxonomy/terms HTML optgroup/options.
			$output = '';
			foreach ( $shared_taxonomies as $taxonomy ) {
				$taxonomy_obj = get_taxonomy( $taxonomy );
				$terms        = get_terms( $taxonomy );

				$output .= '<optgroup label="' . esc_attr( $taxonomy_obj->labels->name ) . '">';
				foreach ( $terms as $term ) {
					$output .= '<option value="' . esc_attr( strtolower( $taxonomy_obj->name ) . '|' . $term->term_id . '|' . $term->slug ) . '"' . selected( strtolower( $taxonomy_obj->name ) . '|' . $term->term_id . '|' . $term->slug, in_array( strtolower( $taxonomy_obj->name ) . '|' . $term->term_id . '|' . $term->slug, (array) $instance->get_config( 'fc_terms', $instance->get_config_default( 'fc_terms' ) ), true ) ? strtolower( $taxonomy_obj->name ) . '|' . $term->term_id . '|' . $term->slug : '', false ) . '>' . esc_html( ucwords( $term->name ) ) . '</option>';
				}
				$output .= '</optgroup>';
			}

			// Send the output back to the script. If it is empty, send back an error, otherwise send back the HTML.
			if ( empty( $output ) ) {
				echo wp_json_encode( array( 'error' => true ) );
				die;
			} else {
				echo wp_json_encode( $output );
				die;
			}
		} else {
			// We only have one post type. Try to grab taxonomies for it.
			if ( isset( $_POST['post_type'] ) ) {
				foreach ( $post_types as $type ) {
					$taxonomies[] = get_object_taxonomies( $type, 'objects' );
				}
			}

			// If no taxonomies can be found, return an error.
			if ( empty( $taxonomies ) ) {
				echo wp_json_encode( array( 'error' => true ) );
				die;
			}

			// Loop through the taxonomies and build the HTML output.
			$output = '';
			foreach ( $taxonomies as $array ) {
				foreach ( $array as $taxonomy ) {
					$terms = get_terms( $taxonomy->name );

					$output .= '<optgroup label="' . esc_attr( $taxonomy->labels->name ) . '">';
					foreach ( $terms as $term ) {
						$output .= '<option value="' . esc_attr( strtolower( $taxonomy->name ) . '|' . $term->term_id . '|' . $term->slug ) . '"' . selected( strtolower( $taxonomy->name ) . '|' . $term->term_id . '|' . $term->slug, in_array( strtolower( $taxonomy->name ) . '|' . $term->term_id . '|' . $term->slug, (array) $instance->get_config( 'fc_terms', $instance->get_config_default( 'fc_terms' ) ), true ) ? strtolower( $taxonomy->name ) . '|' . $term->term_id . '|' . $term->slug : '', false ) . '>' . esc_html( ucwords( $term->name ) ) . '</option>';
					}
					$output .= '</optgroup>';
				}
			}

			// Send the output back to the script. If it is empty, send back an error, otherwise send back the HTML.
			if ( empty( $output ) ) {
				echo wp_json_encode( array( 'error' => true ) );
				die;
			} else {
				echo wp_json_encode( $output );
				die;
			}
		}

		// If we can't grab something, just send back an error.
		echo wp_json_encode( array( 'error' => true ) );
		die;

	}

	/**
	 * Refreshes the individual post selection list for the selected post type.
	 *
	 * @since 1.0.0
	 */
	public function refresh_posts() {

		// Run a security check first.
		check_ajax_referer( 'envira-featured-content-refresh', 'nonce' ); // this was commented out?
		// Die early if no post type is set.
		if ( empty( $_POST['post_type'] ) ) {
			echo wp_json_encode( array( 'error' => true ) );
			die;
		}

		$limit      = apply_filters( 'envira_featured_content_max_queried_posts', 500 );
		$output     = '';
		$instance   = Envira_Gallery_Metaboxes::get_instance();
		$post_types = array_map( 'sanitize_text_field', wp_unslash( $_POST['post_type'] ) );

		if ( isset( $_POST['post_type'] ) ) {
			foreach ( $post_types as $post_type ) {
				$posts = get_posts(
					array(
						'post_type'      => $post_type,
						'posts_per_page' => $limit,
						'no_found_rows'  => true,
						'cache_results'  => false,
					)
				);

				// If we have posts, loop through them and build out the HTML output.
				if ( $posts ) {
					$object  = get_post_type_object( $post_type );
					$output .= '<optgroup label="' . esc_attr( $object->labels->name ) . '">';
					foreach ( (array) $posts as $post ) {
						$output .= '<option value="' . absint( $post->ID ) . '"' . selected( $post->ID, in_array( $post->ID, (array) $instance->get_config( 'fc_inc_ex', $instance->get_config_default( 'fc_inc_ex' ) ), true ) ? $post->ID : '', false ) . '>' . esc_html( ucwords( $post->post_title ) ) . '</option>';
					}
					$output .= '</optgroup>';
				}
			}
		}

		// If results found, output.
		if ( ! empty( $output ) ) {
			echo wp_json_encode( $output );
			die;
		}

		// Output an error if we can't find anything.
		echo wp_json_encode( array( 'error' => true ) );
		die;

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Featured_Content_AJAX object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Featured_Content_AJAX ) ) {
			self::$instance = new Envira_Featured_Content_AJAX();
		}

		return self::$instance;

	}

}

// Load the AJAX class.
$envira_featured_content_ajax = Envira_Featured_Content_AJAX::get_instance();
