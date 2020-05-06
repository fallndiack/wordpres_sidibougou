<?php
/**
 * Metabox class.
 *
 * @since 1.0.0
 *
 * @package Envira_Instagram
 * @author  Envira Team
 */

namespace Envira\Instagram\Admin;

use Envira\Instagram\Frontend\Shortcode;
use Envira\Admin\Notices;

/**
 * Metaboxes class.
 *
 * @since 1.1.0
 *
 * @package Envira_Gallery
 * @author  Envira Gallery Team <support@enviragallery.com>
 */
class Metaboxes {

	/**
	 * Allowed HTML
	 *
	 * @var mixed
	 * @access public
	 */
	public $wp_kses_allowed_html = array(
		'a'      => array(
			'href'                => array(),
			'target'              => array(),
			'class'               => array(),
			'title'               => array(),
			'data-status'         => array(),
			'data-envira-tooltip' => array(),
			'data-id'             => array(),
		),
		'br'     => array(),
		'img'    => array(
			'src'   => array(),
			'class' => array(),
			'alt'   => array(),
		),
		'div'    => array(
			'class' => array(),
		),
		'li'     => array(
			'id'                              => array(),
			'class'                           => array(),
			'data-envira-gallery-image'       => array(),
			'data-envira-gallery-image-model' => array(),
		),
		'em'     => array(),
		'span'   => array(
			'class' => array(),
		),
		'strong' => array(),
	);

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

		// Metaboxes.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 1 );

