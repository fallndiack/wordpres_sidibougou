/**
 * Handles showing and hiding fields conditionally
 */
jQuery( document ).ready(
	function( $ ) {

			// Show/hide elements as necessary when a conditional field is changed
			$( '#envira-tab-zoom input:not([type=hidden]), #envira-tab-zoom select' ).conditions(
				[

				{	// Enable Zoom
					conditions: {
						element: '[name*="[zoom]"]',
						type: 'checked',
						operator: 'is'
					},
					actions: {
						if : [
						{
							element: '#envira-config-zoom-settings-box, #envira-config-zoom-mobile-box',
							action: 'show'
						}
						],
						else : [
						{
							element: '#envira-config-zoom-settings-box, #envira-config-zoom-mobile-box',
							action: 'hide'
						}
						]
					}
				},
				{	// Zoom Type
					conditions: {
						element: '[name*="[zoom_type]"]',
						type: 'value',
						operator: 'array',
						condition: [ 'basic' ]
					},
					actions: {
						if : [
						{
							element: '#envira-config-zoom-window-position-box, #envira-config-zoom-window-size-box, #envira-config-zoom-tint-color-box, #envira-config-zoom-tint-color-opacity-box',
							action: 'show'
						}
						],
						else : [
						{
							element: '#envira-config-zoom-window-position-box, #envira-config-zoom-window-size-box, #envira-config-zoom-tint-color-box, #envira-config-zoom-tint-color-opacity-box',
							action: 'hide'
						}
						]
					}
				}

				]
			);

	}
);
