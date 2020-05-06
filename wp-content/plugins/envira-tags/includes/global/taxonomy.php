<?php
/**
 * Taxonomy class.
 *
 * @since 1.0.5
 *
 * @package Envira_Tags
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Taxonomy class.
 *
 * @since 1.0.5
 *
 * @package Envira_Tags
 * @author  Envira Team
 */
class Envira_Tags_Taxonomy {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.5
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.0.5
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
	 * @since 1.0.5
	 */
	public function __construct() {

		// Load the base class object.
		$this->base = Envira_Gallery::get_instance();

		$envira_whitelabel_name_singular = esc_html( apply_filters( 'envira_whitelabel_name_singular', false ) );

		// Build the labels for the taxonomy.
		$labels = array(
			'name'                       => apply_filters( 'envira_whitelabel', false ) ? $envira_whitelabel_name_singular . ' Tags' : __( 'Envira Tags', 'envira-tags' ),
			'singular_name'              => apply_filters( 'envira_whitelabel', false ) ? $envira_whitelabel_name_singular . ' Tags' : __( 'Envira Tag', 'envira-tags' ),
			'search_items'               => __( 'Search Tags', 'envira-tags' ),
			'popular_items'              => __( 'Popular Tags', 'envira-tags' ),
			'all_items'                  => __( 'All Tags', 'envira-tags' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Tag', 'envira-tags' ),
			'update_item'                => __( 'Update Tag', 'envira-tags' ),
			'add_new_item'               => __( 'Add New Tag', 'envira-tags' ),
			'new_item_name'              => __( 'New Tag Name', 'envira-tags' ),
			'separate_items_with_commas' => __( 'Separate tags with commas', 'envira-tags' ),
			'add_or_remove_items'        => __( 'Add or remove tags', 'envira-tags' ),
			'choose_from_most_used'      => __( 'Choose from the most used tags', 'envira-tags' ),
			'not_found'                  => __( 'No tags found.', 'envira-tags' ),
			'menu_name'                  => apply_filters( 'envira_whitelabel', false ) ? $envira_whitelabel_name_singular . ' Tags' : __( 'Envira Tags', 'envira-tags' ),
		);
		$labels = apply_filters( 'envira_tags_taxonomy_labels', $labels );

		// Build the taxonomy arguments.
		$args = array(
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'query_var'             => true,
			'show_in_nav_menus'     => false,
			'show_tagcloud'         => false,
			'rewrite'               => array( 'slug' => 'envira-tag' ),
			'update_count_callback' => '_update_post_term_count',
			'show_admin_column'     => true,
		);
		$args = apply_filters( 'envira_tags_taxonomy_args', $args );

		// Register the taxonomy with WordPress.
		register_taxonomy( 'envira-tag', 'attachment', $args );

		// Build the labels for the taxonomy.
		$labels = array(
			'name'                       => apply_filters( 'envira_whitelabel', false ) ? $envira_whitelabel_name_singular . ' Categories' : __( 'Envira Categories', 'envira-tags' ),
			'singular_name'              => apply_filters( 'envira_whitelabel', false ) ? $envira_whitelabel_name_singular . ' Categories' : __( 'Envira Category', 'envira-tags' ),
			'search_items'               => __( 'Search Categories', 'envira-tags' ),
			'popular_items'              => __( 'Popular Categories', 'envira-tags' ),
			'all_items'                  => __( 'All Categories', 'envira-tags' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Category', 'envira-tags' ),
			'update_item'                => __( 'Update Category', 'envira-tags' ),
			'add_new_item'               => __( 'Add New Category', 'envira-tags' ),
			'new_item_name'              => __( 'New Category Name', 'envira-tags' ),
			'separate_items_with_commas' => __( 'Separate categories with commas', 'envira-tags' ),
			'add_or_remove_items'        => __( 'Add or remove categories', 'envira-tags' ),
			'choose_from_most_used'      => __( 'Choose from the most used categories', 'envira-tags' ),
			'not_found'                  => __( 'No categories found.', 'envira-tags' ),
			'menu_name'                  => __( 'Categories', 'envira-tags' ),
		);
		$labels = apply_filters( 'envira_tags_category_taxonomy_labels', $labels );

		// Build the taxonomy arguments.
		$args = array(
			'hierarchical'         => true,
			'labels'               => $labels,
			'show_ui'              => true,
			'query_var'            => true,
			'show_in_nav_menus'    => false,
			'show_tagcloud'        => false,
			'rewrite'              => array( 'slug' => 'envira-category' ),
			'meta_box_sanitize_cb' => 'taxonomy_meta_box_sanitize_cb_input', // WP 5.1 adjustment GH #3086.
		);
		$args = apply_filters( 'envira_tags_category_taxonomy_args', $args );

		// Register the category taxonomy with WordPress.
		register_taxonomy( 'envira-category', array( 'envira', 'envira_album' ), $args );

		// Add custom admin columns.
		add_filter( 'manage_edit-envira-tag_columns', array( $this, 'envira_tag_columns' ) );
		add_filter( 'manage_envira-tag_custom_column', array( $this, 'manage_tag_columns' ), 10, 3 );

		// Add nav CSS.
		add_filter( 'admin_init', array( $this, 'menu_tags_css' ), 999 );

	}

	/**
	 * Custom CSS For Nav Menus
	 *
	 * @since 1.0.5
	 *
	 * @return object The Envira_Tags_Taxonomy object.
	 */
	public function menu_tags_css() {

		if ( isset( $_GET['taxonomy'] ) && 'envira-tag' === $_GET['taxonomy'] ) { // @codingStandardsIgnoreLine - recode for allow for nonce

			global $submenu;

			if ( empty( $submenu ) || empty( $submenu['edit.php?post_type=envira'] ) ) {
				return;
			}
			foreach ( $submenu['edit.php?post_type=envira'] as $submenu_key => $submenu_item ) {
				if ( strtolower( $submenu_item[0] ) === 'tags' ) {
					$submenu['edit.php?post_type=envira'][ $submenu_key ][0] = '<span style="color:#fff">' . $submenu['edit.php?post_type=envira'][ $submenu_key ][0] . '</span>'; // @codingStandardsIgnoreLine
				}
			}
		}

	}

	/**
	 * Return custom columns
	 *
	 * @since 1.0.5
	 * @param array $theme_columns Theme Columns.
	 * @return object The Envira_Tags_Taxonomy object.
	 */
	public function envira_tag_columns( $theme_columns ) {
		$new_columns = array(
			'cb'                => '<input type="checkbox" />',
			'name'              => __( 'Name' ),
			'description'       => __( 'Description' ),
			'slug'              => __( 'Slug' ),
			'envira_tag_images' => __( 'Items' ),
		);
		return $new_columns;
	}

	/**
	 * Return custom columns
	 *
	 * @since 1.0.5
	 * @param string $out Out.
	 * @param string $column_name Column Name.
	 * @param int    $term_id Term ID.
	 * @return object The Envira_Tags_Taxonomy object.
	 */
	public function manage_tag_columns( $out, $column_name, $term_id ) {
		$term = get_term( $term_id, 'envira-tag' );
		switch ( $column_name ) {
			case 'envira_tag_images':
				$args  = array(
					'post_type'   => 'attachment',
					'post_status' => 'inherit',
					'tax_query'   => array( // @codingStandardsIgnoreLine
						array(
							'taxonomy' => 'envira-tag',
							'field'    => 'slug',
							'terms'    => $term->slug,
						),
					),
				);
				$query = new WP_Query( $args );
				$out  .= '<a href="' . admin_url( 'upload.php?envira-tag=' . $term->slug . '&post_type=attachment' ) . '">' . $query->found_posts . '</a>';
				break;

			default:
				break;
		}
		return $out;
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.5
	 *
	 * @return object The Envira_Tags_Taxonomy object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Tags_Taxonomy ) ) {
			self::$instance = new Envira_Tags_Taxonomy();
		}

		return self::$instance;

	}

}

// Load the taxonomy class.
$envira_tags_taxonomy = Envira_Tags_Taxonomy::get_instance();
