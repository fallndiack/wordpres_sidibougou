<?php
/**
 * Plugin Name: Envira Gallery - Lightroom Addon
 * Plugin URI:  http://enviragallery.com
 * Description: Create Envira Galleries directly from Adobe Photoshop Lightroom
 * Author:      Envira Gallery Team
 * Author URI:  http://enviragallery.com
 * Version:     2.2.5
 * Text Domain: envira-lightroom
 * Domain Path: languages
 *
 * @package Envira Gallery
 * @subpackage Envira Lightroom
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

/**
 * Main plugin class.
 *
 * @since 1.0.0
 *
 * @package Envira_Lightroom
 * @author  Envira Team
 */
class Envira_Lightroom {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = '2.2.5';

	/**
	 * Required version for the Envira Lightroom App
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $required = '2.0.0';

	/**
	 * The name of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_name = 'Envira Lightroom';

	/**
	 * Unique plugin slug identifier.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_slug = 'envira-lightroom';

	/**
	 * Plugin file.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Load the plugin textdomain.
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

		// Load the plugin.
		add_action( 'envira_gallery_init', array( $this, 'init' ), 10 );

		// Load the updater.
		add_action( 'envira_gallery_updater', array( $this, 'updater' ), 10, 1 );

	}

	/**
	 * Loads the plugin textdomain for translation.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain( $this->plugin_slug, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	}

	/**
	 * Loads the plugin into WordPress.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		global $wp_version;

		if ( version_compare( $wp_version, '4.4.0', '<' ) ) {

			add_action( 'admin_notices', array( $this, 'wordpress_notice' ) );

		}

		// Display a notice if Pretty Permalinks aren't enabled
		// They're required by the WP REST API.
		$permalink_structure = get_option( 'permalink_structure' );
		if ( empty( $permalink_structure ) ) {

			add_action( 'admin_notices', array( $this, 'permalinks_notice' ) );
			return;

		}

		if ( ! empty( $_REQUEST['close_lightroom_notice'] ) ) { // @codingStandardsIgnoreLine - rewrite AJAX Request or Notification Lib
			update_option( 'envira_lightroom_notice', true );
		}

		$version_notice = get_option( 'envira_lightroom_notice' );

		// Load admin only components.
		if ( is_admin() ) {
			$this->require_admin();
		}

		// Load global components.
		$this->require_global();

	}

	/**
	 * WordPress Notice function.
	 *
	 * @access public
	 * @return void
	 * @since 2.0.0
	 */
	public function wordpress_notice() {

		?>
		<div class="error">
			<p>
			<?php

				echo esc_html__( 'The ', 'envira-lightroom' ) . '<strong>' . esc_html__( 'Envira Lightroom Addon', 'envira-lightroom' ) . '</strong>' . esc_html__( 'requires WordPress version 4.40 or higher.', 'envira-lightroom' );
			?>
			</p>
		</div>
		<?php
	}

	/**
	 * Outputs that Pretty Permalinks are required
	 *
	 * @since 1.0.1
	 */
	public function permalinks_notice() {

		?>
		<div class="error">
			<p>
			<?php
				echo esc_html__( 'The ', 'envira-lightroom' ) . '<strong>' . esc_html__( 'Envira Lightroom Addon', 'envira-lightroom' ) . '</strong>' . esc_html__( ' requires that Pretty Permalinks be enabled. Visit the ', 'envira-lightroom' ) . '<a href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '" target="_blank">' . esc_html__( 'Permalinks Screen', 'envira-lightroom' ) . '</a>';
				esc_html_e( ' to fix this.', 'envira-lightroom' );
			?>
			</p>
		</div>
		<?php

	}

	/**
	 * Initializes the addon updater.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The user license key.
	 */
	public function updater( $key ) {

		$args = array(
			'plugin_name' => $this->plugin_name,
			'plugin_slug' => $this->plugin_slug,
			'plugin_path' => plugin_basename( __FILE__ ),
			'plugin_url'  => trailingslashit( WP_PLUGIN_URL ) . $this->plugin_slug,
			'remote_url'  => 'https://enviragallery.com/',
			'version'     => $this->version,
			'key'         => $key,
		);

		$updater = new Updater( $args );

	}

	/**
	 * Loads all admin related files into scope.
	 *
	 * @since 1.0.0
	 */
	public function require_admin() {

		require plugin_dir_path( __FILE__ ) . 'includes/admin/settings.php';

	}

	/**
	 * Loads all global files into scope.
	 *
	 * @since 1.0.0
	 */
	public function require_global() {

		require plugin_dir_path( __FILE__ ) . 'includes/global/common.php';
		require plugin_dir_path( __FILE__ ) . 'includes/global/class-envira-lightroom-api.php';

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Lightroom object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Lightroom ) ) {
			self::$instance = new Envira_Lightroom();
		}

		return self::$instance;

	}

}

// Load the main plugin class.
$envira_lightroom = Envira_Lightroom::get_instance();