		add_action( 'admin_notices', array( $this, 'notice_warnings' ) );

	}

	/**
	 * Adding the Preview metabox. Instagram is the only place where we use it.
	 *
	 * @since 1.5.0
	 */
	public function add_meta_boxes() {

		global $post;

		// bail if nothings there.
		if ( ! isset( $post ) ) {
			return;
		}

		// Check we're on an Envira Gallery.
		if ( 'envira' !== $post->post_type ) {
			return;
		}

		// Get the gallery data.
		$data = get_post_meta( $post->ID, '_eg_gallery_data', true );

		if ( ! isset( $data['config']['type'] ) || 'instagram' !== $data['config']['type'] ) {
			return;
		}

		add_meta_box( 'envira-gallery-preview', __( 'Envira Gallery Preview', 'envira-gallery' ), array( $this, 'meta_box_preview_callback' ), 'envira', 'normal', 'high' );

	}

	/**
	 * Callback for displaying the Preview metabox.
	 *
	 * @since 1.5.0
	 *
	 * @param object $post The current post object.
	 */
	public function meta_box_preview_callback( $post ) {

		// $metabox_instance = Envira_Gallery_Metaboxes::get_instance();
		// $metaboxes  = new Metaboxes();
		// Get the gallery data
		$data = get_post_meta( $post->ID, '_eg_gallery_data', true );

		// Output the display based on the type of slider being created.
		echo '<div id="envira-gallery-preview-main" class="envira-clear">';

		$this->preview_display( envira_get_config( 'type', envira_get_config_default( 'type' ) ), $data );

		echo '</div>
              <div class="spinner"></div>';

	}

	/**
	 * Enqueues JS for the metabox
	 *
	 * @since 1.0.0
	 */
	public function meta_box_scripts() {

		wp_enqueue_script( ENVIRA_INSTAGRAM_SLUG . '-metabox-script', plugins_url( 'assets/js/min/metabox-min.js', ENVIRA_INSTAGRAM_FILE ), array( 'jquery', 'jquery-ui-sortable' ), ENVIRA_INSTAGRAM_VERSION, true );

		wp_enqueue_style( ENVIRA_INSTAGRAM_SLUG . '-metabox-style', plugins_url( 'assets/css/instagram-admin.css', ENVIRA_INSTAGRAM_FILE ), array(), ENVIRA_INSTAGRAM_VERSION );

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
		$auth = envira_instagram_get_instagram_auth();
		$key  = '_envira_gallery';

		wp_nonce_field( 'envira_instagram_save_settings', 'envira_instagram_nonce' );

		if ( empty( $auth['token'] ) ) {
			// Tell the user they need to oAuth with Instagram, and give them the option to do that now.
			// Determine which screen we're on (i.e. New Gallery or Edit Gallery).
			if ( 'auto-draft' === $post->post_status ) {
				$connect_url = envira_instagram_get_oauth_url( 'post-new.php?post_type=envira' );
			} else {
				// Note: the missing 'action=edit' parameter is deliberate. Instagram strips this URL argument in the oAuth
				// process, and would then throw a 400 redirect_uri mismatch error.
				// Envira's API will append the 'action-edit' parameter on the redirect back to this site, ensuring everything
				// works correctly.
				$connect_url = envira_instagram_get_oauth_url( 'post.php?post=' . $post->ID );
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
			?>
			<div id="envira-instagram">
				<p class="envira-intro">
					<?php esc_html_e( 'Instagram Settings', 'envira-instagram' ); ?>
					<div class="important-warning">
						<?php

							/* translators: %s */
							$message = sprintf( __( '<strong>Envira Gallery</strong>: <span>Note: After June 20, 2020, Instagram  <a target="_blank" href="%1$s">will depreciate their API</a>. You will need to upgrade to the latest Envira Gallery Instagram API and re-authenticate to continue displaying Instagram galleries. Check out <a target="_blank" href="%2$s">this documentation</a> for more information.</span>', 'envira-gallery' ), 'https://developers.facebook.com/blog/post/2019/10/15/launch-instagram-basic-display-api/', 'https://enviragallery.com/docs/instagram-api-changes/' );

							echo wp_kses( $message, $this->wp_kses_allowed_html );

						?>
					</div>
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
									<?php foreach ( (array) envira_instagram_instagram_accounts() as $i => $data ) : ?>
										<option value="<?php echo esc_html( $data['value'] ); ?>"<?php selected( $data['value'], $this->get_config( 'instagram_account', envira_get_config_default( 'instagram_account' ) ) ); ?>><?php echo esc_html( $data['name'] ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description">
									<?php /* translators: %s */ ?>
									<?php echo wp_kses( __( 'Choose from one of the <a href="%s">authenticated Instagram accounts</a>.', 'envira-instagram' ), $this->wp_kses_allowed_html, esc_url( admin_url( 'edit.php?post_type=envira&page=envira-gallery-settings#!envira-tab-instagram' ) ) ); ?>

							</td>
						</tr>
						<tr id="envira-config-instagram-type-box">
							<th scope="row">
								<label for="envira-config-instagram-type"><?php esc_html_e( 'Feed Type', 'envira-instagram' ); ?></label>
							</th>
							<td>
								<select id="envira-config-instagram-type" name="_envira_gallery[instagram_type]">
									<?php foreach ( (array) envira_instagram_instagram_types() as $i => $data ) : ?>
										<option value="<?php echo esc_html( $data['value'] ); ?>"<?php selected( $data['value'], $this->get_config( 'instagram_type', envira_get_config_default( 'instagram_type' ) ) ); ?>><?php echo esc_html( $data['name'] ); ?></option>
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
								<input id="envira-config-instagram-number" type="number" name="_envira_gallery[instagram_number]" value="<?php echo esc_html( $this->get_config( 'instagram_number', envira_get_config_default( 'instagram_number' ) ) ); ?>" />
								<p class="description"><?php esc_html_e( 'The number of images to pull from your Instagram feed.', 'envira-instagram' ); ?></p>
							</td>
						</tr>

						<?php

						// account for the fact that instagram no longer has full, revert to selected 'standard_resolution'.
						$image_resolution_config = envira_get_config( 'instagram_res', $data ) !== 'full' ? $this->get_config( 'instagram_res', envira_get_config_default( 'instagram_res' ) ) : 'standard_resolution';

						?>

						<tr id="soliloquy-config-instagram-res-box">
							<th scope="row">
								<label for="envira-config-instagram-res"><?php esc_html_e( 'Image Resolution', 'envira-instagram' ); ?></label>
							</th>
							<td>
								<select id="envira-config-instagram-res" name="_envira_gallery[instagram_res]">
									<?php foreach ( (array) envira_instagram_instagram_resolutions() as $i => $data ) : ?>
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
									<?php foreach ( (array) envira_instagram_get_instagram_link_options() as $i => $data ) : ?>
										<option value="<?php echo esc_html( $data['value'] ); ?>"<?php selected( $data['value'], $this->get_config( 'instagram_link', envira_get_config_default( 'instagram_link' ) ) ); ?>><?php echo esc_html( $data['name'] ); ?></option>
									<?php endforeach; ?>
								</select>
								<?php /* <input id="envira-config-instagram-link" type="checkbox" name="_envira_gallery[instagram_link]" value="<?php echo envira_get_config( 'instagram_link', envira_get_config_default( 'instagram_link' ) ); ?>" <?php checked( envira_get_config( 'instagram_link', envira_get_config_default( 'instagram_link' ) ), 1 ); ?> /> */ ?>
								<p class="description"><?php esc_html_e( 'Links the photo to its original page on Instagram or directly to the image. If special link is selected image will appear in a lightbox if that is enabled.', 'envira-instagram' ); ?></p>
							</td>
						</tr>
						<tr id="envira-config-instagram-link-target-box">
							<th scope="row">
								<label for="envira-config-instagram-link-target"><?php esc_html_e( 'Open In New Tab?', 'envira-instagram' ); ?></label>
							</th>
							<td>
								<input id="envira-config-instagram-link-target" type="checkbox" name="_envira_gallery[instagram_link_target]" value="<?php echo esc_html( $this->get_config( 'instagram_link_target', envira_get_config_default( 'instagram_link_target' ) ) ); ?>" <?php checked( $this->get_config( 'instagram_link_target', envira_get_config_default( 'instagram_link_target' ) ), 1 ); ?> />
								<span class="description"><?php esc_html_e( 'Opens the link to Instagram in a new browser tab', 'envira-instagram' ); ?></span>
							</td>
						</tr>

						<tr id="envira-config-instagram-caption-box">
							<th scope="row">
								<label for="envira-config-instagram-caption"><?php esc_html_e( 'Use Photo Caption?', 'envira-instagram' ); ?></label>
							</th>
							<td>
								<input id="envira-config-instagram-caption" type="checkbox" name="_envira_gallery[instagram_caption]" value="<?php echo esc_html( $this->get_config( 'instagram_caption', envira_get_config_default( 'instagram_caption' ) ) ); ?>" <?php checked( $this->get_config( 'instagram_caption', envira_get_config_default( 'instagram_caption' ) ), 1 ); ?> />
								<span class="description"><?php esc_html_e( 'Displays the photo caption from Instagram on the slide.', 'envira-instagram' ); ?></span>
							</td>
						</tr>
						<tr id="envira-config-instagram-caption-limit-box">
							<th scope="row">
								<label for="envira-config-instagram-caption-limit"><?php esc_html_e( 'Limit Caption Length', 'envira-instagram' ); ?></label>
							</th>
							<td>
								<input id="envira-config-instagram-caption-limit" type="number" name="_envira_gallery[instagram_caption_length]" value="<?php echo esc_html( $this->get_config( 'instagram_caption_length', envira_get_config_default( 'instagram_caption_length' ) ) ); ?>" />
								<p class="description"><?php esc_html_e( 'Limits the number of words to display for each caption.', 'envira-instagram' ); ?></p>
							</td>
						</tr>
						<tr id="envira-config-instagram-cache-box">
							<th scope="row">
								<label for="envira-config-instagram-cache"><?php esc_html_e( 'Cache Data from Instagram?', 'envira-instagram' ); ?></label>
							</th>
							<td>
								<input id="envira-config-instagram-cache" type="checkbox" name="_envira_gallery[instagram_cache]" value="<?php echo esc_html( $this->get_config( 'instagram_cache', envira_get_config_default( 'instagram_cache' ) ) ); ?>" <?php checked( $this->get_config( 'instagram_cache', envira_get_config_default( 'instagram_cache' ) ), 1 ); ?> />
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
	 * Outputs a preview of the Instagram Gallery, based on the Gallery Settings.
	 *
	 * @since 1.0.5
	 *
	 * @param   array $data       Gallery Data.
	 * @return  string              Preview HTML Output
	 */
	public function preview_display( $data ) {

		if ( ! isset( $data['id'] ) ) {
			return;
		}

		$instagram_shortcode = new Shortcode();

		// Inject Instagram Images into Gallery.
		$data['gallery'] = $instagram_shortcode->_get_instagram_data( $data['id'], $data );

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

		$instagram_shortcode = new Shortcode();

		// Attempt to get images from Instagram for the Gallery.
		$instagram_images = $instagram_shortcode->_get_instagram_data( $gallery_id, $gallery_data );

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

		if (
			! isset( $_POST['_envira_gallery'], $_POST['envira_instagram_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['envira_instagram_nonce'] ), 'envira_instagram_save_settings' )
		) {
			return $settings;
		}

		// If not saving an Instagram gallery, do nothing.
		if ( ! isset( $_POST['_envira_gallery']['type_instagram'] ) ) {
			return $settings;
		}

		// If Instagram isn't authorized, but the user has chosen the Instagram gallery type, we won't have any settings to save
		// Get instances and auth.
		$auth = envira_instagram_get_instagram_auth();
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
	 * Outputs a notice warning.
	 *
	 * @since 1.7.0
	 */
	public function notice_warnings() {

		$notices = new Notices();

		/* translators: %s */
		$message = sprintf( __( '<strong>Envira Gallery</strong>: <span>Note: After June 20, 2020, Instagram  <a target="_blank" href="%1$s">will depreciate their API</a>. You will need to upgrade to the latest Envira Gallery Instagram API and re-authenticate to continue displaying Instagram galleries. Check out <a target="_blank" href="%2$s">this documentation</a> for more information.</span></<strong>', 'envira-gallery' ), 'https://developers.facebook.com/blog/post/2019/10/15/launch-instagram-basic-display-api/', 'https://enviragallery.com/docs/instagram-api-changes/' );

		$notices->display_inline_notice( 'warning-instagram-api', false, $message, 'error', false, false, true, DAY_IN_SECONDS );

	}

	/**
	 * Getting config
	 *
	 * @since 1.5.0
	 *
	 * @param object $key The key.
	 * @param object $default Default.
	 */
	public function get_config( $key, $default = false ) {
		global $id, $post;
		// Get the current post ID. If ajax, grab it from the $_POST variable.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && array_key_exists( 'post_id', $_POST ) ) { // @codingStandardsIgnoreLine - nonce
			$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : false; // @codingStandardsIgnoreLine - nonce
		} else {
			$post_id = isset( $post->ID ) ? $post->ID : (int) $id;
		}
		// Get config.
		$settings = get_post_meta( $post_id, '_eg_gallery_data', true );
		// Check config key exists.
		if ( isset( $settings['config'] ) && isset( $settings['config'][ $key ] ) ) {
			return $settings['config'][ $key ];
		} else {
			return $default ? $default : '';
		}
	}

}
