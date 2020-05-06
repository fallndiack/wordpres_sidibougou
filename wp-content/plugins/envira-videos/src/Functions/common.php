<?php
/**
 * Common Video Functions.
 *
 * @since 1.0.0
 *
 * @package Envira Gallery
 * @subpackage Envira Videos
 */

/**
 * Helper function to retrieve a Setting
 *
 * @since 1.0.0
 *
 * @param string $key Setting.
 * @return array Settings
 */
function envira_video_get_setting( $key ) {

	// Get settings.
	$settings = envira_video_get_settings();

	// Check setting exists.
	if ( ! is_array( $settings ) ) {
		return false;
	}
	if ( ! array_key_exists( $key, $settings ) ) {
		return false;
	}

	$setting = apply_filters( 'envira_video_setting', $settings[ $key ] );
	return $setting;

}

/**
 * Helper function to retrieve Settings
 *
 * @since 1.0.0
 *
 * @return array Settings
 */
function envira_video_get_settings() {

	$settings = get_option( 'envira-video' );
	$settings = apply_filters( 'envira_video_settings', $settings );
	return $settings;

}

/**
 * Adds the default settings for this addon.
 *
 * @since 1.0.0
 *
 * @param array $defaults  Array of default config values.
 * @param int   $post_id     The current post ID.
 * @return array $defaults Amended array of default config values.
 */
function envira_video_defaults( $defaults, $post_id = false ) {

	// Add Videos default settings to main defaults array.
	$defaults['videos_play_icon']            = 0;
	$defaults['videos_play_icon_thumbnails'] = 0;
	$defaults['videos_autoplay']             = 0;
	$defaults['videos_enlarge']              = 0;
	$defaults['videos_playpause']            = 1;
	$defaults['videos_progress']             = 1;
	$defaults['videos_current']              = 1;
	$defaults['videos_duration']             = 1;
	$defaults['videos_volume']               = 1;
	$defaults['videos_controls']             = 1;
	$defaults['videos_fullscreen']           = 1;
	$defaults['videos_download']             = 1;

	// Return.
	return $defaults;

}
add_filter( 'envira_gallery_defaults', 'envira_video_defaults', 10, 2 );

/**
 * Returns an array of self hosted video supported file types
 * Edit this to extend support, but bear in mind mediaelementplayer's limitations
 *
 * @since 1.0.0
 *
 * @return array Supported File Types
 */
function envira_video_get_self_hosted_supported_filetypes() {

	$file_types = array(
		'mp4',
		'flv',
		'ogv',
		'webm',
	);

	$file_types = apply_filters( 'envira_videos_self_hosted_supported_filetypes', $file_types );

	return $file_types;

}

/**
 * Converts the given array to a string
 *
 * @since 1.0.0
 *
 * @param string $glue Glue to join array values together.
 * @return string Supported File Types
 */
function envira_video_get_self_hosted_supported_filetypes_string( $glue = '|' ) {

	$file_types     = envira_video_get_self_hosted_supported_filetypes();
	$file_types_str = '';
	foreach ( $file_types as $file_type ) {
		$file_types_str .= '.' . $file_type . $glue;
	}

	// Trim final glue.
	if ( ! empty( $glue ) ) {
		$file_types_str = rtrim( $file_types_str, $glue );
	}

	return $file_types_str;

}

/**
 * Returns the video type an other attributes for the given video URL
 *
 * @since 1.0.0
 *
 * @param string $url Video URL.
 * @param array  $item Gallery Item.
 * @param array  $data Gallery Data.
 * @param bool   $type_only Only return the video type.
 * @return mixed (array) Video Attributes, (string) Video Type, (bool) Unsupported Video Type
 */
