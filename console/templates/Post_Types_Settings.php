<?php

// PLUGIN_NAMESPACE;

// USE_PLUGIN_NAMESPACE\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Post_Types_Settings
 */
class Post_Types_Settings {
	use Singleton;
	
	/**
	 * Registered fields per post type
	 *
	 * @var array
	 */
	private static $slug_fields = [];

	/**
	 * Init function
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_init', [ $this, 'add_post_type_slug_settings' ] );
	}

	/**
	 * Add post type slug settings to permalink settings page
	 *
	 * @return void
	 */
	public function add_post_type_slug_settings() {
		$custom_post_types = get_post_types(
			[
				'_builtin' => false,
				'public'   => true,
			]
		);

		if ( ! empty( $custom_post_types ) ) {
			self::save_slug_setting();

			add_settings_section(
				'post_type_slugs_settings',
				esc_html__( 'Post type slugs', '__PLUGIN_NAME__' ),
				[ $this, 'display_slug_setting_fields' ],
				'permalink'
			);
		}
	}

	/**
	 * Save post type slug settings
	 *
	 * @return void
	 */
	public static function save_slug_setting() {
		foreach ( self::$slug_fields as $field ) {
			if ( isset( $_POST[ $field['field_name'] ] ) ) { // phpcs:ignore
				update_option( $field['field_name'], sanitize_title( $_POST[ $field['field_name'] ] ) ); // phpcs:ignore
			}
		}
	}

	/**
	 * Create slug settings field
	 *
	 * @param string $post_type
	 * @param string $post_type_label
	 * @param string $default_slug
	 *
	 * @return void
	 */
	public static function create_slug_setting_field( $post_type, $post_type_label, $default_slug ) {
		$slug = self::get_post_type_slug( $post_type );
		$slug_value = ! empty( $slug ) ? $slug : '';

		$field = [
			'label' => $post_type_label,
			'type'  => 'text',
			'atts'  => [
				'placeholder' => $default_slug,
			],
			'field_name'  => self::get_post_type_slug_name( $post_type ),
			'value'       => $slug_value
		];

		self::$slug_fields[] = $field;
	}

	/**
	 * Display setting fields for post type slugs
	 *
	 * @return void
	 */
	public function display_slug_setting_fields() {
		if ( ! empty( self::$slug_fields ) ) :
			?>
			<table class="form-table">
				<?php foreach ( self::$slug_fields as $field ) : ?>
					<tr>
						<th>
							<label for="<?php echo esc_attr( $field['field_name'] ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
						</th>
						<td>
							<?php Input_Fields::make_input( $field ); ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
			<?php
		endif;
	}

	/**
	 * Get post type slug input name
	 *
	 * @param string $post_type
	 *
	 * @return string
	 */
	public static function get_post_type_slug_name( $post_type ) {
		return '_' . $post_type . '_slug';
	}

	/**
	 * Get post type slug
	 *
	 * @param string $post_type
	 *
	 * @return string|false
	 */
	public static function get_post_type_slug( $post_type ) {
		return get_option( self::get_post_type_slug_name( $post_type ) );
	}
}