<?php

namespace Financerecruitment_Millnet\Soap;

use Financerecruitment_Millnet\Traits\Singleton;
use SoapClient;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Millnet
 */
class Millnet {
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
				'trace'     => true,
				'exception' => false,
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
					'login'      => 'api', // TODO: Sätt som konstant eller define:a från wp-config
					'password'   => 'troHi39n_$', // TODO: Sätt som konstant eller define:a från wp-config
					'instanceid' => '000815.1', // TODO: Sätt som konstant eller define:a från wp-config
				],
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
	 * @return bool|object
	 */
	public function get_users( bool $include_disabled = false ) {
		$result = self::$client->__soapCall(
			'getUsers',
			[
				'getUsers' => [
					'session'         => self::$session_id,
					// Set variable to a string representation of a boolean value ("true" or "false)
					'includeDisabled' => $include_disabled,
				],
			]
		);

		return ! empty( $result->users ) ? $result->users : false;
	}

	/**
	 * Get groups
	 *
	 * @return array
	 */
	public function get_groups() {
		$result = self::$client->__soapCall(
			'getGroups',
			[
				'getGroups' => [
					'session' => self::$session_id,
				],
			]
		);

		return ! empty( $result->groups ) ? (array) $result->groups : [];
	}

	/**
	 * Add user
	 *
	 * @param array $user
	 * @return object
	 */
	public function add_user( array $user ) {
		return self::$client->__soapCall(
			'addUser',
			[
				'addUser' => [
					'session' => self::$session_id,
					'user'    => $user,
				],
			]
		);
	}
}