<?php
/**
 * Metabox class.
 *
 * @since 1.0.0
 *
 * @package Envira_Schedule
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Metabox class.
 *
 * @since 1.0.0
 *
 * @package Envira_Schedule
 * @author  Envira Gallery Team <support@enviragallery.com>
 */
class Envira_Schedule_Metaboxes {

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
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Load the base class object.
		$this->base = Envira_Schedule::get_instance();

		// CSS + JS.
		add_action( 'envira_gallery_metabox_styles', array( $this, 'styles' ) );
		add_action( 'envira_gallery_metabox_scripts', array( $this, 'scripts' ) );

		// Addon Settings.
		add_filter( 'envira_gallery_tab_nav', array( $this, 'tab_nav' ) );
		add_action( 'envira_gallery_tab_schedule', array( $this, 'settings' ) );
		add_filter( 'envira_gallery_save_settings', array( $this, 'settings_save' ), 10, 2 );
		add_filter( 'envira_gallery_ajax_save_meta', array( $this, 'meta_save' ), 10, 4 );
		add_filter( 'envira_gallery_ajax_save_bulk_meta', array( $this, 'save_bulk' ), 10, 4 );

		/*
		Featured Content Settings.

		Limiting display of featured posts by date or hours is currently disabled, pending work for a future update.

		add_action( 'envira_gallery_fc_box', array( $this, 'featured_content_settings' ) );

		*/
		add_filter( 'envira_featured_content_save', array( $this, 'featured_content_settings_save' ), 10, 2 );

		// Individual Image Settings.
		add_action( 'print_media_templates', array( $this, 'meta_settings' ), 10, 3 );

