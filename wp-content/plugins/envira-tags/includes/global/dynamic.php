<?php
/**
 * Dynamic class.
 *
 * @since 1.3.0
 *
 * @package Envira_Tags_Dynamic
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dynamic class.
 *
 * @since 1.3.0
 *
 * @package Envira_Tags_Dynamic
 * @author  Envira Team
 */
class Envira_Tags_Dynamic {

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
	 * Primary class constructor.
	 *
	 * @since 1.3.0
	 */
	public function __construct() {

		add_filter( 'envira_dynamic_get_dynamic_gallery_types', array( $this, 'get_dynamic_gallery_types' ) );
		add_filter( 'envira_dynamic_get_images_by_tag', array( $this, 'get_images_by_tag' ), 10, 3 );
		add_filter( 'envira_gallery_get_transient_markup', array( $this, 'maybe_cache_transient' ), 10, 3 );

	}

	/**
	 * Removes caching for tags + dynamic
	 * TO DO: Provide option in the future for user to adjust this
	 *
	 * @since 1.1.0
	 *
	 * @param array $transient  Transient.
	 * @param array $gallery_data  Gallery Data.
	 * @return array        New Dynamic Gallery Types
	 */
	public function maybe_cache_transient( $transient, $gallery_data ) {

		if ( isset( $gallery_data['config']['dynamic'] ) && strpos( $gallery_data['config']['dynamic'], 'tags-' ) !== false ) {
			return false;
		}

		return $transient;

	}



	/**
	 * Adds the Tag Addon Dynamic methods for retrieving images to the
	 * array of available Gallery Types
	 *
	 * @since 1.1.0
	 *
	 * @param array $types  Dynamic Gallery Types.
	 * @return array        New Dynamic Gallery Types
	 */
	public function get_dynamic_gallery_types( $types ) {

		$types['envira_dynamic_get_images_by_tag'] = '#^tags-#';

		return $types;

	}

	/**
	 * Retrieves the image data by tag across all Envira Galleries
	 *
	 * @since 1.1.0
	 *
	 * @param array  $dynamic_data    Existing Dynamic Data Array.
	 * @param string $id             ID (tag-term).
	 * @param array  $data            Gallery Configuration.
	 * @return bool|array            Array of data on success, false on failure
	 */
	public function get_images_by_tag( $dynamic_data, $id, $data ) {

		$terms = array();

		// If tags is *, get all tags.
		if ( 'tags-*' === $id ) {

			$tags = get_terms( 'envira-tag' );

			foreach ( $tags as $tag ) {
				$terms[] = $tag->slug;
			}
		} else {

			// Remove the Base ID.
			$term_parts = preg_replace( '/^tags-/', '', $id );

			// Split the terms.
			$term_parts = explode( ',', $term_parts );

			// Add all of our terms to array.
			foreach ( $term_parts as $i => $term_part ) {

				// Add to term string.
				$terms[] = $term_part;

			}
		}

		// Set operator.
		$allowed_operators = array( 'IN', 'AND' );
		$operator          = ( isset( $data['config']['operator'] ) && in_array( sanitize_text_field( $data['config']['operator'] ), $allowed_operators, true ) ) ? $data['config']['operator'] : 'IN';

		// Get limit.
		$limit = ( ( isset( $data['config']['tags_limit'] ) && intval( $data['config']['tags_limit'] ) > 0 ) ? $data['config']['tags_limit'] : -1 );

		// Prepare query args.
		$args = array(
			'post_type'      => 'attachment',
			'post_status'    => 'any',
			'posts_per_page' => $limit,
			'tax_query'      => array( // @codingStandardsIgnoreLine
				array(
					'taxonomy' => 'envira-tag',
					'field'    => 'slug',
					'terms'    => $terms,
					'operator' => $operator,
				),
			),
		);

		// Run query.
		$attachments = new WP_Query( $args );

		// Check for results.
		if ( ! isset( $attachments->posts ) || 0 === count( $attachments->posts ) ) {
			return $dynamic_data;
		}

		// Iterate through attachments.
		foreach ( (array) $attachments->posts as $i => $attachment ) {
			// Get image details.
			$src = wp_get_attachment_image_src( $attachment->ID, 'full' );

			// Build image attributes to match Envira Gallery.
			$dynamic_data[ $attachment->ID ] = array(
				'status'          => 'published',
				'src'             => ( isset( $src[0] ) ? esc_url( $src[0] ) : '' ),
				'title'           => $attachment->post_title,
				'link'            => ( isset( $src[0] ) ? esc_url( $src[0] ) : '' ),
				'alt'             => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
				'caption'         => $attachment->post_excerpt,
				'thumb'           => '',
				'link_new_window' => 0,
			);
		}

		return $dynamic_data;

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.3.0
	 *
	 * @return object The Envira_Tags_Dynamic object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Tags_Dynamic ) ) {
			self::$instance = new Envira_Tags_Dynamic();
		}

		return self::$instance;

	}

}

// Load the dynamic class.
$envira_tags_dynamic = Envira_Tags_Dynamic::get_instance();
