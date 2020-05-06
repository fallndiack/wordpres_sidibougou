<?php
/**
 * Media View class.
 *
 * @since 1.1.2
 *
 * @package Envira_Gallery
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media View class.
 *
 * @since 1.1.2
 *
 * @package Envira_Gallery
 * @author  Envira Team
 */
class Envira_Defaults_Media_View {

	/**
	 * Holds the class object.
	 *
	 * @since 1.1.2
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.1.2
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.1.2
	 */
	public function __construct() {

		add_action( 'print_media_templates', array( $this, 'print_media_templates' ) );

	}

	/**
	 * Outputs backbone.js wp.media compatible templates, which are loaded into the modal
	 * view
	 *
	 * @since 1.1.2
	 */
	public function print_media_templates() {

		// Defaults Gallery Sidebar.
		?>
		<script type="text/html" id="tmpl-envira-defaults-gallery-sidebar">
			<!-- Helpful Tips -->
			<h3><?php esc_html_e( 'Helpful Tips', 'envira-defaults' ); ?></h3>
			<p>
				<?php esc_html_e( 'Choose an existing Gallery to inherit the default configuration from, by clicking on the Gallery to the left.', 'envira-defaults' ); ?>
			</p>
			<p>
				<?php
					esc_html_e( 'Once you have chosen a Gallery, click the ', 'envira-defaults' );
					echo '<i>';
					esc_html_e( 'Create Gallery', 'envira-defaults' );
					echo '</i>';
					esc_html_e( ' button below. ', 'envira-defaults' );
				?>
			</p>
			<p>
				<?php esc_html_e( 'A new, blank Gallery will then be created, inheriting the settings from your chosen Gallery.', 'envira-defaults' ); ?>
			</p>
			<p>
				<?php esc_html_e( 'Don\'t want to see this window and just want to always inherit your Default Gallery configuration?', 'envira-defaults' ); ?>
				<a href="edit.php?post_type=envira&page=envira-gallery-settings#!envira-tab-defaults"><?php esc_html_e( 'Click here', 'envira-defaults' ); ?></a>
				<?php esc_html_e( 'to disable this selection window', 'envira-defaults' ); ?>
			</p>
		</script>

		<?php
		// Defaults Album Sidebar.
		?>
		<script type="text/html" id="tmpl-envira-defaults-album-sidebar">
			<!-- Helpful Tips -->
			<h3><?php esc_html_e( 'Helpful Tips', 'envira-defaults' ); ?></h3>
			<p>
				<?php esc_html_e( 'Choose an existing Album to inherit the default configuration from, by clicking on the Album to the left.', 'envira-defaults' ); ?>
			</p>
			<p>
				<?php esc_html_e( 'Once you have chosen an Album, click the', 'envira-defaults' ); ?>
				<em><?php esc_html_e( 'Create Album', 'envira-defaults' ); ?></em>
				<?php
				esc_html_e( 'button below.', 'envira-defaults' );
				?>
			</p>
			<p>
				<?php esc_html_e( 'A new, blank Album will then be created, inheriting the settings from your chosen Album.', 'envira-defaults' ); ?>
			</p>
			<p>
				<?php esc_html_e( 'Don\'t want to see this window and just want to always inherit your Default Album configuration?', 'envira-defaults' ); ?>
				<a href="edit.php?post_type=envira&page=envira-gallery-settings#!envira-tab-defaults">
				<?php
				esc_html_e( 'Click here', 'envira-defaults' );
				?>
				</a> 
				<?php
				esc_html_e( 'to disable this selection window.', 'envira-defaults' );
				?>
			</p>
		</script>
		<?php

		// Apply Bulk Action Defaults Gallery Sidebar.
		?>
		<script type="text/html" id="tmpl-envira-defaults-gallery-bulk-action-sidebar">
			<!-- Helpful Tips -->
			<h3><?php esc_html_e( 'Helpful Tips', 'envira-defaults' ); ?></h3>
			<p>
				<?php esc_html_e( 'Choose an existing Gallery by clicking on the Gallery to the left.', 'envira-defaults' ); ?>
			</p>
			<p>
				<?php esc_html_e( 'Once you have chosen a Gallery, click the <i>Apply Settings</i> button below.', 'envira-defaults' ); ?>
			</p>
			<p>
				<?php esc_html_e( 'The Galleries you selected in the table will have their settings updated to match the Gallery you have chosen here.', 'envira-defaults' ); ?>
			</p>
		</script>
		<?php

		// Apply Bulk Action Defaults Albums Sidebar.
		?>
		<script type="text/html" id="tmpl-envira-defaults-album-bulk-action-sidebar">
			<!-- Helpful Tips -->
			<h3><?php esc_html_e( 'Helpful Tips', 'envira-defaults' ); ?></h3>
			<p>
				<?php esc_html_e( 'Choose an existing Album by clicking on the Album to the left.', 'envira-defaults' ); ?>
			</p>
			<p>
				<?php esc_html_e( 'Once you have chosen a Album, click the <i>Apply Settings</i> button below.', 'envira-defaults' ); ?>
			</p>
			<p>
				<?php esc_html_e( 'The Albums you selected in the table will have their settings updated to match the Album you have chosen here.', 'envira-defaults' ); ?>
			</p>
		</script>
		<?php

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.1.2
	 *
	 * @return object The Envira_Defaults_Media_View object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Defaults_Media_View ) ) {
			self::$instance = new Envira_Defaults_Media_View();
		}

		return self::$instance;

	}

}

// Load the media view class.
$envira_downloads_media_view = Envira_Defaults_Media_View::get_instance();
