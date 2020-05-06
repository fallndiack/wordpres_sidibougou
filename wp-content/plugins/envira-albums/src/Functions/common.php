<?php
/**
 * Albums Functions
 *
 * @package Envira Albums
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper Method to flust album caches
 *
 * @access public
 * @param int    $post_id album post id.
 * @param string $slug (default: '').
 * @return void
 */
function envira_flush_album_caches( $post_id, $slug = '' ) {

	// Delete known album caches.
	delete_transient( '_eg_cache_' . $post_id );
	delete_transient( '_ea_cache_all' );
	delete_transient( '_eg_fragment_albums_' . $post_id );
	delete_transient( '_eg_fragment_albums_mobile_' . $post_id );

	// Possibly delete slug gallery cache if available.
	if ( ! empty( $slug ) ) {
		delete_transient( '_eg_cache_' . $slug );
	}

	// Run a hook for Addons to access.
	do_action( 'envira_albums_flush_caches', $post_id, $slug );

}

/**
 * Helper method to get album config defaults
 *
 * @access public
 * @param int $post_id album post id.
 * @return array
 */
function envira_albums_get_config_defaults( $post_id ) {

	// Prepare default values.
	$defaults = array(
		// Galleries Tab.
		'type'                        => 'default',

		// Config Tab.
		'columns'                     => '3',
		'justified_row_height'        => 150, // automatic/justified layout.
		'justified_gallery_theme'     => 'normal',
		'justified_last_row'          => 'nojustify',
		'justified_margins'           => '1',
		'gallery_theme'               => 'base',
		'back'                        => 0,
		'back_label'                  => __( 'Back to Album', 'envira-albums' ),
		'album_alignment'             => 0,
		'album_width'                 => 100,
		'description_position'        => 0,
		'description'                 => '',
		'display_titles'              => 0,
		'display_titles_automatic'    => 0,
		'display_captions'            => 0,
		'display_image_count'         => 0,
		'gutter'                      => 10,
		'margin'                      => 10,
		'sorting'                     => 0,
		'crop_width'                  => 960,
		'crop_height'                 => 300,
		'crop'                        => 0,
		'dimensions'                  => 0,
		'isotope'                     => 1,
		'css_animations'              => 1,
		'lazy_loading'                => 1, // lazy loading 'ON' for new galleries.
		'lazy_loading_delay'          => 500,

		// Lightbox.
		'lightbox'                    => false,
		'lightbox_theme'              => 'base_dark',
		'title_display'               => 'float',
		'lightbox_title_caption'      => 'title',
		'arrows'                      => 1,
		'arrows_position'             => 'inside',
		'keyboard'                    => 1,
		'mousewheel'                  => 1,
		'toolbar'                     => 1,
		'toolbar_title'               => 0,
		'toolbar_position'            => 'top',
		'aspect'                      => 1,
		'loop'                        => 1,
		'lightbox_open_close_effect'  => 'fade',
		'effect'                      => 'fade',
		'html5'                       => false,

		// Thumbnails.
		'thumbnails'                  => 1,
		'thumbnails_width'            => 75,
		'thumbnails_height'           => 50,
		'thumbnails_position'         => 'bottom',

		// Mobile.
		'mobile'                      => 1,
		'mobile_width'                => 320,
		'mobile_height'               => 240,
		'mobile_lightbox'             => 1,
		'mobile_touchwipe'            => 1,
		'mobile_touchwipe_close'      => 0,
		'mobile_arrows'               => 1,
		'mobile_toolbar'              => 1,
		'mobile_thumbnails'           => 1,
		'mobile_thumbnails_width'     => 75,
		'mobile_thumbnails_height'    => 50,
		'mobile_justified_row_height' => 80,
		'breadcrumbs_enabled_mobile'  => 1,
		'breadcrumbs_separator'       => '/',

		// Misc.
		'title'                       => '',
		'slug'                        => '',
		'classes'                     => array(),
		'rtl'                         => 0,

	);

	return apply_filters( 'envira_albums_defaults', $defaults, $post_id );

}
/**
 * Returns the back to album location options
 *
 * @since 1.0.0
 */
function envira_back_to_album_locations() {

	$options = array(
		array(
			'name'  => __( 'Above Images', 'envira-gallery' ),
			'value' => 'above',
		),
		array(
			'name'  => __( 'Below Images', 'envira-gallery' ),
			'value' => 'below',
		),
		array(
			'name'  => __( 'Above and Below Images', 'envira-gallery' ),
			'value' => 'above-below',
		),
	);

	return apply_filters( 'envira_gallery_back_to_album_locations', $options );

}


/**
 * Helper method for retrieving title placement options
 *
 * @since 1.2.4.4
 *
 * @return array Array of sorting directions
 */
function envira_get_title_placement_options() {

	$options = array(
		array(
			'name'  => __( 'Do Not Display', 'envira-albums' ),
			'value' => 0,
		),
		array(
			'name'  => __( 'Display Above Gallery Image', 'envira-albums' ),
			'value' => 'above',
		),
		array(
			'name'  => __( 'Display Below Gallery Image', 'envira-albums' ),
			'value' => 'below',
		),
	);

	return apply_filters( 'envira_albums_title_placement_options', $options );

}

/**
 * Helper method for retrieving gallery lightbox sort options
 *
 * @since 1.2.4.4
 *
 * @return array Array of sorting directions
 */
function envira_get_gallery_lightbox_sort_effects() {

	$options = array(
		array(
			'name'  => __( 'Default', 'envira-albums' ),
			'value' => 0,
		),
		array(
			'name'  => __( 'Use Album Sort Setting', 'envira-albums' ),
			'value' => 'album',
		),
		array(
			'name'  => __( 'Use Gallery Sort Setting', 'envira-albums' ),
			'value' => 'gallery',
		),
	);

	return apply_filters( 'envira_albums_gallery_lightbox_sort_option', $options );

}

/**
 * Helper method for retrieving description options.
 *
 * @since 1.0.0
 *
 * @return array Array of positions.
 */
function envira_get_gallery_description_options() {

	$options = array(
		''              => __( 'Do Not Display', 'envira-albums' ),
		'display-above' => __( 'Display Description Above', 'envira-albums' ),
		'display-below' => __( 'Display Description Below', 'envira-albums' ),
	);

	return apply_filters( 'envira_albums_gallery_description_options', $options );

}
