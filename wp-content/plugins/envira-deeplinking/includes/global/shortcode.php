<?php
/**
 * Shortcode class.
 *
 * @since 1.0.0
 *
 * @package Envira_Gallery
 * @subpackage Envira Deeplinking
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode Class.
 *
 * @since 1.0.0
 */
class Envira_Deeplinking_Shortcode {

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
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $shortcode;

	/**
	 * Default slug name.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $slug_name = 'enviragallery';

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Load the base class object.
		$this->base = Envira_Deeplinking::get_instance();

		// Make sure the shortcode class exists.
		if ( ! class_exists( 'Envira_Gallery_Shortcode' ) ) {
			return;
		}
		$this->shortcode = Envira_Gallery_Shortcode::get_instance();

		$version = ( defined( 'ENVIRA_DEBUG' ) && 'true' === ENVIRA_DEBUG ) ? $version = time() . '-' . $this->base->version : $this->base->version;

		// Register script.
		wp_register_script( $this->base->plugin_slug . '-script', plugins_url( 'assets/js/min/envira-deeplinking-min.js', $this->base->file ), array( 'jquery' ), $version, false );

		// Potential Whitelabeling.
		if ( apply_filters( 'envira_whitelabel', false ) ) {
			$this->slug_name = apply_filters( 'envira_whitelabel_envira_deeplinking_slug', $this->slug_name );
		}

		// Actions.
		add_action( 'envira_gallery_before_output', array( $this, 'enqueue_script' ) );
		add_action( 'envira_link_before_output', array( $this, 'enqueue_script' ) );
		add_action( 'envira_gallery_api_end_global', array( $this, 'init' ) );

	}

	/**
	 * Enqueue scripts if Deeplinking is enabled on a gallery
	 *
	 * @since 1.0.5
	 * @param array $data Data.
	 */
	public function enqueue_script( $data ) {

		// Bail if deeplinking not enabled.
		if ( ! $this->shortcode->get_config( 'deeplinking', $data ) ) {
			return;
		}

		// this shouldn't be loaded in wp-admin.
		if ( is_admin() ) {
			return;
		}

		// Enqueue script.
		wp_enqueue_script( $this->base->plugin_slug . '-script' );
		wp_localize_script(
			$this->base->plugin_slug . '-script',
			'envira_gallery_deeplinking',
			array(
				'slug' => $this->slug_name,
			)
		);

	}

	/**
	 * Checks if any of the galleries have Deeplinking enabled
	 *
	 * If so, initialises deeplinking once.
	 *
	 * @since 1.0.5
	 * @param array $galleries Galleries.
	 */
	public function init( $galleries ) {

		// Iterate through galleries.
		foreach ( $galleries as $data ) {
			// Bail if deeplinking not enabled.
			if ( $this->shortcode->get_config( 'deeplinking', $data ) ) {
				// Init once and quit.
				?>
				envira_deeplinking();
				<?php
				break;
			}
		}

	}

	/**
	 * Removes a hash from the location bar.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Data for the Envira gallery.
	 * @return null       Return early if deeplinking is not enabled.
	 */
	public function remove_location_hash( $data ) {

		if ( ! $this->shortcode->get_config( 'deeplinking', $data ) ) {
			return;
		}

		global $post, $wp;

		// if we're on a page then return the query string.
		$paged = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
		if ( $paged > 1 ) {
			$current_url = home_url( add_query_arg( array(), $wp->request ) );
		} else {
			// get the permalink.
			$current_url = get_permalink( $post->ID );
		}

		?>

		if ('pushState' in history) {
			history.replaceState(null, null, '<?php echo esc_url( $current_url ); ?>');
			/* history.pushState( '', document.title, window.location.pathname ); */
		}

		<?php

	}

	/**
	 * Change back browser link.
	 *
	 * @since 1.1.2
	 *
	 * @param array $data Data for the Envira gallery.
	 * @return null       Return early if deeplinking is not enabled.
	 */
	public function change_back_browser_link( $data ) {

		if ( ! $this->shortcode->get_config( 'deeplinking', $data ) ) {
			return;
		}
		?>
		history.pushState({page: 'envira_referrer'}, "", envira_referrer_url);
		history.pushState({page: 'envira_current'}, "", envira_current_url);

		window.onpopstate = function(e) {
			if (location.href.indexOf(envira_current_url) >= 0) {
				return; /* this probably means user has clicked on another deeplinked photo on the page */
			}
			location.href = envira_referrer_url;
		};
		<?php

	}

	/**
	 * Defines variables to be used later.
	 *
	 * @since 1.1.2
	 *
	 * @param array $data Data for the Envira gallery.
	 * @return null       Return early if deeplinking is not enabled.
	 */
	public function set_referrer( $data ) {

		if ( ! $this->shortcode->get_config( 'deeplinking', $data ) ) {
			return;
		}
		?>
		<script type="text/javascript">
		var envira_referrer_url = document.referrer;
		var envira_current_url  = window.location.href;
		</script>
		<?php

	}


	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Deeplinking_Shortcode object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Deeplinking_Shortcode ) ) {
			self::$instance = new Envira_Deeplinking_Shortcode();
		}

		return self::$instance;

	}

}

// Load the common class.
$envira_deeplinking_shortcode = Envira_Deeplinking_Shortcode::get_instance();
