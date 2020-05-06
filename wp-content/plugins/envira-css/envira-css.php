<?php
/**
 * Plugin Name: Envira Gallery - CSS Addon
 * Plugin URI:  http://enviragallery.com
 * Description: Enables custom CSS output for Envira galleries.
 * Author:      Envira Gallery Team
 * Author URI:  http://enviragallery.com
 * Version:     1.3.4
 * Text Domain: envira-css
 * Domain Path: languages
 *
 * @package Envira Gallery
 * @subpackage Envira CSS
 *
 * Envira Gallery is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Envira Gallery is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Envira Gallery. If not, see <http://www.gnu.org/licenses/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Envira\Utils\Updater as Updater;

// Define necessary addon constants.
define( 'ENVIRA_CUSTOM_CSS_PLUGIN_NAME', 'Envira Gallery - CSS Addon' );
define( 'ENVIRA_CUSTOM_CSS_PLUGIN_VERSION', '1.3.4' );
define( 'ENVIRA_CUSTOM_CSS_PLUGIN_SLUG', 'envira-css' );
define( 'ENVIRA_CUSTOM_CSS_PLUGIN_FILE', __FILE__ );

add_action( 'plugins_loaded', 'envira_custom_css_plugins_loaded' );

/**
 * Ensures the full Envira Gallery plugin is active before proceeding.
 *
 * @since 1.0.0
 *
 * @return null Return early if Envira Gallery is not active.
 */
