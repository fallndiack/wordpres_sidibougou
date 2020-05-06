<?php
/**
 * Albums Admin Container Class
 *
 * @package Enivira Albums
 */

namespace Envira\Albums\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Envira\Albums\Admin\Metaboxes;
use Envira\Albums\Admin\Editor;
use Envira\Albums\Admin\Posttype;
use Envira\Albums\Admin\Settings;
use Envira\Albums\Admin\Table;
use Envira\Albums\Admin\Media_View;
use Envira\Albums\Utils\Capabilities;

/**
 * Albums Admin Container Class
 */
class Admin_Container {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		$posttype   = new Posttype();
		$metabox    = new Metaboxes();
		$settings   = new Settings();
		$table      = new Table();
		$editor     = new Editor();
		$media_view = new Media_View();
		$cap        = new Capabilities();

		// Load admin assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );

		// Flush Album Caches on Post/Page/CPT deletion / restore.
		add_action( 'wp_trash_post', array( $this, 'trash_untrash_albums' ) );
		add_action( 'untrash_post', array( $this, 'trash_untrash_albums' ) );

		// Remove Gallery from Album(s) when Gallery Deleted.
		add_action( 'envira_gallery_trash', array( $this, 'delete_gallery_from_albums' ), 10, 2 );

	}

	/**
	 * Loads styles for our admin tables.
	 *
	 * @since 1.0.0
	 *
	 * @return null Return early if not on the proper screen.
	 */
	public function admin_styles() {

		if ( 'envira_album' !== get_current_screen()->post_type ) {
			return;
		}

		// Fire a hook to load in custom admin styles.
		do_action( 'envira_albums_admin_styles' );

	}

	/**
	 * Flush album cache when an album is deleted
	 *
	 * @since 1.0.0
	 *
	 * @param int $id The post ID being trashed.
	 * @return null Return early if no album is found.
	 */
	public function trash_untrash_albums( $id ) {

		$album = get_post( $id );

		// Flush necessary gallery caches to ensure trashed albums are not showing.
		envira_flush_album_caches( $id );

		// Return early if not an Envira album.
		if ( 'envira_album' !== $album->post_type ) {
			return;
		}

	}

	/**
	 * Delete gallery from albums when a gallery is deleted
	 *
	 * @since 1.1.0.1
	 *
	 * @param int   $id     Envira Gallery ID being trashed.
	 * @param array $data Envira Gallery Data.
	 */
	public function delete_gallery_from_albums( $id, $data ) {

		// Iterate through Albums, removing Gallery.
		// Output all other galleries not assigned to this album.
		// Build arguments.
		$arguments = array(
			'post_type'      => 'envira',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		);

		// Get Albums.
		$albums = new \WP_Query(
			array(
				'post_type'      => 'envira_album',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			)
		);
		if ( ! $albums->posts || count( $albums->posts ) === 0 ) {
			return;
		}

		// Iterate through Albums.
		foreach ( $albums->posts as $album ) {
			// Check metadata to see if the gallery exists.
			$album_data = envira_get_album( $album->ID );
			// Check gallery exists in Album.
			if ( ! isset( $album_data['galleryIDs'] ) || empty( $album_data['galleryIDs'] ) ) {
				continue;
			}
			$key = array_search( $id, $album_data['galleryIDs'], true );
			if ( false !== $key ) {
				// Delete Gallery ID + Gallery Details in Album.
				unset( $album_data['galleryIDs'][ $key ] );
				unset( $album_data['gallery'][ $album->ID ] );

				// Update Album Meta.
				update_post_meta( $album->ID, '_eg_album_data', $album_data );
				break; // No need to search any more items in the array.
			}
		}

	}

}
