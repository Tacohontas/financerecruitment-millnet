<?php

namespace GPlate\Commands;

use GPlate\Console;
use GPlate\Utils\Strings;

if ( ! defined( 'GPLATE_CONSOLE' ) ) exit; // Exit if accessed directly

class Make_Post_Type_Command extends Make_Command {

	/**
	 * @var string $signature
	 */
	protected $signature = 'make:post-type';

	/**
	 * @var string $description
	 */
	protected $description = 'Create a post type';

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

		$post_types = [];
		// Build the post type array, add slugs to post type arguments before processing post type
		for ( $i = 0; $i < count( $args ); $i++ ) {
			if ( strpos( $args[$i], '--slug=' ) === 0 ) {
				// This is a slug argument, append to the post type before this slug argument
				$index = count( $post_types ) -1;
				$post_types[$index] .= $args[$i];
			} else {
				// It's a post type argument, add to post type array
				$post_types[] = $args[$i];
			}
		}

		$template = TEMPLATE_DIRECTORY . '/Post_Type.php';

		foreach ( $post_types as $arg ) {
			// $arg can either be 'PostType--slug=slug' or only 'PostType'
			if ( strpos( $arg, '--slug=' ) !== false ) {
				// In the case of the string containing --slug=
				// we explode the string and use first part as Post Type and second part as slug
				$arg = explode( '--', $arg, 2 );
				$post_type = $arg[0];
				$slug = strtolower( substr( $arg[1], 5 ) );
				$post_type_slug = str_replace( '_', '-', $slug );
			} else {
				// Slug has not been provided, use post type name as slug
				$post_type = $arg;
				$post_type_slug = $arg;
			}

			$parts = explode( '_', strtolower( $post_type ) );
			$post_type_label = str_replace( '-', '_', implode( '_', array_map( 'ucfirst', $parts ) ) );

			$post_type_name = strtolower( $post_type_label );

			$post_type_class_name = $post_type_label;

			$template_variables = [
				'class Post_Type'     => 'class ' . $post_type_class_name,
				'__POST_TYPE_NAME__'  => $post_type_name,
				'__POST_TYPE_SLUG__'  => $post_type_slug,
				'__POST_TYPE_LABEL__' => $post_type_label,
				'__CLASS_NAME__'      => $post_type_class_name,
			];

			$new_file_name = Strings::class_to_file_name( $post_type_class_name ) . '.php';

			$post_types_folder = $this->get_or_create_folder( '/inc/Post_Types/' );

			if ( ! file_exists( $post_types_folder ) ) {
				mkdir( $post_types_folder, 0775 );

				$index_file_name = $post_types_folder . 'index.php';

				if ( ! file_exists( $index_file_name ) ) {
					$index_template = TEMPLATE_DIRECTORY . '/index.php';
					file_put_contents( $index_file_name, file_get_contents( $index_template ) );
				}
			}

			$new_file_path = $post_types_folder . $new_file_name;

			if ( file_exists( $new_file_path ) ) {
				Console::error( sprintf( 'Post type "%s" already exists!', $post_type_label ) );
				continue;
			}

			$this->make( $template, $new_file_path, $template_variables );

			// Include the class and load it
			$main_file = GPLATE_BASE_DIR . '/' . Console::get_plugin_name() . '.php';

			$main_file_contents = file_get_contents( $main_file ); //phpcs:ignore

			$main_file_contents = preg_replace(
				'/init_modules\(\) \{([^}]+)\}/',
				'init_modules() {$1' . "\t" . '$this->' . $post_type_name . ' = Post_Types\\' . $post_type_class_name . '::get_instance();' . PHP_EOL .
				"\t\t" . '}',
				$main_file_contents
			);

			$main_file_contents = preg_replace(
				'/run\(\) \{([^}]+)\}/',
				'run() {$1' . "\t" . '$this->' . $post_type_name . '->init();' . PHP_EOL .
				"\t\t" . '}',
				$main_file_contents
			);

			// Append declaration on main class
			$replace = '$1'. PHP_EOL . PHP_EOL .
			"\t\t/**" . PHP_EOL .
			"\t\t * " . ucfirst( $post_type_name ) .' class' . PHP_EOL .
			"\t\t *" . PHP_EOL .
			"\t\t * @var " . $post_type_class_name . PHP_EOL . 
			"\t\t */" . PHP_EOL .
			"\t\tpublic $" . $post_type_name .';' . PHP_EOL;
			$main_file_contents = preg_replace(
				'/(private \$plugin_label;)(\s)/',
				$replace,
				$main_file_contents
			);

			file_put_contents( $main_file, $main_file_contents ); //phpcs:ignore

			// Make helper file
			$helper_template_file_name = 'Post_Types_Helper.php';
			if ( ! file_exists( GPLATE_BASE_DIR . '/inc/' . $helper_template_file_name ) ) {
				$helper_template = TEMPLATE_DIRECTORY . '/' . $helper_template_file_name;

				$new_helper_file_path = GPLATE_BASE_DIR . '/inc/' . $helper_template_file_name;

				$template_variables = [
					'__CLASS_NAME__' => $post_type_class_name,
				];

				$this->make( $helper_template, $new_helper_file_path, $template_variables );

				$main_file_contents = file_get_contents( $main_file ); //phpcs:ignore

				file_put_contents( $main_file, $main_file_contents ); //phpcs:ignore
			}
			
			$post_type_settings_file_name = 'Post_Types_Settings.php';
			if ( ! file_exists( GPLATE_BASE_DIR . '/inc/' . $post_type_settings_file_name ) ) {
				$post_type_settings_template = TEMPLATE_DIRECTORY . '/' . $post_type_settings_file_name;

				$new_post_type_settings_file_path = GPLATE_BASE_DIR . '/inc/' . $post_type_settings_file_name;

				$template_variables = [
					'__CLASS_NAME__' => $post_type_class_name,
				];

				$this->make( $post_type_settings_template, $new_post_type_settings_file_path, $template_variables );

				// Include the class and load it
				$main_file = GPLATE_BASE_DIR . '/' . Console::get_plugin_name() . '.php';

				$main_file_contents = file_get_contents( $main_file );

				$main_file_contents = preg_replace(
					'/init_modules\(\) \{([^}]+)\}/',
					'init_modules() {$1' . "\t" . '$this->post_types_settings = Post_Types_Settings::get_instance();' . PHP_EOL .
					"\t\t" . '}',
					$main_file_contents
				);
		
				$main_file_contents = preg_replace(
					'/run\(\) \{([^}]+)\}/',
					'run() {$1' . "\t" . '$this->post_types_settings->init();' . PHP_EOL .
					"\t\t" . '}',
					$main_file_contents
				);
		
				// Append declaration on main class
				$replace = '$1'. PHP_EOL . PHP_EOL .
				"\t\t/**" . PHP_EOL .
				"\t\t * Post types settings class" . PHP_EOL .
				"\t\t *" . PHP_EOL .
				"\t\t * @var Post types settings"  . PHP_EOL . 
				"\t\t */" . PHP_EOL .
				"\t\tpublic \$post_types_settings;" . PHP_EOL;
				$main_file_contents = preg_replace(
					'/(private \$plugin_label;)(\s)/',
					$replace,
					$main_file_contents
				);

				file_put_contents( $main_file, $main_file_contents ); //phpcs:ignore
			}

			Console::success( sprintf( 'Successfully created the post type "%s"!', $post_type_label ) );
		}
	}

	/**
	 * Get the template variables
	 *
	 * @return string[]
	 */
	public function get_template_variables() {
		$plugin_name = Console::get_plugin_name();

		return [
			'__OPTIONS_NAME__'          => $plugin_name . '-options',
			'__SETTINGS_SECTION_NAME__' => Strings::dashes_to_underscore( $plugin_name ) . '_section',
			'__PLUGIN_NAME__'           => $plugin_name,
			'__PLUGIN_LABEL__'          => Strings::name_to_label( $plugin_name ),
		];
	}

}
