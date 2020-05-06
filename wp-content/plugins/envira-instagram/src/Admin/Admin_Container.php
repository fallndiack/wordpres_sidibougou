<?php
/**
 * Admin_Container
 *
 * @since 1.0.0
 *
 * @package Envira_Instagram
 * @author  Envira Team
 */

namespace Envira\Instagram\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Envira\Instagram\Admin\Metaboxes;
use Envira\Instagram\Admin\Settings;

/**
 * Admin_Container class.
 *
 * @since 1.1.0
 *
 * @package Envira_Gallery
 * @author  Envira Gallery Team <support@enviragallery.com>
 */
class Admin_Container {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		$metabox  = new Metaboxes();
		$settings = new Settings();

	}

}
