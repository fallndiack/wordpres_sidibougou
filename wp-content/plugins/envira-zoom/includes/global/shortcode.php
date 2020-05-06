<?php
/**
 * Shortcode class.
 *
 * @since 1.0.0
 *
 * @package Envira_Zoom
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
 * @package Envira_Zoom
 * @author  Envira Team
 */
class Envira_Zoom_Shortcode {

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
	 * Is Mobile
	 *
	 * @var mixed
	 * @access public
	 */
	public $is_mobile;

	/**
	 * Holds the base class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $base;

	/**
	 * Holds the gallery shortcode
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $gallery_shortcode;

	/**
	 * Holds the album shortcode
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $albums_shortcode;

	/**
	 * Holds a flag to determine whether metadata has been set
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $meta_data_set = false;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Load the base class object.
		$this->base              = Envira_Zoom::get_instance();
		$this->gallery_shortcode = Envira_Gallery_Shortcode::get_instance();
		$this->is_mobile         = envira_mobile_detect()->isMobile();

		$version = ( defined( 'ENVIRA_DEBUG' ) && ENVIRA_DEBUG === 'true' ) ? $version = time() . '-' . ENVIRA_ZOOM_VERSION : ENVIRA_ZOOM_VERSION;

		// Register CSS.
		wp_register_style( $this->base->plugin_slug . '-style', plugins_url( 'assets/css/zoom-style.css', $this->base->file ), false, $version );

		// Register JS Zoom Lib.
		wp_register_script( $this->base->plugin_slug . '-elevate', plugins_url( 'assets/js/min/jquery.elevatezoom-min.js', $this->base->file ), array( 'jquery' ), $version, true );

		// Register JS.
		wp_register_script( $this->base->plugin_slug . '-script', plugins_url( 'assets/js/min/envira-zoom-min.js', $this->base->file ), array( 'jquery', $this->base->plugin_slug . '-elevate' ), $version, true );

		// Admin Hooks.
		add_action( 'admin_enqueue_scripts', array( $this, 'envira_admin_enqueue_scripts' ), 100 );

		// Galleries.
		add_action( 'envira_gallery_before_output', array( $this, 'gallery_output_css_js' ) );
		add_action( 'envira_gallery_before_output', array( $this, 'gallery_enqueue_elevatezoom_helpers' ) );

		// Envira Link.
		add_action( 'envira_link_before_output', array( $this, 'gallery_output_css_js' ) );
		add_action( 'envira_link_before_output', array( $this, 'gallery_enqueue_elevatezoom_helpers' ) );

		// Lightbox.
		add_filter( 'envira_gallery_toolbar_after_next', array( $this, 'toolbar_button' ), 10, 2 );
		add_filter( 'envirabox_actions', array( $this, 'envirabox_actions_button' ), 10, 2 );

		// Albums.
		add_action( 'envira_albums_before_output', array( $this, 'gallery_output_css_js' ) );
		add_action( 'envira_albums_before_output', array( $this, 'gallery_enqueue_elevatezoom_helpers' ) );

		add_action( 'envira_albums_api_after_close', array( $this, 'gallery_output_cleanup_html' ) );
		add_filter( 'envira_albums_toolbar_after_next', array( $this, 'toolbar_button' ), 10, 2 );
		add_action( 'envira_albums_api_after_show', array( $this, 'resume_active_zoom' ) );
		add_action( 'envira_albums_api_before_show', array( $this, 'keep_oringial_image_sizes' ), 99 );
		add_action( 'envira_albums_api_before_show', array( $this, 'add_css_class_to_wrap' ), 99 );

	}

	/**
	 * Admin Enqueue CSS and JS if Zoom is enabled
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Gallery Data.
	 */
	public function envira_admin_enqueue_scripts( $data ) {

		if ( is_admin() ) {

			$version = ( defined( 'ENVIRA_DEBUG' ) && ENVIRA_DEBUG === 'true' ) ? $version = time() . '-' . ENVIRA_ZOOM_VERSION : ENVIRA_ZOOM_VERSION;

			// Add the color picker css file.
			wp_enqueue_style( 'wp-color-picker' );

			// Include our custom jQuery file with WordPress Color Picker dependency.
			wp_enqueue_script( 'envira-zoom-colorpicker', plugins_url( 'assets/js/envira-zoom-admin.js', $this->base->file ), array( 'wp-color-picker' ), $version, true );
		}

	}


