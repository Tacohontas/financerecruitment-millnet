<?php

namespace GPlate;

use GPlate\Utils\Strings;
use GPlate\Utils\Console_Color;

if ( ! defined( 'GPLATE_CONSOLE' ) ) exit; // Exit if accessed directly

class Command_Manager {

	/**
	 * @var array $command_classes
	 */
	private $command_classes = [
		'Inspire_Command',
		'Update_Plugin_Command',

		// Make commands
		'Make_Post_Type_Command',
		'Make_Options_Command',
		'Make_Module_Command',
		'Make_Class_Command',
		'Make_Shortcode_Command',
		'Make_Logger_Command',
		'Make_Help_Page_Command',
		'Make_Index_Command',
		'Make_User_Role_Command',
		// 'Use_Command', Deactivated for now
	];

	/**
	 * @var array $commands
	 */
	private $commands = [];

	/**
	 * Load the command manager
	 *
	 * @return void
	 */
	public function load() {
		// Load base command
		require_once __DIR__ . '/commands/Base_Command.php';

		require_once __DIR__ . '/commands/Make_Command.php';

		$this->load_commands();
	}

	/**
	 * Load all commands
	 *
	 * @return void
	 */
	public function load_commands() {
		foreach ( $this->command_classes as $command_name ) {
			require_once __DIR__ . '/commands/' . Strings::class_to_file_name( $command_name ) . '.php';

			$command_class = 'GPlate\\Commands\\' . $command_name;

			$command = new $command_class();

			$this->commands[ $command->get_signature() ] = $command;
		}
	}

	/**
	 * Handle provided command
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public function handle_command( $args ) {
		if ( ! is_array( $args ) || count( $args ) < 1 ||
			in_array( strtolower( $args[0] ), [ 'mayday', 'help' ], true ) ) {
			$this->show_help();
			return;
		}

		if ( ! isset( $this->commands[ $args[0] ] ) ) {
			Console::error( sprintf( 'No command by name "%s" found.', $args[0] ) );
			$closest_command = $this->get_closest_command( $args[0] );

			if ( $closest_command !== null ) {
				Console::error( sprintf( 'Did you mean "%s"?', $closest_command ) );
			}

			return;
		}

		$this->commands[ $args[0] ]->handle( array_slice( $args, 1 ) );
	}

	public function show_help() {
		Console::line( 'GPlate - The ultimate plugin making tool' . PHP_EOL );

		Console::line( Console_Color::YELLOW . 'Available commands:' );

		$longest_command_length = 0;

		foreach ( $this->commands as $command ) {
			$longest_command_length = max( strlen( $command->get_signature() ), $longest_command_length );
		}

		$minimum_command_length = $longest_command_length + 2;

		foreach ( $this->commands as $command ) {
			$line = '  ' . Console_Color::LIGHT_CYAN . $command->get_signature();

			$pads_length = $minimum_command_length - strlen( $command->get_signature() );

			for ( $i = 0; $i < $pads_length;++$i ) {
				$line .= ' ';
			}

			$line .= Console_Color::WHITE . $command->get_description();

			Console::line( $line );
		}

	}

	/**
	 * Get command closest to the one provided
	 *
	 * @param string $command
	 *
	 * @return string|null Signature of command
	 */
	public function get_closest_command( $command ) {
		$closest_command = null;
		$closest_command_distance = null;

		$command_signatures = array_keys( $this->commands );

		foreach ( $command_signatures as $command_signature ) {
			$distance = levenshtein( $command, $command_signature, 1, 1, 1 );

			if ( $closest_command_distance === null || $distance < $closest_command_distance ) {
				$closest_command = $command_signature;
				$closest_command_distance = $distance;
			}
		}

		if ( $closest_command_distance === null || $closest_command_distance > 3 ) {
			return null;
		}

		return $closest_command;
	}

}
