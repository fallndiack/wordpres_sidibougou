<?php
/**
 * Frontend Container class.
 *
 * @since 1.0.0
 *
 * @package Envira_Videos
 * @author  Envira Team
 */

namespace Envira\Videos\Frontend;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

namespace Envira\Videos\Frontend;

use Envira\Videos\Frontend\Shortcode;

/**
 * Frontend Container class.
 *
 * @since 1.0.0
 *
 * @package Envira_Videos
 * @author  Envira Team
 */
class Frontend_Container {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$shortcode = new Shortcode();

	}

}
