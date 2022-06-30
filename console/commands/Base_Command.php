<?php

namespace GPlate\Commands;

if ( ! defined( 'GPLATE_CONSOLE' ) ) exit; // Exit if accessed directly

abstract class Base_Command {

	/**
	 * @var string $signature
	 */
	protected $signature;

	/**
	 * @var string $description
	 */
	protected $description;

	/**
	 * Get this command's signature
	 *
	 * @return string
	 */
	public function get_signature() {
		return $this->signature;
	}

	/**
	 * Get this command's description
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Handle this command
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public abstract function handle( $args = []);

}