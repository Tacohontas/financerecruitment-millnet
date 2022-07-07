<?php

namespace Financerecruitment_Millnet;

use Financerecruitment_Millnet\Traits\Singleton;
use Financerecruitment_Millnet\Utils\Array_Utils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Logger
 */
class Logger {
	use Singleton;

	/**
	 * Id of this Logger
	 *
	 * @var string
	 */
	private $id = 'financerecruitment-millnet';

	/**
	 * Options for logging
	 *
	 * @var array
	 */
	private $log_options = [
		'LOG',
		'WARNING',
		'ERROR',
	];

	/**
	 * Number of files to keep
	 *
	 * @var int
	 */
	private $files_to_keep = 7;

	/**
	 * Email address for logged errors
	 *
	 * @var string
	 */
	private $error_email_address = '2ndline@thegeneration.se';

	/**
	 * Add an entry to the log file
	 *
	 * @param string $message
	 * @param string $option
	 *
	 * @return void
	 */
	public function log( string $message, string $option = 'LOG' ) {
		if ( ! in_array( $option, $this->log_options ) ) {
			// Logging bad usage of the logging and early exit
			gen()->logger->log( 'Logging invoked with unsupported option.', 'WARNING' );
			return;
		}

		$this->maybe_rotate_log_file();

		$line = '[' . date_i18n( 'Y-m-d H:i:s' ) . '] ' . $option . ': ' . $message;

		if ( $option === 'WARNING' || $option === 'ERROR' ) {
			// Get the information about where the log was called for easier debugging from log file
			$trace_string = $this->parse_backtrace( debug_backtrace() );
			$line .= $trace_string . PHP_EOL; // Append to log line and end the line
		} else {
			// No Warning or Error, end the log line
			$line .= PHP_EOL;
		}

		if ( $option === 'ERROR' ) {
			if ( ! $this->send_error_email( $message, $line ) ) {
				gen()->logger->log( 'Email for error was not sent successfully.', 'WARNING' );
			}
		}

		$this->write( $line );
	}

	/**
	 * Gets the string with information about what file and line generated the logging
	 *
	 * @param array $backtrace
	 *
	 * @return string
	 */
	public function parse_backtrace( $backtrace ) {
		$file = end( explode( '/', $backtrace[0]['file'] ) );
		return ' - Generated in file: ' . $file . ' on line ' . $backtrace[0]['line'];
	}

	/**
	 * Sends an email with error information
	 *
	 * @param string $message
	 * @param string $log_line
	 *
	 * @return void
	 */
	public function send_error_email( $message, $log_line ) {
		$subject = 'Error - ' . Plugin::PLUGIN_NAME . ' - ' . $message;
		return wp_mail( $this->error_email_address, $subject, $log_line );
	}

	/**
	 * Writes the string to the log
	 *
	 * @param String $str
	 *
	 * @return void
	 */
	public function write( string $str ) {
		$log_file = fopen( $this->get_log_file_path(), 'a' ); //phpcs:ignore
		fwrite( $log_file, $str ); //phpcs:ignore
		fclose( $log_file ); //phpcs:ignore
	}

	/**
	 * Maybe setup log file
	 *
	 * @return void
	 */
	private function maybe_rotate_log_file() {
		if ( ! file_exists( $this->get_log_folder_path() ) ) {
			$this->setup_log_folder();
		}

		if ( ! file_exists( $this->get_log_file_path() ) ) {
			$this->setup_log_file();
		}

		$log_folder = $this->get_log_folder_path();

		$log_files = glob( $log_folder . '/*.log' );

		$log_files_count = count( $log_files );

		// Rotate log if more than X files have been created
		if ( $log_files_count > $this->files_to_keep ) {
			$logs_to_delete = $log_files_count - $this->files_to_keep;

			// Order by oldest to newest
			$log_files = Array_Utils::sort_by(
				$log_files,
				function ( $log_file_name ) {
					preg_match( '/[0-9]{4}\-[0-9]{2}\-[0-9]{2}/', $log_file_name, $date_match );

					if ( ! $date_match ) {
						return 0;
					}

					return strtotime( $date_match[0] );
				},
				'ASC'
			);

			for ( $i = 0; $i < $logs_to_delete; ++$i ) {
				unlink( $log_folder . '/' . basename( $log_files[ $i ] ) );
			}
		}
	}

	/**
	 * Setup the log folder
	 *
	 * @return void
	 */
	private function setup_log_folder() {
		$log_folder = $this->get_log_folder_path();

		wp_mkdir_p( $log_folder );

		$htaccess_file_path = $log_folder . '/.htaccess';

		$htaccess_file = fopen( $htaccess_file_path, 'w' ); //phpcs:ignore
		fwrite( $htaccess_file, 'deny from all' ); //phpcs:ignore
		fclose( $htaccess_file ); //phpcs:ignore

		if ( defined( 'FS_CHMOD_FILE' ) ) {
			chmod( $htaccess_file_path, FS_CHMOD_FILE );
		}
	}

	/**
	 * Setup the log file
	 *
	 * @return void
	 */
	private function setup_log_file() {
		$log_file_path = $this->get_log_file_path();

		touch( $log_file_path );

		if ( defined( 'FS_CHMOD_FILE' ) ) {
			chmod( $log_file_path, FS_CHMOD_FILE );
		}
	}

	/**
	 * Get path to the log file
	 *
	 * @return string
	 */
	private function get_log_file_path() {
		$log_file_name = $this->id . '-' . wp_hash( $this->id ) . '-' . date_i18n( 'Y-m-d' ) . '.log';

		return $this->get_log_folder_path() . '/' . $log_file_name;
	}

	/**
	 * Get path to the log folder
	 *
	 * @return string
	 */
	private function get_log_folder_path() {
		if ( defined( 'GEN_LOGGER_PATH' ) ) {
			return GEN_LOGGER_PATH . '/' . $this->id . '-logs';
		}

		$upload_dir = wp_upload_dir();

		$log_folder_path = $upload_dir['basedir'] . '/' . $this->id . '-logs';

		return $log_folder_path;
	}
}
