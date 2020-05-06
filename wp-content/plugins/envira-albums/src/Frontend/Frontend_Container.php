<?php
/**
 * Envira Albums Frontend Container.
 *
 * @since 1.6.0
 *
 * @package Envira Gallery
 * @subpackage Envira Albums
 * @author Envira Gallery Team <support@enviragallery.com>
 */

namespace Envira\Albums\Frontend;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Envira\Albums\Frontend\Shortcode;
use Envira\Albums\Frontend\Posttype;
use Envira\Albums\Frontend\Standalone;
use Envira\Albums\Widget;
use Envira\Albums\Utils\Ajax;

/**
 * Frontend Container Class.
 *
 * @since 1.6.0
 */
class Frontend_Container {

	/**
	 * Class Constructor.
	 *
	 * @since 1.6.0
	 */
	public function __construct() {

		$posttype   = new Posttype();
		$shortcode  = new Shortcode();
		$standalone = new Standalone();
		$ajax       = new Ajax();

		// Load the plugin widget.
		add_action( 'widgets_init', array( $this, 'widget' ) );

	}

	/**
	 * Registers the Envira Gallery widget.
	 *
	 * @since 1.7.0
	 */
	public function widget() {

		register_widget( 'Envira\Albums\Widgets\Widget' );

	}

}
