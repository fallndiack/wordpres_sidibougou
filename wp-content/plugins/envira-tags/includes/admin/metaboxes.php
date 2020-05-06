<?php
/**
 * Metabox class.
 *
 * @since 1.3.0
 *
 * @package Envira_Tags
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Metabox class.
 *
 * @since 1.3.0
 *
 * @package Envira_Tags
 * @author  Envira Team
 */
class Envira_Tags_Metaboxes {

	/**
	 * Holds the class object.
	 *
	 * @since 1.3.0
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.3.0
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Holds the base class object.
	 *
	 * @since 1.3.0
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
		$this->base = Envira_Tags::get_instance();

		// Gallery.
		add_action( 'envira_gallery_metabox_scripts', array( $this, 'scripts' ) );
		add_filter( 'envira_gallery_metabox_ids', array( $this, 'display_metaboxes' ) );
		add_filter( 'envira_gallery_get_gallery_item', array( $this, 'get_tags' ), 10, 3 );
		add_filter( 'envira_gallery_metabox_output_gallery_item_meta', array( $this, 'output_gallery_item_meta' ), 10, 5 );
		add_filter( 'envira_gallery_tab_nav', array( $this, 'tabs' ) );
		add_action( 'envira_gallery_tab_tags', array( $this, 'settings' ) );
		add_filter( 'envira_gallery_save_settings', array( $this, 'gallery_save_settings' ), 10, 2 );
		add_action( 'envira_gallery_flush_caches', array( $this, 'flush_caches' ), 10, 2 );
		add_action( 'envira_gallery_mobile_box', array( $this, 'mobile_screen' ) );

		// Album.
		add_action( 'envira_albums_metabox_scripts', array( $this, 'scripts' ) );
		add_filter( 'envira_albums_tab_nav', array( $this, 'tabs' ) );
		add_action( 'envira_albums_tab_tags', array( $this, 'settings' ) );
		add_filter( 'envira_albums_save_settings', array( $this, 'album_save_settings' ), 10, 2 );
		add_filter( 'envira_albums_save_settings', array( $this, 'album_save_taxonomy_settings' ), 15, 2 );
		add_action( 'envira_albums_mobile_box', array( $this, 'mobile_screen' ) );

	}

	/**
	 * Loads scripts for our metaboxes.
	 *
	 * @since 1.0.5
	 *
	 * @return void
	 */
	public function scripts() {

		// Load necessary metabox styles.
		wp_enqueue_style( $this->base->plugin_slug . '-tags-style', plugins_url( 'assets/css/tags-admin.css', plugin_basename( $this->base->file ) ), array(), $this->base->version );

		// Enqueue assets/js/metabox.js.
		wp_enqueue_script( $this->base->plugin_slug . '-tags-script', plugins_url( 'assets/js/min/metabox-min.js', plugin_basename( $this->base->file ) ), array( 'jquery' ), $this->base->version, true );
		wp_localize_script(
			$this->base->plugin_slug . '-tags-script',
			'envira_tags',
			array(
				'multiple' => __( 'Enter one or more tags, separated by commas. These will be applied to the selected images.', 'envira-tags' ),
				'nonce'    => wp_create_nonce( 'envira-tags-nonce' ),
			)
		);

		// Enqueue assets/js/media-edit.js.
		wp_enqueue_script( $this->base->plugin_slug . '-media-edit-script', plugins_url( 'assets/js/media-edit.js', plugin_basename( $this->base->file ) ), array( 'jquery' ), $this->base->version, true );

		// Conditional Fields.
		wp_register_script( $this->base->plugin_slug . '-conditional-fields-script', plugins_url( 'assets/js/min/conditional-fields-min.js', plugin_basename( $this->base->file ) ), array( 'jquery', Envira_Gallery::get_instance()->plugin_slug . '-conditional-fields-script' ), $this->base->version, true );
		wp_enqueue_script( $this->base->plugin_slug . '-conditional-fields-script' );

	}

