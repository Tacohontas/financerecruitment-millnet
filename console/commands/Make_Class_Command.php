<?php

namespace GPlate\Commands;

use GPlate\Console;
use GPlate\Utils\Strings;

if ( ! defined( 'GPLATE_CONSOLE' ) ) exit; // Exit if accessed directly

class Make_Class_Command extends Make_Command {

	/**
	 * @var string $signature
	 */
	protected $signature = 'make:class';

	/**
	 * @var string $description
	 */
	protected $description = 'Create basic class';

	/**
	 * Handle this command
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public function handle( $args = [] ) {
		if ( count( $args ) < 1 ) {
			Console::error( 'Too few arguments.' );
			return;
		}

		foreach ( $args as $arg ) {
			$class_full_name = $arg;

			$class_parts = array_map( 'GPlate\\Utils\\Strings::label_to_class', explode( '/', $class_full_name ) );

			$folders = [];

			$namespace = Console::get_plugin_namespace();

			if ( count( $class_parts ) > 1 ) {
				$namespace_parts = array_slice( $class_parts, 0, count( $class_parts ) - 1 );

				$folders = array_map(
					function ( $folder ) {
						return str_replace( '_', '-', strtolower( $folder ) );
					},
					$namespace_parts
				);

				$namespace .= '\\' . implode( '\\', $namespace_parts );
			}

			$rel_folder_path = rtrim( '/inc/' . implode( '/', $folders ), '/' ) . '/';

			$folder_path = GPLATE_BASE_DIR . $rel_folder_path;

			if ( ! file_exists( $folder_path ) ) {
				mkdir( $folder_path, 0775, true );
			}

			$class_name = $class_parts[ count( $class_parts ) - 1 ];

			$rel_file_path = $rel_folder_path . Strings::class_to_file_name( $class_name ) . '.php';
			$new_file_path = $folder_path . Strings::class_to_file_name( $class_name ) . '.php';

			if ( file_exists( $new_file_path ) ) {
				Console::error( sprintf( 'File "%s" already exists!', $new_file_path ) );
				continue;
			}

			$template = TEMPLATE_DIRECTORY . '/Basic.php';

			// Fetch template contents
			$this->make(
				$template,
				$new_file_path,
				[
					'class Basic'         => 'class ' . $class_name,
					'Class Basic'         => 'Class ' . $class_name,
					'__CLASS_NAME__'      => $class_name,
				]
			);

			// Include the class and load it
			$main_file = GPLATE_BASE_DIR . '/' . Console::get_plugin_name() . '.php';

			$main_file_contents = file_get_contents( $main_file ); //phpcs:ignore

			$var_name = strtolower( $class_name );

			$main_file_contents = preg_replace(
				'/init_modules\(\) \{([^}]+)\}/',
				'init_modules() {$1' . "\t" . '$this->' . $var_name . ' = new ' . $class_name . '();' . PHP_EOL .
				"\t\t" . '}',
				$main_file_contents
			);

			$main_file_contents = preg_replace(
				'/run\(\) \{([^}]+)\}/',
				'run() {$1' . "\t" . '$this->' . $var_name . '->init();' . PHP_EOL .
				"\t\t" . '}',
				$main_file_contents
			);

			// Append declaration on main class
			$replace = '$1'. PHP_EOL . PHP_EOL .
			"\t\t/**" . PHP_EOL .
			"\t\t * " . $var_name .' class' . PHP_EOL .
			"\t\t *" . PHP_EOL .
			"\t\t * @var " . $class_name . PHP_EOL . 
			"\t\t */" . PHP_EOL .
			"\t\tpublic $" . $var_name .';' . PHP_EOL;
			$main_file_contents = preg_replace(
				'/(private \$plugin_label;)(\s)/',
				$replace,
				$main_file_contents
			);			

			file_put_contents( $main_file, $main_file_contents ); //phpcs:ignore

			Console::success( sprintf( 'Successfully created class "%s"!', $class_name ) );
		}
	}

	public function get_template_variables() {
		$plugin_name = Console::get_plugin_name();

		return [
			'__OPTIONS_NAME__'          => $plugin_name . '-options',
			'__SETTINGS_SECTION_NAME__' => Strings::dashes_to_underscore( $plugin_name ) . '_section',
		];
	}

}
