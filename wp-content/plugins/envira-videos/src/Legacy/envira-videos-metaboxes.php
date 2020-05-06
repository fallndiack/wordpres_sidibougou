<?php
// @codingStandardsIgnoreFile --Legecy
/**
 * Legacy Video Metaboxes.
 *
 * @since 1.0.0
 *
 * @package Envira Gallery
 * @subpackage Envira Videos
 */

if ( ! class_exists( 'Envira_Videos_Metaboxes' ) ) :

	/**
	 * Metaboxes Video Class.
	 *
	 * @since 1.0.0
	 */
	class Envira_Videos_Metaboxes {
		/**
		 * _instance
		 *
		 * (default value: null)
		 *
		 * @var mixed
		 * @access public
		 * @static
		 */
		public static $_instance = null;

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
			$video_type = Envira_Videos_Common::get_instance()->get_video_type( $item['link'], $item, array(), true );
			if ( ! $video_type ) {
				return $output;
			}

			// Output an element with the video type as the class, so we can style it to display the logo.
			$output .= '<span class="envira-video-type ' . $video_type . '">' . $video_type . '</span>';
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
		 * Adds addon settings ui to the new tab
		 *
		 * @since 1.0.0
		 *
		 * @param object $post The current post object.
		 */
		public function settings_screen( $post ) {

			// Get post type so we load the correct metabox instance and define the input field names
			// Input field names vary depending on whether we are editing a Gallery or Album.
			$post_type = get_post_type( $post );
			switch ( $post_type ) {
				/**
				* Gallery
				*/
				case 'envira':
					$instance = Envira_Gallery_Metaboxes::get_instance();
					$key      = '_envira_gallery';
					break;

				/**
				* Album
				*/
				case 'envira_album':
					$instance = Envira_Albums_Metaboxes::get_instance();
					$key      = '_eg_album_data[config]';
					break;
			}
			?>
		<div id="envira-videos">
			<p class="envira-intro">
				<?php esc_html_e( 'Video Lightbox Settings', 'envira-videos' ); ?>
				<small>
					<?php esc_html_e( 'The settings below adjust the Video options for the Lightbox output.', 'envira-videos' ); ?>
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

					<?php

					// only display this particular setting if this is a GALLERY.
					if ( '_envira_gallery' === $key ) {

						?>

					<tr id="envira-config-videos-play-icon-box">
						<th scope="row">
							<label for="envira-config-videos-play-icon"><?php esc_html_e( 'Display Play Icon over Gallery Image?', 'envira-videos' ); ?></label>
						</th>
						<td>
							<input id="envira-config-videos-play-icon" type="checkbox" name="<?php echo esc_html( $key ); ?>[videos_play_icon]" value="1" <?php checked( $instance->get_config( 'videos_play_icon', $instance->get_config_default( 'videos_play_icon' ) ), 1 ); ?> />
							<span class="description"><?php esc_html_e( 'Display a Play Icon over a Gallery Image which is linked to a Video, to make it clear to the user that it is a video. Setting does not apply if an individual image has the &quot;Display Video in Gallery&quot; option enabled.', 'envira-videos' ); ?></span>
						</td>
					</tr>

					<?php } ?>

					<tr id="envira-config-videos-autoplay-box">
						<th scope="row">
							<label for="envira-config-videos-autoplay"><?php esc_html_e( 'Autoplay Videos?', 'envira-videos' ); ?></label>
						</th>
						<td>
							<input id="envira-config-videos-autoplay" type="checkbox" name="<?php echo esc_html( $key ); ?>[videos_autoplay]" value="1" <?php checked( $instance->get_config( 'videos_autoplay', $instance->get_config_default( 'videos_autoplay' ) ), 1 ); ?> />
							<span class="description"><?php esc_html_e( '(YouTube, Vimeo, Wistia, DailyMotion): Automatically begins playback of videos when they are displayed in the Lightbox view.', 'envira-videos' ); ?></span>
						</td>
					</tr>

					<tr id="envira-config-videos-enlarge-box">
						<th scope="row">
							<label for="envira-config-videos-enlarge"><?php esc_html_e( 'Force Larger Videos?', 'envira-videos' ); ?></label>
						</th>
						<td>
							<input id="envira-config-videos-enlarge" type="checkbox" name="<?php echo esc_html( $key ); ?>[videos_enlarge]" value="1" <?php checked( $instance->get_config( 'videos_enlarge', $instance->get_config_default( 'videos_enlarge' ) ), 1 ); ?> />
							<span class="description"><?php esc_html_e( '(YouTube, Vimeo, Wistia): Enlarge video to full screen instead of original size.', 'envira-videos' ); ?></span>
						</td>
					</tr>

					<tr id="envira-config-videos-playpause-box">
						<th scope="row">
							<label for="envira-config-videos-playpause"><?php esc_html_e( 'Show Play/Pause Controls?', 'envira-videos' ); ?></label>
						</th>
						<td>
							<input id="envira-config-videos-playpause" type="checkbox" name="<?php echo esc_html( $key ); ?>[videos_playpause]" value="1" <?php checked( $instance->get_config( 'videos_playpause', $instance->get_config_default( 'videos_playpause' ) ), 1 ); ?> />
							<span class="description"><?php esc_html_e( '(YouTube, Wistia, DailyMotion, Self Hosted): Display play and pause controls on videos in the Lightbox view.', 'envira-videos' ); ?></span>
						</td>
					</tr>

					<tr id="envira-config-videos-progress-box">
						<th scope="row">
							<label for="envira-config-videos-progress"><?php esc_html_e( 'Show Progress Bar?', 'envira-videos' ); ?></label>
						</th>
						<td>
							<input id="envira-config-videos-progress" type="checkbox" name="<?php echo esc_html( $key ); ?>[videos_progress]" value="1" <?php checked( $instance->get_config( 'videos_progress', $instance->get_config_default( 'videos_progress' ) ), 1 ); ?> />
							<span class="description"><?php esc_html_e( '(Wistia, Self Hosted): Display the progress bar on videos in the Lightbox view.', 'envira-videos' ); ?></span>
						</td>
					</tr>

					<tr id="envira-config-videos-current-box">
						<th scope="row">
							<label for="envira-config-videos-current"><?php esc_html_e( 'Show Current Time?', 'envira-videos' ); ?></label>
						</th>
						<td>
							<input id="envira-config-videos-current" type="checkbox" name="<?php echo esc_html( $key ); ?>[videos_current]" value="1" <?php checked( $instance->get_config( 'videos_current', $instance->get_config_default( 'videos_current' ) ), 1 ); ?> />
							<span class="description"><?php esc_html_e( '(Self Hosted): Display the current playback time on videos in the Lightbox view.', 'envira-videos' ); ?></span>
						</td>
					</tr>

					<tr id="envira-config-videos-duration-box">
						<th scope="row">
							<label for="envira-config-videos-duration"><?php esc_html_e( 'Show Video Length?', 'envira-videos' ); ?></label>
						</th>
						<td>
							<input id="envira-config-videos-duration" type="checkbox" name="<?php echo esc_html( $key ); ?>[videos_duration]" value="1" <?php checked( $instance->get_config( 'videos_duration', $instance->get_config_default( 'videos_duration' ) ), 1 ); ?> />
							<span class="description"><?php esc_html_e( '(Self Hosted): Display the video length on videos in the Lightbox view.', 'envira-videos' ); ?></span>
						</td>
					</tr>

					<tr id="envira-config-videos-volume-box">
						<th scope="row">
							<label for="envira-config-videos-volume"><?php esc_html_e( 'Enable Volume Controls?', 'envira-videos' ); ?></label>
						</th>
						<td>
							<input id="envira-config-videos-volume" type="checkbox" name="<?php echo esc_html( $key ); ?>[videos_volume]" value="1" <?php checked( $instance->get_config( 'videos_volume', $instance->get_config_default( 'videos_volume' ) ), 1 ); ?> />
							<span class="description"><?php esc_html_e( '(Wistia, Self Hosted): Display the volume controls on videos in the Lightbox view.', 'envira-videos' ); ?></span>
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

			$settings['config']['videos_play_icon'] = ( isset( $_POST['_envira_gallery']['videos_play_icon'] ) ? 1 : 0 );
			$settings['config']['videos_autoplay']  = ( isset( $_POST['_envira_gallery']['videos_autoplay'] ) ? 1 : 0 );
			$settings['config']['videos_enlarge']   = ( isset( $_POST['_envira_gallery']['videos_enlarge'] ) ? 1 : 0 );
			$settings['config']['videos_playpause'] = ( isset( $_POST['_envira_gallery']['videos_playpause'] ) ? 1 : 0 );
			$settings['config']['videos_progress']  = ( isset( $_POST['_envira_gallery']['videos_progress'] ) ? 1 : 0 );
			$settings['config']['videos_current']   = ( isset( $_POST['_envira_gallery']['videos_current'] ) ? 1 : 0 );
			$settings['config']['videos_duration']  = ( isset( $_POST['_envira_gallery']['videos_duration'] ) ? 1 : 0 );
			$settings['config']['videos_volume']    = ( isset( $_POST['_envira_gallery']['videos_volume'] ) ? 1 : 0 );

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

			$settings['config']['videos_play_icon'] = ( isset( $_POST['_eg_album_data']['config']['videos_play_icon'] ) ? 1 : 0 );
			$settings['config']['videos_autoplay']  = ( isset( $_POST['_eg_album_data']['config']['videos_autoplay'] ) ? 1 : 0 );
			$settings['config']['videos_enlarge']   = ( isset( $_POST['_eg_album_data']['config']['videos_enlarge'] ) ? 1 : 0 );
			$settings['config']['videos_playpause'] = ( isset( $_POST['_eg_album_data']['config']['videos_playpause'] ) ? 1 : 0 );
			$settings['config']['videos_progress']  = ( isset( $_POST['_eg_album_data']['config']['videos_progress'] ) ? 1 : 0 );
			$settings['config']['videos_current']   = ( isset( $_POST['_eg_album_data']['config']['videos_current'] ) ? 1 : 0 );
			$settings['config']['videos_duration']  = ( isset( $_POST['_eg_album_data']['config']['videos_duration'] ) ? 1 : 0 );
			$settings['config']['videos_volume']    = ( isset( $_POST['_eg_album_data']['config']['videos_volume'] ) ? 1 : 0 );

			return $settings;

		}

		/**
		 * Returns the singleton instance of the class.
		 *
		 * @since 1.0.0
		 *
		 * @return object The Envira_Pagination_Metaboxes object.
		 */
		public static function get_instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Videos_Metaboxes ) ) {
				self::$instance = new Envira_Videos_Metaboxes();
			}

			return self::$instance;

		}

	}
endif;
