<?php
/**
 * Common
 *
 * @since 1.5.0
 *
 * @package Envira_Instagram
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

if ( ! class_exists( 'Envira_Instagram_Common' ) ) :

	/**
	 * Common
	 *
	 * @since 1.5.0
	 *
	 * @package Envira_Instagram
	 * @author  Envira Gallery Team <support@enviragallery.com>
	 */
	class Envira_Instagram_Common { // @codingStandardsIgnoreLine - Firing off a duplicate warning?

		/**
		 * Holds the class object.
		 *
		 * @since 1.0.0
		 *
		 * @var object
		 */
		public static $instance;

		/**
		 * Path to the file.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public $file = __FILE__;

		/**
		 * Holds the base class object.
		 *
		 * @since 1.0.0
		 *
		 * @var object
		 */
		public $base;

		/**
		 * Primary class constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			add_filter( 'envira_gallery_defaults', array( $this, 'defaults' ), 10, 2 );

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
		public function defaults( $defaults, $post_id ) {

			$defaults['instagram_type']           = 'users_self_media_recent';
			$defaults['instagram_number']         = 5;
			$defaults['instagram_res']            = 'standard_resolution';
			$defaults['instagram_link']           = '';
			$defaults['instagram_link_target']    = 0;
			$defaults['instagram_caption']        = 1;
			$defaults['instagram_caption_length'] = 999;
			$defaults['instagram_random']         = 0;
			$defaults['instagram_cache']          = 1;

			// Return.
			return $defaults;

		}

		/**
		 * Returns the URL to begin the Instagram oAuth process with
		 *
		 * @since 1.0.5
		 *
		 * @param   string $return_to Return.
		 * @return  string  Instagram oAuth URL
		 */
		public function get_oauth_url( $return_to ) {

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
		public function get_instagram_auth( $slot = 1 ) {

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
		public function instagram_types() {

			$types = array(
				array(
					'value' => 'users_self_media_recent',
					'name'  => __( 'My Instagram Photos', 'envira-instagram' ),
				),
				// Below options removed due to Instagram API changes
				//
				// array(
				// 'value' => 'users_self_media_liked',
				// 'name'  => __( 'Instagram Photos I\'ve Liked', 'envira-instagram' )
				// ),
				// array(
				// 'value' => 'tags_tag_media_recent',
				// 'name'  => __( 'Instagram Photos by Tag', 'envira-instagram' )
				// ),.
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
		public function instagram_accounts() {

			$total_accounts = 3;

			for ( $x = 1; $x <= $total_accounts; $x++ ) {
				$temp_auth = $this->get_instagram_auth( $x );
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
		public function instagram_resolutions() {

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
		public function get_instagram_link_options() {

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


		/**
		 * Returns the singleton instance of the class.
		 *
		 * @since 1.0.0
		 *
		 * @return object The Envira_Instagram_Common object.
		 */
		public static function get_instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Instagram_Common ) ) {
				self::$instance = new Envira_Instagram_Common();
			}

			return self::$instance;

		}

	}
endif;
