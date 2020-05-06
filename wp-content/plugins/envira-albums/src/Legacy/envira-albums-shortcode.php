<?php
// @codingStandardsIgnoreFile

class Envira_Albums_Shortcode {

	/**
	 * is_mobile
	 *
	 * @var mixed
	 * @access public
	 */
	public $is_mobile;

	public static $_instance = null;

	/**
	 * Array of gallery item ids on the page.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $album_item_ids = array();

	/**
	 * Helper method for retrieving config values.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The config key to retrieve.
	 * @param array  $data The gallery data to use for retrieval.
	 * @return string     Key value on success, default if not set.
	 */
	public function get_config( $key, $data ) {

		// bail if no data.
		if ( ! is_array( $data ) ) {
			return;
		}

		$instance = Envira_Albums_Common::get_instance();

		// If we are on a mobile device, some config keys have mobile equivalents, which we need to check instead.
		if ( envira_mobile_detect()->isMobile() ) {
			$mobile_keys = array(
				// 'columns'           => 'mobile_columns',
					'lightbox'      => 'mobile_lightbox',
				'arrows'            => 'mobile_arrows',
				'toolbar'           => 'mobile_toolbar',
				'thumbnails'        => 'mobile_thumbnails',
				'thumbnails_width'  => 'mobile_thumbnails_width',
				'thumbnails_height' => 'mobile_thumbnails_height',
			);
			$mobile_keys = apply_filters( 'envira_albums_get_config_mobile_keys', $mobile_keys );

			if ( array_key_exists( $key, $mobile_keys ) ) {
				// Use the mobile array key to get the config value.
				$key = $mobile_keys[ $key ];
			}
		}

		// We need supersize for the base dark theme, so we are forcing it here.
		if ( $key === 'supersize' && isset( $data['config']['lightbox_theme'] ) && $data['config']['lightbox_theme'] === 'base_dark' ) {
			$data['config'][ $key ] = 1;
		}

		// The toolbar is not needed for base dark so lets disable it.
		if ( $key === 'toolbar' && isset( $data['config']['lightbox_theme'] ) && $data['config']['lightbox_theme'] === 'base_dark' ) {
			$data['config'][ $key ] = 0;
		}

		$data['config'] = apply_filters( 'envira_albums_get_config', $data['config'], $key );

		return isset( $data['config'][ $key ] ) ? $data['config'][ $key ] : $instance->get_config_default( $key );

	}
	/**
	 * Helper method to minify a string of data.
	 *
	 * @since 1.0.4
	 *
	 * @param string $string  String of data to minify.
	 * @return string $string Minified string of data.
	 */
	public function minify( $string, $stripDoubleForwardslashes = true ) {

		// Added a switch for stripping double forwardslashes
		// This can be disabled when using URLs in JS, to ensure http:// doesn't get removed
		// All other comment removal and minification will take place
		$stripDoubleForwardslashes = apply_filters( 'envira_minify_strip_double_forward_slashes', $stripDoubleForwardslashes );

		if ( $stripDoubleForwardslashes ) {
			// $clean = preg_replace( '/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/', '', $string );
			$clean = preg_replace( '/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/', '', $string );

		} else {
			// Use less aggressive method
			$clean = preg_replace( '!/\*.*?\*/!s', '', $string );
			$clean = preg_replace( '/\n\s*\n/', "\n", $clean );
		}

		$clean = str_replace( array( "\r\n", "\r", "\t", "\n", '  ', '    ', '     ' ), '', $clean );

		return apply_filters( 'envira_gallery_minified_string', $clean, $string );

	}

