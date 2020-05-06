jQuery( document ).ready(
	function($) {
		/**
		* Settings
		*/
		if ($( 'tr#envira-config-tags-filtering-box' ).length > 0) {
			tagBox.init();
		}
		$( 'body' ).on(
			'click',
			'input#publish, input#save-post',
			function() {
				// Get tags div
				var container         = $( 'tr#envira-config-tags-filtering-box' );
				var tagsDiv           = $( 'div.tagsdiv', $( container ) );
				var finalTagsTextArea = $( 'textarea.the-tags', $( container ) ); // tagBox.flushTags stores tags in this hidden textarea

				// Flush tags into hidden textarea
				tagBox.flushTags( tagsDiv, false, 1 );

				// Map textarea to hidden input field
				$( 'input.envira-gallery-tags', $( container ) ).val( $( finalTagsTextArea ).val() );
			}
		);

		// Most Popular Tags
		$( 'tr#envira-config-tags-filtering-box .the-tagcloud a' ).on(
			'click',
			function( e ) {

				e.preventDefault();

				var container     = $( this ).closest( 'td' ),
				tag_input         = $( 'input.newtag', $( container ) ),
				tag_input_val     = $( tag_input ).val(),
				tag               = $( this ).text(),
				tag_input_val_new = ( tag_input_val == '' ) ? tag : tag_input_val + ',' + tag;

				// Add tag to tag input
				$( tag_input ).val( tag_input_val_new );

			}
		);

		/**
		 * Settings: Manual Sorting Order
		 */
		var envira_tags_sortable = $( 'ul#envira-tags-order' );
		envira_tags_sortable.sortable(
			{
				update: function( event ) {
					// Get the new sort order and update the hidden field
					var sort_order = envira_tags_sortable.sortable( 'toArray' ).toString();
					$( 'input[name="_envira_gallery[tags_manual_sorting]"], input[name="_eg_album_data[config][tags_manual_sorting]"]' ).val( sort_order );
				}
			}
		).disableSelection();

		/**
		* Modal
		*/
		// Initialise Post Tagging JS on modal load when the info icon on an image is clicked
		$( '#envira-gallery' ).on(
			'click.enviraModify',
			'.envira-gallery-modify-image',
			function(e){
				// Init the tagging JS
				tagBox.init();
			}
		);

		// Most Popular Tags
		$( '.envira-gallery-media-frame .the-tagcloud a' ).on(
			'click',
			function( e ) {

				e.preventDefault();

				var container     = $( this ).closest( 'label.envira-tags' ),
				tag_input         = $( 'input.newtag', $( container ) ),
				tag_input_val     = $( tag_input ).val(),
				tag               = $( this ).text(),
				tag_input_val_new = ( tag_input_val == '' ) ? tag : tag_input_val + ',' + tag;

				// Add tag to tag input
				$( tag_input ).val( tag_input_val_new );

			}
		);

		// Save tags to hidden WP field on save
		$( 'body' ).on(
			'click',
			'.envira-gallery-meta-submit',
			function() {
				// Get tags div for the modal window we have open
				var container         = $( this ).closest( '.media-modal-content' );
				var tagsDiv           = $( 'div.tagsdiv', $( container ) );
				var finalTagsTextArea = $( 'textarea.the-tags', $( container ) ); // tagBox.flushTags stores tags in this hidden textarea

				// Flush tags into hidden textarea
				tagBox.flushTags( tagsDiv, false, 1 );

				// Envira only sends fields with data-envira-meta set, so map the populated textarea
				// to our hidden field
				$( 'input.envira-gallery-tags' ).val( $( finalTagsTextArea ).val() );
			}
		);
	}
);
