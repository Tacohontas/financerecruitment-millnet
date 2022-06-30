<?php

namespace GPlate\Commands;

use GPlate\Console;

if ( ! defined( 'GPLATE_CONSOLE' ) ) exit; // Exit if accessed directly

class Use_Command extends Base_Command {

	/**
	 * @var string $signature
	 */
	protected $signature = 'use';

	/**
	 * @var string $description
	 */
	protected $description = 'Enqueues assets';

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

		// Create module manager if it doesn't exist
		$scripts_file = GPLATE_BASE_DIR . '/inc/Scripts.php';

		if ( ! file_exists( $scripts_file ) ) {
			Console::error( '/inc/Scripts.php does not exist!' );
			return;
		}

		$asset_name = strtolower( $args[0] );

		// Add the module to the module manager
		$scripts_file_contents = file_get_contents( $scripts_file );

		$scripts = $this->get_enqueue_content( $asset_name );
		if ( $scripts === '' ) {
			Console::error( sprintf( 'Could not find the script "%s"', $asset_name ) );
			return;
		}

		$scripts_file_contents = preg_replace(
			'/(enqueue_' . ( isset( $args[1] ) ? $args[1] : 'backend' ) . '_scripts\(\)\s+?{\s+?)([\S\s]+?})/',
			'$1' . $scripts . PHP_EOL . PHP_EOL . '$2',
			$scripts_file_contents
		);
		
		$scripts_file_contents = preg_replace(
			'/GEN_PLUGIN_BOILERPLATE_FILE/',
			strtoupper( Console::get_plugin_namespace() ) . '_FILE',
			$scripts_file_contents
		);

		file_put_contents( $scripts_file, $scripts_file_contents );

		Console::success( sprintf( 'Successfully added "%s"!', $asset_name ) );
	}

	public function get_enqueue_content( $asset_name ) {
		switch ( $asset_name ) {
			case 'select2':
				$enqueue_content = "\t\t" . "wp_enqueue_style( 'select2', plugins_url( 'assets/select2/select2.min.css', GEN_PLUGIN_BOILERPLATE_FILE ), false, Plugin::VERSION );" . PHP_EOL;
				$enqueue_content .= "\t\t" . "wp_enqueue_script( 'select2', plugins_url( 'assets/select2/select2.min.js', GEN_PLUGIN_BOILERPLATE_FILE ), [ 'jquery' ], Plugin::VERSION, true );";
			break;

			case 'daterangepicker':
				$enqueue_content = "\t\t" . "wp_enqueue_style( 'daterangepicker', plugins_url( 'assets/daterangepicker/daterangepicker.css', GEN_PLUGIN_BOILERPLATE_FILE ), false, Plugin::VERSION );" . PHP_EOL;
				$enqueue_content .= "\t\t" . "wp_enqueue_script( 'moment', plugins_url( 'assets/daterangepicker/moment.js', GEN_PLUGIN_BOILERPLATE_FILE ), [ 'jquery' ], Plugin::VERSION, true );" . PHP_EOL;
				$enqueue_content .= "\t\t" . "wp_enqueue_script( 'daterangepicker', plugins_url( 'assets/daterangepicker/daterangepicker.js', GEN_PLUGIN_BOILERPLATE_FILE ), [ 'jquery' ], Plugin::VERSION, true );";
			break;

			default:
				$enqueue_content = '';
			break;
		}

		return $enqueue_content;
	}
}