	/**
	 * Add CSS class to envirabox-wrap for better targeting of lightbox elements over zoom <div>
	 *
	 * @since 1.0.3
	 *
	 * @param array $data Gallery Data.
	 */
	public function add_css_class_to_wrap( $data ) {

		$instance = $this->get_type( $data );

		// If there's no instance, bail or risk fatal error.
		if ( ! isset( $instance ) || ( ! $instance instanceof Envira_Gallery_Shortcode && ! $instance instanceof Envira_Albums_Shortcode ) ) {
			return;
		}

		// Check if zoom functionality is enabled.
		if ( ! $instance->get_config( 'zoom', $data ) ) {
			return;
		}

		// Check if zoom is disabled on mobile
		// This overrides the 'mobile_zoom' parameter in the zoom script itself.
		$this->is_mobile = envira_mobile_detect()->isMobile();
		if ( $instance->get_config( 'mobile_zoom', $data ) && $this->is_mobile ) {
			return;
		}

		$gallery_theme = $instance->get_config( 'gallery_theme', $data );

		if ( $instance->get_config( 'zoom', $data ) ) : ?>

		$('.envirabox-wrap').addClass('envira-zoom');

			<?php

		endif;

	}

	/**
	 * This grabs the oringial width/height of the wrap and actual image <divs> before they are adjusted.
	 * This only needs to happen if the supersize addon is activated. Otherwise this should not happen.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Gallery Data.
	 */
	public function keep_oringial_image_sizes( $data ) {

		$instance = $this->get_type( $data );

		// If there's no instance, bail or risk fatal error.
		if ( ! isset( $instance ) || ( ! $instance instanceof Envira_Gallery_Shortcode && ! $instance instanceof Envira_Albums_Shortcode ) ) {
			return;
		}

		// Check if zoom functionality is enabled.
		if ( ! $instance->get_config( 'zoom', $data ) ) {
			return;
		}

		// Check if zoom is disabled on mobile
		// This overrides the 'mobile_zoom' parameter in the zoom script itself.
		$this->is_mobile = envira_mobile_detect()->isMobile();
		if ( $instance->get_config( 'mobile_zoom', $data ) && $this->is_mobile ) {
			return;
		}

		$gallery_theme = $instance->get_config( 'gallery_theme', $data )

		?>

		_width_wrap = $(".envirabox-wrap").width();
		_height_wrap = $(".envirabox-wrap").height();

		_width_inner = $(".envirabox-inner").width();
		_height_inner = $(".envirabox-inner").height();

		_width_image = $(".envirabox-image").width();
		_height_image = $(".envirabox-image").height();

		<?php
	}

	/**
	 * Enqueue CSS and JS if Zoom is enabled
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Gallery Data.
	 */
	public function gallery_output_css_js( $data ) {

		$instance = $this->get_type( $data );

		// If there's no instance, bail or risk fatal error.
		if ( ! isset( $instance ) || ( ! $instance instanceof Envira_Gallery_Shortcode && ! $instance instanceof Envira_Albums_Shortcode ) ) {
			return;
		}

		// Check if zoom functionality is enabled.
		if ( ! $instance->get_config( 'zoom', $data ) ) {
			return;
		}

		// Check if zoom is disabled on mobile
		// This overrides the 'mobile_zoom' parameter in the zoom script itself.
		$this->is_mobile = envira_mobile_detect()->isMobile();
		if ( $instance->get_config( 'mobile_zoom', $data ) && $this->is_mobile ) {
			return;
		}

		$gallery_theme = $instance->get_config( 'gallery_theme', $data );

		// Enqueue CSS + JS.
		wp_enqueue_style( $this->base->plugin_slug . '-style' );
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( $this->base->plugin_slug . '-elevate' );
		wp_enqueue_script( $this->base->plugin_slug . '-script' );

	}

