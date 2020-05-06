<?php
/**
 * Settings admin class.
 *
 * @since 1.3.0
 *
 * @package Envira_Albums
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

namespace Envira\Albums\Admin;

/**
 * Settings admin class.
 *
 * @since 1.3.0
 *
 * @package Envira_Albums
 * @author  Envira Gallery Team <support@enviragallery.com>
 */
class Settings {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.3.0
	 */
	public function __construct() {

		// NextGEN Importer Addon Support.
		add_filter( 'envira_nextgen_importer_settings_tab_nav', array( $this, 'nextgen_settings_register_tabs' ) );
		add_action( 'envira_nextgen_importer_tab_settings_albums', array( $this, 'nextgen_settings_tab' ) );

	}

	/**
	 * Adds an Albums Tab to the NextGEN Importer Settings Screen
	 *
	 * @since 1.3.0
	 *
	 * @param   array $tabs   Tabs.
	 * @return  array           Tabs
	 */
	public function nextgen_settings_register_tabs( $tabs ) {

		$tabs['albums'] = __( 'Albums', 'envira-nextgen-importer' );
		return $tabs;

	}

	/**
	 * Callback for displaying the UI for the Albums Settings tab in the NextGEN Importer.
	 *
	 * @since 1.3.0
	 */
	public function nextgen_settings_tab() {

		// Check and see if NextGEN is installed... if not, do not attempt to display settings and instead report an error.
		if ( ! is_plugin_active( 'nextgen-gallery/nggallery.php' ) ) { ?>
			<div id="envira-nextgen-importer-settings-galleries">
				<p>Please install and activate the <a href="https://wordpress.org/plugins/nextgen-gallery/" target="_blank">NextGEN Gallery plugin</a> before using this addon.</p>
			</div>
			<?php
			return;
		}

		// Get NextGEN Albums.
		$albums = \Envira_Nextgen_Wrapper::get_instance()->get_albums();

		// Get settings (contains imported albums).
		$settings = get_option( 'envira_nextgen_importer' );
		?>

		<!-- Progress Bar -->
		<div id="album-progress"><div id="album-progress-label"></div></div>

		<div id="envira-nextgen-importer-settings-albums">
			<form id="envira-nextgen-importer-albums" method="post">
				<table class="form-table">
					<tbody>
						<tr id="envira-settings-key-box">
							<th scope="row">
								<label for="envira-settings-key"><?php esc_html_e( 'Albums to Import', 'envira-nextgen-importer' ); ?></label>
							</th>
							<td>
								<?php
								if ( false !== $albums ) {
									foreach ( $albums as $album ) {
										// Check if album imported from NextGEN previously.
										$imported = ( ( isset( $settings['albums'] ) && isset( $settings['albums'][ $album->id ] ) ) ? true : false );
										?>
										<label for="albums-<?php echo esc_attr( $album->id ); ?>" data-id="<?php echo esc_attr( $album->id ); ?>"<?php echo ( $imported ? ' class="imported"' : '' ); ?>>
											<input type="checkbox" name="albums" id="albums-<?php echo esc_attr( $album->id ); ?>" value="<?php echo esc_attr( $album->id ); ?>" />
											<?php echo esc_html( $album->name ); ?>
											<span>
												<?php
												if ( $imported ) {
													// Already imported.
													esc_html_e( 'Imported', 'envira-nextgen-importer' );
												}
												?>
											</span>
										</label>
										<?php
									}
								}
								?>
							</td>
						</tr>
						<tr>
							<th scope="row">
								&nbsp;
							</th>
							<td>
								<?php
								submit_button( __( 'Import Albums', 'envira-nextgen-importer' ), 'primary', 'envira-gallery-verify-submit', false );
								?>
							</td>
						</tr>
						<?php do_action( 'envira_nextgen_importer_settings_albums_box' ); ?>
					</tbody>
				</table>
			</form>
		</div>
		<?php

	}

}
