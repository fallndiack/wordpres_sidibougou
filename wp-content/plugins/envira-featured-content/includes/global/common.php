<?php
/**
 * Common class.
 *
 * @since 1.0.0
 *
 * @package Envira_Featured_Content
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Common class.
 *
 * @since 1.0.0
 *
 * @package Envira_Featured_Content
 * @author  Envira Team
 */
class Envira_Featured_Content_Common {

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
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		add_filter( 'envira_gallery_defaults', array( $this, 'defaults' ), 10, 2 );
		add_action( 'wp_loaded', array( $this, 'register_publish_hooks' ) );
		add_action( 'save_post', array( $this, 'flush_global_caches' ), 999 );
		add_action( 'pre_post_update', array( $this, 'flush_global_caches' ), 999 );
		add_action( 'envira_gallery_flush_caches', array( $this, 'flush_caches' ), 10, 2 );

	}

	/**
	 * Adds the default settings for this addon.
	 *
	 * @since 1.0.0
	 *
	 * @param array $defaults  Array of default config values.
	 * @param int   $post_id     The current post ID.
	 * @return array $defaults Amended array of default config values.
	 */
	public function defaults( $defaults, $post_id ) {

		$defaults['fc_post_types']       = array( 'post' );
		$defaults['fc_terms']            = array();
		$defaults['fc_terms_relation']   = 'IN';
		$defaults['fc_include_posts']    = array();
		$defaults['fc_exclude_posts']    = array();
		$defaults['fc_sticky']           = 0;
		$defaults['fc_orderby']          = 'date';
		$defaults['fc_order']            = 'DESC';
		$defaults['fc_number']           = 5;
		$defaults['fc_offset']           = 0;
		$defaults['fc_status']           = 'publish';
		$defaults['fc_content_type']     = 'post_excerpt';
		$defaults['fc_content_length']   = 40;
		$defaults['fc_content_ellipses'] = 1;
		$defaults['fc_date_define']      = false;

		// Content Settings.
		$defaults['fc_post_url']        = 1;
		$defaults['fc_post_new_window'] = 0;
		$defaults['fc_fallback']        = '';

		// Return.
		return $defaults;

	}

	/**
	 * Helper method for retrieving additional copy options for legacy gallery views
	 *
	 * @since 1.7.0
	 *
	 * @return array Array of last data.
	 */
	public function envira_get_fc_date_define_options() {

		$options = array(
			'datetime' => __( 'Date/Time', 'envira-gallery' ),
			'hours'    => __( 'Post Age (Hours)', 'envira-gallery' ),
		);

		return apply_filters( 'envira_get_fc_date_define_options', $options );

	}

	/**
	 * Registers publish and publish future actions for each public Post Type,
	 * so FC caches can be flushed
	 *
	 * @since 2.3.0
	 */
	public function register_publish_hooks() {

		// Get public Post Types.
		$post_types = get_post_types(
			array(
				'public' => true,
			),
			'objects'
		);

		// Register publish hooks for each Post Type.
		foreach ( $post_types as $post_type => $data ) {
			add_action( 'publish_' . $post_type, array( $this, 'flush_global_caches' ) );
			add_action( 'publish_future_' . $post_type, array( $this, 'flush_global_caches' ) );
		}

	}

	/**
	 * Callback for post types to exclude from the dropdown select box.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of post types to exclude.
	 */
	public function get_post_types() {

		$post_types = apply_filters( 'envira_featured_content_excluded_post_types', array( 'attachment', 'soliloquy', 'envira', 'envira_album' ) );
		return (array) $post_types;

	}

	/**
	 * Callback for taxonomies to exclude from the dropdown select box.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of taxonomies to exclude.
	 */
	public function get_taxonomies() {

		$taxonomies = apply_filters( 'envira_featured_content_excluded_taxonomies', array( 'nav_menu' ) );
		return (array) $taxonomies;

	}

	/**
	 * Callback for taxonomy relation options.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of taxonomies to exclude.
	 */
	public function get_taxonomy_relations() {

		$relations = array(
			'AND' => __( 'Posts must have ALL of the above taxonomy terms (AND)', 'envira-featured-content' ),
			'IN'  => __( 'Posts must have ANY of the above taxonomy terms (IN)', 'envira-featured-content' ),
		);

		// Allow relations to be filtered.
		$relations = apply_filters( 'envira_featured_content_taxonomy_relations', $relations );

		return (array) $relations;

	}

	/**
	 * Returns the available orderby options for the query.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of orderby data.
	 */
	public function get_orderby() {

		$orderby = array(
			array(
				'name'  => __( 'Date', 'envira-featured-content' ),
				'value' => 'date',
			),
			array(
				'name'  => __( 'ID', 'envira-featured-content' ),
				'value' => 'ID',
			),
			array(
				'name'  => __( 'Author', 'envira-featured-content' ),
				'value' => 'author',
			),
			array(
				'name'  => __( 'Title', 'envira-featured-content' ),
				'value' => 'title',
			),
			array(
				'name'  => __( 'Menu Order', 'envira-featured-content' ),
				'value' => 'menu_order',
			),
			array(
				'name'  => __( 'Random', 'envira-featured-content' ),
				'value' => 'rand',
			),
			array(
				'name'  => __( 'Comment Count', 'envira-featured-content' ),
				'value' => 'comment_count',
			),
			array(
				'name'  => __( 'Post Name', 'envira-featured-content' ),
				'value' => 'name',
			),
			array(
				'name'  => __( 'Modified Date', 'envira-featured-content' ),
				'value' => 'modified',
			),
			array(
				'name'  => __( 'Meta Value', 'envira-featured-content' ),
				'value' => 'meta_value',
			),
			array(
				'name'  => __( 'Meta Value (Numeric)', 'envira-featured-content' ),
				'value' => 'meta_value_num',
			),
		);

		return apply_filters( 'envira_featured_content_orderby', $orderby );

	}

	/**
	 * Returns the available order options for the query.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of order data.
	 */
	public function get_order() {

		$order = array(
			array(
				'name'  => __( 'Descending Order', 'envira-featured-content' ),
				'value' => 'DESC',
			),
			array(
				'name'  => __( 'Ascending Order', 'envira-featured-content' ),
				'value' => 'ASC',
			),
		);

		return apply_filters( 'envira_featured_content_order', $order );

	}

	/**
	 * Returns the available post status options for the query.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of post status data.
	 */
	public function get_statuses() {

		$statuses = get_post_stati( array( 'internal' => false ), 'objects' );
		return apply_filters( 'envira_featured_content_statuses', $statuses );

	}

	/**
	 * Returns the available content type options for the query output.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of content type data.
	 */
	public function get_content_types() {

		$types = array(
			array(
				'name'  => __( 'No Content', 'envira-featured-content' ),
				'value' => 'none',
			),
			array(
				'name'  => __( 'Post Content', 'envira-featured-content' ),
				'value' => 'post_content',
			),
			array(
				'name'  => __( 'Post Excerpt', 'envira-featured-content' ),
				'value' => 'post_excerpt',
			),
		);

		return apply_filters( 'envira_featured_content_content_types', $types );

	}

	/**
	 * Flushes the Featured Content data caches globally on save/update of any post.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id The current post ID.
	 */
	public function flush_global_caches( $post_id ) {

		// Get all Featured Content Galleries.
		$galleries = Envira_Gallery::get_instance()->get_galleries();
		if ( is_array( $galleries ) ) {
			foreach ( $galleries as $gallery ) {
				// Skip non-FC galleries.
				if ( ! empty( $gallery['config']['type'] ) && 'fc' !== $gallery['config']['type'] ) {
					continue;
				}

				// Check gallery ID exists
				// Does not exist on gallery creation.
				if ( ! isset( $gallery['id'] ) ) {
					continue;
				}

				// Delete the ID cache.
				delete_transient( '_eg_cache_' . $gallery['id'] );
				delete_transient( '_envira_featured_content_' . $gallery['id'] );

				// Delete the slug cache.
				$slug = get_post_meta( $gallery['id'], '_eg_gallery_data', true );
				if ( ! empty( $slug['config']['slug'] ) ) {
					delete_transient( '_eg_cache_' . $slug['config']['slug'] );
					delete_transient( '_envira_featured_content_' . $slug['config']['slug'] );
				}
			}
		}

	}

	/**
	 * Flushes the Featured Content data caches on save.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $post_id The current post ID.
	 * @param string $slug The current gallery slug.
	 */
	public function flush_caches( $post_id, $slug ) {

		delete_transient( '_envira_featured_content_' . $post_id );
		delete_transient( '_envira_featured_content_' . $slug );

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Featured_Content_Common object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Featured_Content_Common ) ) {
			self::$instance = new Envira_Featured_Content_Common();
		}

		return self::$instance;

	}

}

// Load the common class.
$envira_featured_content_common = Envira_Featured_Content_Common::get_instance();
