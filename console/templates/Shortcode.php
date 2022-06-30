<?php

// PLUGIN_NAMESPACE\Shortcodes;

// USE_PLUGIN_NAMESPACE\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class __CLASS_NAME__
 */
class Shortcode {
	use Singleton;

	/**
	 * Init function
	 *
	 * @return void
	 */
	public function init() {
		add_shortcode( '__SHORTCODE_NAME__', [ $this, 'handle___SHORTCODE_NAME__' ] );
	}

	/**
	 * Shortcode function
	 *
	 * @param array $atts
	 *
	 * @return void
	 */
	public function handle___SHORTCODE_NAME__( $atts ) {
		$atts = shortcode_atts(
			[
				'example_link'   => '#',
                'example_id'     => 'exampleId',
                'example_color'  => 'blue',
                'example_label'  => 'Button',
			],
			$atts
		);

		ob_start();
		
		return ob_get_clean();
	}
}
