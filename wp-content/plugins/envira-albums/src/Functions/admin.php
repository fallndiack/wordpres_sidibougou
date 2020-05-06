<?php
/**
 * Albums Admin Functions
 *
 * @since 1.6.0
 *
 * @package Envira Gallery
 * @subpackage Envira Albums
 * @author Envira Gallery Team <support@enviragallery.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loads a partial view for the Administration screen
 *
 * @since 1.3.0
 *
 * @param   string $template   PHP file at includes/admin/partials, excluding file extension.
 * @param   array  $data       Any data to pass to the view.
 * @return  bool
 */
function envira_album_load_admin_partial( $template, $data = array() ) {

	$dir = trailingslashit( plugin_dir_path( ENVIRA_ALBUMS_FILE ) . 'src/Views/admin/' );

	if ( file_exists( $dir . $template . '.php' ) ) {
		require_once $dir . $template . '.php';
		return true;
	}

	return false;

}
