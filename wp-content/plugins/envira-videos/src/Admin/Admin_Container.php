<?php
/**
 * Admin Container class.
 *
 * @since 1.0.0
 *
 * @package Envira_Videos
 * @author  Envira Team
 */

namespace Envira\Videos\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Envira\Videos\Admin\Ajax;
use Envira\Videos\Admin\Media_View;
use Envira\Videos\Admin\Metaboxes;
use Envira\Videos\Admin\Settings;

/**
 * Admin Container class.
 *
 * @since 1.0.0
 *
 * @package Envira_Videos
 * @author  Envira Team
 */
class Admin_Container {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		$ajax       = new Ajax();
		$metabox    = new Metaboxes();
		$settings   = new Settings();
		$media_view = new Media_View();

	}

}
