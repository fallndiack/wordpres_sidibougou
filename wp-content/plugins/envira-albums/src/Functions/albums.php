<?php
/**
 * Albums Functions
 *
 * @since 1.6.0
 *
 * @package Envira Gallery
 * @subpackage Envira Albums
 * @author Envira Gallery Team <support@enviragallery.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper Method to get an Envira Album.
 *
 * @access public
 * @param int     $album_id Album ID.
 * @param boolean $flush_cache Flush cache or not.
 * @return bool|array
 */
function envira_get_album( $album_id, $flush_cache = false ) {

	if ( ! isset( $album_id ) ) {

		return false;

	}

	$album = get_transient( '_eg_cache_' . $album_id );

	// Attempt to return the transient first, otherwise generate the new query to retrieve the data.
	if ( true === $flush_cache || false === $album ) {

		$album = _envira_get_album( $album_id );

		if ( $album ) {
			$expiration = envira_get_transient_expiration_time( 'envira-albums' );
			set_transient( '_eg_cache_' . $album_id, $album, $expiration );
		}
	}

	// Return the album data.
	return $album;

}

/**
 * _envira_get_album function.
 *
 * @access private
 * @param mixed $album_id Album ID.
 * @return array
 */
function _envira_get_album( $album_id ) {

	return get_post_meta( $album_id, '_eg_album_data', true );

}

/**
 * Gets albums by slug function.
 *
 * @access public
 * @param mixed $slug Album slug.
 * @return array
 */
function envira_get_album_by_slug( $slug ) {

	$album = get_transient( '_eg_cache_' . $slug );

	// Attempt to return the transient first, otherwise generate the new query to retrieve the data.
	if ( false === $album ) {

		$album = _envira_get_album_by_slug( $slug );

		if ( $album ) {
			$expiration = envira_get_transient_expiration_time( 'envira-albums' );
			set_transient( '_eg_cache_' . $slug, $album, $expiration );
		}
	}

	// Return the album data.
	return $album;
}

/**
 * Gets albums by slug function.
 *
 * @access private
 * @param mixed $slug Album slug.
 * @return array
 */
function _envira_get_album_by_slug( $slug ) {

	// Get Envira Album CPT by slug.
	$albums = get_posts(
		array(
			'post_type'      => 'envira_album',
			'name'           => $slug,
			'fields'         => 'ids',
			'posts_per_page' => 1,
		)
	);

	if ( $albums ) {
		return get_post_meta( $albums[0], '_eg_album_data', true );
	}

	// Get Envira CPT by meta-data field (yeah this is an edge case dealing with slugs in shortcode and modified slug in the misc tab of the gallery).
	$albums = new WP_Query(
		array(
			'post_type'      => 'envira_album',
			'meta_key'       => 'envira_album_slug', // @codingStandardsIgnoreLine
			'meta_value'     => $slug, // @codingStandardsIgnoreLine
			'fields'         => 'ids',
			'posts_per_page' => 1,
		)
	);

	if ( $albums->posts ) {
		return get_post_meta( $albums->posts[0], '_eg_album_data', true );
	}

}

/**
 * Helper Method to get Envira Albums.
 *
 * @param bool   $skip_empty   Skip empty albums.
 * @param bool   $ignore_cache Should we ignore cache.
 * @param string $search_terms Search Terms.
 * @return array $albums
 */
function envira_get_albums( $skip_empty = true, $ignore_cache = false, $search_terms = '' ) {

	$albums = get_transient( '_ea_cache_all' );

	// Attempt to return the transient first, otherwise generate the new query to retrieve the data.
	if ( $ignore_cache || ! empty( $search_terms ) || false === $albums ) {

		$albums = _envira_get_albums( $skip_empty, $search_terms );

		// Cache the results if we're not performing a search and we have some results.
		if ( $albums && empty( $search_terms ) ) {
			$expiration = envira_get_transient_expiration_time();
			set_transient( '_ea_cache_all', $albums, $expiration );
		}
	}

			// Return the albums data.
		return $albums;
}

/**
 * _envira_get_albums function.
 *
 * @access private
 * @param bool   $skip_empty (default: true).
 * @param string $search_terms (default: '').
 * @return array
 */
