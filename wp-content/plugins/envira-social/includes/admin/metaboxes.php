<?php
/**
 * Metabox class.
 *
 * @since 1.0.0
 *
 * @package Envira_Social
 * @author  Envira Team
 */

use Envira\Admin\Notices;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Metabox class.
 *
 * @since 1.0.0
 *
 * @package Envira_Social
 * @author  Envira Team
 */
class Envira_Social_Metaboxes {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Holds the base class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $base;

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

		$this->base = Envira_Social::get_instance();

		add_action( 'envira_gallery_metabox_scripts', array( $this, 'metabox_scripts' ) );
		add_action( 'envira_albums_metabox_scripts', array( $this, 'metabox_scripts' ) );

		// Notices.
		add_action( 'admin_notices', array( $this, 'notice' ) );

		// Envira Gallery.
		add_filter( 'envira_gallery_tab_nav', array( $this, 'register_tabs' ) );
		add_action( 'envira_gallery_tab_social', array( $this, 'social_tab' ) );
		add_action( 'envira_gallery_mobile_box', array( $this, 'mobile_screen' ) );
		add_action( 'envira_gallery_mobile_lightbox_box', array( $this, 'mobile_lightbox_screen' ) );
		add_filter( 'envira_gallery_save_settings', array( $this, 'gallery_settings_save' ), 10, 2 );

