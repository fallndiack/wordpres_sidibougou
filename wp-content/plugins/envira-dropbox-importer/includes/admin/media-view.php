<?php
/**
 * Media View class.
 *
 * @since 1.0.3
 *
 * @package Envira_Dropbox_Importer
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media View class.
 *
 * @since 1.0.3
 *
 * @package Envira_Dropbox_Importer
 * @author  Envira Team
 */
class Envira_Dropbox_Importer_Media_View {

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

		// Base.
		$this->base = Envira_Dropbox_Importer::get_instance();

		// Media Insert Third Party Support.
		add_filter( 'envira_gallery_media_insert_third_party_sources', array( $this, 'add_media_insert_third_party_support' ), 10, 2 );

		// Modals.
		add_filter( 'envira_gallery_media_view_strings', array( $this, 'media_view_strings' ) );
		add_action( 'print_media_templates', array( $this, 'print_media_templates' ) );

	}

	/**
	 * Registers this Addon to appear in the modal when the "Insert Images from Other Sources" button is clicked
	 * when editing a Gallery.
	 *
	 * @since 1.1.6
	 *
	 * @param   array $addons     Addons.
	 * @param   int   $post_id    Gallery ID.
	 * @return  array               Addons
	 */
	public function add_media_insert_third_party_support( $addons, $post_id ) {

		// Key = plugin slug; value = plugin AJAX action prefix.
		$addons['envira-dropbox-importer'] = 'envira_dropbox_importer';
		return $addons;

	}

	/**
	 * Adds media view (modal) strings for this addon
	 *
	 * @since 1.0.0
	 *
	 * @param    array $strings    Media View Strings.
	 * @return   array               Media View Strings
	 */
	public function media_view_strings( $strings ) {

		$strings['envira-dropbox-importer'] = __( 'Insert from Dropbox', 'envira-dropbox-importer' );
		return $strings;

	}

	/**
	 * Outputs backbone.js wp.media compatible templates, which are loaded into the modal
	 * view
	 *
	 * @since 1.0.3
	 */
	public function print_media_templates() {

		// Side Bar.
		?>
		<script type="text/html" id="tmpl-envira-dropbox-importer-side-bar">
			<div class="media-sidebar">
				<div class="envira-gallery-meta-sidebar">
					<h3><?php esc_html_e( 'Helpful Tips', 'envira-dropbox-importer' ); ?></h3>
					<strong><?php esc_html_e( 'Importing Dropbox Images', 'envira-dropbox-importer' ); ?></strong>
					<p>
						<?php esc_html_e( 'Select the images you would like to import into your Envira Gallery.  You can also click on individual folders to then choose images within those folders.', 'envira-dropbox-importer' ); ?>
					</p>
					<p>
						<?php esc_html_e( 'Once you have selected your images, click on the', 'envira-dropbox-importer' ); ?>
						<i><?php esc_html_e( 'Insert into Gallery', 'envira-dropbox-importer' ); ?></i> 
						<?php esc_html_e( 'button.  Envira will then download these images and store them in this Gallery.', 'envira-dropbox-importer' ); ?>
					</p>
					<h3>Performance Tip:</h3>
					<p>
						<?php esc_html_e( 'If loading your Dropbox directories is taking an excessive amount of time, see our', 'envira-dropbox-importer' ); ?>
						<a target="_blank" href="http://enviragallery.com/docs/dropbox-importer-addon/#envira-gallery-dropbox-addon-FAQ"><?php esc_html_e( 'online documentation', 'envira-dropbox-importer' ); ?></a>.
					</p>
				</div>
			</div>
		</script>
		<?php

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Dropbox_Importer_Media_View object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Dropbox_Importer_Media_View ) ) {
			self::$instance = new Envira_Dropbox_Importer_Media_View();
		}

		return self::$instance;

	}

}

// Load the media class.
$envira_dropbox_importer_media_view = Envira_Dropbox_Importer_Media_View::get_instance();