	/**
	 * Ensures that the Envira Categories metabox displays when editing a Gallery or Album.
	 *
	 * @since 1.3.4
	 *
	 * @param   array $ids Array of Metaboxes IDs.
	 * @return  array Array of Metaboxes IDs.
	 */
	public function display_metaboxes( $ids ) {

		$ids[] = 'envira-categorydiv';
		return $ids;

	}

	/**
	 * Adds the item's tags to the gallery item array, so that the Media View can pass them via JSON
	 * to the modal
	 *
	 * @since 1.2.5
	 *
	 * @param array $item       Gallery Item.
	 * @param int   $attach_id  Attachment ID.
	 * @param int   $post_id    Gallery ID.
	 * @return array Gallery Item
	 */
	public function get_tags( $item, $attach_id, $post_id ) {

		// Build tags by getting them from the attachment.
		$item['tags'] = '';

		// Check tags exist.
		$tags = wp_get_object_terms( $attach_id, 'envira-tag' );
		if ( is_wp_error( $tags ) || empty( $tags ) || 0 === count( $tags ) ) {
			return $item;
		}

		// Build string of tags.
		foreach ( $tags as $tag ) {
			$item['tags'] .= $tag->name . ', ';
		}

		// Trim the string.
		$item['tags'] = rtrim( $item['tags'], ', ' );

		// Return.
		return $item;

	}

	/**
	 * Adds the item's tags to the gallery item output
	 *
	 * @since 1.3.6
	 *
	 * @param string $output    Meta Output.
	 * @param array  $item      Gallery Item.
	 * @param int    $attach_id Attachment ID.
	 * @param int    $post_id   Gallery ID.
	 * @param int    $type      Type.
	 * @return array  Gallery Item
	 */
	public function output_gallery_item_meta( $output, $item, $attach_id, $post_id, $type = 'list' ) {

		if ( 'list' === $type ) {
			return $output;
		}

		// Check tags exist.
		$tags = get_the_terms( $attach_id, 'envira-tag' );
		if ( is_wp_error( $tags ) || empty( $tags ) || 0 === count( $tags ) ) {
			return $output;
		}

		$output .= '<span class="envira-gallery-item-tags">';

		$output .= '<strong>' . __( 'TAGS: ', 'envira-tags' ) . '</strong>';

		// Build string of tags.
		foreach ( $tags as $tag ) {
			$output .= $tag->name . ', ';
		}

		// Trim the string.
		$output = rtrim( $output, ', ' ) . '<br />';

		$output .= '</span>';

		return $output;

	}

	/**
	 * Adds a new tab for this addon.
	 *
	 * @since 1.1.0
	 *
	 * @param array $tabs  Array of default tab values.
	 * @return array $tabs Amended array of default tab values.
	 */
	public function tabs( $tabs ) {

		if ( false === $tabs || ! is_array( $tabs ) ) {
			$tabs = array();
		}

		$tabs['tags'] = __( 'Tags', 'envira-tags' );
		return $tabs;

	}


