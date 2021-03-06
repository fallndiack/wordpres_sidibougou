/**
 * Handles showing and hiding fields conditionally
 */
jQuery( document ).ready(
	function( $ ) {

			// Show/hide elements as necessary when a conditional field is changed
			$( '#envira-gallery-settings input:not([type=hidden]), #envira-gallery-settings select, #envira-albums-settings input:not([type=hidden]), #envira-albums-settings select' ).conditions(
				[
				{	// Social Elements
					conditions: {
						element: '[name="_envira_gallery[social]"], [name="_eg_album_data[config][social]"]',
						type: 'checked',
						operator: 'is'
					},
					actions: {
						if : {
							element: '#envira-config-social-networks-box, #envira-config-social-position-box, #envira-config-social-orientation-box, #envira-config-social-mobile-box',
							action: 'show'
						},
						else : {
							element: '#envira-config-social-networks-box, #envira-config-social-position-box, #envira-config-social-orientation-box, #envira-config-social-mobile-box',
							action: 'hide'
						}
					}
				},
				{	// Social Elements Independant of Theme, Dependant on Social Icons
					conditions: [
					// {
					// element: '[name="_envira_gallery[social]"]',
					// type: 'checked',
					// operator: 'is'
					// },
					// {
					// element: '[name="_envira_gallery[social_twitter]"]',
					// type: 'checked',
					// operator: 'is'
					// },
					{
						element: '[name="_general[social_twitter_sharing_method]"]',
						type: 'value',
						operator: 'array',
						condition: [ 'card', 'card-photo' ]
					}
					],
					actions: {
						if : {
							element: '#envira-config-social-networks-twitter-summary-card-site, #envira-config-social-networks-twitter-summary-card-desc',
							action: 'show'
						},
						else : {
							element: '#envira-config-social-networks-twitter-summary-card-site, #envira-config-social-networks-twitter-summary-card-desc',
							action: 'hide'
						}
					}
				},
				{	// Social Elements Independant of Theme, Dependant on Social Icons
					conditions: [
					// {
					// element: '[name="_envira_gallery[social]"]',
					// type: 'checked',
					// operator: 'is'
					// },
					// {
					// element: '[name="_general[social_facebook]"]',
					// type: 'checked',
					// operator: 'is'
					// },
					{
						element: '[name="_general[social_facebook_show_option_optional_text]"]',
						type: 'checked',
						operator: 'is'
					}
					],
					actions: {
						if : {
							element: '#envira-config-social-networks-facebook-box',
							action: 'show'
						},
						else : {
							element: '#envira-config-social-networks-facebook-box',
							action: 'hide'
						}
					}
				},
				{	// Social Elements Independant of Theme, Dependant on Social Icons
					conditions: [
					// {
					// element: '[name="_envira_gallery[social]"]',
					// type: 'checked',
					// operator: 'is'
					// },
					// {
					// element: '[name="_envira_gallery[social_facebook]"]',
					// type: 'checked',
					// operator: 'is'
					// },
					{
						element: '[name="_general[social_facebook_show_option_quote]"]',
						type: 'checked',
						operator: 'is'
					}
					],
					actions: {
						if : {
							element: '#envira-config-social-networks-facebook-quote',
							action: 'show'
						},
						else : {
							element: '#envira-config-social-networks-facebook-quote',
							action: 'hide'
						}
					}
				},
				{	// Social Elements Independant of Theme, Dependant on Social Icons
					conditions: [
					// {
					// element: '[name="_envira_gallery[social]"]',
					// type: 'checked',
					// operator: 'is'
					// },
					// {
					// element: '[name="_envira_gallery[social_facebook]"]',
					// type: 'checked',
					// operator: 'is'
					// },
					{
						element: '[name="_general[social_facebook_show_option_tags]"]',
						type: 'checked',
						operator: 'is'
					}
					],
					actions: {
						if : {
							element: '#envira-config-social-networks-facebook-tags-options',
							action: 'show'
						},
						else : {
							element: '#envira-config-social-networks-facebook-tags-options',
							action: 'hide'
						}
					}
				},
				{	// Social Elements Independant of Theme, Dependant on Social Icons
					conditions: [
					// {
					// element: '[name="_envira_gallery[social]"]',
					// type: 'checked',
					// operator: 'is'
					// },
					// {
					// element: '[name="_envira_gallery[social_facebook]"]',
					// type: 'checked',
					// operator: 'is'
					// },
					{
						element: '[name="_general[social_facebook_show_option_tags]"]',
						type: 'checked',
						operator: 'is'
					},
					{
						element: '[name="_general[social_facebook_tag_options]"]',
						type: 'value',
						operator: 'array',
						condition: [ 'manual' ]
					}
					],
					actions: {
						if : {
							element: '#envira-config-social-networks-facebook-tags-options-manual',
							action: 'show'
						},
						else : {
							element: '#envira-config-social-networks-facebook-tags-options-manual',
							action: 'hide'
						}
					}
				},
				{	// Social Elements Dependant on Theme
					conditions: [
					{
						element: '[name="_envira_gallery[lightbox_theme]"], [name="_eg_album_data[config][lightbox_theme]"]',
						type: 'value',
						operator: 'array',
						condition: [ 'base', 'captioned', 'polaroid', 'showcase', 'sleek', 'subtle' ]
					},
					{
						element: '[name="_envira_gallery[social_lightbox]"], [name="_eg_album_data[config][social_lightbox]"]',
						type: 'checked',
						operator: 'is'
					}
					],
					actions: {
						if : {
							element: '#envira-config-social-lightbox-position-box, #envira-config-social-lightbox-outside-box, #envira-config-social-lightbox-orientation-box',
							action: 'show'
						},
						else : {
							element: '#envira-config-social-lightbox-position-box, #envira-config-social-lightbox-outside-box, #envira-config-social-lightbox-orientation-box',
							action: 'hide'
						}
					}
				},
				{	// Advanced Settings
					conditions: [
					{
						element: '[name="_envira_gallery[social]"],[name="_eg_album_data[config][social]"],[name="_envira_gallery[social_lightbox]"],[name="_eg_album_data[config][social_lightbox]"]',
						type: 'checked',
						operator: 'is'
					}
					],
					actions: {
						if : {
							element: '#envira-social-advanced-settings',
							action: 'show'
						},
						else : {
							element: '#envira-social-advanced-settings',
							action: 'hide'
						}
					}
				},
				{	// Social Elements Independant of Theme
					conditions: [
					{
						element: '[name="_envira_gallery[social_lightbox]"],[name="_eg_album_data[config][social_lightbox]"]',
						type: 'checked',
						operator: 'is'
					}
					],
					actions: {
						if : {
							element: '#envira-config-social-lightbox-networks-box',
							action: 'show'
						},
						else : {
							element: '#envira-config-social-lightbox-networks-box',
							action: 'hide'
						}
					}
				},
				{	// Mobile - Gallery
					conditions: [
					{
						element: '[name="_envira_gallery[mobile_social]"],[name="_eg_album_data[config][mobile_social]"]',
						type: 'checked',
						operator: 'is'
					}
					],
					actions: {
						if : {
							element: '#envira-config-social-networks-mobile-box',
							action: 'show'
						},
						else : {
							element: '#envira-config-social-networks-mobile-box',
							action: 'hide'
						}
					}
				},
				{	// Mobile - Lightbox
					conditions: [
					{
						element: '[name="_envira_gallery[mobile_social]"],[name="_eg_album_data[config][mobile_social]"]',
						type: 'checked',
						operator: 'is'
					}
					],
					actions: {
						if : {
							element: '#envira-config-social-networks-mobile-box',
							action: 'show'
						},
						else : {
							element: '#envira-config-social-networks-mobile-box',
							action: 'hide'
						}
					}
				},
				{	// Mobile - Lightbox
					conditions: [
					{
						element: '[name="_envira_gallery[social_lightbox]"],[name="_eg_album_data[config][social_lightbox]"]',
						type: 'checked',
						operator: 'is'
					}
					],
					actions: {
						if : {
							element: '#envira-config-social-lightbox-mobile-box',
							action: 'show'
						},
						else : {
							element: '#envira-config-social-lightbox-mobile-box',
							action: 'hide'
						}
					}
				},
				{ 
					conditions: [
						{
							element: '[name="_envira_gallery[mobile_social_lightbox]"],[name="_eg_album_data[config][mobile_social_lightbox]"]',
							type: 'checked',
							operator: 'is'
						},
						{
							element: '[name="_envira_gallery[social_lightbox]"],[name="_eg_album_data[config][social_lightbox]"]',
							type: 'checked',
							operator: 'is'
						}
					],
					actions: {
						if : {
							element: '#envira-config-social-networks-lightbox-mobile-box',
							action: 'show'
						},
						else : {
							element: '#envira-config-social-networks-lightbox-mobile-box',
							action: 'hide'
						}
					}
				},

				]
			);

	}
);
