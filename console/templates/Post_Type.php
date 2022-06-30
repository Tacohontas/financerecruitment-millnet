<?php

// PLUGIN_NAMESPACE\Post_Types;

// USE_PLUGIN_NAMESPACE\Traits\Singleton;
// USE_PLUGIN_NAMESPACE\Post_Types_Helper;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class __CLASS_NAME__
 */
class Post_Type {
	use Singleton;

	/**
	 * Post type name
	 */
	const POST_TYPE = '__POST_TYPE_NAME__';

	/**
	 * Post type slug
	 */
	const POST_TYPE_SLUG = '__POST_TYPE_SLUG__';

	/**
	 * Taxonomy name
	 */
	const TAXONOMY = '__POST_TYPE_NAME___taxonomy';

	/**
	 * @var array List of meta field
	 */
	private $meta_fields = null;

	/**
	 * Rewrite rules for the post type
	 *
	 * @var array
	 */
	private static $post_type_rewrite_rules = [];

	/**
	 * Init function
	 *
	 * @return void
	 */
	public function init() {
		// Actions for  registrations
		add_action( 'init', [ $this, 'setup_post_types' ] );
		add_action( 'init', [ $this, 'setup_taxonomies' ] );
		add_action( 'init', [ $this, 'register_fields' ] );
		
		// Actions for meta boxes and fields
		add_action( 'add_meta_boxes', [ $this, 'make_meta_boxes' ] );
		add_action( 'save_post_' . self::POST_TYPE, [ $this, 'save_meta' ], 10, 1 );
		
		// Filter for removing post type pagination rewrite
		add_filter( self::POST_TYPE . '_rewrite_rules', [ $this, 'store_post_type_rewrite' ] );
		add_filter( 'rewrite_rules_array', [ $this, 'add_post_rewrite_rule' ] );
		add_filter( 'redirect_canonical', [ $this, 'allow_canonical_pagination' ] );

		// Allow body classes on post type
		add_filter( 'gt_body_classes_metabox_post_type', [ $this, 'allow_body_classes' ] );
	}

	/**
	 * Allow pagination to take place on single pages
	 *
	 * @param string $redirect_url
	 * @return string|false
	 */
	public function allow_canonical_pagination( string $redirect_url ) {
		if (
			get_query_var( 'paged' ) > 1 &&
			is_singular( self::POST_TYPE ) &&
			get_query_var( 'post_count' ) !== 0
		) {
			return false;
		}

		return $redirect_url;
	}

	/**
	 * Add the rewrite rules to the array. This is used to get the rules in the correct order
	 *
	 * @param array $page_rewrite
	 * @return array
	 */
	public function add_post_rewrite_rule( array $page_rewrite ) {
		// Remove the filter to allow polylang to get the actuall rules
		remove_filter( self::POST_TYPE . '_rewrite_rules', [ $this, 'store_post_type_rewrite' ] );
		$post_type_rules = apply_filters( self::POST_TYPE . '_rewrite_rules', self::$post_type_rewrite_rules );
		add_filter( self::POST_TYPE . '_rewrite_rules', [ $this, 'store_post_type_rewrite' ] );

		$matched_rule = '';
		$index = 0;

		// Check for the pagination rewrite rule that has to come before our rule
		if ( ! empty( $page_rewrite ) ) {
			foreach ( $page_rewrite as $pattern => $rule ) {
				++$index;

				$query = str_replace( 'index.php?', '', $rule );
				$params = explode( '&', $query );

				if ( ! empty( $params ) && count( $params ) === 2 ) {
					$param_keys = [];

					foreach ( $params as $param ) {
						$param_keys[] = explode( '=', $param )[0];
					}

					// With only theese as a match, we know it's the rule
					if ( empty( array_diff( [ 'pagename', 'paged' ], $param_keys ) ) ) {
						$matched_rule = $pattern;
						break;
					}
				}
			}
		}

		// A matched rule means that we can insert our rule in the middle
		if ( ! empty( $matched_rule ) ) {
			$page_rewrite = array_slice( $page_rewrite, 0, $index, true ) +
			$post_type_rules +
			array_slice( $page_rewrite, $index, count( $page_rewrite ) - $index, true );
		} else {
			// Our pagination rules wasn't found, prepend it to the top
			$page_rewrite = $post_type_rules + $page_rewrite;
		}

		return $page_rewrite;
	}

	/**
	 * Store the rewrite rules for this post type for later use
	 *
	 * @param array $rewrite_rules
	 * @return array
	 */
	public function store_post_type_rewrite( array $rewrite_rules ) {
		if ( ! empty( $rewrite_rules ) ) {
			self::$post_type_rewrite_rules = $rewrite_rules;
		}

		return [];
	}

