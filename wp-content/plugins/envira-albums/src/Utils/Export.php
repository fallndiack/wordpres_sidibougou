<?php
/**
 * Export class.
 *
 * @since 1.2.4.5
 *
 * @package Envira_Albums
 * @author  Envira Team
 */

namespace Envira\Albums\Utils;

/**
 * Envira Albums Export Class.
 */
class Export {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.2.4.5
	 */
	public function __construct() {

		// Export an album.
		$this->export_album();

	}

	/**
	 * Exports an Envira album.
	 *
	 * @since 1.2.4.5
	 *
	 * @return null Return early if failing proper checks to export the album.
	 */
	public function export_album() {

		if ( ! $this->has_exported_album() ) {
			return;
		}

		if ( ! $this->verify_exported_album() ) {
			return;
		}

		if ( ! $this->can_export_album() ) {
			return;
		}

		// Ignore the user aborting the action.
		ignore_user_abort( true );

		// Grab the proper data.
		$post_id = isset( $_POST['envira_post_id'] ) ? absint( $_POST['envira_post_id'] ) : null; // @codingStandardsIgnoreLine
		$data    = get_post_meta( $post_id, '_eg_album_data', true );

		// Set the proper headers.
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=envira-album-' . $post_id . '-' . gmdate( 'm-d-Y' ) . '.json' );
		header( 'Expires: 0' );

		// Make the settings downloadable to a JSON file and die.
		die( wp_json_encode( $data ) );

	}

	/**
	 * Helper method to determine if an album export is available.
	 *
	 * @since 1.2.4.5
	 *
	 * @return bool True if an exported album is available, false otherwise.
	 */
	public function has_exported_album() {

		return ! empty( $_POST['envira_export'] ); // @codingStandardsIgnoreLine

	}

	/**
	 * Helper method to determine if an album export nonce is valid and verified.
	 *
	 * @since 1.2.4.5
	 *
	 * @return bool True if the nonce is valid, false otherwise.
	 */
	public function verify_exported_album() {

		return isset( $_POST['envira-albums-export'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['envira-albums-export'] ) ), 'envira-albums-export' );

	}

	/**
	 * Helper method to determine if the user can actually export the album.
	 *
	 * @since 1.2.4.5
	 *
	 * @return bool True if the user can export the album, false otherwise.
	 */
	public function can_export_album() {

		$manage_options = current_user_can( 'manage_options' );
		return apply_filters( 'envira_albums_export_cap', $manage_options );

	}

}
