<?php
/**
 * Widgets class.
 *
 * @since 1.7.0
 *
 * @package Envira_Gallery
 * @author  Envira Team
 */

namespace Envira\Albums\Widgets;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Envira Albums Widget
 */
class Widget extends \ WP_Widget {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.7.0
	 */
	public function __construct() {

		// Widget Name.
		$widget_name = __( 'Envira Albums', 'envira-gallery' );
		$widget_name = apply_filters( 'envira_albums_widget_name', $widget_name );

		$widget_ops = apply_filters(
			'envira_albums_widget_ops',
			array(
				'classname'   => 'envira-albums',
				'description' => __( 'Place an Envira album into a widgetized area.', 'envira-gallery' ),
			)
		);

		$control_ops = apply_filters(
			'envira_albums_widget_control_ops',
			array(
				'id_base' => 'envira-albums',
				'height'  => 350,
				'width'   => 225,
			)
		);

		// Init.
		parent::__construct( 'envira-albums', $widget_name, $widget_ops, $control_ops );

		add_action( 'wp_ajax_envira_widget_get_albums', array( $this, 'widget_get_albums' ) );

	}

	/**
	 * Get albums for widget.
	 *
	 * @since 1.0.0
	 */
	public function widget_get_albums() {

		$albums       = envira_get_albums( false );
		$albums_array = array();

		if ( is_array( $albums ) ) {
			foreach ( $albums as $album ) {

				// Instead of pulling the title from config, attempt to pull it from the gallery post first.
				$albums_post = get_post( $album['id'] );

				if ( ! empty( $albums_post->post_title ) ) {
					$title = $albums_post->post_title;
				} elseif ( ! empty( $album['config']['title'] ) ) {
					$title = $album['config']['title'];
				} elseif ( ! empty( $album['config']['slug'] ) ) {
					$title = $album['config']['title'];
				} else {
					/* translators: %s: Album ID */
					$title = sprintf( __( 'Album ID #%s', 'envira-gallery' ), $album['id'] );
				}

				$albums_array[] = array(
					'gallery_title' => $title,
					'gallery_id'    => '' . $album['id'] . '',
				);

			}
		}

		$string = array( 'galleries' => $albums_array );

		echo wp_json_encode( $string );
		exit;
	}

	/**
	 * Outputs the widget within the widgetized area.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args     The default widget arguments.
	 * @param array $instance The input settings for the current widget instance.
	 */
	public function widget( $args, $instance ) {

		// Extract arguments into variables.
		extract( $args ); // @codingStandardsIgnoreLine

		$album_id = false;
		$title    = false;

		if ( isset( $instance['title'] ) ) {
			$title = apply_filters( 'widget_title', $instance['title'] );
		}
		if ( isset( $instance['envira_album_id'] ) ) {
			$album_id = $instance['envira_album_id'];
		}

		if ( ! $album_id ) {
			return; }

		do_action( 'envira_albums_widget_before_output', $args, $instance );

		echo $before_widget; // @codingStandardsIgnoreLine - unknown from WP

		do_action( 'envira_albums_widget_before_title', $args, $instance );

		// If a title exists, output it.
		if ( $title ) {
			echo $before_title . $title . $after_title; // @codingStandardsIgnoreLine - unknown from WP

		}

		do_action( 'envira_albums_widget_before_gallery', $args, $instance );

		// If an album has been selected, output it.
		if ( $album_id ) {
			envira_album( $album_id );
		}

		do_action( 'envira_albums_widget_after_gallery', $args, $instance );

		echo $after_widget; // @codingStandardsIgnoreLine - unknown from WP

		do_action( 'envira_albums_widget_after_output', $args, $instance );

	}

	/**
	 * Sanitizes and updates the widget.
	 *
	 * @since 1.0.0
	 *
	 * @param array $new_instance The new input settings for the current widget instance.
	 * @param array $old_instance The old input settings for the current widget instance.
	 */
	public function update( $new_instance, $old_instance ) {

		// Set $instance to the old instance in case no new settings have been updated for a particular field.
		$instance = $old_instance;

		// Sanitize user inputs.
		$instance['title']           = trim( $new_instance['title'] );
		$instance['envira_album_id'] = absint( $new_instance['envira_album_id'] );

		return apply_filters( 'envira_album_widget_update_instance', $instance, $new_instance );

	}

	/**
	 * Outputs the widget form where the user can specify settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $instance The input settings for the current widget instance.
	 */
	public function form( $instance ) {

		// Get all available galleries and widget properties.
		$albums   = _envira_get_albums( false );
		$title    = isset( $instance['title'] ) ? $instance['title'] : '';
		$album_id = isset( $instance['envira_album_id'] ) ? $instance['envira_album_id'] : false;

		do_action( 'envira_albums_widget_before_form', $instance );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'envira-albums' ); ?></label>
			<input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" style="width: 100%;" />
		</p>
		<?php do_action( 'envira_albums_widget_middle_form', $instance ); ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'envira_album_id' ) ); ?>"><?php esc_html_e( 'Album', 'envira-albums' ); ?></label>

			<select class="form-control" id="<?php echo esc_attr( $this->get_field_id( 'envira_album_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'envira_album_id' ) ); ?>" style="width: 100%;">
			<!--<select class="form-control" name="choices-single-remote-fetch" id="choices-single-remote-fetch">-->
				<?php

				if ( is_array( $albums ) ) {
					foreach ( $albums as $album ) {
						if ( isset( $album['id'] ) ) {
							$title = get_the_title( $album['id'] );
							if ( $album_id && $album['id'] === $album_id ) {
								echo '<option selected="selected" value="' . absint( $album['id'] ) . '">' . esc_html( $title ) . '</option>';
							} else {
								echo '<option value="' . absint( $album['id'] ) . '">' . esc_html( $title ) . '</option>';
							}
						}
					}
				}

				?>
			</select>
		</p>
		<?php
		do_action( 'envira_albums_widget_after_form', $instance );

	}

}
