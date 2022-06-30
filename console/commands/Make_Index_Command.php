<?php

namespace GPlate\Commands;

use GPlate\Console;

if ( ! defined( 'GPLATE_CONSOLE' ) ) exit; // Exit if accessed directly

class Make_Index_Command extends Make_Command {

	/**
	 * @var string $signature
	 */
	protected $signature = 'make:index';

	/**
	 * @var string $description
	 */
	protected $description = 'Make empty index.php files';

	/**
	 * @var int $index_files_created
	 */
	public $index_files_created = 0;

	/**
	 * @var array $index_files
	 */
	public $index_files = [];

	/**
	 * @var string[] $default_paths
	 */
	public $default_paths = [ 'inc', 'partials', 'assets' ];

	/**
	 * Handle this command
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public function handle( $args = [] ) {
		// Init variables
		$dir = GPLATE_BASE_DIR;
		$result = [];
		$paths = $this->default_paths;

		// Early exit on too many args
		if ( count( $args ) > 1 ) {
			Console::error( 'Usage: make:index *relative path* (will check /inc, /partials and /assets as default)' );
			exit();
		}

		// Set specific path
		if ( count( $args ) > 0 ) {
			$args_path = $dir . '/' . trim( $args[0], '/' );
			if ( ! file_exists( $args_path ) ) {
				Console::error( sprintf( 'Path doesn\'t exist: %s', $args_path ) );
				exit();
			}
			$paths = [ trim( $args[0], '/' ) ];
		}

		// Get newly created file paths
		foreach ( $paths as $path ) {
			$result[] = $this->get_new_empty_index_files( $dir . '/' . $path );
		}

		$message = sprintf( 'Successfully created %d empty index files:', $this->index_files_created );
		if ( $this->index_files_created === 0 ) {
			$message = 'Everything\'s looking fine! No empty index files created.';
		}

		Console::success( $message );

		// Output created file paths
		if ( ! empty( $this->index_files ) ) {
			foreach ( $this->index_files as $index_file ) {
				Console::success( $index_file );
			}
		}
	}

	/**
	 * Get paths to the created index files
	 *
	 * @param string $dir
	 * @param array $results
	 *
	 * @return array
	 */
	public function get_new_empty_index_files( $dir, &$results = [] ) {
		$files = scandir( $dir );

		foreach ( $files as $file ) {
			$path = realpath( $dir . DIRECTORY_SEPARATOR . $file );

			if ( ! is_dir( $path ) ) {
				continue;
			}

			if ( $file !== '.' && $file !== '..' ) {
				$this->maybe_create_empty_index_file( $path );
				$this->get_new_empty_index_files( $path, $results );
				$results[] = $path;
			}
		}

		return $results;
	}

	/**
	 * Maybe create empty index file
	 *
	 * @param string $path
	 *
	 * @return void
	 */
	public function maybe_create_empty_index_file( $path ) {
		$index_file_name = $path . '/index.php';
		if ( ! file_exists( $index_file_name ) ) {
			$index_template = TEMPLATE_DIRECTORY . '/index.php';
			file_put_contents( $index_file_name, file_get_contents( $index_template ) );
			$this->index_files_created++;
			$this->index_files[] = $index_file_name;
		}
	}
}