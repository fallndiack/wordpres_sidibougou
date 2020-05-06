/**
 * Handles showing and hiding fields conditionally
 */
jQuery( document ).ready(
	function( $ ) {

			// Show/hide elements as necessary when a conditional field is changed
			$( '#envira-gallery-settings input:not([type=hidden]), #envira-gallery-settings select, #envira-albums-settings input:not([type=hidden]), #envira-albums-settings select' ).conditions(
				[
				// {
				// 	conditions: {
				// 		element: '[name="_envira_gallery[fc_date_define]"]',
				// 		type: 'value',
				// 		operator: 'array',
				// 		condition: ['','0']
				// 	},
				// 	actions: {
				// 		if : {
				// 			element: '#envira-config-fc-age-box, #envira-config-fc-start-date-box, #envira-config-fc-end-date-box',
				// 			action: 'hide'
				// 		}
				// 	}
				// },
				// envira-config-fc-date-define-dropdown
				{
					conditions: {
						element: '[name="_envira_gallery[fc_date_define]"]',
						type: 'value',
						operator: 'array',
						condition: ['datetime']
					},
					actions: {
						if : {
							element: '#envira-config-fc-start-date-box, #envira-config-fc-end-date-box',
							action: 'show'
						},
						else : {
							element: '#envira-config-fc-start-date-box, #envira-config-fc-end-date-box',
							action: 'hide'
						}
					}
				},
				{
					conditions: {
						element: '[name="_envira_gallery[fc_date_define]"]',
						type: 'value',
						operator: 'array',
						condition: ['hours']
					},
					actions: {
						if : {
							element: '#envira-config-fc-age-box',
							action: 'show'
						},
						else : {
							element: '#envira-config-fc-age-box',
							action: 'hide'
						}
					}
				},
				]
			);

	}
);
