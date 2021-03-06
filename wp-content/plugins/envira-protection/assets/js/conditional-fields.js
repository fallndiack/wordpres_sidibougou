/**
 * Handles showing and hiding fields conditionally
 */
jQuery( document ).ready(
	function( $ ) {

			// Show/hide elements as necessary when a conditional field is changed
			$( '#envira-gallery-settings input:not([type=hidden]), #envira-gallery-settings select, #envira-albums-settings input:not([type=hidden]), #envira-albums-settings select' ).conditions(
				[
				{
					conditions: [
					{
						element: '[name="_envira_gallery[protection]"], [name="_eg_album_data[config][protection]"]',
						type: 'checked',
						operator: 'is'
					}
					],
					actions: {
						if : {
							element: '#envira-config-protection-popup',
							action: 'show'
						},
						else : {
							element: '#envira-config-protection-popup',
							action: 'hide'
						}
					}
				},
				{
					conditions: [
					{
						element: '[name="_envira_gallery[protection_popup]"], [name="_eg_album_data[config][protection]"]',
						type: 'checked',
						operator: 'is'
					}
					],
					actions: {
						if : {
							element: '#envira-config-protection-box-message, #envira-config-protection-box-title, #envira-config-protection-box-button',
							action: 'show'
						},
						else : {
							element: '#envira-config-protection-box-message, #envira-config-protection-box-title, #envira-config-protection-box-button',
							action: 'hide'
						}
					}
				},
				]
			);

	}
);
