<?php
/**
 * Albums Theme Functions
 *
 * @package Envira Albums
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loads the Envirabox Album Configuration
 *
 * @access public
 * @param int  $album_id Album post Id.
 * @param bool $raw return raw.
 * @return string json string returned.
 */
function envira_album_load_lightbox_config( $album_id, $raw = false ) {

	$data       = envira_get_album( $album_id );
	$data['id'] = $album_id;

	$lightbox_themes = envira_get_lightbox_themes();
	$key             = array_search( envira_get_config( 'lightbox_theme', $data ), array_column( $lightbox_themes, 'value' ), true );
	$current_theme   = $lightbox_themes[ $key ];

	if ( ! empty( $current_theme['config'] ) && is_array( $current_theme['config'] ) ) {

		$current_theme['config']['base_template'] = function_exists( $current_theme['config']['base_template'] ) ? call_user_func( $current_theme['config']['base_template'], $data ) : envirabox_default_template( $data );

		$config = $current_theme['config'];

	} else {

		$config = envirabox_album_default_config( $album_id );

	}

	$config['load_all']       = apply_filters( 'envira_load_all_images_lightbox', false, $data );
	$config['error_template'] = envirabox_error_template( $data );

	// If supersize is enabled lets override settings.
	if ( envira_get_config( 'supersize', $data ) === 1 ) {
		$config['margins'] = array( 0, 0 );
	}

	$legacy_themes                    = envirabox_legecy_themes();
	$config['thumbs_position']        = in_array( $current_theme['value'], $legacy_themes, true ) ? envira_get_config( 'thumbnails_position', $data ) : 'lock';
	$config['arrow_position']         = in_array( $current_theme['value'], $legacy_themes, true ) ? envira_get_config( 'arrows_position', $data ) : false;
	$config['arrows']                 = in_array( $current_theme['value'], $legacy_themes, true ) ? envira_get_config( 'arrows', $data ) : true;
	$config['toolbar']                = in_array( $current_theme['value'], $legacy_themes, true ) ? false : true;
	$config['infobar']                = in_array( $current_theme['value'], $legacy_themes, true ) ? true : false;
	$config['show_smallbtn']          = in_array( $current_theme['value'], $legacy_themes, true ) ? true : false;
	$config['inner_caption']          = in_array( $current_theme['value'], $legacy_themes, true ) ? true : false;
	$config['caption_position']       = in_array( $current_theme['value'], $legacy_themes, true ) ? envira_get_config( 'title_display', $data ) : false;
	$config['lightbox_title_caption'] = in_array( $current_theme['value'], $legacy_themes, true ) ? envira_get_config( 'lightbox_title_caption', $data ) : false;
	$config['idle_time']              = envira_get_config( 'idle_time', $data ) ? envira_get_config( 'idle_time', $data ) : false;
	$config['click_content']          = envira_get_config( 'click_content', $data ) ? envira_get_config( 'click_content', $data ) : false;
	$config['click_slide']            = envira_get_config( 'click_slide', $data ) ? envira_get_config( 'click_slide', $data ) : false;
	$config['click_outside']          = envira_get_config( 'click_outside', $data ) ? envira_get_config( 'click_outside', $data ) : false;
	$config['small_btn_template']     = '<a data-envirabox-close class="envirabox-item envirabox-close envirabox-button--close" title="' . __( 'Close', 'envira-gallery' ) . '" href="#"></a>';

	return wp_json_encode( $config );

}

/**
 * Get Envira Albums default config.
 *
 * @param int $album_id Album post id.
 * @return array
 */
function envirabox_album_default_config( $album_id ) {

	$data = envira_get_album( $album_id );

	$config = array(
		'arrows'          => 'true',
		'margins'         => array( 220, 0 ), // top/bottom, left/right.
		'template'        => envirabox_default_template( $data ),
		'thumbs_position' => 'bottom',
	);

	return apply_filters( 'envirabox_default_config', $config, $data, $album_id );

}