	/**
	 * Resume Zoom.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Gallery Data.
	 */
	public function resume_active_zoom( $data ) {

		$instance = $this->get_type( $data );

		// If there's no instance, bail or risk fatal error.
		if ( ! isset( $instance ) || ( ! $instance instanceof Envira_Gallery_Shortcode && ! $instance instanceof Envira_Albums_Shortcode ) ) {
			return;
		}

		// Check if zoom functionality is enabled.
		if ( ! $instance->get_config( 'zoom', $data ) ) {
			return;
		}

		// Check if zoom is disabled on mobile
		// This overrides the 'mobile_zoom' parameter in the zoom script itself.
		$this->is_mobile = envira_mobile_detect()->isMobile();
		if ( $instance->get_config( 'mobile_zoom', $data ) && $this->is_mobile ) {
			return;
		}

		$gallery_theme = $instance->get_config( 'gallery_theme', $data );

		?>

	/* legacy check */

		function envira_kill_zoom() {
			if ( jQuery('.zoomContainer').length > 0 ) {
				/* kill the elevateZoom instance */
				var img = jQuery('.envirabox-image');
				jQuery('.zoomContainer').remove();
				/* img.removeData('elevateZoom'); */
				img.removeData('zoomImage');
				jQuery(this).removeClass('btnZoomOn').addClass('btnZoomOff').parent().removeClass('zoom-on');
			}
		}

		function envira_restore_zoom() {
			if ( jQuery('.zoomContainer').length == 0 ) {
				jQuery(this).removeClass('btnZoomOff').addClass('btnZoomOn').parent().addClass('zoom-on');
				envira_setup_zoom_vars();
				jQuery('.zoomContainer').show();
				envirabox_zoom_init();
			}
		}

	if ( $('.envirabox-overlay').hasClass('overlay-captioned') || $('.envirabox-overlay').hasClass('overlay-polaroid') || $('.envirabox-overlay').hasClass('overlay-sleek') || $('.envirabox-overlay').hasClass('overlay-base') || $('.envirabox-overlay').hasClass('overlay-subtle') || $('.envirabox-overlay').hasClass('overlay-base') || $('.envirabox-overlay').hasClass('overlay-showcase') ) {


		$('.envirabox-nav').css('z-index', 1);
		$('.envirabox-nav').css('pointer-events', 'none');
		var currentMousePos = { x: -1, y: -1 };
		$('.envirabox-outer').mousemove(function(e) {

			if ( $( '#btnZoom' ).hasClass('btnZoomOff') ) { return; } /* if zoom is turned off in the toolbar, don't bother */


			var parentOffset = $(this).parent().offset();
			/* or $(this).offset(); if you really just want the current element's offset */
			var relX = e.pageX - parentOffset.left;
			var relY = e.pageY - parentOffset.top;
			var previousArrowTop = parseInt ( $('.envirabox-prev span').css('top') );
			var previousArrowLeft = 0 ; /* parseInt ( $('.envirabox-prev span').css('left') ); */
			var nextArrowTop = parseInt ( $('.envirabox-next span').css('top') );
			var nextArrowRight = parseInt ( $('.envirabox-next span').css('right') );
			var outerWidth = $('.envirabox-outer').width();
			if ( relY >= ( nextArrowTop - 80 ) && ( relY <= nextArrowTop + 80 ) ) {
				if ( relX >= ( previousArrowLeft ) && ( relX <= previousArrowLeft + 80 ) ) {
					$('.envirabox-nav').css('z-index', 1);
					$('.envirabox-prev span').css('visibility','visible');
					envira_kill_zoom();
				} else if ( relX >= ( outerWidth - nextArrowRight - 80 ) && ( relX <= outerWidth ) ) {
					$('.envirabox-nav').css('z-index', 1);
					$('.envirabox-next span').css('visibility','visible');
					envira_kill_zoom();
				} else {
					$('.envirabox-nav').css('z-index', -1);
					$('.envirabox-next span').css('visibility','hidden');
					$('.envirabox-prev span').css('visibility','hidden');
					envira_restore_zoom();
				}

			} else {
				$('.envirabox-nav').css('z-index', -1);
				$('.envirabox-next span').css('visibility','hidden');
				$('.envirabox-prev span').css('visibility','hidden');
				envira_restore_zoom();
			}

		});


		} else { /* end legacy check - now should be light/dark themes */

			if ( $( '#btnZoom' ).hasClass('btnZoomOff') ) { return; } /* if zoom is turned off in the toolbar, don't bother */

			$('.envirabox-nav').css('z-index', 1);
			$('.envirabox-nav').css('pointer-events', 'none');

			var currentMousePos = { x: -1, y: -1 };

			$('.envirabox-outer').mousemove(function(e) {

				var arrow_width = $('.envirabox-nav span').width();
				var arrow_height = $('.envirabox-nav span').width();
				$('.envirabox-nav').css('width', arrow_width);



				var parentOffset = $(this).parent().offset();
				/* or $(this).offset(); if you really just want the current element's offset */
				var outerWidth = $('.envirabox-outer').width();
				var relX = e.pageX - parentOffset.left;
				var relY = e.pageY - parentOffset.top;
				var previousArrowTop = parseInt ( $('.envirabox-prev span').css('top') );
				ar previousArrowLeft = 0 ; /* parseInt ( $('.envirabox-prev span').css('left') ); */
				var nextArrowTop = parseInt ( $('.envirabox-next span').css('top') );
				var nextArrowRight = parseInt ( outerWidth - $('.envirabox-next span').width() );


				if ( relY >= ( nextArrowTop - arrow_height ) && ( relY <= nextArrowTop + arrow_height ) ) {

					if ( relX >= ( previousArrowLeft ) && ( relX <= previousArrowLeft + arrow_width ) ) {

						envira_kill_zoom();

					} else if ( relX >= ( outerWidth - arrow_width ) && ( relX <= outerWidth ) ) {

						envira_kill_zoom();

					} else {

						envira_restore_zoom();

					}

				} else {

					envira_restore_zoom();

				}





			});

		}


		/* resize wrap element */

		var width = $(".envirabox-wrap").width();
		var height = $(".envirabox-wrap").height();

		var img = jQuery('.envirabox-image');
		jQuery('.zoomContainer').remove();
		img.removeData('elevateZoom');
		img.removeData('zoomImage');

		/* init variables*/
		envira_setup_zoom_vars();

		if ( mobile_zoom == 'true' ) {

			if ( zoom_click ) {
				/* the zoom button exists, so we should check and see if this is 'on' before init the gallery*/

			if ( jQuery('#btnZoom').hasClass('btnZoomOn') ) {
					/* if button is on, init the gallery (most likely user clicked zoom on previous photo showing)*/
					envirabox_zoom_init();
			}
			} else {
			/* if the button does not exist, then it must be a zoom on hover, so init the gallery*/
			envirabox_zoom_init();
			}

		}


		<?php
	}

