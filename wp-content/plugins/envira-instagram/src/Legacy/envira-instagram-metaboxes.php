<?php
/**
 * Metaboxes
 *
 * @since 1.5.0
 *
 * @package Envira_Instagram
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

if ( ! class_exists( 'Envira_Instagram_Metaboxes' ) ) :

	/**
	 * Metaboxes
	 *
	 * @since 1.5.0
	 *
	 * @package Envira_Instagram
	 * @author  Envira Gallery Team <support@enviragallery.com>
	 */
	class Envira_Instagram_Metaboxes { // @codingStandardsIgnoreLine - Firing off a duplicate warning?

		/**
		 * Primary class constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			// Actions and Filters!
			add_action( 'envira_gallery_metabox_scripts', array( $this, 'meta_box_scripts' ) );
			add_filter( 'envira_gallery_types', array( $this, 'add_type' ), 9999, 2 );

			add_action( 'envira_gallery_display_instagram', array( $this, 'images_display' ) );
			add_action( 'envira_gallery_preview_instagram', array( $this, 'preview_display' ) );
			add_filter( 'envira_albums_metabox_gallery_inject_images', array( $this, 'albums_inject_images_for_cover_image_selection' ), 10, 3 );

			add_filter( 'envira_gallery_save_settings', array( $this, 'save' ), 10, 2 );
			add_action( 'envira_gallery_flush_caches', array( $this, 'flush_caches' ), 10, 2 );

		}

		/**
		 * Enqueues JS for the metabox
		 *
		 * @since 1.0.0
		 */
		public function meta_box_scripts() {

			wp_enqueue_script( ENVIRA_INSTAGRAM_SLUG . '-metabox-script', plugins_url( 'assets/js/min/metabox-min.js', ENVIRA_INSTAGRAM_FILE ), array( 'jquery', 'jquery-ui-sortable' ), ENVIRA_INSTAGRAM_VERSION, true );

		}

		/**
		 * Registers a new Gallery Type
		 *
		 * @since 1.0.0
		 *
		 * @param array   $types Gallery Types.
		 * @param WP_Post $post WordPress Post.
		 * @return array Gallery Types
		 */
		public function add_type( $types, $post ) {

			// Don't add the type if it's a default or dynamic gallery.
			$data = envira_get_gallery( $post->ID );
			if ( 'defaults' === envira_get_config( 'type', $data ) ||
				'dynamic' === envira_get_config( 'type', $data ) ) {
				return $types;
			}

			// Add Instagram as a Gallery Type.
			$types['instagram'] = __( 'Instagram', 'envira-instagram' );
			return $types;

		}

		/**
		 * Display output for the Images Tab
		 *
		 * @since 1.0.0
		 * @param WP_Post $post WordPress Post.
		 */
		public function images_display( $post ) {

			// Get instances and auth.
			$instance = Envira_Gallery_Metaboxes::get_instance();
			$common   = Envira_Instagram_Common::get_instance();
			$auth     = $common->get_instagram_auth();

			if ( empty( $auth['token'] ) ) {
				// Tell the user they need to oAuth with Instagram, and give them the option to do that now.
				// Determine which screen we're on (i.e. New Gallery or Edit Gallery).
				if ( 'auto-draft' === $post->post_status ) {
					$connect_url = $common->get_oauth_url( 'post-new.php?post_type=envira' );
				} else {
					// Note: the missing 'action=edit' parameter is deliberate. Instagram strips this URL argument in the oAuth
					// process, and would then throw a 400 redirect_uri mismatch error.
					// Envira's API will append the 'action-edit' parameter on the redirect back to this site, ensuring everything
					// works correctly.
					$connect_url = $common->get_oauth_url( 'post.php?post=' . $post->ID );
				}
				?>
				<div class="envira-external-req">

					<h2><?php esc_html_e( 'Instagram Authorization Setup', 'envira-instagram' ); ?></h2>
					<p><?php esc_html_e( 'Before you can create Instagram galleries, you need to authenticate Envira with your Instagram account.', 'envira-instagram' ); ?></p>
					<p>
						<a href="<?php echo esc_url( $connect_url ); ?>" class="button button-primary">
							<?php esc_html_e( 'Click Here to Authenticate Envira with Instagram', 'envira-instagram' ); ?>
						</a>
					</p>
				</div>
				<?php
			} else {

				wp_nonce_field( 'envira_instagram_save_settings', 'envira_instagram_nonce' );

				?>
				<div id="envira-instagram">
					<p class="envira-intro">
						<?php esc_html_e( 'Instagram Settings', 'envira-instagram' ); ?>
						<small>

							<?php esc_html_e( 'The settings below adjust the Instagram options for the gallery.', 'envira-instagram' ); ?>

							<?php if ( apply_filters( 'envira_whitelabel', false ) ) : ?>
								<?php do_action( 'envira_instagram_whitelabel_tab_helptext' ); ?>
							<?php else : ?>
							<br />
								<?php esc_html_e( 'Need some help?', 'envira-instagram' ); ?>
							<a href="http://enviragallery.com/docs/instagram-addon/" class="envira-doc" target="_blank">
								<?php esc_html_e( 'Read the Documentation', 'envira-instagram' ); ?>
							</a>
								or
							<a href="https://www.youtube.com/embed/Um3R-ZCwl2U/?rel=0" class="envira-video" target="_blank">
								<?php esc_html_e( 'Watch a Video', 'envira-instagram' ); ?>
							</a>

							<?php endif; ?>

						</small>
					</p>

					<table class="form-table">
						<tbody>
							<tr id="envira-config-instagram-account">
								<th scope="row">
									<label for="envira-config-instagram-account"><?php esc_html_e( 'Instagram Account', 'envira-instagram' ); ?></label>
								</th>
								<td>
									<select id="envira-config-instagram-account" name="_envira_gallery[instagram_account]">
										<?php foreach ( (array) $common->instagram_accounts() as $i => $data ) : ?>
											<option value="<?php echo esc_html( $data['value'] ); ?>"<?php selected( $data['value'], $instance->get_config( 'instagram_account', $instance->get_config_default( 'instagram_account' ) ) ); ?>><?php echo esc_html( $data['name'] ); ?></option>
										<?php endforeach; ?>
									</select>
									<p class="description">
									<?php
										esc_html_e( 'Choose from one of the ', 'envira-instagram' );
										echo '<a href="' . esc_url( admin_url( 'edit.php?post_type=envira&page=envira-gallery-settings#!envira-tab-instagram' ) ) . '">';
										esc_html_e( ' authenticated Instagram accounts ', 'envira-instagram' );
										echo '.';
									?>
									</p>
								</td>
							</tr>
							<tr id="envira-config-instagram-type-box">
								<th scope="row">
									<label for="envira-config-instagram-type"><?php esc_html_e( 'Feed Type', 'envira-instagram' ); ?></label>
								</th>
								<td>
									<select id="envira-config-instagram-type" name="_envira_gallery[instagram_type]">
										<?php foreach ( (array) $common->instagram_types() as $i => $data ) : ?>
											<option value="<?php echo esc_html( $data['value'] ); ?>"<?php selected( $data['value'], $instance->get_config( 'instagram_type', $instance->get_config_default( 'instagram_type' ) ) ); ?>><?php echo esc_html( $data['name'] ); ?></option>
										<?php endforeach; ?>
									</select>
									<p class="description"><?php esc_html_e( 'The type of images to pull from Instagram.', 'envira-instagram' ); ?></p>
								</td>
							</tr>
							<tr id="envira-config-instagram-number-box">
								<th scope="row">
									<label for="envira-config-instagram-number"><?php esc_html_e( 'Number of Instagram Photos', 'envira-instagram' ); ?></label>
								</th>
								<td>
									<input id="envira-config-instagram-number" type="number" name="_envira_gallery[instagram_number]" value="<?php echo esc_html( $instance->get_config( 'instagram_number', $instance->get_config_default( 'instagram_number' ) ) ); ?>" />
									<p class="description"><?php esc_html_e( 'The number of images to pull from your Instagram feed.', 'envira-instagram' ); ?></p>
								</td>
							</tr>

							<?php

							// account for the fact that instagram no longer has full, revert to selected 'standard_resolution'.
							$image_resolution_config = $instance->get_config( 'instagram_res' ) !== 'full' ? $instance->get_config( 'instagram_res', $instance->get_config_default( 'instagram_res' ) ) : 'standard_resolution';

							?>

							<tr id="soliloquy-config-instagram-res-box">
								<th scope="row">
									<label for="envira-config-instagram-res"><?php esc_html_e( 'Image Resolution', 'envira-instagram' ); ?></label>
								</th>
								<td>
									<select id="envira-config-instagram-res" name="_envira_gallery[instagram_res]">
										<?php foreach ( (array) $common->instagram_resolutions() as $i => $data ) : ?>
											<option value="<?php echo esc_html( $data['value'] ); ?>"<?php selected( $data['value'], $image_resolution_config ); ?>><?php echo esc_html( $data['name'] ); ?></option>
										<?php endforeach; ?>
									</select>
									<p class="description"><?php esc_html_e( 'Determines the image resolution and size to use from Instagram.', 'envira-instagram' ); ?></p>
								</td>
							</tr>
							<tr id="envira-config-instagram-link-box">
								<th scope="row">
									<label for="envira-config-instagram-link"><?php esc_html_e( 'Link to Photo?', 'envira-instagram' ); ?></label>
								</th>
								<td>
									<select id="envira-config-instagram-link" name="_envira_gallery[instagram_link]">
										<?php foreach ( (array) $this->get_instagram_link_options() as $i => $data ) : ?>
											<option value="<?php echo esc_html( $data['value'] ); ?>"<?php selected( $data['value'], $instance->get_config( 'instagram_link', $instance->get_config_default( 'instagram_link' ) ) ); ?>><?php echo esc_html( $data['name'] ); ?></option>
										<?php endforeach; ?>
									</select>
									<?php /* <input id="envira-config-instagram-link" type="checkbox" name="_envira_gallery[instagram_link]" value="<?php echo $instance->get_config( 'instagram_link', $instance->get_config_default( 'instagram_link' ) ); ?>" <?php checked( $instance->get_config( 'instagram_link', $instance->get_config_default( 'instagram_link' ) ), 1 ); ?> /> */ ?>
									<p class="description"><?php esc_html_e( 'Links the photo to its original page on Instagram or directly to the image. If special link is selected image will appear in a lightbox if that is enabled.', 'envira-instagram' ); ?></p>
								</td>
							</tr>
							<tr id="envira-config-instagram-link-target-box">
								<th scope="row">
									<label for="envira-config-instagram-link-target"><?php esc_html_e( 'Open In New Tab?', 'envira-instagram' ); ?></label>
								</th>
								<td>
									<input id="envira-config-instagram-link-target" type="checkbox" name="_envira_gallery[instagram_link_target]" value="<?php echo esc_html( $instance->get_config( 'instagram_link_target', $instance->get_config_default( 'instagram_link_target' ) ) ); ?>" <?php checked( $instance->get_config( 'instagram_link_target', $instance->get_config_default( 'instagram_link_target' ) ), 1 ); ?> />
									<span class="description"><?php esc_html_e( 'Opens the link to Instagram in a new browser tab', 'envira-instagram' ); ?></span>
								</td>
							</tr>

							<tr id="envira-config-instagram-caption-box">
								<th scope="row">
									<label for="envira-config-instagram-caption"><?php esc_html_e( 'Use Photo Caption?', 'envira-instagram' ); ?></label>
								</th>
								<td>
									<input id="envira-config-instagram-caption" type="checkbox" name="_envira_gallery[instagram_caption]" value="<?php echo esc_html( $instance->get_config( 'instagram_caption', $instance->get_config_default( 'instagram_caption' ) ) ); ?>" <?php checked( $instance->get_config( 'instagram_caption', $instance->get_config_default( 'instagram_caption' ) ), 1 ); ?> />
									<span class="description"><?php esc_html_e( 'Displays the photo caption from Instagram on the slide.', 'envira-instagram' ); ?></span>
								</td>
							</tr>
							<tr id="envira-config-instagram-caption-limit-box">
								<th scope="row">
									<label for="envira-config-instagram-caption-limit"><?php esc_html_e( 'Limit Caption Length', 'envira-instagram' ); ?></label>
								</th>
								<td>
									<input id="envira-config-instagram-caption-limit" type="number" name="_envira_gallery[instagram_caption_length]" value="<?php echo esc_html( $instance->get_config( 'instagram_caption_length', $instance->get_config_default( 'instagram_caption_length' ) ) ); ?>" />
									<p class="description"><?php esc_html_e( 'Limits the number of words to display for each caption.', 'envira-instagram' ); ?></p>
								</td>
							</tr>
							<tr id="envira-config-instagram-cache-box">
								<th scope="row">
									<label for="envira-config-instagram-cache"><?php esc_html_e( 'Cache Data from Instagram?', 'envira-instagram' ); ?></label>
								</th>
								<td>
									<input id="envira-config-instagram-cache" type="checkbox" name="_envira_gallery[instagram_cache]" value="<?php echo esc_html( $instance->get_config( 'instagram_cache', $instance->get_config_default( 'instagram_cache' ) ) ); ?>" <?php checked( $instance->get_config( 'instagram_cache', $instance->get_config_default( 'instagram_cache' ) ), 1 ); ?> />
									<span class="description"><?php esc_html_e( 'Caches the data from Instagram to improve performance (recommended).', 'envira-instagram' ); ?></span>
								</td>
							</tr>
							<?php do_action( 'envira_instagram_box', $post ); ?>
						</tbody>
					</table>

				</div>
				<?php
			}

		}

		/**
		 * Helper method for retrieving link options.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of position data.
		 */
		public function get_instagram_link_options() {

			$instance = Envira_Instagram_Common::get_instance();
			return $instance->get_instagram_link_options();

		}

		/**
		 * Outputs a preview of the Instagram Gallery, based on the Gallery Settings.
		 *
		 * @since 1.0.5
		 *
		 * @param   array $data       Gallery.
		 * @return  string              Preview HTML Output
		 */
		public function preview_display( $data ) {

			if ( ! isset( $data['id'] ) ) {
				return;
			}

			// Inject Instagram Images into Gallery.
			$data['gallery'] = Envira_Instagram_Shortcode::get_instance()->_get_instagram_data( $data['id'], $data );

			// Output the preview.
			?>
			<p class="envira-intro">
				<?php esc_html_e( 'Instagram Gallery Preview', 'envira-instagram' ); ?>
			</p>
			<ul id="envira-gallery-preview-output" class="envira-gallery-images-output grid">
				<?php
				if ( ! empty( $data['gallery'] ) ) {
					foreach ( $data['gallery'] as $id => $item ) {
						?>
						<li class="envira-gallery-image">
							<img src="<?php echo esc_url( $item['thumb'] ); ?>" />
							<div class="meta">
								<div class="title"><?php echo ( isset( $item['title'] ) ? esc_html( $item['title'] ) : '' ); ?></div>
							</div>
						</li>
						<?php
					}
				}
				?>
			</ul>
			<?php

		}

		/**
		 * Returns an array of Instagram images for the given Gallery ID, allowing the Albums Addon
		 * to display the images so that the user can choose an image as the cover for that Gallery
		 * within an Album
		 *
		 * @since 1.0.6
		 *
		 * @param   array $images         Gallery Images.
		 * @param   int   $gallery_id     Gallery ID.
		 * @param   array $gallery_data   Gallery Data.
		 * @return  array                   Gallery Images
		 */
		public function albums_inject_images_for_cover_image_selection( $images, $gallery_id, $gallery_data ) {

			// Bail if not an Instagram Gallery.
			if ( 'instagram' !== envira_get_config( 'type', $gallery_data ) ) {
				return $images;
			}

			// Attempt to get images from Instagram for the Gallery.
			$instagram_images = Envira_Instagram_Shortcode::get_instance()->_get_instagram_data( $gallery_id, $gallery_data );

			// If this failed, return the original supplied images.
			if ( ! $instagram_images ) {
				return $images;
			}

			// Instagram images were returned, so return them to the Albums Addon for cover image selection.
			return $instagram_images;

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
		public function save( $settings, $post_id ) {

			// If not saving an Instagram gallery, do nothing.
			if ( ! isset( $_POST['_envira_gallery']['type_instagram'] ) ) {
				return $settings;
			}

			if (
				! isset( $_POST['_envira_gallery'], $_POST['envira_instagram_nonce'] )
				|| ! wp_verify_nonce( sanitize_key( $_POST['envira_instagram_nonce'] ), 'envira_instagram_save_settings' )
			) {
				return $settings;
			}

			// If Instagram isn't authorized, but the user has chosen the Instagram gallery type, we won't have any settings to save
			// Get instances and auth.
			$common = Envira_Instagram_Common::get_instance();
			$auth   = $common->get_instagram_auth();
			if ( empty( $auth['token'] ) ) {
				return $settings;
			}

			// Save the settings.
			$settings['config']['instagram_account']        = isset( $_POST['_envira_gallery']['instagram_account'] ) ? sanitize_text_field( wp_unslash( $_POST['_envira_gallery']['instagram_account'] ) ) : false;
			$settings['config']['instagram_type']           = isset( $_POST['_envira_gallery']['instagram_type'] ) ? sanitize_text_field( wp_unslash( $_POST['_envira_gallery']['instagram_type'] ) ) : false;
			$settings['config']['instagram_number']         = isset( $_POST['_envira_gallery']['instagram_number'] ) ? absint( $_POST['_envira_gallery']['instagram_number'] ) : false;
			$settings['config']['instagram_res']            = isset( $_POST['_envira_gallery']['instagram_res'] ) ? sanitize_text_field( wp_unslash( $_POST['_envira_gallery']['instagram_res'] ) ) : false;
			$settings['config']['instagram_link']           = isset( $_POST['_envira_gallery']['instagram_link'] ) ? sanitize_text_field( wp_unslash( $_POST['_envira_gallery']['instagram_link'] ) ) : false;
			$settings['config']['instagram_link_target']    = isset( $_POST['_envira_gallery']['instagram_link_target'] ) ? 1 : 0;
			$settings['config']['instagram_caption']        = isset( $_POST['_envira_gallery']['instagram_caption'] ) ? 1 : 0;
			$settings['config']['instagram_caption_length'] = isset( $_POST['_envira_gallery']['instagram_caption_length'] ) ? absint( $_POST['_envira_gallery']['instagram_caption_length'] ) : false;
			$settings['config']['instagram_cache']          = isset( $_POST['_envira_gallery']['instagram_cache'] ) ? 1 : 0;

			// Return.
			return $settings;

		}

		/**
		 * Flush Gallery cache on save
		 *
		 * @since 1.0.0
		 *
		 * @param int    $post_id Post ID.
		 * @param string $slug Post Slug.
		 */
		public function flush_caches( $post_id, $slug ) {

			delete_transient( '_envira_instagram_' . $post_id );
			delete_transient( '_envira_instagram_' . $slug );

		}

		/**
		 * Returns the singleton instance of the class.
		 *
		 * @since 1.0.0
		 *
		 * @return object The Envira_Instagram_Metaboxes object.
		 */
		public static function get_instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Instagram_Metaboxes ) ) {
				self::$instance = new Envira_Instagram_Metaboxes();
			}

			return self::$instance;

		}

	}
endif;
