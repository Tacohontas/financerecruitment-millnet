<?php

// PLUGIN_NAMESPACE;

// USE_PLUGIN_NAMESPACE\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Options
 */
class Options {
	use Singleton;

	/**
	 * @var string OPTIONS_NAME
	 */
	const OPTIONS_NAME = '__OPTIONS_NAME__';

	/**
	 * @var string OPTIONS_CAP
	 */
	const OPTIONS_CAP = 'edit_others_posts';

	/**
	 * @var string DIRECTORY_FILE
	 */
	const DIRECTORY_FILE = '__PLUGIN_NAME__/__PLUGIN_NAME__.php';

	/**
	 * @var array $option_fields List
	 */
	private $option_fields = null;

	/**
	 * @var array $option_tabs List
	 */
	private $option_tabs = null;

	/**
	 * Init function
	 *
	 * @return void
	 */
	public function init() {
		if ( is_admin() ) {
			add_action( 'admin_menu', [ $this, 'register_options_page' ] );
			add_action( 'admin_init', [ $this, 'options_init' ] );
			add_action( 'admin_init', [ $this, 'make_options' ] );

			add_filter( 'option_page_capability_' . self::OPTIONS_NAME . '-group', [ $this, 'set_option_page_capability' ] );
			add_filter( 'plugin_action_links_' . self::DIRECTORY_FILE, [ $this, 'add_settings_button' ] );
		}
	}

	/**
	 * Adds a settings link to this plugin's entry on the plugin list
	 *
	 * @param array $links
	 * 
	 * @return array
	 */
	public function add_settings_button( $links ) {
		$links[] = "<a href='options-general.php?page=" . esc_attr( self::OPTIONS_NAME ) . "'>" . esc_html__( 'Settings' ) . '</a>';

		return $links;
	}

	/**
	 * Set capability for option page
	 *
	 * @param string $cap Default capability
	 *
	 * @return string
	 */
	public function set_option_page_capability( $cap ) {
		return self::OPTIONS_CAP;
	}

	/**
	 * Get option if exists else return default value
	 *
	 * @param string $name Name of the option
	 * @param string $default Default value
	 * @param string $suffix Suffix for fetching the value, used for language properties for instance
	 *
	 * @return string
	 */
	public static function get_option( $name, $default = '', $suffix = '' ) {
		$setting_name = sprintf( '%s_%s', self::OPTIONS_NAME, $name );
		
		if ( ! empty( $suffix ) ) {
			$setting_name .= '_' . $suffix;
		}

		$setting = get_option( $setting_name, false );

		if ( $setting === false ) {
			return $default;
		}

		return $setting;

		if ( $setting === false ) {
			return $default;
		}

		return $setting;
	}

	/**
	 * Get current language
	 *
	 * @return string|bool
	 */
	public static function get_current_language() {
		if ( ! defined( 'ICL_LANGUAGE_CODE' ) ) {
			return false;
		}

		$language = false;

		if ( strtolower( ICL_LANGUAGE_CODE ) !== 'all' ) {
			$language = strtolower( ICL_LANGUAGE_CODE );
		} else if ( function_exists( 'pll_default_language' ) ) {
			$language = strtolower( pll_default_language() );
		}

		return $language;
	}

	/**
	 * Initialize options
	 *
	 * @return void
	 */
	public function options_init() {
		if ( ! current_user_can( self::OPTIONS_CAP ) ) {
			return;
		}

		$option_fields = $this->get_option_fields();

		foreach ( $option_fields as $field_name => $field ) {
			$sanitize_callback = function ( $value ) use ( $field_name, $field ) {
				return $this->sanitize_setting( $value, $field_name, $field );
			};

			register_setting( sprintf( '%s-group', self::OPTIONS_NAME ), sprintf( '%s_%s', self::OPTIONS_NAME, $field_name ), $sanitize_callback );
		}

		$section_name = '__SETTINGS_SECTION_NAME__';
		$section_callback = [];
		$page_name = self::OPTIONS_NAME;

		add_settings_section(
			$section_name,
			false, // Skip section title because of redundancy
			$section_callback,
			$page_name
		);
	}

