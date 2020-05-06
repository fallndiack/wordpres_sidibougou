<?php
// @codingStandardsIgnoreFile
if ( ! class_exists( 'Envira_Albums_Metaboxes' ) ) :

	class Envira_Albums_Metaboxes {
		/**
		 * _instance
		 *
		 * (default value: null)
		 *
		 * @var mixed
		 * @access public
		 * @static
		 */
		public static $instance = null;

		/**
		 * Helper method for retrieving config values.
		 *
		 * @since 1.0.0
		 *
		 * @global int $id        The current post ID.
		 * @global object $post   The current post object.
		 * @param string $key     The config key to retrieve.
		 * @param string $default A default value to use.
		 * @return string         Key value on success, empty string on failure.
		 */
		public function get_config( $key, $default = false ) {

			global $id, $post;

			// Get the current post ID. If ajax, grab it from the $_POST variable.
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				$post_id = absint( $_POST['post_id'] );
			} else {
				$post_id = isset( $post->ID ) ? $post->ID : (int) $id;
			}

			$settings = get_post_meta( $post_id, '_eg_album_data', true );
			if ( isset( $settings['config'][ $key ] ) ) {
				return $settings['config'][ $key ];
			} else {
				return $default ? $default : '';
			}

		}

		/**
		 * Helper method for setting default config values.
		 *
		 * @since 1.0.0
		 *
		 * @param string $key The default config key to retrieve.
		 * @return string Key value on success, false on failure.
		 */
		public function get_config_default( $key ) {

			return envira_get_config_default( $key );

		}
		/**
		 * Get_instance function.
		 *
		 * __Depricated since 1.7.0.
		 *
		 * @access public
		 * @static
		 * @return object
		 */
		public static function get_instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Albums_Metaboxes ) ) {

				self::$instance = new self();
			}

			return self::$instance;

		}
	}
endif;
