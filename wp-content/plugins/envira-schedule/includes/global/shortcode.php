<?php
/**
 * Shortcode class.
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
 * Shortcode class.
 *
 * @since 1.0.0
 *
 * @package Envira_Schedule
 * @author  Envira Gallery Team <support@enviragallery.com>
 */
class Envira_Schedule_Shortcode {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public static $instance = null;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		if ( ! class_exists( 'Envira_Gallery_Shortcode' ) ) {
			return;
		}

		// Frontend.
		add_filter( 'envira_gallery_pre_data', array( $this, 'pre_data' ), 10, 2 );
		add_filter( 'envira_images_pre_data', array( $this, 'pre_data' ), 10, 2 );
		add_filter( 'envira_featured_content_query_args', array( $this, 'featured_content_data' ), 10, 3 );
		add_filter( 'envira_gallery_should_cache', array( $this, 'disable_cache' ), 10, 2 );

	}

	/**
	 * Disables a gallery's cache if scheduling is activated.
	 *
	 * @since 1.0.0
	 *
	 * @param array $should_cache      Boolean value.
	 * @param mixed $data Array of gallery data.
	 * @return array $data     Amended array of gallery data.
	 */
	public function disable_cache( $should_cache, $data = false ) {
		if ( empty( $data ) ) {
			return $should_cache;
		}
		if ( ! empty( $data['config']['schedule'] ) && 1 === intval( $data['config']['schedule'] ) ) {
			return false; // disable cache.
		}
		return $should_cache;
	}

	/**
	 * Removes a gallery if it is scheduled and not during the proper time window.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data      Array of gallery data.
	 * @param mixed $gallery_id The current gallery ID.
	 * @return array $data     Amended array of gallery data.
	 */
	public function pre_data( $data, $gallery_id ) {

		if ( is_admin() ) {
			return $data;
		}

		/**
		* Current_time() will return an incorrect date/time if the server or another script sets a non-UTC timezone.
		* (e.g. if server timezone set to LA, current_time() will take another 8 hours off the already adjusted datetime).
		* Therefore we force UTC time, then get current_time().
		*/
		$existing_timezone = date_default_timezone_get();
		date_default_timezone_set( 'UTC' ); // @codingStandardsIgnoreLine - use WP internal timezone support

		// Time variables.
		$time_now   = current_time( 'timestamp', 1 ); // @codingStandardsIgnoreLine - Don't use current_time() for retrieving a Unix
		// need the '1' otherwise we are going by GMT timezone not local.
		$schedule   = envira_get_config( 'schedule', $data );
		$begin_date = envira_get_config( 'schedule_start', $data );
		$end_date   = envira_get_config( 'schedule_end', $data );

		// Check to see if a gallery is scheduled. If it is and it is not the correct time, return the data.
		if ( $schedule ) {
			if ( ( '' !== $begin_date && $begin_date > $time_now ) || ( '' !== $end_date && $end_date < $time_now ) ) {
				return false;
			}
		}

		// FC & Woo dont hold slides so just return.
		if ( ! isset( $data['gallery'] ) ) {
			return apply_filters( 'envira_gallery_schedule_data', $data, $gallery_id );
		}

		// Now check to see if a slide is scheduled. If it is and is not the right time, remove it from display.
		foreach ( (array) $data['gallery'] as $id => $gallery ) {
			// Check scheduling is enabled for this slide
			// If not, allow this slide to be included and move into the next slide.
			$meta_sched = isset( $gallery['schedule_meta'] ) ? $gallery['schedule_meta'] : 0;

			if ( ! $meta_sched ) {
				continue;
			}

			if ( 'active' !== $gallery['status'] ) {
				unset( $data['gallery'][ $id ] );
				continue;
			}

			$meta_ignore_date = isset( $gallery['schedule_meta_ignore_date'] ) ? $gallery['schedule_meta_ignore_date'] : 0;
			$meta_ignore_year = isset( $gallery['schedule_meta_ignore_year'] ) ? $gallery['schedule_meta_ignore_year'] : 0;
			$start_date       = isset( $gallery['schedule_meta_start'] ) ? ( $gallery['schedule_meta_start'] ) : '';
			$end_date         = isset( $gallery['schedule_meta_end'] ) ? ( $gallery['schedule_meta_end'] ) : '';

			// If start and/or end date aren't UNIX timestamps, they should be.
			if ( ! $this->is_valid_timestamp( $start_date ) ) {
				$start_date = strtotime( $start_date );
			}
			if ( ! $this->is_valid_timestamp( $end_date ) ) {
				$end_date = strtotime( $end_date );
			}

			// If we are ignoring the date component, just get the time.
			if ( $meta_ignore_date ) {

				// Get start and end time for slide.
				$start_time         = gmdate( 'H:i:s', $start_date );
				$end_time           = gmdate( 'H:i:s', $end_date );
				$time_now_time_only = gmdate( 'H:i:s', $time_now );

				// Check if start time is in the future, or end time is before current time.
				// If so, remove slide.
				if ( ( '' !== $start_time && $start_time > $time_now_time_only ) ) {
					unset( $data['gallery'][ $id ] );
				} elseif ( ( '' !== $end_time && $end_time < $time_now_time_only ) ) {
					unset( $data['gallery'][ $id ] );
				}
			} elseif ( $meta_ignore_year ) {

				// Modify the start date to be based on this year.
					$start_date_ymd   = gmdate( 'Y-m-d H:i:s', $start_date );
					$start_date_parts = explode( '-', $start_date_ymd );
					$start_date       = strtotime( gmdate( 'Y' ) . '-' . $start_date_parts[1] . '-' . $start_date_parts[2] );

					// If the start date is still after the current date/time, don't display the slide.
				if ( $start_date > $time_now ) {
					unset( $data['gallery'][ $id ] );
				}

					$end_date_ymd   = gmdate( 'Y-m-d H:i:s', $end_date );
					$end_date_parts = explode( '-', $end_date_ymd );
					$end_date       = strtotime( gmdate( 'Y' ) . '-' . $end_date_parts[1] . '-' . $end_date_parts[2] );

					// If the end date is still before the current date/time, don't display the slide.
				if ( $end_date < $time_now ) {
					unset( $data['gallery'][ $id ] );
				}
			} else {

				// Check if start date is in the future, or end date is before current date/time.
				// If so, remove slide.
				if ( ( '' !== $start_date && $start_date > $time_now ) ) {
					unset( $data['gallery'][ $id ] );
				} elseif ( ( '' !== $end_date && $end_date < $time_now ) ) {
					unset( $data['gallery'][ $id ] );
				}
			}
		}

		/**
		* Put timezone back in case other scripts rely on it
		*/
		date_default_timezone_set( $existing_timezone ); // @codingStandardsIgnoreLine - use WP internal timezone support

		return apply_filters( 'envira_gallery_schedule_data', $data, $gallery_id );

	}

	/**
	 * Adds query arguments to the main WP_Query for the Featured Content Addon,
	 * if any time based constraints have been specified on the Featured Content gallery
	 *
	 * @since 1.0.0
	 *
	 * @param array $query_args     WP_Query query arguments.
	 * @param int   $id             gallery ID.
	 * @param array $data           gallery Data.
	 * @return array                WP_Query query arguments.
	 */
	public function featured_content_data( $query_args, $id, $data ) {

		// Check if start/end date/times or hours have been specified.
		$limitation = envira_get_config( 'fc_date_define', $data );
		$start      = envira_get_config( 'fc_start_date', $data );
		$end        = envira_get_config( 'fc_end_date', $data );
		$age        = envira_get_config( 'fc_age', $data );
		if ( false === $limitation ) {
			return $query_args;
		}
		if ( 'datetime' === $limitation && empty( $start ) && empty( $end ) ) {
			return $query_args;
		}
		if ( 'hours' === $limitation && empty( $age ) ) {
			return $query_args;
		}

		// Check for dates.
		if ( 'datetime' === $limitation && ( ! empty( $start ) || ! empty( $end ) ) ) {
			// Restrict Posts by date.
			$dates = array();
			if ( ! empty( $start ) ) {
				$dates['after'] = gmdate( 'Y-m-d H:i:s', strtotime( $start ) );
			}
			if ( ! empty( $end ) ) {
				$dates['before'] = gmdate( 'Y-m-d H:i:s', strtotime( $end ) );
			}

			// Add to query args.
			$query_args['date_query'] = array(
				$dates,
			);
		}

		// Check for age.
		if ( 'hours' === $limitation && ! empty( $age ) ) {
			// Restrict Posts by age.
			$query_args['date_query'] = array(
				array(
					'after'     => $age . ' hours ago',
					'inclusive' => true,
				),
			);
		}

		return $query_args;

	}

	/**
	 * Tests to see if string is a timestamp
	 *
	 * @since 1.0.0
	 *
	 * @param string $timestamp Unix Timestamp.
	 * @return bool
	 */
	public function is_valid_timestamp( $timestamp ) {
		return ( (string) (int) $timestamp === $timestamp )
			&& ( $timestamp <= PHP_INT_MAX )
			&& ( $timestamp >= ~PHP_INT_MAX );
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The envira_gallery_Schedule_Shortcode object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Schedule_Shortcode ) ) {
			self::$instance = new Envira_Schedule_Shortcode();
		}

		return self::$instance;

	}

}

// Load the shortcode class.
$envira_gallery_schedule_shortcode = Envira_Schedule_Shortcode::get_instance();