	/**
	 * This turns off zoom if the user has left it on and moves to another photo
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Gallery Data.
	 * @return JS
	 */
	public function kill_active_zooms( $data ) {

		$instance = $this->get_type( $data );

		// If there's no instance, bail or risk fatal error.
		if ( ! isset( $instance ) || ( ! $instance instanceof Envira_Gallery_Shortcode && ! $instance instanceof Envira_Albums_Shortcode ) ) {
			return;
		}

		// Check if zoom functionality is enabled.
		if ( ! $instance->get_config( 'zoom', $data ) ) {
			return;
		}

		// Check if zoom is disabled on mobile
		// This overrides the 'mobile_zoom' parameter in the zoom script itself.
		$this->is_mobile = envira_mobile_detect()->isMobile();
		if ( $instance->get_config( 'mobile_zoom', $data ) && $this->is_mobile ) {
			return;
		}

		$gallery_theme = $instance->get_config( 'gallery_theme', $data );

		?>
			var img = jQuery('.envirabox-image');
			jQuery('.zoomContainer').remove();
			img.removeData('elevateZoom');
			img.removeData('zoomImage');
			jQuery('#btnZoom').removeClass('btnZoomOn').addClass('btnZoomOff').parent().removeClass('zoom-on');

		<?php

	}


	/**
	 * Adds the Zoom Button In The Envirabox-actions Div
	 *
	 * @since 1.0.0
	 *
	 * @param string $template Template HTML.
	 * @param array  $data Gallery Data.
	 * @return string Template HTML
	 */
	public function envirabox_actions_button( $template, $data ) {

		$instance = $this->get_type( $data );

		// If there's no instance, bail or risk fatal error.
		if ( ! isset( $instance ) || ( ! $instance instanceof Envira_Gallery_Shortcode && ! $instance instanceof Envira_Albums_Shortcode ) ) {
			return $template;
		}

		// Check if zoom functionality is enabled.
		if ( ! $instance->get_config( 'zoom', $data ) ) {
			return $template;
		}

		// Check if zoom is disabled on mobile
		// This overrides the 'mobile_zoom' parameter in the zoom script itself.
		$this->is_mobile = envira_mobile_detect()->isMobile();
		if ( $instance->get_config( 'mobile_zoom', $data ) && $this->is_mobile ) {
			return;
		}

		$gallery_theme = $instance->get_config( 'gallery_theme', $data );
		$config_option = $instance->get_config( 'zoom', $data );

		// We should only output this JS if the Zoom functionality is activate.
		if ( ( ! in_array( $instance->get_config( 'lightbox_theme', $data, true ), array( 'base_light', 'base_dark', 'space_dark', 'space_light', 'box_dark', 'box_light', 'burnt_dark', 'burnt_light', 'modern-dark', 'modern-light' ), true ) ) ) {
			return $template;
		}

		$settings = $data['config'];
		$button   = false;

		// Determine if hover or click setting is set.
		if ( empty( $settings['zoom_hover'] ) ) : // This setting is set for 'click'.
			$button = '<div class="envira-zoom-button"><a id="btnZoom" class="btnZoom btnZoomOff dashicons dashicons-search" title="' . __( 'Zoom', 'envira-zoom' ) . '" href="javascript:;"></a></div>';
		endif;

		// Return with the button appended to the template.
		if ( $button ) {
			return $template . $button;
		} else {
			return $template;
		}

	}

