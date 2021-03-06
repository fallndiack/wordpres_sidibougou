/**
 * Handles showing and hiding fields conditionally
 */
jQuery( document ).ready(
	function( $ ) {

			// Show/hide elements as necessary when a conditional field is changed
			$( '#envira-gallery-settings input:not([type=hidden]), #envira-gallery-settings select, #envira-albums-settings input:not([type=hidden]), #envira-albums-settings select' ).conditions(
				[

				{	// Exif Elements Independant of Theme
					conditions: {
						element: '[name="_envira_gallery[exif]"], [name="_eg_album_data[config][exif]"]',
						type: 'checked',
						operator: 'is'
					},
					actions: {
						if : {
							element: '#envira-config-exif-metadata-box, #envira-config-exif-mobile-box',
							action: 'show'
						},
						else : {
							element: '#envira-config-exif-metadata-box, #envira-config-exif-mobile-box',
							action: 'hide'
						}
					}
				},
				{	// Exif Elements Independant of Theme
					conditions: {
						element: '[name="_envira_gallery[exif_lightbox]"], [name="_eg_album_data[config][exif_lightbox]"]',
						type: 'checked',
						operator: 'is'
					},
					actions: {
						if : {
							element: '#envira-config-exif-lightbox-metadata-box, #envira-config-exif-lightbox-position-box, #envira-config-exif-lightbox-outside-box',
							action: 'show'
						},
						else : {
							element: '#envira-config-exif-lightbox-metadata-box, #envira-config-exif-lightbox-position-box, #envira-config-exif-lightbox-outside-box',
							action: 'hide'
						}
					}
				},
				// {
				// 	conditions: {
				// 		element: '[name="_envira_gallery[exif_capture_time]"], [name="_eg_album_data[config][exif_capture_time]"]',
				// 		type: 'checked',
				// 		operator: 'is'
				// 	},
				// 	actions: {
				// 		if : {
				// 			element: '#envira-config-exif-capture-time-format',
				// 			action: 'show'
				// 		},
				// 		else : {
				// 			element: '#envira-config-exif-capture-time-format',
				// 			action: 'hide'
				// 		}
				// 	}
				// },
				{ 
					conditions: [
						{
							element: '[name="_envira_gallery[exif]"], [name="_eg_album_data[config][exif]"]',
							type: 'checked',
							operator: 'is'
						},
						{
							element: '[name="_envira_gallery[exif_capture_time]"], [name="_eg_album_data[config][exif_capture_time]"]',
							type: 'checked',
							operator: 'is'
						}
					],
					actions: {
						if : {
							element: '#envira-config-exif-capture-time-format',
							action: 'show'
						},
						else : {
							element: '#envira-config-exif-capture-time-format',
							action: 'hide'
						}
					}
				},
				{ 
					conditions: [
						{
							element: '[name="_envira_gallery[exif_lightbox]"], [name="_eg_album_data[config][exif_lightbox]"]',
							type: 'checked',
							operator: 'is'
						},
						{
							element: '[name="_envira_gallery[exif_lightbox_capture_time]"], [name="_eg_album_data[config][exif_lightbox_capture_time]"]',
							type: 'checked',
							operator: 'is'
						}
					],
					actions: {
						if : {
							element: '#envira-config-exif-lightbox-capture-time-format',
							action: 'show'
						},
						else : {
							element: '#envira-config-exif-lightbox-capture-time-format',
							action: 'hide'
						}
					}
				},


				]
			);

	}
);