		add_filter( 'envira_gallery_get_gallery_item', array( $this, 'alter_date_start_format' ), 10, 3 );
		add_filter( 'envira_gallery_get_gallery_item', array( $this, 'alter_date_end_format' ), 10, 3 );
	}


	/**
	 * Altered legacy dates into unix timestamp for new datetimepicker
	 *
	 * @since 1.0.0
	 * @param array $item Item data.
	 * @param int   $id ID.
	 * @param int   $post_id Post ID.
	 */
	public function alter_date_start_format( $item, $id, $post_id ) {

		if ( empty( $item['schedule_meta_start'] ) ) {
			return $item;
		}

		if ( $this->is_valid_timestamp( $item['schedule_meta_start'] ) ) {
			return $item;
		}

		$item['schedule_meta_start'] = strtotime( $item['schedule_meta_start'] );

		return $item;

	}

	/**
	 * Altered legacy dates into unix timestamp for new datetimepicker
	 *
	 * @since 1.0.0
	 * @param array $item Item data.
	 * @param int   $id ID.
	 * @param int   $post_id Post ID.
	 */
	public function alter_date_end_format( $item, $id, $post_id ) {

		if ( empty( $item['schedule_meta_end'] ) ) {
			return $item;
		}

		if ( $this->is_valid_timestamp( $item['schedule_meta_end'] ) ) {
			return $item;
		}

		$item['schedule_meta_end'] = strtotime( $item['schedule_meta_end'] );

		return $item;

	}


	/**
	 * Tests to see if string is a timestamp
	 *
	 * @since 1.0.0
	 * @param string $timestamp Timestamp.
	 */
	public function is_valid_timestamp( $timestamp ) {
		return ( (string) (int) $timestamp === $timestamp )
			&& ( $timestamp <= PHP_INT_MAX )
			&& ( $timestamp >= ~PHP_INT_MAX );
	}

	/**
	 * Loads styles for our metaboxes.
	 *
	 * @since 1.0.0
	 */
	public function styles() {

		wp_enqueue_style( $this->base->plugin_slug . '-style', plugins_url( 'assets/css/schedule-admin.css', $this->base->file ), array(), $this->base->version );

	}

	/**
	 * Loads scripts for our metaboxes.
	 *
	 * @since 1.0.0
	 */
	public function scripts() {

		// Enqueue jQuery UI core and Datepicker.
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-datepicker' );

		wp_enqueue_script( $this->base->plugin_slug . '-media', plugins_url( 'assets/js/media-edit.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );
		wp_enqueue_script( $this->base->plugin_slug . '-moment', plugins_url( 'assets/js/moment-with-locales.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );
		wp_enqueue_script( $this->base->plugin_slug . '-datetimepicker', plugins_url( 'assets/js/jquery.datetimepicker.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );
		wp_enqueue_script( $this->base->plugin_slug . '-script', plugins_url( 'assets/js/jquery.schedule.js', $this->base->file ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker' ), $this->base->version, true );

		wp_register_script( $this->base->plugin_slug . '-conditional-fields-script', plugins_url( 'assets/js/min/conditional-fields-min.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );
		wp_enqueue_script( $this->base->plugin_slug . '-conditional-fields-script' );

		// Localize the script with date and time formats.
		wp_localize_script(
			$this->base->plugin_slug . '-script',
			'envira_gallery_schedule',
			array(
				'date_format'            => 'F j, Y',
				'time_format'            => 'g:i a',
				'envira_datetime_format' => 'MMMM DD, YYYY h:mm a',
				'envira_format_time'     => 'h:mm a',
				'envira_format_date'     => 'MMMM DD, YYYY',
			)
		);

	}

	/**
	 * Filters in a new tab for the addon.
	 *
	 * @since 1.0.0
	 *
	 * @param array $tabs  Array of default tab values.
	 * @return array $tabs Amended array of default tab values.
	 */
	public function tab_nav( $tabs ) {

		$tabs['schedule'] = esc_attr__( 'Schedule', 'envira-schedule' );
		return $tabs;

	}

	/**
	 * Callback for displaying the UI for setting schedule options.
	 *
	 * @since 1.0.0
	 *
	 * @param object $post The current post object.
	 */
	public function settings( $post ) {

		$instance = Envira_Gallery_Metaboxes::get_instance();

		wp_nonce_field( 'envira_schedule_save_settings', 'envira_schedule_nonce' );

		?>

			<p class="envira-intro">
				<?php esc_html_e( 'Schedule Settings', 'envira-social' ); ?>

					<small>

						<?php esc_html_e( 'The settings below adjust the Schedule settings for the gallery.', 'envira-schedule' ); ?>

						<?php if ( apply_filters( 'envira_whitelabel', false ) ) : ?>

							<?php do_action( 'envira_social_whitelabel_tab_helptext' ); ?>

						<?php else : ?>


									<br />
							<?php esc_html_e( 'Need some help?', 'envira-social' ); ?>
				<a href="http://enviragallery.com/docs/schedule-addon" class="envira-doc" target="_blank">
							<?php esc_html_e( 'Read the Documentation', 'envira-social' ); ?>
				</a>
				or
				<a href="https://www.youtube.com/embed/SALWyV-AQYI/?rel=0" class="envira-video" target="_blank">
							<?php esc_html_e( 'Watch a Video', 'envira-social' ); ?>
				</a>
						<?php endif; ?>
					</small>

			</p>

		<div id="envira-schedule">
			<table class="form-table">
				<tbody>
					<tr id="envira-config-schedule-box">
						<th scope="row">
							<label for="envira-config-schedule"><?php esc_html_e( 'Enable Scheduling?', 'envira-schedule' ); ?></label>
						</th>
						<td>
							<input id="envira-config-schedule" type="checkbox" name="_envira[schedule]" value="<?php echo esc_html( $instance->get_config( 'schedule', $instance->get_config_default( 'schedule' ) ) ); ?>" <?php checked( $instance->get_config( 'schedule', $instance->get_config_default( 'schedule' ) ), 1 ); ?> data-conditional="envira-config-schedule-start-box,envira-config-schedule-end-box" />
							<span class="description"><?php esc_html_e( 'Enables or disables scheduling for the gallery.', 'envira-schedule' ); ?></span>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label><?php esc_html_e( 'Current Server Time', 'envira-schedule' ); ?></label>
						</th>
						<td>
							<strong>
								<?php
								echo esc_html( date_i18n( 'F j, Y g:i a' ) );
								?>
							</strong>
							<p class="description">
								<strong><?php esc_html_e( 'NOTE: ', 'envira-schedule' ); ?></strong>
								<?php esc_html_e( 'The Start Date / Time and End Date / Time are compared to the above Current Server Time.  If your Current Server Time is incorrect, please ensure WordPress is set up with the correct timezone (Settings - General), and that your PHP installation and server both report an accurate time (i.e. not a time that is several minutes off).', 'envira-schedule' ); ?>
							</p>
						</td>
					</tr>
					<tr id="envira-config-schedule-start-box">
						<th scope="row">
							<label for="envira-config-schedule-start"><?php esc_html_e( 'Start Date', 'envira-schedule' ); ?></label>
						</th>
						<td>
							<input id="envira-config-schedule-start" class="envira-date" type="text" name="_envira[schedule_start]" value="<?php echo esc_html( $instance->get_config( 'schedule_start', $instance->get_config_default( 'schedule_start' ) ) ); ?>" />
							<p class="description"><?php esc_html_e( 'Sets the start date for the gallery.', 'envira-schedule' ); ?></p>
						</td>
					</tr>
					<tr id="envira-config-schedule-end-box">
						<th scope="row">
							<label for="envira-config-schedule-end"><?php esc_html_e( 'End Date', 'envira-schedule' ); ?></label>
						</th>
						<td>
							<input id="envira-config-schedule-end" class="envira-date" type="text" name="_envira[schedule_end]" value="<?php echo esc_html( $instance->get_config( 'schedule_end', $instance->get_config_default( 'schedule_end' ) ) ); ?>" />
							<p class="description"><?php esc_html_e( 'Sets the end date for the gallery.', 'envira-schedule' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php

	}

	/**
	 * Saves the addon setting.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings  Array of settings to be saved.
	 * @param int   $post_id     The current post ID.
	 * @return array $settings Amended array of settings to be saved.
	 */
	public function settings_save( $settings, $post_id ) {

		if (
			! isset( $_POST['_envira_gallery'], $_POST['envira_schedule_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['envira_schedule_nonce'] ), 'envira_schedule_save_settings' )
		) {
			return $settings;
		}

		$settings['config']['schedule']       = isset( $_POST['_envira']['schedule'] ) ? 1 : 0;
		$settings['config']['schedule_start'] = isset( $_POST['_envira']['schedule_start'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['_envira']['schedule_start'] ) ) ) : false;
		$settings['config']['schedule_end']   = isset( $_POST['_envira']['schedule_end'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['_envira']['schedule_end'] ) ) ) : false;

		return $settings;

	}

	/**
	 * Outputs Schedule options for the Featured Content Addon
	 *
	 * @since 2.0.7
	 *
	 * @param obj $post gallery Post Object.
	 */
	public function featured_content_settings( $post ) {

		$instance = envira_gallery_Metaboxes::get_instance();
		?>

		<tr id="envira-config-fc-date-define">
			<th scope="row">
				<label for="envira-config-fc-date-define"><?php esc_html_e( 'Limit Display?', 'envira-fc' ); ?></label>
			</th>
			<td>
				<select id="envira-config-fc-date-define-dropdown" name="_envira_gallery[fc_date_define]">
					<option value="">No Limitation</option>
					<?php foreach ( (array) Envira_Featured_Content_Common::get_instance()->envira_get_fc_date_define_options() as $option_value => $option_name ) : ?>
						<option value="<?php echo esc_html( $option_value ); ?>" <?php selected( $option_value, $instance->get_config( 'fc_date_define', $instance->get_config_default( 'fc_date_define' ) ) ); ?>><?php echo esc_html( $option_name ); ?></option>
					<?php endforeach; ?>
				</select><br>
				<p class="description"><?php esc_html_e( 'Determine how posts should be selected based on their age or start/end.', 'envira-fc' ); ?></p>
			</td>
		</tr>

		<tr id="envira-config-fc-start-date-box">
			<th scope="row">
				<label for="envira-config-fc-start-date"><?php esc_html_e( 'Post Start Date', 'envira-fc' ); ?></label>
			</th>
			<td>
				<input id="envira-config-fc-start-date" class="envira-date" type="text" name="_envira[fc_start_date]" value="<?php echo esc_html( $instance->get_config( 'fc_start_date', $instance->get_config_default( 'fc_start_date' ) ) ); ?>" />
				<p class="description"><?php esc_html_e( 'Optionally define a start date and time. Posts must be published on or after this date and time for inclusion in the gallery.', 'envira-schedule' ); ?></p>
			</td>
		</tr>
		<tr id="envira-config-fc-end-date-box">
			<th scope="row">
				<label for="envira-config-fc-end-date"><?php esc_html_e( 'Post End Date', 'envira-fc' ); ?></label>
			</th>
			<td>
				<input id="envira-config-fc-end-date" class="envira-date" type="text" name="_envira[fc_end_date]" value="<?php echo esc_html( $instance->get_config( 'fc_end_date', $instance->get_config_default( 'fc_end_date' ) ) ); ?>" />
				<p class="description"><?php esc_html_e( 'Optionally define an end date and time. Posts must be published on or before this date and time for inclusion in the gallery.', 'envira-schedule' ); ?></p>
			</td>
		</tr>
		<tr id="envira-config-fc-age-box">
			<th scope="row">
				<label for="envira-config-fc-age"><?php esc_html_e( 'Post Age (Hours)', 'envira-fc' ); ?></label>
			</th>
			<td>
				<input id="envira-config-fc-age" type="number" min="0" max="999" step="1" name="_envira[fc_age]" value="<?php echo esc_html( $instance->get_config( 'fc_age', $instance->get_config_default( 'fc_age' ) ) ); ?>" />
				<p class="description"><?php esc_html_e( 'Optionally define the maximum age of posts, in hours. Posts must not be older than the given number of hours to be included in the gallery.', 'envira-schedule' ); ?></p>
			</td>
		</tr>
		<?php

	}

	/**
	 * Save Featured Content Addon settings
	 *
	 * @since 2.0.7
	 *
	 * @param array $settings   Settings.
	 * @param int   $post_id      gallery ID.
	 */
	public function featured_content_settings_save( $settings, $post_id ) {

		if (
			! isset( $_POST['_envira_gallery'], $_POST['envira_schedule_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['envira_schedule_nonce'] ), 'envira_schedule_save_settings' )
		) {
			return $settings;
		}

		$settings['config']['fc_date_define'] = isset( $_POST['_envira_gallery']['fc_date_define'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['_envira_gallery']['fc_date_define'] ) ) ) : false;
		$settings['config']['fc_start_date']  = isset( $_POST['_envira']['fc_start_date'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['_envira']['fc_start_date'] ) ) ) : false;
		$settings['config']['fc_end_date']    = isset( $_POST['_envira']['fc_end_date'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['_envira']['fc_end_date'] ) ) ) : false;
		$settings['config']['fc_age']         = isset( $_POST['_envira']['fc_age'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['_envira']['fc_age'] ) ) ) : false;

		return $settings;

	}

	/**
	 * Outputs the schedule meta fields.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $attach_id The current attachment ID.
	 * @param array $data    Array of attachment data.
	 * @param int   $post_id   The current post ID.
	 */
	public function meta( $attach_id, $data, $post_id ) {

		$instance = envira_gallery_Metaboxes::get_instance();

		$image_start = get_post_meta( $post_id, '_envira_gallery_image_begin', true );
		$image_end   = get_post_meta( $post_id, '_envira_gallery_image_end', true );

		$enable = false;
		if ( '' !== $image_start || '' !== $image_end ) {
			$enable = true;
		}

		?>
		<label class="setting">
			<span class="name"><?php esc_html_e( 'Schedule Galleryyyyy?', 'envira-schedule' ); ?></span>
			<input id="envira-schedule-enable-<?php echo esc_html( $attach_id ); ?>" class="envira-schedule-enable" type="checkbox" name="_envira[schedule_meta]" data-envira-meta="schedule_meta" value="<?php echo esc_html( $enable ? $enable : $instance->get_meta( 'schedule_meta', $attach_id, $instance->get_meta_default( 'schedule_meta', $attach_id ) ) ); ?>"<?php checked( ( $enable ? $enable : $instance->get_meta( 'schedule_meta', $attach_id, $instance->get_meta_default( 'schedule_meta', $attach_id ) ) ), 1 ); ?> />
		</label>


		<label class="setting">
			<span class="name"><?php esc_html_e( 'Start Date', 'envira-schedule' ); ?></span>
			<input id="envira-schedule-start-<?php echo esc_html( $attach_id ); ?>" class="envira-schedule-start envira-date envira-time" type="text" name="_envira[schedule_meta_start]" data-envira-meta="schedule_meta_start" value="<?php echo esc_html( $image_start ? $image_start : $instance->get_meta( 'schedule_meta_start', $attach_id, $instance->get_meta_default( 'schedule_meta_start', $attach_id ) ) ); ?>"<?php checked( ( $image_start ? $image_start : $instance->get_meta( 'schedule_meta_start', $attach_id, $instance->get_meta_default( 'schedule_meta_start', $attach_id ) ) ), 1 ); ?> />
		</label>

		<label class="setting">
			<span class="name"><?php esc_html_e( 'End Date', 'envira-schedule' ); ?></span>
			<input id="envira-schedule-end-<?php echo esc_html( $attach_id ); ?>" class="envira-schedule-end envira-date envira-time" type="text" name="_envira[schedule_meta_end]" data-envira-meta="schedule_meta_end" value="<?php echo esc_html( $image_end ? esc_html( $image_end ) : $instance->get_meta( 'schedule_meta_end', $attach_id, $instance->get_meta_default( 'schedule_meta_end', $attach_id ) ) ); ?>"<?php checked( ( $image_end ? esc_html( $image_end ) : $instance->get_meta( 'schedule_meta_end', $attach_id, $instance->get_meta_default( 'schedule_meta_end', $attach_id ) ) ), 1 ); ?> />
		</label>

		<label class="setting">
			<span class="name"><?php esc_html_e( 'Ignore Date?', 'envira-schedule' ); ?></span>
			<input id="envira-schedule-ignore-date-<?php echo esc_html( $attach_id ); ?>" class="envira-schedule-ignore-date" type="checkbox" name="_envira[schedule_meta_ignore_date]" data-envira-meta="schedule_meta_ignore_date" value="1" <?php checked( $instance->get_meta( 'schedule_meta_ignore_date', $attach_id, $instance->get_meta_default( 'schedule_meta_ignore_date', $attach_id ) ), 1 ); ?> />
		</label>

		<label class="setting">
			<span class="name"><?php esc_html_e( 'Ignore Year?', 'envira-schedule' ); ?></span>
			<input id="envira-schedule-ignore-year-<?php echo esc_html( $attach_id ); ?>" class="envira-schedule-ignore-year" type="checkbox" name="_envira[schedule_meta_ignore_year]" data-envira-meta="schedule_meta_ignore_year" value="1" <?php checked( $instance->get_meta( 'schedule_meta_ignore_year', $attach_id, $instance->get_meta_default( 'schedule_meta_ignore_year', $attach_id ) ), 1 ); ?> />
		</label>
		<?php

	}
	/**
	 * Outputs fields in the modal window when editing an existing image,
	 * allowing the user to choose whether to display the video
	 * in the gallery view.
	 *
	 * @since 1.1.6
	 *
	 * @param int $post_id The current post ID.
	 */
	public function meta_settings( $post_id ) {

		// Soliloquy Meta Editor
		// Use: wp.media.template( 'envira-meta-editor-schedule' ).
		?>
		<script type="text/html" id="tmpl-envira-meta-editor-schedule">

			<div class="envira-meta">

				<label class="setting">
					<span class="name"><?php esc_html_e( 'Schedule Photo?', 'envira-schedule' ); ?></span>
					<input class="envira-schedule-enable" type="checkbox" name="schedule_meta" data-envira-meta="schedule_meta" value="1" <# if ( data.schedule_meta == '1' ) { #> checked <# } #>/>
					<span class="check-label"><?php esc_html_e( 'Enables or disables scheduling for this photo.', 'envira-schedule' ); ?></span>
				</label>

				<label class="setting">
					<span class="name"><?php esc_html_e( 'Start Date', 'envira-schedule' ); ?></span>
					<input class="envira-schedule-start envira-date envira-time" id="schedule_meta_start" type="text" name="schedule_meta_start" data-envira-meta="schedule_meta_start" value="{{ data.schedule_meta_start }}" />
					<span class="check-label"><?php esc_html_e( 'Date this Gallery should begin displaying within the gallery.', 'envira-schedule' ); ?></span>
				</label>

				<label class="setting">
					<span class="name"><?php esc_html_e( 'End Date', 'envira-schedule' ); ?></span>
					<input class="envira-schedule-end envira-date envira-time" id="schedule_meta_end" type="text" name="schedule_meta_end" data-envira-meta="schedule_meta_end" value="{{ data.schedule_meta_end }}" />
					<span class="check-label"><?php esc_html_e( 'Date this Gallery should stop displaying within the gallery.', 'envira-schedule' ); ?></span>
				</label>

				<label class="setting">
					<span class="name"><?php esc_html_e( 'Ignore Date?', 'envira-schedule' ); ?></span>
					<input class="envira-schedule-ignore-date" type="checkbox" name="schedule_meta_ignore_date" data-envira-meta="schedule_meta_ignore_date" value="1"<# if ( data.schedule_meta_ignore_date == '1' ) { #> checked <# } #> />
					<span class="check-label"><?php esc_html_e( 'If enabled, schedule Start and End Dates will ignore the date and default to the time specified. Enable this option to display Gallery at a recurring time each day.', 'envira-schedule' ); ?></span>

				</label>

				<label class="setting">
					<span class="name"><?php esc_html_e( 'Ignore Year?', 'envira-schedule' ); ?></span>
					<input class="envira-schedule-ignore-year" type="checkbox" name="schedule_meta_ignore_year" data-envira-meta="schedule_meta_ignore_year" value="1"<# if ( data.schedule_meta_ignore_year == '1' ) { #> checked <# } #> />
					<span class="check-label"><?php esc_html_e( 'If enabled, schedule Start and End Dates will ignore the year and default to the date and time specified. Enable this option to display Gallery at a recurring date / time each year.', 'envira-schedule' ); ?></span>

				</label>

			</div>

		</script>
		<?php

	}
	/**
	 * Saves the addon meta settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings  Array of settings to be saved.
	 * @param array $meta      Array of Gallery meta to use for saving.
	 * @param int   $attach_id   The current attachment ID.
	 * @param int   $post_id     The current post ID.
	 * @return array $settings Amended array of settings to be saved.
	 */
	public function meta_save( $settings, $meta, $attach_id, $post_id ) {

		$settings['gallery'][ $attach_id ]['schedule_meta']             = isset( $meta['schedule_meta'] ) && $meta['schedule_meta'] ? 1 : 0;
		$settings['gallery'][ $attach_id ]['schedule_meta_start']       = isset( $meta['schedule_meta_start'] ) && $meta['schedule_meta_start'] ? esc_attr( $meta['schedule_meta_start'] ) : '';
		$settings['gallery'][ $attach_id ]['schedule_meta_end']         = isset( $meta['schedule_meta_end'] ) && $meta['schedule_meta_end'] ? esc_attr( $meta['schedule_meta_end'] ) : '';
		$settings['gallery'][ $attach_id ]['schedule_meta_ignore_date'] = isset( $meta['schedule_meta_ignore_date'] ) && $meta['schedule_meta_ignore_date'] ? 1 : 0;
		$settings['gallery'][ $attach_id ]['schedule_meta_ignore_year'] = isset( $meta['schedule_meta_ignore_year'] ) && $meta['schedule_meta_ignore_year'] ? 1 : 0;

		return $settings;

	}

	/**
	 * Saves Schedule-specific options when editing bulk
	 *
	 * @since 1.0.0
	 *
	 * @param   array $gallery_data   Gallery Data.
	 * @param   array $meta           Meta.
	 * @param   int   $attach_id      Attachment ID.
	 * @param   int   $post_id        Post (Gallery) ID.
	 * @return  array                   Gallery Data
	 */
	public function save_bulk( $gallery_data, $meta, $attach_id, $post_id ) {

		$gallery_data['gallery'][ $attach_id ]['schedule_meta'] = ( isset( $meta['schedule_meta'] ) ? sanitize_text_field( $meta['schedule_meta'] ) : $gallery_data['gallery'][ $attach_id ]['schedule_meta'] );

		$gallery_data['gallery'][ $attach_id ]['schedule_meta_start'] = ( isset( $meta['schedule_meta_start'] ) ? sanitize_text_field( $meta['schedule_meta_start'] ) : $gallery_data['gallery'][ $attach_id ]['schedule_meta_start'] );

		$gallery_data['gallery'][ $attach_id ]['schedule_meta_end'] = ( isset( $meta['schedule_meta_end'] ) ? sanitize_text_field( $meta['schedule_meta_end'] ) : $gallery_data['gallery'][ $attach_id ]['schedule_meta_end'] );

		$gallery_data['gallery'][ $attach_id ]['schedule_meta_ignore_date'] = ( isset( $meta['schedule_meta_ignore_date'] ) ? absint( $meta['schedule_meta_ignore_date'] ) : $gallery_data['gallery'][ $attach_id ]['schedule_meta_ignore_date'] );

		$gallery_data['gallery'][ $attach_id ]['schedule_meta_ignore_year'] = ( isset( $meta['schedule_meta_ignore_year'] ) ? sanitize_text_field( $meta['schedule_meta_ignore_year'] ) : $gallery_data['gallery'][ $attach_id ]['schedule_meta_ignore_year'] );

		return $gallery_data;

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Schedule_Metaboxes object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Schedule_Metaboxes ) ) {
			self::$instance = new Envira_Schedule_Metaboxes();
		}

		return self::$instance;

	}

}

// Load the metabox class.
$envira_schedule_metaboxes = Envira_Schedule_Metaboxes::get_instance();
