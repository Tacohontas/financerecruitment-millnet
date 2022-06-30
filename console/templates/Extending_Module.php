<?php

// PLUGIN_NAMESPACE\Modules;

use Generation_Theme_Module;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Only instantiate if Generation Theme is activated
if ( class_exists( 'Generation_Theme_Module' ) ) :

	/**
	 * Class __CLASS_NAME__
	 */
	class Module extends Generation_Theme_Module {

		/**
		 * __CLASS_NAME__ constructor.
		 *
		 * @return void
		 */
		public function __construct() {
			parent::__construct( '__MODULE_ID__' );
			$this->title = esc_html__( '__MODULE_NAME__', '__PLUGIN_NAME__' );
			$this->icon = 'umbrella';
		}

		/**
		 * @inheritDoc
		 */
		public function add_hooks() {
			parent::add_hooks();
		}

		/**
		 * Add fields to the module
		 *
		 * @return void
		 */
		public function setup_fields() {
			$this->add_fields(
				'general',
				[
					'content'    => [
						'title' => esc_html__( 'Content', '__PLUGIN_NAME__' ),
						'type'  => 'editor',
					],
					'bg_image'   => [
						'title'   => esc_html__( 'Background image', '__PLUGIN_NAME__' ),
						'type'    => 'image',
						'storage' => 'id',
					],
					'image_size' => [
						'title'   => esc_html__( 'Image size', '__PLUGIN_NAME__' ),
						'type'    => 'select',
						'options' => [
							'small'  => esc_html__( 'Small', '__PLUGIN_NAME__' ),
							'medium' => esc_html__( 'Medium', '__PLUGIN_NAME__' ),
							'large'  => esc_html__( 'Large', '__PLUGIN_NAME__' ),
						],
						'default' => 'medium',
					],
					'bg_color'   => [
						'title' => esc_html__( 'Background color', '__PLUGIN_NAME__' ),
						'type'  => 'color',
					],
				]
			);
		}

		/**
		 * @inheritDoc
		 */
		public function display( $module ) {
			parent::display();
		}

		/**
		 * Build styling for this module
		 *
		 * @param Generation_Theme_Module_Model $module Model containing the fields from the module
		 *
		 * @return string Styling for this module
		 */
		public function build_styling( $module ) {
			// return Generation_Theme_Helper::generate_styling( $styling );
		}

	}

endif;
