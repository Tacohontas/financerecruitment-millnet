<?php

namespace GPlate;

use GPlate\Utils\Console_Color;
use GPlate\Utils\Strings;

if ( ! defined( 'GPLATE_CONSOLE' ) ) exit; // Exit if accessed directly

class Console {

	private static $plugin_name;

	private $command_manager;

	/**
	 * Run the console
	 *
	 * @param string $plugin_namespace
	 * @param array $args
	 *
	 * @return void
	 */
	public function run( $plugin_name, $args ) {
		// Check if this is ran on the actual boilerplate project or not
		// if ( $plugin_name === 'gen-plugin-boilerplate' ) {
		// 	self::error( 'Wait! You are on the boilerplate project, please create a project first. Moron.' );
		// 	return;
		// }

		self::$plugin_name = $plugin_name;

		define( 'TEMPLATE_DIRECTORY', __DIR__ . '/templates' );

		$this->load_dependencies();

		$this->setup_command_manager();

		$this->process_command( $args );
	}

	/**
	 * Load dependences
	 *
	 * @return void
	 */
	public function load_dependencies() {
		require_once __DIR__ . '/Command_Manager.php';

		// Utils
		require_once __DIR__ . '/utils/Strings.php';
		require_once __DIR__ . '/utils/Console_Color.php';
	}

	/**
	 * Setup the command manager
	 *
	 * @param string $plugin_namespace
	 *
	 * @return void
	 */
	public function setup_command_manager() {
		$this->command_manager = new Command_Manager();
		$this->command_manager->load();
	}

	/**
	 * Process a command
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public function process_command( $args ) {
		$this->command_manager->handle_command( array_slice( $args, 1 ) );
	}


	/**
	 * Display an error message
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public static function error( $message ) {
		print( Console_Color::LIGHT_RED . $message . PHP_EOL . Console_Color::RESET );
	}

	/**
	 * Display a info message
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public static function info( $message ) {
		print( Console_Color::LIGHT_CYAN . $message . PHP_EOL . Console_Color::RESET );
	}

	/**
	 * Display a success message
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public static function success( $message ) {
		print( Console_Color::LIGHT_GREEN . $message . PHP_EOL . Console_Color::RESET );
	}

	/**
	 * Print one line
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public static function line( $message ) {
		print( $message . PHP_EOL . Console_Color::RESET );
	}

	/**
	 * Get the name for this plugin
	 *
	 * @return string Plugin name
	 */
	public static function get_plugin_name() {
		return self::$plugin_name;
	}

	/**
	 * Get the namespace for this plugin
	 *
	 * @return string Plugin namespace
	 */
	public static function get_plugin_namespace() {
		return Strings::name_to_namespace( self::$plugin_name );
	}

}
