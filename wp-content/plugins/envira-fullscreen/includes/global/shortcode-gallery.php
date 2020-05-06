<?php
/**
 * Shortcode class.
 *
 * @since 1.0.0
 *
 * @package Envira_Featured_Content
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gallery Shortcode class.
 *
 * @since 1.0.4
 *
 * @package Envira_Fullscreen
 * @author  Envira Team
 */
class Envira_Fullscreen_Shortcode_Gallery {

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

		// Load the base class object.
		$this->base = Envira_Fullscreen::get_instance();

		add_filter( 'envira_gallery_toolbar_after_next', array( $this, 'toolbar_button' ), 10, 2 );
		add_filter( 'envirabox_actions', array( $this, 'base_template_button' ), 5, 2 );
		add_filter( 'envira_always_show_title', array( $this, 'envira_always_show_title' ), 10, 2 );

	}

	/**
	 * Outputs the fullscreen button in the gallery toolbar.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template  The template HTML for the gallery toolbar.
	 * @param array  $data       Data for the Envira gallery.
	 * @return string $template Amended template HTML for the gallery toolbar.
	 */
	public function toolbar_button( $template, $data ) {

		if ( ! envira_get_config( 'fullscreen', $data ) ) {
			return $template;
		}

		// Create the fullscreen button.
		$button = '<li><a data-envirabox-fullscreen class="btnFullscreen" title="' . __( 'Toggle Fullscreen', 'envira-fullscreen' ) . '" href="#"></a></li>';

		// Return with the button appended to the template.
		return $template . $button;

	}

	/**
	 * Outputs the fullscreen button in the gallery toolbar.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template  The template HTML for the gallery toolbar.
	 * @param array  $data       Data for the Envira gallery.
	 * @return string $template Amended template HTML for the gallery toolbar.
	 */
	public function envirabox_actions( $template, $data ) {

		$template .= '<li><a data-envirabox-fullscreen class="btnFullscreen" title="' . __( 'Toggle Fullscreen', 'envira-fullscreen' ) . '" href="#"></a></li>';

		return $template;

	}

	/**
	 * Outputs the title.
	 *
	 * @since 1.0.0
	 *
	 * @param string $show  Template HTML.
	 * @param array  $data       Data for the Envira gallery.
	 * @return string $template Amended template HTML for the gallery toolbar.
	 */
	public function envira_always_show_title( $show, $data ) {

		if ( ! Envira_Gallery_Shortcode::get_instance()->get_config( 'fullscreen', $data ) || ( ! in_array( Envira_Gallery_Shortcode::get_instance()->get_config( 'lightbox_theme', $data ), array( 'base_dark', 'base_light' ), true ) ) ) {
			return $show;
		}

		return true;
	}

	/**
	 * Outputs the fullscreen button in the gallery toolbar.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template  The template HTML for the gallery toolbar.
	 * @param array  $data       Data for the Envira gallery.
	 * @return string $template Amended template HTML for the gallery toolbar.
	 */
	public function base_template_button( $template, $data ) {

		if ( ! Envira_Gallery_Shortcode::get_instance()->get_config( 'fullscreen', $data ) ) {
			return $template;
		}

		// Create the fullscreen button.
		$button = '<div class="envira-fullscreen-button"><a data-envirabox-fullscreen class="btnFullscreen" title="' . __( 'Toggle Fullscreen', 'envira-fullscreen' ) . '" href="#"></a></div>';

		// Return with the button appended to the template.
		return $template . $button;

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Fullscreen_Shortcode_Gallery object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Fullscreen_Shortcode_Gallery ) ) {
			self::$instance = new Envira_Fullscreen_Shortcode_Gallery();
		}

		return self::$instance;

	}

}

// Load the shortcode class.
$envira_fullscreen_shortcode_gallery = Envira_Fullscreen_Shortcode_Gallery::get_instance();