	/**
	 * Outputs an individual album item in the grid
	 *
	 * @since 1.2.5.0
	 *
	 * @param    string $album      Album HTML
	 * @param    array  $data       Album Config
	 * @param    int    $id         Album Gallery ID
	 * @param    int    $i          Index
	 * @return   string              Album HTML
	 */
	public function generate_album_item_markup( $album, $data, $id, $i ) {

		// Skip blank entries.
		if ( empty( $id ) ) {

			return $album;

		}

		$gallery_data = envira_get_gallery( $id );

		// Get some config values that we'll reuse for each gallery.
		$padding = absint( round( envira_albums_get_config( 'gutter', $data ) / 2 ) );

		// Get Gallery.
		$item = $data['galleries'][ $id ];
		$item = apply_filters( 'envira_albums_output_item_data', $item, $id, $data, $i );

		// Get image.
		$imagesrc         = $this->get_image_src( $item['cover_image_id'], $item, $data );
		$image_src_retina = $this->get_image_src( $item['cover_image_id'], $item, $data, false, true ); // copied from gallery shortcode.
		$placeholder      = wp_get_attachment_image_src( $item['cover_image_id'], 'medium' ); // $placeholder is null because $id is 0 for instagram? // copied from gallery shortcode

		// Get Link New Window Only When Lightbox Isn't Available For The Album.
		$link_new_window = false;

		if ( empty( $data['gallery_lightbox'] ) && ! empty( $item['link_new_window'] ) ) {
			$link_new_window = $item['link_new_window'];
		}

		$gallery_theme_name = envira_albums_get_config( 'gallery_theme', $data );

		$album = apply_filters( 'envira_albums_output_before_item', $album, $id, $item, $data, $i );

		$output = '<div id="envira-gallery-item-' . sanitize_html_class( $id ) . '" class="' . $this->get_gallery_item_classes( $item, $i, $data ) . '" style="padding-left: ' . $padding . 'px; padding-bottom: ' . envira_albums_get_config( 'margin', $data ) . 'px; padding-right: ' . $padding . 'px;" ' . apply_filters( 'envira_albums_output_item_attr', '', $id, $item, $data, $i ) . '>';

		// Display Gallery Description (Above).
		if ( isset( $data['config']['gallery_description_display'] ) && 'display-above' === $data['config']['gallery_description_display'] && 0 !== (int) $data['config']['columns'] && isset( $item['id'] ) ) {
			$output = apply_filters( 'envira_albums_output_before_gallery_description', $output, $id, $item, $data, $i );

			// Get description.
			if ( isset( $gallery_data['config']['description'] ) && $gallery_data['config']['description'] ) {

				$gallery_description = wp_kses( $gallery_data['config']['description'], envira_get_allowed_tags() );
				$output             .= '<div class="envira-album-gallery-description">' . apply_filters( 'envira_albums_output_gallery_description', $gallery_description, $id, $item, $data, $i ) . '</div>';
			}
			$output = apply_filters( 'envira_albums_output_before_gallery_description', $output, $id, $item, $data, $i );
		}

		// Display Title.
		// Note: We added the ability to add titles ABOVE in addition to below, but we still need to honor the deprecated setting.
		if ( isset( $data['config']['display_titles'] ) && 'above' === $data['config']['display_titles'] && 0 !== (int) $data['config']['columns'] ) {

			$new_window = $link_new_window ? 'target="_blank" ' : '';

			$album_title = ( ! empty( $item['link_title_gallery'] ) && 1 === intval( $item['link_title_gallery'] ) ) ? '<a ' . $new_window . ' href="' . get_permalink( $id ) . '">' . htmlspecialchars_decode( $item['title'] ) . '</a>' : htmlspecialchars_decode( $item['title'] );

			$album_title = apply_filters( 'envira_albums_album_title', $album_title, $id, $item, $data, $i );

			if ( ! empty( $item['title'] ) ) {
				$output .= '<div class="envira-album-title">' . $album_title . '</div>';
			}

			$output = apply_filters( 'envira_albums_output_after_title', $output, $id, $item, $data, $i );

		}

			$output .= '<div class="envira-gallery-item-inner">';
			$output  = apply_filters( 'envira_albums_output_before_link', $output, $id, $item, $data, $i );

			// Top Left box.
			$css_class = false; // no css classes yet.
			$css_class = apply_filters( 'envira_albums_output_dynamic_position_css', $css_class, $output, $id, $item, $data, $i, 'top-left' );

			$output .= '<div class="envira-gallery-position-overlay ' . $css_class . ' envira-gallery-top-left">';
			$output  = apply_filters( 'envira_albums_output_dynamic_position', $output, $id, $item, $data, $i, 'top-left' );
			$output .= '</div>';

			// Top Right box.
			$css_class = false; // no css classes yet.
			$css_class = apply_filters( 'envira_albums_output_dynamic_position_css', $css_class, $output, $id, $item, $data, $i, 'top-right' );

			$output .= '<div class="envira-gallery-position-overlay ' . $css_class . ' envira-gallery-top-right">';
			$output  = apply_filters( 'envira_albums_output_dynamic_position', $output, $id, $item, $data, $i, 'top-right' );
			$output .= '</div>';

			// Bottom Left box.
			$css_class = false; // no css classes yet.
			$css_class = apply_filters( 'envira_albums_output_dynamic_position_css', $css_class, $output, $id, $item, $data, $i, 'bottom-left' );

			$output .= '<div class="envira-gallery-position-overlay ' . $css_class . ' envira-gallery-bottom-left">';
			$output  = apply_filters( 'envira_albums_output_dynamic_position', $output, $id, $item, $data, $i, 'bottom-left' );
			$output .= '</div>';

			// Bottom Right box.
			$css_class = false; // no css classes yet.
			$css_class = apply_filters( 'envira_albums_output_dynamic_position_css', $css_class, $output, $id, $item, $data, $i, 'bottom-right' );

			$output .= '<div class="envira-gallery-position-overlay ' . $css_class . ' envira-gallery-bottom-right">';
			$output  = apply_filters( 'envira_albums_output_dynamic_position', $output, $id, $item, $data, $i, 'bottom-right' );
			$output .= '</div>';

			$create_link = true;

		if ( $create_link ) {

			$new_window                        = $link_new_window ? 'target="_blank" ' : '';
			$gallery_images_data               = envira_get_gallery_images( $id, null, null, true, true );
			$gallery_images                    = $gallery_images_data['gallery_images'];
			$sorted_ids                        = $gallery_images_data['sorted_ids'];
			$css                               = isset( $item['gallery_lightbox'] ) && 1 !== intval( $item['gallery_lightbox'] ) ? '' : 'envira-gallery-link'; // check for override (located in modal).
			$css                               = envira_albums_get_config( 'lightbox', $data ) === 0 ? '' : $css; // check for override (located in modal).
			$gallery_images_attribute          = "data-gallery-images='" . $gallery_images . "' ";
			$gallery_images_sort_ids_attribute = "data-gallery-sort-ids='" . $sorted_ids . "' ";

			if ( strpos( $gallery_images, 'cdninstagram' ) !== false ) {
				// todo: we need a better check for instagram but since album data is saved in the database without hooks, this is the best way for backwards compataiblity.
				$gallery_images_array = json_decode( $gallery_images, true );
				if ( ( empty( $gallery_images_array ) ) ) {
					$gallery_images_attribute = false;
					$css                      = false;
				} else {
					$first_element = reset( $gallery_images_array );
					if ( ( empty( $first_element ) || empty( $first_element['link'] ) ) ) {
						// checks to see if this is an instagram gallery and if there's a link in the first element (if not, likely user has selected 'no link' in the gallery settings).
						$gallery_images_attribute = false;
						$css                      = false;
					}
					$first_element = false;
				}
			}

			$output .= '<a ' . $new_window . 'href="' . get_permalink( $id ) . '" ' . $gallery_images_attribute . ' ' . $gallery_images_sort_ids_attribute . ' class="envira-album-gallery-' . $id . ' ' . $css . '" title="' . wp_strip_all_tags( htmlspecialchars_decode( $item['title'] ) ) . '" ' . apply_filters( 'envira_gallery_output_link_attr', '', $id, $item, $data, $i ) . '>';

		}

		// Image.
		$output        = apply_filters( 'envira_albums_output_before_image', $output, $id, $item, $data, $i );
		$gallery_theme = envira_albums_get_config( 'columns', $data ) === 0 ? ' envira-' . envira_albums_get_config( 'justified_gallery_theme', $data ) : '';

		// Captions (for automatic layout).
		$item_caption = false;

		// Don't assume there is one.
		if ( empty( $item['caption'] ) ) {
			$item['caption'] = ''; }

		// If the user has choosen to display Gallery Description, then it's a complete override.
		if ( isset( $data['config']['gallery_description_display'] ) && $data['config']['gallery_description_display'] && 0 === (int) $data['config']['columns'] && ! empty( $gallery_data['config']['description'] ) && isset( $item['id'] ) ) {

				$item_caption = sanitize_text_field( $gallery_data['config']['description'] );

		} else {

			$caption_array = array();
			if ( envira_albums_get_config( 'display_titles', $data ) && isset( $item['title'] ) ) {
				$caption_array[] = htmlspecialchars_decode( $item['title'] );
			}
			if ( envira_albums_get_config( 'display_captions', $data ) && isset( $item['caption'] ) ) {
				$caption_array[] = esc_attr( $item['caption'] );
			}

			// Remove any empty elements.
			$caption_array = array_filter( $caption_array );

			// Seperate.
			$item_caption_seperator = apply_filters( 'envira_albums_output_seperator', ' - ', $data );
			$item_caption           = implode( $item_caption_seperator, $caption_array );

			// Add Image Count To Captions (for automatic layout).
			if ( isset( $data['config']['display_image_count'] ) && 1 === $data['config']['display_image_count'] && 0 === (int) $data['config']['columns'] ) {

				// Note: We are providing a unique filter here just for automatic layout.
				$item_caption = apply_filters( 'envira_albums_output_automatic_before_image_count', $item_caption, $id, $item, $data, $i );

				// Get count.
				if ( 'fc' !== $data['config']['type'] ) {
					$count = envira_get_gallery_image_count( str_replace( $id . '_' . $this->counter, '', $id ) );
				} elseif ( 'fc' === $data['config']['type'] ) {
					$fc    = \ Envira_Featured_Content_Shortcode::get_instance();
					$count = $fc->get_fc_data_total( $id, $data );
				}

				// Filter count label.
				$label = '(' . $count . ' ' . _n( 'Photo', 'Photos', $count, 'envira-albums' ) . ')';
				// Add a space?
				if ( strlen( $item_caption ) > 0 ) {
					$item_caption .= ' ';
				}

				$item_caption .= '<span class="envira-album-image-count">' . apply_filters( 'envira_albums_output_automatic_image_count', $label, $count ) . '</span>';

				$item_caption = apply_filters( 'envira_albums_output_automatic_after_image_count', $item_caption, $id, $item, $data, $i );

			}
		}

			// Allow HTML tags w/o issues.
			$item_caption = htmlspecialchars( $item_caption );

			// Build the image and allow filtering.
			// Update: how we build the html depends on the lazy load script.
			// Check if user has lazy loading on - if so, we add the css class.
			$envira_lazy_load = envira_albums_get_config( 'lazy_loading', $data ) === 1 ? 'envira-lazy' : '';

			// Determine/confirm the width/height of the immge.
			// $placeholder should hold it but not for instagram.
		if ( envira_albums_get_config( 'crop', $data ) ) { // the user has selected the image to be cropped.
			$output_src = $imagesrc;
		} elseif ( envira_albums_get_config( 'image_size', $data ) !== 'full' ) { // use the image being provided thanks to the user selecting a unique image size.
			$output_src = $imagesrc;
		} elseif ( ! empty( $item['src'] ) ) {
			$output_src = $item['src'];
		} elseif ( ! empty( $placeholder[0] ) ) {
			$output_src = $placeholder[0];
		} elseif ( ! empty( $item['cover_image_url'] ) ) {
			$output_src = $item['cover_image_url'];
		} else {
			$output_src = false;
		}

		if ( envira_albums_get_config( 'crop', $data ) && envira_albums_get_config( 'crop_width', $data ) ) {

			$output_width = envira_albums_get_config( 'crop_width', $data );
		} elseif ( envira_albums_get_config( 'image_size', $data ) === 'default' && envira_albums_get_config( 'crop_width', $data ) && envira_albums_get_config( 'crop_height', $data ) ) {
			$output_width = envira_albums_get_config( 'crop_width', $data );
		} elseif ( ! empty( $item['width'] ) ) {
			$output_width = $item['width'];
		} elseif ( ! empty( $placeholder[1] ) ) {
			$output_width = $placeholder[1];
		} elseif ( ! empty( $item['cover_image_url'] ) && strpos( $item['cover_image_url'], 'cdninstagram' ) !== false ) {
			// if this is an instagram image, @getimagesize might not work
			// therefore we should try to extract the size from the url itself.
			if ( strpos( $item['cover_image_url'], '150x150' ) ) {
				$output_width = '150';
			} else {
				$output_width = '150';
			}
		} else {

			$output_width = envira_albums_get_config( 'crop_width', $data ) ? envira_albums_get_config( 'crop_width', $data ) : false;

		}

		if ( envira_albums_get_config( 'crop', $data ) && envira_albums_get_config( 'crop_height', $data ) ) {
			$output_height = envira_albums_get_config( 'crop_height', $data );
		} elseif ( envira_albums_get_config( 'image_size', $data ) === 'default' && envira_albums_get_config( 'crop_width', $data ) && envira_albums_get_config( 'crop_height', $data ) ) {
			$output_height = envira_albums_get_config( 'crop_height', $data );
		} elseif ( ! empty( $placeholder[2] ) ) {
			$output_height = $placeholder[2];
		} elseif ( ! empty( $item['height'] ) ) {
			$output_height = $item['height'];
		} else {
			$output_height = envira_albums_get_config( 'justified_row_height', $data ) ? envira_albums_get_config( 'justified_row_height', $data ) : 150;
		}

		if ( intval( envira_albums_get_config( 'columns', $data ) ) === 0 ) {

			// Automatic.
			$output_item = '<img id="envira-gallery-image-' . sanitize_html_class( $id ) . '" class="envira-gallery-image envira-gallery-image-' . $i . $gallery_theme . ' ' . $envira_lazy_load . '" src="' . esc_url( $imagesrc ) . '" width="' . envira_albums_get_config( 'crop_width', $data ) . '" height="' . envira_albums_get_config( 'crop_height', $data ) . '" data-envira-width="' . $output_width . '" data-envira-height="' . $output_height . '" data-envira-src="' . esc_url( $output_src ) . '" data-caption="' . htmlentities( $item_caption ) . '" data-envira-item-id="' . $id . '" data-automatic-caption="' . $item_caption . '" data-envira-album-id="' . $data['id'] . '" data-envira-gallery-id="' . sanitize_html_class( $id ) . '" alt="' . esc_attr( $item['alt'] ) . '" title="' . wp_strip_all_tags( htmlspecialchars_decode( $item['title'] ) ) . '" ' . apply_filters( 'envira_albums_output_image_attr', '', $item['cover_image_id'], $item, $data, $i ) . ' srcset="' . ( ( $envira_lazy_load ) ? 'data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==' : esc_url( $image_src_retina ) . ' 2x' ) . '" data-safe-src="' . ( ( $envira_lazy_load ) ? 'data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==' : esc_url( $output_src ) ) . '" />';

		} else {

			// Legacy.
			$output_item = false;

			if ( $envira_lazy_load ) {

				if ( $output_height > 0 && $output_width > 0 ) {
					$padding_bottom = ( $output_height / $output_width ) * 100;
				} else {
					// this shouldn't be happening, but this avoids a debug message.
					$padding_bottom = 100;
				}
				if ( $padding_bottom > 100 ) {
					$padding_bottom = 100;
				}
				$output_item .= '<div class="envira-lazy" style="padding-bottom:' . $padding_bottom . '%;">';

			}

			$output_item .= '<img id="envira-gallery-image-' . sanitize_html_class( $id ) . '" class="envira-gallery-image envira-gallery-image-' . $i . $gallery_theme . '" data-envira-index="' . $i . '" src="' . esc_url( $output_src ) . '" width="' . envira_albums_get_config( 'crop_width', $data ) . '" height="' . envira_albums_get_config( 'crop_height', $data ) . '" data-envira-src="' . esc_url( $output_src ) . '" data-envira-album-id="' . $data['id'] . '" data-envira-gallery-id="' . sanitize_html_class( $id ) . '" data-envira-item-id="' . $id . '" data-caption="' . $item_caption . '" alt="' . esc_attr( $item['alt'] ) . '" title="' . wp_strip_all_tags( htmlspecialchars( $item['title'] ) ) . '" ' . apply_filters( 'envira_albums_output_image_attr', '', $item['cover_image_id'], $item, $data, $i ) . ' data-envira-srcset="' . esc_url( $output_src ) . ' 400w,' . esc_url( $output_src ) . ' 2x" srcset="' . ( ( $envira_lazy_load ) ? 'data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==' : esc_url( $image_src_retina ) . ' 2x' ) . '" />';

			if ( $envira_lazy_load ) {

				$output_item .= '</div>';

			}
		}

			$output_item = apply_filters( 'envira_albums_output_image', $output_item, $id, $item, $data, $i, $album );

			// Add image to output.
			$output .= $output_item;
			$output  = apply_filters( 'envira_albums_output_after_image', $output, $id, $item, $data, $i );

		if ( $create_link ) {
			$output .= '</a>';
		}
			$output = apply_filters( 'envira_albums_output_after_link', $output, $id, $item, $data, $i );

			// Display Title For Legacy.
			// Note: We added the ability to add titles ABOVE in addition to below, but we still need to honor the deprecated setting.
		if ( isset( $data['config']['display_titles'] ) && ( 1 === $data['config']['display_titles'] || 'below' === $data['config']['display_titles'] ) && 0 !== (int) $data['config']['columns'] ) {
			$output      = apply_filters( 'envira_albums_output_before_title', $output, $id, $item, $data, $i );
			$album_title = ( ! empty( $item['link_title_gallery'] ) && 1 === intval( $item['link_title_gallery'] ) ) ? '<a ' . $new_window . ' href="' . get_permalink( $id ) . '">' . htmlspecialchars_decode( $item['title'] ) . '</a>' : htmlspecialchars_decode( $item['title'] );

			$album_title = apply_filters( 'envira_albums_album_title', $album_title, $id, $item, $data, $i );

			if ( ! empty( $item['title'] ) ) {
				$output .= '<div class="envira-album-title">' . $album_title . '</div>';
			}

			$output = apply_filters( 'envira_albums_output_after_title', $output, $id, $item, $data, $i );
		}

			// Display Caption For Legacy.
		if ( isset( $data['config']['display_captions'] ) && 1 === $data['config']['display_captions'] && 0 !== (int) $data['config']['columns'] ) {
			$output        = apply_filters( 'envira_albums_output_before_caption', $output, $id, $item, $data, $i );
			$gallery_theme = envira_albums_get_config( 'gallery_theme', $data );

			// add a <br> if there's a line break.
			$item['caption'] = str_replace(
				'
	',
				'<br/>',
				( $item['caption'] )
			);

			$output .= '<div class="envira-album-caption">' . $item['caption'] . '</div>';
			$output  = apply_filters( 'envira_albums_output_after_caption', $output, $id, $item, $data, $i );
		}

			$output .= '</div>';

			// Display Gallery Description (Below).
		if ( isset( $data['config']['gallery_description_display'] ) && 'display-below' === $data['config']['gallery_description_display'] && 0 !== (int) $data['config']['columns'] && isset( $item['id'] ) ) {
			$output = apply_filters( 'envira_albums_output_before_gallery_description', $output, $id, $item, $data, $i );

			// Extract description from gallery.
			// Note that this doesn't care if the gallery is enabled to display on the gallery or not.
			$gallery_data = envira_get_gallery( $item['id'] );
			// Get description.
			if ( isset( $gallery_data['config']['description'] ) && $gallery_data['config']['description'] ) {

				$gallery_description = wp_kses( $gallery_data['config']['description'], envira_get_allowed_tags() );
				$output             .= '<div class="envira-album-gallery-description">' . apply_filters( 'envira_albums_output_gallery_description', $gallery_description, $id, $item, $data, $i ) . '</div>';
			}
			$output = apply_filters( 'envira_albums_output_before_gallery_description', $output, $id, $item, $data, $i );
		}

			// Display Image Count.
		if ( isset( $data['config']['display_image_count'] ) && 1 === $data['config']['display_image_count'] && 0 !== intval( $data['config']['columns'] ) ) {
			$output = apply_filters( 'envira_albums_output_before_image_count', $output, $id, $item, $data, $i );

			// Get count.
			if ( 'fc' !== $data['config']['type'] ) {
				$count = envira_get_gallery_image_count( $id );
			} elseif ( 'fc' === $data['config']['type'] && class_exists( 'Envira_Featured_Content_Shortcode' ) ) {
				$fc    = \ Envira_Featured_Content_Shortcode::get_instance();
				$count = $fc->get_fc_data_total( $id, $data );
			}

			// Filter count label.
			$label   = $count . ' ' . _n( 'Photo', 'Photos', $count, 'envira-albums' );
			$output .= '<div class="envira-album-image-count">' . apply_filters( 'envira_albums_output_image_count', $label, $count ) . '</div>';

			$output = apply_filters( 'envira_albums_output_after_image_count', $output, $id, $item, $data, $i );
		}

		$output .= '</div>';
		$output  = apply_filters( 'envira_albums_output_single_item', $output, $id, $item, $data, $i );

		// Append Album to the output.
		$album .= $output;

		// Filter the output.
		$album = apply_filters( 'envira_albums_output_after_item', $album, $id, $item, $data, $i );

		return $album;

	}

