<?php
/**
 * Common class.
 *
 * @since 1.0.0
 *
 * @package Envira_Defaults
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Common class.
 *
 * @since 1.0.0
 *
 * @package Envira_Defaults
 * @author  Envira Gallery Team <support@enviragallery.com>
 */
class Envira_Defaults_Table {

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
	 * Holds the Envira Gallery Default ID.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public $gallery_default_id;

	/**
	 * Holds the Envira Album Default ID.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public $album_default_id;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Load the base class object.
		$this->base = Envira_Defaults::get_instance();

		// Get Envira Gallery and Album Default IDs.
		$this->gallery_default_id = get_option( 'envira_default_gallery' );
		$this->album_default_id   = get_option( 'envira_default_album' );

		// Actions and Filters.
		add_action( 'admin_footer-edit.php', array( $this, 'bulk_actions' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
		add_action( 'admin_head', array( $this, 'remove_checkbox' ) );
		add_filter( 'page_row_actions', array( $this, 'remove_row_actions' ), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'remove_row_actions' ), 10, 2 );

	}

	/**
	 * Adds an option to bulk update settings for the selected Galleries
	 *
	 * We can't use the bulk_actions-edit-envira, because it doesn't let us add actions:
	 * http://codex.wordpress.org/Plugin_API/Filter_Reference/bulk_actions
	 *
	 * This might change in WP 4.5, though:
	 * http://core.trac.wordpress.org/ticket/16031
	 *
	 * @since 1.0.6
	 */
	public function bulk_actions() {

		global $post_type;

		// Check we're on the WP_List_Table for an Envira Gallery or Album.
		if ( 'envira' !== $post_type && 'envira_album' !== $post_type ) {
			return;
		}

		// Define the label.
		switch ( $post_type ) {
			/**
			* Gallery
			*/
			case 'envira':
				$label = __( 'Gallery', 'envira-defaults' );
				break;

			/**
			* Album
			*/
			case 'envira_album':
				$label = __( 'Album', 'envira-defaults' );
				break;

		}

		// Use some JS to add a new option to the dropdown.
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function( $ ) {
				<?php /* translators: %s */ ?>
				$( '<option>' ).val( 'envira-defaults' ).text( '<?php echo sprintf( esc_attr__( 'Apply Settings from Another %s', 'envira-defaults' ), esc_html( $label ) ); ?>' ).appendTo( "select[name='action'], select[name='action2']" );
			} );
		</script>
		<?php

	}

	/**
	 * Register and enqueue scripts for the Admin UI
	 *
	 * @since 1.0.3
	 */
	public function scripts() {

		// Get Gallery instance.
		$instance = Envira_Gallery::get_instance();

		// Enqueue the gallery / album selection script, if we require it.
		$disable_modal = Envira_Defaults_Common::get_instance()->get_setting( 'disable_modal' );

		wp_enqueue_media();
		wp_enqueue_script( $instance->plugin_slug . '-gallery-select-script', plugins_url( 'assets/js/gallery-select.js', $instance->file ), array( 'jquery' ), $instance->version, true );
		wp_localize_script(
			$instance->plugin_slug . '-gallery-select-script',
			'envira_gallery_select',
			array(
				'get_galleries_nonce' => wp_create_nonce( 'envira-gallery-editor-get-galleries' ),
				'modal_title'         => __( 'Insert', 'envira-gallery' ),
				'insert_button_label' => __( 'Insert', 'envira-gallery' ),
			)
		);

		wp_enqueue_script( $this->base->plugin_slug . '-admin', plugins_url( 'assets/js/admin.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );
		wp_localize_script(
			$this->base->plugin_slug . '-admin',
			'envira_defaults',
			array(
				// Modal Disabled?
				'disable_modal'                          => $disable_modal,

				// Albums.
				'album_modal_title'                      => __( 'Create a new Album', 'envira-defaults' ),
				'album_modal_button_label'               => __( 'Create Album', 'envira-defaults' ),
				'album_bulk_action_modal_title'          => __( 'Apply Album Configuration', 'envira-defaults' ),
				'album_bulk_action_modal_button_label'   => __( 'Apply', 'envira-defaults' ),
				'album_default_id'                       => $this->album_default_id,

				// Galleries.
				'gallery_modal_title'                    => __( 'Create a new Gallery', 'envira-defaults' ),
				'gallery_modal_button_label'             => __( 'Create Gallery', 'envira-defaults' ),
				'gallery_bulk_action_modal_title'        => __( 'Apply Gallery Configuration', 'envira-defaults' ),
				'gallery_bulk_action_modal_button_label' => __( 'Apply', 'envira-defaults' ),
				'gallery_default_id'                     => $this->gallery_default_id,

				// Nonce.
				'nonce'                                  => wp_create_nonce( 'envira-defaults' ),
			)
		);

	}

	/**
	 * Removes the Checkbox from the Envira Default Post
	 * This prevents accidental trashing of the Post
	 *
	 * @since 1.0.0
	 */
	public function remove_checkbox() {

		// Gallery.
		if ( isset( get_current_screen()->post_type ) && 'envira' === get_current_screen()->post_type ) {
			?>
			<script type="text/javascript">
				jQuery(document).ready(function($){
					$('#post-<?php echo sanitize_html_class( $this->gallery_default_id ); ?> .check-column, #post-<?php echo sanitize_html_class( $this->gallery_default_id ); ?> .column-shortcode, #post-<?php echo sanitize_html_class( $this->gallery_default_id ); ?> .column-template, #post-<?php echo sanitize_html_class( $this->gallery_default_id ); ?> .column-images').empty();
				});
			</script>
			<?php
		}

		// Album.
		if ( isset( get_current_screen()->post_type ) && 'envira_album' === get_current_screen()->post_type ) {
			?>
			<script type="text/javascript">
				jQuery(document).ready(function($){
					$('#post-<?php echo sanitize_html_class( $this->album_default_id ); ?> .check-column, #post-<?php echo sanitize_html_class( $this->album_default_id ); ?> .column-shortcode, #post-<?php echo sanitize_html_class( $this->album_default_id ); ?> .column-template, #post-<?php echo sanitize_html_class( $this->album_default_id ); ?> .column-images').empty();
				});
			</script>
			<?php
		}

	}

	/**
	 * Removes Trash and View actions from the Envira Default Gallery Post
	 *
	 * @since 1.0.0
	 *
	 * @param array   $actions Post Row Actions.
	 * @param WP_Post $post WordPress Post.
	 * @return array Post Row Actions
	 */
	public function remove_row_actions( $actions, $post ) {

		switch ( get_post_type( $post ) ) {
			case 'envira':
				// Check Post = Envira Gallery Default Post.
				if ( $post->ID !== $this->gallery_default_id ) {
					return $actions;
				}
				break;
			case 'envira_album':
				// Check Post = Envira Album Default Post.
				if ( $post->ID !== $this->album_default_id ) {
					return $actions;
				}
				break;
			default:
				// Not an Envira CPT.
				return $actions;
		}

		// If here, this is the Envira Default Post.
		// Remove View + Trash Actions.
		unset( $actions['trash'], $actions['view'] );

		return $actions;

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Defaults_Table object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Defaults_Table ) ) {
			self::$instance = new Envira_Defaults_Table();
		}

		return self::$instance;

	}

}

// Load the table class.
$envira_defaults_table = Envira_Defaults_Table::get_instance();
