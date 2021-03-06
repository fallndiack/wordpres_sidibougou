/**
 * Handles showing and hiding fields conditionally
 */
jQuery( document ).ready(
	function( $ ) {

			// Show/hide elements as necessary when a conditional field is changed
			$( '#envira-albums-settings input:not([type=hidden]), #envira-albums-settings select, #envira-gallery-settings input:not([type=hidden]), #envira-gallery-settings select' ).conditions(
				[

				{	// Gallery Elements
					conditions: {
						element: '[name="_eg_album_data[config][pagination]"], [name="_envira_gallery[pagination]"]',
						type: 'checked',
						operator: 'is'
					},
					actions: {
						if : [
						{
							element: '#envira-pagination-lightbox-settings, #envira-config-pagination-display-all-images, #envira-config-pagination-position-box, #envira-config-pagination-posts-per-page-box, #envira-config-pagination-prev-next-box, #envira-config-pagination-prev-text-box, #envira-config-pagination-next-text-box, #envira-config-pagination-scroll-box, #envira-config-pagination-ajax-load-box, #envira-config-pagination-mobile-sub-heading, #envira-config-pagination-mobile-images-per-page-box, #envira-config-pagination-mobile-prev-next-box',
							action: 'show'
						}
						],
						else : [
						{
							element: '#envira-pagination-lightbox-settings, #envira-config-pagination-display-all-images, #envira-config-pagination-position-box, #envira-config-pagination-posts-per-page-box, #envira-config-pagination-prev-next-box, #envira-config-pagination-prev-text-box, #envira-config-pagination-next-text-box, #envira-config-pagination-scroll-box, #envira-config-pagination-ajax-load-box, #envira-config-pagination-mobile-sub-heading, #envira-config-pagination-mobile-images-per-page-box, #envira-config-pagination-mobile-prev-next-box',
							action: 'hide'
						}
						]
					}
				},
				{
					conditions: [
					{
						element: '[name="_eg_album_data[config][pagination_ajax_load]"], [name="_envira_gallery[pagination_ajax_load]"]',
						type: 'value',
						operator: 'array',
						condition: [ '3' ]
					},
					{
						element: '[name="_eg_album_data[config][pagination]"], [name="_envira_gallery[pagination]"]',
						type: 'checked',
						operator: 'is'
					},
					],
					actions: {
						if : [
						{
							element: '#envira-config-pagination-load-more-text',
							action: 'show'
						}
						],
						else : [
						{
							element: '#envira-config-pagination-load-more-text',
							action: 'hide'
						}
						]
					}
				},

				]
			);

	}
);
