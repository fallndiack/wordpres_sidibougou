/**
 * View
 */
var EnviraVideosView = Backbone.View.extend(
	{

			/**
			 * The Tag Name and Tag's Class(es)
			 */
		tagName:    'div',
		className:  'envira-video',

			/**
			 * Template
			 * - The template to load inside the above tagName element
			 */
		template:   wp.template( 'envira-meta-editor-video' ),

			/**
			 * Events
			 */
		events: {
			// Update Model on input change
			// 'keyup input': 'updateItem',
			// 'keyup textarea': 'updateItem',
			'click .envira-insert-placeholder':  'insertPlaceholder',

		},

			/**
			 * Insert Placeholder Image from media library
			 */
		insertPlaceholder: function( event ){

			var envira_placeholder_frame,
				$model = this.model;

			event.preventDefault();

			var $button       = jQuery( event.currentTarget ),
				input_box_url = $button.parent().parent().find( 'input[name="video_thumbnail"]' ),
				input_box_id  = $button.parent().parent().find( 'input[name="video_thumbnail_id"]' );

			if ( envira_placeholder_frame ) {

				envira_placeholder_frame.open();

				return;

			}

				envira_placeholder_frame = wp.media.frames.envira_placeholder_frame = wp.media(
					{

						frame: 'select',
						library: {
							type: 'image'
						},
						title: 'Select A Thumbnail', // soliloquy_metabox.insert_placeholder,
						button: {
							text: 'Submit', // soliloquy_metabox.insert_placeholder,
						},
						contentUserSetting: false,
						multiple: false
						}
				);

					envira_placeholder_frame.on(
						'select',
						function() {

							attachment = envira_placeholder_frame.state().get( 'selection' ).first().toJSON();

							input_box_url.val( attachment.url );
							input_box_id.val( attachment.id );
							if ( attachment.url !== undefined && attachment.id !== undefined && input_box_url.val() !== undefined && input_box_url.val() !== '' ) {
								$model.set( 'src', attachment.url, { silent: true } );
								$model.set( 'thumbnail_id', attachment.id, { silent: true } );
								$model.set( 'thumbnail_url', input_box_url.val(), { silent: true } );
							}

						}
					);

					envira_placeholder_frame.open();

		},



			/**
			 * Initialize
			 */
		initialize: function( args ) {

			this.model = args.model;

		},

			/**
			 * Render
			 */
		render: function() {

			// Set the template HTML
			this.$el.html( this.template( this.model.attributes ) );

			return this;

		}

	}
);

// Add the view to the EnviraGalleryChildViews, so that it's loaded in the modal
EnviraGalleryChildViews.push( EnviraVideosView );

jQuery( document ).ready( 
	function( $ ) {

		$( 'body' ).on(
			'click',
			'.envira-gallery-accepted-urls > ul > li a',
			function(e) {
				e.preventDefault();
				if ( $( this ).hasClass( 'title-closed' ) ) {
					$( this ).removeClass( 'title-closed' );
					$( this ).addClass( 'title-opened' );
				} else if ( $( this ).hasClass( 'title-opened' ) ) {
					$( this ).removeClass( 'title-opened' );
					$( this ).addClass( 'title-closed' );
				} else {
					$( this ).addClass( 'title-opened' );
				}
				$( this ).parent().parent().find( 'ul' ).toggle();
			}
		);

	}
)
