jQuery( document ).ready(
	function( $ ) {

			// Global vars we'll use
			var envira_defaults_url;

			/**
			* New Gallery / Album: When the 'Add New' option for an Envira Gallery or Album is clicked, display a modal
			* to give the user an option to select a Gallery / Album or use the Envira Defaults config.
			*/
			$( "a[href$='post-new.php?post_type=envira'], a[href$='post-new.php?post_type=envira_album']" ).on(
				'click',
				function( e ) {

					// If this modal is disabled, don't do anything
					if ( parseInt( envira_defaults.disable_modal ) == 1 ) {
						 return true;
					}

					// Prevent default action
					e.preventDefault();

					// Get the link target, as we will use this to load the Add New screen later
					envira_defaults_url = $( this ).attr( 'href' );

					// Determine the action
					var action = ( ( $( this ).attr( 'href' ).search( 'envira_album' ) > -1 ) ? 'album' : 'gallery' ),
					default_id = ( ( $( this ).attr( 'href' ).search( 'envira_album' ) > -1 ) ? envira_defaults.album_default_id : envira_defaults.gallery_default_id );

					// Define the modal's view
					EnviraGalleryModalWindow.content(
						new EnviraGallerySelectionView(
							{
								action:              action,
								multiple:            false,
								sidebar_view:        'envira-defaults-' + action + '-sidebar',
								modal_title:         ( ( action == 'album' ) ? envira_defaults.album_modal_title : envira_defaults.gallery_modal_title ),
								insert_button_label: ( ( action == 'album' ) ? envira_defaults.album_modal_button_label : envira_defaults.gallery_modal_button_label ),
								prepend_ids:         [ parseInt( default_id ) ],
								select_ids:          [ parseInt( default_id ) ],
								onInsert: function() {
									this.selection.forEach(
										function( item ) {
											// Redirect to the Add New screen with the Gallery / Album ID as the default config ID to inherit.
											window.location = envira_defaults_url + '&envira_defaults_config_id=' + item.id;
											return true;
										}
									);
								}
							}
						)
					);

					// Open the modal window
					EnviraGalleryModalWindow.open();

				}
			);

			/**
			* Bulk Actions: When the user chooses the "Apply Defaults" option from the Bulk Actions dropdown on the WP_List_Table
			* and they click Apply, display a modal to give the user an option to copy the config from another Gallery or use the
			* Envira Defaults config.
			*/
			$( 'body.post-type-envira, body.post-type-envira_album' ).on(
				'click',
				'input#doaction, input#doaction2',
				function( e ) {

					// Get action based on the input button clicked, and check it belongs to this Addon.
					var input_button_id = $( this ).attr( 'id' );
					switch ( input_button_id ) {
						case 'doaction':
							// Check the action matches envira-defaults
							var action = $( 'select[name=action]' ).val();
							if ( action != 'envira-defaults' ) {
								  return;
							}
							  break;
						case 'doaction2':
							// Check the action matches envira-defaults
							var action = $( 'select[name=action2]' ).val();
							if ( action != 'envira-defaults' ) {
								  return;
							}
							  break;

					}

					// Check that at least one gallery / album has been selected in the WP_List_Table
					var id   = $( 'select', $( this ) ).val(),
					post_ids = [];

					// Get list of selected Galleries / Albums
					$( 'tbody#the-list input[type=checkbox]:checked' ).each(
						function( i ) {
							post_ids.push( $( this ).val() );
						}
					);

					// If no Galleries / Albums selected, bail
					if ( post_ids.length == 0 ) {
						return false;
					}

					// Prevent default action
					e.preventDefault();

					// Determine the action and default ID
					var action = ( ( $( 'body.post-type-envira_album' ).length > 0 ) ? 'album' : 'gallery' ),
					default_id = ( ( 'album' == action ) ? envira_defaults.album_default_id : envira_defaults.gallery_default_id );

					// Define the modal's view
					EnviraGalleryModalWindow.content(
						new EnviraGallerySelectionView(
							{
								action:              action,
								multiple:            false,
								sidebar_view:        'envira-defaults-' + action + '-bulk-action-sidebar',
								modal_title:         ( ( action == 'album' ) ? envira_defaults.album_bulk_action_modal_title : envira_defaults.gallery_bulk_action_modal_title ),
								insert_button_label: ( ( action == 'album' ) ? envira_defaults.album_bulk_action_modal_button_label : envira_defaults.gallery_bulk_action_modal_button_label ),
								prepend_ids:         [ parseInt( default_id ) ],
								select_ids:          [ parseInt( default_id ) ],
								onInsert: function() {
									this.selection.forEach(
										function( item ) {

											// Clear any existing messages
											$( '#message' ).remove();

											// Perform an AJAX request to change the config of the selected Galleries / Albums
											$.ajax(
												{
													url:        ajaxurl,
													type:       'post',
													async:      true,
													cache:      false,
													data: {
														action:     'envira_defaults_apply',
														nonce:      envira_defaults.nonce,
														id:         item.id,
														post_ids:   post_ids,
														post_type:  action // gallery|album
													},
													success: function( response ) {

														// Unselect the previously selected items
														$( 'tbody#the-list input[type=checkbox]' ).prop( 'checked', false );

														// Display a message to tell the user the action succeeded
														$( 'div.wrap > h1' ).after( '<div id="message" class="updated notice is-dismissible"><p>Settings applied successfully!</p></div>' );

														// Add a base class to help keep some Envira custom UI inplace
														$( 'body' ).addClass( 'envira-default-success' );

														// Close modal
														EnviraGalleryModalWindow.close();

													},
													error: function( xhr, textStatus, e ) {

														// Display error
														$( 'div.wrap > h1' ).after( '<div id="message" class="error notice is-dismissible"><p>Error: ' + textStatus + '</p></div>' );

														// Close modal
														EnviraGalleryModalWindow.close();

													}
												}
											);
										}
									);
								}
							}
						)
					);

					// Open the modal window
					EnviraGalleryModalWindow.open();

				}
			);

	}
);
