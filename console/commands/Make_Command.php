<?php

namespace GPlate\Commands;

use GPlate\Console;
use GPlate\Utils\Strings;

if ( ! defined( 'GPLATE_CONSOLE' ) ) exit; // Exit if accessed directly

abstract class Make_Command extends Base_Command {

	public function replace_template_variables( $file_contents, $template_variables = [] ) {
		$template_variables = array_merge(
			$this->get_default_template_variables(),
			$this->get_template_variables(),
			$template_variables
		);

		foreach ( $template_variables as $variable_name => $replacement ) {
			$file_contents = str_replace( $variable_name, $replacement, $file_contents );
		}

		return $file_contents;
	}

	private function get_default_template_variables() {
		$plugin_name = Console::get_plugin_name();

		return [
			'// USE_PLUGIN_NAMESPACE' => 'use ' . Console::get_plugin_namespace(),
			'// PLUGIN_NAMESPACE'     => 'namespace ' . Console::get_plugin_namespace(),
			'__PLUGIN_NAME__'         => $plugin_name,
			'__PLUGIN_LABEL__'        => Strings::name_to_label( $plugin_name ),
			'PLUGIN_NAMESPACE'        => Console::get_plugin_namespace(),
		];
	}

	public function get_template_variables() {
		return [];
	}

	public function make( $template_file, $new_file, $template_variables = [] ) {
		$template_contents = $this->replace_template_variables( file_get_contents( $template_file ), $template_variables );

		return file_put_contents( $new_file, $template_contents );
	}

	public function get_or_create_folder( $folder ) {
		$folder = GPLATE_BASE_DIR . $folder;

		if ( ! file_exists( $folder ) ) {
			mkdir( $folder, 0775 );

			$index_file_name = $folder . 'index.php';

			if ( ! file_exists( $index_file_name ) ) {
				$index_template = TEMPLATE_DIRECTORY . '/index.php';
				file_put_contents( $index_file_name, file_get_contents( $index_template ) );
			}
		}
		return $folder;
	}
}