	/**
	 * Register post type
	 *
	 * @return void
	 */
	public function setup_post_types() {
		Post_Types_Helper::register_post_type(
			self::POST_TYPE,
			self::POST_TYPE_SLUG,
			esc_html__( '__POST_TYPE_LABEL__', '__PLUGIN_NAME__' ),
			esc_html__( '__POST_TYPE_LABEL__s', '__PLUGIN_NAME__' ),
			[
				'menu_icon' => 'dashicons-admin-page',
			]
		);
	}

	/**
	 * Sets up taxonomies
	 *
	 * @return void
	 */
	public function setup_taxonomies() {
		Post_Types_Helper::register_taxonomy(
			self::TAXONOMY,
			self::POST_TYPE,
			'__POST_TYPE_NAME___taxonomy',
			esc_html_x( '__POST_TYPE_LABEL__ taxonomy', 'taxonomy singular name', '__PLUGIN_NAME__' ),
			esc_html_x( '__POST_TYPE_LABEL__ taxonomy', 'taxonomy general name', '__PLUGIN_NAME__' )
		);
	}

	/**
	 * Register meta fields
	 *
	 * @return void
	 */
	public function register_fields() {
		Post_Types_Helper::register_fields( $this->get_meta_fields(), self::POST_TYPE );
	}

	/*
	 * Create meta boxes based on the meta box array
	 *
	 * @return void
	 */
	public function make_meta_boxes() {
		Post_Types_Helper::make_meta_boxes( self::POST_TYPE );
	}

	/**
	 * Save meta on custom post type
	 *
	 * @param string $post_id
	 *
	 * @return void
	 */
	public function save_meta( $post_id ) {
		Post_Types_Helper::save_meta( $post_id );
	}

	/**
	 * Allow the post type to have extra body classes
	 *
	 * @param string[] $post_types
	 *
	 * @return string[]
	 */
	public function allow_body_classes( $post_types ) {
		$post_types[] = self::POST_TYPE;

		return $post_types;
	}