	/**
	 * Helper method to retrieve the proper image src attribute based on gallery settings.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $id      The image attachment ID to use.
	 * @param array $item  Gallery item data.
	 * @param array $data  The gallery data to use for retrieval.
	 * @param bool  $mobile Whether or not to retrieve the mobile image.
	 * @return string      The proper image src attribute for the image.
	 */
	public function get_image_src( $id, $item, $data, $mobile = false ) {

		// Detect if user is on a mobile device - if so, override $mobile flag which may be manually set
		// by out of date addons or plugins.
		if ( envira_albums_get_config( 'mobile', $data ) ) {
			$mobile = envira_mobile_detect()->isMobile();
		}

		// Get the full image src. If it does not return the data we need, return the image link instead.
		$image = ( isset( $item['cover_image_url'] ) ? $item['cover_image_url'] : '' );

		// Fallback to image ID.
		if ( empty( $image ) ) {
			$src   = wp_get_attachment_image_src( $id, 'full' );
			$image = ! empty( $src[0] ) ? $src[0] : false;
		}

		// Fallback to item source.
		if ( ! $image ) {
			$image = ! empty( $item['src'] ) ? $item['src'] : false;
			if ( ! $image ) {
				return apply_filters( 'envira_album_no_image_src', $id, $item, $data );
			}
		}

		// Resize or crop image
		// This is safe to call every time, as resize_image() will check if the image already exists, preventing thumbnails
		// from being generated every single time.
		$type = $mobile ? 'mobile' : 'crop'; // 'crop' is misleading here - it's the key that stores the thumbnail width + height
		$args = apply_filters(
			'envira_gallery_crop_image_args',
			array(
				'position' => 'c',
				'width'    => envira_albums_get_config( $type . '_width', $data ),
				'height'   => envira_albums_get_config( $type . '_height', $data ),
				'quality'  => 100,
				'retina'   => false,
			)
		);

		$resized_image = \ Envira_Gallery_Common::get_instance()->resize_image( $image, $args['width'], $args['height'], envira_albums_get_config( 'crop', $data ), $args['position'], $args['quality'], $args['retina'], $data );

		// If there is an error, possibly output error message and return the default image src.
		if ( is_wp_error( $resized_image ) ) {
			// If debugging is defined, print out the error.
			if ( defined( 'ENVIRA_GALLERY_CROP_DEBUG' ) && ENVIRA_GALLERY_CROP_DEBUG ) {
				echo '<pre>' . var_export( $resized_image->get_error_message(), true ) . '</pre>';
			}

			// Return the non-cropped image as a fallback.
			return apply_filters( 'envira_gallery_image_src', $image, $id, $item, $data );
		} else {
			return apply_filters( 'envira_gallery_image_src', $resized_image, $id, $item, $data );
		}
	}

