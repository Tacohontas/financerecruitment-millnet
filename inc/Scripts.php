<?php

namespace Financerecruitment_Millnet;

use Financerecruitment_Millnet\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Scripts
 */
class Scripts {
	use Singleton;

	/**
	 * Init function
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_backend_scripts' ] );
	}

	/**
	 * Enqueues scripts and styles for the frontend
	 *
	 * @return  void
	 */
	public function enqueue_frontend_scripts() {
		wp_enqueue_style( 'financerecruitment-millnet', plugins_url( 'assets/css/frontend/application.min.css', FINANCERECRUITMENT_MILLNET_FILE ), false, Plugin::VERSION );
		wp_enqueue_script( 'financerecruitment-millnet', plugins_url( 'assets/js/frontend/application.min.js', FINANCERECRUITMENT_MILLNET_FILE ), [ 'jquery' ], Plugin::VERSION, true );
	}

	/**
	 * Enqueues scripts and styles for the backend
	 *
	 * @param string $hook The current admin hook
	 *
	 * @return void
	 */
	public function enqueue_backend_scripts( $hook ) {
		wp_enqueue_editor();

		global $pagenow;
		if ( in_array( $pagenow, [ 'post.php', 'options-general.php', 'options.php' ], true ) ) {
			wp_enqueue_media();
			wp_enqueue_script( 'ace-editor', plugins_url( 'assets/js/ace/ace.js', FINANCERECRUITMENT_MILLNET_FILE ), [], '1.3.1', true );
			wp_enqueue_script( 'ace-language_tools', plugins_url( '/assets/js/ace/ext-language_tools.js', FINANCERECRUITMENT_MILLNET_FILE ), [], '1.3.1', true );
		}

		wp_enqueue_style( 'fontawesome', plugins_url( 'assets/fontawesome/css/all.min.css', FINANCERECRUITMENT_MILLNET_FILE ), false, '5.10.1' );
		wp_enqueue_style( 'financerecruitment-millnet', plugins_url( 'assets/css/backend/application.min.css', FINANCERECRUITMENT_MILLNET_FILE ), false, Plugin::VERSION );
		wp_enqueue_script( 'financerecruitment-millnet', plugins_url( 'assets/js/backend/application.min.js', FINANCERECRUITMENT_MILLNET_FILE ), [ 'jquery' ], Plugin::VERSION, true );

		$financerecruitment_millnet_vars = [
			'CHOOSE_MEDIA'  => __( 'Choose media', 'financerecruitment-millnet' ),
			'editor_styles' => class_exists( 'Generation_Theme_Scripts' ) ? \Generation_Theme_Scripts::editor_styles() : [],
			'style_formats' => class_exists( 'Generation_Theme_Support' ) ? \Generation_Theme_Support::style_formats() : [],
			'i18n'          => [
				'field_not_found' => __( 'The field could not be found. If you tried targeting radio and/or checkboxes you might need to add "[]" at the end of "field" in the PHP', 'financerecruitment-millnet' ),
			],
		];

		wp_localize_script(
			'financerecruitment-millnet',
			'financerecruitment_millnet_vars',
			$financerecruitment_millnet_vars
		);
	}

}
