<?php
/**
 * Metabox class.
 *
 * @since 1.0.8
 *
 * @package Envira_Slideshow
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Metabox class.
 *
 * @since 1.0.8
 *
 * @package Envira_Slideshow
 * @author  Envira Team
 */
class Envira_Slideshow_Metaboxes {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.8
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.0.8
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.8
	 */
	public function __construct() {

		$this->base = Envira_Slideshow::get_instance();

		add_action( 'envira_gallery_metabox_scripts', array( $this, 'metabox_scripts' ) );
		add_action( 'envira_albums_metabox_scripts', array( $this, 'metabox_scripts' ) );

		// Gallery.
		add_filter( 'envira_gallery_tab_nav', array( $this, 'register_tabs' ) );
		add_action( 'envira_gallery_tab_slideshow', array( $this, 'slideshow_tab' ) );
		add_filter( 'envira_gallery_save_settings', array( $this, 'gallery_save' ), 10, 2 );

		// Album.
		add_filter( 'envira_albums_tab_nav', array( $this, 'register_tabs' ) );
		add_action( 'envira_albums_tab_slideshow', array( $this, 'slideshow_tab' ) );
		add_filter( 'envira_albums_save_settings', array( $this, 'album_save' ), 10, 2 );

	}

	/**
	 * Initializes scripts for the metabox admin.
	 *
	 * @since 1.0.0
	 */
	public function metabox_scripts() {
		// Conditional Fields.
		wp_register_script( $this->base->plugin_slug . '-conditional-fields-script', plugins_url( 'assets/js/min/conditional-fields-min.js', $this->base->file ), array( 'jquery', Envira_Gallery::get_instance()->plugin_slug . '-conditional-fields-script' ), $this->base->version, true );
		wp_enqueue_script( $this->base->plugin_slug . '-conditional-fields-script' );
	}

	/**
	 * Filters in a new tab for the addon.
	 *
	 * @since 1.0.8
	 *
	 * @param array $tabs  Array of default tab values.
	 * @return array $tabs Amended array of default tab values.
	 */
	public function register_tabs( $tabs ) {

		$tabs['slideshow'] = __( 'Slideshow', 'envira-slideshow' );
		return $tabs;

	}

	/**
	 * Callback for displaying the UI for setting gallery slideshow options.
	 *
	 * @since 1.0.8
	 *
	 * @param object $post The current post object.
	 */
	public function slideshow_tab( $post ) {

		// Get post type so we load the correct metabox instance and define the input field names
		// Input field names vary depending on whether we are editing a Gallery or Album.
		$post_type = get_post_type( $post );
		switch ( $post_type ) {

			/**
			* Gallery
			*/
			case 'envira':
				$instance = Envira_Gallery_Metaboxes::get_instance();
				$key      = '_envira_gallery';
				$type     = 'Gallery';
				break;

			/**
			* Album
			*/
			case 'envira_album':
				$instance = Envira_Albums_Metaboxes::get_instance();
				$key      = '_eg_album_data[config]';
				$type     = 'Album';
				break;
		}

		wp_nonce_field( 'envira_slideshow_save_settings', 'envira_slideshow_nonce' );

		?>
		<div id="envira-slideshow">
			<p class="envira-intro">
				<?php esc_html_e( 'Slideshow Lightbox Settings', 'envira-slideshow' ); ?>
				<small>
					<?php esc_html_e( 'The settings below adjust the Slideshow options for the Lightbox output.', 'envira-slideshow' ); ?>
					<br />
					<?php esc_html_e( 'Need some help?', 'envira-slideshow' ); ?>
					<a href="http://enviragallery.com/docs/slideshow-addon/" class="envira-doc" target="_blank">
						<?php esc_html_e( 'Read the Documentation', 'envira-slideshow' ); ?>
					</a>
					or
					<a href="https://www.youtube.com/embed/BnVFQP2_kac/?rel=0" class="envira-video" target="_blank">
						<?php esc_html_e( 'Watch a Video', 'envira-slideshow' ); ?>
					</a>

				</small>
			</p>
			<table class="form-table">
				<tbody>
					<tr id="envira-config-slideshow-box">
						<th scope="row">
							<label for="envira-config-slideshow">
								<?php /* translators: %s: term name */ ?>
								<?php $text = sprintf( __( 'Enable %s Slideshow?', 'envira-slideshow' ), esc_html( $type ) ); ?>
								<?php echo esc_html( $text ); ?>
							</label>
						</th>
						<td>
							<input id="envira-config-slideshow" type="checkbox" name="<?php echo esc_attr( $key ); ?>[slideshow]" value="<?php echo esc_attr( $instance->get_config( 'slideshow', $instance->get_config_default( 'slideshow' ) ) ); ?>" <?php checked( $instance->get_config( 'slideshow', $instance->get_config_default( 'slideshow' ) ), 1 ); ?> />
							<span class="description">
							<?php /* translators: %s: term name */ ?>
							<?php $text = sprintf( __( 'Enables or disables the %s lightbox slideshow?', 'envira-slideshow' ), esc_html( $type ) ); ?>
							<?php echo esc_html( $text ); ?>
							</span>
						</td>
					</tr>
					<tr id="envira-config-slideshow-autoplay-box">
						<th scope="row">
							<label for="envira-config-slideshow-autoplay"><?php esc_html_e( 'Autoplay the Slideshow?', 'envira-slideshow' ); ?></label>
						</th>
						<td>
							<input id="envira-config-slideshow-autoplay" type="checkbox" name="<?php echo esc_attr( $key ); ?>[autoplay]" value="<?php echo esc_attr( $instance->get_config( 'autoplay', $instance->get_config_default( 'autoplay' ) ) ); ?>" <?php checked( $instance->get_config( 'autoplay', $instance->get_config_default( 'autoplay' ) ), 1 ); ?> />
							<span class="description"><?php esc_html_e( 'Enables or disables autoplaying the slideshow on lightbox open.', 'envira-slideshow' ); ?></span>
						</td>
					</tr>
					<tr id="envira-config-slideshow-hover-box">
						<th scope="row">
							<label for="envira-config-slideshow-hover"><?php esc_html_e( 'Pause the Slideshow on Hover?', 'envira-slideshow' ); ?></label>
						</th>
						<td>
							<input id="envira-config-slideshow-hover" type="checkbox" name="<?php echo esc_attr( $key ); ?>[slideshow_hover]" value="<?php echo esc_attr( $instance->get_config( 'slideshow_hover', $instance->get_config_default( 'slideshow_hover' ) ) ); ?>" <?php checked( $instance->get_config( 'slideshow_hover', $instance->get_config_default( 'slideshow_hover' ) ), 1 ); ?> />
							<span class="description"><?php esc_html_e( 'Enables or disables pausing the slideshow on hover.', 'envira-slideshow' ); ?></span>
						</td>
					</tr>

					<tr id="envira-config-slideshow-speed-box">
						<th scope="row">
							<label for="envira-config-slideshow-speed"><?php esc_html_e( 'Slideshow Speed', 'envira-slideshow' ); ?></label>
						</th>
						<td>
							<input id="envira-config-slideshow-speed" type="number" name="<?php echo esc_attr( $key ); ?>[ss_speed]" value="<?php echo esc_attr( $instance->get_config( 'ss_speed', $instance->get_config_default( 'ss_speed' ) ) ); ?>" />
							<p class="description"><?php esc_html_e( 'Sets the speed of the gallery lightbox slideshow.', 'envira-slideshow' ); ?></p>
						</td>
					</tr>
					<?php do_action( 'envira_gallery_slideshow_box', $post ); ?>
				</tbody>
			</table>
		</div>
		<?php

	}