	/**
	 * Add option boxes
	 *
	 * @return void
	 */
	public function make_options() {
		$section_name = '__SETTINGS_SECTION_NAME__';
		$page_name = self::OPTIONS_NAME;

		$option_fields = $this->get_option_fields();

		foreach ( $option_fields as $field_name => $field ) {
			// Check if there's any tabs created
			if ( ! empty( $this->get_option_tabs() ) ) {
				// Check if this field belong to current tab
				if ( ! $this->is_field_current_tab( $field ) ) {
					continue;
				}
			}

			$field['field_name'] = sprintf( '%s_%s', self::OPTIONS_NAME, $field_name );

			if ( $field['type'] === 'hidden' ) {
				$field['class'] = 'hidden';
			}

			if ( isset( $field['conditional']['field'] ) ) {
				$field['conditional']['field'] = sprintf( '%s_%s', self::OPTIONS_NAME, $field['conditional']['field'] );
			}

			add_settings_field(
				$field_name,
				$field['label'],
				[ $this, 'make_options_callback' ],
				$page_name,
				$section_name,
				$field
			);
		}
	}

	/**
	 * Renders input fields
	 *
	 * @param array $args List of arguments
	 *
	 * @return void
	 */
	public function make_options_callback( $args ) {
		$field_name = explode( '_', $args['field_name'], 2 )[1];
		$args['value'] = isset( $args['value'] ) ? $args['value'] : self::get_option( $field_name, '' );

		Input_Fields::make_input( $args );
		?>
		<p class="description"><?php echo esc_html( $args['description'] ); ?></p>
		<?php
	}

	/**
	 * Sanitize single input
	 *
	 * @param string $value Input field value
	 * @param string $field_name Input field name
	 * @param array $field List of Input field attributes
	 *
	 * @return string cleaned input
	 */
	public function sanitize_setting( $value, $field_name, $field ) {

		// Fallback: Get option field if field doesn't belong to current tab
		if ( ! $this->is_field_current_tab( $field ) ) {
			return self::get_option( $field_name );
		}

		// Format arguments for sanitize function
		$field['field_name'] = $field_name;
		$input = [ $field_name => $value ];

		return Input_Fields::sanitize_input( $field, $input );
	}

	/**
	 * Register options page
	 *
	 * @return void
	 */
	public function register_options_page() {
		$page_title = esc_html__( '__PLUGIN_LABEL__ settings', '__PLUGIN_NAME__' );
		$menu_title = esc_html__( '__PLUGIN_LABEL__ settings', '__PLUGIN_NAME__' );
		$capability = self::OPTIONS_CAP;
		$menu_slug = self::OPTIONS_NAME;
		$callback = [ $this, 'options_page' ];

		add_options_page( $page_title, $menu_title, $capability, $menu_slug, $callback );
	}

	/**
	 * Display options page
	 *
	 * @return void
	 */
	public function options_page() {
		?>
		<div class="wrap">
			<?php settings_errors( self::OPTIONS_NAME ); ?>
			<form action="options.php" method="post">
				<?php

				// Create tabs
				if ( ! empty( $this->get_option_tabs() ) ) {
					$this->create_option_tabs();
				}

				settings_fields( sprintf( '%s-group', self::OPTIONS_NAME ) );
				do_settings_sections( self::OPTIONS_NAME );

				?>
				<input type="hidden" name="current_tab" value="<?php echo esc_attr( $this->get_current_tab() ); ?>">
				<input name="submit" type="submit" class="button button-primary" value="<?php esc_html_e( 'Save settings', '__PLUGIN_NAME__' ); ?>"/>
			</form>
		</div>
		<?php
	}

	/**
	 * Get option fields
	 *
	 * @return array
	 */
	public function get_option_fields() {
		if ( $this->option_fields !== null ) {
			return $this->option_fields;
		}

		$this->option_fields = [
			'example_field_1' => [
				'label'       => esc_html__( 'Example input option', '__PLUGIN_NAME__' ),
				'type'        => 'text',
				'tab'         => 'general',
				'description' => esc_html__( 'Short intro', '__PLUGIN_NAME__' ),
			],

			'example_field_2' => [
				'label'       => esc_html__( 'Example select option', '__PLUGIN_NAME__' ),
				'type'        => 'select',
				'tab'         => 'general',
				'description' => esc_html__( 'Favorite artist', '__PLUGIN_NAME__' ),
				'options'     => [
					'anna-bok'        => 'Anna Book',
					'mozart'          => 'Mozart',
					'marcus-martinus' => 'Marcus & Martinus',
					'wagner'          => 'Wagner',
				],
			],

			'example_field_3' => [
				'label'       => esc_html__( 'Example multiple select options', '__PLUGIN_NAME__' ),
				'type'        => 'checkbox',
				'tab'         => 'general',
				'description' => esc_html__( 'Select your best stews', '__PLUGIN_NAME__' ),
				'options'     => [
					'cassoulet'     => 'Cassoulet',
					'tagine'        => 'Tagine',
					'bouillabaisse' => 'Bouillabaisse',
					'tian'          => 'Tian',
					'ratatouille'   => 'Ratatouille',
				],
			],

			'example_field_4' => [
				'label'       => esc_html__( 'Choose a page', '__PLUGIN_NAME__' ),
				'type'        => 'page_select',
				'tab'         => 'general',
				'description' => esc_html__( 'Select a page', '__PLUGIN_NAME__' ),
			],

			'example_field_5' => [
				'label'       => esc_html__( 'Example input option', '__PLUGIN_NAME__' ),
				'type'        => 'text',
				'tab'         => 'general',
				'description' => esc_html__( 'Short intro', '__PLUGIN_NAME__' ),
			],
		];

		$current_language = self::get_current_language(); 

		// Generate all field keys with language
		if ( $current_language !== false ) {
			$this->option_fields = array_combine(
				array_map(
					function ( $key ) use ( $current_language ) {
						return sprintf( '%s_%s', $key, $current_language );
					},
					array_keys( $this->option_fields )
				),
				array_values( $this->option_fields )
			);
		}

		return $this->option_fields;
	}