function envira_custom_css_plugins_loaded() {

	// Bail if the main class does not exist.
	if ( ! class_exists( 'Envira_Gallery' ) ) {
		return;
	}

	// Fire up the addon.
	add_action( 'envira_gallery_init', 'envira_custom_css_plugin_init' );

	// Load the plugin textdomain.
	load_plugin_textdomain( ENVIRA_CUSTOM_CSS_PLUGIN_SLUG, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

}

/**
 * Loads all of the addon hooks and filters.
 *
 * @since 1.0.0
 */
function envira_custom_css_plugin_init() {

	add_filter( 'envira_gallery_defaults', 'envira_custom_css_defaults', 10, 2 );
	add_action( 'envira_gallery_misc_box', 'envira_custom_css_setting', 999 );
	add_filter( 'envira_gallery_save_settings', 'envira_custom_css_save', 10, 2 );
	add_filter( 'envira_gallery_output_start', 'envira_custom_css_output', 0, 2 );
	add_filter( 'envira_get_gallery_config', 'envira_santitize_gallery_config', 10, 2 );

	// Album Support.
	add_action( 'envira_albums_misc_box', 'envira_custom_css_setting_album', 999 );
	add_filter( 'envira_albums_save_settings', 'envira_custom_css_save_album', 10, 2 );
	add_filter( 'envira_albums_output_start', 'envira_custom_css_output_album', 0, 2 );
	add_filter( 'envira_get_album_config', 'envira_santitize_gallery_config', 10, 2 );

	// Admin.
	add_action( 'admin_enqueue_scripts', 'envira_custom_css_load_scripts' );

}

add_action( 'envira_gallery_updater', 'envira_custom_css_updater', 10, 1 );
/**
 * Initializes the addon updater.
 *
 * @since 1.0.0
 *
 * @param string $key The user license key.
 */
function envira_custom_css_updater( $key ) {

	$args = array(
		'plugin_name' => ENVIRA_CUSTOM_CSS_PLUGIN_NAME,
		'plugin_slug' => ENVIRA_CUSTOM_CSS_PLUGIN_SLUG,
		'plugin_path' => plugin_basename( __FILE__ ),
		'plugin_url'  => trailingslashit( WP_PLUGIN_URL ) . ENVIRA_CUSTOM_CSS_PLUGIN_SLUG,
		'remote_url'  => 'https://enviragallery.com/',
		'version'     => ENVIRA_CUSTOM_CSS_PLUGIN_VERSION,
		'key'         => $key,
	);

	$updater = new Updater( $args );

}

/**
 * Loads admin scripts/css
 *
 * @since 1.3.2
 */
function envira_custom_css_load_scripts() {

	// Bail if we're not on the Envira Post Type screen.
	if ( 'envira' !== get_current_screen()->post_type && 'envira_album' !== get_current_screen()->post_type ) {
		return;
	}

	$version = ( defined( 'ENVIRA_DEBUG' ) && true === ENVIRA_DEBUG ) ? $version = time() . '-' . ENVIRA_CUSTOM_CSS_PLUGIN_VERSION : ENVIRA_CUSTOM_CSS_PLUGIN_VERSION;

	// Enqueue code editor and settings for manipulating CSS.
	if ( function_exists( 'wp_enqueue_code_editor' ) ) {
		$settings = wp_enqueue_code_editor( array( 'type' => 'text/css' ) );
	}

	// Bail if user disabled CodeMirror.
	if ( false === $settings ) {
		return;
	}

	// Register CSS.
	wp_register_style( ENVIRA_CUSTOM_CSS_PLUGIN_SLUG . '-style', plugins_url( 'assets/css/envira-css.css', ENVIRA_CUSTOM_CSS_PLUGIN_FILE ), array(), $version );
	wp_enqueue_style( ENVIRA_CUSTOM_CSS_PLUGIN_SLUG . '-style' );

}

/**
 * Overides general config santitizion to preserve quotes, etc.
 *
 * @since 1.3.2
 *
 * @param string $data Santitized data.
 * @param string $original_data Unsantitized data.
 */
function envira_santitize_gallery_config( $data, $original_data ) {

	if ( ! isset( $data['config']['custom_css'] ) || ! isset( $original_data['config']['custom_css'] ) ) {
		return $data;
	}

	$data['config']['custom_css'] = $original_data['config']['custom_css'];

	return $data;

}

/**
 * Applies a default to the addon setting.
 *
 * @since 1.0.0
 *
 * @param array $defaults  Array of default config values.
 * @param int   $post_id     The current post ID.
 * @return array $defaults Amended array of default config values.
 */
function envira_custom_css_defaults( $defaults, $post_id ) {

	// Disabled by default.
	$defaults['custom_css'] = '';
	return $defaults;

}

/**
 * Adds addon setting to the Misc tab for Galleries
 *
 * @since 1.0.0
 *
 * @param object $post The current post object.
 */
function envira_custom_css_setting( $post ) {

	$post_type = get_post_type( $post );
	switch ( $post_type ) {
		/**
		* Gallery
		*/
		case 'envira':
			$instance = Envira_Gallery_Metaboxes::get_instance();
			break;

		/**
		* Album
		*/
		case 'envira_album':
			$instance = Envira_Albums_Metaboxes::get_instance();
			break;
	}

	?>
	<tr id="envira-config-custom-css-box-test">
		<th scope="row">
			<label for="envira-config-custom-css"><?php esc_html_e( 'Custom Gallery CSS', 'envira-css' ); ?></label>
		</th>
		<td>
		<?php
		if ( ! empty( $instance->get_config( 'custom_css', $instance->get_config_default( 'custom_css' ) ) ) ) {
			$inital_code = $instance->get_config( 'custom_css', $instance->get_config_default( 'custom_css' ) );
		} else {
			$inital_code = '#envira-gallery-' . intval( $post->ID ) . ' {
    /* margin-bottom: 20px; */
    /* font-size: 18px; */
    /* font-family: "roboto"; */
}'; }
		?>
			<textarea id="envira-config-custom-css-test" class="envira-css-code-editx" name="_envira_gallery[custom_css]"><?php echo esc_html( $inital_code ); ?></textarea>
			<p class="description"><?php esc_html_e( 'All custom CSS for this gallery should start with ', 'envira-css' ); ?> <code><?php echo '#envira-gallery-' . intval( $post->ID ); ?></code> <a href="http://enviragallery.com/docs/css-addon/" title="Need help?" target="_blank"><?php esc_html_e( ' Help?', 'envira-css' ); ?></a>
		</td>
	</tr>
	<?php

}

/**
 * Adds addon setting to the Misc tab for Albums
 *
 * @since 1.0.0
 *
 * @param object $post The current post object.
 */
function envira_custom_css_setting_album( $post ) {

	$data     = get_post_meta( $post->ID, '_eg_album_data', true );
	$instance = Envira_Albums_Metaboxes::get_instance();

	?>
	<tr id="envira-config-custom-css-box">
		<th scope="row">
			<label for="envira-config-custom-css"><?php esc_html_e( 'Custom Album CSS', 'envira-css' ); ?></label>
		</th>
		<td>
		<?php
		if ( ! empty( $instance->get_config( 'custom_css', $instance->get_config_default( 'custom_css' ) ) ) ) {
			$inital_code = $instance->get_config( 'custom_css', $instance->get_config_default( 'custom_css' ) );
		} else {
			$inital_code = '#envira-gallery-' . intval( $post->ID ) . ' {
    /* margin-bottom: 20px; */
    /* font-size: 18px; */
    /* font-family: "roboto"; */
}'; }
		?>
			<textarea id="envira-config-custom-css-test" class="envira-css-code-editx" name="_eg_album_data[config][custom_css]"><?php echo esc_html( $inital_code ); ?>              </textarea>
			<p class="description"><?php esc_html_e( 'All custom CSS for this gallery should start with ', 'envira-css' ); ?> <code><?php echo '#envira-gallery-' . intval( $post->ID ); ?></code> <a href="http://enviragallery.com/docs/css-addon/" title="Need help?" target="_blank"><?php esc_html_e( ' Help?', 'envira-css' ); ?></a>
		</td>
	</tr>
	<?php

}

/**
 * Saves the addon setting for Galleries
 *
 * @since 1.0.0
 *
 * @param array $settings  Array of settings to be saved.
 * @param int   $post_id      The current post ID.
 * @return array $settings Amended array of settings to be saved.
 */
function envira_custom_css_save( $settings, $post_id ) {

	global $wp_version;
	// Bail out if we fail a security check.
	if ( ! isset( $_POST['envira-gallery'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['envira-gallery'] ) ), 'envira-gallery' ) || ! isset( $_POST['_envira_gallery'] ) ) {
		return;
	}

	if ( version_compare( $wp_version, '4.7', '>=' ) ) {
		// wp_slash it twice to make sure any backslashes in the CSS remain.
		$entered_css                      = isset( $_POST['_envira_gallery']['custom_css'] ) ? str_replace( "'", '"', wp_strip_all_tags( wp_unslash( $_POST['_envira_gallery']['custom_css'] ) ) ) : '';
		$settings['config']['custom_css'] = trim( sanitize_textarea_field( $entered_css ) );
	} else {
		// wp_slash it twice to make sure any backslashes in the CSS remain.
		$settings['config']['custom_css'] = isset( $_POST['_envira_gallery']['custom_css'] ) ? trim( wp_slash( wp_slash( esc_html( wp_strip_all_tags( wp_unslash( $_POST['_envira_gallery']['custom_css'] ) ) ) ) ) ) : '';

	}

	return $settings;

}

