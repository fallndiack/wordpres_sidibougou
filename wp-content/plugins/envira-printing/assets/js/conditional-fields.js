/**
 * Handles showing and hiding fields conditionally
 */
jQuery( document ).ready(
	function( $ ) {

			// Show/hide elements as necessary when a conditional field is changed
			$( '#envira-gallery-settings input:not([type=hidden]), #envira-gallery-settings select, #envira-albums-settings input:not([type=hidden]), #envira-albums-settings select' ).conditions(
				[

				{	// Printing Button Elements Dependant on Theme
					conditions: [
					{
						element: '[name="_envira_gallery[lightbox_theme]"], [name="_eg_album_data[config][lightbox_theme]"]',
						type: 'value',
						operator: 'array',
						condition: [ 'base', 'captioned', 'polaroid', 'showcase', 'sleek', 'subtle' ]
					},
					{
						element: '[name="_envira_gallery[print_lightbox]"], [name="_eg_album_data[config][print_lightbox]"]',
						type: 'checked',
						operator: 'is'
					}
					],
					actions: {
						if : {
							element: '#envira-config-print-lightbox-position-box',
							action: 'show'
						},
						else : {
							element: '#envira-config-print-lightbox-position-box',
							action: 'hide'
						}
					}
				},
				{	// Printing Button Elements Independant on Theme
					conditions: [
					{
						element: '[name="_envira_gallery[print]"], [name="_eg_album_data[config][print]"]',
						type: 'checked',
						operator: 'is'
					}
					],
					actions: {
						if : {
							element: '#envira-config-print-position-box',
							action: 'show'
						},
						else : {
							element: '#envira-config-print-position-box',
							action: 'hide'
						}
					}
				}

				]
			);

	}
);