	/**
	 * Adds addon setting to the Misc tab.
	 *
	 * @since 1.0.0
	 *
	 * @param object $post The current post object.
	 */
	public function settings( $post ) {

		$available_tags = array();

		// Get post type so we load the correct metabox instance and define the input field names.
		// Input field names vary depending on whether we are editing a Gallery or Album.
		$post_type = get_post_type( $post );
		switch ( $post_type ) {
			/**
			* Gallery
			*/
			case 'envira':
				$instance = Envira_Gallery_Metaboxes::get_instance();
				$key      = '_envira_gallery';
				$type     = __( 'Gallery', 'envira-tags' );
				$items    = __( 'images', 'envira-tags' );
				$taxonomy = 'envira-tag';
				$data     = get_post_meta( $post->ID, '_eg_gallery_data', true );

				// Build array of available tags based on gallery images.
				if ( ! empty( $data['gallery'] ) ) {
					foreach ( $data['gallery'] as $attachment_id => $image ) {
						$tags = wp_get_post_terms( $attachment_id, 'envira-tag' );
						if ( empty( $tags ) ) {
							continue;
						}

						foreach ( $tags as $tag ) {
							$available_tags[ $tag->slug ] = $tag->name;
						}
					}
				}
				break;

			/**
			* Album
			*/
			case 'envira_album':
				$instance = Envira_Albums_Metaboxes::get_instance();
				$key      = '_eg_album_data[config]';
				$type     = __( 'Album', 'envira-tags' );
				$items    = __( 'galleries', 'envira-tags' );
				$taxonomy = 'envira-category';
				$data     = get_post_meta( $post->ID, '_eg_album_data', true );

				// Build array of available tags based on album galleries.
				if ( ! empty( $data['galleryIDs'] ) ) {
					foreach ( $data['galleryIDs'] as $gallery_id ) {
						$tags = wp_get_post_terms( $gallery_id, $taxonomy );
						if ( empty( $tags ) ) {
							continue;
						}

						foreach ( $tags as $tag ) {
							$available_tags[ $tag->slug ] = $tag->name;
						}
					}
				}
				break;
		}

		// Get the most popular tags.
		$tags = get_terms(
			$taxonomy,
			array(
				'number'  => 5,
				'orderby' => 'count',
				'order'   => 'DESC',
			)
		);
		if ( is_array( $tags ) ) {
			foreach ( $tags as $tag_key => $tag ) {
				$tags[ $tag_key ]->link = '#';
			}
		}

		// Get sorting options.
		$sorting_options = Envira_Tags_Common::get_instance()->get_sorting_options();

		// Get position options.
		$position_options = Envira_Tags_Common::get_instance()->get_position_options();

		// Get manual sorting tags order.
		$manual_sorting = (array) $instance->get_config( 'tags_manual_sorting', $instance->get_config_default( 'tags_manual_sorting' ) );

		// Remove duplicates if they exist.
		if ( is_array( $manual_sorting ) && ! empty( $manual_sorting ) ) {
			$manual_sorting = array_unique( $manual_sorting );
		}

		wp_nonce_field( 'envira_tags_save_settings', 'envira_tags_nonce' );
		?>
		<div id="envira-tags">
			<p class="envira-intro">
				<?php /* translators: %s: term name */ ?>
				<?php $text = sprintf( __( 'Tags %s Settings', 'envira-tags' ), esc_html( $type ) ); ?>
				<?php echo esc_html( $text ); ?>

				<small>
					<?php /* translators: %s: term name */ ?>
					<?php $text = sprintf( __( 'The settings below adjust the Tag options for the %s output.', 'envira-tags' ), esc_html( $type ) ); ?>
					<?php echo esc_html( $text ); ?>

					<?php if ( apply_filters( 'envira_whitelabel', false ) ) : ?>
						<?php do_action( 'envira_tags_whitelabel_tab_helptext' ); ?>
					<?php else : ?>

					<br />
						<?php esc_html_e( 'Need some help?', 'envira-tags' ); ?>
					<a href="http://enviragallery.com/docs/tags-addon/" class="envira-doc" target="_blank">
						<?php esc_html_e( 'Read the Documentation', 'envira-tags' ); ?>
					</a>
					or
					<a href="https://www.youtube.com/embed/llXEWMiC8VY/?rel=0" class="envira-video" target="_blank">
						<?php esc_html_e( 'Watch a Video', 'envira-tags' ); ?>
					</a>

					<?php endif; ?>

				</small>

			</p>
			<table class="form-table">
				<tbody>
					<tr id="envira-config-tags-box">
						<th scope="row">
							<label for="envira-config-tags"><?php esc_html_e( 'Enable Tag Filtering?', 'envira-tags' ); ?></label>
						</th>
						<td>
							<input id="envira-config-tags" type="checkbox" name="<?php echo esc_html( $key ); ?>[tags]" value="<?php echo esc_html( $instance->get_config( 'tags', $instance->get_config_default( 'tags' ) ) ); ?>" <?php checked( $instance->get_config( 'tags', $instance->get_config_default( 'tags' ) ), 1 ); ?> />
							<span class="description">
							<?php /* translators: %s: term name */ ?>
							<?php $text = sprintf( __( 'Enables or disables tag filtering for the %s display.', 'envira-tags' ), esc_html( strtolower( $type ) ) ); ?>
							<?php echo esc_html( $text ); ?>
						</td>
					</tr>
					<tr id="envira-config-tags-position">
						<th scope="row">
							<label for="envira-config-tags-sorting"><?php esc_html_e( 'Tags Position?', 'envira-tags' ); ?></label>
						</th>
						<td>
							<select id="envira-config-tags-sorting" name="<?php echo esc_html( $key ); ?>[tags_position]" size="1">
								<?php
								foreach ( (array) $position_options as $position_option ) {
									?>
									<option value="<?php echo esc_html( $position_option['value'] ); ?>"<?php selected( $position_option['value'], $instance->get_config( 'tags_position', $instance->get_config_default( 'tags_position' ) ) ); ?>><?php echo esc_html( $position_option['name'] ); ?></option>
									<?php
								}
								?>
							</select>
							<p class="description"><?php esc_html_e( 'Define where you would like the tags displayed - above or below the gallery images.', 'envira-tags' ); ?></p>
						</td>
					</tr>
					<tr id="envira-config-tags-filtering-box">
						<th scope="row">
							<label for="envira-config-tags-filtering"><?php esc_html_e( 'Tags to include in Filtering', 'envira-tags' ); ?></label>
						</th>
						<td>
							<?php
							// Output tag meta box.
							post_tags_meta_box(
								$post,
								array(
									'id'    => 'envira-albums-box',
									'args'  => array(
										'taxonomy' => $taxonomy,
										'title'    => __( 'Tags', 'envira-tags' ),
									),
									'title' => __( 'Tags', 'envira-tags' ),
								)
							);

							// Most Popular Tags.
							if ( is_array( $tags ) ) {
								?>
								<p class="the-tagcloud">
									<?php
									echo wp_generate_tag_cloud(
										$tags,
										array(
											'filter' => 0,
										)
									);
									?>
								</p>
								<?php
							}

							// Output hidden field containing current taxonomy terms.
							?>
							<input type="hidden" class="envira-gallery-tags" name="<?php echo esc_html( $key ); ?>[tags_filter]" value="<?php echo esc_html( $instance->get_config( 'tags_filter', $instance->get_config_default( 'tags_filter' ) ) ); ?>" />
							<span class="description"><?php esc_html_e( 'Optionally define which tags to display. If none are set, the list of tags will be automatically generated.', 'envira-tags' ); ?></span>
						</td>
					</tr>
					<tr id="envira-config-tags-all-enabled-box">
						<th scope="row">
							<label for="envira-config-tags-all-enabled"><?php esc_html_e( 'Enable "All Tags" Option', 'envira-tags' ); ?></label>
						</th>
						<td>
							<input id="envira-config-tags" type="checkbox" name="<?php echo esc_html( $key ); ?>[tags_all_enabled]" value="<?php echo esc_html( $instance->get_config( 'tags_all_enabled', $instance->get_config_default( 'tags_all_enabled' ) ) ); ?>" <?php checked( $instance->get_config( 'tags_all_enabled', $instance->get_config_default( 'tags_all_enabled' ) ), 1 ); ?> />
							<span class="description">
							<?php /* translators: %s: term name */ ?>
							<?php $text = sprintf( __( 'Enables or disables the "All" tag, which shows all %s.', 'envira-tags' ), esc_html( $items ) ); ?>
							<?php echo esc_html( $text ); ?>
						</td>
					</tr>
					<tr id="envira-config-tags-all-box">
						<th scope="row">
							<label for="envira-config-tags-all"><?php esc_html_e( 'All Tags Label', 'envira-tags' ); ?></label>
						</th>
						<td>
							<input id="envira-config-tags-all" type="text" name="<?php echo esc_html( $key ); ?>[tags_all]" value="<?php echo esc_html( $instance->get_config( 'tags_all', $instance->get_config_default( 'tags_all' ) ) ); ?>" />
							<p class="description"><?php esc_html_e( 'The label to display for the All Tags link.', 'envira-tags' ); ?></p>
						</td>
					</tr>
					<tr id="envira-config-tags-sorting-box">
						<th scope="row">
							<label for="envira-config-tags-sorting"><?php esc_html_e( 'Sort Tags', 'envira-tags' ); ?></label>
						</th>
						<td>
							<select id="envira-config-tags-sorting" name="<?php echo esc_html( $key ); ?>[tags_sorting]" size="1">
								<?php
								foreach ( (array) $sorting_options as $sorting_option ) {
									?>
									<option value="<?php echo esc_html( $sorting_option['value'] ); ?>"<?php selected( $sorting_option['value'], $instance->get_config( 'tags_sorting', $instance->get_config_default( 'tags_sorting' ) ) ); ?>><?php echo esc_html( $sorting_option['name'] ); ?></option>
									<?php
								}
								?>
							</select>
							<p class="description"><?php esc_html_e( 'Define the display order for the Tag Filtering.', 'envira-tags' ); ?></p>
						</td>
					</tr>
					<tr id="envira-config-tags-manual-sorting">
						<th scope="row">
							<label for="envira-config-tags-manual-sorting"><?php esc_html_e( 'Manual Sorting Order', 'envira-tags' ); ?></label>
						</th>
						<td>
							<ul id="envira-tags-order">
								<?php
								// Output.
								$ordered_tag_slugs = array();

								// If we've previously defined a manual order for our tags, output that now.
								foreach ( $manual_sorting as $tag_slug ) {

									if ( ! empty( $available_tags ) ) {
										foreach ( $available_tags as $slug => $available_tag ) {
											$available_tags[ $this->sanitize_tag_slug( $slug ) ] = $available_tag;
										}
									}

									if ( isset( $available_tags[ $tag_slug ] ) ) {
										echo '<li id="' . esc_attr( $tag_slug ) . '">' . esc_html( $available_tags[ $tag_slug ] ) . '</li>';
									}

									// Build the ordered tag ID array for our hidden field.
									$ordered_tag_slugs[] = $tag_slug;
								}

								// Now output the image tags, excluding any we've just output.
								foreach ( $available_tags as $tag_slug => $tag ) {

									$tag_slug_to_check = $this->sanitize_tag_slug( $tag_slug );

									// Skip any image tags which have already been sorted in the manual sort order.
									if ( count( $manual_sorting ) !== 0 && in_array( $tag_slug_to_check, $manual_sorting, true ) ) {
										continue;
									}

									echo '<li id="' . esc_attr( $tag_slug_to_check ) . '">' . esc_attr( $tag ) . '</li>';

									// Build the ordered tag ID array for our hidden field.
									$ordered_tag_slugs[] = $tag_slug_to_check;
								}
								?>
							</ul>
							<input type="hidden" name="<?php echo esc_html( $key ); ?>[tags_manual_sorting]" value="<?php echo esc_html( implode( ',', $ordered_tag_slugs ) ); ?>" />
							<p class="description">
							<?php /* translators: %s: term name */ ?>
							<?php $text = sprintf( __( 'Drag and drop the tags to sort their display order. If %1$s in this %2$s has a tag which is not in the above list, Publish/Update the %3$s first.', 'envira-tags' ), esc_html( $items ), esc_html( $type ), esc_html( $type ) ); ?>
							<?php echo esc_html( $text ); ?>
							</p>
						</td>
					</tr>
					<tr id="envira-config-tags-display-box">
						<th scope="row">
							<label for="envira-config-tags-display">
							<?php /* translators: %s: term name */ ?>
							<?php $text = sprintf( __( 'Display Specific %s on Load', 'envira-tags' ), esc_html( $items ) ); ?>
							<?php echo esc_html( $text ); ?>
							</label>
						</th>
						<td>
							<select id="envira-config-tags-display" name="<?php echo esc_html( $key ); ?>[tags_display]" size="1">
								<option value=""<?php selected( '', $instance->get_config( 'tags_display', $instance->get_config_default( 'tags_display' ) ) ); ?>>
								<?php /* translators: %s: term name */ ?>
								<?php $text = sprintf( __( 'Display All %s', 'envira-tags' ), esc_html( $items ) ); ?>
								<?php echo esc_html( $text ); ?>
								</option>
								<?php
								foreach ( (array) $available_tags as $tag_slug => $tag ) {
									?>
									<option value="<?php echo esc_html( sanitize_title_with_dashes( $tag_slug ) ); ?>"<?php selected( $tag_slug, $instance->get_config( 'tags_display', $instance->get_config_default( 'tags_display' ) ) ); ?>><?php echo esc_html( $tag ); ?></option>
									<?php
								}
								?>
							</select>
							<p class="description">
							<?php /* translators: %s: term name */ ?>
							<?php $text = sprintf( __( 'If selected, only displays %s assigned to the selected tag on load.', 'envira-tags' ), esc_html( $items ) ); ?>
							<?php echo esc_html( $text ); ?>
							</p>
						</td>
					</tr>
					<tr id="envira-config-tags-scroll-box">
						<th scope="row">
							<label for="envira-config-tags-scroll">
							<?php /* translators: %s: term name */ ?>
							<?php $text = sprintf( __( 'Scroll to %s?', 'envira-tags' ), esc_html( $items ) ); ?>
							<?php echo esc_html( $text ); ?>
							</label>
						</th>
						<td>
							<input id="envira-config-tags-scroll" type="checkbox" name="<?php echo esc_html( $key ); ?>[tags_scroll]" value="1" <?php checked( $instance->get_config( 'tags_scroll', $instance->get_config_default( 'tags_scroll' ) ), 1 ); ?> />
							<span class="description">
							<?php /* translators: %s: term name */ ?>
							<?php $text = sprintf( __( 'If enabled, scrolls / jumps to the %s when a tag is clicked.', 'envira-tags' ), esc_html( $type ) ); ?>
							<?php echo esc_html( $text ); ?>						
							</span>
						</td>
					</tr>
					<?php

					if ( 'gallery' === strtolower( $type ) ) {

						?>
					<tr id="envira-config-tags-count-box">
						<th scope="row">
							<label for="envira-config-tags-count"><?php esc_html_e( 'Show Tag Count?', 'envira-tags' ); ?></label>
						</th>
						<td>
							<input id="envira-config-tags-count" type="checkbox" name="<?php echo esc_html( $key ); ?>[tags_count]" value="1" <?php checked( $instance->get_config( 'tags_count', $instance->get_config_default( 'tags_count' ) ), 1 ); ?> /><span class="description">
							<?php esc_html_e( 'If enabled, will show number of photos in gallery with the associated tag.', 'envira-tags' ); ?>
							</span>
						</td>
					</tr>
					<?php } ?>
					<?php
					if ( $instance->get_config( 'type' ) === 'dynamic' ) {
						?>
						<tr id="envira-config-tags-limit-box">
							<th scope="row">
								<label for="envira-config-tags-limit">
								<?php /* translators: %s: term name */ ?>
								<?php $text = sprintf( __( 'Number of %s', 'envira-tags' ), esc_html( $items ) ); ?>
								<?php echo esc_html( $text ); ?>
								</label>
							</th>
							<td>
								<input id="envira-config-tags-limit" type="text" name="<?php echo esc_html( $key ); ?>[tags_limit]" value="<?php echo esc_html( $instance->get_config( 'tags_limit', $instance->get_config_default( 'tags_limit' ) ) ); ?>" />
								<p class="description">
								<?php /* translators: %s: term name %s: term name */ ?>
								<?php $text = sprintf( __( 'Limit the number of %1$s to display when using the Dynamic Addon. Zero = all %2$s will be displayed.', 'envira-tags' ), esc_html( $items ), esc_html( $items ) ); ?>
								<?php echo esc_html( $text ); ?>
								</p>
							</td>
						</tr>
						<?php
					}
					?>
					<?php do_action( 'envira_tags_tag_box', $post ); ?>
				</tbody>
			</table>
		</div>
		<?php

	}

