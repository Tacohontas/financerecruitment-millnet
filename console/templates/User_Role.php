<?php

// PLUGIN_NAMESPACE\User_Roles;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class User_Role
 */
class User_Role {

	/**
	 * Name of User Role
	 */
	const ROLE_NAME = '__USER_ROLE_NAME__';
	
	/**
	 * Init function
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_post_gt_refresh_roles', [ $this, 'refresh_role' ]);
	}

	/**
	 * Add custom user role with certain capabilities
	 *
	 * @return void
	 */
	public static function add_user_role() {
		add_role(
			self::ROLE_NAME,
			self::ROLE_NAME,
			self::get_capabilities()
		);
	}

	/**
	 * Activate User Role on Plugin Activation
	 *
	 * @return void
	 */
	public static function run_functions_on_activation() {
		self::remove_user_role();
		self::add_user_role();
	}

	/**
	 * Activate User Role on Plugin Activation
	 *
	 * @return void
	 */
	public static function run_functions_on_deactivation() {
		self::remove_user_role();
	}

	/**
	 * Remove user role
	 *
	 * @return void
	 */
	public static function remove_user_role() {
		remove_role( self::ROLE_NAME );
	}

	/**
	 * Flush User Role
	 *
	 * @return void
	 */
	public function refresh_role() {
		$this->remove_user_role();
		$this->add_user_role();
	}

	/**
	 * Get capabilities for user role
	 *
	 * @return array
	 */
	public static function get_capabilities() {
		return [
			// Example Capabilites
			// Read more on https://wordpress.org/support/article/roles-and-capabilities/
			'read'                          => true,
			'level_1'                       => true,

			// Allows creation of users
			'create_users'                  => true,
			'add_users'                     => true,
			'delete_users'                  => true,
			'edit_users'                    => true,
			'list_users'                    => true,
			'remove_users'                  => true,

			// Other capabilities
			'promote_users'                 => true,

			// G-theme specific
			'gt_edit_footer'                => true, // Allow admins to admin the footer
			'gt_edit_header'                => true, // Allow admins to admin the header
		];
	}
}