	/**
	 * Helper method for adding custom gallery classes.
	 *
	 * @since 1.0.4
	 *
	 * @param array $item Array of item data.
	 * @param int   $i      The current position in the gallery.
	 * @param array $data The gallery data to use for retrieval.
	 * @return string     String of space separated gallery item classes.
	 */
	public function get_gallery_item_classes( $item, $i, $data ) {

		// Set default class.
		$classes   = array();
		$classes[] = 'envira-gallery-item';
		$classes[] = 'enviratope-item';
		$classes[] = 'envira-gallery-item-' . $i;

		// Allow filtering of classes and then return what's left.
		$classes = apply_filters( 'envira_albums_output_item_classes', $classes, $item, $i, $data );
		return trim( implode( ' ', array_map( 'trim', array_map( 'sanitize_html_class', array_unique( $classes ) ) ) ) );

	}

	/**
	 * Creates the shortcode for the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @global object $post The current post object.
	 *
	 * @param array $atts Array of shortcode attributes.
	 * @return string     The gallery output.
	 */
	public function shortcode( $atts ) {

		global $post, $wp_current_filter;

		$album_id = false;

		// Don't do anything for excerpts (this helps prevent issues with third-party plugins ).
		if ( in_array( 'get_the_excerpt', (array) $wp_current_filter ) ) {
			return false;
		}

		if ( isset( $atts['id'] ) ) {

			$album_id = (int) $atts['id'];
			$data     = is_preview() ? _envira_get_album( $album_id ) : envira_get_album( $album_id );

		} elseif ( isset( $atts['slug'] ) ) {

			$album_id = $atts['slug'];
			$data     = is_preview() ? _envira_get_album_by_slug( $album_id ) : envira_get_album_by_slug( $album_id );

		} else {

			// A custom attribute must have been passed. Allow it to be filtered to grab data from a custom source.
			$data = apply_filters( 'envira_albums_custom_gallery_data', false, $atts, $post );

		}

		if ( empty( $data['id'] ) ) {

			return;

		}

		$this->album_data = $data;

		// This filter detects if something needs to be displayed BEFORE a gallery is displayed, such as a password form.
		$pre_album_html = apply_filters( 'envira_abort_album_output', false, $data, $album_id, $atts );

		if ( $pre_album_html !== false ) {

			// If there is HTML, then we stop trying to display the gallery and return THAT HTML.
			return apply_filters( 'envira_gallery_output', $pre_album_html, $data );

		}

		// Lets check if this gallery has already been output on the page.
		$this->album_data['album_id'] = $this->album_data['id'];

		if ( ! empty( $atts['counter'] ) ) {
			// we are forcing a counter so lets force the object in the album_ids.
			$this->counter     = $atts['counter'];
			$this->album_ids[] = $this->album_data['id'];
		}

		if ( ! in_array( $this->album_data['id'], $this->album_ids ) ) {
			$this->album_ids[] = $this->album_data['id'];
		} elseif ( $this->counter > 1 ) {
			$this->album_data['id'] = $this->album_data['id'] . '_' . $this->counter;
		}

		if ( empty( $atts['presorted'] ) ) {
			$this->album_sort[ $this->album_data['id'] ] = false; // reset this to false, otherwise multiple galleries on the same page might get other ids, or other wackinesses.
		}

		// If this is a dynamic gallery and there are no gallery IDs and the user is requesting "all", then let's grab all eligable ones.
		if ( ( ! isset( $this->album_data['galleryIDs'] ) || empty( $this->album_data['galleryIDs'] ) && $this->album_data['galleries'] != 'all' && $this->album_data['type'] == 'dynamic' ) ) {

			if ( class_exists( 'Envira_Dynamic_Album_Shortcode' ) ) {
				$galleries = \ Envira_Dynamic_Album_Shortcode::get_instance()->get_galleries( $this->album_data, $this->album_data['id'], $this->album_data, null );
			} else {
				// bail if dynamic isnt installed.
				return;
			}

			$this->album_data['galleryIDs'] = $galleries['galleryIDs'];
			$this->album_data['galleries']  = $galleries['galleries'];

		}

		if ( ! empty( $this->album_data['galleryIDs'] ) ) {
			foreach ( $this->album_data['galleryIDs'] as $key => $id ) {

				// Lets check if this gallery has already been output on the page.
				if ( ! in_array( $id, $this->album_item_ids ) ) {
					$this->album_item_ids[] = $id;
				} else {
					$this->album_data['galleries'][ $id . '_' . $this->counter ] = $this->album_data['galleries'][ $id ];
					unset( $this->album_data['galleries'][ $id ] );

					$id                               = $id . '_' . $this->counter;
					$this->album_data['galleryIDs'][] = $id;
					unset( $this->album_data['galleryIDs'][ $key ] );

				}
			}
		}

		// Store the unfiltered Album in the class array
		// This can be used in the Lightbox later on to build the Galleries and Images to display.
		$this->unfiltered_albums[ $this->album_data['id'] ] = $this->album_data;

		// Change the album order, if specified.
		$this->album_data = $this->maybe_sort_album( $this->album_data, $album_id );

		// Allow the data to be filtered before it is stored and used to create the album output.
		$this->album_data = apply_filters( 'envira_albums_pre_data', $this->album_data, $album_id );

		// If there is no data to output or the gallery is inactive, do nothing.
		if ( ! $this->album_data || empty( $this->album_data['galleryIDs'] ) ) {
			return;
		}

		// Get rid of any external plugins trying to jack up our stuff where a gallery is present.
		$this->plugin_humility();

		// Prepare variables.
		$this->index[ $this->album_data['id'] ] = array();
		$album                                  = '';
		$i                                      = 1;
		$this->album_markup                     = '';

		// If this is a feed view, customize the output and return early.
		if ( is_feed() ) {
			return $this->do_feed_output( $this->album_data );
		}

		// Load scripts and styles.
		wp_enqueue_style( ENVIRA_SLUG . '-style' );

		wp_enqueue_style( ENVIRA_SLUG . '-jgallery' );
		wp_enqueue_style( ENVIRA_ALBUMS_SLUG . '-style' );

		wp_enqueue_script( ENVIRA_SLUG . '-script' );
		wp_enqueue_script( ENVIRA_ALBUMS_SLUG . '-script' );

		wp_localize_script(
			ENVIRA_SLUG . '-script',
			'envira_gallery',
			array(
				'debug'      => ( defined( 'ENVIRA_DEBUG' ) && ENVIRA_DEBUG ? true : false ),
				'll_delay'   => isset( $this->album_data['config']['lazy_loading_delay'] ) ? intval( $this->album_data['config']['lazy_loading_delay'] ) : 500,
				'll_initial' => 'false',
				'll'         => envira_albums_get_config( 'lazy_loading', $data ) == 1 ? 'true' : 'false',
				'mobile'     => $this->is_mobile,

			)
		);

		// Load custom gallery themes if necessary.
		if ( 'base' !== envira_albums_get_config( 'gallery_theme', $this->album_data ) ) {
			envira_load_gallery_theme( envira_albums_get_config( 'gallery_theme', $this->album_data ) );
		}

		// Load custom lightbox themes if necessary, don't load if user hasn't enabled lightbox.
		if ( envira_albums_get_config( 'lightbox', $this->album_data ) ) {

			envira_load_lightbox_theme( envira_albums_get_config( 'lightbox_theme', $this->album_data ) );
		}

		// Run a hook before the gallery output begins but after scripts and inits have been set.
		do_action( 'envira_albums_before_output', $this->album_data );

		$markup = apply_filters( 'envira_albums_get_transient_markup', get_transient( '_eg_fragment_albums_' . $this->album_data['album_id'] ), $this->album_data );

		if ( $markup && ( ! defined( 'ENVIRA_DEBUG' ) || ! ENVIRA_DEBUG ) ) {

			$this->album_markup = $markup;

		} else {

			// Apply a filter before starting the gallery HTML.
			$this->album_markup = apply_filters( 'envira_gallery_output_start', $this->album_markup, $this->album_data );

			// Build out the album HTML.
			$this->album_markup    .= '<div id="envira-gallery-wrap-' . sanitize_html_class( $this->album_data['id'] ) . '" class="envira-album-wrap ' . $this->get_album_classes( $this->album_data ) . '" ' . $this->get_custom_width( $this->album_data ) . '>';
			$this->album_markup = apply_filters( 'envira_albums_output_before_container', $this->album_markup, $this->album_data );

			// Description.
			if ( isset( $this->album_data['config']['description_position'] ) && $this->album_data['config']['description_position'] == 'above' ) {
				$this->album_markup = $this->description( $this->album_markup, $this->album_data );
			}

			// add justified CSS?
			$extra_css = 'envira-gallery-justified-public';
			if ( envira_albums_get_config( 'columns', $this->album_data ) > 0 ) {
				$extra_css = false;
			}

			// add a CSS class for lazy-loading.
			$extra_css .= envira_albums_get_config( 'lazy_loading', $data ) == 1 ? ' envira-lazy ' : ' envira-no-lazy ';
			$album_config          = "data-album-config='" . envira_get_album_config( $this->album_data['album_id'] ) . "'";
			$album_lightbox_config = " data-lightbox-theme='" . envira_album_load_lightbox_config( $this->album_data['album_id'] ) . "'";
			$album_galleries_json  = " data-album-galleries='" . envira_get_album_galleries( $this->album_data['album_id'] ) . "'";
			$this->album_markup   .= '<div ' . $album_config . $album_lightbox_config . $album_galleries_json . ' id="envira-gallery-' . sanitize_html_class( $this->album_data['id'] ) . '" class="envira-album-public ' . $extra_css . ' envira-gallery-' . sanitize_html_class( envira_albums_get_config( 'columns', $this->album_data ) ) . '-columns envira-clear' . ( envira_albums_get_config( 'isotope', $this->album_data ) && envira_albums_get_config( 'columns', $this->album_data ) > 0 ? ' enviratope' : '' ) . '" data-envira-columns="' . envira_albums_get_config( 'columns', $this->album_data ) . '">';

			foreach ( $this->album_data['galleryIDs'] as $key => $id ) {

				// Skip gallery if its not published.
				if ( get_post_status( $id ) !== 'publish' ) {
							continue;
				}

							// Add the album item to the markup.
							$this->album_markup = $this->generate_album_item_markup( $this->album_markup, $this->album_data, $id, $i );

							// Increment the iterator.
							$i++;

			}

			$this->album_markup .= '</div>';

			// Description.
			if ( isset( $this->album_data['config']['description_position'] ) && $this->album_data['config']['description_position'] == 'below' ) {
				$this->album_markup = $this->description( $this->album_markup, $this->album_data );
			}

			$this->album_markup = apply_filters( 'envira_albums_output_after_container', $this->album_markup, $this->album_data );
			$this->album_markup    .= '</div>';
			$this->album_markup     = apply_filters( 'envira_albums_output_end', $this->album_markup, $this->album_data );

			// Increment the counter.
			$this->counter++;

			// Add no JS fallback support.
			$no_js = $this->get_indexable_images( $this->album_data['id'] );
			if ( $no_js ) {
				$no_js = '<noscript>' . $no_js . '</noscript>';
			}
			$this->album_markup .= $no_js;
			$transient            = set_transient( '_eg_fragment_albums_' . $this->album_data['album_id'], $this->album_markup, DAY_IN_SECONDS );

		}

		$this->data[ $this->album_data['id'] ] = $this->album_data;

		// Return the album HTML.
		return apply_filters( 'envira_albums_output', $this->album_markup, $this->album_data );

	}


