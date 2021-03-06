<?php

namespace Financerecruitment_Millnet;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * @wordpress-plugin
 * Plugin Name: Financerecruitment Millnet Integration
 * Plugin URI: https://thegeneration.se/
 * Description: Handles Millnet integration for Financerecruitment
 * Version: 1.0.0 
 * Author: The Generation AB
 * Author URI: https://thegeneration.se
 * Text Domain: financerecruitment-millnet
 * Domain Path: languages
 */

/**
 * Define an absolute constant to be used in the plugin files
 */
if ( ! defined( 'FINANCERECRUITMENT_MILLNET_DIR' ) ) {
	define( 'FINANCERECRUITMENT_MILLNET_DIR', __DIR__ );
}

if ( ! defined( 'FINANCERECRUITMENT_MILLNET_FILE' ) ) {
	define( 'FINANCERECRUITMENT_MILLNET_FILE', __FILE__ );
}

if ( ! class_exists( 'Financerecruitment_Millnet\\Plugin' ) ) :

	class Plugin {

		/**
		 * Name of plugin
		 */
		const PLUGIN_NAME = 'financerecruitment-millnet';

		/**
		 * Version of plugin
		 */
		const VERSION = '1.0.0';

		/**
		 * @var string
		 */
		private $plugin_description;

		/**
		 * @var string
		 */
		private $plugin_label;

		/**
		 * Translation class
		 *
		 * @var I18n
		 */
		public $i18n;

		/**
		 * Scripts class
		 *
		 * @var Scripts
		 */
		public $scripts;

		/**
		 * The single instance of the class.
		 *
		 * @var object
		 */
		protected static $instance = null;

		/**
		 * Get class instance.
		 *
		 * @return object Instance.
		 */
		final public static function get_instance() {
			if ( static::$instance === null ) {
				static::$instance = new static();
			}
			return static::$instance;
		}

		/**
		 * Financerecruitment_Millnet constructor.
		 */
		public function __construct() {
			$this->load_dependencies();
			$this->init_modules();

			$this->plugin_description = esc_html__( 'Handles Millnet integration for Financerecruitment', 'financerecruitment-millnet' );
			$this->plugin_label = esc_html__( 'Financerecruitment Millnet Integration', 'financerecruitment-millnet' );
		}

		/**
		 * Require all the classes we need
		 *
		 * @return void
		 */
		public function load_dependencies() {
			// Autoload all classes
			require_once FINANCERECRUITMENT_MILLNET_DIR . '/vendor/autoload.php';
		}

		/**
		 * Initialize plugin modules
		 *
		 * @return void
		 */
		public function init_modules() {
			$this->i18n = I18n::get_instance();
			$this->scripts = Scripts::get_instance();
		}

		/**
		 * Run init on all modules
		 *
		 * @return void
		 */
		public function run() {
			$this->i18n->init();
			$this->scripts->init();
		}
	}

	/**
	 * Wrapper for getting the plugin instance
	 *
	 * @return Plugin
	 */
	function gen() {
		return Plugin::get_instance();
	}

	gen()->run();

endif;