	/**
	 * Adds the Zoom Button In The Toolbar
	 *
	 * @since 1.0.0
	 *
	 * @param string $template Template HTML.
	 * @param array  $data Gallery Data.
	 * @return string Template HTML
	 */
	public function toolbar_button( $template, $data ) {

		$instance = $this->get_type( $data );

		// If there's no instance, bail or risk fatal error.
		if ( ! isset( $instance ) || ( ! $instance instanceof Envira_Gallery_Shortcode && ! $instance instanceof Envira_Albums_Shortcode ) ) {
			return $template;
		}

		// Check if zoom functionality is enabled.
		if ( ! $instance->get_config( 'zoom', $data ) ) {
			return $template;
		}

		// Check if zoom is disabled on mobile
		// This overrides the 'mobile_zoom' parameter in the zoom script itself.
		$this->is_mobile = envira_mobile_detect()->isMobile();
		if ( $instance->get_config( 'mobile_zoom', $data ) && $this->is_mobile ) {
			return;
		}

		$gallery_theme = $instance->get_config( 'gallery_theme', $data );

		$settings = $data['config'];
		$button   = false;

		// Determine if hover or click setting is set.
		if ( empty( $settings['zoom_hover'] ) || 1 !== $settings['zoom_hover'] ) : // This setting is set for 'click'.
			$button = '<li><a id="btnZoom" class="btnZoom btnZoomOff dashicons dashicons-search" title="' . __( 'Zoom', 'envira-zoom' ) . '" href="javascript:;"></a></li>';
		endif;

		// Return with the button appended to the template.
		if ( $button ) {
			return $template . $button;
		} else {
			return $template;
		}

	}

