<?php

namespace GPlate\Commands;

use GPlate\Console;
use GPlate\Utils\Strings;

if ( ! defined( 'GPLATE_CONSOLE' ) ) exit; // Exit if accessed directly

class Make_Options_Command extends Make_Command {

	/**
	 * @var string $signature
	 */
	protected $signature = 'make:options';

	/**
	 * @var string $description
	 */
	protected $description = 'Scaffold options page';

	/**
	 * Handle this command
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public function handle( $args = [] ) {
		$new_file_path = GPLATE_BASE_DIR . '/inc/Options.php';

		if ( file_exists( $new_file_path ) ) {
			Console::error( 'Options are already created!' );
			return;
		}

		$template = TEMPLATE_DIRECTORY . '/Options.php';

		// Fetch template contents
		$this->make( $template, $new_file_path );

		// Include the class and load it
		$main_file = GPLATE_BASE_DIR . '/' . Console::get_plugin_name() . '.php';

		$main_file_contents = file_get_contents( $main_file );

		$main_file_contents = preg_replace(
			'/init_modules\(\) \{([^}]+)\}/',
			'init_modules() {$1' . "\t" . '$this->options = Options::get_instance();' . PHP_EOL .
			"\t\t" . '}',
			$main_file_contents
		);

		$main_file_contents = preg_replace(
			'/run\(\) \{([^}]+)\}/',
			'run() {$1' . "\t" . '$this->options->init();' . PHP_EOL .
			"\t\t" . '}',
			$main_file_contents
		);

		// Append declaration on main class
		$replace = '$1'. PHP_EOL . PHP_EOL .
		"\t\t/**" . PHP_EOL .
		"\t\t * Options class" . PHP_EOL .
		"\t\t *" . PHP_EOL .
		"\t\t * @var Options"  . PHP_EOL . 
		"\t\t */" . PHP_EOL .
		"\t\tpublic \$options;" . PHP_EOL;
		$main_file_contents = preg_replace(
			'/(private \$plugin_label;)(\s)/',
			$replace,
			$main_file_contents
		);

		file_put_contents( $main_file, $main_file_contents );

		Console::success( 'Successfully setup options!' );
	}

	public function get_template_variables() {
		$plugin_name = Console::get_plugin_name();

		return [
			'__PLUGIN_NAME__'			=> $plugin_name,
			'__OPTIONS_NAME__'          => $plugin_name . '-options',
			'__SETTINGS_SECTION_NAME__' => Strings::dashes_to_underscore( $plugin_name ) . '_section',
		];
	}

}
