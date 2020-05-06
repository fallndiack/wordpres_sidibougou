/**
 * Initialises the Addon within Envira Gallery's
 * "Insert Images from Other Sources" modal.
 */
var EnviraDropboxImporter     = wp.media.view.MediaFrame.Post;
wp.media.view.MediaFrame.Post = EnviraDropboxImporter.extend(
	{

		/**
		 * Addon Slug
		 *
		 * @var string
		 */
		addon_slug: 'envira-dropbox-importer',

		/**
		 * Addon Action Base
		 *
		 * @var string
		 */
		addon_action_base: 'envira_dropbox_importer',

		/**
		 * Init
		 */
		initialize: function() {

			EnviraDropboxImporter.prototype.initialize.apply( this, arguments );

			// Add the Addon to the modal's left hand menu
			this.states.add(
				[
				new wp.media.controller.EnviraGalleryController(
					{
						id:         this.addon_slug,
						content: 	this.addon_slug + '-content',
						toolbar: 	this.addon_slug + '-toolbar',
						menu:       'default',
						title:      'Dropbox',
						priority:   200,
						type: 		'link',
						insert_action: this.addon_action_base + '_insert_images'
					}
				)
				]
			);

			// Main UI (where attachments are displayed)
			this.on( 'content:render:' + this.addon_slug + '-content', this.renderContent, this );

			// Bottom Toolbar (where the selected items and button are displayed)
			this.on( 'toolbar:create:' + this.addon_slug + '-toolbar', this.renderToolbar, this );
		},

		/**
		 * Content Area
		 */
		renderContent: function() {

			this.content.set(
				new wp.media.view.EnviraGalleryView(
					{
						controller: 		this,
						model: 				this.state().props,
						sidebar_template: 	this.addon_slug + '-side-bar',
						get_action: 		this.addon_action_base + '_get_files_folders',
						search_action: 		this.addon_action_base + '_search_files_folders',
						insert_action: 		this.addon_action_base + '_insert_images',
						path: 				'/'
					}
				)
			);

		},

		/**
		 * Toolbar Area
		 */
		renderToolbar: function( toolbar ) {

			toolbar.view = new wp.media.view.Toolbar.EnviraGalleryToolbar(
				{
					controller: this
				}
			);

		},

	}
);
