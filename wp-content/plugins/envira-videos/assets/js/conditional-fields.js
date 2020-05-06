/**
 * Handles showing and hiding fields conditionally
 */
jQuery( document ).ready(
	function( $ ) {

			// Show/hide elements as necessary when a conditional field is changed
			$( '#envira-albums-settings input:not([type=hidden]), #envira-albums-settings select, #envira-gallery-settings input:not([type=hidden]), #envira-gallery-settings select' ).conditions(
				[

				{  // Main Video Setting Elements
					conditions: {
						element: '[name="_envira_gallery[videos_controls]"], [name="_eg_album_data[config][videos_controls]"]',
						type: 'checked',
						operator: 'is'
					},
					actions: {
						if : {
							element: '#envira-config-videos-playpause-box, #envira-config-videos-progress-box, #envira-config-videos-current-box, #envira-config-videos-duration-box, #envira-config-videos-volume-box, #envira-config-videos-fullscreen-box, #envira-config-videos-download-box',
							action: 'show'
						},
						else : {
							element: '#envira-config-videos-playpause-box, #envira-config-videos-progress-box, #envira-config-videos-current-box, #envira-config-videos-duration-box, #envira-config-videos-volume-box, #envira-config-videos-fullscreen-box, #envira-config-videos-download-box',
							action: 'hide'
						}
					}
				}

				]
			);

	}
);
