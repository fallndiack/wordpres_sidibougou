<?php
/**
 * Ajax class.
 *
 * @since 1.0.0
 *
 * @package Envira_Password_Protection
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ajax class.
 *
 * @since 1.0.0
 *
 * @package Envira_Password_Protection
 * @author  Envira Team
 */
class Envira_Password_Protection_Admin_AJAX {

	/**
	 * Holds the class object.
	 *
	 * @since 1.1.3
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.1.3
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
	 * @since 1.1.3
	 */
	public function __construct() {

		add_action( 'wp_ajax_envira_password_protection_update_private_links', array( $this, 'gallery_update_private_links' ) );

	}

	/**
	 * Returns HTML markup for the required Gallery ID / Album ID and Page
	 *
	 * @since 1.1.3
	 */
	public function gallery_update_private_links() {

		// Check nonce.
		check_ajax_referer( 'envira-password-protection', 'nonce' );

		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : false;

		if ( ! $post_id ) {
			wp_send_json_error();
			die();
		}

		$old           = get_post_meta( intval( $post_id ), 'envira_private_links', true );
		$new           = array();
		$codes_decoded = isset( $_POST['link_data'] ) ? urldecode( sanitize_text_field( wp_unslash( $_POST['link_data'] ) ) ) : false;
		parse_str( $codes_decoded, $codes );
		$codes = $codes['envira_private_link_code'];

		$count = count( $codes );

		foreach ( $codes as $code ) {

			if ( trim( $code ) !== '' ) :
				$used = ( ! empty( intval( $old[ $code ]['used'] ) ) ) ? intval( $old[ $code ]['used'] ) : 0;
				$new[ stripslashes( wp_strip_all_tags( $code ) ) ] = array( 'used' => $used );
			endif;

		}

		if ( ! empty( $new ) && $new !== $old ) {
			update_post_meta( $post_id, 'envira_private_links', $new );
		} elseif ( empty( $new ) && $old ) {
			delete_post_meta( $post_id, 'envira_private_links', $old );
		} else {
			wp_send_json_error();
			die();
		}

		wp_send_json_success();

		die();

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.1.3
	 *
	 * @return object The Envira_Pagination_AJAX object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Password_Protection_Admin_AJAX ) ) {
			self::$instance = new Envira_Password_Protection_Admin_AJAX();
		}

		return self::$instance;

	}

}

// Load the ajax class.
$envira_password_protection_admin_ajax = Envira_Password_Protection_Admin_AJAX::get_instance();