	/**
	 * Gallery: Outputs JavaScript That "Cleans Up" Zoom JavaScript After LightBox Closes
	 * Note: This Requires WordPress 4.5 due to wp_add_inline_script()
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Gallery Data.
	 * @return JS
	 */
	public function gallery_enqueue_elevatezoom_helpers( $data ) {

		$settings = $data['config'];

		if ( ! isset( $settings['zoom'] ) || empty( $settings['zoom'] ) ) {
			return;
		}

		/* there was a return here */

		/* Determine the size of the preview window */

		if ( isset( $settings['zoom_window_size'] ) ) :

			switch ( $settings['zoom_window_size'] ) {
				case 'small': // or bottom right.
					$zoom_window_size = 100;
					break;
				case 'large': // or upper left.
					$zoom_window_size = 300;
					break;
				case 'x-large':
					$zoom_window_size = 350;
					break;
				default: // default is medium.
					$zoom_window_size = 200;
					break;
			}

			else :

				$zoom_window_size = 200; // default.

		endif;

			/* Determine the Position of the Zoom Preview Window */

			if ( isset( $settings['zoom_position'] ) ) :

				switch ( $settings['zoom_position'] ) {
					case 'lower-right': // or bottom right.
						$zoom_window_position = 4;
						$zoom_window_offset_x = -abs( $zoom_window_size );
						$zoom_window_offset_y = -abs( $zoom_window_size );
						break;
					case 'upper-left': // or upper left.
						$zoom_window_position = 11;
						$zoom_window_offset_x = $zoom_window_size;
						$zoom_window_offset_y = 0;
						break;
					case 'lower-left':
						$zoom_window_position = 9;
						$zoom_window_offset_x = $zoom_window_size;
						$zoom_window_offset_y = 0;
						break;
					default: // default is above or upper right.
						$zoom_window_position = 1;
						$zoom_window_offset_x = -abs( $zoom_window_size );
						$zoom_window_offset_y = 0;
						break;
				}

				else :

					// defaults if value doesn't exist.
					$zoom_window_position = 1;
					$zoom_window_offset_x = -abs( $zoom_window_size );
					$zoom_window_offset_y = 0;

		endif;

				if ( ! empty( $settings['mobile_zoom'] ) && 1 === $settings['mobile_zoom'] ) : // Disable On Mobile.
					$mobile_zoom_js = 'mobile_zoom = false;';
					$mobile_zoom    = false;
		else :
			$mobile_zoom_js = '';
			$mobile_zoom    = true;
		endif;

		if ( empty( $settings['zoom_hover'] ) || 0 === $settings['zoom_hover'] ) :
			$zoom_hover = false;
		else :
			$zoom_hover = true;
		endif;

		$script = '

                  var _width_wrap = 0;
                  var _height_wrap = 0;
                  var _width_inner = 0;
                  var _height_inner = 0;
                  var _width_image = 0;
                  var _height_image = 0;

                  var zoom_window_height      = ' . $zoom_window_size . ';
                  var zoom_window_width       = ' . $zoom_window_size . ';
                  var zoom_window_offset_x    = ' . $zoom_window_offset_x . ';
                  var zoom_window_offset_y    = ' . $zoom_window_offset_y . ';
                  var zoom_window_position    = ' . $zoom_window_position . ';
                  var zoom_lens_size          = 200;
                  var mobile_zoom             = ' . $mobile_zoom . ';
                  var zoom_click              = ' . $zoom_hover . ';

                  function envira_setup_zoom_vars() {

                    /* Let\'s Check Again, IE related */

                    if ( zoom_window_height == undefined )    { zoom_window_height      = ' . $zoom_window_size . '; }
                    if ( zoom_window_width == undefined )     { zoom_window_width       = ' . $zoom_window_size . '; }
                    if ( zoom_window_offset_x == undefined )  { zoom_window_offset_x    = ' . $zoom_window_offset_x . '; }
                    if ( zoom_window_offset_y == undefined )  { zoom_window_offset_y    = ' . $zoom_window_offset_y . '; }
                    if ( zoom_window_position == undefined )  { zoom_window_position    = ' . $zoom_window_position . '; }
                    if ( zoom_lens_size == undefined )        { zoom_lens_size   = 200 }
                    if ( mobile_zoom == undefined )           { mobile_zoom      = ' . $mobile_zoom . '; }
                    if ( zoom_click == undefined )            { zoom_click       = ' . $zoom_hover . '; }

                    var browser_width = jQuery(window).width();
                    var offset_percent = 1;
                    var max_width = 9999;

                    switch (true) {
                        case ( browser_width < 400 ):
                            offset_percent = .50;
                            max_width = 100;
                            zoom_lens_size = 5;
                            x_offset_offset = 2;
                            y_offset_offset = -2;
                            ' . $mobile_zoom_js . '
                            break;
                        case ( browser_width > 399 && browser_width < 768):
                            offset_percent = .70;
                            max_width = 200;
                            zoom_lens_size = 100;
                            x_offset_offset = 2;
                            y_offset_offset = -2;
                            ' . $mobile_zoom_js . '
                            break;
                        case ( browser_width > 767 && browser_width < 1024):
                            offset_percent = .90;
                            max_width = 300;
                            x_offset_offset = 2;
                            y_offset_offset = -2;
                            mobile_zoom = \'true\';
                            break;
                        case ( browser_width > 1023 && browser_width < 1200):
                            offset_percent = .90;
                            max_width = 300;
                            x_offset_offset = 2;
                            y_offset_offset = -2;
                            mobile_zoom = \'true\';
                            break;
                        default:
                            offset_percent = 1;
                            x_offset_offset = 2;
                            y_offset_offset = -2;
                            mobile_zoom = \'true\';
                            break;
                    }

                    /* x_offset_offset is a "hack" to resolve a one-pixel shift seen at a narrow range of browser sizes in Chrome */

                    zoom_window_height      = ' . $zoom_window_size . ' * offset_percent;
                    zoom_window_width       = ' . $zoom_window_size . ' * offset_percent;
                    zoom_window_offset_x    = (' . $zoom_window_offset_x . ' * offset_percent);
                    zoom_window_offset_y    = (' . $zoom_window_offset_y . ' * offset_percent);

                    /* Ensure Max Is Not Exceeded */

                    if ( zoom_window_height > max_width )   { zoom_window_height = max_width; }
                    if ( zoom_window_width > max_width )    { zoom_window_width = max_width; }
                    if ( zoom_window_offset_x > max_width ) { zoom_window_offset_x = max_width; }
                    if ( zoom_window_offset_y > max_width ) { zoom_window_offset_y = max_width; }

                  }

                  envira_setup_zoom_vars();


        ';

		/* Hover or Click? */

		if ( empty( $settings['zoom_hover'] ) ) :
			$zoom_hover = 'click';
		else :
			$zoom_hover = 'hover';
		endif;

		/* Determine the Zoom Type */

		if ( isset( $settings['zoom_type'] ) ) {

			switch ( $settings['zoom_type'] ) {
				case 'basic':
					$zoom_type = 'window';
					break;
				case 'mousewheel':
					$zoom_type = 'window';
					break;
				default:
					$zoom_type = sanitize_text_field( $settings['zoom_type'] );
					break;
			}
		} else {

			$zoom_type = 'window';

		}

		/* Tint? */

		if ( isset( $zoom_type ) && 'window' === $zoom_type && ! empty( $settings['zoom_tint_color'] ) ) :
			$tint               = true;
			$tint_color         = sanitize_key( $settings['zoom_tint_color'] );
			$tint_color_opacity = $settings['zoom_tint_color_opacity'] * 0.01;
		else :
			$tint               = false;
			$tint_color         = false;
			$tint_color_opacity = 0;
		endif;

		/* Determine the Lens Shape */

		if ( isset( $settings['zoom_type'] ) ) {

			switch ( $settings['zoom_lens_shape'] ) {
				case 'square': // or bottom right.
					$zoom_lens_shape = 'square';
					break;
				default: // default is circle.
					$zoom_lens_shape = 'round';
					break;
			}
		} else {

			$zoom_lens_shape = 'round';

		}

		/* Mousewheel? */

		if ( empty( $settings['zoom_mousewheel'] ) || 1 !== $settings['zoom_mousewheel'] ) :
			$zoom_mousewheel = false;
		else :
			$zoom_mousewheel = true;
		endif;

		/* Lens */

		if ( isset( $settings['zoom_lens_shape'] ) ) {

			switch ( $settings['zoom_lens_shape'] ) {
				case 'square': // or bottom right.
					$zoom_lens_shape = 'square';
					break;
				default: // default is circle.
					$zoom_lens_shape = 'round';
					break;
			}
		}

		if ( isset( $zoom_mousewheel ) && true === $zoom_mousewheel ) {

			$scroll_zoom = '1';

		} else {

			$scroll_zoom = '';

		}

		$lens_fade_in    = 0;
		$lens_fade_out   = 0;
		$easing          = '';
		$easing_duration = 2000;

		if ( isset( $settings['zoom_effect'] ) && 'easing' === $settings['zoom_effect'] ) {
			$easing = '1';
		} else {
			$easing = '0';
		}
		if ( isset( $settings['zoom_effect'] ) && 'fade-in' === $settings['zoom_effect'] ) {
			$lens_fade_in = 1000;
			$lens_fade_in = 10;
		}
		if ( isset( $settings['zoom_effect'] ) && 'fade-out' === $settings['zoom_effect'] ) {
			$lens_fade_in = 10;
			$lens_fade_in = 1000;
		}

		$settings_array = array(
			'_width_wrap'          => 0,
			'_height_wrap'         => 0,
			'_width_inner'         => 0,
			'_height_inner'        => 0,
			'_width_image'         => 0,
			'_height_image'        => 0,
			'zoom_window_height'   => $zoom_window_size,
			'zoom_window_width'    => $zoom_window_size,
			'zoom_window_offset_x' => $zoom_window_offset_x,
			'zoom_window_offset_y' => $zoom_window_offset_y,
			'zoom_window_position' => $zoom_window_position,
			'mobile_zoom'          => $mobile_zoom,
			// old setting 'zoom_click' => $zoom_click.
			'zoom_hover'           => $zoom_hover,
			'mobile_zoom_js'       => $mobile_zoom_js,
			'zoom_type'            => $zoom_type,
			'tint'                 => $tint,
			'tint_color'           => $tint_color,
			'tint_color_opacity'   => $tint_color_opacity,
			'zoom_lens_shape'      => $zoom_lens_shape,
			'zoom_mousewheel'      => $zoom_mousewheel,
			'zoomType'             => $zoom_type,
			'zoom_lens_shape'      => $zoom_lens_shape,
			'scroll_zoom'          => $scroll_zoom,
			'easing'               => $easing,
			'easingDuration'       => $easing_duration,
			'lensFadeOut'          => $lens_fade_out,
			'lensFadeIn'           => $lens_fade_in,
		);

		wp_localize_script( $this->base->plugin_slug . '-script', 'envira_zoom_settings', $settings_array );

	}