function _envira_get_albums( $skip_empty = true, $search_terms = '' ) {
		// Build WP_Query arguments.
	$args = array(
		'post_type'      => 'envira_album',
		'post_status'    => 'publish',
		'posts_per_page' => 99,
		'no_found_rows'  => true,
		'fields'         => 'ids',
		'meta_query'     => array( // @codingStandardsIgnoreLine
			array(
				'key'     => '_eg_album_data',
				'compare' => 'EXISTS',
			),
		),
	);

		// If search terms exist, add a search parameter to the arguments.
	if ( ! empty( $search_terms ) ) {
		$args['s'] = $search_terms;
	}

		// Run WP_Query.
	$albums = new WP_Query( $args );
	if ( ! isset( $albums->posts ) || empty( $albums->posts ) ) {
		return false;
	}

	// Now loop through all the albums found and only use albums that have galleries in them.
	$ret = array();
	foreach ( $albums->posts as $id ) {
		$data = get_post_meta( $id, '_eg_album_data', true );

			// Skip albums with no galleries in them.
		if ( $skip_empty && empty( $data['galleryIDs'] ) ) {
			continue;
		}

		// Skip certain album types.
		if ( 'defaults' === envira_albums_get_config( 'type', $data ) || 'dynamic' === envira_albums_get_config( 'type', $data ) ) {
			continue;
		}

		$ret[] = $data;
	}

	// Return the album data.
	return $ret;

}

/**
 * Returns full Gallery Config defaults to json object.
 *
 * @since 1.7.1
 *
 * @access public
 * @param int  $album_id Album ID.
 * @param bool $raw      Return raw or json encode.
 * @param bool $is_dynamic      Is this a dynamic album.
 * @param bool $dynamic_id      Dynamic ID.
 * @return array|string
 */
function envira_get_album_config( $album_id, $raw = false, $is_dynamic = false, $dynamic_id = false ) {

	if ( ! isset( $album_id ) ) {
		return false;
	}

	$album = envira_get_album( $album_id );

	if ( $is_dynamic ) {
		$album['id'] = $dynamic_id;
	}

	if ( ! isset( $album['config']['album_id'] ) && isset( $album['id'] ) ) {
		$album['config']['album_id'] = $album['id'];
	}

	// temp hack: preserve keyboard and mousewheel settings (see 1980).
	$keyboard   = isset( $album['config']['keyboard'] ) ? $album['config']['keyboard'] : 1;
	$mousewheel = isset( $album['config']['mousewheel'] ) ? $album['config']['mousewheel'] : 1;

	// below filter makes keyboard 0 and makes mousewheel reappear as 0.
	$album['config']['keyboard']   = $keyboard;
	$album['config']['mousewheel'] = $mousewheel;

	$album = apply_filters( 'envira_albums_pre_data', $album, $album_id );

	if ( $raw ) {

		return $album['config'];

	}

	// Santitize Description And Title.
	$album['config']['description'] = ( isset( $album['config']['description'] ) ) ? envira_santitize_description( $album['config']['description'] ) : false;
	$album['config']['title']       = ( isset( $album['config']['title'] ) ) ? envira_santitize_title( $album['config']['title'] ) : false;

	// Some older albums might be missing lightbox_title_caption (without updating in admin).
	$album['config']['lightbox_title_caption'] = ( ! isset( $album['config']['lightbox_title_caption'] ) ) ? 'title' : $album['config']['lightbox_title_caption'];

	// Disable/Remove FullScreen if Fullscreen addon is not present.
	if ( ! class_exists( 'Envira_Fullscreen' ) ) {
		if ( isset( $album['config']['open_fullscreen'] ) ) {
			unset( $album['config']['open_fullscreen'] );
		}
	}

	// Auto Thumbnail Size Check.
	$album = envira_album_maybe_set_thumbnail_size_auto( $album );

	return wp_json_encode( $album['config'] );

}

/**
 * Determine if lightbox width and height settings should be set to auto
 *
 * @since 1.8.3
 *
 * @access public
 * @param array $data Gallery data.
 * @return array
 */
function envira_album_maybe_set_thumbnail_size_auto( $data ) {

	if ( isset( $data['config']['thumbnails_custom_size'] ) && 0 === $data['config']['thumbnails_custom_size'] ) {
		$data['config']['thumbnails_width']  = 'auto';
		$data['config']['thumbnails_height'] = 'auto';
	}

	// if this value 'thumbnails_custom_size' isn't set/exists, then this is a gallery created/updated before 1.8.3 so the width/height values should be honored.
	return $data;

}

/**
 * Returns All Gallery Images defaults to json object.
 *
 * @since 1.7.1
 *
 * @param int  $album_id ALbum Id.
 * @param bool $raw      (default: false).
 * @return bool|string
 */
function envira_get_album_galleries( $album_id, $raw = false ) {

	if ( ! isset( $album_id ) ) {
		return false;
	}

	$album = envira_get_album( $album_id );
	$album = apply_filters( 'envira_albums_galleries_pre_data', $album, $album_id );

	if ( $raw ) {

		return $album['galleries'];

	}

	$album['galleries'] = ( isset( $album['galleries'] ) ) ? envira_albums_sanitizate( $album['galleries'] ) : false;

	return wp_json_encode( $album['galleries'] );

}

/**
 * Temp Helper method for encoding gallery text fields so they don't break the JSON
 *
 * @since 1.7.0
 *
 * @param array $galleries_to_sanitizate Array of galleries to santize.
 * @return string Key value on success, false on failure.
 */
