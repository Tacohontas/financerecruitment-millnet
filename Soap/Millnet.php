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
			'https://financerecruitment.millnet.cloud/cgi/api_service.cgi?wsdl', 
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
}