	/**
	 * Gallery: Outputs JavaScript That "Cleans Up" Zoom JavaScript After LightBox Closes
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Gallery Data.
	 * @return JS
	 */
	public function gallery_output_cleanup_html( $data ) {

		$instance = $this->get_type( $data );

		// If there's no instance, bail or risk fatal error.
		if ( ! isset( $instance ) || ( ! $instance instanceof Envira_Gallery_Shortcode && ! $instance instanceof Envira_Albums_Shortcode ) ) {
			return;
		}

		// Check if zoom functionality is enabled.
		if ( ! $instance->get_config( 'zoom', $data ) ) {
			return;
		}

		// Check if zoom is disabled on mobile
		// This overrides the 'mobile_zoom' parameter in the zoom script itself.
		$this->is_mobile = envira_mobile_detect()->isMobile();
		if ( $instance->get_config( 'mobile_zoom', $data ) && $this->is_mobile ) {
			return;
		}

		$gallery_theme = $instance->get_config( 'gallery_theme', $data );

		// This will effectively turn off the ElevateZoom (there is no "destroy" with this JS lib).
		?>
		var img = jQuery('.envirabox-image');
		jQuery('.zoomContainer').remove();
		img.removeData('elevateZoom');
		img.removeData('zoomImage');

		/*Re-create*/
		img.elevateZoom();
		<?php
	}

