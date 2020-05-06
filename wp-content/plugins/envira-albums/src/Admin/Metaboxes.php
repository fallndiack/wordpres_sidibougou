<?php
/**
 * Metabox class.
 *
 * @since 1.0.0
 *
 * @package Envira_Albums
 * @author  Envira Team
 */

namespace Envira\Albums\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Envira\Albums\Utils\Import;
use Envira\Albums\Utils\Export;
use Envira\Admin\Notices;

/**
 * Albums Metabox class
 */
class Metaboxes {

	/**
	 * Duplicate Post Id
	 *
	 * @var int
	 */
	public $duplicate_post_id;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Load metabox assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'fix_plugin_js_conflicts' ), 100 );

		// Load the metabox hooks and filters.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 1 );

		// Add the envira-gallery class to the form, so our styles can be applied.
		add_action( 'post_edit_form_tag', array( $this, 'add_form_class' ) );

		// Load all tabs.
		add_action( 'envira_albums_tab_galleries', array( $this, 'galleries_tab' ) );
		add_action( 'envira_albums_tab_config', array( $this, 'config_tab' ) );
		add_action( 'envira_albums_tab_lightbox', array( $this, 'lightbox_tab' ) );
		add_action( 'envira_albums_tab_thumbnails', array( $this, 'thumbnails_tab' ) );
		add_action( 'envira_albums_tab_mobile', array( $this, 'mobile_tab' ) );
		add_action( 'envira_albums_tab_standalone', array( $this, 'standalone_tab' ) );
		add_action( 'envira_albums_tab_misc', array( $this, 'misc_tab' ) );

		// Add action to save metabox config options.
		add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 2 );

		// Remove Spacing For New Album Screen.
		add_filter( 'admin_body_class', array( $this, 'envira_ablum_admin_body_class' ) );

		// Load admin assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );

		// Output success notice.
		add_action( 'admin_notices', array( $this, 'admin_notice_warnings' ) );

		// Breadcrumbs - Tab and Metabox.
		add_filter( 'envira_albums_tab_nav', array( $this, 'tabs' ) );
		add_action( 'envira_albums_tab_breadcrumbs', array( $this, 'breadcrumbs_box' ) );

		// Breadcrumbs - Save Settings.
		add_filter( 'envira_albums_save_settings', array( $this, 'save_breadcrumbs' ), 10, 2 );

		$export = new Export();
		$import = new Import();

	}

	/**
	 * Loads styles for our metaboxes.
	 *
	 * @since 1.0.0
	 *
	 * @return null Return early if not on the proper screen.
	 */
	public function styles() {

		// Get current screen.
		$screen = get_current_screen();

		// Bail if we're not on the Envira Post Type screen.
		if ( 'envira_album' !== $screen->post_type ) {
			return;
		}

		// Bail if we're not on an editing screen.
		if ( 'post' !== $screen->base ) {
			return;
		}

		// Load necessary metabox styles from Envira Gallery.
		wp_register_style( ENVIRA_SLUG . '-metabox-style', plugins_url( 'assets/css/metabox.css', ENVIRA_FILE ), array(), ENVIRA_VERSION );
		wp_enqueue_style( ENVIRA_SLUG . '-metabox-style' );
		wp_enqueue_style( 'media-views' );

		// Fire a hook to load in custom metabox styles.
		do_action( 'envira_album_metabox_styles' );

	}

	/**
	 * Adds css to admin backend
	 *
	 * @since 1.0.0
	 *
	 * @return null Return early if not on the proper screen.
	 */
	public function admin_styles() {

		// Get current screen.
		$screen = get_current_screen();

		// Bail if we're not on the Envira Post Type screen.
		if ( 'envira_album' !== $screen->post_type ) {
			return;
		}

		// Proceed loading remaining admin CSS necessary admin styles.
		wp_register_style( ENVIRA_ALBUMS_SLUG . '-albums-admin-style', plugins_url( 'assets/css/albums-admin.css', ENVIRA_ALBUMS_FILE ), array(), ENVIRA_ALBUMS_VERSION );
		wp_enqueue_style( ENVIRA_ALBUMS_SLUG . '-albums-admin-style' );

	}


	/**
	 * Adds admin warning.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function admin_notice_warnings() {

		$notices = new Notices();

		if ( isset( $_GET['envira_album_slug_exists'] ) ) { // @codingStandardsIgnoreLine

			$duplicate_post_id = intval( $_GET['envira_album_slug_exists'] ); // @codingStandardsIgnoreLine
			if ( $duplicate_post_id ) {
				$duplicate_post = get_edit_post_link( $duplicate_post_id );
				// Add a notice for the user that this changed, but saving anyway.
				/* translators: %s: url */
				$message = sprintf( __( '<strong>Envira Gallery</strong>: There was <a target="_blank" href="%s">already a post on your site</a> with the same slug. Envira generated a unique slug for this album.</a>', 'envira-gallery' ), $duplicate_post );
			} else {
				// Add a notice for the user that this changed, but saving anyway.
				$message = sprintf( __( '<strong>Envira Gallery</strong>: There was already a post on your site with the same slug. Envira generated a unique slug for this album.', 'envira-gallery' ) );
			}

			$notices->display_inline_notice( 'warning-post-slug-exists', false, $message, 'warning', false, false, true, false );

		}

	}

	/**
	 * Adds a body class when there are no published albums in the album list admin screen.
	 *
	 * @since 1.0.0
	 *
	 * @param string $body_class WP Admin body claass.
	 * @return null Return early if not on the proper screen.
	 */
	public function envira_ablum_admin_body_class( $body_class ) {

		// Get current screen.
		$screen = get_current_screen();

		// Bail if we're not on the Envira Post Type screen.
		if ( 'envira_album' !== $screen->post_type ) {
			return $body_class;
		}

		$albums = envira_get_albums();

		if ( ! $albums || empty( $albums ) ) {
			$body_class .= ' envira-no-albums';
		}

		// Fire a hook to load in custom metabox styles.
		do_action( 'envira_ablum_admin_body_class' );

		return $body_class;

	}

	/**
	 * Remove plugins scripts that break Envira's admin.
	 *
	 * @access public
	 * @return void
	 */
	public function fix_plugin_js_conflicts() {

		global $id, $post;

		// Get current screen.
		$screen = get_current_screen();

		// Bail if we're not on the Envira Post Type screen.
		if ( 'envira_album' !== $screen->post_type ) {
			return;
		}

		wp_dequeue_script( 'ngg-igw' );

	}

	/**
	 * Loads scripts for our metaboxes.
	 *
	 * @since 1.0.0
	 *
	 * @global int $id      The current post ID.
	 * @global object $post The current post object.
	 * @param string $hook  The page hook.
	 * @return null         Return early if not on the proper screen.
	 */
	public function scripts( $hook ) {

		global $id, $post;

		// Get current screen.
		$screen = get_current_screen();

		// Bail if we're not on the Envira Post Type screen.
		if ( 'envira_album' !== $screen->post_type ) {
			return;
		}

		// Bail if we're not on an editing screen.
		if ( 'post' !== $screen->base ) {
			return;
		}

		// Set the post_id for localization.
		$post_id = isset( $post->ID ) ? $post->ID : (int) $id;
		wp_enqueue_script( 'jquery' );
		// Load WordPress necessary scripts.
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );

		// Image Uploader (to get Yoast 3.x working).
		if ( $post_id > 0 ) {
			wp_enqueue_media(
				array(
					'post' => $post_id,
				)
			);
		}

		// Gallery Tabs.
		wp_register_script( ENVIRA_SLUG . '-tabs-script', plugins_url( 'assets/js/min/tabs-min.js', ENVIRA_FILE ), array( 'jquery' ), ENVIRA_VERSION, true );
		wp_enqueue_script( ENVIRA_SLUG . '-tabs-script' );

		// Gallery Clipboard.
		wp_register_script( ENVIRA_SLUG . '-clipboard-script', plugins_url( 'assets/js/min/clipboard-min.js', ENVIRA_FILE ), array( 'jquery' ), ENVIRA_VERSION, true );
		wp_enqueue_script( ENVIRA_SLUG . '-clipboard-script' );

		// Conditional Fields.
		wp_register_script( ENVIRA_SLUG . '-conditional-fields-script', plugins_url( 'assets/js/min/conditional-fields-min.js', ENVIRA_FILE ), array( 'jquery' ), ENVIRA_VERSION, true );
		wp_enqueue_script( ENVIRA_SLUG . '-conditional-fields-script' );

		// Album Metabox.
		wp_enqueue_script( ENVIRA_ALBUMS_SLUG . '-metabox-script', plugins_url( 'assets/js/min/metabox-min.js', ENVIRA_ALBUMS_FILE ), array( 'jquery' ), ENVIRA_ALBUMS_VERSION, true );
		wp_localize_script(
			ENVIRA_ALBUMS_SLUG . '-metabox-script',
			'envira_albums_metabox',
			array(
				'ajax'                     => admin_url( 'admin-ajax.php' ),
				'get_gallery_images_nonce' => wp_create_nonce( 'envira-albums-get-gallery-images' ),
				'id'                       => $post_id,
				'remove'                   => __( 'Are you sure you want to remove this gallery from the album?', 'envira-albums' ),
				'save_nonce'               => wp_create_nonce( 'envira-albums-save' ),
				'saving'                   => __( 'Saving', 'envira-albums' ),
				'search'                   => wp_create_nonce( 'envira-albums-search' ),
				'sort'                     => wp_create_nonce( 'envira-albums-sort' ),
			)
		);

		// Add custom CSS for hiding specific things.
		add_action( 'admin_head', array( $this, 'meta_box_css' ) );

		// Fire a hook to load in custom metabox scripts.
		do_action( 'envira_albums_metabox_scripts' );

	}

	/**
	 * Returns the post types to skip for loading Envira metaboxes.
	 *
	 * @since 1.0.7
	 *
	 * @return array Array of skipped posttypes.
	 */
	public function get_skipped_posttypes() {

		return apply_filters( 'envira_album_skipped_posttypes', array( 'attachment', 'revision', 'nav_menu_item', 'soliloquy', 'soliloquyv2' ) );

	}

	/**
	 * Hides unnecessary meta box items on Envira post type screens.
	 *
	 * @since 1.0.0
	 */
	public function meta_box_css() {

		?>
		<style type="text/css">body.post-type-envira_album .misc-pub-section:not(.misc-pub-post-status):not(.misc-pub-visibility) { display: none; }</style>
		<?php

		// Fire action for CSS on Envira post type screens.
		do_action( 'envira_gallery_admin_css' );

	}

	/**
	 * Creates metaboxes for handling and managing galleries.
	 *
	 * @since 1.0.0
	 */
	public function add_meta_boxes() {

		global $post;

		$data = envira_get_album( $post->ID );

		// Let's remove all of those dumb metaboxes from our post type screen to control the experience.
		$this->remove_all_the_metaboxes();
		/**
		* Add our metaboxes to Envira CPT.
		* Types Metabox
		* Allows the user to upload galleries or choose an External Album Type
		* We don't display this if the Album is a Dynamic or Default Album, as these settings don't apply
		*/
		$type = envira_albums_get_config( 'type', $data );

		if ( ! in_array( $type, array( 'defaults', 'dynamic' ), true ) ) {
			add_meta_box( 'envira-albums', __( 'Envira Albums', 'envira-albums' ), array( $this, 'meta_box_album_callback' ), 'envira_album', 'normal', 'high' );
		}

		// Settings Metabox.
		add_meta_box( 'envira-albums-settings', __( 'Envira Album Settings', 'envira-albums' ), array( $this, 'meta_box_callback' ), 'envira_album', 'normal', 'high' );

		// If the default addon is active, check to see if this the default gallery - we don't need this screen on there!
		if ( class_exists( 'Envira_Defaults' ) && isset( $post->ID ) ) {
			$default_id_album = get_option( 'envira_default_album' );
			if ( $post->ID === $default_id_album ) {
				return;
			}
		}

		// If the default addon is active, check to see if this the default gallery - we don't need this screen on there!
		if ( class_exists( 'Envira_Dynamic' ) && isset( $post->ID ) ) {
			$dynamic_id_album = get_option( 'envira_dynamic_album' );
			if ( $post->ID === $dynamic_id_album ) {
				return;
			}
		}

		// Display the Gallery Code metabox if we're editing an existing Gallery.
		if ( 'auto-draft' !== $post->post_status ) {
			add_meta_box( 'envira-albums-code', __( 'Envira Album Code', 'envira-albums' ), array( $this, 'meta_box_album_code_callback' ), 'envira_album', 'side', 'default' );
		}

	}

	/**
	 * Removes all the metaboxes except the ones I want on MY POST TYPE. RAGE.
	 *
	 * @since 1.0.0
	 *
	 * @global array $wp_meta_boxes Array of registered metaboxes.
	 * smile $for_my_buyers Happy customers with no spammy metaboxes!
	 */
	public function remove_all_the_metaboxes() {

		global $wp_meta_boxes;

		// This is the post type you want to target. Adjust it to match yours.
		$post_type = 'envira_album';

		// These are the metabox IDs you want to pass over. They don't have to match exactly. preg_match will be run on them.
		$pass_over_defaults = array( 'submitdiv', 'envira' );

		if ( envira_get_setting( 'standalone_enabled' ) ) {
			$pass_over_defaults[] = 'slugdiv';
			$pass_over_defaults[] = 'authordiv';
			$pass_over_defaults[] = 'wpseo_meta';
			$pass_over_defaults[] = 'postimagediv';
		}

		$pass_over = apply_filters( 'envira_albums_metabox_ids', $pass_over_defaults );

		// All the metabox contexts you want to check.
		$contexts = apply_filters( 'envira_albums_metabox_contexts', array( 'normal', 'advanced', 'side' ) );

		// All the priorities you want to check.
		$priorities = apply_filters( 'envira_albums_metabox_priorities', array( 'high', 'core', 'default', 'low' ) );

		// Loop through and target each context.
		foreach ( $contexts as $context ) {
			// Now loop through each priority and start the purging process.
			foreach ( $priorities as $priority ) {
				if ( isset( $wp_meta_boxes[ $post_type ][ $context ][ $priority ] ) ) {
					foreach ( (array) $wp_meta_boxes[ $post_type ][ $context ][ $priority ] as $id => $metabox_data ) {
						// If the metabox ID to pass over matches the ID given, remove it from the array and continue.
						if ( in_array( $id, $pass_over, true ) ) {
							unset( $pass_over[ $id ] );
							continue;
						}

						// Otherwise, loop through the pass_over IDs and if we have a match, continue.
						foreach ( $pass_over as $to_pass ) {
							if ( preg_match( '#^' . $id . '#i', $to_pass ) ) {
								continue;
							}
						}

						// If we reach this point, remove the metabox completely.
						unset( $wp_meta_boxes[ $post_type ][ $context ][ $priority ][ $id ] );
					}
				}
			}
		}

	}

	/**
	 * Adds an envira-gallery class to the form when adding or editing an Album,
	 * so our plugin's CSS and JS can target a specific element and its children.
	 *
	 * @since 1.3.0
	 *
	 * @param   WP_Post $post   WordPress Post.
	 */
	public function add_form_class( $post ) {

		// Check the Post is an Album.
		if ( 'envira_album' !== get_post_type( $post ) ) {
			return;
		}

		echo ' class="envira-gallery"';

	}

	/**
	 * Callback for displaying the Current Galleries section.
	 *
	 * @since 1.3.0
	 *
	 * @param object $post The current post object.
	 */
	public function meta_box_album_callback( $post ) {

		// Get all album data.
		$album_data = get_post_meta( $post->ID, '_eg_album_data', true );

		?>
		<!-- Types -->
		<div id="envira-types">
			<!-- Native Envira Album - Drag and Drop Galleries -->
			<div id="envira-album-native" class="envira-tab envira-clear<?php echo ( ( envira_albums_get_config( 'type', $album_data ) === 'default' ) ? ' envira-active' : '' ); ?>">
				<input type="hidden" name="galleryIDs" value="<?php echo esc_html( ( ( isset( $album_data['galleryIDs'] ) ? implode( ',', $album_data['galleryIDs'] ) : '' ) ) ); ?>" />

				<!-- Galleries -->
				<ul id="envira-album-drag-drop-area" class="envira-gallery-images-output">
					<?php
					// Output existing galleries.
					if ( isset( $album_data['galleryIDs'] ) ) {
						foreach ( $album_data['galleryIDs'] as $gallery_id ) {

							// Skip blank entries.
							if ( empty( $gallery_id ) ) {
								continue;
							}

							// Get the album gallery metadata.
							$item = array();
							if ( isset( $album_data['galleries'][ $gallery_id ] ) ) {
								$item = $album_data['galleries'][ $gallery_id ];
							}

							// Output the Gallery.
							$this->output_gallery_li( $gallery_id, $item, $post->ID );

						}
					}
					?>
				</ul>
				<p class="drag-drop-info<?php echo ( ( isset( $album_data['galleryIDs'] ) && count( $album_data['galleryIDs'] ) > 0 ) ? ' hidden' : '' ); ?>">
					<span class="drag"><?php esc_html_e( 'Drag and Drop Galleries Here', 'envira-albums' ); ?></span>
					<small><?php esc_html_e( 'or', 'envira-albums' ); ?></small>
					<span class="click"><?php esc_html_e( 'Select Galleries below and click the &quot;Add Selected Galleries to Album&quot; Button', 'envira-albums' ); ?></span>
				</p>
			</div>
		</div>
		<?php

	}

	/**
	 * Callback for displaying the Gallery Settings section.
	 *
	 * @since 1.0.0
	 *
	 * @param object $post The current post object.
	 */
	public function meta_box_callback( $post ) {

		// Keep security first.
		wp_nonce_field( 'envira-albums', 'envira-albums' );

		// Load view.
		envira_album_load_admin_partial(
			'metabox-album-settings',
			array(
				'post' => $post,
				'tabs' => $this->get_envira_tab_nav(),
			)
		);

	}

	/**
	 * Callback for displaying the Album Code metabox.
	 *
	 * @since 1.3.0
	 *
	 * @param object $post The current post object.
	 */
	public function meta_box_album_code_callback( $post ) {

		// Load view.
		envira_album_load_admin_partial(
			'metabox-album-code',
			array(
				'post'       => $post,
				'album_data' => get_post_meta( $post->ID, '_eg_album_data', true ),
			)
		);

	}

	/**
	 * Returns the types of albums available.
	 *
	 * @since 1.0.0
	 *
	 * @param object $post The current post object.
	 * @return array       Array of gallery types to choose.
	 */
	public function get_envira_types( $post ) {

		$types = array(
			'default' => __( 'Default', 'envira-albums' ),
		);

		return apply_filters( 'envira_albums_types', $types, $post );

	}

	/**
	 * Callback for getting all of the tabs for Envira galleries.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of tab information.
	 */
	public function get_envira_tab_nav() {

		$tabs = array(
			'galleries' => __( 'Galleries', 'envira-albums' ),
			'config'    => __( ' Configuration', 'envira-albums' ),
			'lightbox'  => __( 'Lightbox', 'envira-albums' ),
			'mobile'    => __( 'Mobile', 'envira-albums' ),
		);

		if ( envira_get_setting( 'standalone_enabled' ) ) {
			$tabs['standalone'] = __( 'Standalone', 'envira-gallery' );
		}

		$tabs = apply_filters( 'envira_albums_tab_nav', $tabs );

		// "Misc" tab is required.
		$tabs['misc'] = __( 'Misc', 'envira-albums' );

		return $tabs;

	}

	/**
	 * Callback for displaying the UI for the Available Galleries tab.
	 *
	 * @since 1.0.0
	 *
	 * @param object $post The current post object.
	 */
	public function galleries_tab( $post ) {
		$album_data = get_post_meta( $post->ID, '_eg_album_data', true );

		// Output the display based on the type of album being created.
		echo '<div id="envira-albums-main" class="envira-clear">';

		// Allow Addons to display a WordPress-style notification message.
		echo esc_html( apply_filters( 'envira_albums_galleries_tab_notice', '', $post ) );

		// Output the tab panel for the Gallery Type.
		$this->galleries_display( envira_albums_get_config( 'type', $album_data ), $post );

		echo '</div><div class="spinner"></div>';

	}

	/**
	 * Determines the Galleries tab display based on the type of album selected.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The type of display to output.
	 * @param object $post The current post object.
	 */
	public function galleries_display( $type = 'default', $post ) {

		// Output a unique hidden field for settings save testing for each type of slider.
		echo '<input type="hidden" name="_eg_album_data[type_' . esc_attr( $type ) . ']" value="1" />';

		// Output the display based on the type of slider available.
		switch ( $type ) {
			case 'default':
				$this->do_default_display( $post );
				break;
			default:
				do_action( 'envira_albums_display_' . $type, $post );
				break;
		}

	}


	/**
	 * Callback for displaying the default gallery UI.
	 *
	 * @since 1.0.9
	 *
	 * @param object $post The current post object.
	 */
	public function do_default_display( $post ) {

		// Get all album data.
		$album_data = get_post_meta( $post->ID, '_eg_album_data', true );

		// Output all other galleries not assigned to this album.
		// Build arguments.
		$arguments = array(
			'post_type'      => 'envira',
			'post_status'    => 'publish',
			'posts_per_page' => 10,
		);

		// Exclude galleries we already included in this album.
		if ( isset( $album_data['galleryIDs'] ) ) {
			$arguments['post__not_in'] = $album_data['galleryIDs'];
		}

		// Get galleries and output.
		$galleries = new \WP_Query( $arguments );
		?>
		<p class="envira-intro">
			<?php esc_html_e( 'Available Galleries', 'envira-albums' ); ?>
			<small>
				<?php if ( apply_filters( 'envira_whitelabel', false ) ) : ?>

					<?php esc_html_e( 'Displaying the most recent galleries. Please use the search box to display all matching galleries.', 'envira-albums' ); ?>

					<?php do_action( 'envira_album_whitelabel_text_available_galleries' ); ?>

				<?php else : ?>
					<?php esc_html_e( 'Displaying the most recent Envira Galleries. Please use the search box to display all matching Envira Galleries.', 'envira-albums' ); ?>
					<br/>
					<?php esc_html_e( 'Need some help?', 'envira-albums' ); ?>
					<a href="http://enviragallery.com/docs/albums-addon/" class="envira-doc" target="_blank">
						<?php esc_html_e( 'Read the Documentation', 'envira-albums' ); ?>
					</a>
					or
					<a href="https://www.youtube.com/embed/tIOdz1CY7D0/?rel=0" class="envira-video" target="_blank">
						<?php esc_html_e( 'Watch a Video', 'envira-albums' ); ?>
					</a>
				<?php endif; ?>
			</small>
		</p>

		<!-- Add Selected & Search -->
		<nav class="envira-tab-options">
			<a href="#" class="button button-primary envira-galleries-add">
				<?php esc_html_e( 'Add Selected Galleries to Album', 'envira-albums' ); ?>
			</a>

			<input type="search" name="search" value="" placeholder="<?php esc_html_e( 'Search Galleries', 'envira-albums' ); ?>" id="envira-albums-gallery-search" />
		</nav>

		<?php
		do_action( 'envira_albums_do_default_display', $post );
		?>
		<ul id="envira-albums-output" class="envira-gallery-images-output">
			<?php
			// Output Available Galleries.
			if ( count( $galleries->posts ) > 0 ) {
				foreach ( $galleries->posts as $gallery ) {

					// Get Gallery.
					$data = envira_get_gallery( $gallery->ID );

					if ( ! $data ) {
						// there is no data information - possibly a result of an auto-save without the user saving/publishing the post?
						// generate the $item with just the data from the post itself.
						$gallery_post = get_post( $gallery->ID );

						if ( trim( $gallery_post->post_title ) === '' ) {
							// if there is no title, assign "(no title)" as the title.
							$item_title = __( '(no title)', 'envira-albums' );
						} else {
							$item_title = $gallery_post->post_title;
						}

						$item = array(
							'id'      => $gallery->ID,
							'title'   => $item_title,
							'caption' => ! empty( $gallery_post->post_excerpt ) ? $gallery_post->post_excerpt : '',
						);

					} else {

						// Skip Default and Dynamic Galleries.
						if ( isset( $data['config']['type'] ) ) {
							if ( 'dynamic' === $data['config']['type'] || 'defaults' === $data['config']['type'] ) {
								continue;
							}
						}

						// Attempt To Pull Post Title Over Internal Title.
						$gallery_post = get_post( $data['id'] );
						if ( ! empty( $gallery_post->post_title ) ) {
							$title = $gallery_post->post_title;
						} else {
							$title = ! empty( $data['config']['title'] ) ? $data['config']['title'] : '(no title)';

						}

						// Build item array comprising of gallery metadata.
						$item = array(
							'id'      => $data['id'],
							'title'   => $title,
							'caption' => ! empty( $data['config']['description'] ) ? $data['config']['description'] : '',
						);

					}

					// Output <li> element.
					$this->output_gallery_li( $gallery->ID, $item, $post->ID );

				}
			}
			?>
		</ul>

		<!-- Add Selected -->
		<nav class="envira-select-options">
			<a href="#" class="button button-primary envira-galleries-add">
				<?php esc_html_e( 'Add Selected Galleries to Album', 'envira-albums' ); ?>
			</a>
		</nav>
		<?php

	}

	/**
	 * Outputs the <li> element for a gallery
	 *
	 * @param int   $gallery_id     The ID of the item to retrieve.
	 * @param array $item           The item data (i.e. album gallery metadata).
	 * @param int   $album_id       Album ID.
	 * @return void
	 */
	public function output_gallery_li( $gallery_id, $item, $album_id ) {

		// Define the required key/value pairs for the Gallery, if it's inserted into the Album.
		$defaults = array(
			'id'              => '',
			'title'           => '',
			'caption'         => '',
			'alt'             => '',
			'publish_date'    => '',
			'cover_image_id'  => '',
			'cover_image_url' => '',
		);

		// Merge the item with the defaults, so we always have a standardised array.
		$item = array_merge( $defaults, $item );

		// Add id to $item for Backbone model.
		$item['id'] = $gallery_id;

		// Get the cover image ID and URL.
		$item['cover_image_id']  = $this->get_gallery_cover_image_id( $item );
		$item['cover_image_url'] = $this->get_gallery_cover_image_url( $item );

		// Allow addons to populate the item's data - for example, tags which are stored against the attachment.
		$item        = apply_filters( 'envira_albums_get_gallery_item', $item, $gallery_id, $album_id );
		$item['alt'] = str_replace( '&quot;', '\"', $item['alt'] );

		// Get the 150x150 thumbnail.
		if ( ! empty( $item['cover_image_id'] ) && is_numeric( $item['cover_image_id'] ) ) {
			$thumbnail = wp_get_attachment_image_src( $item['cover_image_id'], 'thumbnail' );
		} else {
			$thumbnail = array( $item['cover_image_url'] );
		}

		// Establish title - and make it 'clean', such as altering quotes to prevent possible JSON/JS errors on page load.
		$item['title'] = htmlspecialchars( $item['title'], ENT_NOQUOTES );
		$gallery_model = wp_json_encode( $item, JSON_HEX_APOS );

		// Don't show dynamic galleries as choices.
		$dynamic_id = get_option( 'envira_dynamic_gallery' );
		if ( absint( $item['id'] ) === intval( $dynamic_id ) ) {
			return;
		}

		// Output.
		?>
		<li id="envira-gallery-<?php echo esc_attr( $gallery_id ); ?>" class="envira-gallery-image" data-envira-gallery="<?php echo esc_html( $gallery_id ); ?>" data-envira-album-gallery-model='<?php echo esc_html( $gallery_model ); ?>'>
			<?php
			if ( is_null( $thumbnail[0] ) ) {
				?>
				<div class="placeholder-image"></div>
				<?php
			} else {
				?>
				<img src="<?php echo esc_url( $thumbnail[0] ); ?>" title="<?php echo esc_attr( $item['title'] ); ?>" alt="<?php esc_attr( $item['title'] ); ?>" />
				<?php
			}
			?>
			<div class="meta">
				<div class="title"><?php echo esc_html( $item['title'] ); ?></div>
			</div>

			<a href="#" class="check"><div class="media-modal-icon"></div></a>
			<a href="#" class="dashicons dashicons-trash envira-gallery-remove-image" title="<?php esc_html_e( 'Remove Gallery from Album?', 'envira-albums' ); ?>"></a>
			<a href="#" class="dashicons dashicons-edit envira-gallery-modify-image" title="<?php esc_html_e( 'Modify Gallery', 'envira-albums' ); ?>"></a>
		</li>
		<?php

	}

	/**
	 * Helper method to retrieve a Gallery, and run a filter which allows
	 * Addons to populate the Gallery data if necessary - for example, the Dynamic
	 * and Instagram Addons hook into this to tell us the available images
	 * at the time of the query
	 *
	 * @since 1.2.4.3
	 *
	 * @param int $gallery_id     Gallery ID.
	 * @return array                 Gallery
	 */
	public function get_gallery_data( $gallery_id ) {

		// Get gallery data from Post Meta.
		$data = get_post_meta( $gallery_id, '_eg_gallery_data', true );

		// Allow Addons to filter the information.
		$data = apply_filters( 'envira_albums_metaboxes_get_gallery_data', $data, $gallery_id );

		// Return.
		return $data;

	}

	/**
	 * Returns the Attachment ID of the gallery data's cover image.
	 * If no cover image has been defined, returns the first available Attachment ID
	 * within the gallery
	 *
	 * @since 1.2.4.3
	 *
	 * @param array $item           Album Gallery Data.
	 * @return int                   Image ID
	 */
	public function get_gallery_cover_image_id( $item ) {

		// If the Gallery within the Album already has a cover image ID defined, return that.
		if ( isset( $item['cover_image_id'] ) && ! empty( $item['cover_image_id'] ) ) {
			return $item['cover_image_id'];
		}

		// Get Gallery.
		$gallery_data = envira_get_gallery( $item['id'] );

		// Get the first available image from the gallery, in case we need to use it.
		// as the cover image.
		if ( isset( $gallery_data['gallery'] ) && ! empty( $gallery_data['gallery'] ) ) {
			// Get the first image.
			$images = $gallery_data['gallery'];
			reset( $images );
			$key = key( $images );
		}

		// Return the first image's attachment ID.
		if ( isset( $key ) ) {
			return $key;
		}

	}

	/**
	 * Returns the image URL of the gallery data's cover image.
	 * If no cover image has been defined, returns the first available image URL
	 * within the gallery
	 *
	 * @since 1.3.0
	 *
	 * @param array $item           Album Gallery Data.
	 * @return string                Image URL
	 */
	public function get_gallery_cover_image_url( $item ) {

		// If the Gallery within the Album already has a cover image URL defined, return that.
		if ( isset( $item['cover_image_url'] ) && ! empty( $item['cover_image_url'] ) ) {
			return $item['cover_image_url'];
		}

		// Get Gallery.
		$gallery_data = envira_get_gallery( $item['id'] );

		// Allow External Galleries (Instagram, Featured Content) to inject images into the gallery array.
		// This ensures that a cover image URL can be found / chosen.
		$gallery_data['gallery'] = apply_filters( 'envira_albums_metabox_gallery_inject_images', ( isset( $gallery_data['gallery'] ) ? $gallery_data['gallery'] : array() ), $item['id'], $gallery_data );

		// Get the first available image from the gallery, as we need to use that.
		if ( isset( $gallery_data['gallery'] ) && ! empty( $gallery_data['gallery'] ) ) {
			// Get the first image.
			$images = $gallery_data['gallery'];
			reset( $images );
			$key   = key( $images );
			$image = $images[ $key ];
		}

		// Return the first image's URL.
		if ( isset( $image ) ) {
			return $image['src'];
		}

	}

	/**
	 * Callback for displaying the UI for setting album config options.
	 *
	 * @since 1.0.0
	 *
	 * @param object $post The current post object.
	 */
	public function config_tab( $post ) {

		$album_data = get_post_meta( $post->ID, '_eg_album_data', true );

		?>
		<div id="envira-config">
			<p class="envira-intro">
				<?php esc_html_e( 'Album Settings', 'envira-albums' ); ?>
				<small>
					<?php esc_html_e( 'The settings below adjust the basic configuration options for the Album.', 'envira-albums' ); ?>

					<?php if ( apply_filters( 'envira_whitelabel', false ) ) : ?>

						<?php esc_html_e( 'Displaying the most recent galleries. Please use the search box to display all matching galleries.', 'envira-albums' ); ?>

						<?php do_action( 'envira_album_whitelabel_text_settings' ); ?>

					<?php else : ?>


						<br />
						<?php esc_html_e( 'Need some help?', 'envira-albums' ); ?>
						<a href="http://enviragallery.com/docs/albums-addon/" class="envira-doc" target="_blank">
							<?php esc_html_e( 'Read the Documentation', 'envira-albums' ); ?>
						</a>
						or
						<a href="https://www.youtube.com/embed/tIOdz1CY7D0/?rel=0" class="envira-video" target="_blank">
							<?php esc_html_e( 'Watch a Video', 'envira-albums' ); ?>
						</a>

					<?php endif; ?>

				</small>
			</p>
			<?php

			// determine if this is a dynamic gallery - if so, add the type variable so that
			// it says probably along with the rest of the settings
			// get option.
			$dynamic_id = get_option( 'envira_dynamic_album' );

			if ( $dynamic_id && $dynamic_id === $post->ID ) :

				echo '<input type="hidden" name="_eg_album_data[config][type]" value="dynamic" />';

			endif;

			?>
			<table class="form-table" style="margin-bottom: 0;">
				<tbody>
					<tr id="envira-config-columns-box">
						<th scope="row">
							<label for="envira-config-columns"><?php esc_html_e( 'Number of Album Columns', 'envira-albums' ); ?></label>
						</th>
						<td>
							<select id="envira-config-columns" name="_eg_album_data[config][columns]">
								<?php foreach ( (array) envira_get_columns() as $i => $data ) : ?>
									<option value="<?php echo esc_html( $data['value'] ); ?>"<?php selected( $data['value'], envira_albums_get_config( 'columns', $album_data ) ); ?>><?php echo esc_html( $data['name'] ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'Determines the number of columns in the gallery. Automatic will attempt to fill each row as much as possible before moving on to the next row.', 'envira-albums' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>
			<?php // New Automatic Layout / Justified Layout Options. ?>
			<div id="envira-config-album-justified-settings-box">
				<table class="form-table" style="margin-bottom: 0;">
					<tbody>
						<tr id="envira-config-justified-row-height">
							<th scope="row">
								<label for="envira-config-justified-row-height"><?php esc_html_e( 'Automatic Layout: Row Height', 'envira-gallery' ); ?></label>
							</th>
							<td>
								<input id="envira-config-justified-row-height" type="number" name="_eg_album_data[config][justified_row_height]" value="<?php echo esc_html( envira_albums_get_config( 'justified_row_height', $album_data ) ); ?>" /> <span class="envira-unit"><?php esc_html_e( 'px', 'envira-gallery' ); ?></span>
								<p class="description"><?php esc_html_e( 'Determines how high (in pixels) each row will be. 150px is default. ', 'envira-gallery' ); ?></p>
							</td>
						</tr>
						<tr id="envira-config-justified-margins">
							<th scope="row">
								<label for="envira-config-justified-margins"><?php esc_html_e( 'Automatic Layout: Margins', 'envira-gallery' ); ?></label>
							</th>
							<td>
								<input id="envira-config-justified-margins" type="number" name="_eg_album_data[config][justified_margins]" value="<?php echo esc_html( envira_albums_get_config( 'justified_margins', $album_data ) ); ?>" /> <span class="envira-unit"><?php esc_html_e( 'px', 'envira-gallery' ); ?></span>
								<p class="description"><?php esc_html_e( 'Sets the space between the images (defaults to 1)', 'envira-gallery' ); ?></p>
							</td>
						</tr>
						<tr id="envira-config-gallery-justified-last-row">
							<th scope="row">
								<label for="envira-config-gallery-last-row"><?php esc_html_e( 'Automatic Layout: Last Row', 'envira-gallery' ); ?></label>
							</th>
							<td>
								<select id="envira-config-gallery-last-row" name="_eg_album_data[config][justified_last_row]">
									<?php foreach ( (array) envira_get_justified_last_row() as $i => $data ) : ?>
										<option value="<?php echo esc_html( $data['value'] ); ?>"<?php selected( $data['value'], envira_albums_get_config( 'justified_last_row', $album_data ) ); ?>><?php echo esc_html( $data['name'] ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e( 'Sets how the last row is displayed.', 'envira-gallery' ); ?></p>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div id="envira-config-description-settings-box">
				<table class="form-table no-bottom-margin">
					<tbody>

					<?php if ( ! $dynamic_id || $dynamic_id !== $post->ID ) { ?>

					<!-- Back to Album Support -->
					<tr id="envira-config-back-box">
						<th scope="row">
							<label for="envira-config-back"><?php esc_html_e( 'Display Back to Album Link?', 'envira-albums' ); ?></label>
						</th>
						<td>
							<input id="envira-config-back" type="checkbox" name="_eg_album_data[config][back]" value="1" <?php checked( envira_albums_get_config( 'back', $album_data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( 'If enabled and Lightbox is disabled, when the visitor clicks on a Gallery in this Album, they will see a link at the top of the Gallery to return back to this Album.', 'envira-albums' ); ?></span>
						</td>
					</tr>

					<?php } ?>

					<!-- Back to Album Location -->
					<tr id="envira-config-back-location">
						<th scope="row">
							<label for="envira-config-back-label"><?php esc_html_e( 'Back to Album Link Location:', 'envira-albums' ); ?></label>
						</th>
							<td>
								<select id="envira-config-album-back-location" name="_eg_album_data[config][back_location]">
									<?php foreach ( (array) envira_back_to_album_locations() as $i => $data ) : ?>
										<option value="<?php echo esc_html( $data['value'] ); ?>"<?php selected( $data['value'], envira_albums_get_config( 'back_location', $album_data ) ); ?>><?php echo esc_html( $data['name'] ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e( 'Sets where the link is displayed.', 'envira-albums' ); ?></p>
							</td>
					</tr>

					<!-- Back to Album Text -->
					<tr id="envira-config-back-label-box">
						<th scope="row">
							<label for="envira-config-back-label"><?php esc_html_e( 'Back to Album Label', 'envira-albums' ); ?></label>
						</th>
						<td>
							<input id="envira-config-back-label" type="text" name="_eg_album_data[config][back_label]" value="<?php echo esc_html( envira_albums_get_config( 'back_label', $album_data ) ); ?>" />
						</td>
					</tr>

					<?php

					if ( ! isset( $post ) || 'auto-draft' === $post->post_status ) {
						// make the lazy loading checkbox "checked", otherwise if this is a previous post don't force it.
						?>
					<tr id="envira-config-lazy-loading-box">
						<th scope="row">
							<label for="envira-config-lazy-loading"><?php esc_html_e( 'Enable Lazy Loading?', 'envira-gallery' ); ?></label>
						</th>
						<td>
							<input id="envira-config-lazy-loading" type="checkbox" name="_eg_album_data[config][lazy_loading]" value="<?php echo esc_html( envira_albums_get_config( 'lazy_loading', $album_data ) ); ?>" <?php checked( envira_albums_get_config( 'lazy_loading', $album_data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( 'Enables or disables lazy loading, which helps with performance by loading thumbnails only when they are visible. See our documentation for more information.', 'envira-gallery' ); ?></span>
						</td>
					</tr>

					<?php } else { ?>

					<tr id="envira-config-lazy-loading-box">
						<th scope="row">
							<label for="envira-config-lazy-loading"><?php esc_html_e( 'Enable Lazy Loading?', 'envira-gallery' ); ?></label>
						</th>
						<td>
							<input id="envira-config-lazy-loading" type="checkbox" name="_eg_album_data[config][lazy_loading]" value="<?php echo esc_html( envira_albums_get_config( 'lazy_loading', $album_data ) ); ?>" <?php checked( envira_albums_get_config( 'lazy_loading', $album_data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( 'Enables or disables lazy loading, which helps with performance by loading thumbnails only when they are visible. See our documentation for more information.', 'envira-gallery' ); ?></span>
						</td>
					</tr>

					<?php } ?>



					<tr id="envira-config-lazy-loading-delay">
						<th scope="row">
							<label for="envira-config-lazy-loading-delay"><?php esc_html_e( 'Lazy Loading Delay', 'envira-gallery' ); ?></label>
						</th>
							<td>
								<input id="envira-config-lazy-loading-delay" type="number" name="_eg_album_data[config][lazy_loading_delay]" value="<?php echo esc_html( envira_albums_get_config( 'lazy_loading_delay', $album_data ) ); ?>" /> <span class="envira-unit"><?php esc_html_e( 'milliseconds', 'envira-gallery' ); ?></span>
								<p class="description"><?php esc_html_e( 'Set a delay when new images are loaded', 'envira-gallery' ); ?></p>
							</td>
					</tr>
					<!-- Display Alignment -->
					<tr id="envira-config-album-alignment-box">
						<th scope="row">
							<label for="envira-config-album-alignment"><?php esc_html_e( 'Align Album?', 'envira-albums' ); ?></label>
						</th>
						<td>
							<select id="envira-config-album-alignment" name="_eg_album_data[config][album_alignment]">
								<?php foreach ( (array) $this->get_album_alignment_options() as $i => $data ) : ?>
									<option value="<?php echo esc_html( $data['value'] ); ?>"<?php selected( $data['value'], envira_albums_get_config( 'album_alignment', $album_data ) ); ?>><?php echo esc_html( $data['name'] ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'Choose an alignment for this album. This will add a CSS class to the \'envira-album-wrap\' div.', 'envira-albums' ); ?></p>
						</td>
					</tr>

					<!-- Display Width Percentage -->
					<tr id="envira-config-album-width-box">
						<th scope="row">
							<label for="envira-config-album-width"><?php esc_html_e( 'Album Width', 'envira-albums' ); ?></label>
						</th>
						<td>
							<input id="envira-config-album-width" type="number" name="_eg_album_data[config][album_width]" value="<?php echo esc_html( envira_albums_get_config( 'album_width', $album_data ) ); ?>" /> <span class="envira-unit"><?php esc_html_e( '%', 'envira-albums' ); ?></span>
							<p class="description"><?php esc_html_e( 'Overrides the default album width of 100%, especially useful if you are defining a left or right alignment for the album.', 'envira-albums' ); ?></p>
						</td>
					</tr>

					<tr id="envira-config-album-theme-box">
						<th scope="row">
							<label for="envira-config-album-theme"><?php esc_html_e( 'Album Theme', 'envira-albums' ); ?></label>
						</th>
						<td>
							<select id="envira-config-album-theme" name="_eg_album_data[config][gallery_theme]">
								<?php foreach ( (array) envira_get_gallery_themes() as $i => $data ) : ?>
									<option value="<?php echo esc_html( $data['value'] ); ?>"<?php selected( $data['value'], envira_albums_get_config( 'gallery_theme', $album_data ) ); ?>><?php echo esc_html( $data['name'] ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'Sets the theme for the gallery display.', 'envira-albums' ); ?></p>
						</td>
					</tr>

					<!-- Display Description -->
					<tr id="envira-config-display-description-box">
						<th scope="row">
							<label for="envira-config-display-description"><?php esc_html_e( 'Display Album Description?', 'envira-albums' ); ?></label>
						</th>
						<td>
							<select id="envira-config-display-description" name="_eg_album_data[config][description_position]">
								<?php foreach ( (array) envira_get_display_description_options() as $i => $data ) : ?>
									<option value="<?php echo esc_html( $data['value'] ); ?>"<?php selected( $data['value'], envira_albums_get_config( 'description_position', $album_data ) ); ?>><?php echo esc_html( $data['name'] ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'Choose to display a description above or below this album\'s galleries.', 'envira-albums' ); ?></p>
						</td>
					</tr>

					<!-- Description -->
					<tr id="envira-config-description-box">
						<th scope="row">
							<label for="envira-album-description"><?php esc_html_e( 'Album Description', 'envira-albums' ); ?></label>
						</th>
						<td>
							<?php
							$description = envira_albums_get_config( 'description', $album_data );

							wp_editor(
								$description,
								'envira-album-description',
								array(
									'media_buttons' => false,
									'wpautop'       => true,
									'tinymce'       => true,
									'textarea_name' => '_eg_album_data[config][description]',
								)
							);
							?>
							<p class="description"><?php esc_html_e( 'The description to display for this album.', 'envira-albums' ); ?></p>
						</td>
					</tr>

					</tbody>
				</table>
			</div>
			<div id="envira-config-album-standard-gallery-titles">
				<table class="form-table" style="margin-bottom: 0;">
					<tbody>
					<!-- Display Gallery Titles -->
					<tr id="envira-config-title-box">
						<th scope="row">
							<label for="envira-config-title"><?php esc_html_e( 'Display Gallery Titles?', 'envira-albums' ); ?></label>
						</th>
						<td>
							<select id="envira-config-title" name="_eg_album_data[config][display_titles]">
								<?php

								$display_title_config = envira_albums_get_config( 'display_titles', $album_data ) === 1 ? 'below' : envira_albums_get_config( 'display_titles', $album_data );

								foreach ( (array) envira_get_title_placement_options() as $i => $data ) {
									?>
									<option value="<?php echo esc_html( $data['value'] ); ?>"<?php selected( $data['value'], $display_title_config ); ?>><?php echo esc_html( $data['name'] ); ?></option>
									<?php
								}
								?>
							</select>
							<p class="description"><?php esc_html_e( 'Choose to display an image title above or below the gallery image for column layouts. For automatic layouts, the title will appear in the image caption on hover.', 'envira-albums' ); ?></p>
						</td>

					</tr>
				</table>
			</div>
			<div id="envira-config-standard-settings-box">
				<table class="form-table">

					<?php

					if ( ! $dynamic_id || $dynamic_id !== $post->ID ) :

						?>

					<!-- Display Gallery Caption -->
					<tr id="envira-config-caption-box">
						<th scope="row">
							<label for="envira-config-caption"><?php esc_html_e( 'Display Gallery Captions?', 'envira-albums' ); ?></label>
						</th>
						<td>
							<input id="envira-config-caption" type="checkbox" name="_eg_album_data[config][display_captions]" value="1" <?php checked( envira_albums_get_config( 'display_captions', $album_data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( 'Displays gallery captions. For automatic layouts, the caption will appear in the image caption on hover. For column layouts, this will be below each gallery image.', 'envira-albums' ); ?></span>
						</td>
					</tr>

					<?php endif; ?>

					<!-- Display Gallery Description -->
					<tr id="envira-config-gallery-description">
						<th scope="row">
							<label for="envira-config-gallery-description"><?php esc_html_e( 'Display Gallery Descriptions?', 'envira-gallery' ); ?></label>
						</th>
						<td>
							<select id="envira-config-gallery-description" name="_eg_album_data[config][gallery_description_display]">
								<?php foreach ( (array) envira_get_gallery_description_options() as $i => $data ) : ?>
									<option value="<?php echo esc_html( $i ); ?>"<?php selected( $i, envira_albums_get_config( 'gallery_description_display', $album_data ) ); ?>><?php echo esc_html( $data ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'Allows you to display gallery description. For automatic layouts, the description overrides the image caption.', 'envira-albums' ); ?></p>
						</td>
					</tr>

					<!-- Display Gallery Image Count -->
					<tr id="envira-config-image-count-box">
						<th scope="row">
							<label for="envira-config-image-count"><?php esc_html_e( 'Display Gallery Image Count', 'envira-albums' ); ?></label>
						</th>
						<td>
							<input id="envira-config-image-count" type="checkbox" name="_eg_album_data[config][display_image_count]" value="1" <?php checked( envira_albums_get_config( 'display_image_count', $album_data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( 'Displays the number of images in each gallery below each gallery image. For automatic layouts, the count will be appended to the image caption.', 'envira-albums' ); ?></span>
						</td>
					</tr>

					<!-- Gutter and Margin -->
					<tr id="envira-config-gutter-box">
						<th scope="row">
							<label for="envira-config-gutter"><?php esc_html_e( 'Column Gutter Width', 'envira-albums' ); ?></label>
						</th>
						<td>
							<input id="envira-config-gutter" type="number" name="_eg_album_data[config][gutter]" value="<?php echo esc_html( envira_albums_get_config( 'gutter', $album_data ) ); ?>" /> <span class="envira-unit"><?php esc_html_e( 'px', 'envira-albums' ); ?></span>
							<p class="description"><?php esc_html_e( 'Sets the space between the columns (defaults to 10).', 'envira-albums' ); ?></p>
						</td>
					</tr>
					<tr id="envira-config-margin-box">
						<th scope="row">
							<label for="envira-config-margin"><?php esc_html_e( 'Margin Below Each Image', 'envira-albums' ); ?></label>
						</th>
						<td>
							<input id="envira-config-margin" type="number" name="_eg_album_data[config][margin]" value="<?php echo esc_html( envira_albums_get_config( 'margin', $album_data ) ); ?>" /> <span class="envira-unit"><?php esc_html_e( 'px', 'envira-albums' ); ?></span>
							<p class="description"><?php esc_html_e( 'Sets the space below each item in the album.', 'envira-albums' ); ?></p>
						</td>
					</tr>

					<!-- Sorting -->
					<tr id="envira-config-sorting-box">
						<th scope="row">
							<label for="envira-config-sorting"><?php esc_html_e( 'Sorting', 'envira-albums' ); ?></label>
						</th>
						<td>
							<select id="envira-config-sorting" name="_eg_album_data[config][sorting]">
								<?php
								foreach ( (array) envira_get_sorting_options( true ) as $i => $data ) {
									?>
									<option value="<?php echo esc_html( $data['value'] ); ?>"<?php selected( $data['value'], envira_albums_get_config( 'sorting', $album_data ) ); ?>><?php echo esc_html( $data['name'] ); ?></option>
									<?php
								}
								?>
							</select>
							<p class="description"><?php esc_html_e( 'Choose the sort order for your galleries.', 'envira-albums' ); ?></p>
						</td>
					</tr>
					<tr id="envira-config-sorting-direction-box">
						<th scope="row">
							<label for="envira-config-sorting-direction"><?php esc_html_e( 'Direction', 'envira-albums' ); ?></label>
						</th>
						<td>
							<select id="envira-config-sorting-direction" name="_eg_album_data[config][sorting_direction]">
								<?php
								foreach ( (array) envira_get_sorting_directions() as $i => $data ) {
									?>
									<option value="<?php echo esc_html( $data['value'] ); ?>"<?php selected( $data['value'], envira_albums_get_config( 'sorting_direction', $album_data ) ); ?>><?php echo esc_html( $data['name'] ); ?></option>
									<?php
								}
								?>
							</select>
						</td>
					</tr>

					<!-- Image Sizes -->
					<tr id="envira-config-crop-size-box">
						<th scope="row">
							<label for="envira-config-crop-width"><?php esc_html_e( 'Image Dimensions', 'envira-albums' ); ?></label>
						</th>
						<td>
							<input id="envira-config-crop-width" type="number" name="_eg_album_data[config][crop_width]" value="<?php echo esc_html( envira_albums_get_config( 'crop_width', $album_data ) ); ?>" /> &#215; <input id="envira-config-crop-height" type="number" name="_eg_album_data[config][crop_height]" value="<?php echo esc_html( envira_albums_get_config( 'crop_height', $album_data ) ); ?>" /> <span class="envira-unit"><?php esc_html_e( 'px', 'envira-albums' ); ?></span>
							<p class="description"><?php esc_html_e( 'You should adjust these dimensions based on the number of columns in your album.', 'envira-albums' ); ?></p>
						</td>
					</tr>
					<tr id="envira-config-crop-box">
						<th scope="row">
							<label for="envira-config-crop"><?php esc_html_e( 'Crop Images?', 'envira-albums' ); ?></label>
						</th>
						<td>
							<input id="envira-config-crop" type="checkbox" name="_eg_album_data[config][crop]" value="<?php echo esc_html( envira_albums_get_config( 'crop', $album_data ) ); ?>" <?php checked( envira_albums_get_config( 'crop', $album_data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( 'If enabled, forces images to exactly match the sizes defined above for Image Dimensions.', 'envira-albums' ); ?></span>
							<span class="description"><?php esc_html_e( 'If disabled, images will be resized to maintain their aspect ratio.', 'envira-albums' ); ?></span>

						</td>
					</tr>
					<tr id="envira-config-isotope-box">	
						<th scope="row">	
							<label for="envira-config-isotope"><?php esc_html_e( 'Enable Isotope?', 'envira-albums' ); ?></label>	
						</th>	
						<td>	
							<input id="envira-config-isotope" type="checkbox" name="_eg_album_data[config][isotope]" value="<?php echo esc_html( envira_albums_get_config( 'isotope', $album_data ) ); ?>" <?php checked( envira_albums_get_config( 'isotope', $album_data ), 1 ); ?> />	
							<span class="description"><?php esc_html_e( 'Enables or disables isotope/masonry layout support for the main gallery images.', 'envira-albums' ); ?></span>	
						</td>	
					</tr>

					<?php do_action( 'envira_albums_config_box', $post ); ?>

				</tbody>
			</table>
		</div>

	</div>
		<?php

	}

	/**
	 * Callback for displaying the UI for setting gallery lightbox options.
	 *
	 * @since 1.0.0
	 *
	 * @param object $post The current post object.
	 */
	public function lightbox_tab( $post ) {

		$album_data = envira_get_album( $post->ID, true ); // flush transient as you grab settings.

		?>
		<div id="envira-lightbox">
			<p class="envira-intro">
				<?php esc_html_e( 'Lightbox Settings', 'envira-albums' ); ?>
				<small>
					<?php esc_html_e( 'The settings below adjust the lightbox output.', 'envira-albums' ); ?>

					<?php if ( apply_filters( 'envira_whitelabel', false ) ) : ?>

						<?php do_action( 'envira_album_whitelabel_lightbox_settings' ); ?>

					<?php else : ?>

						<br />
						<?php esc_html_e( 'Need some help?', 'envira-albums' ); ?>
						<a href="http://enviragallery.com/docs/albums-addon/" class="envira-doc" target="_blank">
							<?php esc_html_e( 'Read the Documentation', 'envira-albums' ); ?>
						</a>
						or
						<a href="https://www.youtube.com/embed/tIOdz1CY7D0/?rel=0" class="envira-video" target="_blank">
							<?php esc_html_e( 'Watch a Video', 'envira-albums' ); ?>
						</a>

					<?php endif; ?>



				</small>
			</p>
			<table class="form-table no-margin">
				<tbody>
					<tr id="envira-config-lightbox">
						<th scope="row">
							<label for="envira-config-lightbox"><?php esc_html_e( 'Enable Lightbox?', 'envira-albums' ); ?></label>
						</th>
						<td>
							<input id="envira-config-lightbox" type="checkbox" name="_eg_album_data[config][lightbox]" value="1"<?php checked( envira_albums_get_config( 'lightbox', $album_data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( 'If checked, displays the Gallery in a lightbox when the album cover image is clicked.', 'envira-albums' ); ?></span>
						</td>
					</tr>
				</tbody>
			</table>

			<div id="envira-lightbox-settings">
				<table class="form-table">
					<tbody>
						<tr id="envira-config-lightbox-theme">
							<th scope="row">
								<label for="envira-config-lightbox"><?php esc_html_e( 'Album Lightbox Theme', 'envira-albums' ); ?></label>
							</th>
							<td>
								<select id="envira-config-lightbox-theme" name="_eg_album_data[config][lightbox_theme]">
									<?php foreach ( (array) envira_get_lightbox_themes() as $i => $data ) : ?>
										<option value="<?php echo esc_html( $data['value'] ); ?>"<?php selected( $data['value'], envira_albums_get_config( 'lightbox_theme', $album_data ) ); ?>><?php echo esc_html( $data['name'] ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e( 'Sets the theme for the album lightbox display.', 'envira-albums' ); ?></p>
							</td>
						</tr>
						<tr id="envira-config-lightbox-additional-title-caption">
							<th scope="row">
								<label for="envira-config-title-caption"><?php esc_html_e( 'Show Title Or Caption?', 'envira-gallery' ); ?></label>
							</th>
							<td>
								<select id="envira-config-lightbox-title-caption" name="_eg_album_data[config][lightbox_title_caption]">
									<?php foreach ( (array) envira_get_additional_copy_options() as $option_value => $option_name ) : ?>
										<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $option_value, envira_albums_get_config( 'lightbox_title_caption', $album_data ) ); ?>><?php echo esc_attr( $option_name ); ?></option>
									<?php endforeach; ?>
								</select><br>
							</td>
						</tr>
						<tr id="envira-config-lightbox-title-display-box">
							<th scope="row">
								<label for="envira-config-lightbox-title-display"><?php esc_html_e( 'Caption Position', 'envira-albums' ); ?></label>
							</th>
							<td>
								<select id="envira-config-lightbox-title-display" name="_eg_album_data[config][title_display]">
									<?php foreach ( (array) envira_get_title_displays() as $i => $data ) : ?>
										<option value="<?php echo esc_html( $data['value'] ); ?>"<?php selected( $data['value'], envira_albums_get_config( 'title_display', $album_data ) ); ?>><?php echo esc_html( $data['name'] ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e( 'Sets the display of the lightbox image\'s caption.', 'envira-albums' ); ?></p>
							</td>
						</tr>
						<tr id="envira-config-lightbox-arrows-box">
							<th scope="row">
								<label for="envira-config-lightbox-arrows"><?php esc_html_e( 'Enable Gallery Arrows?', 'envira-albums' ); ?></label>
							</th>
							<td>
								<input id="envira-config-lightbox-arrows" type="checkbox" name="_eg_album_data[config][arrows]" value="1"<?php checked( envira_albums_get_config( 'arrows', $album_data ), 1 ); ?> />
								<span class="description"><?php esc_html_e( 'Enables or disables the gallery lightbox navigation arrows.', 'envira-albums' ); ?></span>
							</td>
						</tr>
						<tr id="envira-config-lightbox-arrows-position-box">
							<th scope="row">
								<label for="envira-config-lightbox-arrows-position"><?php esc_html_e( 'Gallery Arrow Position', 'envira-albums' ); ?></label>
							</th>
							<td>
								<select id="envira-config-lightbox-arrows-position" name="_eg_album_data[config][arrows_position]">
									<?php foreach ( (array) envira_get_arrows_positions() as $i => $data ) : ?>
										<option value="<?php echo esc_html( $data['value'] ); ?>"<?php selected( $data['value'], envira_albums_get_config( 'arrows_position', $album_data ) ); ?>><?php echo esc_html( $data['name'] ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e( 'Sets the position of the gallery lightbox navigation arrows.', 'envira-albums' ); ?></p>
							</td>
						</tr>
						<tr id="envira-config-lightbox-toolbar-box">
							<th scope="row">
								<label for="envira-config-lightbox-toolbar"><?php esc_html_e( 'Enable Gallery Toolbar?', 'envira-albums' ); ?></label>
							</th>
							<td>
								<input id="envira-config-lightbox-toolbar" type="checkbox" name="_eg_album_data[config][toolbar]" value="1"<?php checked( envira_albums_get_config( 'toolbar', $album_data ), 1 ); ?> />
								<span class="description"><?php esc_html_e( 'Enables or disables the gallery lightbox toolbar.', 'envira-albums' ); ?></span>
							</td>
						</tr>
						<tr id="envira-config-lightbox-toolbar-title-box">
							<th scope="row">
								<label for="envira-config-lightbox-toolbar-title"><?php esc_html_e( 'Display Title in Gallery Toolbar?', 'envira-albums' ); ?></label>
							</th>
							<td>
								<input id="envira-config-lightbox-toolbar-title" type="checkbox" name="_eg_album_data[config][toolbar_title]" value="<?php echo esc_html( envira_albums_get_config( 'toolbar_title', $album_data ) ); ?>" <?php checked( envira_albums_get_config( 'toolbar_title', $album_data ), 1 ); ?> />
								<span class="description"><?php esc_html_e( 'Display the gallery title in the lightbox toolbar.', 'envira-albums' ); ?></span>
							</td>
						</tr>
						<tr id="envira-config-lightbox-toolbar-position-box">
							<th scope="row">
								<label for="envira-config-lightbox-toolbar-position"><?php esc_html_e( 'Gallery Toolbar Position', 'envira-albums' ); ?></label>
							</th>
							<td>
								<select id="envira-config-lightbox-toolbar-position" name="_eg_album_data[config][toolbar_position]">
									<?php foreach ( (array) envira_get_toolbar_positions() as $i => $data ) : ?>
										<option value="<?php echo esc_html( $data['value'] ); ?>"<?php selected( $data['value'], envira_albums_get_config( 'toolbar_position', $album_data ) ); ?>><?php echo esc_html( $data['name'] ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e( 'Sets the position of the lightbox toolbar.', 'envira-albums' ); ?></p>
							</td>
						</tr>
						<tr id="envira-config-lightbox-aspect-box">
							<th scope="row">
								<label for="envira-config-lightbox-aspect"><?php esc_html_e( 'Keep Aspect Ratio?', 'envira-albums' ); ?></label>
							</th>
							<td>
								<input id="envira-config-lightbox-toolbar" type="checkbox" name="_eg_album_data[config][aspect]" value="<?php echo esc_html( envira_albums_get_config( 'aspect', $album_data ) ); ?>" <?php checked( envira_albums_get_config( 'aspect', $album_data ), 1 ); ?> />
								<span class="description"><?php esc_html_e( 'If enabled, images will always resize based on the original aspect ratio.', 'envira-albums' ); ?></span>
							</td>
						</tr>
						<tr id="envira-config-lightbox-loop-box">
							<th scope="row">
								<label for="envira-config-lightbox-loop"><?php esc_html_e( 'Loop Gallery Navigation?', 'envira-albums' ); ?></label>
							</th>
							<td>
								<input id="envira-config-lightbox-loop" type="checkbox" name="_eg_album_data[config][loop]" value="<?php echo esc_html( envira_albums_get_config( 'loop', $album_data ) ); ?>" <?php checked( envira_albums_get_config( 'loop', $album_data ), 1 ); ?> />
								<span class="description"><?php esc_html_e( 'Enables or disables infinite navigation cycling of the lightbox gallery.', 'envira-albums' ); ?></span>
							</td>
						</tr>

						<tr id="envira-config-lightbox-open-close-effect-box">
							<th scope="row">
								<label for="envira-config-lightbox-open-close-effect"><?php esc_html_e( 'Lightbox Open/Close Effect', 'envira-albums' ); ?></label>
							</th>
							<td>
								<select id="envira-config-lightbox-open-close-effect" name="_eg_album_data[config][lightbox_open_close_effect]">
									<?php
									// Standard Effects.
									foreach ( (array) envira_get_envirabox_open_effects() as $i => $data ) {
										?>
										<option value="<?php echo esc_html( $data['value'] ); ?>"<?php selected( $data['value'], envira_albums_get_config( 'lightbox_open_close_effect', $album_data ) ); ?>><?php echo esc_html( $data['name'] ); ?></option>
										<?php
									}
									?>
								</select>
								<p class="description"><?php esc_html_e( 'Type of transition when opening and closing the lightbox.', 'envira-albums' ); ?></p>
							</td>
						</tr>



						<tr id="envira-config-lightbox-effect-box">
							<th scope="row">
								<label for="envira-config-lightbox-effect"><?php esc_html_e( 'Lightbox Transition Effect', 'envira-albums' ); ?></label>
							</th>
							<td>
								<select id="envira-config-lightbox-effect" name="_eg_album_data[config][effect]">
									<?php
									$effect = envira_get_config( 'lightbox_open_close_effect', $data ) === 'zomm-in-out' ? 'zoom-in-out' : envira_get_config( 'lightbox_open_close_effect', $data );

									// Standard Effects.
									foreach ( (array) envira_get_transition_effects() as $i => $data ) {
										?>
										<option value="<?php echo esc_html( $data['value'] ); ?>"<?php selected( $data['value'], envira_albums_get_config( 'effect', $album_data ) ); ?>><?php echo esc_html( $data['name'] ); ?></option>
										<?php
									}

									?>
								</select>
								<p class="description"><?php esc_html_e( 'Type of transition between images in the lightbox view.', 'envira-albums' ); ?></p>
							</td>
						</tr>
						<tr id="envira-config-gallery-sort">
							<th scope="row">
								<label for="envira-config-gallery-sort"><?php esc_html_e( 'Lightbox Sort', 'envira-albums' ); ?></label>
							</th>
							<td>
								<select id="envira-config-gallery-sort" name="_eg_album_data[config][gallery_sort]">
									<?php
									// Standard Effects.
									foreach ( (array) envira_get_gallery_lightbox_sort_effects() as $i => $data ) {
										?>
										<option value="<?php echo esc_html( $data['value'] ); ?>"<?php selected( $data['value'], envira_albums_get_config( 'gallery_sort', $album_data ) ); ?>><?php echo esc_html( $data['name'] ); ?></option>
										<?php
									}
									?>
								</select>
								<p class="description"><?php esc_html_e( 'Determine the sort order of the lightbox gallery images in this album.', 'envira-albums' ); ?></p>
							</td>
						</tr>
						<tr id="envira-config-supersize-box">
							<th scope="row">
								<label for="envira-config-supersize"><?php esc_html_e( 'Enable Lightbox Supersize?', 'envira-albums' ); ?></label>
							</th>
							<td>
								<input id="envira-config-supersize" type="checkbox" name="_eg_album_data[config][supersize]" value="<?php echo esc_html( envira_albums_get_config( 'supersize', $album_data ) ); ?>" <?php checked( envira_albums_get_config( 'supersize', $album_data ), 1 ); ?> />
								<span class="description"><?php esc_html_e( 'Enables or disables supersize mode for gallery lightbox images.', 'envira-albums' ); ?></span>
							</td>
						</tr>
						<?php do_action( 'envira_albums_lightbox_box', $post ); ?>

						<tr id="envira-config-image-counter">
							<th scope="row">
								<label for="envira-config-margin"><?php esc_html_e( 'Enable Image Counter?', 'envira-gallery' ); ?></label>
							</th>
								<td>
									<input id="envira-config-lightbox-image-counter" type="checkbox" name="_eg_album_data[config][image_counter]" value="<?php echo esc_html( envira_albums_get_config( 'image_counter', $album_data ) ); ?>" <?php checked( envira_albums_get_config( 'image_counter', $album_data ), 1 ); ?> />
									<span class="description"><?php esc_html_e( 'Adds \'Image X of X\' after your caption.', 'envira-gallery' ); ?></span>
								</td>

						</tr>

					</tbody>
				</table>
			</div>
		</div>

		<!-- Thumbnails -->
		<div id="envira-thumbnails-settings">
			<p class="envira-intro">
				<?php esc_html_e( 'Lightbox Thumbnail Settings', 'envira-albums' ); ?>
				<small>
					<?php esc_html_e( 'The settings below adjust the thumbnail views for the lightbox display.', 'envira-albums' ); ?>
				</small>
			</p>
			<table class="form-table">
				<tbody>
					<tr id="envira-config-thumbnails-box">
						<th scope="row">
							<label for="envira-config-thumbnails"><?php esc_html_e( 'Enable Gallery Thumbnails?', 'envira-albums' ); ?></label>
						</th>
						<td>
							<input id="envira-config-thumbnails" type="checkbox" name="_eg_album_data[config][thumbnails]" value="1" <?php checked( envira_albums_get_config( 'thumbnails', $album_data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( 'Enables or disables the gallery lightbox thumbnails.', 'envira-albums' ); ?></span>
						</td>
					</tr>
					<?php
								$custom = envira_get_config( 'thumbnails_custom_size', $album_data );
								$width  = envira_get_config( 'thumbnails_width', $album_data );
								$height = envira_get_config( 'thumbnails_height', $album_data );

								/**
								 * If the user has a pre-existing gallery with width/height that were NOT once before the defaults
								 * which are likely 75px width and 50px height - then auto check the custom width/height box
								 */
					if ( empty( $custom ) &&
									! empty( $width ) &&
									( envira_get_config( 'thumbnails_width', $album_data ) !== envira_get_config_default( 'thumbnails_width' ) ) &&
									! empty( $height ) &&
									( envira_get_config( 'thumbnails_height', $album_data ) !== envira_get_config_default( 'thumbnails_height' ) ) ) {
						$checked = 'checked="true"';
					} else {
						$checked = checked( envira_get_config( 'thumbnails_custom_size', $album_data ), 1, false );
					}

					?>
					<tr id="envira-config-thumbnails-custom-size">
						<th scope="row">
							<label for="envira-config-thumbnails-custom-size"><?php esc_html_e( 'Use Custom Width/Height?', 'envira-gallery' ); ?></label>
						</th>
						<td>
							<input id="envira-config-thumbnails-custom-size" type="checkbox" name="_eg_album_data[config][thumbnails_custom_size]" value="<?php echo esc_html( envira_albums_get_config( 'thumbnails_custom_size', $album_data ) ); ?>" <?php echo esc_html( $checked ); ?> />
							<span class="description"><?php esc_html_e( 'This enables you to enter a custom width and height, overriding Envira\'s automatic settings.', 'envira-gallery' ); ?></span>
						</td>
					</tr>
					<tr id="envira-config-thumbnails-width-box">
						<th scope="row">
							<label for="envira-config-thumbnails-width"><?php esc_html_e( 'Gallery Thumbnails Width', 'envira-albums' ); ?></label>
						</th>
						<td>
							<input id="envira-config-thumbnails-width" type="number" name="_eg_album_data[config][thumbnails_width]" value="<?php echo esc_html( envira_albums_get_config( 'thumbnails_width', $album_data ) ); ?>" /> <span class="envira-unit"><?php esc_html_e( 'px', 'envira-albums' ); ?></span>
							<p class="description"><?php esc_html_e( 'Sets the width of each lightbox thumbnail.', 'envira-albums' ); ?></p>
						</td>
					</tr>
					<tr id="envira-config-thumbnails-height-box">
						<th scope="row">
							<label for="envira-config-thumbnails-height"><?php esc_html_e( 'Gallery Thumbnails Height', 'envira-albums' ); ?></label>
						</th>
						<td>
							<input id="envira-config-thumbnails-height" type="number" name="_eg_album_data[config][thumbnails_height]" value="<?php echo esc_html( envira_albums_get_config( 'thumbnails_height', $album_data ) ); ?>" /> <span class="envira-unit"><?php esc_html_e( 'px', 'envira-albums' ); ?></span>
							<p class="description"><?php esc_html_e( 'Sets the height of each lightbox thumbnail.', 'envira-albums' ); ?></p>
						</td>
					</tr>
					<tr id="envira-config-thumbnails-position-box">
						<th scope="row">
							<label for="envira-config-thumbnails-position"><?php esc_html_e( 'Gallery Thumbnails Position', 'envira-albums' ); ?></label>
						</th>
						<td>
							<select id="envira-config-thumbnails-position" name="_eg_album_data[config][thumbnails_position]">
								<?php foreach ( (array) envira_get_thumbnail_positions() as $i => $data ) : ?>
									<option value="<?php echo esc_html( $data['value'] ); ?>"<?php selected( $data['value'], envira_albums_get_config( 'thumbnails_position', $album_data ) ); ?>><?php echo esc_html( $data['name'] ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'Sets the position of the lightbox thumbnails.', 'envira-albums' ); ?></p>
						</td>
					</tr>
					<?php do_action( 'envira_albums_thumbnails_box', $post ); ?>
				</tbody>
			</table>
		</div>
		<?php

	}

	/**
	 * Callback for displaying the UI for setting mobile options.
	 *
	 * @since 1.2
	 *
	 * @param object $post The current post object.
	 */
	public function mobile_tab( $post ) {

		$album_data = envira_get_album( $post->ID, true ); // flush transient as you grab settings.

		?>
		<div id="envira-mobile">
			<p class="envira-intro">
				<?php esc_html_e( 'Mobile Gallery Settings', 'envira-albums' ); ?>
				<small>
					<?php esc_html_e( 'The settings below adjust configuration options for the Gallery when viewed on a mobile device.', 'envira-albums' ); ?>


					<?php if ( apply_filters( 'envira_whitelabel', false ) ) : ?>

						<?php do_action( 'envira_album_whitelabel_lightbox_settings' ); ?>

					<?php else : ?>

						<br />
						<?php esc_html_e( 'Need some help?', 'envira-albums' ); ?>
						<a href="http://enviragallery.com/docs/albums-addon/" class="envira-doc" target="_blank">
							<?php esc_html_e( 'Read the Documentation', 'envira-albums' ); ?>
						</a>
						or
						<a href="https://www.youtube.com/embed/tIOdz1CY7D0/?rel=0" class="envira-video" target="_blank">
							<?php esc_html_e( 'Watch a Video', 'envira-albums' ); ?>
						</a>

					<?php endif; ?>

				</small>
			</p>
			<table class="form-table">
				<tbody>
					<tr id="envira-config-mobile-box">
						<th scope="row">
							<label for="envira-config-mobile"><?php esc_html_e( 'Create Mobile Album Images?', 'envira-albums' ); ?></label>
						</th>
						<td>
							<input id="envira-config-mobile" type="checkbox" name="_eg_album_data[config][mobile]" value="<?php echo esc_html( envira_albums_get_config( 'mobile', $album_data ) ); ?>" <?php checked( envira_albums_get_config( 'mobile', $album_data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( 'Enables or disables creating specific images for mobile devices.', 'envira-albums' ); ?></span>
						</td>
					</tr>
					<tr id="envira-config-mobile-size-box">
						<th scope="row">
							<label for="envira-config-mobile-width"><?php esc_html_e( 'Mobile Dimensions', 'envira-albums' ); ?></label>
						</th>
						<td>
							<input id="envira-config-mobile-width" type="number" name="_eg_album_data[config][mobile_width]" value="<?php echo esc_html( envira_albums_get_config( 'mobile_width', $album_data ) ); ?>" /> &#215; <input id="envira-config-mobile-height" type="number" name="_eg_album_data[config][mobile_height]" value="<?php echo esc_html( envira_albums_get_config( 'mobile_height', $album_data ) ); ?>" /> <span class="envira-unit"><?php esc_html_e( 'px', 'envira-albums' ); ?></span>
							<p class="description"><?php esc_html_e( 'These will be the sizes used for images displayed on mobile devices.', 'envira-albums' ); ?></p>
						</td>
					</tr>

					<tr id="envira-config-mobile-justified-row-height">
							<th scope="row">
								<label for="envira-config-justified-row-height-mobile"><?php esc_html_e( 'Automatic Layout: Row Height', 'envira-gallery' ); ?></label>
							</th>
							<td>
								<input id="envira-config-justified-row-height-mobile" type="number" name="_eg_album_data[config][mobile_justified_row_height]" value="<?php echo esc_html( envira_albums_get_config( 'mobile_justified_row_height', $album_data ) ); ?>" /> <span class="envira-unit"><?php esc_html_e( 'px', 'envira-gallery' ); ?></span>
								<p class="description"><?php esc_html_e( 'Determines how high (in pixels) each row will be. 80px is default. ', 'envira-gallery' ); ?></p>
							</td>
					</tr>

					<tr id="envira-config-mobile-breadcrumbs">
						<th scope="row">
							<label for="envira-config-mobile"><?php esc_html_e( 'Enable Breadcrumbs?', 'envira-albums' ); ?></label>
						</th>
						<td>
							<input id="envira-config-mobile" type="checkbox" name="_eg_album_data[config][breadcrumbs_enabled_mobile]" value="<?php echo esc_html( envira_albums_get_config( 'breadcrumbs_enabled_mobile', $album_data ) ); ?>" <?php checked( envira_albums_get_config( 'breadcrumbs_enabled_mobile', $album_data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( 'Enables or disables breadcrumb navigation for mobile devices.', 'envira-albums' ); ?></span>
						</td>
					</tr>

					<?php do_action( 'envira_albums_mobile_box', $post ); ?>
				</tbody>
			</table>

			<!-- Lightbox -->
			<p class="envira-intro">
				<?php esc_html_e( 'Mobile Lightbox Settings', 'envira-albums' ); ?>
				<small>
					<?php esc_html_e( 'The settings below adjust configuration options for the Lightbox when viewed on a mobile device.', 'envira-albums' ); ?>
				</small>
			</p>
			<table class="form-table">
				<tbody>
					<tr id="envira-config-mobile-lightbox-box">
						<th scope="row">
							<label for="envira-config-mobile-lightbox"><?php esc_html_e( 'Enable Lightbox?', 'envira-albums' ); ?></label>
						</th>
						<td>
							<input id="envira-config-mobile-lightbox" type="checkbox" name="_eg_album_data[config][mobile_lightbox]" value="<?php echo esc_html( envira_albums_get_config( 'mobile_lightbox', $album_data ) ); ?>" <?php checked( envira_albums_get_config( 'mobile_lightbox', $album_data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( 'Enables or disables the gallery lightbox on mobile devices.', 'envira-albums' ); ?></span>
						</td>
					</tr>
					<tr id="envira-config-mobile-arrows-box">
						<th scope="row">
							<label for="envira-config-mobile-arrows"><?php esc_html_e( 'Enable Gallery Arrows?', 'envira-albums' ); ?></label>
						</th>
						<td>
							<input id="envira-config-mobile-arrows" type="checkbox" name="_eg_album_data[config][mobile_arrows]" value="<?php echo esc_html( envira_albums_get_config( 'mobile_arrows', $album_data ) ); ?>" <?php checked( envira_albums_get_config( 'mobile_arrows', $album_data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( 'Enables or disables the gallery lightbox navigation arrows on mobile devices.', 'envira-albums' ); ?></span>
						</td>
					</tr>
					<tr id="envira-config-mobile-toolbar-box">
						<th scope="row">
							<label for="envira-config-mobile-toolbar"><?php esc_html_e( 'Enable Gallery Toolbar?', 'envira-albums' ); ?></label>
						</th>
						<td>
							<input id="envira-config-mobile-toolbar" type="checkbox" name="_eg_album_data[config][mobile_toolbar]" value="<?php echo esc_html( envira_albums_get_config( 'mobile_toolbar', $album_data ) ); ?>" <?php checked( envira_albums_get_config( 'mobile_toolbar', $album_data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( 'Enables or disables the gallery lightbox toolbar on mobile devices.', 'envira-albums' ); ?></span>
						</td>
					</tr>
					<tr id="envira-config-mobile-thumbnails-box">
						<th scope="row">
							<label for="envira-config-mobile-thumbnails"><?php esc_html_e( 'Enable Gallery Thumbnails?', 'envira-albums' ); ?></label>
						</th>
						<td>
							<input id="envira-config-mobile-thumbnails" type="checkbox" name="_eg_album_data[config][mobile_thumbnails]" value="<?php echo esc_html( envira_albums_get_config( 'mobile_thumbnails', $album_data ) ); ?>" <?php checked( envira_albums_get_config( 'mobile_thumbnails', $album_data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( 'Enables or disables the gallery lightbox thumbnails on mobile devices.', 'envira-albums' ); ?></span>
						</td>
					</tr>
					<tr id="envira-config-mobile-thumbnails-width-box">
						<th scope="row">
							<label for="envira-config-mobile-thumbnails-width"><?php esc_html_e( 'Gallery Thumbnails Width', 'envira-gallery' ); ?></label>
						</th>
						<td>
							<input id="envira-config-mobile-thumbnails-width" type="number" name="_eg_album_data[config][mobile_thumbnails_width]" value="<?php echo esc_html( envira_albums_get_config( 'mobile_thumbnails_width', $album_data ) ); ?>" /> <span class="envira-unit"><?php esc_html_e( 'px', 'envira-gallery' ); ?></span>
							<p class="description"><?php esc_html_e( 'Sets the width of each lightbox thumbnail when on mobile devices.', 'envira-gallery' ); ?></p>
						</td>
					</tr>
					<tr id="envira-config-mobile-thumbnails-height-box">
						<th scope="row">
							<label for="envira-config-mobile-thumbnails-height"><?php esc_html_e( 'Gallery Thumbnails Height', 'envira-gallery' ); ?></label>
						</th>
						<td>
							<input id="envira-config-mobile-thumbnails-height" type="number" name="_eg_album_data[config][mobile_thumbnails_height]" value="<?php echo esc_html( envira_albums_get_config( 'mobile_thumbnails_height', $album_data ) ); ?>" /> <span class="envira-unit"><?php esc_html_e( 'px', 'envira-gallery' ); ?></span>
							<p class="description"><?php esc_html_e( 'Sets the height of each lightbox thumbnail when on mobile devices.', 'envira-gallery' ); ?></p>
						</td>
					</tr>
					<?php do_action( 'envira_albums_mobile_lightbox_box', $post ); ?>
				</tbody>
			</table>
		</div>
		<?php

	}

	/**
	 * Callback for displaying the UI for setting album miscellaneous options.
	 *
	 * @since 1.0.0
	 *
	 * @param object $post The current post object.
	 */
	public function misc_tab( $post ) {

		$album_data = get_post_meta( $post->ID, '_eg_album_data', true );

		?>
		<div id="envira-misc">
			<p class="envira-intro">
				<?php esc_html_e( 'Miscellaneous Settings', 'envira-albums' ); ?>
				<small>
					<?php esc_html_e( 'The settings below adjust the miscellaneous options for the album.', 'envira-albums' ); ?>
				</small>
			</p>
			<table class="form-table">
				<tbody>
					<tr id="envira-config-slug-box">
						<th scope="row">
							<label for="envira-config-slug"><?php esc_html_e( 'Album Slug', 'envira-albums' ); ?></label>
						</th>
						<td>
							<input id="envira-config-slug" type="text" name="_eg_album_data[config][slug]" value="<?php echo esc_html( envira_albums_get_config( 'slug', $album_data ) ); ?>" />
							<p class="description"><?php esc_html_e( 'Unique internal album slug for identification and advanced album queries.', 'envira-albums' ); ?></p>
						</td>
					</tr>
					<tr id="envira-config-import-export-box">
						<th scope="row">
							<label for="envira-config-import-gallery"><?php esc_html_e( 'Import/Export Album', 'envira-albums' ); ?></label>
						</th>
						<td>
							<form></form>
							<?php
							$import_url = 'auto-draft' === $post->post_status ? add_query_arg(
								array(
									'post'   => $post->ID,
									'action' => 'edit',
									'envira-album-imported' => true,
								),
								admin_url( 'post.php' )
							) : add_query_arg( 'envira-album-imported', true );
							?>
							<form action="<?php echo esc_url( $import_url ); ?>" id="envira-config-import-album-form" class="envira-albums-import-form" method="post" enctype="multipart/form-data">
								<input id="envira-config-import-album" type="file" name="envira_import_album" />
								<input type="hidden" name="envira_albums_import" value="1" />
								<input type="hidden" name="envira_post_id" value="<?php echo esc_html( $post->ID ); ?>" />
								<?php wp_nonce_field( 'envira-albums-import', 'envira-albums-import' ); ?>
								<?php submit_button( __( 'Import Album', 'envira-albums' ), 'secondary', 'envira-albums-import-submit', false ); ?>
								<span class="spinner envira-gallery-spinner"></span>
							</form>

							<hr />

							<form id="envira-config-export-album-form" method="post">
								<input type="hidden" name="envira_export" value="1" />
								<input type="hidden" name="envira_post_id" value="<?php echo esc_html( $post->ID ); ?>" />
								<?php wp_nonce_field( 'envira-albums-export', 'envira-albums-export' ); ?>
								<?php submit_button( __( 'Export Album', 'envira-albums' ), 'secondary', 'envira-albums-export-submit', false ); ?>
							</form>
						</td>
					</tr>
					<tr id="envira-config-rtl-box">
						<th scope="row">
							<label for="envira-config-rtl"><?php esc_html_e( 'Enable RTL Support?', 'envira-albums' ); ?></label>
						</th>
						<td>
							<input id="envira-config-rtl" type="checkbox" name="_eg_album_data[config][rtl]" value="<?php echo esc_html( envira_albums_get_config( 'rtl', $album_data ) ); ?>" <?php checked( envira_albums_get_config( 'rtl', $album_data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( 'Enables or disables RTL support in Envira for right-to-left languages.', 'envira-albums' ); ?></span>
						</td>
					</tr>
					<?php do_action( 'envira_albums_misc_box', $post ); ?>
				</tbody>
			</table>
		</div>
		<?php

	}

	/**
	 * Callback for displaying the settings UI for the Standalone tab.
	 *
	 * @since 1.3.2
	 *
	 * @param object $post The current post object.
	 */
	public function standalone_tab( $post ) {

		/* Get list of templates */
		$templates  = get_page_templates();
		$album_data = get_post_meta( $post->ID, '_eg_album_data', true );
		$key        = '_eg_album_data[config]';

		?>
			<p class="envira-intro">
				<?php esc_html_e( 'Standalone Options', 'envira-standalone' ); ?>

				<small>
					<?php esc_html_e( 'The settings below adjust the Standalone settings.', 'envira-standalone' ); ?>
					<br/>
					<?php esc_html_e( 'Need some help?', 'envira-standalone' ); ?>
					<a href="http://enviragallery.com/docs/standalone/" class="envira-doc" target="_blank">
						<?php esc_html_e( 'Read the Documentation', 'envira-standalone' ); ?>
					</a>
					or
					<a href="https://www.youtube.com/embed/dJ2t7uplFkw?autoplay=1&rel=0" class="envira-video" target="_blank">
						<?php esc_html_e( 'Watch a Video', 'envira-standalone' ); ?>
					</a>
				</small>
			</p>
			<table class="form-table">
				<tbody>
					<tr id="envira-config-standalone-box">
							<th scope="row">
								<label for="envira-config-standalone-template"><?php esc_html_e( 'Template', 'envira-standalone' ); ?></label>
							</th>
							<td>
								<?php if ( ! empty( $templates ) ) : ?>
								<select id="envira-config-standalone-template" name="<?php echo esc_html( $key ); ?>[standalone_template]">
									<option value="">(Default)</option>
									<?php foreach ( (array) $templates as $name => $filename ) : ?>

									<option value="<?php echo esc_html( $filename ); ?>"<?php selected( $filename, envira_albums_get_config( 'standalone_template', $album_data ) ); ?>><?php echo esc_html( $name ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e( 'By default we use single.php, which is the default template of the single blog post in your theme.', 'envira-albums' ); ?></p>

								<?php else : ?>

								<p class="description"><?php esc_html_e( 'Your current theme does not have any custom templates. If you want to use a template besides the default, you need to add a custom template to your theme.', 'envira-albums' ); ?></p>

								<?php endif; ?>

							</td>
					</tr>
				</tbody>
			</table>
			<?php

	}

	/**
	 * Helper method for retrieving description options.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of description options.
	 */
	public function get_display_description_options() {

		return array(
			array(
				'name'  => __( 'Do not display', 'envira-albums' ),
				'value' => 0,
			),
			array(
				'name'  => __( 'Display above gallery', 'envira-albums' ),
				'value' => 'above',
			),
			array(
				'name'  => __( 'Display below gallery', 'envira-albums' ),
				'value' => 'below',
			),
		);

	}

	/**
	 * Helper method for retrieving album alignment options.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of description options.
	 */
	public function get_album_alignment_options() {

		return array(
			array(
				'name'  => __( 'None', 'envira-albums' ),
				'value' => 0,
			),
			array(
				'name'  => __( 'Left', 'envira-albums' ),
				'value' => 'left',
			),
			array(
				'name'  => __( 'Right', 'envira-albums' ),
				'value' => 'right',
			),
			array(
				'name'  => __( 'Center', 'envira-albums' ),
				'value' => 'center',
			),
		);

	}

	/**
	 * Callback for saving values from Envira metaboxes.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $post_id The current post ID.
	 * @param object $post The current post object.
	 */
	public function save_meta_boxes( $post_id, $post ) {

		// Bail out if we fail a security check.
		if ( ! isset( $_POST['envira-albums'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['envira-albums'] ) ), 'envira-albums' ) ) {
			return;
		}

		// Bail out if running an autosave, ajax, cron or revision.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Bail out if the user doesn't have the correct permissions to update the slider.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Get the existing settings.
		$settings = get_post_meta( $post_id, '_eg_album_data', true );

		if ( ! is_array( $settings ) || false === $settings ) {
			// there are no settings, so start with an array
			// also, this "resets" any "bad" data in this metadata.
			$settings = array();
		}

		// If the ID of the album is not set or is lost, replace it now.
		if ( empty( $settings['id'] ) || ! $settings['id'] ) {
			$settings['id'] = $post_id;
		}

		// Build $settings array, comprising of
		// - galleryIDs - an array of gallery IDs to include in this album
		// - config - general configuration for this album
		// Convert gallery IDs to array.
		if ( empty( $_POST['galleryIDs'] ) ) {
			unset( $settings['galleryIDs'] );
			unset( $settings['galleries'] );
		} else {
			$settings['galleryIDs'] = isset( $_POST['galleryIDs'] ) ? explode( ',', wp_unslash( $_POST['galleryIDs'] ) ) : null; // @codingStandardsIgnoreLine
			$settings['galleryIDs'] = array_filter( $settings['galleryIDs'] );
		}

		// Never have width equal zero, even if the user removes the value.
		$album_width = isset( $_POST['_eg_album_data']['config']['album_width'] ) ? absint( $_POST['_eg_album_data']['config']['album_width'] ) : envira_albums_get_config_default( 'album_width' );

		// Store album config.
		$settings['config']            = array();
		$settings['config']['type']    = isset( $_POST['_eg_album_data']['config']['type'] ) ? sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['type'] ) ) : envira_albums_get_config_default( 'type' );
		$settings['config']['columns'] = isset( $_POST['_eg_album_data']['config']['columns'] ) ? sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['columns'] ) ) : envira_albums_get_config_default( 'columns' );

		// Store album title.
		$settings['config']['title'] = isset( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : '';

		// Automatic/Justified.
		$settings['config']['justified_row_height'] = isset( $_POST['_eg_album_data']['config']['justified_row_height'] ) ? absint( $_POST['_eg_album_data']['config']['justified_row_height'] ) : 150;
		$settings['config']['justified_margins']    = isset( $_POST['_eg_album_data']['config']['justified_margins'] ) ? absint( $_POST['_eg_album_data']['config']['justified_margins'] ) : envira_albums_get_config_default( 'justified_margins' );
		$settings['config']['justified_last_row']   = isset( $_POST['_eg_album_data']['config']['justified_last_row'] ) ? sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['justified_last_row'] ) ) : envira_albums_get_config_default( 'justified_last_row' );

		$settings['config']['back']                        = ( isset( $_POST['_eg_album_data']['config']['back'] ) ? 1 : 0 );
		$settings['config']['back_location']               = isset( $_POST['_eg_album_data']['config']['back_location'] ) ? sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['back_location'] ) ) : envira_albums_get_config_default( 'back_location' );
		$settings['config']['back_label']                  = isset( $_POST['_eg_album_data']['config']['back_label'] ) ? sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['back_label'] ) ) : envira_albums_get_config_default( 'back_label' );
		$settings['config']['description_position']        = isset( $_POST['_eg_album_data']['config']['description_position'] ) ? sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['description_position'] ) ) : envira_albums_get_config_default( 'description_position' );
		$settings['config']['album_alignment']             = isset( $_POST['_eg_album_data']['config']['album_alignment'] ) ? sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['album_alignment'] ) ) : envira_albums_get_config_default( 'album_alignment' );
		$settings['config']['gallery_theme']               = isset( $_POST['_eg_album_data']['config']['gallery_theme'] ) ? sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['gallery_theme'] ) ) : envira_albums_get_config_default( 'gallery_theme' );
		$settings['config']['album_width']                 = $album_width ? $album_width : 100;
		$settings['config']['description']                 = trim( wp_unslash( $_POST['_eg_album_data']['config']['description'] ) ); // @codingStandardsIgnoreLine - Rewrite output with htmlentities
		$settings['config']['display_titles']              = isset( $_POST['_eg_album_data']['config']['display_titles'] ) ? sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['display_titles'] ) ) : envira_albums_get_config_default( 'display_titles' );
		$settings['config']['display_titles_automatic']    = ( isset( $_POST['_eg_album_data']['config']['display_titles_automatic'] ) ? 1 : 0 );
		$settings['config']['gallery_description_display'] = isset( $_POST['_eg_album_data']['config']['gallery_description_display'] ) ? sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['gallery_description_display'] ) ) : false;

		$settings['config']['display_captions']    = isset( $_POST['_eg_album_data']['config']['display_captions'] ) ? 1 : 0;
		$settings['config']['display_image_count'] = isset( $_POST['_eg_album_data']['config']['display_image_count'] ) ? 1 : 0;
		$settings['config']['gutter']              = isset( $_POST['_eg_album_data']['config']['gutter'] ) ? absint( wp_unslash( $_POST['_eg_album_data']['config']['gutter'] ) ) : envira_albums_get_config_default( 'gutter' );
		$settings['config']['margin']              = isset( $_POST['_eg_album_data']['config']['margin'] ) ? absint( wp_unslash( $_POST['_eg_album_data']['config']['margin'] ) ) : envira_albums_get_config_default( 'margin' );
		$settings['config']['sorting']             = isset( $_POST['_eg_album_data']['config']['sorting'] ) ? sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['sorting'] ) ) : envira_albums_get_config_default( 'sorting' );
		$settings['config']['sorting_direction']   = isset( $_POST['_eg_album_data']['config']['sorting_direction'] ) ? sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['sorting_direction'] ) ) : envira_albums_get_config_default( 'sorting_direction' );

		$settings['config']['crop']        = isset( $_POST['_eg_album_data']['config']['crop'] ) ? 1 : 0;
		$settings['config']['crop_width']  = isset( $_POST['_eg_album_data']['config']['crop_width'] ) ? absint( $_POST['_eg_album_data']['config']['crop_width'] ) : envira_albums_get_config_default( 'crop_width' );
		$settings['config']['crop_height'] = isset( $_POST['_eg_album_data']['config']['crop_height'] ) ? absint( $_POST['_eg_album_data']['config']['crop_height'] ) : envira_albums_get_config_default( 'crop_height' );
		$settings['config']['isotope']     = isset( $_POST['_eg_album_data']['config']['isotope'] ) ? 1 : 0;

		$settings['config']['lazy_loading']       = isset( $_POST['_eg_album_data']['config']['lazy_loading'] ) ? 1 : 0;
		$settings['config']['lazy_loading_delay'] = isset( $_POST['_eg_album_data']['config']['lazy_loading_delay'] ) ? absint( $_POST['_eg_album_data']['config']['lazy_loading_delay'] ) : 100;

		// Lightbox.
		$settings['config']['lightbox']                   = isset( $_POST['_eg_album_data']['config']['lightbox'] ) ? 1 : 0;
		$settings['config']['lightbox_theme']             = isset( $_POST['_eg_album_data']['config']['lightbox_theme'] ) ? sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['lightbox_theme'] ) ) : envira_albums_get_config_default( 'lightbox_theme' );
		$settings['config']['title_display']              = isset( $_POST['_eg_album_data']['config']['title_display'] ) ? sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['title_display'] ) ) : envira_albums_get_config_default( 'title_display' );
		$settings['config']['lightbox_title_caption']     = isset( $_POST['_eg_album_data']['config']['lightbox_title_caption'] ) ? sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['lightbox_title_caption'] ) ) : envira_albums_get_config_default( 'lightbox_title_caption' );
		$settings['config']['arrows']                     = isset( $_POST['_eg_album_data']['config']['arrows'] ) ? 1 : 0;
		$settings['config']['arrows_position']            = isset( $_POST['_eg_album_data']['config']['arrows_position'] ) ? sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['arrows_position'] ) ) : envira_albums_get_config_default( 'arrows_position' );
		$settings['config']['toolbar']                    = isset( $_POST['_eg_album_data']['config']['toolbar'] ) ? 1 : 0;
		$settings['config']['toolbar_title']              = isset( $_POST['_eg_album_data']['config']['toolbar_title'] ) ? 1 : 0;
		$settings['config']['toolbar_position']           = isset( $_POST['_eg_album_data']['config']['toolbar_position'] ) ? sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['toolbar_position'] ) ) : envira_albums_get_config_default( 'toolbar_position' );
		$settings['config']['aspect']                     = isset( $_POST['_eg_album_data']['config']['aspect'] ) ? 1 : 0;
		$settings['config']['loop']                       = isset( $_POST['_eg_album_data']['config']['loop'] ) ? 1 : 0;
		$settings['config']['lightbox_open_close_effect'] = isset( $_POST['_eg_album_data']['config']['lightbox_open_close_effect'] ) ? sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['lightbox_open_close_effect'] ) ) : envira_albums_get_config_default( 'lightbox_open_close_effect' );
		$settings['config']['effect']                     = isset( $_POST['_eg_album_data']['config']['effect'] ) ? sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['effect'] ) ) : envira_albums_get_config_default( 'lightbox_open_close_effect' );
		$settings['config']['html5']                      = isset( $_POST['_eg_album_data']['config']['html5'] ) ? 1 : 0;
		$settings['config']['gallery_sort']               = isset( $_POST['_eg_album_data']['config']['gallery_sort'] ) ? preg_replace( '#[^a-z0-9-_]#', '', sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['gallery_sort'] ) ) ) : envira_albums_get_config_default( 'gallery_sort' );
		$settings['config']['supersize']                  = isset( $_POST['_eg_album_data']['config']['supersize'] ) ? 1 : 0;
		$settings['config']['image_counter']              = isset( $_POST['_eg_album_data']['config']['image_counter'] ) ? 1 : 0;

		// Lightbox Thumbnails.
		$settings['config']['thumbnails']             = isset( $_POST['_eg_album_data']['config']['thumbnails'] ) ? 1 : 0;
		$settings['config']['thumbnails_width']       = isset( $_POST['_eg_album_data']['config']['thumbnails_width'] ) ? absint( $_POST['_eg_album_data']['config']['thumbnails_width'] ) : envira_albums_get_config_default( 'thumbnails_width' );
		$settings['config']['thumbnails_height']      = isset( $_POST['_eg_album_data']['config']['thumbnails_height'] ) ? absint( $_POST['_eg_album_data']['config']['thumbnails_height'] ) : envira_albums_get_config_default( 'thumbnails_height' );
		$settings['config']['thumbnails_position']    = isset( $_POST['_eg_album_data']['config']['thumbnails_position'] ) ? preg_replace( '#[^a-z0-9-_]#', '', sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['thumbnails_position'] ) ) ) : envira_albums_get_config_default( 'thumbnails_position' );
		$settings['config']['thumbnails_custom_size'] = isset( $_POST['_eg_album_data']['config']['thumbnails_custom_size'] ) ? 1 : 0;

		// Mobile.
		$settings['config']['mobile']                      = isset( $_POST['_eg_album_data']['config']['mobile'] ) ? 1 : 0;
		$settings['config']['mobile_width']                = isset( $_POST['_eg_album_data']['config']['mobile_width'] ) ? absint( $_POST['_eg_album_data']['config']['mobile_width'] ) : envira_albums_get_config_default( 'mobile_width' );
		$settings['config']['mobile_height']               = isset( $_POST['_eg_album_data']['config']['mobile_height'] ) ? absint( $_POST['_eg_album_data']['config']['mobile_height'] ) : envira_albums_get_config_default( 'mobile_height' );
		$settings['config']['mobile_lightbox']             = isset( $_POST['_eg_album_data']['config']['mobile_lightbox'] ) ? 1 : 0;
		$settings['config']['mobile_arrows']               = isset( $_POST['_eg_album_data']['config']['mobile_arrows'] ) ? 1 : 0;
		$settings['config']['mobile_toolbar']              = isset( $_POST['_eg_album_data']['config']['mobile_toolbar'] ) ? 1 : 0;
		$settings['config']['mobile_thumbnails']           = isset( $_POST['_eg_album_data']['config']['mobile_thumbnails'] ) ? 1 : 0;
		$settings['config']['mobile_thumbnails_width']     = isset( $_POST['_eg_album_data']['config']['mobile_thumbnails_width'] ) ? absint( $_POST['_eg_album_data']['config']['mobile_thumbnails_width'] ) : envira_albums_get_config_default( 'mobile_thumbnails_width' );
		$settings['config']['mobile_thumbnails_height']    = isset( $_POST['_eg_album_data']['config']['mobile_thumbnails_height'] ) ? absint( $_POST['_eg_album_data']['config']['mobile_thumbnails_height'] ) : envira_albums_get_config_default( 'mobile_thumbnails_height' );
		$settings['config']['mobile_justified_row_height'] = isset( $_POST['_eg_album_data']['config']['mobile_justified_row_height'] ) ? absint( $_POST['_eg_album_data']['config']['mobile_justified_row_height'] ) : envira_albums_get_config_default( 'mobile_justified_row_height' );

		// Standalone.
		if ( envira_get_setting( 'standalone_enabled' ) ) {
			$settings['config']['standalone_template'] = ( isset( $_POST['_eg_album_data']['config']['standalone_template'] ) ? str_replace( '-php', '.php', sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['standalone_template'] ) ) ) : '' );
		}

		// Misc.
		$settings['config']['classes'] = ( isset( $_POST['_eg_album_data']['config']['classes'] ) ? explode( "\n", sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['classes'] ) ) ) : '' );
		$settings['config']['rtl']     = ( isset( $_POST['_eg_album_data']['config']['rtl'] ) ? 1 : 0 );

		// Slug.
		$settings['config']['slug'] = isset( $_POST['_eg_album_data']['config']['slug'] ) ? sanitize_title( wp_unslash( $_POST['_eg_album_data']['config']['slug'] ) ) : sanitize_title( $post->post_name );
		$slug_to_save               = ( ! empty( $settings['config']['slug'] ) ) ? $settings['config']['slug'] : get_post_field( 'post_name', $post_id );

		// We need to add metadata if the config slug doesn't match.
		if ( ! empty( $slug_to_save ) && $slug_to_save !== $post->post_name ) {

			$existing_page = get_page_by_path( $slug_to_save, ARRAY_A, array( 'envira', 'envira_album', 'post', 'page' ) );

			// Does this slug exist for any other post?
			if ( $existing_page ) {

				$this->duplicate_post_id = $existing_page['ID'];

				// Generate a unique slug, like WP does, and place it in settings.
				$slug_to_save = wp_unique_post_slug( $slug_to_save, $post_id, $post->post_status, 'envira_album', false );

				add_filter( 'redirect_post_location', array( $this, 'add_notice_slug_exists' ), 99 );

			}

			if ( ! wp_is_post_revision( $post_id ) ) {

				// unhook this function so it doesn't loop infinitely.
				remove_action( 'save_post', array( $this, 'save_meta_boxes' ) );

				// update the post, which calls save_post again.
				wp_update_post(
					array(
						'ID'        => $post_id,
						'post_name' => $slug_to_save,
					)
				);

				// re-hook this function.
				add_action( 'save_post', array( $this, 'save_meta_boxes' ) );

			}

			// finally update the envira gallery slug meta data.
			update_post_meta( $post_id, 'envira_album_slug', $slug_to_save );

		} else {

			// this metadata SHOULD no longer be needed, so let's delete it if it exists and the slug is what it should be anyway.
			delete_post_meta( $post_id, 'envira_album_slug' );

		}

		$settings['config']['slug'] = $slug_to_save;

		// Provide a filter to override settings.
		$settings = apply_filters( 'envira_albums_save_settings', $settings, $post_id, $post );

		// Update the post meta.
		update_post_meta( $post_id, '_eg_album_data', $settings );

		// Fire a hook for addons that need to utilize the cropping feature.
		do_action( 'envira_albums_saved_settings', $settings, $post_id, $post );

		// Flush the album cache.
		envira_flush_album_caches( $post_id, $slug_to_save );

		// Finally, flush all gallery caches to ensure everything is up to date.
		if ( ! empty( $settings['galleryIDs'] ) && is_array( $settings['galleryIDs'] ) ) {
			foreach ( $settings['galleryIDs'] as $gallery_id ) {
				$gallery_slug = get_post_field( 'guid', $gallery_id );
				envira_flush_gallery_caches( $gallery_id, $gallery_slug );
			}
		}

	}


	/**
	 * Add notice for duplicate slugs
	 *
	 * @since 1.8.4
	 * @param string $location Location.
	 */
	public function add_notice_slug_exists( $location ) {

		remove_filter( 'redirect_post_location', array( $this, 'add_notice_query_var' ), 99 );

		return add_query_arg( array( 'envira_album_slug_exists' => $this->duplicate_post_id ), $location );

	}

	/**
	 * Quick check to see if the slug exists ANYWHERE, not just in Envira custom post types
	 *
	 * @since 1.8.4
	 * @param string $post_name The Slug.
	 */
	public function the_slug_exists( $post_name ) {

		global $wpdb;

		if ( $wpdb->get_row( "SELECT post_name FROM wp_posts WHERE post_name = '" . $post_name . "'", 'ARRAY_A' ) ) { // @codingStandardsIgnoreLine
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Registers the Breadcrumbs tab for Albums
	 *
	 * @since 1.0
	 *
	 * @param array $tabs Admin Tabs when editing an Album.
	 * @return array Admin Tabs
	 */
	public function tabs( $tabs ) {

		global $post;

		// get option.
		$dynamic_id = get_option( 'envira_dynamic_album' );

		if ( ! $dynamic_id || $dynamic_id !== $post->ID ) {

			$tabs['breadcrumbs'] = __( 'Breadcrumbs', 'envira-breadcrumbs' );

		}

		return $tabs;

	}

	/**
	 * Outputs options for enabling/disabling Breadcrumbs for Albums
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post Album Post.
	 */
	public function breadcrumbs_box( $post ) {

		wp_nonce_field( 'envira_breadcrumbs_save_settings', 'envira_breadcrumbs_nonce' );

		// Get all album data.
		$album_data = get_post_meta( $post->ID, '_eg_album_data', true );

		?>
		<div id="envira-breadcrumbs">
			<p class="envira-intro">
				<?php esc_html_e( 'Breadcrumb Settings', 'envira-breadcrumbs' ); ?>
				<small>
					<?php esc_html_e( 'The settings below adjust the breadcrumb options on Galleries assigned to this Album.', 'envira-breadcrumbs' ); ?>
					<br />
					<?php esc_html_e( 'Need some help?', 'envira-breadcrumbs' ); ?>
					<a href="http://enviragallery.com/docs/breadcrumbs-addon/" class="envira-doc" target="_blank">
						<?php esc_html_e( 'Read the Documentation', 'envira-breadcrumbs' ); ?>
					</a>
					or
					<a href="https://www.youtube.com/embed/qEsw50Q0zTw" class="envira-video" target="_blank">
						<?php esc_html_e( 'Watch a Video', 'envira-breadcrumbs' ); ?>
					</a>
				</small>
			</p>
			<table class="form-table">
				<tbody>
					<tr id="envira-breadcrumbs-enabled-box">
						<th scope="row">
							<label for="envira-breadcrumbs-enabled"><?php esc_html_e( 'Enable Breadcrumbs?', 'envira-breadcrumbs' ); ?></label>
						</th>
						<td>
							<input id="envira-breadcrumbs-enabled" type="checkbox" name="_eg_album_data[config][breadcrumbs_enabled]" value="1" <?php checked( envira_albums_get_config( 'breadcrumbs_enabled', $album_data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( 'If enabled, breadcrumb navigation will be displayed above this album and its galleries.', 'envira-breadcrumbs' ); ?></span>
						</td>
					</tr>
					<tr id="envira-breadcrumbs-separator-box">
						<th scope="row">
							<label for="envira-breadcrumbs-separator"><?php esc_html_e( 'Breadcrumb Separator', 'envira-breadcrumbs' ); ?></label>
						</th>
						<td>
							<input id="envira-breadcrumbs-separator" type="text" name="_eg_album_data[config][breadcrumbs_separator]" value="<?php echo esc_html( envira_albums_get_config( 'breadcrumbs_separator', $album_data ) ); ?>" />
							<p class="description">
								<?php esc_html_e( 'The separator to use between breadcrumb items. Examples:', 'envira-breadcrumbs' ); ?>
								<code>&raquo;, &rsaquo;, &rarr;, /</code>
							</p>
						</td>
					</tr>
					<tr id="envira-breadcrumbs-enabled-yoast-box">
						<th scope="row">
							<label for="envira-breadcrumbs-yoast-enabled"><?php esc_html_e( 'Enable Yoast Breadcrumbs?', 'envira-breadcrumbs' ); ?></label>
						</th>
						<td>
							<input id="envira-breadcrumbs-yoast-enabled" type="checkbox" name="_eg_album_data[config][breadcrumbs_enabled_yoast]" value="1" <?php checked( envira_albums_get_config( 'breadcrumbs_enabled_yoast', $album_data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( 'If you\'re using the Yoast SEO plugin\'s breadcrumb functionality, enabling this option injects the Album to the breadcrumb list when a Gallery is accessed from an Album.', 'envira-breadcrumbs' ); ?></span>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<?php

	}

	/**
	 * Saves the addon's settings for Galleries.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings  Array of settings to be saved.
	 * @param int   $post_id     The current post ID.
	 * @return array $settings Amended array of settings to be saved.
	 */
	public function save_breadcrumbs( $settings, $post_id ) {

		if (
			! isset( $_POST['_eg_album_data'], $_POST['envira_breadcrumbs_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['envira_breadcrumbs_nonce'] ), 'envira_breadcrumbs_save_settings' )
		) {
			return $settings;
		}

		$settings['config']['breadcrumbs_enabled']        = isset( $_POST['_eg_album_data']['config']['breadcrumbs_enabled'] ) ? 1 : 0;
		$settings['config']['breadcrumbs_enabled_mobile'] = isset( $_POST['_eg_album_data']['config']['breadcrumbs_enabled_mobile'] ) ? 1 : 0;

		$settings['config']['breadcrumbs_separator']     = isset( $_POST['_eg_album_data']['config']['breadcrumbs_separator'] ) ? sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['breadcrumbs_separator'] ) ) : false;
		$settings['config']['breadcrumbs_enabled_yoast'] = isset( $_POST['_eg_album_data']['config']['breadcrumbs_enabled_yoast'] ) ? 1 : 0;

		return $settings;

	}

}
