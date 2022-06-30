<?php

// PLUGIN_NAMESPACE;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Post_Types_Helper
 */
class Post_Types_Helper {
	
	/**
	 * Registered metaboxes per post type
	 *
	 * @var array
	 */
	private static $registered_metaboxes = [];

	/**
	 * Array with fields per post type
	 *
	 * @var array
	 */
	private static $fields = [];

	/**
	 * Register a post type
	 *
	 * @param string $post_type Slug of the post type being registered
	 * @param string $slug Rewrite slug for the post type being registered
	 * @param string $singular Label of the taxonomy in singular
	 * @param string $plural Label of the taxonomy in plural
	 * @param array|null $args Other arguments to pass to the register post type function
	 *
	 * @return void
	 */
	public static function register_post_type( $post_type, $slug, $singular, $plural, $args = [] ) {
		$labels = [
			'name'               => ucfirst( $plural ),
			'singular_name'      => ucfirst( $singular ),
			'edit_item'          => esc_html__( 'Edit', '__PLUGIN_NAME__' ),
			'menu_name'          => ucfirst( $plural ),
			'name_admin_bar'     => ucfirst( $singular ),
			'add_new'            => esc_html__( 'Add New', '__PLUGIN_NAME__' ),
			/* translators: %s: name of post type */
			'add_new_item'       => sprintf( esc_html__( 'Add New %s', '__PLUGIN_NAME__' ), $singular ),
			/* translators: %s: name of post type */
			'new_item'           => sprintf( esc_html__( 'New %s', '__PLUGIN_NAME__' ), $singular ),
			/* translators: %s: name of post type */
			'edit_item'          => sprintf( esc_html__( 'Edit %s', '__PLUGIN_NAME__' ), $singular ),
			/* translators: %s: name of post type */
			'view_item'          => sprintf( esc_html__( 'View %s', '__PLUGIN_NAME__' ), $singular ),
			/* translators: %s: name of post type */
			'all_items'          => sprintf( esc_html__( 'All %s', '__PLUGIN_NAME__' ), $plural ),
			/* translators: %s: name of post type */
			'search_items'       => sprintf( esc_html__( 'Search %s', '__PLUGIN_NAME__' ), $plural ),
			/* translators: %s: name of post type */
			'parent_item_colon'  => sprintf( esc_html__( 'Parent %s:', '__PLUGIN_NAME__' ), $singular ),
			/* translators: %s: name of post type */
			'not_found'          => sprintf( esc_html__( 'No %s found.', '__PLUGIN_NAME__' ), $plural ),
			/* translators: %s: name of post type */
			'not_found_in_trash' => sprintf( esc_html__( 'No %s found in Trash.', '__PLUGIN_NAME__' ), $plural ),
		];

		$own_slug = Post_Types_Settings::get_post_type_slug( $post_type );
		$slug = ! empty( $own_slug ) ? $own_slug : $slug; 

		$args = array_merge(
			[
				'labels'      => $labels,
				'public'      => true,
				'has_archive' => false,
				'rewrite'     => [
					'slug'       => $slug,
					'with_front' => false,
				],
				'supports'    => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes' ],
				'menu_icon'   => 'dashicons-admin-page',
			],
			$args
		);
		
		register_post_type( $post_type, $args );

		if ( $args['public'] ) {
			Post_Types_Settings::create_slug_setting_field( $post_type, $singular, $slug ); 
		}
	}

