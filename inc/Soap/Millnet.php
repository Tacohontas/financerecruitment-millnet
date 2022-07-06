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
	 * User types
	 */
	const GROUPS = [
		'user_types' => [
			'Alla anställda',
			'Alla användare',
		],
		'fraa_frtemp' => [
			'FR AA',
			'FR Temp',
		],
		'salary_type' => [
			'LÖN: Timlön',
		]
	];

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
	 * @return bool|object
	 */
	public function get_users( bool $include_disabled = false ) {
		$result = self::$client->__soapCall(
			'getUsers', 
			[
				'getUsers' => [
						'session' => self::$session_id,
						// Set variable to a string representation of a boolean value ("true" or "false)
						'includeDisabled' => $include_disabled,
					]
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
					]
				]
			);
			
		return ! empty( $result->groups ) ? (array) $result->groups : [];
	}

	/**
	 * Get group collection by defined group name (named by Finance)
	 *
	 * @param array $fetched_groups
	 * @return array
	 */
	public function get_group_collection( string $group_name, array $fetched_groups = [] ) {
		$group_collection = [];

		if ( empty( self::GROUPS[ $group_name ] ) ) {
			return $group_collection;
		}
		
		if ( empty( $fetched_groups ) ) {
			$fetched_groups = $this->get_groups();
		}

		if ( empty( $fetched_groups ) ) {
			return $group_collection;
		}

		foreach( $fetched_groups as $group ) {
			if ( ! in_array( $group->GroupName, self::GROUPS[ $group_name ], true ) ) {
				continue;
			}

			$group_collection[] = (array) $group;
		}

		return $group_collection;
	}

	/**
	 * Get user by email
	 *
	 * @param string $email
	 * @return bool|object
	 */
	public function get_user_by_email( string $email ) {
		$users = $this->get_users();

		if ( ! $users ) {
			return false;
		}

		foreach( $users as $user ) {
			if ( empty( $user->EMail ) ) {
				continue;
			}

			if ( $user->EMail === $email ) {
				return $user;
			}
		}
	}

	public function add_user() {
		$user = [
			'name' => 'Generation Testperson',
			'email' => 'jonathan+test@thegeneration.se',
			'start_date' => '2022-07-06',
			'end_date' => '2022-07-06',
			'hourly_pay' => 1111,
			'groups' => [
				300000000000000145,
				300000000000000082,
			]
		];

		$result = self::$client->__soapCall(
			'addUser', 
			[
				'addUser' => [
						'session' => self::$session_id,
						'user' => [
							'UserId' => '253',
							'FullName' => $user['name'],
							'UserLogin' => $this->make_username( $user['name'] ),
							'EMail' => $user['email'],
							'StartDate' => $user['start_date'],
							'EndDate' => $user['end_date'],
							'CostHour' => $user['hourly_pay'],
							'Groups' => $user['groups'],
							'Disabled' => 1,
						],
				]
			]
			);
			
		return $result;
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