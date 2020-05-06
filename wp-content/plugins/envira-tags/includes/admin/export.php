<?php
/**
 * Export class.
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
 * Export class.
 *
 * @since 1.4.0.1
 *
 * @package Envira_Tags
 * @author  Envira Team
 */
class Envira_Tags_Export {

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

		add_filter( 'envira_gallery_export_gallery_data', array( $this, 'export' ), 10, 2 );

	}

	/**
	 * Appends tags to the Gallery export data
	 *
	 * @since 1.4.0.1
	 *
	 * @param   array $data       Gallery Config.
	 * @param   int   $gallery_id Gallery ID.
	 * @return  array Gallery Data
	 */
	public function export( $data, $gallery_id ) {

		// If no images are included in this Gallery, bail.
		if ( empty( $data['gallery'] ) || count( $data['gallery'] ) === 0 ) {
			return $data;
		}

		// Iterate through each image in the Gallery, appending tag data if necessary.
		foreach ( $data['gallery'] as $attachment_id => $image ) {
			// Try to get tags (terms) for this image.
			$terms = get_the_terms( $attachment_id, 'envira-tag' );

			if ( is_wp_error( $terms ) || empty( $terms ) || ( is_array( $terms ) && count( $terms ) === 0 ) ) {
				continue;
			}

			// Set the tags array to store the tags in for this image.
			if ( ! isset( $image['tags'] ) || ! is_array( $image['tags'] ) ) {
				$data['gallery'][ $attachment_id ]['tags'] = array();
			}

			// Append the tags as slug/name key/value pairs.
			if ( $terms ) {
				foreach ( $terms as $term ) {
					$data['gallery'][ $attachment_id ]['tags'][ $term->slug ] = $term->name;
				}
			}
		}

		// Append categories as slug/name key/value pairs.
		$terms = get_the_terms( $gallery_id, 'envira-category' );
		if ( $terms ) {
			foreach ( $terms as $term ) {
				$data['envira_categories'][ $term->slug ] = $term->name;
			}
		}

		// Return the gallery data.
		return $data;

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.4.0.1
	 *
	 * @return object The Envira_Tags_Export object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Tags_Export ) ) {
			self::$instance = new Envira_Tags_Export();
		}

		return self::$instance;

	}

}

// Load the export class.
$envira_tags_export = Envira_Tags_Export::get_instance();