		// Envira Album.
		add_filter( 'envira_albums_tab_nav', array( $this, 'register_tabs' ) );
		add_action( 'envira_albums_tab_social', array( $this, 'social_tab' ) );
		add_action( 'envira_albums_mobile_box', array( $this, 'mobile_screen' ) );
		add_action( 'envira_albums_mobile_lightbox_box', array( $this, 'mobile_lightbox_screen' ) );
		add_filter( 'envira_albums_save_settings', array( $this, 'album_settings_save' ), 10, 2 );
	}

	/**
	 * Initializes scripts for the metabox admin.
	 *
	 * @since 1.0.0
	 */
	public function metabox_scripts() {

		$version = ( defined( 'ENVIRA_DEBUG' ) && 'true' === ENVIRA_DEBUG ) ? $version = time() . '-' . ENVIRA_VERSION : ENVIRA_VERSION;
		// Conditional Fields.
		wp_register_script( $this->base->plugin_slug . '-conditional-fields-script', plugins_url( 'assets/js/conditional-fields.js', $this->base->file ), array( 'jquery', Envira_Gallery::get_instance()->plugin_slug . '-conditional-fields-script' ), $this->base->version, true );

		wp_enqueue_script( $this->base->plugin_slug . '-conditional-fields-script' );

		wp_register_style( $this->base->plugin_slug . '-social-admin', plugins_url( 'assets/css/social-admin.css', $this->base->file ), array(), $version );
		wp_enqueue_style( $this->base->plugin_slug . '-social-admin' );
	}

	/**
	 * Show a notice if the plugin settings haven't been configured
	 *
	 * These are required to ensure that Facebook and Twitter sharing doesn't throw errors
	 *
	 * @since 1.0.4
	 */
	public function notice() {

		// Check if we have required config options.
		$common           = Envira_Social_Common::get_instance();
		$facebook_app_id  = $common->get_setting( 'facebook_app_id' );
		$twitter_username = $common->get_setting( 'twitter_username' );

		if ( empty( $facebook_app_id ) || empty( $twitter_username ) ) {

			$notices = new Notices();
			$message = sprintf(
				/* translators: %s: URL */
				__( '<strong>Envira Gallery:</strong> The Social Addon requires configuration with Facebook and Twitter. Please visit the <a href="%s" title="Settings" target="_blank">Settings</a> screen to complete setup.', 'envira-social' ),
				esc_url(
					add_query_arg(
						array(
							'post_type' => 'envira',
							'page'      => 'envira-gallery-settings',
						),
						admin_url( 'edit.php' )
					)
				)
			);

			$notices->display_inline_notice( 'warning-social-fb-tw', false, $message, 'error', $button_text = '', $button_url = '', true, DAY_IN_SECONDS );

		}

	}

	/**
	 * Registers tab(s) for this Addon in the Settings screen
	 *
	 * @since 1.0.0
	 *
	 * @param   array $tabs   Tabs.
	 * @return  array Tabs
	 */
	public function register_tabs( $tabs ) {

		$tabs['social'] = __( 'Social', 'envira-social' );
		return $tabs;

	}

	/**
	 * Adds addon settings UI to the Social tab
	 *
	 * @since 1.0.0
	 *
	 * @param object $post The current post object.
	 */
	public function social_tab( $post ) {

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

		// Gallery options only apply to Galleries, not Albums.
		wp_nonce_field( 'envira_social_save_settings', 'envira_social_nonce' );

		?>
			<p class="envira-intro">
				<?php esc_html_e( 'Social Gallery Settings', 'envira-social' ); ?>

					<small>

						<?php esc_html_e( 'The settings below adjust the Social Sharing options for the Gallery output.', 'envira-social' ); ?>

						<?php if ( apply_filters( 'envira_whitelabel', false ) ) : ?>

							<?php do_action( 'envira_social_whitelabel_tab_helptext' ); ?>

						<?php else : ?>


									<br />
							<?php esc_html_e( 'Need some help?', 'envira-social' ); ?>
				<a href="http://enviragallery.com/docs/social-addon/" class="envira-doc" target="_blank">
							<?php esc_html_e( 'Read the Documentation', 'envira-social' ); ?>
				</a>
				or
				<a href="https://www.youtube.com/embed/FQhR0PA9skQ/?rel=0" class="envira-video" target="_blank">
							<?php esc_html_e( 'Watch a Video', 'envira-social' ); ?>
				</a>
						<?php endif; ?>
					</small>

			</p>
			<table class="form-table">
				<tbody>
					<tr id="envira-config-social-box">
						<th scope="row">
							<label for="envira-config-social"><?php esc_html_e( 'Display Social Sharing Buttons?', 'envira-social' ); ?></label>
						</th>
						<td>
							<input id="envira-config-social" type="checkbox" name="<?php echo esc_html( $key ); ?>[social]" value="1" <?php checked( $instance->get_config( 'social', $instance->get_config_default( 'social' ) ), 1 ); ?> />
							<span class="description"><?php esc_html_e( 'Enables or disables displaying social sharing buttons on each image in the gallery view.', 'envira-social' ); ?></span>
						</td>
					</tr>

					<tr id="envira-config-social-networks-box">
						<th scope="row">
							<label><?php esc_html_e( 'Social Buttons', 'envira-social' ); ?></label>
						</th>
						<td>
							<?php
							foreach ( $this->get_networks() as $network => $name ) {
								?>
								<label for="envira-config-social-<?php echo esc_html( $network ); ?>" class="label-for-checkbox">
									<input id="envira-config-social-<?php echo esc_html( $network ); ?>" type="checkbox" name="<?php echo esc_html( $key ); ?>[social_<?php echo esc_html( $network ); ?>]" value="1" <?php checked( $instance->get_config( 'social_' . $network, $instance->get_config_default( 'social_' . $network ) ), 1 ); ?> />
									<?php echo esc_html( $name ); ?>
								</label>
								<?php
							}
							?>
						</td>
					</tr>
					<tr id="envira-config-social-position-box">
						<th scope="row">
							<label for="envira-config-social-position"><?php esc_html_e( 'Social Buttons Position', 'envira-social' ); ?></label>
						</th>
						<td>
							<select id="envira-config-social-position" name="<?php echo esc_html( $key ); ?>[social_position]">
								<?php foreach ( (array) $this->get_positions() as $value => $name ) : ?>
									<option value="<?php echo esc_html( $value ); ?>"<?php selected( $value, $instance->get_config( 'social_position', $instance->get_config_default( 'social_position' ) ) ); ?>><?php echo esc_html( $name ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'Where to display the social sharing buttons over the image.', 'envira-social' ); ?></p>
						</td>
					</tr>

					<tr id="envira-config-social-orientation-box">
						<th scope="row">
							<label for="envira-config-social-orientation"><?php esc_html_e( 'Social Buttons Orientation', 'envira-social' ); ?></label>
						</th>
						<td>
							<select id="envira-config-social-orientation" name="<?php echo esc_html( $key ); ?>[social_orientation]">
								<?php foreach ( (array) $this->get_orientations() as $value => $name ) : ?>
									<option value="<?php echo esc_html( $value ); ?>"<?php selected( $value, $instance->get_config( 'social_orientation', $instance->get_config_default( 'social_orientation' ) ) ); ?>><?php echo esc_html( $name ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'Displays the social sharing buttons horizontally or vertically.', 'envira-social' ); ?></p>
						</td>
					</tr>



				</tbody>
			</table>
			<?php
			// }
			// Lightbox Options
			?>
		<p class="envira-intro">
			<?php esc_html_e( 'Social Lightbox Settings', 'envira-social' ); ?>

					<small>

						<?php esc_html_e( 'The settings below adjust the Social Sharing options for the Lightbox output.', 'envira-social' ); ?>

						<?php if ( apply_filters( 'envira_whitelabel', false ) ) : ?>

							<?php do_action( 'envira_social_lightbox_whitelabel_tab_helptext' ); ?>

						<?php else : ?>


						<?php endif; ?>
					</small>


		</p>
		<table class="form-table">
			<tbody>
				<tr id="envira-config-social-lightbox-box">
					<th scope="row">
						<label for="envira-config-social-lightbox"><?php esc_html_e( 'Display Social Sharing Buttons?', 'envira-social' ); ?></label>
					</th>
					<td>
						<input id="envira-config-social-lightbox" type="checkbox" name="<?php echo esc_html( $key ); ?>[social_lightbox]" value="1" <?php checked( $instance->get_config( 'social_lightbox', $instance->get_config_default( 'social_lightbox' ) ), 1 ); ?> />
						<span class="description"><?php esc_html_e( 'Enables or disables displaying social sharing buttons on each image in the Lightbox view.', 'envira-social' ); ?></span>
					</td>
				</tr>
				<tr id="envira-config-social-lightbox-networks-box">
					<th scope="row">
						<label><?php esc_html_e( 'Social Networks', 'envira-social' ); ?></label>
					</th>
					<td>
						<?php
						foreach ( $this->get_networks() as $network => $name ) {
							?>
							<label for="envira-config-social-lightbox-<?php echo esc_html( $network ); ?>" class="label-for-checkbox">
								<input id="envira-config-social-lightbox-<?php echo esc_html( $network ); ?>" type="checkbox" name="<?php echo esc_html( $key ); ?>[social_lightbox_<?php echo esc_html( $network ); ?>]" value="1" <?php checked( $instance->get_config( 'social_lightbox_' . $network, $instance->get_config_default( 'social_lightbox_' . $network ) ), 1 ); ?> />
								<?php echo esc_html( $name ); ?>
							</label>
							<?php
						}
						?>
					</td>
				</tr>


				<tr id="envira-config-social-lightbox-position-box">
					<th scope="row">
						<label for="envira-config-social-lightbox-position"><?php esc_html_e( 'Social Buttons Position', 'envira-social' ); ?></label>
					</th>
					<td>
						<select id="envira-config-social-lightbox-position" name="<?php echo esc_html( $key ); ?>[social_lightbox_position]">
							<?php foreach ( (array) $this->get_positions() as $value => $name ) : ?>
								<option value="<?php echo esc_html( $value ); ?>"<?php selected( $value, $instance->get_config( 'social_lightbox_position', $instance->get_config_default( 'social_lightbox_position' ) ) ); ?>><?php echo esc_html( $name ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Where to display the social sharing buttons over the image.', 'envira-social' ); ?></p>
					</td>
				</tr>



				<tr id="envira-config-social-lightbox-orientation-box">
					<th scope="row">
						<label for="envira-config-social-lightbox-orientation"><?php esc_html_e( 'Social Buttons Orientation', 'envira-social' ); ?></label>
					</th>
					<td>
						<select id="envira-config-social-lightbox-orientation" name="<?php echo esc_html( $key ); ?>[social_lightbox_orientation]">
							<?php foreach ( (array) $this->get_orientations() as $value => $name ) : ?>
								<option value="<?php echo esc_html( $value ); ?>"<?php selected( $value, $instance->get_config( 'social_lightbox_orientation', $instance->get_config_default( 'social_lightbox_orientation' ) ) ); ?>><?php echo esc_html( $name ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Displays the social sharing buttons horizontally or vertically.', 'envira-social' ); ?></p>
					</td>
				</tr>

				<tr id="envira-config-social-lightbox-outside-box">
					<th scope="row">
						<label for="envira-config-social-outside"><?php esc_html_e( 'Display Social Buttons Outside of Image?', 'envira-social' ); ?></label>
					</th>
					<td>
						<input id="envira-config-social-lightbox-outside" type="checkbox" name="<?php echo esc_html( $key ); ?>[social_lightbox_outside]" value="1" <?php checked( $instance->get_config( 'social_lightbox_outside', $instance->get_config_default( 'social_lightbox_outside' ) ), 1 ); ?> />
						<span class="description"><?php esc_html_e( 'If enabled, displays the social sharing buttons outside of the lightbox/image frame.', 'envira-social' ); ?></span>
					</td>
				</tr>

			</tbody>
		</table>

		<div id="envira-social-advanced-settings">

			<p class="envira-intro">
				<?php esc_html_e( 'Advanced Settings', 'envira-social' ); ?>
				<small>
					<?php esc_html_e( 'The settings below apply to social sharing in both lighboxes and galleries.', 'envira-social' ); ?>
					<br />
					<?php echo sprintf( wp_kses( __( '<strong>Note:</strong> There are no options currently available for <strong>WhatsApp</strong> (available in Mobile tab) and <strong>LinkedIn</strong>.', 'envira-social' ), $this->wp_kses_allowed_html ) ); ?>
				</small>
			</p>

			<?php $key = '_general'; ?>

				<table class="form-table facebook-settings">
					<thead>
						<td colspan="2" scope="row" style="padding:0;">
							<h3 class="social-heading" style="font-size: 1.1em; padding: 0; text-indent: 0px; background-color: #fff;  width: 100%; line-height: 50px;">
								<img style="display:inline-block; margin: 10px 10px 10px 0; float: left;" width="30" height="30" src="<?php echo esc_html( $this->base->path ); ?>assets/images/admin_facebook.svg" alt="Facebook" />
								<span style="display:inline-block; height: 100%; vertical-align: middle; color: #000;"><?php esc_html_e( 'Facebook Options', 'envira-social' ); ?></span>
							</h3>
						</th>
					</thead>
					<tbody>
						<tr id="envira-config-social-networks-facebook-what-to-show">
							<th scope="row">
								<label><?php esc_html_e( 'What To Share', 'envira-social' ); ?></label>
							</th>
							<td>
								<?php
								foreach ( $this->get_facebook_show_options() as $option_value => $option_name ) {
									?>
									<label for="envira-config-social-<?php echo esc_html( $option_value ); ?>" class="label-for-checkbox">
										<input id="envira-config-social-<?php echo esc_html( $option_value ); ?>" type="checkbox" name="<?php echo esc_html( $key ); ?>[social_facebook_show_option_<?php echo esc_html( $option_value ); ?>]" value="1" <?php checked( $instance->get_config( 'social_facebook_show_option_' . $option_value, $instance->get_config_default( 'social_facebook_show_option_' . $option_value ) ), 1 ); ?> />
										<?php echo esc_html( $option_name ); ?>
									</label>
									<?php
								}
								?>
								<p class="description">
									<?php esc_html_e( 'Select the information that should be shared with each image.', 'envira-social' ); ?>
								</p>
							</td>
						</tr>
						<tr id="envira-config-social-networks-facebook-box">
							<th scope="row">
								<label for="envira-config-social-networks-facebook"><?php esc_html_e( 'Facebook Optional Text', 'envira-social' ); ?></label>
							</th>
							<td>
								<input id="envira-config-social-networks-facebook" type="text" name="<?php echo esc_html( $key ); ?>[social_facebook_text]" value="<?php echo esc_html( $instance->get_config( 'social_facebook_text', $instance->get_config_default( 'social_facebook_text' ) ) ); ?>" />
								<p class="description">
									<?php esc_html_e( 'Enter an optional message to append to Facebook shares. The image, image URL, title and caption are automatically shared.', 'envira-social' ); ?>
								</p>
							</td>
						</tr>

						<tr id="envira-config-social-networks-facebook-tags-options">
							<th scope="row">
								<label><?php esc_html_e( 'Facebook Tag Options', 'envira-social' ); ?></label>
							</th>
							<td>
								<select id="envira-config-social-position" name="<?php echo esc_html( $key ); ?>[social_facebook_tag_options]">
									<?php foreach ( (array) $this->get_facebook_show_tag_options() as $value => $name ) : ?>
										<option value="<?php echo esc_html( $value ); ?>"<?php selected( $value, $instance->get_config( 'social_facebook_tag_options', $instance->get_config_default( 'social_facebook_tag_options' ) ) ); ?>><?php echo esc_html( $name ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description">
									<?php
										esc_html_e( 'You can manually set one tag for all gallery images or automatically use tags assigned with the', 'envira-social' );
										echo ' <a href="http://enviragallery.com/addons/tags-addon/" target="_blank">';
										esc_html_e( 'Tags Addon', 'envira-social' );
										echo '</a> ';
										esc_html_e( '. Note that you are allowed only one Facebook tag when using the Manual option.', 'envira-social' );
									?>
								</p>
							</td>
						</tr>

						<tr id="envira-config-social-networks-facebook-tags-options-manual">
							<th scope="row">
								<label for="envira-config-social-networks-facebook-tags-options-manual"><?php esc_html_e( 'Facebook Hashtag', 'envira-social' ); ?></label>
							</th>
							<td>
								<input id="envira-config-social-networks-facebook-tags-options-manual" name="<?php echo esc_html( $key ); ?>[social_facebook_tags_manual]" value="<?php echo esc_html( $instance->get_config( 'social_facebook_tags_manual', $instance->get_config_default( 'social_facebook_tags_manual' ) ) ); ?>" />
								<p class="description">
									<?php
										esc_html_e( 'Add one tag, starting with the "#" symbol.', 'envira-social' );
										echo '<strong>';
										esc_html_e( ' Example:', 'envira-social' );
										echo '</strong>';
										esc_html_e( ' #Envira.', 'envira-social' );
									?>
								</p>
							</td>
						</tr>

						<tr id="envira-config-social-networks-facebook-quote">
							<th scope="row">
								<label for="envira-config-social-networks-facebook-quote"><?php esc_html_e( 'Facebook Quote', 'envira-social' ); ?></label>
							</th>
							<td>
								<textarea id="envira-config-social-networks-facebook-quote" name="<?php echo esc_html( $key ); ?>[social_facebook_quote]"><?php echo esc_html( $instance->get_config( 'social_facebook_quote', $instance->get_config_default( 'social_facebook_quote' ) ) ); ?></textarea>
								<p class="description">
									<?php echo sprintf( wp_kses( __( 'Add a short text statement to be included with each image shared from this gallery. <a href="http://enviragallery.com/docs/social-addon/" target="_blank">See our documentation</a> for additional information. Field accepts text only, no HTML.', 'envira-social' ), $this->wp_kses_allowed_html ) ); ?>
								</p>
							</td>
						</tr>
					</tbody>
				</table>

				<table class="form-table">
					<thead>
						<td colspan="2" scope="row" style="padding:0;">
							<h3 class="social-heading" style="font-size: 1.1em; padding: 0; text-indent: 0px; background-color: #fff; width: 100%; line-height: 50px;">
								<img style="display:inline-block; margin: 10px 10px 10px 0; float: left;" width="30" height="30" src="<?php echo esc_html( $this->base->path ); ?>assets/images/admin_pinterest.svg" alt="Pinterest" />
								<span style="display:inline-block; height: 100%; vertical-align: middle; color: #000"><?php esc_html_e( 'Pinterest Options', 'envira-social' ); ?></span>
							</h3>
						</th>
					</thead>
					<tbody>

						<tr id="envira-config-social-networks-pinterest-title">
							<th scope="row">
								<label for="envira-config-social-networks-pinterest-title"><?php esc_html_e( 'Pinterest Shared Title', 'envira-social' ); ?></label>
							</th>
							<td>
							<?php

							$post_type = get_post_type( $post );
							switch ( $post_type ) {
								/**
								* Gallery
								*/
								case 'envira':
									?>
								<select id="envira-config-social-networks-pinterest-title" name="<?php echo esc_html( $key ); ?>[social_pinterest_title]">
									<?php foreach ( (array) $this->get_pinterest_title_options() as $value => $name ) : ?>
										<option value="<?php echo esc_html( $value ); ?>"<?php selected( $value, $instance->get_config( 'social_pinterest_title', $instance->get_config_default( 'social_pinterest_title' ) ) ); ?>><?php echo esc_html( $name ); ?></option>
									<?php endforeach; ?>
								</select>
									<?php
									break;

								/**
								* Album
								*/
								case 'envira_album':
									?>
								<select id="envira-config-social-networks-pinterest-title" name="<?php echo esc_html( $key ); ?>[social_pinterest_title]">
									<?php foreach ( (array) $this->get_pinterest_title_album_options() as $value => $name ) : ?>
										<option value="<?php echo esc_html( $value ); ?>"<?php selected( $value, $instance->get_config( 'social_pinterest_title', $instance->get_config_default( 'social_pinterest_title' ) ) ); ?>><?php echo esc_html( $name ); ?></option>
									<?php endforeach; ?>
								</select>
									<?php
									break;
							}

							?>

								<p class="description">
									<?php esc_html_e( 'Define what to pass along as a title with the image being shared.', 'envira-social' ); ?>
								</p>
							</td>
						</tr>

						<tr id="envira-config-social-networks-pinterest-type">
							<th scope="row">
								<label for="envira-config-social-networks-pinterest-type"><?php esc_html_e( 'Pinterest Sharing Type', 'envira-social' ); ?></label>
							</th>
							<td>
								<select id="envira-config-social-networks-pinterest-type" name="<?php echo esc_html( $key ); ?>[social_pinterest_type]">
									<?php foreach ( (array) $this->get_pinterest_share_options() as $value => $name ) : ?>
										<option value="<?php echo esc_html( $value ); ?>"<?php selected( $value, $instance->get_config( 'social_pinterest_type', $instance->get_config_default( 'social_pinterest_type' ) ) ); ?>><?php echo esc_html( $name ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description">
									<?php esc_html_e( 'Pin One includes the image and caption of the image being shared. Pin All displays all available images and allows users to select the specific image they wish to share.', 'envira-social' ); ?>
								</p>
							</td>
						</tr>

						<tr id="envira-config-social-networks-pinterest-rich-row">
							<th scope="row">
								<label for="envira-config-social-networks-pinterest-rich"><?php esc_html_e( 'Pinterest Rich Pins', 'envira-social' ); ?></label>
							</th>
							<td>
								<input id="envira-config-social-networks-pinterest-rich" type="checkbox" name="<?php echo esc_html( $key ); ?>[social_pinterest_rich]" value="1" <?php checked( $instance->get_config( 'social_pinterest_rich', $instance->get_config_default( 'social_pinterest_rich' ) ), 1 ); ?> />
								<span class="description"><?php echo sprintf( wp_kses( __( 'Enable Pinterest\'s Rich Pins on the page where this gallery is displayed. Important: Pinterest must pre-approve your site for this option to work. <a href="http://enviragallery.com/docs/social-addon/" target="_blank">See our documentation</a> for additional information.', 'envira-social' ), $this->wp_kses_allowed_html ) ); ?></span>
							</td>
						</tr>

					</tbody>
				</table>

				<table class="form-table">
					<thead>
						<td colspan="2" scope="row" style="padding:0;">
							<h3 class="social-heading" style="font-size: 1.1em; padding: 0; text-indent: 0px; background-color: #fff; width: 100%; line-height: 50px;">
								<img style="display:inline-block; margin: 10px 10px 10px 0; float: left;" width="30" height="30" src="<?php echo esc_html( $this->base->path ); ?>assets/images/admin_twitter.svg" alt="Twitter" />
								<span style="display:inline-block; height: 100%; vertical-align: middle; color: #000"><?php esc_html_e( 'Twitter Options', 'envira-social' ); ?></span>
							</h3>
						</th>
					</thead>
					<tbody>
						<tr id="envira-config-social-networks-twitter-box">
							<th scope="row">
								<label for="envira-config-social-networks-twitter"><?php esc_html_e( 'Twitter Optional Text', 'envira-social' ); ?></label>
							</th>
							<td>
								<input id="envira-config-social-networks-twitter" type="text" name="<?php echo esc_html( $key ); ?>[social_twitter_text]" value="<?php echo esc_html( $instance->get_config( 'social_twitter_text', $instance->get_config_default( 'social_twitter_text' ) ) ); ?>" />
								<p class="description">
									<?php esc_html_e( 'Enter an optional message to append to Tweets. The image, image URL and caption are automatically shared.', 'envira-social' ); ?>
								</p>
							</td>
						</tr>

						<tr id="envira-config-social-networks-twitter-summary-card">
							<th scope="row">
								<label for="envira-config-social-networks-twitter-summary-card"><?php esc_html_e( 'Twitter Summary Card', 'envira-social' ); ?></label>
							</th>
							<td>
								<select id="envira-config-social-networks-twitter-summary-card" name="<?php echo esc_html( $key ); ?>[social_twitter_sharing_method]">
									<?php foreach ( (array) $this->get_twitter_sharing_methods() as $value => $name ) : ?>
										<option value="<?php echo esc_html( $value ); ?>"<?php selected( $value, $instance->get_config( 'social_twitter_sharing_method', $instance->get_config_default( 'social_twitter_sharing_method' ) ) ); ?>><?php echo esc_html( $name ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description">
									<?php esc_html_e( 'Twitter summary cards share additional details (such as an image, title, and caption) than a standard text Tweet.', 'envira-social' ); ?><br/>

									<?php echo sprintf( wp_kses( __( '<strong>No Summary Card</strong> disables Summary Cards and shares only a text link.', 'envira-social' ), $this->wp_kses_allowed_html ) ); ?><br />

									<?php echo sprintf( wp_kses( __( '<strong>Summary Card + Thumbnail</strong> shares a 120 x 120 pixel image with Title and Caption. <a href="https://dev.twitter.com/cards/types/summary">Learn more</a>.', 'envira-social' ), $this->wp_kses_allowed_html ) ); ?><br />

									<?php echo sprintf( wp_kses( __( '<strong>Summary Card + Large Image</strong> shares a larger image with Title and Caption. <a href="https://dev.twitter.com/cards/types/summary-large-image">Learn more</a>.', 'envira-social' ), $this->wp_kses_allowed_html ) ); ?><br />

									<?php echo sprintf( wp_kses( __( 'The image shared from the gallery is used for the Summary Card image. <a href="http://enviragallery.com/docs/social-addon/" target="_blank">See our documentation</a> for additional information.', 'envira-social' ), $this->wp_kses_allowed_html ) ); ?><br />

								</p>
							</td>
						</tr>

						<tr id="envira-config-social-networks-twitter-summary-card-site">
							<th scope="row">
								<label for="envira-config-social-networks-twitter-summary-card-site"><?php esc_html_e( 'Twitter Summary Card Username', 'envira-social' ); ?></label>
							</th>
							<td>
								<input id="envira-config-social-networks-twitter-summary-card-site" type="text" name="<?php echo esc_html( $key ); ?>[social_twitter_summary_card_site]" value="<?php echo esc_html( $instance->get_config( 'social_twitter_summary_card_site', $instance->get_config_default( 'social_twitter_summary_card_site' ) ) ); ?>" />
								<p class="description">
									<?php echo sprintf( wp_kses( __( 'The Twitter username to attribute the Summary Card to, starting with the "@" sign. <strong>Example:</strong> @enviragallery', 'envira-social' ), $this->wp_kses_allowed_html ) ); ?>
								</p>
							</td>
						</tr>

						<tr id="envira-config-social-networks-twitter-summary-card-desc">
							<th scope="row">
								<label for="envira-config-social-networks-twitter-summary-card-desc"><?php esc_html_e( 'Twitter Summary Card Description', 'envira-social' ); ?></label>
							</th>				

							<td>
								<textarea id="envira-config-social-networks-twitter-summary-card-desc" name="<?php echo esc_html( $key ); ?>[social_twitter_summary_card_desc]"><?php echo esc_html( $instance->get_config( 'social_twitter_summary_card_desc', $instance->get_config_default( 'social_twitter_summary_card_desc' ) ) ); ?></textarea>
								<p class="description">
									<?php echo sprintf( wp_kses( __( 'Twitter requires a description for Summary Cards. Envira will attempt to find and use the image caption, gallery description, or Twitter Optional Text setting (in that order). Optionally, Envira can pass the custom text entered in this field to Twitter instead. <a href="http://enviragallery.com/docs/social-addon/" target="_blank">See our documentation</a> for additional information.', 'envira-social' ), $this->wp_kses_allowed_html ) ); ?>
								</p>
							</td>
						</tr>

					</tbody>
				</table>

				<?php
				/*
				<table class="form-table">
					<thead>
						<td colspan="2" scope="row" style="padding:0;">
							<h3 class="social-heading" style="font-size: 1.1em; padding: 0; text-indent: 0px; background-color: #fff; color: #fff; width: 100%; line-height: 50px;">
								<img style="display:inline-block; margin: 10px 10px 10px 0; float: left;" width="30" height="30" src="<?php echo esc_html( $this->base->path ); ?>assets/images/admin_linkedin.svg" alt="LinkedIn" />
								<span style="display:inline-block; height: 100%; vertical-align: middle; color: #000"><?php _e( 'LinkedIn Options', 'envira-social' ); ?></span>
							</h3>
						</th>
					</thead>
					<tbody>

						<tr>
							<th scope="row">
								<label>&nbsp;</label>
							</th>
							<td>
								<p><em>There are currently no options for LinkedIn sharing.</em></p>
							</td>
						</tr>

						<tr id="envira-config-social-networks-linkedin-what-to-share">
							<th scope="row">
								<label><?php _e( 'What To Share', 'envira-social' ); ?></label>
							</th>
							<td>
								<?php
								foreach ( $this->get_linkedin_show_options() as $option_value => $option_name ) {
									?>
									<label for="envira-config-social-<?php echo esc_html( $option_value ); ?>" class="label-for-checkbox">
										<input id="envira-config-social-<?php echo esc_html( $option_value ); ?>" type="checkbox" name="<?php echo esc_html( $key ); ?>[social_linkedin_show_option_<?php echo esc_html( $option_value ); ?>]" value="1" <?php checked( $instance->get_config( 'social_linkedin_show_option_' . $option_value, $instance->get_config_default( 'social_linkedin_show_option_' . $option_value ) ), 1 ); ?> />
										<?php echo esc_html( $option_name ); ?>
									</label>
									<?php
								}
								?>
								<p class="description">
									<?php _e( 'Select the information that will be sent to LinkedIn.', 'envira-social' ); ?>
								</p>
							</td>
						</tr>


					</tbody>
				</table>

				*/
				?>

				<table class="form-table">
					<thead>
						<td colspan="2" scope="row" style="padding:0;">
							<h3 class="social-heading" style="font-size: 1.1em; padding: 0; text-indent: 0px; background-color: #fff; color: #fff; width: 100%; line-height: 50px; margin-bottom: 0;">
								<div class="envira-social-settings-email-icon"></div>
								<span style="display:inline-block; line-height: 32px; height: 100%; vertical-align: top; color: #000"><?php esc_html_e( 'Email Options', 'envira-social' ); ?></span>
							</h3>
						</th>
					</thead>
					<tbody>

						<tr id="envira-config-social-networks-email-image-size">
							<th scope="row">
								<label for="envira-config-social-networks-email-image-size"><?php esc_html_e( 'Image Size To Share', 'envira-social' ); ?></label>
							</th>

							<td>
								<select id="envira-config-image-size" name="<?php echo esc_html( $key ); ?>[social_email_image_size]">
									<?php
									foreach ( (array) $this->get_email_image_sizes() as $option_value => $option_name ) {
										?>
										<option value="<?php echo esc_html( $option_value ); ?>"<?php selected( $option_value, $instance->get_config( 'social_email_image_size', $instance->get_config_default( 'social_email_image_size' ) ) ); ?>><?php echo esc_html( $option_name ); ?></option>
										<?php
									}
									?>
								</select>
								<p class="description">
									<?php esc_html_e( 'Select if you want to share the url of the full sized image or a smaller image via email.', 'envira-social' ); ?>
								</p>
							</td>
						</tr>

						<tr id="envira-social-email-subject-box">
							<th scope="row">
								<label for="envira-social-email-subject"><?php esc_html_e( 'Email Subject', 'envira-social' ); ?></label>
							</th>
							<td>
								<input id="envira-social-email-subject" type="text" name="<?php echo esc_html( $key ); ?>[social_email_subject]" value="<?php echo esc_attr( $instance->get_config( 'social_email_subject', $instance->get_config_default( 'social_email_subject' ) ) ); ?>" /><br />
								<span class="description"><?php esc_html_e( 'The email message subject.', 'envira-social' ); ?></span>
							</td>
						</tr>
						<tr id="envira-social-email-message-box">
							<th scope="row">
								<label for="envira-social-email-message"><?php esc_html_e( 'Email Message', 'envira-social' ); ?></label>
							</th>
							<td>
								<?php
								$message = $instance->get_config( 'social_email_message' );
								if ( empty( $message ) ) {
									$message = $instance->get_config_default( 'social_email_message' );
								}
								?>
								<textarea rows="15" id="<?php echo esc_html( $key ) . '[social_email_message]'; ?>" name="<?php echo esc_html( $key ) . '[social_email_message]'; ?>"><?php echo sprintf( wp_kses( $message, $this->wp_kses_allowed_html ) ); ?></textarea>
								<p class="description">
									<?php esc_attr_e( 'The default email message that is presented in the email dialog box. We do not recommend using HTML, as some email clients will not support it.', 'envira-social' ); ?><br />
									<?php esc_attr_e( 'Supported Tags:', 'envira-social' ); ?><br />
									<?php esc_attr_e( 'The Image Title', 'envira-social' ); ?> : {title}<br />
									<?php esc_attr_e( 'The Gallery URL', 'envira-social' ); ?> : {url}<br />
									<?php esc_attr_e( 'The Shared Photo URL', 'envira-social' ); ?> : {photo_url} 
								</p>
							</td>
						</tr>


					</tbody>
				</table>

		</div>

		<?php

	}

	/**
	 * Adds addon settings UI to the Mobile tab
	 *
	 * @since 1.0.9
	 *
	 * @param object $post The current post object.
	 */
	public function mobile_screen( $post ) {

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
		<tr id="envira-config-social-mobile-box">
			<th scope="row">
				<label for="envira-config-social-mobile"><?php esc_html_e( 'Display Social Sharing Buttons On Gallery?', 'envira-social' ); ?></label>
			</th>
			<td>
				<input id="envira-config-social-mobile" type="checkbox" name="<?php echo esc_html( $key ); ?>[mobile_social]" value="1" <?php checked( $instance->get_config( 'mobile_social', $instance->get_config_default( 'mobile_social' ) ), 1 ); ?> />
				<span class="description"><?php esc_html_e( 'If enabled, will display social sharing buttons based on the settings in the Social Addon: Gallery settings. If disabled, no social sharing buttons for galleries will be displayed on mobile.', 'envira-social' ); ?></span>
			</td>
		</tr>
		<tr id="envira-config-social-networks-mobile-box">
			<th scope="row">
				<label><?php esc_html_e( 'Social Networks', 'envira-social' ); ?></label>
			</th>
			<td>
				<?php
				foreach ( $this->get_networks_mobile() as $network => $name ) {
					?>
					<label for="envira-config-mobile-social-<?php echo esc_html( $network ); ?>" class="label-for-checkbox">
						<input id="envira-config-mobile-social-<?php echo esc_html( $network ); ?>" type="checkbox" name="<?php echo esc_html( $key ); ?>[mobile_social_<?php echo esc_html( $network ); ?>]" value="1" <?php checked( $instance->get_config( 'mobile_social_' . $network, $instance->get_config_default( 'mobile_social_' . $network ) ), 1 ); ?> />
						<?php echo esc_html( $name ); ?>
					</label>
					<?php
				}
				?>
			</td>
		</tr>

		<?php

	}

	/**
	 * Adds addon settings UI to the Mobile tab
	 *
	 * @since 1.0.9
	 *
	 * @param object $post The current post object.
	 */
	public function mobile_lightbox_screen( $post ) {

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

		<tr id="envira-config-social-lightbox-mobile-box">
			<th scope="row">
				<label for="envira-config-social-mobile"><?php esc_html_e( 'Display Social Sharing Buttons In Lightboxes?', 'envira-social' ); ?></label>
			</th>
			<td>
				<input id="envira-config-social-mobile" type="checkbox" name="<?php echo esc_html( $key ); ?>[mobile_social_lightbox]" value="1" <?php checked( $instance->get_config( 'mobile_social_lightbox', $instance->get_config_default( 'mobile_social_lightbox' ) ), 1 ); ?> />
				<span class="description"><?php esc_html_e( 'If enabled, will display social sharing buttons based on the settings in the Social Addon: Lightbox settings. If disabled, no social sharing buttons in lightboxes will be displayed on mobile.', 'envira-social' ); ?></span>
			</td>
		</tr>
		<tr id="envira-config-social-networks-lightbox-mobile-box">
			<th scope="row">
				<label><?php esc_html_e( 'Social Networks', 'envira-social' ); ?></label>
			</th>
			<td>
				<?php
				foreach ( $this->get_networks_mobile() as $network => $name ) {
					?>
					<label for="envira-config-mobile-social-lightbox-<?php echo esc_html( $network ); ?>" class="label-for-checkbox">
						<input id="envira-config-mobile-social-lightbox-<?php echo esc_html( $network ); ?>" type="checkbox" name="<?php echo esc_html( $key ); ?>[mobile_social_lightbox_<?php echo esc_html( $network ); ?>]" value="1" <?php checked( $instance->get_config( 'mobile_social_lightbox_' . $network, $instance->get_config_default( 'mobile_social_lightbox_' . $network ) ), 1 ); ?> />
						<?php echo esc_html( $name ); ?>
					</label>
					<?php
				}
				?>
			</td>
		</tr>

		<?php

	}

	/**
	 * Helper method for retrieving social networks.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of position data.
	 */
	public function get_networks() {

		$instance = Envira_Social_Common::get_instance();
		return $instance->get_networks();

	}

	/**
	 * Helper method for retrieving social networks for mobile
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of position data.
	 */
	public function get_networks_mobile() {

		$instance = Envira_Social_Common::get_instance();
		return $instance->get_networks_mobile();

	}

	/**
	 * Helper method for retrieving positions.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of position data.
	 */
	public function get_positions() {

		$instance = Envira_Social_Common::get_instance();
		return $instance->get_positions();

	}


	/**
	 * Helper method for retrieving Twitter sharing methods.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of position data.
	 */
	public function get_twitter_sharing_methods() {

		$instance = Envira_Social_Common::get_instance();
		return $instance->get_twitter_sharing_methods();

	}

	/**
	 * Helper method for retrieving Facebook sharing methods.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of position data.
	 */
	public function get_facebook_show_options() {

		$instance = Envira_Social_Common::get_instance();
		return $instance->get_facebook_show_options();

	}

	/**
	 * Helper method for retrieving LinkedIn sharing methods.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of position data.
	 */
	public function get_linkedin_show_options() {

		$instance = Envira_Social_Common::get_instance();
		return $instance->get_linkedin_show_options();

	}

	/**
	 * Helper method for retrieving Facebook sharing methods.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of position data.
	 */
	public function get_facebook_show_tag_options() {

		$instance = Envira_Social_Common::get_instance();
		return $instance->get_facebook_show_tag_options();

	}

	/**
	 * Helper method for retrieving Pinterest sharing methods.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of position data.
	 */
	public function get_pinterest_share_options() {

		$instance = Envira_Social_Common::get_instance();
		return $instance->get_pinterest_share_options();

	}

	/**
	 * Helper method for retrieving Pinterest title options
	 *
	 * @since 1.5.4
	 *
	 * @return array Array of position data.
	 */
	public function get_pinterest_title_options() {

		$instance = Envira_Social_Common::get_instance();
		return $instance->get_pinterest_title_options();

	}

	/**
	 * Helper method for retrieving Pinterest title options for albums
	 *
	 * @since 1.5.4
	 *
	 * @return array Array of position data.
	 */
	public function get_pinterest_title_album_options() {

		$instance = Envira_Social_Common::get_instance();
		return $instance->get_pinterest_title_album_options();

	}

	/**
	 * Helper method for retrieving Pinterest sharing methods.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of position data.
	 */
	public function get_email_image_sizes() {

		$instance = Envira_Social_Common::get_instance();
		return $instance->get_email_image_sizes();

	}


	/**
	 * Helper method for retrieving orientations.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of position data.
	 */
	public function get_orientations() {

		$instance = Envira_Social_Common::get_instance();
		return $instance->get_orientations();

	}

	/**
	 * Saves the addon's settings for Galleries.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings  Array of settings to be saved.
	 * @param int   $post_id   The current post ID.
	 * @return array $settings Amended array of settings to be saved.
	 */
	public function gallery_settings_save( $settings, $post_id ) {

		if (
			! isset( $_POST['_envira_gallery'], $_POST['envira_social_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['envira_social_nonce'] ), 'envira_social_save_settings' )
		) {
			return $settings;
		}

		// Gallery.
		$settings['config']['social'] = ( isset( $_POST['_envira_gallery']['social'] ) ? 1 : 0 );
		foreach ( $this->get_networks() as $network => $name ) {
			$settings['config'][ 'social_' . $network ] = ( isset( $_POST['_envira_gallery'][ 'social_' . $network ] ) ? 1 : 0 );
		}

		// The below four options were moved to _general in the settings form.
		$settings['config']['social_position']    = isset( $_POST['_envira_gallery']['social_position'] ) ? preg_replace( '#[^a-z0-9-_]#', '', sanitize_text_field( wp_unslash( $_POST['_envira_gallery']['social_position'] ) ) ) : false;
		$settings['config']['social_orientation'] = isset( $_POST['_envira_gallery']['social_orientation'] ) ? preg_replace( '#[^a-z0-9-_]#', '', sanitize_text_field( wp_unslash( $_POST['_envira_gallery']['social_orientation'] ) ) ) : false;

		// Social Twitter (New).
		$settings['config']['social_twitter_text']              = isset( $_POST['_general']['social_twitter_text'] ) ? htmlentities( sanitize_text_field( wp_unslash( $_POST['_general']['social_twitter_text'] ), ENT_QUOTES ) ) : false;
		$settings['config']['social_twitter_sharing_method']    = isset( $_POST['_general']['social_twitter_sharing_method'] ) ? sanitize_text_field( wp_unslash( $_POST['_general']['social_twitter_sharing_method'] ) ) : false;
		$settings['config']['social_twitter_summary_card_site'] = isset( $_POST['_general']['social_twitter_summary_card_site'] ) ? sanitize_text_field( wp_unslash( $_POST['_general']['social_twitter_summary_card_site'] ) ) : false;
		$settings['config']['social_twitter_summary_card_desc'] = isset( $_POST['_general']['social_twitter_summary_card_desc'] ) ? sanitize_text_field( wp_unslash( $_POST['_general']['social_twitter_summary_card_desc'] ) ) : false;

		// Social Facebook (New).
		foreach ( $this->get_facebook_show_options() as $value => $name ) {
			$settings['config'][ 'social_facebook_show_option_' . $value ] = ( isset( $_POST['_general'][ 'social_facebook_show_option_' . $value ] ) ? 1 : 0 );
		}
		$settings['config']['social_facebook_text'] = isset( $_POST['_general']['social_facebook_text'] ) ? htmlentities( sanitize_text_field( wp_unslash( $_POST['_general']['social_facebook_text'] ), ENT_QUOTES ) ) : false;

		$settings['config']['social_facebook_tag_options'] = isset( $_POST['_general']['social_facebook_tag_options'] ) ? sanitize_text_field( wp_unslash( $_POST['_general']['social_facebook_tag_options'] ) ) : false;
		$settings['config']['social_facebook_tags_manual'] = isset( $_POST['_general']['social_facebook_tags_manual'] ) ? sanitize_text_field( wp_unslash( $_POST['_general']['social_facebook_tags_manual'] ) ) : false;
		$settings['config']['social_facebook_quote']       = isset( $_POST['_general']['social_facebook_quote'] ) ? sanitize_text_field( wp_unslash( $_POST['_general']['social_facebook_quote'] ) ) : false;

		// Social Pinterest (new).
		$settings['config']['social_pinterest_type']  = isset( $_POST['_general']['social_pinterest_type'] ) ? preg_replace( '#[^a-z0-9-_]#', '', sanitize_text_field( wp_unslash( $_POST['_general']['social_pinterest_type'] ) ) ) : false;
		$settings['config']['social_pinterest_title'] = isset( $_POST['_general']['social_pinterest_title'] ) ? preg_replace( '#[^a-z0-9-_]#', '', sanitize_text_field( wp_unslash( $_POST['_general']['social_pinterest_title'] ) ) ) : false;
		if ( ! empty( $_POST['_general']['social_pinterest_rich'] ) ) {
			$settings['config']['social_pinterest_rich'] = isset( $_POST['_general']['social_pinterest_rich'] ) ? preg_replace( '#[^a-z0-9-_]#', '', sanitize_text_field( wp_unslash( $_POST['_general']['social_pinterest_rich'] ) ) ) : false;
		} else {
			$settings['config']['social_pinterest_rich'] = false;
		}

		// Social LinkedIn (New).
		foreach ( $this->get_linkedin_show_options() as $value => $name ) {
			$settings['config'][ 'social_linkedin_show_option_' . $value ] = ( isset( $_POST['_general'][ 'social_linkedin_show_option_' . $value ] ) ? 1 : 0 );
		}

		// Email.
		$settings['config']['social_email_image_size'] = ( isset( $_POST['_general']['social_email_image_size'] ) ? sanitize_text_field( wp_unslash( $_POST['_general']['social_email_image_size'] ) ) : '' );
		$settings['config']['social_email_subject']    = ( isset( $_POST['_general']['social_email_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['_general']['social_email_subject'] ) ) : '' );
		$settings['config']['social_email_message']    = ( isset( $_POST['_general']['social_email_message'] ) ? ( wp_unslash( $_POST['_general']['social_email_message'] ) ) : '' ); // @codingStandardsIgnoreLine - find better santitzation

		// Lightbox.
		$settings['config']['social_lightbox'] = ( isset( $_POST['_envira_gallery']['social_lightbox'] ) ? 1 : 0 );
		foreach ( $this->get_networks() as $network => $name ) {
			$settings['config'][ 'social_lightbox_' . $network ] = ( isset( $_POST['_envira_gallery'][ 'social_lightbox_' . $network ] ) ? 1 : 0 );
		}

		$settings['config']['social_lightbox_position']    = isset( $_POST['_envira_gallery']['social_lightbox_position'] ) ? preg_replace( '#[^a-z0-9-_]#', '', sanitize_text_field( wp_unslash( $_POST['_envira_gallery']['social_lightbox_position'] ) ) ) : false;
		$settings['config']['social_lightbox_outside']     = ( isset( $_POST['_envira_gallery']['social_lightbox_outside'] ) ? 1 : 0 );
		$settings['config']['social_lightbox_orientation'] = isset( $_POST['_envira_gallery']['social_lightbox_orientation'] ) ? preg_replace( '#[^a-z0-9-_]#', '', sanitize_text_field( wp_unslash( $_POST['_envira_gallery']['social_lightbox_orientation'] ) ) ) : false;

		// Mobile.
		$settings['config']['mobile_social'] = ( isset( $_POST['_envira_gallery']['mobile_social'] ) ? 1 : 0 );
		foreach ( $this->get_networks_mobile() as $network => $name ) {
			$settings['config'][ 'mobile_social_' . $network ] = ( isset( $_POST['_envira_gallery'][ 'mobile_social_' . $network ] ) ? 1 : 0 );
		}
		$settings['config']['mobile_social_lightbox'] = ( isset( $_POST['_envira_gallery']['mobile_social_lightbox'] ) ? 1 : 0 );
		foreach ( $this->get_networks_mobile() as $network => $name ) {
			$settings['config'][ 'mobile_social_lightbox_' . $network ] = ( isset( $_POST['_envira_gallery'][ 'mobile_social_lightbox_' . $network ] ) ? 1 : 0 );
		}

		return $settings;

	}

	/**
	 * Saves the addon's settings for Albums.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings  Array of settings to be saved.
	 * @param int   $post_id   The current post ID.
	 * @return array $settings Amended array of settings to be saved.
	 */
	public function album_settings_save( $settings, $post_id ) {

		if (
			! isset( $_POST['_eg_album_data'], $_POST['envira_social_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['envira_social_nonce'] ), 'envira_social_save_settings' )
		) {
			return $settings;
		}

		$settings['config']['social'] = ( isset( $_POST['_eg_album_data']['config']['social'] ) ? 1 : 0 );
		foreach ( $this->get_networks() as $network => $name ) {
			$settings['config'][ 'social_' . $network ] = ( isset( $_POST['_eg_album_data']['config'][ 'social_' . $network ] ) ? 1 : 0 );
		}

		$settings['config']['social_position']    = ( isset( $_POST['_eg_album_data']['config']['social_position'] ) ) ? preg_replace( '#[^a-z0-9-_]#', '', sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['social_position'] ) ) ) : false;
		$settings['config']['social_orientation'] = ( isset( $_POST['_eg_album_data']['config']['social_orientation'] ) ) ? preg_replace( '#[^a-z0-9-_]#', '', sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['social_orientation'] ) ) ) : false;

		// Lightbox.
		$settings['config']['social_lightbox'] = ( isset( $_POST['_eg_album_data']['config']['social_lightbox'] ) ? 1 : 0 );
		foreach ( $this->get_networks() as $network => $name ) {
			$settings['config'][ 'social_lightbox_' . $network ] = ( isset( $_POST['_eg_album_data']['config'][ 'social_lightbox_' . $network ] ) ? 1 : 0 );
		}
		$settings['config']['social_lightbox_position']    = ( isset( $_POST['_eg_album_data']['config']['social_lightbox_position'] ) ) ? preg_replace( '#[^a-z0-9-_]#', '', sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['social_lightbox_position'] ) ) ) : false;
		$settings['config']['social_lightbox_outside']     = ( isset( $_POST['_eg_album_data']['config']['social_lightbox_outside'] ) ? 1 : 0 );
		$settings['config']['social_lightbox_orientation'] = ( isset( $_POST['_eg_album_data']['config']['social_lightbox_orientation'] ) ) ? preg_replace( '#[^a-z0-9-_]#', '', sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['social_lightbox_orientation'] ) ) ) : false;

		if ( isset( $_POST['_general'] ) ) {

			// Twitter.
			$settings['config']['social_twitter_text']              = isset( $_POST['_general']['social_twitter_text'] ) ? htmlentities( sanitize_text_field( wp_unslash( $_POST['_general']['social_twitter_text'] ), ENT_QUOTES ) ) : false;
			$settings['config']['social_twitter_sharing_method']    = isset( $_POST['_general']['social_twitter_sharing_method'] ) ? sanitize_text_field( wp_unslash( $_POST['_general']['social_twitter_sharing_method'] ) ) : false;
			$settings['config']['social_twitter_summary_card_site'] = isset( $_POST['_general']['social_twitter_summary_card_site'] ) ? sanitize_text_field( wp_unslash( $_POST['_general']['social_twitter_summary_card_site'] ) ) : false;
			$settings['config']['social_twitter_summary_card_desc'] = isset( $_POST['_general']['social_twitter_summary_card_desc'] ) ? sanitize_text_field( wp_unslash( $_POST['_general']['social_twitter_summary_card_desc'] ) ) : false;

			// Facebook.
			foreach ( $this->get_facebook_show_options() as $value => $name ) {
				$settings['config'][ 'social_facebook_show_option_' . $value ] = ( isset( $_POST['_general'][ 'social_facebook_show_option_' . $value ] ) ? 1 : 0 );
			}
			$settings['config']['social_facebook_text']        = isset( $_POST['_general']['social_facebook_text'] ) ? htmlentities( sanitize_text_field( wp_unslash( $_POST['_general']['social_facebook_text'] ) ), ENT_QUOTES ) : false;
			$settings['config']['social_facebook_tag_options'] = isset( $_POST['_general']['social_facebook_tag_options'] ) ? preg_replace( '#[^a-z0-9-_]#', '', sanitize_text_field( wp_unslash( $_POST['_general']['social_facebook_tag_options'] ) ) ) : false;
			$settings['config']['social_facebook_tags_manual'] = isset( $_POST['_general']['social_facebook_tags_manual'] ) ? sanitize_text_field( wp_unslash( $_POST['_general']['social_facebook_tags_manual'] ) ) : false;
			$settings['config']['social_facebook_quote']       = isset( $_POST['_general']['social_facebook_quote'] ) ? sanitize_text_field( wp_unslash( $_POST['_general']['social_facebook_quote'] ) ) : false;

			// Pinterest.
			$settings['config']['social_pinterest_type']  = isset( $_POST['_general']['social_pinterest_type'] ) ? preg_replace( '#[^a-z0-9-_]#', '', sanitize_text_field( wp_unslash( $_POST['_general']['social_pinterest_type'] ) ) ) : false;
			$settings['config']['social_pinterest_title'] = isset( $_POST['_general']['social_pinterest_title'] ) ? preg_replace( '#[^a-z0-9-_]#', '', sanitize_text_field( wp_unslash( $_POST['_general']['social_pinterest_title'] ) ) ) : false;
			if ( ! empty( $_POST['_general']['social_pinterest_rich'] ) ) {
				$settings['config']['social_pinterest_rich'] = isset( $_POST['_general']['social_pinterest_rich'] ) ? preg_replace( '#[^a-z0-9-_]#', '', sanitize_text_field( wp_unslash( $_POST['_general']['social_pinterest_rich'] ) ) ) : false;
			} else {
				$settings['config']['social_pinterest_rich'] = false;
			}

			// LinkedIn.
			foreach ( $this->get_linkedin_show_options() as $value => $name ) {
				$settings['config'][ 'social_linkedin_show_option_' . $value ] = ( isset( $_POST['_general'][ 'social_linkedin_show_option_' . $value ] ) ? 1 : 0 );
			}

			// Email.
			$settings['config']['social_email_image_size'] = ( isset( $_POST['_general']['social_email_image_size'] ) ? sanitize_text_field( wp_unslash( $_POST['_general']['social_email_image_size'] ) ) : '' );
			$settings['config']['social_email_subject']    = ( isset( $_POST['_general']['social_email_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['_general']['social_email_subject'] ) ) : '' );
			$settings['config']['social_email_message']    = ( isset( $_POST['_general']['social_email_message'] ) ? ( wp_unslash( $_POST['_general']['social_email_message'] ) ) : '' ); // @codingStandardsIgnoreLine - find better santitzation

		}

		// Mobile.
		$settings['config']['mobile_social'] = ( isset( $_POST['_eg_album_data']['config']['mobile_social'] ) ? 1 : 0 );
		foreach ( $this->get_networks_mobile() as $network => $name ) {
			$settings['config'][ 'mobile_social_' . $network ] = ( isset( $_POST['_eg_album_data']['config'][ 'mobile_social_' . $network ] ) ? 1 : 0 );
		}
		$settings['config']['mobile_social_lightbox'] = ( isset( $_POST['_eg_album_data']['config']['mobile_social_lightbox'] ) ? 1 : 0 );
		foreach ( $this->get_networks_mobile() as $network => $name ) {
			$settings['config'][ 'mobile_social_lightbox_' . $network ] = ( isset( $_POST['_eg_album_data']['config'][ 'mobile_social_lightbox_' . $network ] ) ? 1 : 0 );
		}

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

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Social_Metaboxes ) ) {
			self::$instance = new Envira_Social_Metaboxes();
		}

		return self::$instance;

	}

}

// Load the metabox class.
$envira_social_metaboxes = Envira_Social_Metaboxes::get_instance();
