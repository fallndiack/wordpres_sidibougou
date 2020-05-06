<?php
/**
 * Taxonomy class.
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
 * Taxonomy class.
 *
 * @since 1.0.0
 *
 * @package Envira_Exif
 * @author  Envira Team
 */
class Envira_Exif_Taxonomy {

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
	 * @since 1.0.5
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

		// Build the labels for the Manufacturers taxonomy.
		$manufacturer_labels = array(
			'name'                       => __( 'Manufacturers', 'envira-exif' ),
			'singular_name'              => __( 'Manufacturer', 'envira-exif' ),
			'search_items'               => __( 'Search Manufacturers', 'envira-exif' ),
			'popular_items'              => __( 'Popular Manufacturers', 'envira-exif' ),
			'all_items'                  => __( 'All Manufacturers', 'envira-exif' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Manufacturer', 'envira-exif' ),
			'update_item'                => __( 'Update Manufacturer', 'envira-exif' ),
			'add_new_item'               => __( 'Add New Manufacturer', 'envira-exif' ),
			'new_item_name'              => __( 'New Manufacturer Name', 'envira-exif' ),
			'separate_items_with_commas' => __( 'Separate manufacturers with commas', 'envira-exif' ),
			'add_or_remove_items'        => __( 'Add or remove manufacturers', 'envira-exif' ),
			'choose_from_most_used'      => __( 'Choose from the most used manufacturers', 'envira-exif' ),
			'not_found'                  => __( 'No manufacturers found.', 'envira-exif' ),
			'menu_name'                  => __( 'Manufacturers', 'envira-exif' ),
		);
		$manufacturer_labels = apply_filters( 'envira_exif_manufacturer_taxonomy_labels', $manufacturer_labels );

		// Build the taxonomy arguments for the Manufacturers taxonomy.
		$manufacturer_args = array(
			'hierarchical'      => true,
			'labels'            => $manufacturer_labels,
			'show_ui'           => true,
			'query_var'         => true,
			'show_in_nav_menus' => false,
			'rewrite'           => array( 'slug' => 'envira-exif-manufacturer' ),
		);
		$manufacturer_args = apply_filters( 'envira_exif_manufacturer_taxonomy_args', $manufacturer_args );

		// Register the taxonomies with WordPress.
		register_taxonomy( 'envira-exif-manufacturer', 'attachment', $manufacturer_args );

		// Move registered taxonomy menu items from Media to Envira Gallery.
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'move_taxonomy_menu_items' ) );
		}

		// Add custom admin columns.
		add_filter( 'manage_edit-envira-exif-manufacturer_columns', array( $this, 'envira_manufacturer_columns' ) );
		add_filter( 'manage_envira-exif-manufacturer_custom_column', array( $this, 'manage_manufacturer_columns' ), 10, 3 );

		// Add nav CSS.
		add_filter( 'admin_init', array( $this, 'menu_manufacturer_css' ), 999 );

	}

	/**
	 * Custom CSS For Nav Menus
	 *
	 * @since 1.0.5
	 *
	 * @return object The Envira_Tags_Taxonomy object.
	 */
	public function menu_manufacturer_css() {

		if ( isset( $_GET['taxonomy'] ) && 'envira-exif-manufacturer' == $_GET['taxonomy'] ) { // @codingStandardsIgnoreLine - NO NONCE

			global $submenu;

			if ( empty( $submenu ) || empty( $submenu['edit.php?post_type=envira'] ) ) {
				return;
			}
			foreach ( $submenu['edit.php?post_type=envira'] as $submenu_key => $submenu_item ) {
				if ( strtolower( $submenu_item[0] ) === 'manufacturers' ) {
					$submenu['edit.php?post_type=envira'][ $submenu_key ][0] = '<span style="color:#fff">' . $submenu['edit.php?post_type=envira'][ $submenu_key ][0] . '</span>'; // @codingStandardsIgnoreLine
				}
			}
		}

	}

	/**
	 * Return custom columns
	 *
	 * @since 1.0.5
	 *
	 * @param array $theme_columns Theme columns.
	 * @return object The Envira_Tags_Taxonomy object.
	 */
	public function envira_manufacturer_columns( $theme_columns ) {
		$new_columns = array(
			'cb'            => '<input type="checkbox" />',
			'name'          => __( 'Name' ),
			'description'   => __( 'Description' ),
			'slug'          => __( 'Slug' ),
			'envira_images' => __( 'Items' ),
		);
		return $new_columns;
	}

	/**
	 * Return custom columns
	 *
	 * @since 1.0.5
	 *
	 * @param string $out Output.
	 * @param string $column_name Column name.
	 * @param int    $term_id Term ID.
	 * @return object The Envira_Tags_Taxonomy object.
	 */
	public function manage_manufacturer_columns( $out, $column_name, $term_id ) {
		$term = get_term( $term_id, 'envira-exif-manufacturer' );
		switch ( $column_name ) {
			case 'envira_images':
				$args  = array(
					'post_type'   => 'attachment',
					'post_status' => 'inherit',
					// stopped using the 'post_mime_type' => 'image/jpeg,image/gif,image/jpg,image/png',.
					'tax_query'   => array( // @codingStandardsIgnoreLine
						array(
							'taxonomy' => 'envira-exif-manufacturer',
							'field'    => 'slug',
							'terms'    => $term->slug,
						),
					),
				);
				$query = new WP_Query( $args );
				$out  .= '<a href="' . admin_url( 'upload.php?envira-exif-manufacturer=' . $term->slug . '&post_type=attachment' ) . '">' . $query->found_posts . '</a>';
				break;

			default:
				break;
		}
		return $out;
	}

	/**
	 * Moves taxonomy menu items from Media to Envira Gallery.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function move_taxonomy_menu_items() {

		add_submenu_page( 'edit.php?post_type=envira', __( 'Manufacturers', 'envira-exif' ), __( 'Manufacturers', 'envira-exif' ), 'edit_others_posts', 'edit-tags.php?taxonomy=envira-exif-manufacturer&post_type=envira' );

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Exif_Taxonomy object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Exif_Taxonomy ) ) {
			self::$instance = new Envira_Exif_Taxonomy();
		}

		return self::$instance;

	}

}

// Load the taxonomy class.
$envira_exif_taxonomy = Envira_Exif_Taxonomy::get_instance();
