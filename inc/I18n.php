<?php

namespace Financerecruitment_Millnet;

use Financerecruitment_Millnet\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class I18n
 */
class I18n {
	use Singleton;

	/**
	 * Init function
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', [ $this, 'load_language_files' ], 1 );
	}

	/**
	 * Loads the language-files to be used throughout the plugin
	 *
	 * @return void
	 */
	public function load_language_files() {
		load_plugin_textdomain( 'financerecruitment-millnet', false, plugin_basename( FINANCERECRUITMENT_MILLNET_DIR ) . '/languages' );
	}

}
