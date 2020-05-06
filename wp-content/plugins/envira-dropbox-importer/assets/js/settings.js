jQuery( document ).ready(
	function( $ ) {

			// Confirm the user wants to unlink their Dropbox account
			$( 'a.envira-dropbox-importer-unlink' ).click(
				function(e) {
					if ( ! confirm( envira_dropbox_importer_settings.unlink ) ) {
						e.preventDefault();
						return false;
					}
				}
			);

	}
);
