<?php

namespace GPlate\Commands;

use GPlate\Console;
use GPlate\Utils\Strings;

if ( ! defined( 'GPLATE_CONSOLE' ) ) exit; // Exit if accessed directly

class Make_User_Role_Command extends Make_Command {

	/**
	 * @var string $signature
	 */
	protected $signature = 'make:user-role';

	/**
	 * @var string $description
	 */
	protected $description = 'Creates a new user role';

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
			$user_role_name = ucwords( str_replace( '-', ' ', $arg ) );

			$user_role_class = Strings::label_to_class( $user_role_name );

			$template = TEMPLATE_DIRECTORY . '/User_Role.php';

			$template_variables = [
				'Class User_Role'    => 'Class ' . $user_role_class,
				'class User_Role'    => 'class ' . $user_role_class,
				'__USER_ROLE_NAME__' => $user_role_name,
			];

			$new_file_name = Strings::class_to_file_name( $user_role_class ) . '.php';

			// Include the class and load it
			$main_file = GPLATE_BASE_DIR . '/' . Console::get_plugin_name() . '.php';

			$main_file_contents = file_get_contents( $main_file ); //phpcs:ignore

			$var_name = strtolower( $user_role_class );

			$plugin_class = Strings::label_to_class( Console::get_plugin_name() ) ;
			$regex_string = '/if \( ! class_exists\( \'([A-z]+)\' \) \) :/';

			// Get the line that checks if the class exists or not (starting line of plugin)
			preg_match(
				$regex_string,
				$main_file_contents,
				$starting_line
			);

			$starting_line[0] = implode( '\\\\', explode( '\\', $starting_line[0] ) ); // Account for losing backslashes when using string

			// Add activation/deactivation for new created user role
			$main_file_contents = preg_replace(
				$regex_string,
				$starting_line[0] . PHP_EOL . "\t" . 'register_activation_hook( __FILE__, [ \'' . $plugin_class . '\User_Roles\\' . $user_role_class .'\', \'run_functions_on_activation\' ] );' . PHP_EOL .
				"\t" . 'register_deactivation_hook( __FILE__, [ \'' . $plugin_class . '\User_Roles\\' . $user_role_class .'\', \'run_functions_on_deactivation\' ] );',
				$main_file_contents
			);

			$main_file_contents = preg_replace(
				'/init_modules\(\) \{([^}]+)\}/',
				'init_modules() {$1' . "\t" . '$this->' . $var_name . ' = new User_Roles\\' . $user_role_class . '();' . PHP_EOL .
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
			"\t\t * @var " . $user_role_class . PHP_EOL . 
			"\t\t */" . PHP_EOL .
			"\t\tpublic $" . $var_name .';' . PHP_EOL;
			$main_file_contents = preg_replace(
				'/(private \$plugin_label;)(\s)/',
				$replace,
				$main_file_contents
			);

			file_put_contents( $main_file, $main_file_contents ); //phpcs:ignore

			$user_roles_folder = GPLATE_BASE_DIR . '/inc/User_Roles/';

			if ( ! file_exists( $user_roles_folder ) ) {
				mkdir( $user_roles_folder, 0775 );

				$index_file_name = $user_roles_folder . 'index.php';

				if ( ! file_exists( $index_file_name ) ) {
					$index_template = TEMPLATE_DIRECTORY . '/index.php';
					file_put_contents( $index_file_name, file_get_contents( $index_template ) ); //phpcs:ignore
				}
			}

			$new_file_path = $user_roles_folder . $new_file_name;

			if ( file_exists( $new_file_path ) ) {
				Console::error( sprintf( 'User Role "%s" already exists!', $user_role_name ) );
				continue;
			}

			$this->make( $template, $new_file_path, $template_variables );

			Console::success( sprintf( 'Successfully created the User Role "%s"!', $user_role_name ) );
		}
	}
}