	/**
	 * Maybe sort the album galleries, if specified in the config
	 *
	 * @since 1.2.4.4
	 *
	 * @param   array $data       Album Config
	 * @param   int   $gallery_id Album ID
	 * @return  array               Album Config
	 */
	public function maybe_sort_album( $data, $album_id ) {

		if ( isset( $data['galleries'] ) && ! is_array( $data['galleries'] ) ) {
			return $data;
		}
		// Get sorting method.
		$sorting_method    = (string) envira_albums_get_config( 'sorting', $data );
		$sorting_direction = envira_albums_get_config( 'sorting_direction', $data );

		// Sort images based on method.
		switch ( $sorting_method ) {
			/**
			* Random
			*/
			case 'random':
				// Shuffle keys.
				$keys = array_keys( $data['galleries'] );
				shuffle( $keys );

				// Rebuild array in new order.
				$new = array();
				foreach ( $keys as $key ) {
					$new[ $key ] = $data['galleries'][ $key ];
				}

				// Assign back to gallery.
				$data['galleries'] = $new;
				break;

			/**
			* Gallery Metadata
			*/
			case 'title':
			case 'caption':
			case 'alt':
			case 'publish_date':
				// Get metadata.
				$keys = array();
				foreach ( $data['galleries'] as $id => $item ) {
					// If no title or publish date is specified, get it now.
					// The image's title / publish date are populated on an Album save, but if the user upgraded.
					// to the latest version of this Addon and hasn't saved their Album, this data might not be available yet.
					if ( ! isset( $item[ $sorting_method ] ) || empty( $item[ $sorting_method ] ) ) {
						if ( $sorting_method == 'title' ) {
							$item[ $sorting_method ] = get_the_title( $id );
						}
						if ( $sorting_method == 'publish_date' ) {
							$item[ $sorting_method ] = get_the_date( 'Y-m-d', $id );
						}
					}

					// Sort.
					$keys[ $id ] = strip_tags( $item[ $sorting_method ] );
				}

				// Sort titles / captions.
				if ( $sorting_direction == 'ASC' ) {
					asort( $keys );
				} else {
					arsort( $keys );
				}

				// Iterate through sorted items, rebuilding gallery.
				$new = array();
				foreach ( $keys as $key => $title ) {
					$new[ $key ] = $data['galleries'][ $key ];
				}

				// Assign back to gallery.
				$data['galleries'] = $new;
				break;

			/**
			* None
			* - Do nothing
			*/
			case '0':
			case '':
				break;

			/**
			* If developers have added their own sort options, let them run them here.
			*/
			default:
				$data = apply_filters( 'envira_albums_sort_album', $data, $sorting_method, $album_id );
				break;

		}

		// Rebuild the galleryIDs array so it matches the new sort order.
		$data['galleryIDs'] = array();

		foreach ( $data['galleries'] as $gallery_id => $gallery ) {
			$data['galleryIDs'][] = $gallery_id;
		}

		return $data;

	}

