/**
 * Handles showing and hiding fields conditionally
 */
jQuery( document ).ready(
	function( $ ) {

			// Show/hide elements as necessary when a conditional field is changed
			$( '#envira-gallery-settings input:not([type=hidden]), #envira-gallery-settings select' ).conditions(
				[

				{	// Instagram elements
					conditions: {
						element: '[name="_envira_gallery[instagram_caption]"]',
						type: 'checked',
						operator: 'is'
					},
					actions: {
						if : [
						{
							element: '#envira-config-instagram-caption-limit-box',
							action: 'show'
						}
						],
						else : [
						{
							element: '#envira-config-instagram-caption-limit-box',
							action: 'hide'
						}
						]
					}
				}

				]
			);

	}
);
