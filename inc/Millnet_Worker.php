<?php

namespace Financerecruitment_Millnet;

use Financerecruitment_Millnet\Soap\Millnet;
use Financerecruitment_Millnet\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Millnet_Worker
 */
class Millnet_Worker {
	use Singleton;

	/**
	 * Form CSS class
	 */
	const FORM_CSS_CLASS = 'financerecruitment-millnet-form';

	/**
	 * Get user by email
	 *
	 * @param string $email
	 * @param Millnet $client
	 * @return bool|object
	 */
	public static function get_user_by_email( string $email, Millnet $client ) {
		$users = $client->get_users( true );

		if ( ! $users ) {
			return false;
		}

		foreach ( $users as $user ) {
			if ( empty( $user->EMail ) ) { //phpcs:ignore WordPress.NamingConventions.ValidVariableName
				continue;
			}

			if ( $user->EMail === $email ) { //phpcs:ignore WordPress.NamingConventions.ValidVariableName
				return (array) $user;
			}
		}
	}

	/**
	 * Get group collection by defined group name (named by Finance)
	 *
	 * @param array $fetched_groups
	 * @param array $groups_from_form
	 * @return array
	 */
	public static function get_group_collection( array $fetched_groups, array $groups_from_form ) {
		$group_collection = [];

		if ( empty( $fetched_groups ) ) {
			return $group_collection;
		}

		foreach ( $fetched_groups as $group ) {
			if ( ! in_array( $group->GroupName, $groups_from_form, true ) ) { //phpcs:ignore WordPress.NamingConventions.ValidVariableName
				continue;
			}

			$group_collection[] = $group->GroupId; //phpcs:ignore WordPress.NamingConventions.ValidVariableName
		}

		return $group_collection;
	}

	/**
	 * Make user data array
	 *
	 * @param array $user
	 * @param Millnet $user
	 * @return array
	 */
	public static function make_user_data( array $user, Millnet $client ) {
		$user_data = [
			'UserLogin' => self::make_username( $user['name'] ),
			'StartDate' => $user['start_date'],
			'EndDate'   => $user['end_date'],
			'CostHour'  => $user['salary'],
			'Disabled'  => 1,
		];

		$existing_user = self::get_user_by_email( $user['email'], $client );

		// If user exists - update it (FullName and Email shouldn't be updated)
		if ( $existing_user ) {
			$user_data['UserId'] = $existing_user['UserId'];
			$user_data['UserLogin'] = $existing_user['UserLogin'];
		} else {
			$user_data['FullName'] = $user['name'];
			$user_data['EMail'] = $user['email'];
		}

		$millnet_groups = $client->get_groups();
		$group_collection = self::get_group_collection( $millnet_groups, $user['groups'] );
		$user_data['Groups'] = $group_collection;

		return $user_data;
	}

	/**
	 * Create or update user
	 * - User will get updated if array has "UserID" property
	 *
	 * @param array $user
	 * @return void
	 */
	public static function create_or_update_user( array $user ) {
		$client = gen()->millnet_soap();
		$login_success = $client->login();

		if ( ! $login_success ) {
			return;
		}

		$user_data = self::make_user_data( $user, $client );
		// $client->add_user( $user_data );
	}

	/**
	 * Make username (and return it)
	 *
	 * @param string $full_name
	 * @return string
	 */
	public static function make_username( string $full_name ) {
		return str_replace( ' ', '.', strtolower( remove_accents( $full_name ) ) );
	}

}