	/**
	 * Registers taxonomy
	 *
	 * @param string $taxonomy Taxonomy slug
	 * @param string $post_type Post-type slug that the taxonomy should be attached to
	 * @param string $slug Taxonomy slug
	 * @param string $singular Label of the taxonomy in singular
	 * @param string $plural Label of the taxonomy in plural
	 * @param array $args Other arguments to pass to the register taxonomy function
	 *
	 * @return void
	 */
	public static function register_taxonomy( $taxonomy, $post_type, $slug, $singular, $plural, $args = [] ) {
		$labels = [
			'name'                       => ucfirst( $plural ),
			'singular_name'              => ucfirst( $singular ),
			/* translators: %s: name of post type */
			'search_items'               => sprintf( esc_html__( 'Search %s', '__PLUGIN_NAME__' ), $plural ),
			/* translators: %s: name of post type */
			'popular_items'              => sprintf( esc_html__( 'Popular %s', '__PLUGIN_NAME__' ), $plural ),
			/* translators: %s: name of post type */
			'all_items'                  => sprintf( esc_html__( 'All %s', '__PLUGIN_NAME__' ), $plural ),
			/* translators: %s: name of post type */
			'parent_item'                => sprintf( esc_html__( 'Parent %s', '__PLUGIN_NAME__' ), $singular ),
			/* translators: %s: name of post type */
			'parent_item_colon'          => sprintf( esc_html__( 'Parent %s:', '__PLUGIN_NAME__' ), $singular ),
			/* translators: %s: name of post type */
			'edit_item'                  => sprintf( esc_html__( 'Edit %s', '__PLUGIN_NAME__' ), $singular ),
			/* translators: %s: name of post type */
			'update_item'                => sprintf( esc_html__( 'Update %s', '__PLUGIN_NAME__' ), $singular ),
			/* translators: %s: name of post type */
			'add_new_item'               => sprintf( esc_html__( 'Add new %s', '__PLUGIN_NAME__' ), $singular ),
			/* translators: %s: name of post type */
			'new_item_name'              => sprintf( esc_html__( 'New %s', '__PLUGIN_NAME__' ), $singular ),
			/* translators: %s: name of post type */
			'separate_items_with_commas' => sprintf( esc_html__( 'Separate %s with commas', '__PLUGIN_NAME__' ), $plural ),
			/* translators: %s: name of post type */
			'add_or_remove_items'        => sprintf( esc_html__( 'Add or remove %s', '__PLUGIN_NAME__' ), $plural ),
			/* translators: %s: name of post type */
			'choose_from_most_used'      => sprintf( esc_html__( 'Choose from the most used %s', '__PLUGIN_NAME__' ), $plural ),
			'menu_name'                  => ucfirst( $plural ),
		];

		$args = array_merge(
			[
				'hierarchical'          => true,
				'labels'                => $labels,
				'show_ui'               => true,
				'update_count_callback' => '_update_post_term_count',
				'query_var'             => true,
				'public'                => false,
				'show_ui'               => true,
				'show_in_nav_menus'     => true,
				'show_admin_column'     => true,
                'rewrite'     => [
                    'slug'       => $slug,
                    'with_front' => false,
                ],
				'sort'                  => true,
			],
			$args
		);

		register_taxonomy( $taxonomy, $post_type, $args );
	}

	/**
	 * Get registerd fields for post type
	 *
	 * @param string|int $post_type
	 * 
	 * @return array
	 */
	public static function get_meta_fields( $post_type ) {
		// Check if the user passed an id instead of string
		if ( is_int( $post_type ) ) {
			$post_type = get_post_type( $post_type );
		}

		return self::$registered_metaboxes[ $post_type ] ?: [];
	}

	/**
	 * Add fields to global array
	 *
	 * @param string $post_type
	 * @param array $fields
	 * @param string $parent Parent key
	 * 
	 * @return void
	 */
	public static function add_fields( $post_type, $fields, $parent = '' ) {
		if ( ! isset( self::$fields[ $post_type ] ) ) {
			self::$fields[ $post_type ] = [];
		}

		// Loop through the fields
		foreach ( $fields as $field_key => $field ) {

			// Save the type of the field
			self::$fields[ $post_type ][ $field_key ] = [
				'type' => $field['type']
			];

			// If parent id is sent, add the fields as a "fields"-key on that entry
			if ( ! empty( $parent ) ) {
				if ( ! isset( self::$fields[ $post_type ][ $parent ] ) ) {
					self::$fields[ $post_type ][ $parent ] = [
						'fields' => []
					];
				}

				self::$fields[ $post_type ][ $parent ]['fields'][] = $field_key;
			}

			// If the type is repeatable, call this function again to add the sub-fields
			if ( $field['type'] === 'repeatable' ) {
				self::add_fields( $post_type, $field['fields'], $field_key );
			}
		}
	}

