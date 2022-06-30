<?php

namespace GPlate\Commands;

use GPlate\Console;
use GPlate\Utils\Strings;

if ( ! defined( 'GPLATE_CONSOLE' ) ) exit; // Exit if accessed directly

class Make_Shortcode_Command extends Make_Command {

	protected $template_variables;

	/**
	 * @var string $signature
	 */
	protected $signature = 'make:shortcode';

	/**
	 * @var string $description
	 */
	protected $description = 'Creates a new shortcode';

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

		$shortcode_name = ucwords( str_replace( '-', ' ', $args[0] ) );

		$shortcode_class = Strings::label_to_class( $shortcode_name );

		$template = TEMPLATE_DIRECTORY . '/Shortcode.php';

		$template_variables = [
			'class Shortcode'    => 'class ' . $shortcode_class,
			'__CLASS_NAME__' 	 => $shortcode_class,
			'__SHORTCODE_NAME__' => strtolower( $shortcode_name )
		];

		$new_file_name = Strings::class_to_file_name( $shortcode_class ) . '.php';

		$shortcode_folder = $this->get_or_create_folder( '/inc/Shortcodes/' );

		$new_file_path = $shortcode_folder . $new_file_name;

		if ( file_exists( $new_file_path ) ) {
			Console::error( 'Shortcode already exists!' );
			return;
		}

		$this->make( $template, $new_file_path, $template_variables );

		// Include the class and load it
		$main_file = GPLATE_BASE_DIR . '/' . Console::get_plugin_name() . '.php';

		$main_file_contents = file_get_contents( $main_file );

		$var_name = strtolower( $shortcode_name );

		$main_file_contents = preg_replace(
			'/init_modules\(\) \{([^}]+)\}/',
			'init_modules() {$1' . "\t" . '$this->' . $var_name . ' = Shortcodes\\' . $shortcode_class . '::get_instance();' . PHP_EOL .
			"\t\t" . '}',
			$main_file_contents
		);

		$main_file_contents = preg_replace(
			'/run\(\) \{([^}]+)\}/',
			'run() {$1' . "\t" . '$this->' . $var_name . '->init();' . PHP_EOL .
			"\t\t" . '}',
			$main_file_contents
		);

		// Append propertydeclaration on main class
		$replace = '$1'. PHP_EOL . PHP_EOL .
		"\t\t/**" . PHP_EOL .
		"\t\t * \$" . $var_name .' class' . PHP_EOL .
		"\t\t *" . PHP_EOL .
		"\t\t * @var " . $shortcode_class . PHP_EOL . 
		"\t\t */" . PHP_EOL .
		"\t\tpublic $" . $var_name .';' . PHP_EOL;
		$main_file_contents = preg_replace(
			'/(private \$plugin_label;)(\s)/',
			$replace,
			$main_file_contents
		);

		file_put_contents( $main_file, $main_file_contents );

		Console::success( sprintf( 'Successfully created the shortcode "%s"!', $shortcode_name ) );
	}
}