	/**
	 * Helper method for retrieving gallery config values.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The config key to retrieve.
	 * @param array  $data The gallery data to use for retrieval.
	 * @return string     Key value on success, default if not set.
	 */
	public function get_config( $key, $data ) {

		$instance = $this->get_type( $data );

		// If there's no instance, bail or risk fatal error.
		if ( ! isset( $instance ) || ( ! $instance instanceof Envira_Gallery_Shortcode && ! $instance instanceof Envira_Albums_Shortcode ) ) {
			return;
		}

		// Return value.
		return $instance->get_config( $key, $data );

	}

	/**
	 * Helper method for retrieving instances
	 *
	 * @since 1.0.0
	 *
	 * @param  array $data The gallery data to use for retrieval.
	 * @return string     Key value on success, default if not set.
	 */
	public function get_type( $data ) {

		$post_type = false;

		// Determine whether data is for a gallery or album.
		if ( isset( $data['config']['type'] ) && 'dynamic' === $data['config']['type'] ) {
			if ( isset( $data['gallery'] ) ) {
				$post_type = 'envira';
			}
		} elseif ( ! empty( $data['id'] ) ) {
			$post_type = get_post_type( $data['id'] );
		}

		if ( class_exists( 'Envira_Albums_Shortcode' ) && ! $this->albums_shortcode ) {
			$this->albums_shortcode = Envira_Albums_Shortcode::get_instance();
		}

		// If post type is false, we're probably on a dynamic gallery/album
		// Grab the ID from the config.
		if ( ! $post_type && isset( $data['config']['id'] ) ) {
			$post_type = get_post_type( $data['config']['id'] );
		}

		switch ( $post_type ) {
			case 'envira':
				$instance = $this->gallery_shortcode;
				break;
			case 'envira_album':
				$instance = $this->albums_shortcode;
				break;
			case 'post':
				$instance = $this->gallery_shortcode;
				break;
			default: /* last resort */
				$instance = false;
				break;
		}

		return $instance;

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Zoom_Shortcode object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Zoom_Shortcode ) ) {
			self::$instance = new Envira_Zoom_Shortcode();
		}

		return self::$instance;

	}

}

// Load the shortcode class.
$envira_zoom_shortcode = Envira_Zoom_Shortcode::get_instance();
