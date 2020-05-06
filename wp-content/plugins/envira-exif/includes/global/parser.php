<?php
/**
 * Envira_Exif_Parser class.
 *
 * @since 1.1.7
 *
 * @package Envira_Exif
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extracts EXIF data from an image file
 *
 * @since 1.1.7
 *
 * @package Envira_Exif
 * @author  Envira Team
 */
class Envira_Exif_Parser {

	/**
	 * Holds the class object.
	 *
	 * @since 1.1.7
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.1.7
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Defines keys that may contain EXIF data
	 *
	 * @since 1.1.7
	 *
	 * @var array
	 */
	public $exif_keys = array(
		'Model',                    // Camera Model.
		'YResolution',              // DPI.
		'XResolution',              // DPI.
		'ResolutionUnit',           // Resolution Unit.
		'Make',                     // Camera Make.
		'ApertureValue',            // Aperture.
		'ShutterSpeedValue',        // Shutter Speed.
		'ColorSpace',               // Color Space.
		'DateTimeOriginal',         // Date/time.
		'ExposureBiasValue',        // Exposure Bias.
		'ExifImageLength',          // Image Height.
		'ExifImageWidth',           // Image Width.
		'FocalLength',              // Focal Length.
		'Flash',                    // Flash used?
		'ExposureTime',             // Exposure Time.
		'FNumber',                  // ?
		'CaptureTime',             // Capture time.

		// GPS.
		'GPSImgDirection',          // Direction image was taken (degrees, 0 - 359.99).
		'GPSImgDirectionRef',       // Whether image direction is true (T) or magnetic (M).
		'GPSLatitudeRef',           // Direction of Latitude (N,S).
		'GPSLatitude',              // Latitude co-ords.
		'GPSLongitudeRef',          // Direction of Longitude (E,W).
		'GPSLongitudeRef',          // Longitude co-ords.
		'GPSAltitudeRef',           // Direction of Altitude ().
		'GPSAltitude',              // Altitude co-ords.
		'GPSTimestamp',             // GPS Time.
		'GPSDateStamp',             // GPS Date.

		// WordPress calculated values from wp_read_image_metadata().
		'aperture',
		'credit',
		'camera',
		'caption',
		'created_timestamp',
		'copyright',
		'focal_length',
		'iso',
		'shutter_speed',
		'title',
		'orientation',
	);

