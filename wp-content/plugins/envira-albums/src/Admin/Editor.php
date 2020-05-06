<?php
/**
 * Editor class.
 *
 * @since 1.0.0
 *
 * @package Envira_Albums
 * @author  Envira Team
 */

namespace Envira\Albums\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WordPress Editor Class
 *
 * @since 2.0.0
 */
class Editor {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Add a custom media button to the editor.
		add_filter( 'media_buttons_context', array( $this, 'media_button' ) );

	}

	/**
	 * Adds a custom gallery insert button beside the media uploader∏ button.
	 *
	 * @since 1.0.0
	 *
	 * @param string $buttons  The media buttons context HTML.
	 * @return string $buttons Amended media buttons context HTML.
	 */
	public function media_button( $buttons ) {

		// Create the media button.
		$button = '<a id="envira-media-modal-button" href="#" class="button envira-albums-choose-album" data-action="album" title="' . esc_attr__( 'Add Album', 'envira-albums' ) . '" >
			<span class="envira-media-icon"></span> ' . __( 'Add Album', 'envira-albums' ) . '</a>';

		// Filter the button.
		$button = apply_filters( 'envira_albums_media_button', $button, $buttons );

		// Append the button.
		return $buttons . $button;

	}

}
