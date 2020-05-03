<?php
namespace WPEventManagerSlidder\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Elementor Event Owl Carousel Slider
 *
 * Elementor widget for event slider.
 *
 */
class Elementor_Owl_Carousel_Slider extends Widget_Base {

	/**
	 * Retrieve the widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'event-slider';
	}

	/**
	 * Retrieve the widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Event Slider', 'wp-event-manager' );
	}
	/**	
	 * Get widget icon.
	 *
	 * Retrieve shortcode widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-post-slider';
	}
	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return [ 'event-slider', 'code' ];
	}

	/**
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * Note that currently Elementor supports only one category.
	 * When multiple categories passed, Elementor uses the first one.
	 *
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'wp-event-manager-categories' ];
	}

	/**
	 * Register the widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @access protected
	 */
	protected function _register_controls() {
		$this->start_controls_section(
			'section_shortcode',
			[
				'label' => __( 'Event Slider', 'wp-event-manager' ),
			]
		);
	
		$this->add_control(
			'featured',
			[
				'label' => __( 'Show Featured', 'wp-event-manager' ),
				'type' => Controls_Manager::SELECT,
				'default' => '1',
				'options' => [
					'0' => __( 'False', 'wp-event-manager' ),
					'1' => __( 'True', 'wp-event-manager' ),
				],
			]
		);

		$this->add_control(
			'limit',
			[
				'label'       => __( 'Limit', 'wp-event-manager' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => '5',
			]
		);

		$this->add_control(
			'orderby',
			[
				'label' => __( 'Order By', 'wp-event-manager' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'modified',
				'options' => [
					'title' => __( 'Title', 'wp-event-manager' ),
					'ID' => __( 'ID', 'wp-event-manager' ),
					'name' => __( 'Name', 'wp-event-manager' ),
					'modified' => __( 'Modified', 'wp-event-manager' ),
					'parent' => __( 'Parent', 'wp-event-manager' ),
					'modified' => __( 'Modified', 'wp-event-manager' ),
					'rand' => __( 'Rand', 'wp-event-manager' ),
				],
			]
		);

		$this->add_control(
			'navigation',
			[
				'label' => __( 'Navigation', 'wp-event-manager' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'true',
				'options' => [
					'false' => __( 'False', 'wp-event-manager' ),
					'true' => __( 'True', 'wp-event-manager' ),
				],
			]
		);

		$this->add_control(
			'slide_speed',
			[
				'label'       => __( 'Slide Speed', 'wp-event-manager' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => '300',
			]
		);

		
		$this->add_control(
			'single_item',
			[
				'label' => __( 'Single Item', 'wp-event-manager' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'true',
				'options' => [
					'false' => __( 'False', 'wp-event-manager' ),
					'true' => __( 'True', 'wp-event-manager' ),
				],
			]
		);

		$this->add_control(
			'lazy_load',
			[
				'label' => __( 'Lazy Load', 'wp-event-manager' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'true',
				'options' => [
					'false' => __( 'False', 'wp-event-manager' ),
					'true' => __( 'True', 'wp-event-manager' ),
				],
			]
		);

		$this->add_control(
			'auto_play',
			[
				'label'       => __( 'Auto Pay', 'wp-event-manager' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => '7000',
			]
		);

		$this->end_controls_section();

	}

	/**
	 * Render the widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		if($settings['limit']>0)
			$limit = 'limit='.$settings['limit'];
		else
			$limit = 'limit=300';
			
		if($settings['slide_speed']>0)
			$slide_speed = 'slide_speed='.$settings['slide_speed'];
		else
		    $slide_speed = 'slide_speed=300';
		
		if($settings['auto_play']>0)
			$auto_play = 'auto_play='.$settings['auto_play'];
		else
			$auto_play = 'auto_play=7000';
			
		echo do_shortcode('[event_owl_carousel_slider featured='.$settings['featured'].' orderby='.$settings['orderby'].' navigation='.$settings['navigation'].' single_item='.$settings['single_item'].' lazy_load='.$settings['lazy_load'].' '.$limit.' '.$slide_speed.' '.$auto_play.' ]');
	}

	/**
	 * Render the widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @access protected
	 */
	protected function _content_template() {}
}
