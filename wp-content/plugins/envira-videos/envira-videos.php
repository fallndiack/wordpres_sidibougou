<?php
/**
 * Plugin Name: Envira Gallery - Videos Addon
 * Plugin URI:  http://enviragallery.com
 * Description: Displays Instagram, Facebook, Twitch, Vimeo, Wistia, Youtube or self-hosted videos in Lightbox views for Envira galleries
 * Author:      Envira Gallery Team
 * Author URI:  http://enviragallery.com
 * Version:     1.5.9
 * Text Domain: envira-videos
 * Domain Path: languages
 *
 * @package Envira Gallery
 * @subpackage Envira Videos
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

use Envira\Videos\Frontend\Frontend_Container;
use Envira\Videos\Admin\Admin_Container;

use Envira\Utils\Updater;

if ( ! class_exists( 'Envira_Videos' ) ) :

	/**
	 * Main plugin class.
	 *
	 * @since 1.0.0
	 *
	 * @package Envira_Videos
	 * @author  Envira Team
	 */
	class Envira_Videos {

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
		public $version = '1.5.9';

		/**
		 * The name of the plugin.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public $plugin_name = 'Envira Videos';

		/**
		 * Unique plugin slug identifier.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public $plugin_slug = 'envira-videos';

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

			// Fire a hook before the class is setup.
			do_action( 'envira_videos_pre_init' );

			// Load the plugin textdomain.
			add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

			// Load the plugin.
			add_action( 'envira_gallery_init', array( $this, 'init' ), 99 );

			// Load the updater.
			add_action( 'envira_gallery_updater', array( $this, 'updater' ), 10, 1 );

		}

		/**
		 * Setup Constants function.
		 *
		 * @since 1.6.0
		 *
		 * @access public
		 * @return void
		 */
		public function setup_constants() {

			if ( ! defined( 'ENVIRA_VIDEOS_VERSION' ) ) {

				define( 'ENVIRA_VIDEOS_VERSION', $this->version );

			}

			if ( ! defined( 'ENVIRA_VIDEOS_SLUG' ) ) {

				define( 'ENVIRA_VIDEOS_SLUG', $this->plugin_slug );

			}

			if ( ! defined( 'ENVIRA_VIDEOS_FILE' ) ) {

				define( 'ENVIRA_VIDEOS_FILE', $this->file );

			}

			if ( ! defined( 'ENVIRA_VIDEOS_DIR' ) ) {

				define( 'ENVIRA_VIDEOS_DIR', plugin_dir_path( __FILE__ ) );

			}

			if ( ! defined( 'ENVIRA_VIDEOS_URL' ) ) {

				define( 'ENVIRA_VIDEOS_URL', plugin_dir_url( __FILE__ ) );

			}

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

			// Run hook once Envira has been initialized.
			do_action( 'envira_videos_init' );

			// Load admin only components.
			if ( is_admin() ) {
				$admin = new \Envira\Videos\Admin\Admin_Container();
			}

			$frontend = new \Envira\Videos\Frontend\Frontend_Container();

		}

		/**
		 * Loads all admin related files into scope.
		 *
		 * @since 1.0.0
		 */
		public function require_admin() {

			require plugin_dir_path( __FILE__ ) . 'includes/admin/ajax.php';
			require plugin_dir_path( __FILE__ ) . 'includes/admin/media-view.php';
			require plugin_dir_path( __FILE__ ) . 'includes/admin/metaboxes.php';
			require plugin_dir_path( __FILE__ ) . 'includes/admin/vimeo.php';

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
		 * Loads legacy
		 *
		 * @since 1.0.0
		 */
		public function load_legacy() {

			require_once trailingslashit( ENVIRA_VIDEOS_DIR ) . 'src/Legacy/envira-videos-common.php';
			require_once trailingslashit( ENVIRA_VIDEOS_DIR ) . 'src/Legacy/envira-videos-metaboxes.php';
			require_once trailingslashit( ENVIRA_VIDEOS_DIR ) . 'src/Legacy/envira-videos-shortcode.php';

		}

		/**
		 * Loads all global files into scope.
		 *
		 * @since 1.0.0
		 */
		public function require_global() {

			require_once trailingslashit( ENVIRA_VIDEOS_DIR ) . 'src/Functions/common.php';

		}

		/**
		 * Autoload function.
		 *
		 * @access public
		 * @static
		 * @param mixed $class The class.
		 * @return void
		 */
		public static function autoload( $class ) {

			// Prepare variables.
			$prefix   = 'Envira\\Videos\\';
			$base_dir = __DIR__ . '/src/';
			$length   = mb_strlen( $prefix );

			// If the class is not using the namespace prefix, return.
			if ( 0 !== strncmp( $prefix, $class, $length ) ) {
				return;
			}

			// Prepare classes to be autoloaded.
			$relative_class = mb_substr( $class, $length );
			$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

			// If the file exists, load it.
			if ( file_exists( $file ) ) {
				require $file;
			}

		}

		/**
		 * Returns the singleton instance of the class.
		 *
		 * @since 1.4.1
		 *
		 * @return object The Envira_Videos object.
		 */
		public static function get_instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Videos ) ) {
				self::$instance = new self();
				self::$instance->setup_constants();
				self::$instance->require_global();
				self::$instance->load_legacy();
			}

			return self::$instance;

		}

	}

	spl_autoload_register( 'Envira_Videos::autoload' );

	add_action( 'envira_gallery_init', 'envira_videos_init' );

	/**
	 * Init function.
	 */
	function envira_videos_init() {
		return Envira_Videos::get_instance();
	}

endif;
