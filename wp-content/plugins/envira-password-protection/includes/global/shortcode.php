<?php
/**
 * Shortcode class.
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
 * Shortcode class.
 *
 * @since 1.0.0
 *
 * @package Envira_Password_Protection
 * @author  Envira Team
 */
class Envira_Password_Protection_Shortcode {

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
	 * Helps store if the gallery is dynamic
	 *
	 * @since 1.0.0
	 *
	 * @var boolean
	 */
	public $is_dynamic = false;

	/**
	 * Helps store a dynamic password if needed
	 *
	 * @since 1.0.0
	 *
	 * @var boolean
	 */
	public $dynamic_password = '';

	/**
	 * Helps store a dynamic email/login if needed
	 *
	 * @since 1.0.0
	 *
	 * @var boolean
	 */
	public $dynamic_email = '';

	/**
	 * Allowed HTML
	 *
	 * @var mixed
	 * @access public
	 */
	public $wp_kses_allowed_html = array(
		'a'      => array(
			'href'                => array(),
			'target'              => array(),
			'class'               => array(),
			'title'               => array(),
			'data-status'         => array(),
			'data-envira-tooltip' => array(),
			'data-id'             => array(),
		),
		'br'     => array(),
		'img'    => array(
			'src'   => array(),
			'class' => array(),
			'alt'   => array(),
		),
		'p'      => array(
			'class' => array(),
		),
		'div'    => array(
			'class' => array(),
		),
		'li'     => array(
			'id'                              => array(),
			'class'                           => array(),
			'data-envira-gallery-image'       => array(),
			'data-envira-gallery-image-model' => array(),
		),
		'em'     => array(),
		'span'   => array(
			'class' => array(),
		),
		'strong' => array(),
	);

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		add_action( 'wp', array( $this, 'check_private_link' ), 1 );
		add_action( 'login_form_postpass', array( $this, 'check_username' ) );
		add_filter( 'envira_abort_gallery_output', array( $this, 'maybe_password_protect' ), 10, 4 );
		add_filter( 'envira_abort_gallery_output', array( $this, 'maybe_password_protect_dynamic' ), 10, 4 );
		add_filter( 'envira_abort_album_output', array( $this, 'maybe_password_protect' ), 10, 4 );
		add_filter( 'the_password_form', array( $this, 'amend_password_form' ) );
		add_filter( 'the_password_form', array( $this, 'change_password_message' ) );
		add_filter( 'post_password_expires', array( $this, 'custom_post_password_expires' ) );

	}

	/**
	 * Customize cookie expire time for password.
	 *
	 * @since 1.0.0
	 *
	 * @param int $expires The expiry time, as passed to setcookie().
	 */
	public function custom_post_password_expires( $expires ) {
		$expires = apply_filters( 'envira_password_protection_time_limit', time() + 864000, false, false ); // default is 10 days, same as Password Protected Posts.
		return $expires;
	}

	/**
	 * Customize message for password.
	 *
	 * @since 1.0.0
	 * @param mixed $output Output.
	 */
	public function change_password_message_content( $output ) {

		// There's no function var or public exposure, so grab the ID from the form.
		$start     = 'id="pwbox-';
		$end       = '"';
		$start_pos = strpos( $output, $start ) + strlen( $start );
		$end_pos   = strpos( $output, $end, $start_pos );
		$id        = substr( $output, $start_pos, ( $end_pos - $start_pos ) );

		// Check we got a valid ID.
		if ( ! is_numeric( $id ) ) {
			return $output;
		}

		// If this isn't a dynamic, check post is an Envira Post.
		global $post;

		if ( false === $this->is_dynamic && ! in_array( $post->post_type, array( 'envira', 'envira_album' ), true ) ) {
			return $output;
		}

		// Depend on whether we are on a Gallery or Album, read appropriate config.
		switch ( $post->post_type ) {
			/**
			* Gallery
			*/
			case 'envira':
				$instance = Envira_Gallery_Shortcode::get_instance();
				$data     = Envira_Gallery::get_instance()->get_gallery( $id );
				break;

			/**
			* Album
			*/
			case 'envira_album':
				$instance = Envira_Albums_Shortcode::get_instance();
				$data     = Envira_Albums::get_instance()->get_album( $id );
				break;

			/**
			* Non-Envira - bail
			*/
			default:
				return $output;
		}

		$message = ( ! empty( $data['config']['password_protection_message'] ) ) ? $data['config']['password_protection_message'] : false;
		$message = apply_filters( 'envira_password_protection_message', $message, $post, $data );
		if ( ! empty( $post->post_password ) && post_password_required( $post ) === false ) {

			$output = '

			<form action="' . get_option( 'siteurl' ) . '/wp-pass.php" method="post">
			  ' . $message . '

			    <label for="post_password">Password:</label>
			    <input name="post_password" class="input" type="password" size="20" />
			    <input type="submit" name="Submit" class="button" value="' . __( 'Submit' ) . '" />

			</form>

			';
			return $output;

		} else {

			return $output;
		}

	}


	/**
	 * Check for private link.
	 *
	 * @since 1.0.0
	 */
	public function check_private_link() {

		if ( ! isset( $_GET['code'] ) ) { // @codingStandardsIgnoreLine - potentially add nonce to querystring?
			return;
		}

		global $post;

		if ( ! isset( $post->ID ) ) {
			return;
		}

		$envira_private_links = get_post_meta( $post->ID, 'envira_private_links', true );

		// Does the inputted code match codes entered for this post?
		$entered_code = stripslashes( wp_strip_all_tags( trim( wp_unslash( $_GET['code'] ) ) ) ); // @codingStandardsIgnoreLine - potentially add nonce to querystring?

		if ( array_key_exists( $entered_code, $envira_private_links ) ) {

			// the code exists, so bypass password which means setting cookie.
			if ( isset( $post->post_password ) ) {

				require_once ABSPATH . WPINC . '/class-phpass.php';
				$hasher = new PasswordHash( 8, true );

				$expire = apply_filters( 'envira_password_protection_time_limit', time() + 864000, $post, false ); // default is 10 days, same as Password Protected Posts.
				setcookie(
					'wp-postpass_' . COOKIEHASH,
					$hasher->HashPassword( wp_unslash( esc_attr( $post->post_password ) ) ),
					$expire,
					COOKIEPATH
				);

			}

			$update_used = apply_filters( 'envira_private_link_update_counter', true, $entered_code, $post );

			if ( $update_used ) {

				// add one to the 'used' counter, regardless of the above and update meta data.
				if ( isset( $envira_private_links[ $entered_code ] ) ) {
					$envira_private_links[ $entered_code ]['used'] = intval( $envira_private_links[ $entered_code ]['used'] ) + 1;
				}

				update_post_meta( $post->ID, 'envira_private_links', $envira_private_links );

			}

			wp_safe_redirect( get_permalink( $post->ID ) );
			exit;

		}

	}

	/**
	 * Checks if the given POSTed Gallery / Album requires a username as part of the validation
	 * process, and if so attempts to validate it.
	 *
	 * If validation fails, we abort so that the Post password isn't tested.
	 * If validation passes, we store the username in a cookie, and continue.
	 *
	 * @since 1.0.1
	 */
	public function check_username() {

		// Check a Post ID and Username are specified in the POST request.
		if ( ! isset( $_POST['post_ID'] ) || ! isset( $_POST['post_username'] ) ) {  // @codingStandardsIgnoreLine - potentially add nonce to querystring?
			return;
		}

		// Get the gallery/album's email address.
		if ( strpos( $_POST['post_ID'], 'dynamic_' ) !== false ) { // @codingStandardsIgnoreLine - potentially add nonce to querystring?
			// Prepare vars.
			$dynamic_id = esc_html( wp_unslash( $_POST['post_ID'] ) ); // @codingStandardsIgnoreLine - potentially add nonce to querystring?
			// this is a dynamic scenario.
			$email = sanitize_email( wp_unslash( $_POST['post_username'] ) ); // @codingStandardsIgnoreLine - potentially add nonce to querystring?
			// add a filter so user can set time limit, otherwise default to 10 days (same as Password Protected Posts in WordPress).
			$time_limit = apply_filters( 'envira_password_protection_time_limit', time() + 864000, false, false );
			// write cookie.
			setcookie( 'envira_password_protection_email_' . $dynamic_id, $email, time() . intval( $time_limit ) );
		} else {
			// Prepare vars.
			$id       = absint( $_POST['post_ID'] ); // @codingStandardsIgnoreLine - potentially add nonce to querystring?
			$cpt_post = get_post( $id );
			switch ( $cpt_post->post_type ) {
				/**
				* Gallery
				*/
				case 'envira':
					$instance = Envira_Gallery_Shortcode::get_instance();
					$data     = Envira_Gallery::get_instance()->get_gallery( $id );
					break;

				/**
				* Album
				*/
				case 'envira_album':
					$instance = Envira_Albums_Shortcode::get_instance();
					$data     = Envira_Albums::get_instance()->get_album( $id );
					break;

				/**
				* Non-Envira - bail
				*/
				default:
					return;
			}

			// Check the email address / username matches the gallery/album.
			$email = $instance->get_config( 'password_protection_email', $data );

			if ( isset( $email ) && ! empty( $email ) ) {
				if ( $email !== $_POST['post_username'] ) { // @codingStandardsIgnoreLine - potentially add nonce to querystring?
					// Username doesn't match.
					// Redirect to referring page.
					wp_redirect( $_SERVER['HTTP_REFERER'] ); // @codingStandardsIgnoreLine - potentially add nonce to querystring?
					die();
				}

				// If here, username matches.
				// Set cookie.
				$time_limit = apply_filters( 'envira_password_protection_time_limit', time() + 864000, $cpt_post, $data );
				setcookie( 'envira_password_protection_email_' . $id, $email, time() + intval( $time_limit ) );

			}
		}

	}

	/**
	 * Password protect a gallery or album, if password protection is enabled and the password
	 * hasn't been successfully entered.
	 *
	 * @since 1.0.0
	 *
	 * @param array $password_form Password Form.
	 * @param array $data Gallery or Album Data.
	 * @param int   $id Gallery or Album ID.
	 * @return mixed false (if password required) or Gallery/Album Data
	 */
	public function maybe_password_protect( $password_form, $data, $id = false ) {

		if ( ! isset( $data['config']['type'] ) || ( isset( $data['config']['type'] ) && 'dynamic' === $data['config']['type'] ) ) {
			return $password_form;
		}

		// Only do this on the frontend, bail if this is the backend/admin.
		if ( is_admin() ) {
			return false;
		}

		// Bail if we couldn't get the id.
		if ( ! $id ) {
			return false;
		}

		// Get Gallery/Album Post.
		$cpt_post = get_post( $id );

		// Bail if we couldn't get the Gallery / Album.
		if ( ! $cpt_post ) {
			return false;
		}

		if ( empty( $cpt_post->post_password ) ) {
			return false;
		}

		// Assume username is valid.
		$username_valid = true;

		// Get instance.
		switch ( $cpt_post->post_type ) {
			/**
			* Gallery
			*/
			case 'envira':
				$instance = Envira_Gallery_Shortcode::get_instance();
				break;

			/**
			* Album
			*/
			case 'envira_album':
				$instance = Envira_Albums_Shortcode::get_instance();
				break;

			/**
			* Non-Envira - bail
			*/
			default:
				return false;

		}

		// Check the email address for the Gallery/Album is set, and matches the cookie.
		$email = $instance->get_config( 'password_protection_email', $data );
		if ( isset( $email ) && ! empty( $email ) ) {
			// Check cookie.
			if ( ! isset( $_COOKIE[ 'envira_password_protection_email_' . $id ] ) ||
				$_COOKIE[ 'envira_password_protection_email_' . $id ] !== $email ) {

				// No cookie, or cookie exists and doesn't match username required.
				$username_valid = false;
			}
		}

		// Check if Post is password protected, and if so whether the password
		// has been provided.
		if ( $username_valid && post_password_required( $cpt_post ) === false ) {
			// Non password protected or username/password provided and valid - OK to return gallery/album.
			return false;
		}

		/**
		* Post is password protected, and a password hasn't been specified
		* If Post is viewed through Standalone Plugin, WordPress will append
		* the password form automatically.
		* Otherwise we need to render the password form
		*/
		if ( is_singular( array( 'envira', 'envira_album' ) ) ) {
			if ( isset( $_COOKIE[ 'wp-postpass_' . COOKIEHASH ] ) ) {
				$wrong_password_message = ! empty( envira_get_config( 'wrong_password_message', $data ) ) ? esc_html( envira_get_config( 'wrong_password_message', $data ) ) : sprintf( __( 'Sorry, your password is wrong.', 'envira_gallery' ) );
				echo wp_kses( apply_filters( 'envira_password_protection_failed_message', $wrong_password_message, $cpt_post, $data ), $this->wp_kses_allowed_html );
			}
			return;
		}

		// Assign password form to variable.
		$password_return = get_the_password_form( $id );

		return $password_return;

	}

	/**
	 * Password protect a gallery or album, if password protection is enabled and the password
	 * hasn't been successfully entered.
	 *
	 * @since 1.0.0
	 *
	 * @param array $password_form Password Form.
	 * @param array $data Gallery or Album Data.
	 * @param int   $id Gallery or Album ID.
	 * @param array $atts Attributes.
	 * @return mixed false (if password required) or Gallery/Album Data
	 */
	public function maybe_password_protect_dynamic( $password_form, $data, $id = false, $atts = false ) {

		// Only do this on the frontend, bail if this is the backend/admin.
		if ( is_admin() ) {
			return false;
		}

		// Turn away any non-dynamic gallery.
		if ( ! isset( $data['config']['type'] ) || ( isset( $data['config']['type'] ) && 'dynamic' !== $data['config']['type'] ) ) {
			return $password_form;
		}

		// Since this is a dynamic gallery, we check for password protection... a password must be passed, otherwise no go.
		if ( ! $atts || ! array_key_exists( 'password', $atts ) ) {
			return $password_form;
		}

		// Bail if we couldn't get the id.
		if ( ! $id ) {
			return false;
		}

		// User needs to have passed a password arg.
		if ( ! isset( $data['config']['password'] ) ) {
			return;
		} else {
			$this->is_dynamic       = true;
			$this->dynamic_password = $data['config']['password'];
		}

		// User needs to have passed a password arg.
		if ( isset( $data['config']['password_protection_email'] ) ) {
			$this->is_dynamic    = true;
			$this->dynamic_email = $data['config']['password_protection_email'];
		}

		// Get Gallery/Album Post.
		$cpt_post = get_post( $id );
		// Bail if we couldn't get the Gallery / Album.
		if ( ! $cpt_post ) {
			return false;
		}

		$dynamic_id = 'dynamic_' . $id;

		// Assume username is valid.
		$username_valid = true;

		// Check the email address for the Gallery/Album is set, and matches the cookie.
		if ( isset( $this->dynamic_email ) && ! empty( $this->dynamic_email ) ) {
			// Check cookie.
			if ( ! isset( $_COOKIE[ 'envira_password_protection_email_' . $dynamic_id ] ) || $_COOKIE[ 'envira_password_protection_email_' . $dynamic_id ] !== $this->dynamic_email ) {
				// No cookie, or cookie exists and doesn't match username required.
				$username_valid = false;
			}
		}

		// If the cookie doesn't exist, then we need the form.
		if ( ! isset( $_COOKIE[ 'wp-postpass_' . COOKIEHASH ] ) ) {
			$password_return = get_the_password_form( $id );
			return $password_return;
		}

		// If the cookie DOES exist, we need to check it...
		// ... and we need to duplicate how WordPress does it because we can't pass the dynamic email/password to the function.
		require_once ABSPATH . WPINC . '/class-phpass.php';

		$hasher = new PasswordHash( 8, true );
		$hash   = sanitize_text_field( wp_unslash( $_COOKIE[ 'wp-postpass_' . COOKIEHASH ] ) );
		if ( 0 !== strpos( $hash, '$P$B' ) ) {
				$required = true;
		} else {
				$required = ! $hasher->CheckPassword( $this->dynamic_password, $hash );
		}

		if ( $required ) {

			// the password didn't match so we need to return the password form.
			$password_return = get_the_password_form( $id );
			return $password_return;
		} else {

			// NOT required, which means we in theory could proceed.
			if ( ! $username_valid ) {

				// if the username isn't valid, display the form.
				$password_return = get_the_password_form( $id );
				return $password_return;
			}
		}

		// If we made it this far, then we SHOULD be ok to show the user the gallery.
		return false;

	}

	/**
	 * Checks if the given form belongs to an Envira Gallery, and if that gallery
	 * has a custom message, display that rather than the WordPress default
	 *
	 * @since 1.0.1
	 *
	 * @param string $output Output.
	 * @return string Output
	 */
	public function change_password_message( $output ) {

		// There's no function var or public exposure, so grab the ID from the form.
		$start     = 'id="pwbox-';
		$end       = '"';
		$start_pos = strpos( $output, $start ) + strlen( $start );
		$end_pos   = strpos( $output, $end, $start_pos );
		$id        = substr( $output, $start_pos, ( $end_pos - $start_pos ) );

		// Check we got a valid ID.
		if ( ! is_numeric( $id ) ) {
			return $output;
		}

		// If this isn't a dynamic, check post is an Envira Post.
		$post = get_post( $id );
		if ( false === $this->is_dynamic && ! in_array( $post->post_type, array( 'envira', 'envira_album' ), true ) ) {
			return $output;
		}

		// Depend on whether we are on a Gallery or Album, read appropriate config.
		switch ( $post->post_type ) {
			/**
			* Gallery
			*/
			case 'envira':
				$data = envira_get_gallery( $id );
				break;

			/**
			* Album
			*/
			case 'envira_album':
				$instance = Envira_Albums_Shortcode::get_instance();
				$data     = Envira_Albums::get_instance()->get_album( $id );
				break;

			/**
			* Non-Envira - bail
			*/
			default:
				return $output;

		}

		$message = ( ! empty( $data['config']['password_protection_message'] ) ) ? $data['config']['password_protection_message'] : 'This content is password protected. To view it please enter your password below:';
		$message = apply_filters( 'envira_password_protection_message', $message, $post, $data );

		if ( $message ) {
			$output = str_replace( 'This content is password protected. To view it please enter your password below:', wpautop( $message ), $output );
			$output = apply_filters( 'envira_password_protection_message_output', $output, $post, $data );
			return $output;
		} else {
			return $output;
		}

	}

	/**
	 * Checks if the given form belongs to an Envira Gallery, and if that gallery
	 * requires a username as well, prepends the form with a username field
	 *
	 * @since 1.0.1
	 *
	 * @param string $output Output.
	 * @return string Output
	 */
	public function amend_password_form( $output ) {

		// There's no function var or public exposure, so grab the ID from the form.
		$start     = 'id="pwbox-';
		$end       = '"';
		$start_pos = strpos( $output, $start ) + strlen( $start );
		$end_pos   = strpos( $output, $end, $start_pos );
		$id        = substr( $output, $start_pos, ( $end_pos - $start_pos ) );

		// Check we got a valid ID.
		if ( ! is_numeric( $id ) ) {
			return $output;
		}

		// If this isn't a dynamic, check post is an Envira Post.
		$post = get_post( $id );
		if ( false === $this->is_dynamic && ! empty( $post->post_type ) && ! in_array( $post->post_type, array( 'envira', 'envira_album' ), true ) ) {
			return $output;
		}

		if ( true === $this->is_dynamic ) {
			// define the dynamic id.
			$dynamic_id = 'dynamic_' . $id;
			// Build username field.
			$username       = ( isset( $_COOKIE[ 'envira_password_protection_email_' . $dynamic_id ] ) ? htmlspecialchars( sanitize_text_field( wp_unslash( $_COOKIE[ 'envira_password_protection_email_' . $dynamic_id ] ) ) ) : '' );
			$username_field = '<p><label for="username-' . $dynamic_id . '">Username: <input type="text" name="post_username" id="username-' . $dynamic_id . '" value="' . $username . '" /></label></p>';
		} else {
			// Build username field.
			$username       = ( isset( $_COOKIE[ 'envira_password_protection_email_' . $id ] ) ? htmlspecialchars( sanitize_text_field( wp_unslash( $_COOKIE[ 'envira_password_protection_email_' . $id ] ) ) ) : '' );
			$username_field = '<p><label for="username-' . $id . '">Username: <input type="text" name="post_username" id="username-' . $id . '" value="' . $username . '" /></label></p>';
		}

		if ( false === $this->is_dynamic ) {

			// Depend on whether we are on a Gallery or Album, read appropriate config.
			switch ( $post->post_type ) {
				/**
				* Gallery
				*/
				case 'envira':
					$data = envira_get_gallery( $id );
					break;

				/**
				* Album
				*/
				case 'envira_album':
					$instance = Envira_Albums_Shortcode::get_instance();
					$data     = Envira_Albums::get_instance()->get_album( $id );
					break;

				/**
				* Non-Envira - bail
				*/
				default:
					return $output;

			}
		}

		// Insert the username field, if an email address is specified
		// Also add the Post ID as a hidden form field.
		if ( false === $this->is_dynamic ) {
			$email = envira_get_config( 'password_protection_email', $data );
		} else {
			$email = $this->dynamic_email;
		}
		if ( isset( $email ) && ! empty( $email ) ) {
			$output = str_replace( "</p>\n", $username_field, $output );
			if ( true === $this->is_dynamic ) {
				$output = str_replace( '</form>', '<input type="hidden" name="post_ID" value="' . $dynamic_id . '" /></form>', $output );
			} else {
				$output = str_replace( '</form>', '<input type="hidden" name="post_ID" value="' . $id . '" /></form>', $output );
			}
		}

		return $output;

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Pagination_Shortcode object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Password_Protection_Shortcode ) ) {
			self::$instance = new Envira_Password_Protection_Shortcode();
		}

		return self::$instance;

	}

}

// Load the shortcode class.
$envira_password_protection_shortcode = Envira_Password_Protection_Shortcode::get_instance();
