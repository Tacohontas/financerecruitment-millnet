<?php

namespace GPlate\Commands;

use Gen_Plugin_Boilerplate\Plugin;
use GPlate\Console;
use GPlate\Utils\Strings;

if ( ! defined( 'GPLATE_CONSOLE' ) ) exit; // Exit if accessed directly

class Update_Plugin_Command extends Make_Command {

	/**
	 * @var string $signature
	 */
	protected $signature = 'version-set';

	/**
	 * @var string $description
	 */
	protected $description = 'Sets a new version on plugin';

	/**
	 * Handle this command
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public function handle( $args = [] ) {
        if ( count( $args ) !== 1 ) {
            Console::error( 'Too many or too few arguments. Only one (version number) is allowed.' );
            return;
        }

        $new_version = $args[0];

        if ( ! $this->is_version_format( $new_version ) ) {
            Console::error( 'Not a valid version format. Please use format: x.x.x' );
            return;
        }

        // Get the main file
        $main_file = GPLATE_BASE_DIR . '/' . Console::get_plugin_name() . '.php';
        $main_file_contents = file_get_contents( $main_file ); //phpcs:ignore

        // Get the old version
        preg_match("/(?:(?:const VERSION = ')([\d.\d.\d]+)')/", $main_file_contents, $plugin_version_matches);
        $old_plugin_version = $plugin_version_matches[1];

        if ( ! $this->progressive_versions( $old_plugin_version, $new_version ) ) {
            Console::error( 'Regressive plugin versions are not allowed.' );
            return;
        }

        // Update Plugin Version
        $main_file_contents = preg_replace(
            '/(\* Version: '. $old_plugin_version . ')/',
            '* Version: '. $new_version,
            $main_file_contents
        );

        // Update Plugin Constant
        $main_file_contents = preg_replace(
            '/(const VERSION = \''. $old_plugin_version . '\')/',
            'const VERSION = \''. $new_version . '\'',
            $main_file_contents
        );

        // Get Package Json File
        $package_json_file = GPLATE_BASE_DIR . '/package.json';
        $package_json_contents = file_get_contents( $package_json_file ); //phpcs:ignore

        // Get the old version
        preg_match('/(?:(?:"version": ")([\d.\d.\d]+))/', $package_json_contents, $package_version_matches);
        $old_package_json_version = $package_version_matches[1];

        // Update Package.json version
        $package_json_contents = preg_replace(
            '/("version": "'. $old_package_json_version .'")/',
            '"version": "'. $new_version .'"',
            $package_json_contents
        );

        // Get Changelog File
        $changelog_file = GPLATE_BASE_DIR . '/CHANGELOG.md';
        $changelog_file_contents = file_get_contents( $changelog_file ); //phpcs:ignore

        $firstline = `head -n1 {$changelog_file}`;
        $plugin_name = trim( preg_replace( '/# /', '', $firstline ) );

        // Replace top of file with new entry
        $changelog_file_contents = preg_replace(
			'/# ' . $plugin_name . '/',
			'# ' . $plugin_name . "\n\n" . '## ' . $new_version . ' (' . date('Y-m-d') . ')' . PHP_EOL .
			"- Type: Comment #Issue",
			$changelog_file_contents
		);

        // Update the files with the content
        file_put_contents( $main_file, $main_file_contents ); //phpcs:ignore
        file_put_contents( $package_json_file, $package_json_contents ); //phpcs:ignore
        file_put_contents( $changelog_file, $changelog_file_contents );

        Console::success( sprintf( 'Successfully updated versions and updated changelog with a new entry for version: "%s"!', $new_version ) );
	}

    /**
     * Checks if the version provided to command is higher than the last version
     *
     * @param string $old
     * @param string $new
     * 
     * @return bool
     */
    public function progressive_versions( $old, $new ) {
        $old_array = explode('.', $old);
        $new_array = explode('.', $new);

        for ( $i = 0; $i < count($new_array); ++$i ) {
            if ( (int) $new_array[ $i ] > (int) $old_array[ $i ] ) {
                return true;
            }
        }

        return false;
    }


    /**
     * Checks if the given version follows the correct format
     *
     * @param string $version
     * 
     * @return boolean
     */
    public function is_version_format( $version ) {
        return count( explode('.', $version ) ) === 3 ? true : false; 
    }
}
