<?php
/**
 * Common class
 *
 * @since 1.0.0
 *
 * @package Envira_Lightroom
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Common class
 *
 * @since 1.0.0
 *
 * @package Envira_Lightroom
 * @author  Envira Team
 */
class Envira_Lightroom_Common {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Generates a new access token, storing it in the options table
	 *
	 * @since   1.0
	 *
	 * @param   int $length     Required Access Token Length.
	 * @return  string              Access Token
	 */
	public function generate_access_token( $length = 32 ) {

		// Generate and save token.
		$access_token = wp_generate_password( $length, false, false );
		update_option( 'envira_lightroom_access_token', $access_token );

		// Return.
		return $access_token;

	}

	/**
	 * Retrieves the access token from the options table
	 *
	 * If a token doesn't exist, one is created and stored using
	 * generate_access_token()
	 *
	 * @since   1.0
	 * @return  string              Acccess Token
	 */
	public function get_access_token() {

		// Get access token from options table.
		$access_token = get_option( 'envira_lightroom_access_token' );

		// If no access token exists, create one now.
		if ( empty( $access_token ) ) {
			$access_token = $this->generate_access_token();
		}

		// Return.
		return $access_token;

	}

	/**
	 * Retrieves the user ID from the options table
	 *
	 * @since   1.0
	 * @return  int                 WordPress User ID
	 */
	public function get_user_id() {

		// Get user ID from options table.
		$user_id = get_option( 'envira_lightroom_user_id' );

		// Return.
		return $user_id;

	}

	/**
	 * Updates the user ID in the options table
	 *
	 * @since   1.0
	 * @param   int $user_id User id.
	 * @return  int WordPress User ID
	 */
	public function update_user_id( $user_id ) {

		// Update User ID.
		update_option( 'envira_lightroom_user_id', $user_id );

		// Return.
		return $user_id;

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Lightroom_Common object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Lightroom_Common ) ) {
			self::$instance = new Envira_Lightroom_Common();
		}

		return self::$instance;

	}

}

// Load the common class.
$envira_lightroom_common = Envira_Lightroom_Common::get_instance();
