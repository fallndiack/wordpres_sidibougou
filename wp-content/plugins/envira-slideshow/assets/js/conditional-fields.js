/**
 * Handles showing and hiding fields conditionally
 */
jQuery( document ).ready(
	function ($) {

		// Show/hide elements as necessary when a conditional field is changed
		$( '#envira-gallery-settings input:not([type=hidden]), #envira-gallery-settings select, #envira-albums-settings input:not([type=hidden]), #envira-albums-settings select' ).conditions(
			[

			{   // Slideshow Elements
				conditions: [
					{
						element: '[name="_envira_gallery[slideshow]"], [name="_eg_album_data[config][slideshow]"]',
						type: 'checked',
						operator: 'is'
				}
				],
				actions: {
					if : {
						element: '#envira-config-slideshow-autoplay-box, #envira-config-slideshow-speed-box, #envira-config-slideshow-hover-box',
						action: 'show'
					},
					else : {
						element: '#envira-config-slideshow-autoplay-box, #envira-config-slideshow-speed-box, #envira-config-slideshow-hover-box',
						action: 'hide'
					}
				}
			}

			]
		);

	}
);