	/**
	 * I'm sure some plugins mean well, but they go a bit too far trying to reduce
	 * conflicts without thinking of the consequences.
	 *
	 * 1. Prevents Foobox from completely borking envirabox as if Foobox rules the world.
	 *
	 * @since 1.0.0
	 */
	public function plugin_humility() {

		if ( class_exists( 'fooboxV2' ) ) {
			remove_action( 'wp_footer', array( $GLOBALS['foobox'], 'disable_other_lightboxes' ), 200 );
		}

	}

	/**
	 * Helper method for adding custom album classes.
	 *
	 * @since 1.1.1
	 *
	 * @param array $data The album data to use for retrieval.
	 * @return string     String of space separated album classes.
	 */
	public function get_album_classes( $data ) {

		// Set default class.
		$classes   = array();
		$classes[] = 'envira-gallery-wrap';

		// Add custom class based on data provided.
		$classes[] = 'envira-gallery-theme-' . envira_albums_get_config( 'gallery_theme', $data );
		$classes[] = 'envira-lightbox-theme-' . envira_albums_get_config( 'lightbox_theme', $data );

		// If we have custom classes defined for this gallery, output them now.
		foreach ( (array) envira_albums_get_config( 'classes', $data ) as $class ) {
			$classes[] = $class;
		}

		// If the gallery has RTL support, add a class for it.
		if ( envira_albums_get_config( 'rtl', $data ) ) {
			$classes[] = 'envira-gallery-rtl';
		}

		// If the user has selected an alignment for this gallery, add a class for it.
		if ( envira_albums_get_config( 'album_alignment', $data ) ) {
			$classes[] = 'envira-gallery-align-' . envira_albums_get_config( 'album_alignment', $data );
		}

		// If the user has overrided the default width, add a class for it.
		if ( envira_albums_get_config( 'album_width', $data ) && envira_albums_get_config( 'album_width', $data ) != 100 ) {
			$classes[] = 'envira-gallery-width-' . envira_albums_get_config( 'album_width', $data );
		}

		// Allow filtering of classes and then return what's left.
		$classes = apply_filters( 'envira_albums_output_classes', $classes, $data );
		return trim( implode( ' ', array_map( 'trim', array_map( 'sanitize_html_class', array_unique( $classes ) ) ) ) );

	}

