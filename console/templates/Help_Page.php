<?php

// PLUGIN_NAMESPACE;

// USE_PLUGIN_NAMESPACE\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Help_Page
 */
class Help_Page {
	use Singleton;

	/**
	 * Init function
	 *
	 * @return void
	 */
	public function init() {
		if ( is_admin() ) {
			add_action( 'admin_menu', [ $this, 'add_page' ] );
		}
	}

	/**
	 * Add page
	 *
	 * @return void
	 */
	public function add_page() {
		$page_title = esc_html__( 'Help page', 'financerecruitment-millnet' );
		$menu_title = esc_html__( 'Help page', 'financerecruitment-millnet' );
		$capability = 'publish_pages';
		$menu_slug = 'financerecruitment-millnet-help';
		$callback = [ $this, 'display' ];

		// phpcs:ignore TODO REMOVE OR ADD add_submenu_page( 'edit.php?post_type=' . Post_Type::POST_TYPE, $page_title, $menu_title, $capability, $menu_slug, $callback );
	}

	/**
	 * Display the help page
	 *
	 * @return void
	 */
	public function display() {
		?>
		<div class="wrap">
		<?php

		include FINANCERECRUITMENT_MILLNET_DIR . '/partials/backend/help-page.php';

		?>
		</div>
		<?php
	}

}
