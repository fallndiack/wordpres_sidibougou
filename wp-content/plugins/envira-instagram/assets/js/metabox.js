// @codekit-append "conditional-fields.js";

jQuery( document ).ready(
	function( $ ) {

			// Toggle fields in Metabox
			var envira_instagram_images_type_toggle = function( val ) {
				switch ( val ) {
					case 'users_self_media_recent':
						$( '#envira-config-instagram-user-id-box' ).hide();
						$( '#envira-config-instagram-tag-box' ).hide();
				break;
					case 'users_self_media_liked':
						$( '#envira-config-instagram-user-id-box' ).hide();
						$( '#envira-config-instagram-tag-box' ).hide();
				break;
					case 'tags_tag_media_recent':
						$( '#envira-config-instagram-user-id-box' ).hide();
						$( '#envira-config-instagram-tag-box' ).show();
				break;
				}
			}
			envira_instagram_images_type_toggle( $( 'select#envira-config-instagram-type' ).val() );
			$( 'select#envira-config-instagram-type' ).on(
				'change',
				function() {
					envira_instagram_images_type_toggle( $( this ).val() );
				}
			);

			// Trigger the enviraGalleryPreview when any setting is changed
			$( document ).on(
				'change',
				'#envira-instagram select, #envira-instagram input',
				function( e ) {

					$( document ).trigger( 'enviraGalleryPreview' );

				}
			);

			// Hide some settings on the Config tab when the Instagram Gallery Type is selected
			var envira_instagram_type_toggle = function( val ) {

				if ( val == 'instagram' ) {
					// Hide some settings on the Config tab, as they're not used.
					$( '#envira-config-image-size-box' ).hide();
					$( '#envira-config-crop-size-box' ).hide();
					$( '#envira-config-crop-box' ).hide();
				} else {
					// Show some settings on the Config tab.
					$( '#envira-config-image-size-box' ).show();

					/* Check The Selected Image Size */
					if ( $( "#envira-config-image-size option:selected" ).val() !== "default" ) {
						$( '#envira-config-crop-size-box' ).hide();
						$( '#envira-config-crop-box' ).hide();
					} else {
						$( '#envira-config-crop-size-box' ).show();
						$( '#envira-config-crop-box' ).show();
					}
				}

			}
			envira_instagram_type_toggle( $( 'input[name="_envira_gallery[type]"]:checked' ).val() );
			$( 'input[name="_envira_gallery[type]"]' ).on(
				'change',
				function() {
					envira_instagram_type_toggle( $( 'input[name="_envira_gallery[type]"]:checked' ).val() );
				}
			);

	}
);
