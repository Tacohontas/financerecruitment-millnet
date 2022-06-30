<?php

// PLUGIN_NAMESPACE;

// USE_PLUGIN_NAMESPACE\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Module_Manager
 */
class Module_Manager {
	use Singleton;
	
	/**
	 * Init function
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'generation_theme_modules', [ $this, 'theme_modules' ], 20, 1 );
	}

	/**
	 * Add modules to the theme
	 *
	 * @param array $modules Modules being filtered
	 *
	 * @return array
	 */
	public function theme_modules( $modules ) {
		return array_merge(
			$modules,
			[]
		);
	}

}
