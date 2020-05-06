<?php
/**
 * WP List Table Admin Class.
 *
 * @since 1.3.0
 *
 * @package Envira_Albums
 * @author  Envira Team
 */

namespace Envira\Albums\Admin;

/**
 * WP List Table Admin Class.
 *
 * @since 1.3.0
 *
 * @package Envira_Albums
 * @author  Envira Team
 */
class Table {

	/**
	 * Holds the base class object.
	 *
	 * @since 1.3.0
	 *
	 * @var object
	 */
	public $base;

	/**
	 * Holds the metabox class object.
	 *
	 * @since 1.3.0
	 *
	 * @var object
	 */
	public $metabox;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.3.0
	 */
	public function __construct() {

		// Append data to various admin columns.
		add_filter( 'manage_edit-envira_album_columns', array( $this, 'columns' ) );
		add_action( 'manage_envira_album_posts_custom_column', array( $this, 'custom_columns' ), 10, 2 );

		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ), 51 ); // upped to 51 because of Event Calendar plugin.

		// Expand search with IDs in addition to WordPress default of post/album titles.
		add_action( 'posts_where', array( $this, 'enable_search_by_album_id' ), 10 );

	}

	/**
	 * Enables Search By Album ID
	 *
	 * @since 1.6.4.1
	 *
	 * @param  string $where Search.
	 * @return null Return early if not on the proper value.
	 */
	public function enable_search_by_album_id( $where ) {

		// Bail if we are not in the admin area or not doing a search.
		if ( ! is_admin() || ! is_search() ) {
			return $where;
		}

		// Bail if this is not the envira page.
		if ( empty( $_GET['post_type'] ) || 'envira_album' !== $_GET['post_type'] ) {        // @codingStandardsIgnoreLine
			return $where;
		}

		global $wpdb;

		// Get the value that is being searched.
		$search_string = get_query_var( 's' );

		if ( is_numeric( $search_string ) ) {

			$where = str_replace( '(' . $wpdb->posts . '.post_title LIKE', '(' . $wpdb->posts . '.ID = ' . $search_string . ') OR (' . $wpdb->posts . '.post_title LIKE', $where );

		} elseif ( preg_match( '/^(\d+)(,\s*\d+)*$/', $search_string ) ) { // string of post IDs.

			$where = str_replace( '(' . $wpdb->posts . '.post_title LIKE', '(' . $wpdb->posts . '.ID in (' . $search_string . ')) OR (' . $wpdb->posts . '.post_title LIKE', $where );
		}

		return $where;

	}

	/**
	 * Get Posts
	 *
	 * @since 1.3.0
	 *
	 * @param  array $query Query.
	 */
	public function pre_get_posts( $query ) {

		if ( is_admin() && 'edit.php' === $GLOBALS['pagenow']

			&& $query->is_main_query()
			&& $query->get( 'post_type' ) === 'envira_album' ) {

			$this->stickies   = array();
			$this->stickies[] = get_option( 'envira_default_album' );
			$this->stickies[] = get_option( 'envira_dynamic_album' );

			add_filter( 'post_class', array( $this, 'post_class' ), 10, 3 );
			add_filter( 'option_sticky_posts', array( $this, 'custom_stickies' ) );
			$query->is_home = 1;
			$query->set( 'ignore_sticky_posts', 0 );

		}

	}

	/**
	 * Customize the Stickies
	 *
	 * @since 1.3.0
	 *
	 * @param  array $data Data.
	 * @return array $data Data.
	 */
	public function custom_stickies( $data ) {

		if ( count( $this->stickies ) > 0 ) {

			$data = $this->stickies;

		}

		return $data;
	}

	/**
	 * Customize the CSS
	 *
	 * @since 1.3.0
	 *
	 * @param array   $classes Array of classes.
	 * @param string  $class   Class Name.
	 * @param integer $id      Class ID.
	 * @return array $classes CSS.
	 */
	public function post_class( $classes, $class, $id ) {

		if ( in_array( $id, $this->stickies, true ) ) {

			$classes[] = 'is-admin-sticky';

		}

		return $classes;

	}

	/**
	 * Customize the post columns for the Envira Album post type.
	 *
	 * @since 1.3.0
	 *
	 * @param array $columns  The default columns.
	 * @return array $columns Amended columns.
	 */
	public function columns( $columns ) {

		// Add additional columns we want to display.
		$envira_columns = array(
			'cb'        => '<input type="checkbox" />',
			'title'     => __( 'Title', 'envira-albums' ),
			'shortcode' => __( 'Shortcode', 'envira-albums' ),
			'galleries' => __( 'Number of Galleries', 'envira-albums' ),
			'modified'  => __( 'Last Modified', 'envira-albums' ),
			'date'      => __( 'Date', 'envira-albums' ),
		);

		// Allow filtering of columns.
		$envira_columns = apply_filters( 'envira_albums_table_columns', $envira_columns, $columns );

		// Return merged column set.  This allows plugins to output their columns (e.g. Yoast SEO),
		// and column management plugins, such as Admin Columns, should play nicely.
		return array_merge( $envira_columns, $columns );

	}

	/**
	 * Add data to the custom columns added to the Envira Album post type.
	 *
	 * @since 1.3.0
	 *
	 * @global object $post  The current post object
	 * @param string $column The name of the custom column.
	 * @param int    $post_id   The current post ID.
	 */
	public function custom_columns( $column, $post_id ) {

		$post_id = absint( $post_id );

		switch ( $column ) {
			/**
			* Shortcode
			*/
			case 'shortcode':
				echo '
				<div class="envira-code">
					<textarea class="code-textfield" id="envira_shortcode_' . sanitize_html_class( $post_id ) . '">[envira-album id=&quot;' . sanitize_html_class( $post_id ) . '&quot;]</textarea>
					<a href="#" title="' . esc_html__( 'Copy Shortcode to Clipboard', 'envira-album' ) . '" data-clipboard-target="#envira_shortcode_' . sanitize_html_class( $post_id ) . '" class="dashicons dashicons-clipboard envira-clipboard">
						<span>' . esc_html__( 'Copy to Clipboard', 'envira-album' ) . '</span>
					</a>
				</div>';
				break;

			/**
			* Galleries
			*/
			case 'galleries':
				$data = get_post_meta( $post_id, '_eg_album_data', true );
				echo ( isset( $data['galleryIDs'] ) ? count( $data['galleryIDs'] ) : 0 );
				break;

			/**
			* Last Modified
			*/
			case 'modified':
				the_modified_date();
				break;
		}

	}

}