function envira_video_get_video_type( $url, $item = false, $data = false, $type_only = false ) {

	$result = false;
	$regex  = envira_video_get_self_hosted_supported_filetypes_string();
	$slug   = false;
	$args   = false;

	if ( preg_match( '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $y_matches ) ) {
		// YouTube.
		$video_id_temp = $y_matches[1];

		/* if the YouTube string has a timestamp, include that in the embed url */

		// Some people use 'start' in the url(?) so switch this over.
		$url = str_replace( '?start=', '?t=', $url );

		// Add the pound sign to ensure it's in the proper foramt.
		$url = str_replace( '?t=', '?#t=', $url );

		// Get number of seconds to pass along to embed.
		$seconds = envira_video_get_seconds_from_url( $url );
		$start   = ( 0 !== $seconds ) ? '?start=' . $seconds : false;

		// Rip out any added query string values.
		if ( strpos( $video_id_temp, '?v=' ) !== false || strpos( $video_id_temp, '?vi=' ) !== false ) {
			$video_id = $video_id_temp;
		} else {
			$video_id_array = explode( '?', $video_id_temp );
			$video_id       = $video_id_array[0];
		}

		$type = 'youtube';

		if ( $type_only ) {
			return $type;
		}

		$parts = wp_parse_url( html_entity_decode( $url ) );
		if ( isset( $parts['fragment'] ) ) {
			parse_str( $parts['fragment'], $args );
			if ( array_key_exists( 't', $args ) ) {
				$args['start'] = ( 0 !== $seconds ) ? $seconds : false;
				unset( $args['t'] );
			}
		}

		$embed_url = esc_url( add_query_arg( envira_video_get_youtube_args( $data ), '//youtube.com/embed/' . $y_matches[1] . $start ) );

	} elseif ( preg_match( '~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{12,})[a-z0-9;:@#?&%=+\/\$_.-]*~i', $url, $yp_matches ) ) {

		$video_id = $yp_matches[1];

		$type = 'youtube_playlist';

		if ( $type_only ) {
			return $type;
		}

		$embed_url = esc_url( add_query_arg( envira_video_get_youtube_playlist_args( $data ), '//youtube.com/embed/videoseries?list=' . $yp_matches[1] ) );

	} elseif ( preg_match( '#(?:https?:\/\/(?:[\w]+\.)*vimeo\.com(?:[\/\w]*\/videos?)?\/([0-9]+)[^\s]*)#i', $url, $v_matches ) ) {
		// Vimeo.
		$video_id = $v_matches[1];
		$type     = 'vimeo';

		if ( $type_only ) {
			return $type;
		}

		$embed_url = esc_url( add_query_arg( envira_video_get_vimeo_args( $data ), '//player.vimeo.com/video/' . $v_matches[1] ) );

	} elseif ( preg_match( '/https?:\/\/(.+)?(wistia.com|wi.st)\/.*/i', $url, $w_matches ) ) {
		// Wistia.
		$parts    = explode( '/', $w_matches[0] );
		$video_id = array_pop( $parts );
		$type     = 'wistia';

		if ( $type_only ) {
			return $type;
		}

		$embed_url = esc_url( add_query_arg( envira_video_get_wistia_args( $data ), '//wistia.com/medias/' . $video_id ) );

	} elseif ( preg_match( '/(instagr\.am|instagram\.com)\/p\/([a-zA-Z0-9_\-]+)\/?/i', $url, $i_matches ) ) {

		// Instagram.
		$parts = explode( '/', $i_matches[0] );

		$video_id = $i_matches[2];
		$type     = 'instagram';
		$mp4_link = false;

		if ( $type_only ) {
			return $type;
		}

		// Get page data from instagram to find mp4.
		$page_data = wp_remote_get(
			$url,
			array(
				'timeout' => 10,
			)
		);

		if ( is_wp_error( $page_data ) ) {

			$embed_url = false;

		} else {

			$doc = new DOMDocument();
			libxml_use_internal_errors( true );
			$mp4_link = false;

			if ( ! empty( $page_data['body'] ) ) {
				$doc->loadHTML( $page_data['body'] );
				libxml_use_internal_errors( false );
				$metas = $doc->getElementsByTagName( 'meta' );

				for ( $i = 0; $i < $metas->length; $i++ ) {
					$meta = $metas->item( $i );
					if ( $meta->getAttribute( 'property' ) === 'og:video' ) {
						$mp4_link = $meta->getAttribute( 'content' );
					}
					if ( $meta->getAttribute( 'property' ) === 'og:video:secure_url' ) {
						$mp4_link = $meta->getAttribute( 'content' );
					}
				}
			}

			$embed_url = $mp4_link;

		}
	} elseif ( preg_match( '/(instagr\.am|instagram\.com)\/tv\/([a-zA-Z0-9_\-]+)\/?/i', $url, $i_matches ) ) {
		// Instagram.
		$parts    = explode( '/', $i_matches[0] );
		$video_id = $i_matches[2];
		$type     = 'instagram';
		$mp4_link = false;

		if ( $type_only ) {
			return $type;
		}

		// Get page data from instagram to find mp4.
		$page_data = wp_remote_get(
			$url,
			array(
				'timeout' => 10,
			)
		);

		if ( is_wp_error( $page_data ) ) {
			$embed_url = false;
		}

		$doc = new DOMDocument();
		libxml_use_internal_errors( true );
		$doc->loadHTML( $page_data['body'] );
		libxml_use_internal_errors( false );

		$metas = $doc->getElementsByTagName( 'meta' );

		for ( $i = 0; $i < $metas->length; $i++ ) {
			$meta = $metas->item( $i );
			if ( $meta->getAttribute( 'property' ) === 'og:video' ) {
				$mp4_link = $meta->getAttribute( 'content' );
			}
			if ( $meta->getAttribute( 'property' ) === 'og:video:secure_url' ) {
				$mp4_link = $meta->getAttribute( 'content' );
			}
		}

		/* exit used to be here */

		$embed_url = $mp4_link;

	} elseif ( preg_match( '/dailymotion.com\/video\/(.*)\/?(.*)/', $url, $d_matches ) ) {
		// DailyMotion.
		$parts    = explode( '/', $d_matches[0] );
		$video_id = $d_matches[1];
		$type     = 'dailymotion';

		if ( $type_only ) {
			return $type;
		}

		$embed_url = esc_url( add_query_arg( envira_video_get_dailymotion_args( $data ), '//www.dailymotion.com/video/' . $d_matches[1] ) );

	} elseif ( preg_match( '/metacafe.com\/watch\/(\d+)\/(.*)?/', $url, $m_matches ) ) {
		// Metacafe.
		$parts    = explode( '/', $m_matches[0] );
		$video_id = $m_matches[1];
		$slug     = $m_matches[2];
		$type     = 'metacafe';

		if ( $type_only ) {
			return $type;
		}

		$embed_url = esc_url( add_query_arg( envira_video_get_metacafe_args( $data ), '//www.metacafe.com/watch/' . $m_matches[1] . '/' . $slug ) );

	} elseif ( preg_match( '/twitch.tv\/videos\/(.*)\/?(.*)/', $url, $t_matches ) ) {
		// Twich.
		$parts    = explode( '/', $t_matches[0] );
		$video_id = $t_matches[1];
		$type     = 'twitch';

		if ( $type_only ) {
			return $type;
		}

		$embed_url = esc_url( add_query_arg( envira_video_get_twitch_args( $data ), '//player.twitch.tv/?video=' . $video_id ) );

	} elseif ( preg_match( '/facebook.com\/facebook\/videos\/(.*)\/?(.*)/', $url, $fb_matches ) ) {
		// Facebook Video.
		$parts    = explode( '/', $fb_matches[0] );
		$video_id = $fb_matches[1];
		$type     = 'facebook';

		if ( $type_only ) {
			return $type;
		}

		$embed_url = $url;

	} elseif ( preg_match( '/videopress.com\/v\/(.*)\/?(.*)/', $url, $vp_matches ) ) {
		// VideoPress.
		$parts    = explode( '/', $vp_matches[0] );
		$video_id = $vp_matches[1];
		$type     = 'videopress';

		if ( $type_only ) {
			return $type;
		}

		$embed_url = esc_url( add_query_arg( envira_video_get_videopress_args( $data ), '//videopress.com/v/' . $video_id ) );

	} elseif ( preg_match( '/(' . $regex . ')/', $url, $matches ) ) {
		// Self hosted.
		$parts = explode( '.', $matches[0] );
		$type  = isset( $parts[1] ) ? $parts[1] : false;

		if ( $type_only ) {
			return $type;
		}

		$video_id  = 0;
		$embed_url = $url;
	} else {

		// Not a video.
		if ( $type_only ) {
			return false;
		}
	}

	// If a video type was found, return an array of video attributes.
	if ( isset( $type ) ) {
		$result = array(
			'type'      => $type,
			'video_id'  => $video_id,
			'embed_url' => $embed_url,
			'slug'      => $slug,
			'args'      => $args,
		);
	}

	// Allow devs and custom addons to build their own routine for populating attribute data for their custom video type.
	$result = apply_filters( 'envira_videos_get_video_type', $result, $url, $item, $data );

	return $result;

}

/**
 * Returns the query args to be passed to YouTube embedded videos.
 *
 * @since 1.0.0
 *
 * @param array $data Array of gallery data.
 */
function envira_video_get_youtube_args( $data ) {

	// Get instance.
	$instance = Envira_Gallery_Shortcode::get_instance();

	$args = array(
		'autoplay'       => $instance->get_config( 'videos_autoplay', $data ),
		'controls'       => $instance->get_config( 'videos_playpause', $data ),
		'enablejsapi'    => 1,
		'modestbranding' => 1,
		'origin'         => get_home_url(),
		'rel'            => 0,
		'showinfo'       => 0,
		'version'        => 3,
		'wmode'          => 'transparent',
	);

	return apply_filters( 'envira_videos_youtube_args', $args, $data );

}

/**
 * Returns the query args to be passed to YouTube embedded playlists.
 *
 * @since 1.0.0
 *
 * @param array $data Array of gallery data.
 */
function envira_video_get_youtube_playlist_args( $data ) {

	// Get instance.
	$instance = Envira_Gallery_Shortcode::get_instance();

	$args = array(
		'autoplay'       => $instance->get_config( 'videos_autoplay', $data ),
		'controls'       => $instance->get_config( 'videos_playpause', $data ),
		'enablejsapi'    => 1,
		'modestbranding' => 1,
		'origin'         => get_home_url(),
		'rel'            => 0,
		'showinfo'       => 1,
		'version'        => 3,
		'wmode'          => 'transparent',
	);

	return apply_filters( 'envira_videos_youtube_playlist_args', $args, $data );

}

/**
 * Returns the query args to be passed to YouTube embedded videos.
 *
 * @since 1.0.0
 *
 * @param array $url The URL.
 */
function envira_video_get_seconds_from_url( $url ) {

	$regex_pattern1 = '([#|\&]+t+\=+[0-9]+[m]+[0-9]+[s])'; // minutes and seconds.
	$regex_pattern2 = '([#|\&]+t+\=+[0-9]+[m])'; // only minutes.
	$regex_pattern3 = '([#|\&]+t+\=+[0-9]+[s])'; // only seconds.
	$pattern_used   = null;
	if ( ! preg_match_all( $regex_pattern1, $url, $time ) ) { // not found "#t=XmXs".
		if ( ! preg_match_all( $regex_pattern2, $url, $time ) ) { // not found "#t=Xm".
			if ( ! preg_match_all( $regex_pattern3, $url, $time ) ) { // not found "#t=Xs".
				$parts = wp_parse_url( html_entity_decode( $url ) );
				$args  = false;
				parse_str( $parts['fragment'], $args );
				if ( array_key_exists( 't', $args ) ) {
					return intval( $args['t'] );
				}
				return false;
			} else {
				$pattern_used = 3;
			}
		} else {
			$pattern_used = 2;
		}
	} else {
		$pattern_used = 1;
	}

	$time = substr( $time[0][0], 3, strlen( $time[0][0] ) ); // deleting "#t=".

	// prints "1m40s" or "40s" if only seconds are given.
	$pattern_minutes = '([0-9]+[s])';
	$pattern_seconds = '([0-9]+[m])';
	if ( 1 === $pattern_used ) { // we have both minutes and seconds defined.

		$minutes       = preg_split( $pattern_minutes, $time );
		$seconds       = preg_split( $pattern_seconds, $time );
		$minutes       = intval( substr( $minutes[0], 0, -1 ) );
		$seconds       = intval( substr( $seconds[1], 0, -1 ) );
		$total_seconds = ( $minutes * 60 ) + $seconds;

	} else {

		if ( 2 === $pattern_used ) {
			$time2 = preg_split( $pattern_minutes, $time );
		} else {
			$time2 = preg_split( $pattern_seconds, $time );
		}
		$total_seconds = substr( $time2[0], 0, -1 );

	}

	return $total_seconds;

}

/**
 * Returns the query args to be passed to Vimeo embedded videos.
 *
 * @since 1.0.0
 *
 * @param array $data Array of gallery data.
 */
function envira_video_get_vimeo_args( $data ) {

	$args = array(
		'autoplay'   => Envira_Gallery_Shortcode::get_instance()->get_config( 'videos_autoplay', $data ),
		'badge'      => 0,
		'byline'     => 0,
		'portrait'   => 0,
		'title'      => 0,
		'api'        => 1,
		'wmode'      => 'transparent',
		'fullscreen' => 1,
	);

	return apply_filters( 'envira_videos_vimeo_args', $args, $data );

}

/**
 * Returns the query args to be passed to Instagram embedded videos.
 *
 * @since 1.0.0
 *
 * @param array $data Array of gallery data.
 */
function envira_video_get_instagram_args( $data ) {

	$args = array(
		'autoplay'   => Envira_Gallery_Shortcode::get_instance()->get_config( 'videos_autoplay', $data ),
		'badge'      => 0,
		'byline'     => 0,
		'portrait'   => 0,
		'title'      => 0,
		'api'        => 1,
		'wmode'      => 'transparent',
		'fullscreen' => Envira_Gallery_Shortcode::get_instance()->get_config( 'videos_fullscreen', $data ),
	);

	return apply_filters( 'envira_videos_instagram_args', $args, $data );

}

/**
 * Returns the query args to be passed to Wistia embedded videos.
 *
 * @since 1.0.0
 *
 * @param array $data Array of gallery data.
 */
function envira_video_get_wistia_args( $data ) {

	// Get instance.
	$instance = Envira_Gallery_Shortcode::get_instance();

	$args = array(
		'autoPlay'        => $instance->get_config( 'videos_autoplay', $data ) ? 'true' : 'false',
		'chromeless'      => 'false', // Controls.
		'playbar'         => $instance->get_config( 'videos_progress', $data ) ? 'true' : 'false',
		'smallPlayButton' => $instance->get_config( 'videos_playpause', $data ) ? 'true' : 'false',
		'videoFoam'       => 'true',
		'volumeControl'   => $instance->get_config( 'videos_volume', $data ) ? 'true' : 'false',
		'wmode'           => 'opaque',
	);

	return apply_filters( 'envira_videos_wistia_args', $args, $data );

}


/**
 * Returns the query args to be passed to DailyMotion embedded videos.
 *
 * @since 1.0.0
 *
 * @param array $data Array of gallery data.
 */
function envira_video_get_dailymotion_args( $data ) {

	// Get instance.
	$instance = Envira_Gallery_Shortcode::get_instance();

	$args = array(
		'autoplay'         => $instance->get_config( 'videos_autoplay', $data ),
		'controls'         => $instance->get_config( 'videos_playpause', $data ),
		'mute'             => false,
		'ui-theme'         => 'dark',
		'wmode'            => 'transparent',
		'endscreen-enable' => true,
	);
	return apply_filters( 'envira_videos_dailymotion_args', $args, $data );

}

/**
 * Returns the query args to be passed to Metacafe embedded videos.
 *
 * @since 1.0.0
 *
 * @param array $data Array of gallery data.
 */
function envira_video_get_metacafe_args( $data ) {

	// Get instance.
	$instance = Envira_Gallery_Shortcode::get_instance();

	$args = array(
		'autoplay' => $instance->get_config( 'videos_autoplay', $data ),
		'controls' => $instance->get_config( 'videos_playpause', $data ),
		'wmode'    => 'transparent',
	);
	return apply_filters( 'envira_videos_metacafe_args', $args, $data );

}

/**
 * Returns the query args to be passed to Twitch embedded videos.
 *
 * @since 1.0.0
 *
 * @param array $data Array of gallery data.
 */
function envira_video_get_twitch_args( $data ) {

	// Get instance.
	$instance = Envira_Gallery_Shortcode::get_instance();

	$args = array();
	return apply_filters( 'envira_videos_twitch_args', $args, $data );

}

/**
 * Returns the query args to be passed to VideoPress videos.
 *
 * @since 1.0.0
 *
 * @param array $data Array of gallery data.
 */
function envira_video_get_videopress_args( $data ) {

	$args = array(
		'autoplay'   => Envira_Gallery_Shortcode::get_instance()->get_config( 'videos_autoplay', $data ),
		'wmode'      => 'transparent',
		'fullscreen' => Envira_Gallery_Shortcode::get_instance()->get_config( 'videos_fullscreen', $data ),
	);

	return apply_filters( 'envira_videos_videopress_args', $args, $data );

}

/**
 * Returns the query args to be passed to embedded / self hosted videos
 *
 * @since 1.0.0
 *
 * @param array  $data Array of gallery data.
 * @param string $url Video URL.
 */
function envira_video_get_embed_args( $data, $url ) {

	$args = array(
		'url' => rawurlencode( $url ),
	);

	return apply_filters( 'envira_videos_embed_args', $args, $data, $url );

}