	/**
	 * Saves the addon setting.
	 *
	 * @since 1.0.8
	 *
	 * @param array $settings  Array of settings to be saved.
	 * @param int   $post_id     The current post ID.
	 * @return array $settings Amended array of settings to be saved.
	 */
	public function gallery_save( $settings, $post_id ) {

		if (
			! isset( $_POST['_envira_gallery'], $_POST['envira_slideshow_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['envira_slideshow_nonce'] ), 'envira_slideshow_save_settings' )
		) {
			return $settings;
		}

		$settings['config']['slideshow']       = isset( $_POST['_envira_gallery']['slideshow'] ) ? 1 : 0;
		$settings['config']['autoplay']        = isset( $_POST['_envira_gallery']['autoplay'] ) ? 1 : 0;
		$settings['config']['slideshow_hover'] = isset( $_POST['_envira_gallery']['slideshow_hover'] ) ? 1 : 0;

		$settings['config']['ss_speed'] = isset( $_POST['_envira_gallery']['ss_speed'] ) ? absint( $_POST['_envira_gallery']['ss_speed'] ) : false;
		return $settings;

	}

	/**
	 * Saves the addon setting for Albms
	 *
	 * @since 1.0.8
	 *
	 * @param array $settings  Array of settings to be saved.
	 * @param int   $post_id     The current post ID.
	 * @return array $settings Amended array of settings to be saved.
	 */
	public function album_save( $settings, $post_id ) {

		if (
			! isset( $_POST['_eg_album_data']['config'], $_POST['envira_slideshow_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['envira_slideshow_nonce'] ), 'envira_slideshow_save_settings' )
		) {
			return $settings;
		}

		$settings['config']['slideshow']       = isset( $_POST['_eg_album_data']['config']['slideshow'] ) ? 1 : 0;
		$settings['config']['autoplay']        = isset( $_POST['_eg_album_data']['config']['autoplay'] ) ? 1 : 0;
		$settings['config']['slideshow_hover'] = isset( $_POST['_eg_album_data']['config']['slideshow_hover'] ) ? 1 : 0;
		$settings['config']['ss_speed']        = isset( $_POST['_eg_album_data']['config']['ss_speed'] ) ? absint( $_POST['_eg_album_data']['config']['ss_speed'] ) : false;
		return $settings;

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.8
	 *
	 * @return object The Envira_Slideshow_Metaboxes object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Slideshow_Metaboxes ) ) {
			self::$instance = new Envira_Slideshow_Metaboxes();
		}

		return self::$instance;

	}

}

// Load the metabox class.
$envira_slideshow_metabox = Envira_Slideshow_Metaboxes::get_instance();
