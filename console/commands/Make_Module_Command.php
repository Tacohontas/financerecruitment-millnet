<?php

namespace GPlate\Commands;

use GPlate\Console;
use GPlate\Utils\Strings;

if ( ! defined( 'GPLATE_CONSOLE' ) ) exit; // Exit if accessed directly

class Make_Module_Command extends Make_Command {

	protected $template_variables;

	/**
	 * @var string $signature
	 */
	protected $signature = 'make:module';

	/**
	 * @var string $description
	 */
	protected $description = 'Creates a new module';

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

		// Prevent the creation of a model called "extends"
		if ( array_search( 'extends', $args ) !== false) {
			Console::error( 'You need to wrap the module name(s) with quotes if extending' );
			return;
		}

		// Create module manager if it doesn't exist
		$module_manager_file = GPLATE_BASE_DIR . '/inc/Module_Manager.php';

		if ( ! file_exists( $module_manager_file ) ) {
			$module_manager_template = TEMPLATE_DIRECTORY . '/Module_Manager.php';

			$this->make( $module_manager_template, $module_manager_file );

			// Include the class and load it
			$main_file = GPLATE_BASE_DIR . '/' . Console::get_plugin_name() . '.php';

			$main_file_contents = file_get_contents( $main_file );

			$main_file_contents = preg_replace(
				'/init_modules\(\) \{([^}]+)\}/',
				'init_modules() {$1' . "\t" . '$this->module_manager = Module_Manager::get_instance();' . PHP_EOL .
				"\t\t" . '}',
				$main_file_contents
			);

			$main_file_contents = preg_replace(
				'/run\(\) \{([^}]+)\}/',
				'run() {$1' . "\t" . '$this->module_manager->init();' . PHP_EOL .
				"\t\t" . '}',
				$main_file_contents
			);

			// Append declaration on main class
			$replace = '$1'. PHP_EOL . PHP_EOL .
			"\t\t/**" . PHP_EOL .
			"\t\t * Module manager class" . PHP_EOL .
			"\t\t *" . PHP_EOL .
			"\t\t * @var Module_Manager"  . PHP_EOL . 
			"\t\t */" . PHP_EOL .
			"\t\tpublic \$module_manager;" . PHP_EOL;
			$main_file_contents = preg_replace(
				'/(private \$plugin_label;)(\s)/',
				$replace,
				$main_file_contents
			);

			file_put_contents( $main_file, $main_file_contents );
		}

		foreach ( $args as $arg ) {
			$extends = '';
			
			if ( strpos( $arg, ' extends ' ) ) {
				$parts = explode( ' extends ', $arg);
				$arg = $parts[0];
				$extends = $parts[1];
			}
			
			$module_name = ucwords( str_replace( '-', ' ', $arg ) );

			$module_class = Strings::label_to_class( $module_name );

			$module_id = Strings::dashes_to_underscore( Strings::label_to_slug( $module_name ) );

			$template_file = $extends !== '' ? '/Extending_Module.php' : '/Module.php'; // Pick extended module file instead if the module should extend.
			$template = TEMPLATE_DIRECTORY . $template_file; 

			$template_variables = [
				'class Module'    => 'class ' . $module_class,
				'__CLASS_NAME__'  => $module_class,
				'__MODULE_NAME__' => $module_name,
				'__MODULE_ID__'   => $module_id,
			];

			if ( $extends !== '' ) {
				$template_variables['Generation_Theme_Module'] = $extends;
			}

			$new_file_name = Strings::class_to_file_name( $module_class ) . '.php';

			$modules_folder = GPLATE_BASE_DIR . '/inc/Modules/';

			if ( ! file_exists( $modules_folder ) ) {
				mkdir( $modules_folder, 0775 );

				$index_file_name = $modules_folder . 'index.php';

				if ( ! file_exists( $index_file_name ) ) {
					$index_template = TEMPLATE_DIRECTORY . '/index.php';
					file_put_contents( $index_file_name, file_get_contents( $index_template ) );
				}
			}

			$new_file_path = $modules_folder . $new_file_name;

			if ( file_exists( $new_file_path ) ) {
				Console::error( sprintf( 'Module "%s" already exists!', $module_name ) );
				continue;
			}

			$this->make( $template, $new_file_path, $template_variables );

			// Add the module to the module manager
			$module_manager_contents = file_get_contents( $module_manager_file );

			$module_manager_contents = preg_replace(
				'/theme_modules\(\s?\$modules\s?\)\s?\{([\S\s]+?)return array_merge\(\s*?\$modules,\s*?\[(\s*[\S\s]*?)\s*\]\s*?\);([\S\s]*?)\}/',
				'theme_modules( $modules ) {$1return array_merge(' . PHP_EOL .
				"\t\t\t" . '$modules,' . PHP_EOL .
				"\t\t\t" . '[$2' . PHP_EOL .
				"\t\t\t\t" . '\'' . $module_id . '\' => [' . PHP_EOL .
				"\t\t\t\t\t" . '\'file\' => ' . Strings::name_to_constant( Console::get_plugin_name() ) . '_DIR' . ' . \'/inc/Modules/' . $new_file_name . '\',' . PHP_EOL .
				"\t\t\t\t\t" . '\'class\' => \'' . Console::get_plugin_namespace() . '\\\\\\Modules\\\\\\' . $module_class . '\',' . PHP_EOL .
				"\t\t\t\t" . '],' . PHP_EOL .
				"\t\t\t" . ']' . PHP_EOL .
				"\t\t" . ');$3}',
				$module_manager_contents
			);

			file_put_contents( $module_manager_file, $module_manager_contents );

			$message = $module_name;

			if ( $extends ) {
				$message .= ' that extends ' . $extends;
			}

			Console::success( sprintf( 'Successfully created the module "%s"!', $message ) );
		}
	}
}
