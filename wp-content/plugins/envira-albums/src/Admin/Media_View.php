<?php
/**
 * Media View class.
 *
 * @since 1.3.0
 *
 * @package Envira_Albums
 * @author  Envira Team
 */

namespace Envira\Albums\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media View Class
 */
class Media_View {


	/**
	 * Primary class constructor.
	 *
	 * @since 1.3.0
	 */
	public function __construct() {

		// Modals.
		add_action( 'print_media_templates', array( $this, 'print_media_templates' ) );

	}

	/**
	 * Outputs backbone.js wp.media compatible templates, which are loaded into the modal
	 * view
	 *
	 * @since 1.3.0
	 */
	public function print_media_templates() {

		// Get the Album Post and Config.
		global $post;
		if ( isset( $post ) ) {
			$post_id = absint( $post->ID );
		} else {
			$post_id = 0;
		}

		// Bail if we're not editing an Envira Album.
		if ( 'envira_album' !== get_post_type( $post_id ) ) {
			return;
		}

		// Single Image Editor.
		// Use: wp.media.template( 'envira-meta-editor' ).
		?>
		<script type="text/html" id="tmpl-envira-albums-meta-editor">

		<?php

		$album                 = _envira_get_album( $post_id );
		$is_lightbox_activated = false;
		if ( isset( $album['config']['lightbox'] ) ) {
			$is_lightbox_activated = $album['config']['lightbox'];
		}

		?>

			<div class="edit-media-header">
				<button class="left dashicons"><span class="screen-reader-text"><?php esc_html_e( 'Edit previous gallery item', 'envira-albums' ); ?></span></button>
				<button class="right dashicons"><span class="screen-reader-text"><?php esc_html_e( 'Edit next gallery item', 'envira-albums' ); ?></span></button>
			</div>
			<div class="media-frame-title">
				<h1><?php esc_html_e( 'Edit Metadata', 'envira-albums' ); ?></h1>
			</div>
			<div class="media-frame-content">
				<div class="attachment-details save-ready">
					<!-- Left -->
					<div class="attachment-media-view portrait">
						<ul class="attachments envira-albums-gallery-cover-image">
						</ul>
					</div>

					<!-- Right -->
					<div class="attachment-info">
						<!-- Settings -->
						<div class="settings">
							<!-- Gallery ID and Cover Image ID -->
							<input type="hidden" name="id" value="{{ data.id }}" />
							<input type="hidden" name="cover_image_id" value="{{ data.cover_image_id }}" />

							<!-- Gallery Title -->
							<label class="setting">
								<span class="name"><?php esc_html_e( 'Title', 'envira-albums' ); ?></span>
								<input type="text" name="title" value="{{ data.title }}" />
								<div class="description">
									<?php esc_html_e( 'Displayed below the Gallery in the Album.', 'envira-gallery' ); ?>
								</div>
							</label>

							<!-- Caption -->
							<div class="setting">
								<span class="name"><?php esc_html_e( 'Caption', 'envira-albums' ); ?></span>
								<?php
								wp_editor(
									'',
									'caption',
									array(
										'media_buttons' => false,
										'wpautop'       => false,
										'tinymce'       => false,
										'textarea_name' => 'caption',
										'quicktags'     => array(
											'buttons' => 'strong,em,link,ul,ol,li,close',
										),
										'editor_height' => 100,
									)
								);
								?>
								<div class="description">
									<?php esc_html_e( 'Captions can take any type of HTML, and are displayed when an image is clicked in the Lightbox view.', 'envira-albums' ); ?>
								</div>
							</div>

							<!-- Alt Text -->
							<label class="setting">
								<span class="name"><?php esc_html_e( 'Alt Text', 'envira-albums' ); ?></span>
								<input type="text" name="alt" value="{{ data.alt }}" />
								<div class="description">
									<?php esc_html_e( 'Very important for SEO, the Alt Text describes the cover image for this Gallery.', 'envira-albums' ); ?>
								</div>
							</label>

							<!-- Cover Image URL -->
							<label class="setting">
								<span class="name"><?php esc_html_e( 'Cover Image URL', 'envira-albums' ); ?></span>
								<input type="text" name="cover_image_url" value="{{ data.cover_image_url }}" />
								<div class="buttons" style="padding-top: 10px; clear: both; ">
									<button class="button button-small envira-insert-placeholder"><?php esc_html_e( 'Upload Media', 'envira' ); ?></button>
								</div>
								<div class="description">
									<?php esc_html_e( 'Defined when you choose a Gallery image.  You can specify your own cover image URL instead (i.e. a third party image)', 'envira-albums' ); ?>
								</div>
							</label>

							<!-- New Window -->


								<!-- Link in New Window -->
								<label class="setting">
									<span class="name"><?php esc_html_e( 'Open URL in New Window?', 'envira-gallery' ); ?></span>
									<span class="description">
										<input type="checkbox" name="link_new_window" value="1"<# if ( data.link_new_window == '1' ) { #> checked <# } #> />
										<?php esc_html_e( 'Opens your gallery links in a new browser window / tab when the lightbox is disabled for the album.', 'envira-gallery' ); ?>
									</span>
								</label>

								<!-- Title Link -->
								<label class="setting">
									<span class="name"><?php esc_html_e( 'Make Gallery Title Linkable?', 'envira-gallery' ); ?></span>
									<span class="description">
										<input type="checkbox" name="link_title_gallery" value="1"<# if ( data.link_title_gallery == '1' ) { #> checked <# } #> />
										<?php esc_html_e( 'Links the gallery title, if displayed, to your gallery.', 'envira-gallery' ); ?>
									</span>
								</label>

								<?php if ( $is_lightbox_activated ) { ?>
								<!-- Lightbox Override -->
								<label class="setting">
									<span class="name"><?php esc_html_e( 'Enable Lightbox For This Gallery?', 'envira-gallery' ); ?></span>
									<span class="description">
										<input type="checkbox" name="gallery_lightbox" value="1"<# if ( data.gallery_lightbox == '1' ) { #> checked <# } #> />
										<?php esc_html_e( 'Uncheck this if you choose NOT to have a lightbox for this gallery, even if you have enabled lightboxes for galleries in your album settings.', 'envira-albums' ); ?>
									</span>
								</label>
								<?php } ?>


							<!-- Addons can populate the UI here -->
							<div class="envira-addons"></div>
						</div>
						<!-- /.settings -->

						<!-- Actions -->
						<div class="actions">
							<a href="#" class="envira-gallery-meta-submit button media-button button-large button-primary media-button-insert" title="<?php esc_attr_e( 'Save Metadata', 'envira-albums' ); ?>">
								<?php esc_html_e( 'Save Metadata', 'envira-albums' ); ?>
							</a>

							<!-- Save Spinner -->
							<span class="settings-save-status">
								<span class="spinner"></span>
								<span class="saved"><?php esc_html_e( 'Saved.', 'envira-albums' ); ?></span>
							</span>
						</div>
						<!-- /.actions -->
					</div>
				</div>
			</div>
		</script>

		<?php
		// Error.
		// Use: wp.media.template( 'envira-albums-error' ).
		?>
		<script type="text/html" id="tmpl-envira-albums-error">
			<p>
				{{ data.error }}
			</p>
		</script>

		<?php
		// Single Gallery (displays images, one of which is selected as the cover image ID).
		// Use: wp.media.template( 'envira-albums-item' ).
		?>
		<script type="text/html" id="tmpl-envira-albums-item">
			<div class="attachment-preview js--select-attachment type-image" data-id="{{ data.id }}" data-src="{{ data.src }}">
				<div class="thumbnail">
					<div class="centered">
						<img src="{{ data.thumb }}" draggable="false" alt="{{ data.title }}" />
					</div>
				</div>
			</div>
			<a class="check">
				<div class="media-modal-icon"></div>
			</a>
		</script>
		<?php

	}

}
