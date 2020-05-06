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
class Metaboxes {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Styles and Scripts.
		add_action( 'admin_init', array( $this, 'styles' ) );
		add_action( 'envira_gallery_metabox_scripts', array( $this, 'scripts' ) );

		// Gallery.
		add_filter( 'envira_gallery_metabox_output_gallery_item_meta', array( $this, 'output_gallery_item_meta' ), 10, 4 );
		add_filter( 'envira_gallery_tab_nav', array( $this, 'tab_nav' ) );
		add_action( 'envira_gallery_tab_videos', array( $this, 'settings_screen' ) );
		add_filter( 'envira_gallery_save_settings', array( $this, 'gallery_settings_save' ), 10, 2 );

		// Albums.
		add_filter( 'envira_albums_tab_nav', array( $this, 'tab_nav' ) );
		add_action( 'envira_albums_tab_videos', array( $this, 'settings_screen' ) );
		add_filter( 'envira_albums_save_settings', array( $this, 'album_settings_save' ), 10, 2 );

	}

	/**
	 * Enqueues styles used when creating or editing a Gallery
	 *
	 * @since 1.1.9
	 */
	public function styles() {

		if ( is_admin() ) {

			wp_enqueue_style( ENVIRA_VIDEOS_SLUG . '-metabox-style', plugins_url( 'assets/css/videos-admin.css', ENVIRA_VIDEOS_FILE ), array(), ENVIRA_VIDEOS_VERSION );

		}

	}

	/**
	 * Enqueues the Media Editor script, which is used when editing a gallery image
	 * This outputs the Video settings for each individual image
	 *
	 * @since 1.1.6
	 */
	public function scripts() {

		wp_enqueue_script( ENVIRA_VIDEOS_SLUG . '-media-edit', plugins_url( 'assets/js/media-edit.js', ENVIRA_VIDEOS_FILE ), array( 'jquery' ), ENVIRA_VIDEOS_VERSION, true );
		wp_enqueue_script( ENVIRA_VIDEOS_SLUG . '-conditional-fields-script', plugins_url( 'assets/js/min/conditional-fields-min.js', ENVIRA_VIDEOS_FILE ), array( 'jquery' ), ENVIRA_VIDEOS_VERSION, true );

	}

	/**
	 * Adds the item's video type to the gallery item output
	 *
	 * @since 1.1.9
	 *
	 * @param string $output     Meta Output.
	 * @param array  $item       Gallery Item.
	 * @param int    $attach_id  Attachment ID.
	 * @param int    $post_id    Gallery ID.
	 * @return array                Gallery Item
	 */
	public function output_gallery_item_meta( $output, $item, $attach_id, $post_id ) {

		// Determine if the item is a video.
		$video_type = envira_video_get_video_type( $item['link'], $item, array(), true );
		if ( ! $video_type ) {
			return $output;
		}

		// Output an element with the video type as the class, so we can style it to display the logo.
		$output .= '<span title="' . ucwords( $video_type ) . ' Video" class="envira-video-type ' . $video_type . '"></span>';
		return $output;

	}

	/**
	 * Adds a new tab for this addon.
	 *
	 * @since 1.0.0
	 *
	 * @param array $tabs  Array of default tab values.
	 * @return array $tabs Amended array of default tab values.
	 */
	public function tab_nav( $tabs ) {

		$tabs['videos'] = __( 'Videos', 'envira-videos' );
		return $tabs;

	}

	/**
	 * Adds a new tab for this addon.
	 *
	 * @since 1.0.0
	 *
	 * @param array $option  Option.
	 * @param array $key  Key.
	 * @param array $data  Gallery data.
	 * @return array Options..
	 */
	public function get_config( $option, $key, $data ) {

		if ( '_eg_album_data[config]' === $key ) {
			return envira_albums_get_config( $option, $data );
		}

		return envira_get_config( $option, $data );

	}

	/**
	 * Adds addon settings ui to the new tab
	 *
	 * @since 1.0.0
	 *
	 * @param object $post The current post object.
	 */
	public function settings_screen( $post ) {

		wp_nonce_field( 'envira_videos_save_settings', 'envira_videos_nonce' );

		// Get post type so we load the correct metabox instance and define the input field names
		// Input field names vary depending on whether we are editing a Gallery or Album.
		$post_type = get_post_type( $post );

		switch ( $post_type ) {
			/**
			* Gallery
			*/
			case 'envira':
				$key  = '_envira_gallery';
				$data = get_post_meta( $post->ID, '_eg_gallery_data', true );
				break;

			/**
			* Album
			*/
			case 'envira_album':
				$key  = '_eg_album_data[config]';
				$data = get_post_meta( $post->ID, '_eg_album_data', true );
				break;
		}
		?>
		<div id="envira-videos">
			<?php

			// only display this particular setting if this is a GALLERY.
			if ( '_envira_gallery' === $key ) {

				?>
			<p class="envira-intro">
				<?php esc_html_e( 'Video Settings', 'envira-videos' ); ?>
				<small>
					<?php esc_html_e( 'The settings below adjust the video options for gallery and lightbox output.', 'envira-videos' ); ?>
					<br />
					<?php esc_html_e( 'Need some help?', 'envira-videos' ); ?>
					<a href="http://enviragallery.com/docs/video-addon/" class="envira-doc" target="_blank">
						<?php esc_html_e( 'Read the Documentation', 'envira-videos' ); ?>
					</a>
					or
					<a href="https://www.youtube.com/embed/ODfL38a9cJ4/?rel=0" class="envira-video" target="_blank">
						<?php esc_html_e( 'Watch a Video', 'envira-videos' ); ?>
					</a>
				</small>
			</p>
			<table class="form-table">
				<tbody>
					<tr id="envira-config-videos-play-icon-box">
						<th scope="row">
							<label for="envira-config-videos-play-icon"><?php esc_html_e( 'Display Play Icon Over Gallery Image?', 'envira-videos' ); ?></label>
						</th>
						<td>
							<input id="envira-config-videos-play-icon" type="checkbox" name="<?php echo esc_html( $key ); ?>[videos_play_icon]" value="1" <?php checked( $this->get_config( 'videos_play_icon', $key, $data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( 'Display a Play Icon over a Gallery Image which is linked to a Video, to make it clear to the user that it is a video. Setting does not apply if an individual image has the &quot;Display Video in Gallery&quot; option enabled.', 'envira-videos' ); ?></span>
						</td>
					</tr>
				</tbody>
			</table>
			<?php } ?>
			<p class="envira-intro">
				<?php esc_html_e( 'Video Lightbox Settings', 'envira-videos' ); ?>
				<small>
					<strong>Note:</strong> Some browsers might ignore some settings below. See <a href="http://enviragallery.com/docs/video-addon/" class="envira-doc" target="_blank">our documentation</a> for details.
				</small>
			</p>
			<table class="form-table">
				<tbody>

					<tr id="envira-config-videos-play-icon-thumbnail-box">
						<th scope="row">
							<label for="envira-config-videos-play-icon-thumbnail"><?php esc_html_e( 'Display Play Icon Over Lightbox Thumbnails?', 'envira-videos' ); ?></label>
						</th>
						<td>
							<input id="envira-config-videos-play-icon-thumbnail" type="checkbox" name="<?php echo esc_html( $key ); ?>[videos_play_icon_thumbnails]" value="1" <?php checked( $this->get_config( 'videos_play_icon_thumbnails', $key, $data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( 'Display a Play Icon over a thumbnail that is linked to a video.', 'envira-videos' ); ?></span>
						</td>
					</tr>


					<tr id="envira-config-videos-autoplay-box">
						<th scope="row">
							<label for="envira-config-videos-autoplay"><?php esc_html_e( 'Autoplay Videos?', 'envira-videos' ); ?></label>
						</th>
						<td>
							<input id="envira-config-videos-autoplay" type="checkbox" name="<?php echo esc_html( $key ); ?>[videos_autoplay]" value="1" <?php checked( $this->get_config( 'videos_autoplay', $key, $data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( '(DailyMotion, Instagram, VideoPress, Vimeo,  Wistia, YouTube, Self Hosted): Automatically begins playback of videos when they are displayed in the Lightbox view.', 'envira-videos' ); ?></span>
						</td>
					</tr>

					<tr id="envira-config-videos-enlarge-box">
						<th scope="row">
							<label for="envira-config-videos-enlarge"><?php esc_html_e( 'Force Larger Videos?', 'envira-videos' ); ?></label>
						</th>
						<td>
							<input id="envira-config-videos-enlarge" type="checkbox" name="<?php echo esc_html( $key ); ?>[videos_enlarge]" value="1" <?php checked( $this->get_config( 'videos_enlarge', $key, $data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( '(Twitch, VideoPress, Vimeo, YouTube, Self Hosted): Enlarge video to full screen instead of original size.', 'envira-videos' ); ?></span>
						</td>
					</tr>

					<tr id="envira-config-videos-controls">
						<th scope="row">
							<label for="envira-config-videos-controls"><?php esc_html_e( 'Show Video Controls?', 'envira-videos' ); ?></label>
						</th>
						<td>
							<input id="envira-config-videos-controls" type="checkbox" name="<?php echo esc_html( $key ); ?>[videos_controls]" value="1" <?php checked( $this->get_config( 'videos_controls', $key, $data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( '(Instagram, Self Hosted): Display the video\'s control bar and any controls (regardless of the above settings) in the Lightbox view. If no controls are shown, Envira autoplays the video.', 'envira-videos' ); ?></span>
						</td>
					</tr>

					<tr id="envira-config-videos-playpause-box">
						<th scope="row">
							<label for="envira-config-videos-playpause"><?php esc_html_e( 'Show Play/Pause Controls?', 'envira-videos' ); ?></label>
						</th>
						<td>
							<input id="envira-config-videos-playpause" type="checkbox" name="<?php echo esc_html( $key ); ?>[videos_playpause]" value="1" <?php checked( $this->get_config( 'videos_playpause', $key, $data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( '(DailyMotion, Wistia, YouTube, Self Hosted): Display play and pause controls on videos in the Lightbox view. ', 'envira-videos' ); ?></span>
						</td>
					</tr>

					<tr id="envira-config-videos-progress-box">
						<th scope="row">
							<label for="envira-config-videos-progress"><?php esc_html_e( 'Show Progress Bar?', 'envira-videos' ); ?></label>
						</th>
						<td>
							<input id="envira-config-videos-progress" type="checkbox" name="<?php echo esc_html( $key ); ?>[videos_progress]" value="1" <?php checked( $this->get_config( 'videos_progress', $key, $data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( '(Wistia, Self Hosted): Display the progress bar on videos in the Lightbox view. ', 'envira-videos' ); ?></span>
						</td>
					</tr>

					<tr id="envira-config-videos-current-box">
						<th scope="row">
							<label for="envira-config-videos-current"><?php esc_html_e( 'Show Current Time?', 'envira-videos' ); ?></label>
						</th>
						<td>
							<input id="envira-config-videos-current" type="checkbox" name="<?php echo esc_html( $key ); ?>[videos_current]" value="1" <?php checked( $this->get_config( 'videos_current', $key, $data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( '(Self Hosted): Display the current playback time on videos in the Lightbox view. ', 'envira-videos' ); ?></span>
						</td>
					</tr>

					<tr id="envira-config-videos-duration-box">
						<th scope="row">
							<label for="envira-config-videos-duration"><?php esc_html_e( 'Show Video Length?', 'envira-videos' ); ?></label>
						</th>
						<td>
							<input id="envira-config-videos-duration" type="checkbox" name="<?php echo esc_html( $key ); ?>[videos_duration]" value="1" <?php checked( $this->get_config( 'videos_duration', $key, $data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( '(Self Hosted): Display the video length on videos in the Lightbox view. ', 'envira-videos' ); ?></span>
						</td>
					</tr>

					<tr id="envira-config-videos-volume-box">
						<th scope="row">
							<label for="envira-config-videos-volume"><?php esc_html_e( 'Enable Volume Controls?', 'envira-videos' ); ?></label>
						</th>
						<td>
							<input id="envira-config-videos-volume" type="checkbox" name="<?php echo esc_html( $key ); ?>[videos_volume]" value="1" <?php checked( $this->get_config( 'videos_volume', $key, $data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( '(Instagram, Self Hosted): Display the volume controls on videos in the Lightbox view.', 'envira-videos' ); ?></span>
						</td>
					</tr>

					<tr id="envira-config-videos-fullscreen-box">
						<th scope="row">
							<label for="envira-config-videos-fullscreen"><?php esc_html_e( 'Enable Fullscreen?', 'envira-videos' ); ?></label>
						</th>
						<td>
							<input id="envira-config-videos-fullscreen" type="checkbox" name="<?php echo esc_html( $key ); ?>[videos_fullscreen]" value="1" <?php checked( $this->get_config( 'videos_fullscreen', $key, $data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( '(Instagram, Self Hosted): Display the fullscreen controls on videos in the Lightbox view. ', 'envira-videos' ); ?></span>
						</td>
					</tr>

					<tr id="envira-config-videos-download-box">
						<th scope="row">
							<label for="envira-config-videos-download"><?php esc_html_e( 'Enable Downloads?', 'envira-videos' ); ?></label>
						</th>
						<td>
							<input id="envira-config-videos-download" type="checkbox" name="<?php echo esc_html( $key ); ?>[videos_download]" value="1" <?php checked( $this->get_config( 'videos_download', $key, $data ), 1 ); ?> />
							<span class="description"><?php esc_html_e( '(Instagram, Self Hosted): Display the download controls on videos in the Lightbox view. ', 'envira-videos' ); ?></span>
						</td>
					</tr>

				</tbody>
			</table>
		</div>
		<?php

	}

	/**
	 * Saves the addon's settings for Galleries.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings  Array of settings to be saved.
	 * @param int   $post_id     The current post ID.
	 * @return array $settings Amended array of settings to be saved.
	 */
	public function gallery_settings_save( $settings, $post_id ) {

		if (
			! isset( $_POST['_envira_gallery'], $_POST['envira_videos_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['envira_videos_nonce'] ), 'envira_videos_save_settings' )
		) {
			return $settings;
		}

		$settings['config']['videos_play_icon']            = ( isset( $_POST['_envira_gallery']['videos_play_icon'] ) ? 1 : 0 );
		$settings['config']['videos_play_icon_thumbnails'] = ( isset( $_POST['_envira_gallery']['videos_play_icon_thumbnails'] ) ? 1 : 0 );
		$settings['config']['videos_autoplay']             = ( isset( $_POST['_envira_gallery']['videos_autoplay'] ) ? 1 : 0 );
		$settings['config']['videos_enlarge']              = ( isset( $_POST['_envira_gallery']['videos_enlarge'] ) ? 1 : 0 );
		$settings['config']['videos_playpause']            = ( isset( $_POST['_envira_gallery']['videos_playpause'] ) ? 1 : 0 );
		$settings['config']['videos_progress']             = ( isset( $_POST['_envira_gallery']['videos_progress'] ) ? 1 : 0 );
		$settings['config']['videos_current']              = ( isset( $_POST['_envira_gallery']['videos_current'] ) ? 1 : 0 );
		$settings['config']['videos_duration']             = ( isset( $_POST['_envira_gallery']['videos_duration'] ) ? 1 : 0 );
		$settings['config']['videos_volume']               = ( isset( $_POST['_envira_gallery']['videos_volume'] ) ? 1 : 0 );
		$settings['config']['videos_controls']             = ( isset( $_POST['_envira_gallery']['videos_controls'] ) ? 1 : 0 );
		$settings['config']['videos_fullscreen']           = ( isset( $_POST['_envira_gallery']['videos_fullscreen'] ) ? 1 : 0 );
		$settings['config']['videos_download']             = ( isset( $_POST['_envira_gallery']['videos_download'] ) ? 1 : 0 );

		return $settings;

	}

	/**
	 * Saves the addon's settings for Albums.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings  Array of settings to be saved.
	 * @param int   $post_id     The current post ID.
	 * @return array $settings Amended array of settings to be saved.
	 */
	public function album_settings_save( $settings, $post_id ) {

		if (
			! isset( $_POST['_eg_album_data'], $_POST['envira_videos_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['envira_videos_nonce'] ), 'envira_videos_save_settings' )
		) {
			return $settings;
		}

		$settings['config']['videos_play_icon']            = ( isset( $_POST['_eg_album_data']['config']['videos_play_icon'] ) ? 1 : 0 );
		$settings['config']['videos_play_icon_thumbnails'] = ( isset( $_POST['_eg_album_data']['config']['videos_play_icon_thumbnails'] ) ? 1 : 0 );
		$settings['config']['videos_autoplay']             = ( isset( $_POST['_eg_album_data']['config']['videos_autoplay'] ) ? 1 : 0 );
		$settings['config']['videos_enlarge']              = ( isset( $_POST['_eg_album_data']['config']['videos_enlarge'] ) ? 1 : 0 );
		$settings['config']['videos_playpause']            = ( isset( $_POST['_eg_album_data']['config']['videos_playpause'] ) ? 1 : 0 );
		$settings['config']['videos_progress']             = ( isset( $_POST['_eg_album_data']['config']['videos_progress'] ) ? 1 : 0 );
		$settings['config']['videos_current']              = ( isset( $_POST['_eg_album_data']['config']['videos_current'] ) ? 1 : 0 );
		$settings['config']['videos_duration']             = ( isset( $_POST['_eg_album_data']['config']['videos_duration'] ) ? 1 : 0 );
		$settings['config']['videos_volume']               = ( isset( $_POST['_eg_album_data']['config']['videos_volume'] ) ? 1 : 0 );
		$settings['config']['videos_controls']             = ( isset( $_POST['_eg_album_data']['config']['videos_controls'] ) ? 1 : 0 );
		$settings['config']['videos_fullscreen']           = ( isset( $_POST['_eg_album_data']['config']['videos_fullscreen'] ) ? 1 : 0 );
		$settings['config']['videos_download']             = ( isset( $_POST['_eg_album_data']['config']['videos_download'] ) ? 1 : 0 );

		return $settings;

	}


}
