<?php
/**
 * Import class.
 *
 * @since 1.4.0.1
 *
 * @package Envira_Tags
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

/**
 * Import class.
 *
 * @since 1.4.0.1
 *
 * @package Envira_Tags
 * @author  Envira Team
 */
class Envira_Tags_Import {

	/**
	 * Holds the class object.
	 *
	 * @since 1.4.0.1
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.4.0.1
	 *
	 * @var string
	 */
	public $file = __FILE__;


	/**
	 * Primary class constructor.
	 *
	 * @since 1.4.0.1
	 */
	public function __construct() {

		// When images are imported, envira_gallery_ajax_prepare_gallery_data() is called in Envira Gallery
		// We hook onto this to set tags against the imported Media Library attachment.
		add_filter( 'envira_gallery_ajax_prepare_gallery_data_item', array( $this, 'import' ), 10, 4 );

		// When gallery is imported, add the categories.
		add_action( 'envira_import_gallery_end', array( $this, 'update_categories' ), 10, 2 );
	}

	/**
	 * Update categories.
	 *
	 * @since 1.7.6
	 *
	 * @param   array $data   Gallery Config.
	 * @param   int   $post_id  Post ID.
	 */
	public function update_categories( $data, $post_id ) {

		// Update envira categories.
		if ( ! empty( $data['envira_categories'] ) ) {
			foreach ( $data['envira_categories'] as $slug => $term_name ) {
				// check and see if the term exists.
				$term_id = term_exists( $slug, 'envira-category' );
				if ( false === $term_id ) {
					// doesn't exist so create the term.
					$term_id = wp_insert_term(
						$term_name, // the term.
						'envira-category', // the taxonomy.
						array(
							'description' => false, // we don't import this (yet).
							'slug'        => $slug,
							'parent'      => false, // numeric term id.
						)
					);
				} elseif ( ! empty( $term_id['term_id'] ) ) {
					$term_id = intval( $term_id['term_id'] );
				}
				if ( $term_id ) {
					$category_ids[] = $term_id;
				}
			}
			wp_set_post_terms( $post_id, $category_ids, 'envira-category' );
		}

	}

	/**
	 * Sets tags against an imported Media Library attachment
	 *
	 * @since 1.4.0.1
	 *
	 * @param   array $new_image      Image Metadata in Media Library.
	 * @param   array $image          Image Metadata in Envira Gallery.
	 * @param   int   $attachment_id  Attachment ID.
	 * @param   array $gallery_data   Gallery Config.
	 * @return  array Image Metadata
	 */
	public function import( $new_image, $image, $attachment_id, $gallery_data ) {

		// If the original $image metadata contains tags, assign them as terms
		// to the Media Library attachment now.
		if ( ! isset( $image['tags'] ) || ! is_array( $image['tags'] ) || count( $image['tags'] ) === 0 ) {
			return $new_image;
		}

		// For each tag in the image, create a new term if it doesn't exist.
		// This preserves slugs when exporting and then importing a Gallery.
		$tags = array();
		foreach ( $image['tags'] as $slug => $name ) {
			wp_insert_term(
				$name,
				'envira-tag',
				array(
					'slug' => $slug,
				)
			);
			$tags[] = $name;
		}

		// Assign the term names to the image.
		wp_set_object_terms( $attachment_id, $tags, 'envira-tag' );

		// Return data.
		return $new_image;

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.4.0.1
	 *
	 * @return object The Envira_Tags_Import object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Tags_Import ) ) {
			self::$instance = new Envira_Tags_Import();
		}

		return self::$instance;

	}

}

// Load the import class.
$envira_tags_import = Envira_Tags_Import::get_instance();
