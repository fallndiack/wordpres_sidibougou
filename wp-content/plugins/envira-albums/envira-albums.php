<?php
/**
 * Plugin Name: Envira Gallery - Albums Addon
 * Plugin URI:  https://enviragallery.com
 * Description: Enables album capabilities for Envira galleries.
 * Author:      Envira Gallery Team
 * Author URI:  https://enviragallery.com
 * Version:     1.7.5.2
 * Text Domain: envira-albums
 * Domain Path: languages
 *
 * @package Envira Gallery
 * @subpackage Envira Albums
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

use Envira\Albums\Frontend\Frontend_Container;
use Envira\Albums\Admin\Admin_Container;

use Envira\Utils\Updater as Updater;

// Register the installation/uninstall hooks.
register_activation_hook( __FILE__, 'Envira_Albums::activate' );

if ( ! class_exists( 'Envira_Albums' ) ) :

	/**
	 * Main plugin class.
	 *
	 * @since 1.6.0
	 *
	 * @package Envira_Albums
	 * @author  Envira Team
	 */
	class Envira_Albums {

		/**
		 * Holds the class object.
		 *
		 * @since 1.6.0
		 *
		 * @var object
		 */
		public static $instance;

		/**
		 * Plugin version, used for cache-busting of style and script file references.
		 *
		 * @since 1.6.0
		 *
		 * @var string
		 */
		public $version = '1.7.5.2';

		/**
		 * The name of the plugin.
		 *
		 * @since 1.6.0
		 *
		 * @var string
		 */
		public $plugin_name = 'Envira Albums';

		/**
		 * Unique plugin slug identifier.
		 *
		 * @since 1.6.0
		 *
		 * @var string
		 */
		public $plugin_slug = 'envira-albums';

		/**
		 * Plugin file.
		 *
		 * @since 1.6.0
		 *
		 * @var string
		 */
		public $file = __FILE__;

		/**
		 * Primary class constructor.
		 *
		 * @since 1.6.0
		 */
		public function __construct() {

			// Fire a hook before the class is setup.
			do_action( 'envira_albums_pre_init' );

			// Load the plugin textdomain.
			add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

			// Load the plugin widget.
			add_action( 'widgets_init', array( $this, 'widget' ) );

			// Load the plugin.
			add_action( 'envira_gallery_loaded', array( $this, 'init' ), 98 );
			// Load the updater.
			add_action( 'envira_gallery_updater', array( $this, 'updater' ), 10 );

		}

		/**
		 * Setup Plugin Constants.
		 *
		 * @since 1.6.0
		 *
		 * @access public
		 * @return void
		 */
		public function setup_constants() {

			if ( ! defined( 'ENVIRA_ALBUMS_VERSION' ) ) {

				define( 'ENVIRA_ALBUMS_VERSION', $this->version );

			}

			if ( ! defined( 'ENVIRA_ALBUMS_SLUG' ) ) {

				define( 'ENVIRA_ALBUMS_SLUG', $this->plugin_slug );

			}

			if ( ! defined( 'ENVIRA_ALBUMS_FILE' ) ) {

				define( 'ENVIRA_ALBUMS_FILE', $this->file );

			}

			if ( ! defined( 'ENVIRA_ALBUMS_DIR' ) ) {

				define( 'ENVIRA_ALBUMS_DIR', plugin_dir_path( __FILE__ ) );

			}

			if ( ! defined( 'ENVIRA_ALBUMS_URL' ) ) {

				define( 'ENVIRA_ALBUMS_URL', plugin_dir_url( __FILE__ ) );

			}

		}

		/**
		 * Loads the plugin textdomain for translation.
		 *
		 * @since 1.6.0
		 */
		public function load_plugin_textdomain() {

			load_plugin_textdomain( $this->plugin_slug, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		}

		/**
		 * Registers the Envira Albums widget.
		 *
		 * @since 1.6.0
		 */
		public function widget() {

		}

		/**
		 * Loads the plugin into WordPress.
		 *
		 * @since 1.6.0
		 */
		public function init() {

			// Envira Gallery 1.5.7.3 includes standalone in core, so we don't need this message unless its an older version of Envira Gallery.
			if ( version_compare( Envira_Gallery::get_instance()->version, '1.5.8', '<' ) ) {
				// Display a notice if Envira Standalone isn't enabled.
				// Don't load anything else until Standalone is enabled.
				if ( ! defined( 'ENVIRA_STANDALONE_PLUGIN_NAME' ) ) {
					add_action( 'admin_notices', array( $this, 'standalone_notice' ) );
					return;
				}
			} elseif ( ! get_option( 'envira_gallery_standalone_enabled' ) ) {
				add_action( 'admin_notices', array( $this, 'new_standalone_notice' ) );
				return;
			}

			// Check if breadcrumbs plugin is active.
			if ( is_plugin_active( 'envira-breadcrumbs/envira-breadcrumbs.php' ) ) {
				set_transient( 'envira_breadcrumbs_notice', true, 12 * HOUR_IN_SECONDS );
				deactivate_plugins( 'envira-breadcrumbs/envira-breadcrumbs.php' );
			}

			if ( ! empty( $_REQUEST['close_breadcrumbs_notice'] ) ) { // @codingStandardsIgnoreLine
				delete_transient( 'envira_breadcrumbs_notice' );
			}

			if ( get_transient( 'envira_breadcrumbs_notice' ) ) {
				add_action( 'admin_notices', array( $this, 'breadcrumbs_notice' ) );
			}

			// Check if we need to run an update routine.
			$this->maybe_run_update();

			// Run hook once Envira has been initialized.
			do_action( 'envira_albums_init' );

			// Load admin only components.
			if ( is_admin() ) {
				$admin = new \Envira\Albums\Admin\Admin_Container();
			}

			$frontend = new \Envira\Albums\Frontend\Frontend_Container();

		}

		/**
		 * Undocumented function
		 *
		 * @return void
		 */
		public function maybe_run_update() {

			$version = get_option( 'envira_albums_version' );

			if ( version_compare( $version, ENVIRA_VERSION, '<' ) ) {

				envira_flush_all_cache();

				update_option( 'envira_albums_version', ENVIRA_VERSION, 'no' );

			}
		}

		/**
		 * Outputs 'standalone addon required' notice for the addon to work.
		 *
		 * @since 1.6.0
		 */
		public function standalone_notice() {

			?>
			<div class="error">
				<?php /* translators: %s: plugin name */ ?>
				<p><?php printf( esc_html__( 'The %s requires the Envira Standalone addon. Please install and activate the Standalone Addon.', 'envira-albums' ), '<strong>' . esc_attr( $this->plugin_name ) . esc_html__( 'Addon', 'envira-albums' ) . '</strong>' ); ?></p>
			</div>
			<?php

		}

		/**
		 * Outputs 'standalone addon required' notice for the addon to work.
		 *
		 * @since 1.6.0
		 */
		public function new_standalone_notice() {

			?>
			<div class="error">
				<?php /* translators: %s: plugin name */ ?>
				<p><?php printf( esc_html__( 'The %s requires that Envira Standalone is enabled. Please enable Standalone from the Envira Settings page.', 'envira-albums' ), '<strong>' . esc_attr( $this->plugin_name ) . esc_html__( 'Addon', 'envira-albums' ) . '</strong>' ); ?></p>
			</div>
			<?php

		}

		/**
		 * Initializes the addon updater.
		 *
		 * @since 1.6.0
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
		 * Helper Method to load legacy files.
		 *
		 * @since 1.6.0
		 *
		 * @return void
		 */
		public function load_legacy() {

			require_once trailingslashit( ENVIRA_ALBUMS_DIR ) . 'src/Legacy/envira-albums-metaboxes.php';
			require_once trailingslashit( ENVIRA_ALBUMS_DIR ) . 'src/Legacy/envira-albums-shortcode.php';
			require_once trailingslashit( ENVIRA_ALBUMS_DIR ) . 'src/Legacy/envira-albums-common.php';

		}

		/**
		 * Loads all global files into scope.
		 *
		 * @since 1.6.0
		 */
		public function require_global() {

			require_once trailingslashit( ENVIRA_ALBUMS_DIR ) . 'src/Functions/albums.php';
			require_once trailingslashit( ENVIRA_ALBUMS_DIR ) . 'src/Functions/themes.php';
			require_once trailingslashit( ENVIRA_ALBUMS_DIR ) . 'src/Functions/common.php';

			if ( is_admin() ) {

				require_once trailingslashit( ENVIRA_ALBUMS_DIR ) . 'src/Functions/admin.php';

			}
		}

		/**
		 * Returns an album based on ID.
		 *
		 * @since 1.6.0
		 *
		 * @param int $id     The album ID used to retrieve an album.
		 * @return array|bool Array of album data or false if none found.
		 */
		public function get_album( $id ) {

			return envira_get_album( $id );

		}

		// Legacy Code.
		// @codingStandardsIgnoreStart
		/**
		 * Internal method that returns an album based on ID.
		 *
		 * @since 1.6.0
		 *
		 * @param int $id     The album ID used to retrieve an album.
		 * @return array|bool Array of album data or false if none found.
		 */
		public function _get_album( $id ) {

			return _envira_get_album( $id );

		}
		// @codingStandardsIgnoreEnd

		/**
		 * Returns an album based on slug.
		 *
		 * @since 1.6.0
		 *
		 * @param string $slug The album slug used to retrieve an album.
		 * @return array|bool  Array of album data or false if none found.
		 */
		public function get_album_by_slug( $slug ) {

			return envira_get_album_by_slug( $slug );

		}

		// Legacy Code.
		// @codingStandardsIgnoreStart
		/**
		 * Internal method that returns an album based on slug.
		 *
		 * @since 1.6.0
		 *
		 * @param string $slug The album slug used to retrieve an album.
		 * @return array|bool  Array of album data or false if none found.
		 */
		public function _get_album_by_slug( $slug ) {

			return _envira_get_album_by_slug( $slug );

		}
		// @codingStandardsIgnoreEnd

		/**
		 * Returns all albums created on the site.
		 *
		 * @since 1.6.0
		 *
		 * @param bool   $skip_empty   Skip empty albums.
		 * @param bool   $ignore_cache Ignore Transient cache.
		 * @param string $search_terms Search for specified Albums by Title.
		 * @return array|bool Array of album data or false if none found.
		 */
		public function get_albums( $skip_empty = true, $ignore_cache = false, $search_terms = '' ) {

			return envira_get_albums( $skip_empty, $ignore_cache, $search_terms );

		}

		// Legacy Code.
		// @codingStandardsIgnoreStart
		/**
		 * Internal method that returns all albums created on the site.
		 *
		 * @since 1.6.0
		 *
		 * @param bool   $skip_empty     Skip Empty Albums.
		 * @param string $search_terms   Search for specified Albums by Title
		 * @return mixed                    Array of albums data or false if none found.
		 */
		public function _get_albums( $skip_empty = true, $search_terms = '' ) {

			return _envira_get_albums( $skip_empty, $search_terms );

		}
		// @codingStandardsIgnoreEnd

		/**
		 * Helper Method fires when Envira Albums Activated.
		 *
		 * @access public
		 * @static
		 * @return void
		 */
		public static function activate() {

			/**
			 * We cannot get to the class here, but registering this and then flushing permalinks should be enough
			 * posttype class will re-register with args
			 */

			register_post_type( 'envira_album' );

			flush_rewrite_rules();

		}

		/**
		 * Output a nag notice if the user has breadcrumbs activated
		 *
		 * @since 1.6.5
		 */
		public function breadcrumbs_notice() {

			?>
			<div class="notice notice-error" style="position: relative;padding-right: 38px;">
				<p><?php esc_html__( '<strong>Envira Gallery:</strong> The Breadcrumbs addon was detected on your system. All features have been merged directly into the Envira Albums addon, so it is no longer necessary. It has been deactivated.', 'envira-albums' ); ?></p>
				<a href="<?php echo esc_url( add_query_arg( 'close_breadcrumbs_notice', 'true' ) ); ?>"><button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html__( 'Dismiss this notice.', 'envira-albums' ); ?></span></button></a>
			</div>
			<?php

		}

		/**
		 * Autoload function.
		 *
		 * @access public
		 * @static
		 * @param string $class Class to Autoload.
		 * @return void
		 */
		public static function autoload( $class ) {

			// Prepare variables.
			$prefix   = 'Envira\\Albums\\';
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
		 * @since 1.6.0
		 *
		 * @return object The Envira_Albums object.
		 */
		public static function get_instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Albums ) ) {
				self::$instance = new self();
				self::$instance->setup_constants();
				self::$instance->require_global();
				self::$instance->load_legacy();
			}

			return self::$instance;

		}

	}

	spl_autoload_register( 'Envira_Albums::autoload' );

	add_action( 'envira_gallery_init', 'envira_albums_init' );

	/**
	 * Helper Method to get Albums Instance.
	 *
	 * @return object|Envira_Alumbs::instance
	 */
	function envira_albums_init() {

		return Envira_Albums::get_instance();
	}

endif;
