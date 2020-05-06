<?php
/**
 * Common
 *
 * @since 1.5.0
 *
 * @package Envira_Instagram
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

/**
 * Adds the default settings for this addon.
 *
 * @since 1.0.0
 *
 * @param array $defaults  Array of default config values.
 * @param int   $post_id     The current post ID.
 * @return array $defaults Amended array of default config values.
 */
function envira_instagram_defaults( $defaults, $post_id ) {

	$defaults['instagram_type']           = 'users_self_media_recent';
	$defaults['instagram_number']         = 5;
	$defaults['instagram_res']            = 'standard_resolution';
	$defaults['instagram_link']           = '';
	$defaults['instagram_link_target']    = 0;
	$defaults['instagram_caption']        = 1;
	$defaults['instagram_caption_length'] = 999;
	$defaults['instagram_random']         = 0;
	$defaults['instagram_cache']          = 1;

	return $defaults;

}
add_filter( 'envira_gallery_defaults', 'envira_instagram_defaults', 10, 2 );

/**
 * Returns the URL to begin the Instagram oAuth process with
 *
 * @since 1.0.5
 *
 * @param   string $return_to URL to return to.
 * @return  string  Instagram oAuth URL
 */
function envira_instagram_get_oauth_url( $return_to ) {

	$url = add_query_arg(
		array(
			'client_id'     => '7deb7ccef2eb4908adf1f1836f59973d',
			'response_type' => 'code',
			'redirect_uri'  => 'https://enviragallery.com/?return_to=' . rawurlencode( admin_url( $return_to ) ),
		),
		'https://api.instagram.com/oauth/authorize/'
	);

	return $url;

}

/**
 * Returns Instagram auth data.
 *
 * @since 1.4.2
 * @param int $slot Slot.
 * @return string|bool Access token on success, false on failure.
 */
function envira_instagram_get_instagram_auth( $slot = 1 ) {

	if ( intval( $slot ) <= 1 ) {
		return get_option( 'envira_instagram' );
	} else {
		return get_option( 'envira_instagram_' . $slot );
	}

}

/**
 * Returns the available Instagram query types.
 *
 * @since 1.0.0
 *
 * @return array Array of Instagram query types.
 */
function envira_instagram_instagram_types() {

	$types = array(
		array(
			'value' => 'users_self_media_recent',
			'name'  => __( 'My Instagram Photos', 'envira-instagram' ),
		),
	);

	return apply_filters( 'envira_instagram_types', $types );

}

/**
 * Returns the available accounts.
 *
 * @since 1.4.2
 *
 * @return array Array of Instagram query types.
 */
function envira_instagram_instagram_accounts() {

	$total_accounts = 3;

	for ( $x = 1; $x <= $total_accounts; $x++ ) {
		$temp_auth = envira_instagram_get_instagram_auth( $x );
		if ( $temp_auth ) {
			$accounts[] = array(
				'value' => $x,
				'name'  => ( ! empty( $temp_auth['username'] ) ) ? $temp_auth['username'] : 'Instagram Account #' . $x,
			);
		}
	}

	return apply_filters( 'envira_instagram_accounts', $accounts );

}

/**
 * Returns the available Instagram image resolutions.
 *
 * @since 1.0.0
 *
 * @return array Array of Instagram image resolutions.
 */
function envira_instagram_instagram_resolutions() {

	$resolutions = array(
		array(
			'value' => 'thumbnail',
			'name'  => __( 'Thumbnail (150x150)', 'envira-instagram' ),
		),
		array(
			'value' => 'low_resolution',
			'name'  => __( 'Low Resolution (306x306)', 'envira-instagram' ),
		),
		array(
			'value' => 'standard_resolution',
			'name'  => __( 'Standard Resolution (640x640)', 'envira-instagram' ),
		),
	);

	return apply_filters( 'envira_instagram_resolutions', $resolutions );

}

/**
 * Returns the available Instagram link options.
 *
 * @since 1.0.0
 *
 * @return array Array of Instagram image resolutions.
 */
function envira_instagram_get_instagram_link_options() {

	$link_options = array(
		array(
			'value' => '',
			'name'  => __( 'No link', 'envira-instagram' ),
		),
		array(
			'value' => 'instagram_page',
			'name'  => __( 'Original Page at Instagram', 'envira-instagram' ),
		),
		array(
			'value' => 'instagram_image',
			'name'  => __( 'Direct Image On Instagram', 'envira-instagram' ),
		),
	);

	return apply_filters( 'envira_instagram_link_options', $link_options );

}
