<?php
/**
 * AJAX class.
 *
 * @since 1.0.0
 *
 * @package Envira_Exif
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX class.
 *
 * @since 1.0.0
 *
 * @package Envira_Exif
 * @author  Envira Team
 */
class Envira_Exif_Ajax {

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

		// Envira Gallery.
		add_filter( 'envira_gallery_ajax_item_data', array( $this, 'get_exif_data' ), 10, 3 );

		// Tags Addon
		// Stores EXIF keywords as tags.
		$exif_tags = get_option( 'envira_exif_tags' );
		if ( $exif_tags ) {
			add_action( 'envira_tags_ajax_load_image', array( $this, 'get_exif_keywords_upload' ), 10, 2 );
			add_action( 'envira_tags_ajax_insert_images', array( $this, 'get_exif_keywords_insert' ), 10, 2 );
		}

	}

	/**
	 * Get EXIF data when an image is being uploaded to an Envira Gallery
	 *
	 * @since 1.0.0
	 *
	 * @param   array   $gallery_data   Gallery Data.
	 * @param   WP_Post $attachment    Image Attachment.
	 * @param   int     $id             Attachment ID.
	 * @return  array       $gallery_data   Gallery Data
	 */
	public function get_exif_data( $gallery_data, $attachment, $id ) {

		// Get EXIF data (this call will store the data against the attachment metadata).
		Envira_Exif_Parser::get_instance()->get_exif_data( $id );

		// Return the original gallery data.
		return $gallery_data;

	}

	/**
	 * Stores EXIF Keywords as Tags when uploading an Image.
	 *
	 * @since 1.0.1
	 *
	 * @param int $attachment_id  Attachment ID.
	 * @param int $post_id        Envira Gallery ID.
	 */
	public function get_exif_keywords_upload( $attachment_id, $post_id ) {

		$this->store_exif_keywords_as_tags( $attachment_id, $post_id );

	}

	/**
	 * Stores EXIF Keywords as Tags when selecting an image from the Media Library.
	 *
	 * @since 1.0.1
	 *
	 * @param array $images  Images.
	 * @param int   $post_id        Envira Gallery ID.
	 */
	public function get_exif_keywords_insert( $images, $post_id ) {

		// Iterate through images.
		foreach ( $images as $attachment ) {
			$this->store_exif_keywords_as_tags( $attachment['id'], $post_id );
		}

	}

	/**
	 * Main routine to extract EXIF keyword data and store as Envira Tags
	 *
	 * @since 1.0.1
	 *
	 * @param int $attachment_id  Attachment ID.
	 * @param int $post_id        Envira Gallery ID.
	 * @return null
	 */
	private function store_exif_keywords_as_tags( $attachment_id, $post_id ) {

		// Get file.
		$image = get_attached_file( $attachment_id );
		$tags  = array();

		// 1. EXIF
		// Keywords may be encoded
		// http://stackoverflow.com/questions/20441628/exif-read-data-keywords-decoded-incorrectly
		// Get current decoding, change it, read the keywords, and restore PHP settings back to how they were
		$exif_decode = ini_get( 'exif.decode_unicode_motorola' );
		ini_set( 'exif.decode_unicode_motorola', 'UCS-2LE' ); // @codingStandardsIgnoreLine
		$exif_data = exif_read_data( $image, 'IFD0' );
		ini_set( 'exif.decode_unicode_motorola', $exif_decode ); // @codingStandardsIgnoreLine
		if ( isset( $exif_data['Keywords'] ) && ! empty( $exif_data['Keywords'] ) ) {
			$tags = explode( ' ', $exif_data['Keywords'] );
		}

		// 2. IPTC
		// @TODO Improve.
		$size = getimagesize( $image, $info );
		if ( isset( $info['APP13'] ) ) {
			$iptc = iptcparse( $info['APP13'] );
			if ( isset( $iptc['2#025'] ) ) {

				$tags = $iptc['2#025'];
			}
		}

		// If no tags, bail.
		if ( count( $tags ) === 0 ) {
			return;
		}

		// Append against attachment.
		wp_set_object_terms( $attachment_id, $tags, 'envira-tag', true );

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Exif_Ajax object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Exif_Ajax ) ) {
			self::$instance = new Envira_Exif_Ajax();
		}

		return self::$instance;

	}

}

// Load the AJAX class.
$envira_exif_ajax = Envira_Exif_Ajax::get_instance();