function envira_albums_sanitizate( $galleries_to_sanitizate ) {

	if ( empty( $galleries_to_sanitizate ) ) {
		return $galleries_to_sanitizate;
	}

	foreach ( $galleries_to_sanitizate as $gallery_id => $gallery_data ) {
		foreach ( $gallery_data as $gallery_key => $gallery_value ) {
			$galleries_to_sanitizate[ $gallery_id ][ $gallery_key ] = htmlspecialchars( $gallery_value, ENT_QUOTES );
		}
	}

	return $galleries_to_sanitizate;

}
/**
 * Helper method for setting default config values.
 *
 * @since 1.7.0
 *
 * @global int $id      The current post ID.
 * @global object $post The current post object.
 * @param string $key   The default config key to retrieve.
 * @return string       Key value on success, false on failure.
 */
function envira_albums_get_config_default( $key ) {

	global $id, $post;

	// Get the current post ID. If ajax, grab it from the $_POST variable.
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_POST['post_id'] ) ) { // @codingStandardsIgnoreLine
		$post_id = absint( $_POST['post_id'] ); // @codingStandardsIgnoreLine
	} else {
		$post_id = isset( $post->ID ) ? $post->ID : (int) $id;
	}

	// Prepare default values.
	$defaults = envira_albums_get_config_defaults( $post_id );

	// Return the key specified.
	return isset( $defaults[ $key ] ) ? $defaults[ $key ] : false;

}

/**
 * Helper method for retrieving config values.
 *
 * @since 1.0.0
 *
 * @param string $key     The config key to retrieve.
 * @param array  $data    Album config data.
 * @param string $default A default value to use.
 * @return string Key value on success, empty string on failure.
 */
function envira_albums_get_config( $key, $data, $default = '' ) {

	if ( ! is_array( $data ) ) {

		return envira_albums_get_config_default( $key );
	}

	$is_mobile_keys = array();

	// If we are on a mobile device, some config keys have mobile equivalents, which we need to check instead.
	if ( envira_mobile_detect()->isMobile() ) {

		$is_mobile_keys = array(
			'lightbox'          => 'mobile_lightbox',
			'arrows'            => 'mobile_arrows',
			'toolbar'           => 'mobile_toolbar',
			'thumbnails'        => 'mobile_thumbnails',
			'thumbnails_width'  => 'mobile_thumbnails_width',
			'thumbnails_height' => 'mobile_thumbnails_height',

		);

		if ( false !== $data['config']['mobile'] ) {
			$is_mobile_keys['crop_width']  = 'mobile_width';
			$is_mobile_keys['crop_height'] = 'mobile_height';

		}

		$is_mobile_keys = apply_filters( 'envira_gallery_get_config_mobile_keys', $is_mobile_keys );

		if ( array_key_exists( $key, $is_mobile_keys ) ) {
			// Use the mobile array key to get the config value.
			$key = $is_mobile_keys[ $key ];
		}
	} else { // if we are not on a mobile device, check for custom thumbnail sizes.

		// If the user hasn't overrided lightbox thumbnails with custom sizes, make sure these are set to auto.
		if ( ( 'thumbnails_height' === $key || 'thumbnails_width' === $key ) && ( ! isset( $data['config']['thumbnails_custom_size'] ) || false === $data['config']['thumbnails_custom_size'] ) ) {
			$value = 'auto';
		}
	}

	if ( isset( $data['config'] ) ) {

			$data['config'] = apply_filters( 'envira_album_get_config', $data['config'], $key );

	} else {

		$data['config'][ $key ] = false;
	}

	$value = isset( $data['config'][ $key ] ) ? $data['config'][ $key ] : envira_albums_get_config_default( $key );

	return $value;

}
// Conditionally load the template tag.
if ( ! function_exists( 'envira_album' ) ) {
	/**
	 * Primary template tag for outputting Envira albums in templates.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $id         The ID of the album to load.
	 * @param string $type    The type of field to query.
	 * @param array  $args     Associative array of args to be passed.
	 * @param bool   $return    Flag to echo or return the gallery HTML.
	 */
	function envira_album( $id, $type = 'id', $args = array(), $return = false ) {

		// If we have args, build them into a shortcode format.
		$args_string = '';
		if ( ! empty( $args ) ) {
			foreach ( (array) $args as $key => $value ) {
				$args_string .= ' ' . $key . '="' . $value . '"';
			}
		}

		// Build the shortcode.
		$shortcode = ! empty( $args_string ) ? '[envira-album ' . $type . '="' . $id . '"' . $args_string . ']' : '[envira-album ' . $type . '="' . $id . '"]';

		// Return or echo the shortcode output.
		if ( $return ) {
			return do_shortcode( $shortcode );
		} else {
			echo do_shortcode( $shortcode );
		}

	}
}
