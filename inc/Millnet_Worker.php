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
		$inputs = [];
		$id = 0;

		foreach ( $group_collection as $group ) {
			++$id;
			$choices[] = [
				'text'       => $group['GroupName'],
				'value'      => $group['GroupId'],
				'isSelected' => false,
				'price'      => '',
			];
			$inputs[] = [
				'id'    => $field->id . '.' . $id,
				'label' => $group['GroupName'],
				'name'  => $group['GroupName'],
			];
		}
		
		// Populate inputs and choices attributes
		$field->inputs = $inputs;
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
	public function get_user_by_email( string $email, Millnet $client ) {
		$users = $client->get_users();

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
	}

}
