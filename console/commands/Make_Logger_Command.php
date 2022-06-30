<?php

namespace GPlate\Commands;

use GPlate\Console;
use GPlate\Utils\Strings;

if ( ! defined( 'GPLATE_CONSOLE' ) ) exit; // Exit if accessed directly

class Make_Logger_Command extends Make_Command {

	/**
	 * @var string $signature
	 */
	protected $signature = 'make:logger';

	/**
	 * @var string $description
	 */
	protected $description = 'Scaffold logger class';

	/**
	 * Handle this command
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public function handle( $args = [] ) {
		$new_file_path = GPLATE_BASE_DIR . '/inc/Logger.php';

		if ( file_exists( $new_file_path ) ) {
			Console::error( 'Logger class is already created!' );
			return;
		}

		$template = TEMPLATE_DIRECTORY . '/Logger.php';

		// Fetch template contents
		$this->make( $template, $new_file_path );

		// Include the class and load it
		$main_file = GPLATE_BASE_DIR . '/' . Console::get_plugin_name() . '.php';

		$main_file_contents = file_get_contents( $main_file );

		$main_file_contents = preg_replace(
			'/init_modules\(\) \{([^}]+)\}/',
			'init_modules() {$1' . "\t" . '$this->logger = Logger::get_instance();' . PHP_EOL .
			"\t\t" . '}',
			$main_file_contents
		);

		// Append declaration on main class
		$replace = '$1'. PHP_EOL . PHP_EOL .
		"\t\t/**" . PHP_EOL .
		"\t\t * Logger class" . PHP_EOL .
		"\t\t *" . PHP_EOL .
		"\t\t * @var Logger"  . PHP_EOL . 
		"\t\t */" . PHP_EOL .
		"\t\tpublic \$logger;" . PHP_EOL;
		$main_file_contents = preg_replace(
			'/(private \$plugin_label;)(\s)/',
			$replace,
			$main_file_contents
		);

		file_put_contents( $main_file, $main_file_contents );

		Console::success( 'Successfully setup logger!' );
	}

	public function get_template_variables() {
		return [
			'__ERROR_EMAIL__' => '2ndline@thegeneration.se'
		];
	}

}