	/**
	 * Helper method for adding custom width.
	 *
	 * @since 1.1.1
	 *
	 * @param array $data The album data to use for retrieval.
	 * @return string     String of style attr.
	 */
	public function get_custom_width( $data ) {

		$html = false;

		if ( envira_albums_get_config( 'album_width', $data ) && envira_albums_get_config( 'album_width', $data ) != 100 ) {
			$html = 'style="width:' . intval( envira_albums_get_config( 'album_width', $data ) ) . '%"';
		}

		// Allow filtering of this style.
		return apply_filters( 'envira_albums_output_style', $html, $data );

	}

	/**
	 * Returns a set of indexable image links to allow SEO indexing for preloaded images.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $id       The slider ID to target.
	 * @return string $images String of indexable image HTML.
	 */
	public function get_indexable_images( $id ) {

		// If there are no images, don't do anything.
		$images = '';
		$i      = 1;
		if ( empty( $this->index[ $id ] ) ) {
			return $images;
		}

		foreach ( (array) $this->index[ $id ] as $attach_id => $data ) {
			$images .= '<img src="' . esc_url( $data['src'] ) . '" alt="' . esc_attr( $data['alt'] ) . '" />';
			$i++;
		}

		return apply_filters( 'envira_gallery_indexable_images', $images, $this->index, $id );

	}


	/**
	 * get_instance function.
	 *
	 * __Depricated since 1.7.0.
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	public static function get_instance() {

		if ( ! isset( self::$_instance ) && ! ( self::$_instance instanceof Envira_Albums_Metaboxes ) ) {

			self::$_instance = new self();
		}

		return self::$_instance;

	}
}