	/**
	 * Helper method to return EXIF information about an image attachment
	 *
	 * If the data does not exist in meta, the function attempts to get it and store
	 * it in the attachment metadata for reuse.
	 *
	 * @since 1.0.0
	 *
	 * @param   int $id Attachment ID.
	 * @param   int $file File Path.
	 * @return  array EXIF Data
	 */
	public function get_exif_data( $id, $file = false ) {

		require_once ABSPATH . 'wp-admin/includes/image.php';

		if ( false === $file ) {

			// Check if we can get EXIF data for this file type.
			$mime_type = get_post_mime_type( $id );
			if ( 'image/jpeg' !== $mime_type && 'image/jpeg' !== $mime_type ) {
				return false;
			}

			// Get attachment and existing EXIF data, if it exists.
			$attachment = get_post( $id );
			$exif       = get_post_meta( $id, '_envira_exif', true );

			// Check if EXIF data exists.
			if ( is_array( $exif ) ) {
				return $exif;
			}

			// If here, no EXIF data exists. Attempt to get it.

			$file    = get_attached_file( $id );
			$file_id = get_attached_file( $id );

		} else {
			$file_id = $file;
		}

		if ( ! $file || empty( $file ) ) {
			// Nothings there.
			return false;
		}

		set_error_handler( array( $this, 'exif_error_report' ) ); // @codingStandardsIgnoreLine

		$exif_data = exif_read_data( $file );

		restore_error_handler(); // @codingStandardsIgnoreLine

		if ( ! is_array( $exif_data ) ) {
			// Couldn't get any EXIF data.
			return false;
		}

		$wp_exif_data = wp_read_image_metadata( $file_id );

		if ( ! is_array( $wp_exif_data ) ) {
			// Couldn't get any EXIF data.
			return false;
		}

		// Build EXIF data array.
		$exif = array();
		foreach ( $this->exif_keys as $key ) {
			$exif[ $key ] = ( array_key_exists( $key, $exif_data ) ? $exif_data[ $key ] : ( array_key_exists( $key, $wp_exif_data ) ? $wp_exif_data[ $key ] : '' ) );
		}

		// Calculate Aperture.
		if ( isset( $exif['FNumber'] ) && ! empty( $exif['FNumber'] ) ) {
			list ( $a, $b )   = explode( '/', $exif['FNumber'] );
			$exif['Aperture'] = ( $a / $b );
		}

		// Calculate Shutter Speed.
		if ( $exif['shutter_speed'] > 0 && ( 1 / $exif['shutter_speed'] ) > 1 ) {
			if ( number_format( ( 1 / $exif['shutter_speed'] ), 1 ) === number_format( ( 1 / $exif['shutter_speed'] ), 0 ) ) {
				$exif['ShutterSpeed'] = '1/' . number_format( ( 1 / $exif['shutter_speed'] ), 0, '.', '' );
			} else {
				$exif['ShutterSpeed'] = '1/' . number_format( ( 1 / $exif['shutter_speed'] ), 1, '.', '' );
			}
		} else {
			$exif['ShutterSpeed'] = $exif['shutter_speed'];
		}

		// Calculate Focal Length.
		$exif['FocalLength'] = $exif['focal_length'];

		// Calculate Capture Time.
		$exif['CaptureTime'] = $exif['created_timestamp'];

		// Update EXIF data in gallery item, so we don't make multiple calls to exif_read_data().
		update_post_meta( $id, '_envira_exif', $exif );

		// Add Manufacturer Taxonomy Term.
		if ( ! empty( $exif['Make'] ) ) {
			// Check if term exists.
			$term = get_term_by( 'name', $exif['Make'], 'envira-exif-manufacturer' );

			// Term does not exist, create it.
			if ( ! $term ) {
				$term = wp_insert_term( $exif['Make'], 'envira-exif-manufacturer' );
			}

			// Get Term ID
			// Will be an array or object depending on if creating new term or grabbing existing term.
			$manufacturer_term_id = ( is_array( $term ) ? $term['term_id'] : $term->term_id );

			// Add term to attachment.
			wp_set_object_terms( $id, $manufacturer_term_id, 'envira-exif-manufacturer' );
		}

		// Add Model Taxonomy Term.
		if ( ! empty( $exif['Make'] ) ) {
			// Check if term exists.
			$term = get_term_by( 'name', $exif['Model'], 'envira-exif-manufacturer' );

			// Term does not exist, create it.
			if ( ! $term ) {
				// Set manufacturer term as parent, so we have manufacturer > model hierarchal terms.
				$term = wp_insert_term(
					$exif['Model'],
					'envira-exif-manufacturer',
					array(
						'parent' => $manufacturer_term_id,
					)
				);
			}

			// Get Term ID
			// Will be an array or object depending on if creating new term or grabbing existing term.
			$model_term_id = ( is_array( $term ) ? $term['term_id'] : $term->term_id );

			// Append term to attachment.
			wp_set_object_terms( $id, $model_term_id, 'envira-exif-manufacturer', true );
		}

		// Return.
		return $exif;

	}

	/**
	 * Handles error handling.
	 *
	 * @since 1.1.7
	 *
	 * @param  mixed $e_num value.
	 * @param  mixed $e_mes value.
	 * @param  mixed $e_file value.
	 * @param  mixed $e_line value.
	 * @param  mixed $e_vars value.
	 * @return void
	 */
	public function exif_error_report( $e_num, $e_mes, $e_file, $e_line, $e_vars ) {
		if ( strpos( $e_mes, 'exif_read_data' ) !== false ) {
			// @codingStandardsIgnoreStart
			error_log( 'Envira Debug: EXIF', 0 );
			error_log( $e_num );
			error_log( $e_mes );
			// @codingStandardsIgnoreEnd
		}
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.1.7
	 *
	 * @return object The Envira_Exif_Parser object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Exif_Parser ) ) {
			self::$instance = new Envira_Exif_Parser();
		}

		return self::$instance;

	}

}

// Load the EXIF class.
$envira_exif_parser = Envira_Exif_Parser::get_instance();
