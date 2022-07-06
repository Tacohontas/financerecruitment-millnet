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
	}

}
