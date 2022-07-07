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
	 * Init function
	 *
	 * @return void
	 */
	public function init() {
		// add_action('init', [$this, 'test']);
		add_filter( 'gform_admin_pre_render', [ $this, 'handle_form_pre_render' ] );
	}

	/**
	 * Handle form pre render in admin view
	 *
	 * @param array $form
	 * @return array
	 */
	public function handle_form_pre_render( $form ) {
		if ( ! empty( $form['cssClass'] ) && strpos( $form['cssClass'], self::FORM_CSS_CLASS ) !== false ) {
			return $this->populate_fields( $form );
		}

		return $form;
	}

	/**
	 * Populate custom millnet fields
	 *
	 * @param array $form
	 * @return array
	 */
	public function populate_fields( $form ) {
		$client = gen()->millnet_soap();
		$login_success = $client->login();

		if ( ! $login_success ) {
			return $form;
		}

		$groups = $client->get_groups();

		foreach ( $form['fields'] as &$field ) {
			if ( strpos( $field->cssClass, 'fr-millnet-user-group' ) !== false ) {
				$this->populate_field_with_group( 'user_types', $groups, $field );
			}

			if ( strpos( $field->cssClass, 'fr-millnet-fraa-frtemp' ) !== false ) {
				$this->populate_field_with_group( 'fraa_frtemp', $groups, $field );
			}

			if ( strpos( $field->cssClass, 'fr-millnet-salary-type' ) !== false ) {
				$this->populate_field_with_group( 'salary_type', $groups, $field );
			}
		}

		return $form;
	}

	/**
	 * Populate field with group
	 *
	 * @param string $group_name
	 * @param array $groups
	 * @param object $field
	 * @return void
	 */
	public function populate_field_with_group( string $group_name, array $groups, object &$field) {
		$group_collection = $this->get_group_collection( $group_name, $groups );
		// Init arrays and id counter
		$choices = [];
		$id = 0;

		foreach ( $group_collection as $group ) {
			++$id;
			$choices[] = [
				'text'       => $group['GroupName'],
				'value'      => $group['GroupId'],
				'isSelected' => false,
				'price'      => '',
			];
		}
		
		// Populate inputs and choices attributes
		$field->choices = $choices;
	}
	}

	/**
	 * Get user by email
	 *
	 * @param string $email
	 * @param Millnet $client
	 * @return bool|object
	 */
	public static function get_user_by_email( string $email, Millnet $client ) {
		$users = $client->get_users(true);

		if ( ! $users ) {
			return false;
		}

		foreach( $users as $user ) {
			if ( empty( $user->EMail ) ) {
				continue;
			}

			if ( $user->EMail === $email ) {
				return (array) $user;
			}
		}
	}

	/**
	 * Get group collection by defined group name (named by Finance)
	 *
	 * @param string $group_name
	 * @param array $fetched_groups
	 * @return array
	 */
	public function get_group_collection( string $group_name, array $fetched_groups ) {
		$group_collection = [];

		if ( empty( Millnet::GROUPS[ $group_name ] ) ) {
			return $group_collection;
		}

		if ( empty( $fetched_groups ) ) {
			return $group_collection;
		}

		foreach( $fetched_groups as $group ) {
			if ( ! in_array( $group->GroupName, Millnet::GROUPS[ $group_name ], true ) ) {
				continue;
			}

			$group_collection[] = (array) $group;
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
			'EndDate' => $user['end_date'],
			'CostHour' => $user['salary'],
			'Disabled' => 1,
		];
		
		$existing_user = self::get_user_by_email( $user['email'], $client );
		
		if ( $existing_user ) {
			$user_data['UserId'] = $existing_user['UserId'];
			$user_data['UserLogin'] = $existing_user['UserLogin'];
		} else {
			$user_data['FullName'] = $user['name'];
			$user_data['EMail'] = $user['email'];
		}
		
		foreach( $user['groups'] as $group_collection ) {
			foreach( explode( ',', $group_collection ) as $group ) {
				$groups[] = trim( $group );
			}
		}
		
		$user_data['Groups'] = $groups;

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
