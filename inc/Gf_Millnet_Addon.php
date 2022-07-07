<?php

namespace Financerecruitment_Millnet;

use GFForms, GFFeedAddOn, GFCommon, GFAPI, GFFormsModel;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

GFForms::include_feed_addon_framework();

/**
 * Class Gf_Millnet_Addon
 */
class Gf_Millnet_Addon extends GFFeedAddOn {

	/**
	 * Version of this addon
	 *
	 * @var string
	 */
	protected $_version = Plugin::VERSION;

	/**
	 * Minimum version of Gravity Forms
	 *
	 * @var string
	 */
	protected $_min_gravityforms_version = '1.9';

	/**
	 * Slug for this addon
	 *
	 * @var string
	 */
	protected $_slug = 'gravityforms-fr-millnet';

	/**
	 * Path to the main file of this plugin
	 *
	 * @var string
	 */
	protected $_path = 'financerecruitment-millnet/financerecruitment-millnet.php';

	/**
	 * Path to this file
	 *
	 * @var string
	 */
	protected $_full_path = __FILE__;

	/**
	 * Title of this addon
	 *
	 * @var string
	 */
	protected $_title = 'Gravity Forms Finance Recruitment Millnet';

	/**
	 * Short title of this addon
	 *
	 * @var string
	 */
	protected $_short_title = 'Finance Recruitment Millnet';

	/**
	 * @var object|null $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Returns an instance of this class, and stores it in the $_instance property.
	 *
	 * @return object $_instance An instance of this class.
	 */
	public static function get_instance() {
		if ( self::$_instance === null ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Settings for the plugin feeds
	 *
	 * @return array
	 */
	public function feed_settings_fields() {
		$settings = [
			[
				'title'  => esc_html__( 'Millnet feed settings', 'financerecruitment-millnet' ),
				'fields' => [
					[
						'name'     => 'feedName',
						'label'    => esc_html__( 'Name', 'financerecruitment-millnet' ),
						'type'     => 'text',
						'required' => true,
						'class'    => 'medium',
					],
				],
			],
			[
				'title'       => '',
				'description' => '',
				'fields'      => [
					[
						'name'  => 'fr_millnet_name',
						'label' => esc_html__( 'Candidate name', 'financerecruitment-millnet' ),
						'type'  => 'text',
						'class' => 'medium merge-tag-support',
					],
					[
						'name'  => 'fr_millnet_date_start',
						'label' => esc_html__( 'Date start', 'financerecruitment-millnet' ),
						'type'  => 'text',
						'class' => 'medium merge-tag-support',
					],
					[
						'name'  => 'fr_millnet_date_end',
						'label' => esc_html__( 'Date end', 'financerecruitment-millnet' ),
						'type'  => 'text',
						'class' => 'medium merge-tag-support',
					],
					[
						'name'  => 'fr_millnet_email',
						'label' => esc_html__( 'Candidate Email', 'financerecruitment-millnet' ),
						'type'  => 'text',
						'class' => 'medium merge-tag-support',
					],
					[
						'name'  => 'fr_millnet_user_group',
						'label' => esc_html__( 'User group', 'financerecruitment-millnet' ),
						'type'  => 'text',
						'class' => 'medium merge-tag-support',
					],
					[
						'name'  => 'fr_millnet_consultant',
						'label' => esc_html__( 'Recruitment Consultant', 'financerecruitment-millnet' ),
						'type'  => 'text',
						'class' => 'medium merge-tag-support',
						'tooltip' => esc_html__( 'Field that lists Recruitment Consultants (will set FR AA/FR TEMP based on consultant)', 'financerecruitment-millnet' ),
					],
					[
						'name'    => 'fr_millnet_salary_type',
						'label'   => esc_html__( 'Salary type', 'financerecruitment-millnet' ),
						'type'    => 'text',
						'class'   => 'medium merge-tag-support',
						'tooltip' => esc_html__( 'Type of salary (eg Hourly pay or by invoice)', 'financerecruitment-millnet' ),
					],
					[
						'name'  => 'fr_millnet_salary',
						'label' => esc_html__( 'Candidate Salary (hourly)', 'financerecruitment-millnet' ),
						'type'  => 'text',
						'class' => 'medium merge-tag-support',
					],
				],

			],
		];

		return $settings;
	}

	/**
	 * Sets which columns should be displayed in the feed list
	 *
	 * @return array
	 */
	public function feed_list_columns() {
		return [
			'feedName' => esc_html__( 'Name', 'financerecruitment-millnet' ),
		];
	}

	/**
	 * Process the feed
	 *
	 * @param array $feed The feed object to be processed.
	 * @param array $entry The entry object currently being processed.
	 * @param array $form The form object currently being processed.
	 *
	 * @return void
	 */
	public function process_feed( $feed, $entry, $form ) {
		if ( ! $feed['meta'] ) {
			return;
		}

		Millnet_Worker::create_or_update_user(
			[
				'name'       => $this->get_millnet_field_value( 'fr_millnet_name', $feed, $form, $entry ),
				'email'      => $this->get_millnet_field_value( 'fr_millnet_email', $feed, $form, $entry ),
				'start_date' => $this->get_millnet_field_value( 'fr_millnet_date_start', $feed, $form, $entry ),
				'end_date'   => $this->get_millnet_field_value( 'fr_millnet_date_end', $feed, $form, $entry ),
				'salary'     => $this->get_millnet_field_value( 'fr_millnet_salary', $feed, $form, $entry ),
				'groups'     => [
					$this->get_millnet_field_value( 'fr_millnet_consultant', $feed, $form, $entry ),
					$this->get_millnet_field_value( 'fr_millnet_salary_type', $feed, $form, $entry ),
					$this->get_millnet_field_value( 'fr_millnet_user_group', $feed, $form, $entry ),
				],
			]
		);
	}

	/**
	 * Get mapped millnet field value
	 *
	 * @param string $prop
	 * @param array $feed
	 * @param array $form
	 * @param array $entry
	 * @return string
	 */
	public function get_millnet_field_value( $prop, $feed, $form, $entry ) {
		return GFCommon::replace_variables( rgar( $feed['meta'], $prop ), $form, $entry, false, false, false, 'number' );
	}
}