	/**
	 * Saves the addon setting for Galleries
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings  Array of settings to be saved.
	 * @param int   $post_id     The current post ID.
	 * @return array $settings Amended array of settings to be saved.
	 */
	public function gallery_save_settings( $settings, $post_id ) {

		if (
			! isset( $_POST['_envira_gallery'], $_POST['envira_tags_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['envira_tags_nonce'] ), 'envira_tags_save_settings' )
		) {
			return $settings;
		}

		$settings['config']['tags']                = ( isset( $_POST['_envira_gallery']['tags'] ) ? 1 : 0 );
		$settings['config']['tags_filter']         = ( isset( $_POST['_envira_gallery']['tags_filter'] ) ? sanitize_text_field( wp_unslash( $_POST['_envira_gallery']['tags_filter'] ) ) : false );
		$settings['config']['tags_position']       = ( isset( $_POST['_envira_gallery']['tags_position'] ) ? sanitize_text_field( wp_unslash( $_POST['_envira_gallery']['tags_position'] ) ) : false );
		$settings['config']['tags_all_enabled']    = ( isset( $_POST['_envira_gallery']['tags_all_enabled'] ) ? 1 : 0 );
		$settings['config']['tags_all']            = ( isset( $_POST['_envira_gallery']['tags_all'] ) ? sanitize_text_field( wp_unslash( $_POST['_envira_gallery']['tags_all'] ) ) : false );
		$settings['config']['tags_sorting']        = ( isset( $_POST['_envira_gallery']['tags_sorting'] ) ? sanitize_text_field( wp_unslash( $_POST['_envira_gallery']['tags_sorting'] ) ) : false );
		$settings['config']['tags_manual_sorting'] = ( isset( $_POST['_envira_gallery']['tags_manual_sorting'] ) ? explode( ',', ( wp_unslash( $_POST['_envira_gallery']['tags_manual_sorting'] ) ) ) : false ); // @codingStandardsIgnoreLine - todo: find suitable sanitization
		$settings['config']['tags_display']        = ( isset( $_POST['_envira_gallery']['tags_display'] ) ? sanitize_text_field( wp_unslash( $_POST['_envira_gallery']['tags_display'] ) ) : false );
		$settings['config']['tags_scroll']         = isset( $_POST['_envira_gallery']['tags_scroll'] ) ? 1 : 0;
		$settings['config']['tags_mobile']         = isset( $_POST['_envira_gallery']['tags_mobile'] ) ? 1 : 0;
		$settings['config']['tags_count']          = isset( $_POST['_envira_gallery']['tags_count'] ) ? 1 : 0;

		if ( isset( $_POST['_envira_gallery']['tags_limit'] ) ) {
			$settings['config']['tags_limit'] = absint( $_POST['_envira_gallery']['tags_limit'] );
		}

		if ( ! empty( $settings['config']['tags_manual_sorting'] ) ) {
			foreach ( $settings['config']['tags_manual_sorting'] as $index => $tag ) {
				$settings['config']['tags_manual_sorting'][ $index ] = $this->sanitize_tag_slug( $tag );
			}
		}

		return $settings;

	}

	/**
	 * Sanitize Tag Slug (foreign)
	 *
	 * @since 1.0.0
	 *
	 * @param string $tag Tag.
	 * @return string Tag.
	 */
	public function sanitize_tag_slug( $tag ) {

		$tag = sanitize_title( $tag );

		// @codingStandardsIgnoreStart
		return preg_replace( '/[^A-Za-z0-9 -]/', '', $tag );
		// @codingStandardsIgnoreEnd		

	}

	/**
	 * Saves the addon setting for Albums
	 *
	 * @since 1.4.0.1
	 *
	 * @param array $settings Array of settings to be saved.
	 * @param int   $post_id  The current post ID.
	 * @return array $settings Amended array of settings to be saved.
	 */
	public function album_save_settings( $settings, $post_id ) {

		if (
			! isset( $_POST['_eg_album_data'], $_POST['envira_tags_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['envira_tags_nonce'] ), 'envira_tags_save_settings' )
		) {
			return $settings;
		}

		$settings['config']['tags']                = ( isset( $_POST['_eg_album_data']['config']['tags'] ) ? 1 : 0 );
		$settings['config']['tags_filter']         = ( isset( $_POST['_eg_album_data']['config']['tags_filter'] ) ? sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['tags_filter'] ) ) : false );
		$settings['config']['tags_position']       = ( isset( $_POST['_eg_album_data']['config']['tags_position'] ) ? sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['tags_position'] ) ) : false );
		$settings['config']['tags_all_enabled']    = ( isset( $_POST['_eg_album_data']['config']['tags_all_enabled'] ) ? 1 : 0 );
		$settings['config']['tags_all']            = ( isset( $_POST['_eg_album_data']['config']['tags_all'] ) ? sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['tags_all'] ) ) : false );
		$settings['config']['tags_sorting']        = ( isset( $_POST['_eg_album_data']['config']['tags_sorting'] ) ? sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['tags_sorting'] ) ) : false );
		$settings['config']['tags_manual_sorting'] = ( isset( $_POST['_eg_album_data']['config']['tags_manual_sorting'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['tags_manual_sorting'] ) ) ) : false );
		$settings['config']['tags_display']        = ( isset( $_POST['_eg_album_data']['config']['tags_display'] ) ? sanitize_text_field( wp_unslash( $_POST['_eg_album_data']['config']['tags_display'] ) ) : false );
		$settings['config']['tags_scroll']         = isset( $_POST['_eg_album_data']['config']['tags_scroll'] ) ? 1 : 0;
		$settings['config']['tags_mobile']         = isset( $_POST['_eg_album_data']['config']['tags_mobile'] ) ? 1 : 0;

		if ( isset( $_POST['_eg_album_data']['config']['tags_limit'] ) ) {
			$settings['config']['tags_limit'] = absint( $_POST['_eg_album_data']['config']['tags_limit'] );
		}

		return $settings;

	}

	/**
	 * Adds addon settings UI to the Mobile tab
	 *
	 * @since 1.0.0
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
		<tr id="envira-config-tags-mobile-box">
			<th scope="row">
				<label for="envira-config-tags-mobile"><?php esc_html_e( 'Enable Tags On Mobile?', 'envira-tags' ); ?></label>
			</th>
			<td>
				<input id="envira-config-tags-mobile" type="checkbox" name="<?php echo esc_html( $key ); ?>[tags_mobile]" value="1" <?php checked( $instance->get_config( 'tags_mobile', $instance->get_config_default( 'tags_mobile' ) ), 1 ); ?> />
				<span class="description"><?php esc_html_e( 'Enables or disables tags on mobile devices.', 'envira-gallery' ); ?></span>
			</td>
		</tr>
		<?php

	}

	/**
	 * Ensures that taxonomy ('envira-category') info is updated for albums
	 *
	 * @since 1.4.1.1
	 *
	 * @param array $settings Array of settings to be saved.
	 * @param int   $post_id  The current post ID.
	 * @return array $settings Amended array of settings to be saved.
	 */
	public function album_save_taxonomy_settings( $settings, $post_id ) {

		$taxonomy = 'envira-category';

		// Get the already saved settings.
		$terms = ( isset( $settings['config'] ) && isset( $settings['tags_filter'] ) ) ? $settings['config']['tags_filter'] : false;

		if ( false === $terms ) {
			return $settings;
		}

		// Convert into an array, then find the term ids.
		$term_ids = array();
		$terms    = explode( ',', $terms );
		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term_name ) {
				$the_term = get_term_by( 'name', trim( $term_name ), $taxonomy );
				if ( $the_term ) {
					$term_ids[] = $the_term->term_id;
				}
			}
		}

		// This should be a comma delimited list of values OR nothing at all.
		// Either way we pass this along to wp_set_post_terms.
		$result = wp_set_post_terms( $post_id, $term_ids, $taxonomy, false );
		if ( is_wp_error( $result ) ) {
			echo esc_html( $result->get_error_message() );
		}

		return $settings;

	}


	/**
	 * Flushes the tag gallery cache.
	 *
	 * @since 1.0.0
	 *
	 * @global object $wpdb The WordPress database object.
	 */
	public function flush_caches() {

		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE (%s)", '%\_eg\_tags\_%' ) ); // @codingStandardsIgnoreLine

	}


	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.3.0
	 *
	 * @return object The Envira_Tags_Metaboxes object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Tags_Metaboxes ) ) {
			self::$instance = new Envira_Tags_Metaboxes();
		}

		return self::$instance;

	}

}

// Load the metaboxes class.
$envira_tags_metaboxes = Envira_Tags_Metaboxes::get_instance();