	/**
	 * Get default tab
	 *
	 * @return string|null
	 */
	public function get_default_tab() {
		$default_tab = array_search( true, array_column( $this->option_tabs, 'default' ), true );

		$tab_keys = array_keys( $this->option_tabs );

		if ( $default_tab !== false ) {
			return $tab_keys[ $default_tab ];
		}

		if ( ! empty( $tab_keys ) ) {
			// If not default tab is set, return the first
			return $tab_keys[0];
		}

		return null;
	}

	/**
	 * Get current tab
	 *
	 * @return string
	 */
	public function get_current_tab() {
		// Set default tab
		$current_tab = $this->get_default_tab();

		// Check if GET-variable is set
		// phpcs:ignore
		if ( isset( $_GET['tab'] ) ) {
			// phpcs:ignore
			$current_tab = sanitize_text_field( $_GET['tab'] );
		}

		// Check if POST-variable is set
		// phpcs:ignore
		if ( isset( $_POST['current_tab'] ) ) {
			// phpcs:ignore
			$current_tab = sanitize_text_field( $_POST['current_tab'] );
		}

		// Check if current tab exists, if not set to default
		if ( ! in_array( $current_tab, array_keys( $this->get_option_tabs() ), true ) ) {
			return 'default';
		}

		// Return default as fallback if tab doesn't exist OR if GET/POST-variable isnt set'
		return $current_tab;
	}

	/**
	 * Check if input field belongs to current tab
	 *
	 * @param array $field
	 *
	 * @return bool
	 */
	public function is_field_current_tab( $field ) {
		// Get current tab
		$current_tab = $this->get_current_tab();

		// Fallback to default tab if no tab is set
		$field_tab = $this->get_default_tab();

		if ( ! empty( $field['tab'] ) ) {
			$field_tab = $field['tab'];
		}

		return $field_tab === $current_tab;
	}

	/**
	 * Get option tabs
	 *
	 * @return array
	 */
	public function get_option_tabs() {
		if ( $this->option_tabs !== null ) {
			return $this->option_tabs;
		}

		$this->option_tabs = [
			'general' => [
				'label'   => esc_html__( 'General', '__PLUGIN_NAME__' ),
				'default' => true,
			],
		];

		return $this->option_tabs;
	}

	/**
	 * Create option tabs
	 *
	 * @return void
	 */
	public function create_option_tabs() {
		$option_tabs = $this->get_option_tabs();

		?>
		<div class="gt-admin-header">
			<h1 class="wp-heading-inline"><?php echo esc_html__( '__PLUGIN_LABEL__ settings', '__PLUGIN_NAME__' ); ?></h1>
			<hr class="wp-header-end">
			<h2 class="nav-tab-wrapper wp-clearfix">
			<?php

			$current_tab = $this->get_current_tab();

			if ( array_search( true, array_column( $option_tabs, 'default' ), true ) === false ) :
				// Add a default tab label if there's no default tab set
				$active_tab = $current_tab === 'default';

				?>
				<a class="<?php echo $active_tab ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>" href="?page=<?php echo esc_attr( self::OPTIONS_NAME ) . '&tab=default'; ?>" ><?php esc_html_e( 'General', '__PLUGIN_NAME__' ); ?></a>
				<?php
				endif;
			foreach ( $option_tabs as $tab_name => $tab_attr ) :
				// Check if current iterations tab is active
				$active_tab = $current_tab === $tab_name;
				?>
				<a class="<?php echo $active_tab ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>" href="?page=<?php echo esc_attr( self::OPTIONS_NAME ) . '&tab=' . esc_attr( $tab_name ); ?>"><?php echo esc_html( $tab_attr['label'] ); ?></a>
				<?php
			endforeach;
			?>
			</h2>
		</div>
		<?php
	}
}
