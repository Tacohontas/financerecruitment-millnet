<?php

namespace Financerecruitment_Millnet\Traits;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

trait Singleton {

	/**
	 * The single instance of the class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function __construct() {}

	/**
	 * Get class instance.
	 *
	 * @return static Instance.
	 */
	final public static function get_instance() {
		if ( static::$instance === null ) {
			static::$instance = new static();
		}

		return static::$instance;
	}
}
