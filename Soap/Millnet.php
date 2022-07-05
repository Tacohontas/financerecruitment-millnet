<?php

namespace Financerecruitment_Millnet\Api;

use Financerecruitment_Millnet\Traits\Singleton;
use Financerecruitment_Millnet\Options;
use SoapClient;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Cision
 */
class Cision {
	use Singleton;
	
	/**
	 * WSDL
	 */
	const WSDL = 'https://financerecruitment.millnet.cloud/cgi/api_service.cgi?wsdl';

	/**
	 * Client object
	 *
	 * @var object
	 */
	private static $client = '';

	/**
	 * Session ID
	 *
	 * @var string
	 */
	private static $session_id = '';

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function __construct() {
		self::$client = new SoapClient(
			self::WSDL, 
			[
				'trace' => true, 
				'exception' => false
			]
		);
	}

	/**
	 * Login method
	 *
	 * @return bool
	 */
	public function login() {
		$result = self::$client->__soapCall(
			'login', 
			[
				'loginRequest' => [
						'login' => 'api', // TODO: Sätt som konstant eller define:a från wp-config
						'password' => 'troHi39n_$', // TODO: Sätt som konstant eller define:a från wp-config
						'instanceid' => '000815.1' // TODO: Sätt som konstant eller define:a från wp-config
					]
				]
			);

		if ( ! empty( $result->session ) ) {
			self::$session_id = $result->session;
		}

		return ! empty( self::$session_id );
	}

	/**
	 * Get users
	 *
	 * @param bool $include_disabled
	 * @return bool
	 */
	public function get_users( bool $include_disabled = false ) {
		$result = self::$client->__soapCall(
			'getUsers', 
			[
				'getUsers' => [
						'session' => self::$session_id,
						// Set variable to a string representation of a boolean value ("true" or "false)
						'includeDisabled' => $include_disabled ? 'true' : 'false',
					]
				]
			);
			
		return count( (array) $result ) === 0 ?: $result;
	}

	/**
	 * Make username (and return it)
	 *
	 * @param string $full_name
	 * @return string
	 */
	public function make_username( string $full_name ) {
		return str_replace( ' ', '.', strtolower( remove_accents( $full_name ) ) );
	}
}