	/**
	 * Get meta fields
	 *
	 * @return array
	 */
	public function get_meta_fields() {
		if ( $this->meta_fields !== null ) {
			return $this->meta_fields;
		}

		$this->meta_fields = apply_filters(
			self::POST_TYPE . '_meta_fields',
			[
				[
					'title'    => esc_html__( 'First metabox', '__PLUGIN_NAME__' ),
					'position' => 'normal',
					'fields'   => [
						'example_field_1'  => [
							'label'       => esc_html__( 'Example field text', '__PLUGIN_NAME__' ),
							'type'        => 'text',
							'atts'        => [
								'placeholder' => esc_html__( 'Some input text', '__PLUGIN_NAME__' ),
							],
							'description' => esc_html__( 'Enter some text', '__PLUGIN_NAME__' ),
							'tooltip' 	  => esc_html__( 'Enter some tooltip text', '__PLUGIN_NAME__' )
						],
						'example_field_2'  => [
							'label' => esc_html__( 'Example field date', '__PLUGIN_NAME__' ),
							'type'  => 'date',
						],
						'example_field_3'  => [
							'label' => esc_html__( 'Example field time', '__PLUGIN_NAME__' ),
							'type'  => 'time',
						],
						'example_field_5'  => [
							'label' => esc_html__( 'Example field email', '__PLUGIN_NAME__' ),
							'type'  => 'email',
							'atts'  => [
								'placeholder' => esc_html__( 'name@mail.com', '__PLUGIN_NAME__' ),
							],
						],
						'example_field_6'  => [
							'label' => esc_html__( 'Example field tel', '__PLUGIN_NAME__' ),
							'type'  => 'tel',
							'atts'  => [
								'placeholder' => esc_html__( '070-123 12 34', '__PLUGIN_NAME__' ),
							],
						],
						'example_field_7'  => [
							'label' => esc_html__( 'Example field url', '__PLUGIN_NAME__' ),
							'type'  => 'url',
							'atts'  => [
								'placeholder' => esc_html__( 'https://domain.com', '__PLUGIN_NAME__' ),
							],
						],
						'example_field_8'  => [
							'label' => esc_html__( 'Example field number', '__PLUGIN_NAME__' ),
							'type'  => 'number',
							'atts'  => [
								'placeholder' => esc_html__( '56', '__PLUGIN_NAME__' ),
								'min'         => 0,
								'max'         => 100,
								'step'        => 0.5,
							],
						],
						'example_field_9'  => [
							'label'       => esc_html__( 'Example field checkbox', '__PLUGIN_NAME__' ),
							'type'        => 'checkbox',
							'description' => esc_html__( 'Check this box if you want', '__PLUGIN_NAME__' ),
						],
						'example_field_10' => [
							'label'   => esc_html__( 'Example field checkboxes', '__PLUGIN_NAME__' ),
							'type'    => 'checkbox',
							'options' => [
								'red'    => esc_html__( 'Red', '__PLUGIN_NAME__' ),
								'green'  => esc_html__( 'Green', '__PLUGIN_NAME__' ),
								'blue'   => esc_html__( 'Blue', '__PLUGIN_NAME__' ),
								'yellow' => esc_html__( 'Yellow', '__PLUGIN_NAME__' ),
							],
						],
						'example_field_11' => [
							'label'   => esc_html__( 'Example field radio', '__PLUGIN_NAME__' ),
							'type'    => 'radio',
							'options' => [
								'apples'     => esc_html__( 'Apples', '__PLUGIN_NAME__' ),
								'oranges'    => esc_html__( 'Oranges', '__PLUGIN_NAME__' ),
								'bananas'    => esc_html__( 'Bananas', '__PLUGIN_NAME__' ),
								'pineapples' => esc_html__( 'Pineapples', '__PLUGIN_NAME__' ),
							],
						],
						'example_field_12' => [
							'label'   => esc_html__( 'Example field select', '__PLUGIN_NAME__' ),
							'type'    => 'select',
							'options' => [
								'mozart'          => esc_html__( 'Mozart', '__PLUGIN_NAME__' ),
								'markus-martinus' => esc_html__( 'Markus & Martinus', '__PLUGIN_NAME__' ),
								'wagner'          => esc_html__( 'Wagner', '__PLUGIN_NAME__' ),
							],
						],
						'example_field_13' => [
							'label' => esc_html__( 'Example field textarea', '__PLUGIN_NAME__' ),
							'type'  => 'textarea',
						],
						'example_field_14' => [
							'label'  => esc_html__( 'Example field repeatable', '__PLUGIN_NAME__' ),
							'type'   => 'repeatable',
							'fields' => [
								'example_field_14_1' => [
									'label' => esc_html__( 'Example field textarea', '__PLUGIN_NAME__' ),
									'type'  => 'textarea',
									'atts'  => [
										'placeholder' => esc_html__( 'Text paragraph', '__PLUGIN_NAME__' ),
									],
								],
								'example_field_14_2' => [
									'label'   => esc_html__( 'Example field select', '__PLUGIN_NAME__' ),
									'type'    => 'select',
									'options' => [
										'mozart'          => esc_html__( 'Mozart', '__PLUGIN_NAME__' ),
										'markus-martinus' => esc_html__( 'Markus & Martinus', '__PLUGIN_NAME__' ),
										'wagner'          => esc_html__( 'Wagner', '__PLUGIN_NAME__' ),
									],
								],
								'example_field_14_3' => [
									'label'   => esc_html__( 'Example field checkboxes', '__PLUGIN_NAME__' ),
									'type'    => 'checkbox',
									'options' => [
										'red'    => esc_html__( 'Red', '__PLUGIN_NAME__' ),
										'green'  => esc_html__( 'Green', '__PLUGIN_NAME__' ),
										'blue'   => esc_html__( 'Blue', '__PLUGIN_NAME__' ),
										'yellow' => esc_html__( 'Yellow', '__PLUGIN_NAME__' ),
									],
								],
							],
						],
					],
				],
				[
					'title'    => esc_html__( 'Second metabox', '__PLUGIN_NAME__' ),
					'position' => 'side',
					'fields'   => [
						'example_field_15' => [
							'label' => esc_html__( 'Example field text', '__PLUGIN_NAME__' ),
							'type'  => 'text',
							'atts'  => [
								'placeholder' => esc_html__( 'Some input text', '__PLUGIN_NAME__' ),
								'readonly'
							],
						],
						'example_field_16' => [
							'label' => esc_html__( 'Example field date', '__PLUGIN_NAME__' ),
							'type'  => 'date',
						],
						'example_field_17' => [
							'label' => esc_html__( 'Example field time', '__PLUGIN_NAME__' ),
							'type'  => 'time',
						],
						'example_field_18' => [
							'label' => esc_html__( 'Example field media', '__PLUGIN_NAME__' ),
							'type'  => 'media',
							'mime_types' => [ 
								'image/jpeg', 
								'image/png', 
								'application/pdf' 
							]
						],
					],
				],
			]
		);

		return $this->meta_fields;
	}
}