	/**
	 * Get the field information such as type and sub fields
	 *
	 * @param string $post_type
	 * @param string $field_name
	 * 
	 * @return array|bool
	 */
	public static function get_field_info( $post_type, $field_name ) {
		// Remove the first underscore
		$field_name = ltrim( $field_name, '_' );
		
		if ( isset( self::$fields[ $post_type ], self::$fields[ $post_type ][ $field_name ] ) ) {
			return self::$fields[ $post_type ][ $field_name ];
		}

		return false;
	}

	/**
	 * Get metadata
	 *
	 * @param int $post_id
	 * @param string $meta_key
	 * 
	 * @return mixed
	 */
	public static function get_post_meta( $post_id, $meta_key ) {
		$post_type = get_post_type( $post_id );
		$field_info = self::get_field_info( $post_type, $meta_key );

		$post_meta = '';

		// Make sure the sub-fields are fetched if the type is "repeatable"
		if ( $field_info['type'] === 'repeatable' ) {
			$post_meta = [];

			if ( ! empty( $field_info['fields'] ) ) {
				$count = (int) get_post_meta( $post_id, $meta_key, true );

				// The $count variable contains the number of rows to fetch
				for ( $i = 0; $i < $count; $i++ ) { 
					foreach ( $field_info['fields'] as $sub_field ) {
						$post_meta[ $i ][ $sub_field ] = get_post_meta( $post_id, $meta_key . '_' . $i . '_' . $sub_field, true );
					}
				}
			}
		} else {
			$post_meta = get_post_meta( $post_id, $meta_key, true );
		}

		return $post_meta;
	}

	/**
	 * Register meta boxes with fields
	 * 
	 * @param array $metaboxes
	 * @param string $post_type
	 *
	 * @return void
	 */
	public static function register_fields( $metaboxes, $post_type ) {
		// Add the fields 
		if ( ! empty( $metaboxes ) ) {
			foreach ( $metaboxes as $metabox ) {
				self::add_fields( $post_type, $metabox['fields'] );
			}
		}

		// Make sure an array is present for this post type 
		if ( ! isset( self::$registered_metaboxes[ $post_type ] ) ) {
			self::$registered_metaboxes[ $post_type ] = [];	
		}

		// Merge with already present metaboxes
		self::$registered_metaboxes[ $post_type ] = array_merge(
			$metaboxes,
			self::$registered_metaboxes[ $post_type ]
		);
	}

	/**
	 * Create meta boxes based on the meta box array
	 * 
	 * @param array $meta_fields List of meta boxes
	 * @param string $post_type Post type to add meta boxes to
	 *
	 * @return void
	 */
	public static function make_meta_boxes( $post_type ) {
		$meta_fields = self::get_meta_fields( $post_type );

		foreach ( $meta_fields as $meta_field_id => $meta_field_data ) {
			$meta_field_id = ! is_int( $meta_field_id ) ? $meta_field_id : sprintf( '%s_metabox', sanitize_title( $meta_field_data['title'] ) );

			add_meta_box(
				$meta_field_id,
				$meta_field_data['title'],
				[ __CLASS__, 'make_meta_boxes_callback' ],
				$post_type,
				( isset( $meta_field_data['position'] ) ? $meta_field_data['position'] : 'normal' ),
				( isset( $meta_field_data['priority'] ) ? $meta_field_data['priority'] : 'default' ),
				$meta_field_data
			);

			add_filter( 'postbox_classes_' . $post_type . '_' . $meta_field_id, [ __CLASS__, 'postbox_classes' ], 999 );
		}
	}

	/**
	 * Adds the same class on all custom metaboxes
	 *
	 * @param array $classes Array of metabox classes
	 * 
	 * @return array
	 */
	public static function postbox_classes( $classes ) {
		return array_merge( $classes, [ 'gen-metabox' ] );
	}

