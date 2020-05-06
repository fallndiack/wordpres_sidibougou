<?php
/**
 * Metabox class.
 *
 * @since 1.0.0
 *
 * @package Envira_Videos
 * @author  Envira Team
 */

namespace Envira\Videos\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {

	exit;

}
/**
 * Metabox class.
 *
 * @since 1.0.0
 *
 * @package Envira_Videos
 * @author  Envira Team
 */
class Media_View {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Scripts.
		add_action( 'envira_gallery_metabox_scripts', array( $this, 'scripts' ) );

		// Modals.
		add_filter( 'envira_gallery_media_view_strings', array( $this, 'media_view_strings' ) );
		add_action( 'envira_print_media_templates', array( $this, 'print_media_templates' ) );

	}



	/**
	 * Enqueues JS for this Addon
	 *
	 * @since 1.0.0
	 */
	public function scripts() {

		// Get Gallery ID.
		global $id, $post;
		$post_id = isset( $post->ID ) ? $post->ID : (int) $id;

		$version = ( defined( 'ENVIRA_DEBUG' ) && 'true' === ENVIRA_DEBUG ) ? $version = time() . '-' . ENVIRA_VIDEOS_VERSION : ENVIRA_VIDEOS_VERSION;

		wp_register_script( ENVIRA_VIDEOS_SLUG . '-media-script', plugins_url( 'assets/js/media-view.js', ENVIRA_VIDEOS_FILE ), array( 'jquery' ), $version, true );
		wp_enqueue_script( ENVIRA_VIDEOS_SLUG . '-media-script' );
		wp_localize_script(
			ENVIRA_VIDEOS_SLUG . '-media-script',
			'envira_videos_media_view',
			array(
				'nonce'   => wp_create_nonce( 'envira-videos-media-view-nonce' ),
				'post_id' => $post_id,
			)
		);

	}

	/**
	 * Adds media view (modal) strings for this addon
	 *
	 * @since 1.0.3
	 *
	 * @param    array $strings    Media View Strings.
	 * @return   array               Media View Strings
	 */
	public function media_view_strings( $strings ) {

		$strings['enviraVideosTitle']           = __( 'Insert Videos', 'envira-videos' );
		$strings['enviraVideosValidationError'] = __( 'Please ensure all required fields are specified for each video you want to add to the Gallery.', 'envira-videos' );
		return $strings;

	}

	/**
	 * Outputs backbone.js wp.media compatible templates, which are loaded into the modal
	 * view
	 *
	 * @since 1.0.3
	 *
	 * @param    int $post_id    Post ID.
	 */
	public function print_media_templates( $post_id ) {

		// Router Bar
		// Use: wp.media.template( 'envira-videos-router' ).
		?>
		<script type="text/html" id="tmpl-envira-videos-router">
			<div class="media-toolbar">
				<div class="media-toolbar-secondary">
					<span class="spinner"></span>
				</div>
				<div class="media-toolbar-primary search-form">
					<button class="envira-videos-add button button-primary"><?php esc_html_e( 'Add Video', 'envira-videos' ); ?></button>
				</div>
			</div>
		</script>

		<?php
		// Side Bar
		// Use: wp.media.template( 'envira-videos-side-bar' ).
		?>
		<script type="text/html" id="tmpl-envira-videos-side-bar">
			<div class="media-sidebar">
				<div class="envira-gallery-meta-sidebar">
					<h3><?php esc_html_e( 'Helpful Tips', 'envira-videos' ); ?></h3>
					<strong><?php esc_html_e( 'Creating Video Items', 'envira-videos' ); ?></strong>
					<p><?php esc_html_e( 'The image for each video is automatically created from the video link you supply. Video links can be from the below sources, including locally hosted video files. They <strong>must</strong> follow one of the formats listed:', 'envira-videos' ); ?></p>

					<div class="envira-gallery-accepted-urls modal-urls">
						<ul>
							<li>
								<strong><a href="#"><?php esc_html_e( 'YouTube URLs', 'envira-videos' ); ?></a></strong>
								<ul class="closed">
									<li>https://youtube.com/v/{vidID}</li>
									<li>https://youtube.com/vi/{vidID}</li>
									<li>https://youtube.com/?v={vidID}</li>
									<li>https://youtube.com/?vi={vidID}</li>
									<li>https://youtube.com/watch?v={vidID}</li>
									<li>https://youtube.com/watch?vi={vidID}</li>
									<li>https://youtu.be/{vidID}</li>
									<li>https://youtube.com/{vidID}?t={startMin}m{startSec}s</li>
									<li>https://youtube.com/{vidID}?t={startSec}</li>
									<li>https://youtube.com/playlist?list={playlistID}</li>
								</ul>
							</li>
						</ul>

						<ul>
							<li>
								<strong><a href="#"><?php esc_html_e( 'Vimeo URLs', 'envira-videos' ); ?></a></strong>
								<ul class="closed">
									<li>https://vimeo.com/{vidID}</li>
									<li>https://vimeo.com/groups/tvc/videos/{vidID}</li>
									<li>https://player.vimeo.com/video/{vidID}</li>
								</ul>
							</li>
						</ul>

						<ul>
							<li><strong><a href="#"><?php esc_html_e( 'Dailymotion URLs', 'envira-videos' ); ?></a></strong>
								<ul class="closed">
									<li>http://www.dailymotion.com/video/{vidID}</li>
								</ul>
							</li>
						</ul>

						<ul>
							<li><strong><a href="#"><?php esc_html_e( 'Instagram URLs', 'envira-videos' ); ?></a></strong>
								<ul class="closed">
									<li>http://www.instagram.com/p/{vidID}</li>
									<li>http://www.instagram.com/tv/{vidID}</li>
								</ul>
							</li>
						</ul>

						<ul>
							<li><strong><a href="#"><?php esc_html_e( 'Facebook URLs', 'envira-videos' ); ?></a></strong>
								<ul class="closed">
									<li>https://facebook.com/facebook/videos/{vidID}</li>
								</ul>
							</li>
						</ul>

						<ul>
							<li><strong><a href="#"><?php esc_html_e( 'Twitch URLs', 'envira-videos' ); ?></a></strong>
								<ul class="closed">
									<li>https://www.twitch.tv/videos/{vidID}</li>
								</ul>
							</li>
						</ul>

						<ul>
							<li><strong><a href="#"><?php esc_html_e( 'VideoPress URLs', 'envira-videos' ); ?></a></strong>
								<ul class="closed">
									<li>https://videopress.com/v/{vidID}</li>
								</ul>
							</li>
						</ul>

						<ul>
							<li><strong><a href="#"><?php esc_html_e( 'Wistia URLs', 'envira-videos' ); ?></a></strong>
								<ul class="closed">
									<li>https://wistia.com/medias/{vidID}</li>
								</ul>
							</li>
						</ul>

						<ul>
							<li><strong><a href="#"><?php esc_html_e( 'Local URLs', 'envira-videos' ); ?></a></strong>
								<ul class="closed">
									<li><?php bloginfo( 'url' ); ?>/path/to/video.mp4</li>
									<li><?php bloginfo( 'url' ); ?>/path/to/video.ogv</li>
									<li><?php bloginfo( 'url' ); ?>/path/to/video.webm</li>
									<li><?php bloginfo( 'url' ); ?>/path/to/video.3gp</li>
								</ul>
							</li>
						</ul>

						<?php do_action( 'envira_gallery_accepted_video_urls' ); ?>

					</div>
				</div>
			</div>
		</script>

		<?php
		// Error Message
		// Use: wp.media.template( 'envira-videos-error' ).
		?>
		<script type="text/html" id="tmpl-envira-videos-error">
			<p>
				{{ data.error }}
			</p>
		</script>

		<?php
		// Collection of Videos
		// Use: wp.media.template( 'envira-videos-items' )
		// wp.media.template( 'envira-videos-item' ) is used to inject <li> items into this template.
		?>
		<script type="text/html" id="tmpl-envira-videos-items">
			<ul class="attachments envira-videos-attachments"></ul>
		</script>
		<?php

		// Single Video
		// Use: wp.media.template( 'envira-videos-item' ).
		?>
		<script type="text/html" id="tmpl-envira-videos-item">
			<div class="envira-videos-item">
				<div class="header-links">
					<a href="#" class="envira-item-collapse"><?php esc_html_e( 'Collapse', 'envira-videos' ); ?></a>
					<a href="#" class="envira-videos-delete" title="<?php esc_html_e( 'Remove', 'envira-videos' ); ?>"><?php esc_html_e( 'X', 'envira-videos' ); ?></a>
				</div>
				<!-- Title -->
				<div>
					<label>
						<strong><?php esc_html_e( 'Title *', 'envira-videos' ); ?></strong>
						<div class="envira-input-group">
							<input type="text" name="title" />
						</div>
					</label>
				</div>

				<!-- Video URL -->
				<?php
				/*
				<div>
					<label>
						<strong><?php esc_html_e( 'Video URL *', 'envira-videos' ); ?></strong>
						<input type="text" name="link" />
					</label>
				</div>
				*/
				?>

				<!-- Video URL -->
				<div class="envira-item-setting">
					<label>
						<strong><?php esc_html_e( 'Video URL *', 'envira-videos' ); ?></strong>
						<div class="envira-input-group">

							<div class="envira-grid-10 envira-first">

								<input type="text" name="link" />
							</div>
							<div class="envira-grid-2 envira-media-button">
								<a href="#" class="button button-envira-secondary envira-insert-video"><?php esc_html_e( 'Upload Media', 'envira-videos' ); ?></a>
							</div>

							<div class="envira-clearfix"></div>

						</div>
					</label>
				</div>

				<!-- Image -->
				<?php
				/*
				<div class="image">
					<label>
						<strong><?php esc_html_e( 'Image URL *', 'envira-videos' ); ?></strong>
						<input type="text" name="image" />
					</label>
					<p class="description"><?php esc_html_e( 'Required if specifying a local video URL.', 'envira-videos' ); ?></p>
				</div>
				*/
				?>

				<!-- Image -->
				<div class="envira-item-setting image">
					<label class="setting">
						<strong><?php esc_html_e( 'Image URL *', 'envira-videos' ); ?></strong>

						<div class="envira-input-group">
							<div class="envira-grid-10 envira-first">

							<input type="text" name="image" />

							</div>
							<div class="envira-grid-2 envira-media-button">

								<a href="#" class="button button-envira-secondary envira-insert-placeholder"><?php esc_html_e( 'Upload Media', 'envira-videos' ); ?></a>

							</div>

							<div class="envira-clearfix"></div>
						</div>
					<p class="description"><?php esc_html_e( 'Required if specifying a local video URL.', 'envira-videos' ); ?></p>

					</label>
				</div>

				<!-- Caption -->
				<div class="envira-item-setting">
					<label>
						<strong><?php esc_html_e( 'Caption', 'envira-videos' ); ?></strong>
						<div class="envira-input-group">
							<textarea name="caption" rows="3"></textarea>
						</div>
					</label>
				</div>

				<!-- Alt Text -->
				<div class="envira-item-setting">
					<label>
						<strong><?php esc_html_e( 'Alt Text', 'envira-videos' ); ?></strong>
						<div class="envira-input-group">
							<input type="text" name="alt" />
						</div>
					</label>
				</div>

			</div>
		</script>

		<script type="text/html" id="tmpl-envira-videos-item">
			<div class="envira-videos-item">
				<a href="#" class="button button-secondary envira-videos-delete" title="<?php esc_html_e( 'Remove', 'envira-videos' ); ?>"><?php esc_html_e( 'Remove', 'envira-videos' ); ?></a>

				<!-- Title -->
				<div>
					<label>
						<strong><?php esc_html_e( 'Title *', 'envira-videos' ); ?></strong>
						<input type="text" name="title" />
					</label>
				</div>

				<!-- Video URL -->
				<div>
					<label>
						<strong><?php esc_html_e( 'Video URL *', 'envira-videos' ); ?></strong>
						<input type="text" name="link" />
					</label>
				</div>

				<!-- Image -->
				<div class="image">
					<label>
						<strong><?php esc_html_e( 'Image URL *', 'envira-videos' ); ?></strong>
						<input type="text" name="image" />
					</label>
					<p class="description"><?php esc_html_e( 'Required if specifying a local video URL.', 'envira-videos' ); ?></p>
				</div>

				<!-- Caption -->
				<div>
					<label>
						<strong><?php esc_html_e( 'Caption', 'envira-videos' ); ?></strong>
						<input type="text" name="caption" />
					</label>
				</div>

				<!-- Alt Text -->
				<div>
					<label>
						<strong><?php esc_html_e( 'Alt Text', 'envira-videos' ); ?></strong>
						<input type="text" name="alt" />
					</label>
				</div>
			</div>
		</script>

		<?php
		// Edit Metadata
		// Use: wp.media.template( 'envira-meta-editor-video' ).
		?>
		<script type="text/html" id="tmpl-envira-meta-editor-video">

			<label class="setting">
				<span class="name"><?php esc_html_e( 'Is 16:9 Video?', 'envira-videos' ); ?></span>
				<span class="description">
					<input type="checkbox" name="video_aspect_ratio" value="16:9"<# if ( data.video_aspect_ratio == '16:9' ) { #> checked <# } #> />
					<?php esc_html_e( 'If this video is in 16:9 aspect ratio, check this option to ensure the video displays without black bars in the Lightbox view.', 'envira-videos' ); ?>
				</span>
			</label>

			<label class="setting">
				<span class="name"><?php esc_html_e( 'Video Width', 'envira-videos' ); ?></span>
				<span class="description">
					<input type="text" name="video_width" value="{{ data.video_width }}" />
					<?php esc_html_e( 'For videos that don\'t display the dimensions properly, you can set the width here.', 'envira-videos' ); ?>
				</span>
			</label>
			<label class="setting">
				<span class="name"><?php esc_html_e( 'Video Height', 'envira-videos' ); ?></span>
				<span class="description">
					<input type="text" name="video_height" value="{{ data.video_height }}" />
					<?php esc_html_e( 'For videos that don\'t display the dimensions properly, you can set the height here.', 'envira-videos' ); ?>
				</span>
			</label>

			<label class="setting">
				<span class="name"><?php esc_html_e( 'Display Video in Gallery?', 'envira-videos' ); ?></span>
				<span class="description">
					<input type="checkbox" name="video_in_gallery" value="1"<# if ( data.video_in_gallery == '1' ) { #> checked <# } #> />
					<?php esc_html_e( 'If this media item\'s URL is a self-hosted, YouTube, Vimeo or Wistia video, you can check this option to display the video in the gallery grid, instead of displaying the placeholder image.', 'envira-videos' ); ?>
				</span>
			</label>

			<label class="setting">
				<span class="name"><?php esc_html_e( 'Video Thumbnail', 'envira-videos' ); ?></span>
				<?php /* <input type="text" name="video_thumbnail" value="{{ data._thumbnail }}" /> */ ?>
				<input type="text" name="video_thumbnail" value="{{ data.src }}" />
				<input type="text" name="video_thumbnail_id" value="{{ data._thumbnail_id }}" />
				<div class="buttons" style="padding-top: 10px; clear: both; ">
					<button class="button button-small envira-insert-placeholder"><?php esc_html_e( 'Upload Media', 'envira' ); ?></button>
				</div>
				<span class="description">
					<?php /* <# if ( data._thumbnail != '' ) { #><p>Current Thumbnail: {{ data._thumbnail }}</p><# } #> */ ?>
					<?php /* <input type="file" name="video_thumbnail" /> */ ?>
					<?php esc_html_e( 'If you wish to override auto-generated thumbnails from the video services you\'ve selected, click the \'Upload Media\' button and upload and/or select your image from the WordPress media library.', 'envira-videos' ); ?>
				</span>
			</label>

		</script>

		<?php

	}

}