/**
 * Saves the addon setting for Albums
 *
 * @since 1.0.0
 *
 * @param array $settings  Array of settings to be saved.
 * @param int   $post_id      The current post ID.
 * @return array $settings Amended array of settings to be saved.
 */
function envira_custom_css_save_album( $settings, $post_id ) {

	global $wp_version;

	// Bail out if we fail a security check.
	if ( ! isset( $_POST['envira-albums'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['envira-albums'] ) ), 'envira-albums' ) || ! isset( $_POST['_eg_album_data'] ) ) {
		return;
	}

	if ( version_compare( $wp_version, '4.7', '>=' ) ) {
		// wp_slash it twice to make sure any backslashes in the CSS remain.
		$settings['config']['custom_css'] = isset( $_POST['_eg_album_data']['config']['custom_css'] ) ? trim( wp_strip_all_tags( wp_unslash( $_POST['_eg_album_data']['config']['custom_css'] ) ) ) : '';
	} else {
		// wp_slash it twice to make sure any backslashes in the CSS remain.
		$settings['config']['custom_css'] = isset( $_POST['_eg_album_data']['config']['custom_css'] ) ? trim( wp_slash( wp_slash( esc_html( wp_strip_all_tags( wp_unslash( $_POST['_eg_album_data']['config']['custom_css'] ) ) ) ) ) ) : '';
	}

	return $settings;

}

/**
 * Outputs the custom CSS to the specific gallery.
 *
 * @since 1.0.0
 *
 * @param string $gallery  The HTML output for the gallery.
 * @param array  $data      Data for the Envira gallery.
 * @return string $gallery Amended gallery HTML.
 */
function envira_custom_css_output( $gallery, $data ) {

	// If there is no style, return the default gallery HTML.
	$instance = Envira_Gallery_Shortcode::get_instance();
	if ( ! $instance->get_config( 'custom_css', $data ) ) {
		return $gallery;
	}

	if ( ! empty( $data['config']['custom_css'] ) ) {

		// Build out the custom CSS.
		$style = '<style type="text/css">' . $instance->minify( html_entity_decode( $data['config']['custom_css'], ENT_QUOTES ), false ) . '</style>';

		// Add a filter just in case they want to wipe even the default CSS for now.
		$style = apply_filters( 'envira_gallery_custom_css_output', $style, $data['id'], $gallery, $data );

		// Return the style prepended to the gallery.
		return $style . $gallery;

	}

	return $gallery;

}

/**
 * Outputs the custom CSS to the specific album.
 *
 * @since 1.0.0
 *
 * @param string $gallery  The HTML output for the album.
 * @param array  $data      Data for the Envira album.
 * @return string $gallery Amended gallery HTML.
 */
function envira_custom_css_output_album( $gallery, $data ) {

	// If there is no style, return the default gallery HTML.
	$instance = Envira_Gallery_Shortcode::get_instance();
	if ( ! $instance->get_config( 'custom_css', $data ) ) {
		return $gallery;
	}

	if ( ! empty( $data['config']['custom_css'] ) ) {

		// Build out the custom CSS.
		$style = '<style type="text/css">' . $instance->minify( html_entity_decode( $data['config']['custom_css'], ENT_QUOTES ), false ) . '</style>';

		// Add a filter just in case they want to wipe even the default CSS for now.
		$style = apply_filters( 'envira_album_custom_css_output', $style, $data['id'], $gallery, $data );

		// Return the style prepended to the gallery.
		return $style . $gallery;

	}

	return $gallery;

}