	/**
	 * Callback that displays a metabox
	 *
	 * @param WP_Post $post Post where the meta box is displayed
	 * @param array   $args Array containing arguments for meta box display
	 *
	 * @return void
	 */
	public static function make_meta_boxes_callback( $post, $meta_field_data ) {
		$args = $meta_field_data['args'];
		$fields = $args['fields'];
		$position = $args['position'];
		?>
		<table class="form-table financerecruitment-millnet-metabox-table">
			<?php
			foreach ( $fields as $name => $meta_field ) :
				$field_name = sprintf( '_%s', $name );
				$meta_field['field_name'] = $field_name;
				$meta_field['value'] = self::get_post_meta( $post->ID, $field_name );				$description = isset( $meta_field['description'] ) ? $meta_field['description'] : '';
				$tooltip = isset( $meta_field['tooltip'] ) ? $meta_field['tooltip'] : '';

				if ( isset( $meta_field['conditional']['field'] ) ) :
					$meta_field['conditional']['field'] = '_' . $meta_field['conditional']['field'];
				endif;

				?>
				<tr class="<?php echo esc_attr( $meta_field['type'] === 'hidden' ? 'hidden' : '' ); ?>">
					<th scope="row">
						<label for="<?php echo esc_attr( $field_name ); ?>">
							<?php echo esc_attr( $meta_field['label'] ); ?>
						</label>
					</th>
					<td class="financerecruitment-millnet-metabox-field">
						<?php
						wp_nonce_field( $field_name, $field_name . '_nonce' );
						Input_Fields::make_input( $meta_field );

						if( ! empty( $tooltip ) ) :
							?><div class="financerecruitment-millnet-tooltip">
								<i class="fas fa-question-circle"></i>
								<div class="tooltip">
									<p><?php echo esc_html( $tooltip ); ?></p>
								</div>
							</div><?php
						endif;

						echo wp_kses_post( $description ? '<p class="description">' . $description . '</p>' : '' );
						?>
					</td>
				</tr>
			<?php
			endforeach;
			?>
		</table>
		<?php
	}

	/**
	 * Save meta on custom post type
	 *
	 * @param string $post_id
	 *
	 * @return void
	 */
	public static function save_meta( $post_id ) {
		// Only save meta fields when the save is not automatic
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$meta_fields = self::get_meta_fields( $post_id );

		foreach ( $meta_fields as $meta_field_data ) {
			$fields = isset( $meta_field_data['fields'] ) ? $meta_field_data['fields'] : [];
		
			foreach ( $fields as $name => $field ) {
				$field_name = sprintf( '_%s', $name );
				$nonce_name = sprintf( '%s_nonce', $field_name );
				$field['field_name'] = $field_name;

				if ( ! isset( $_POST[ $nonce_name ] ) || ! wp_verify_nonce( $_POST[ $nonce_name ], $field_name ) ) {
					continue;
				}

				if ( $field['type'] === 'repeatable' ) {
					// Remove all meta that begins with this meta key since new data is comming
					/** @var \wpdb $wpdb */
					global $wpdb;
					$query = $wpdb->prepare( 'DELETE FROM ' . $wpdb->postmeta . ' WHERE post_id = %d', $post_id );
					$query .= $wpdb->prepare( ' AND meta_key LIKE %s', $field_name . '_%' );
					//phpcs:ignore
					$count = $wpdb->query( $query );

					$full_array = $_POST[ $field_name ];

					// Update the count of the original key
					update_post_meta( $post_id, $field_name, count( $full_array ) );

					foreach ( $field['fields'] as $row_field_name =>  $row_field ) {
						$row_field['field_name'] = $row_field_name;
						
						if ( ! empty( $full_array ) ) {
							foreach ( $full_array as $index => $row ) {
								$meta_value = Input_Fields::sanitize_input( $row_field, $row );
								
								if ( $meta_value !== null ) {
									$meta_key = $field_name . '_' . $index . '_' . $row_field_name;
									
									// Important to use "add" and not "update" to set the value
									add_post_meta( $post_id, $meta_key, $meta_value );
								} 
							}
						}
					}
				} else {
					// Update as a regular meta value
					$meta_value = Input_Fields::sanitize_input( $field, $_POST );
			
					if ( $meta_value !== null ) {
						update_post_meta( $post_id, $field_name, $meta_value );
					}
				}
			}
		}
	}
}
