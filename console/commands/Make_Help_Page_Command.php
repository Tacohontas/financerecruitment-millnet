<?php

namespace GPlate\Commands;

use GPlate\Console;

if ( ! defined( 'GPLATE_CONSOLE' ) ) exit; // Exit if accessed directly

class Make_Help_Page_Command extends Make_Command {

	/**
	 * @var string $signature
	 */
	protected $signature = 'make:help-page';

	/**
	 * @var string $description
	 */
	protected $description = 'Scaffold help page';

	/**
	 * Handle this command
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public function handle( $args = [] ) {
		$new_file_path = GPLATE_BASE_DIR . '/inc/Help_Page.php';

		if ( file_exists( $new_file_path ) ) {
			Console::error( 'Help page has already been created!' );
			return;
		}

		$template = TEMPLATE_DIRECTORY . '/Help_Page.php';

		// Fetch template contents
		$this->make( $template, $new_file_path );

		// Include the class and load it
		$main_file = GPLATE_BASE_DIR . '/' . Console::get_plugin_name() . '.php';

		$backend_partials_folder = GPLATE_BASE_DIR . '/partials/backend/';

		if ( ! file_exists( $backend_partials_folder ) ) {
			mkdir( $backend_partials_folder, 0775 );

			$index_file_name = $backend_partials_folder . 'index.php';

			if ( ! file_exists( $index_file_name ) ) {
				$index_template = TEMPLATE_DIRECTORY . '/index.php';
				file_put_contents( $index_file_name, file_get_contents( $index_template ) );
			}
		}

		$partial_template = TEMPLATE_DIRECTORY . '/partials/help-page.php';

		$partial_file_path = $backend_partials_folder . '/help-page.php';

		$this->make( $partial_template, $partial_file_path );

		$main_file_contents = file_get_contents( $main_file );

		$main_file_contents = preg_replace(
			'/init_modules\(\) \{([^}]+)\}/',
			'init_modules() {$1' . "\t" . '$this->help_page = Help_Page::get_instance();' . PHP_EOL .
			"\t\t" . '}',
			$main_file_contents
		);

		$main_file_contents = preg_replace(
			'/run\(\) \{([^}]+)\}/',
			'run() {$1' . "\t" . '$this->help_page->init();' . PHP_EOL .
			"\t\t" . '}',
			$main_file_contents
		);

		// Append declaration on main class
		$replace = '$1'. PHP_EOL . PHP_EOL .
		"\t\t/**" . PHP_EOL .
		"\t\t * Help page class" . PHP_EOL .
		"\t\t *" . PHP_EOL .
		"\t\t * @var Help_Page"  . PHP_EOL .
		"\t\t */" . PHP_EOL .
		"\t\tpublic \$help_page;" . PHP_EOL;
		$main_file_contents = preg_replace(
			'/(private \$plugin_label;)(\s)/',
			$replace,
			$main_file_contents
		);

		file_put_contents( $main_file, $main_file_contents );

		Console::success( 'Successfully setup help page!' );
	}

}
