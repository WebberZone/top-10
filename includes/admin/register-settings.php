<?php
/**
 * Register settings.
 *
 * Functions to register, read, write and update settings.
 * Portions of this code have been inspired by Easy Digital Downloads, WordPress Settings Sandbox, etc.
 *
 * @link  https://webberzone.com
 * @since 2.5.0
 *
 * @package Top 10
 * @subpackage Admin/Register_Settings
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since 2.5.0
 *
 * @param string $key Key of the option to fetch.
 * @param mixed  $default Default value to fetch if option is missing.
 * @return mixed
 */
function tptn_get_option( $key = '', $default = null ) {

	global $tptn_settings;

	if ( is_null( $default ) ) {
		$default = tptn_get_default_option( $key );
	}

	$value = ! empty( $tptn_settings[ $key ] ) ? $tptn_settings[ $key ] : $default;

	/**
	 * Filter the value for the option being fetched.
	 *
	 * @since 2.5.0
	 *
	 * @param mixed $value  Value of the option
	 * @param mixed $key  Name of the option
	 * @param mixed $default Default value
	 */
	$value = apply_filters( 'tptn_get_option', $value, $key, $default );

	/**
	 * Key specific filter for the value of the option being fetched.
	 *
	 * @since 2.5.0
	 *
	 * @param mixed $value  Value of the option
	 * @param mixed $key  Name of the option
	 * @param mixed $default Default value
	 */
	return apply_filters( 'tptn_get_option_' . $key, $value, $key, $default );
}


/**
 * Update an option
 *
 * Updates an tptn setting value in both the db and the global variable.
 * Warning: Passing in an empty, false or null string value will remove
 *          the key from the tptn_options array.
 *
 * @since 2.5.0
 *
 * @param  string          $key   The Key to update.
 * @param  string|bool|int $value The value to set the key to.
 * @return boolean   True if updated, false if not.
 */
function tptn_update_option( $key = '', $value = false ) {

	// If no key, exit.
	if ( empty( $key ) ) {
		return false;
	}

	// If no value, delete.
	if ( empty( $value ) ) {
		$remove_option = tptn_delete_option( $key );
		return $remove_option;
	}

	// First let's grab the current settings.
	$options = get_option( 'tptn_settings' );

	/**
	 * Filters the value before it is updated
	 *
	 * @since 2.5.0
	 *
	 * @param  string|bool|int $value The value to set the key to
	 * @param  string          $key   The Key to update
	 */
	$value = apply_filters( 'tptn_update_option', $value, $key );

	// Next let's try to update the value.
	$options[ $key ] = $value;
	$did_update = update_option( 'tptn_settings', $options );

	// If it updated, let's update the global variable.
	if ( $did_update ) {
		global $tptn_settings;
		$tptn_settings[ $key ] = $value;
	}
	return $did_update;
}


/**
 * Remove an option
 *
 * Removes an tptn setting value in both the db and the global variable.
 *
 * @since 2.5.0
 *
 * @param  string $key The Key to update.
 * @return boolean   True if updated, false if not.
 */
function tptn_delete_option( $key = '' ) {

	// If no key, exit.
	if ( empty( $key ) ) {
		return false;
	}

	// First let's grab the current settings.
	$options = get_option( 'tptn_settings' );

	// Next let's try to update the value.
	if ( isset( $options[ $key ] ) ) {
		unset( $options[ $key ] );
	}

	$did_update = update_option( 'tptn_settings', $options );

	// If it updated, let's update the global variable.
	if ( $did_update ) {
		global $tptn_settings;
		$tptn_settings = $options;
	}
	return $did_update;
}


/**
 * Register settings function
 *
 * @since 2.5.0
 *
 * @return void
 */
function tptn_register_settings() {

	if ( false === get_option( 'tptn_settings' ) ) {
		add_option( 'tptn_settings', tptn_settings_defaults() );
	}

	foreach ( tptn_get_registered_settings() as $section => $settings ) {

		add_settings_section(
			'tptn_settings_' . $section, // ID used to identify this section and with which to register options, e.g. tptn_settings_general.
			__return_null(), // No title, we will handle this via a separate function.
			'__return_false', // No callback function needed. We'll process this separately.
			'tptn_settings_' . $section  // Page on which these options will be added.
		);

		foreach ( $settings as $setting ) {

			$args = wp_parse_args(
				$setting, array(
					'section'          => $section,
					'id'               => null,
					'name'             => '',
					'desc'             => '',
					'type'             => null,
					'options'          => '',
					'max'              => null,
					'min'              => null,
					'step'             => null,
					'size'             => null,
					'field_class'      => '',
					'field_attributes' => '',
				)
			);

			add_settings_field(
				'tptn_settings[' . $args['id'] . ']', // ID of the settings field. We save it within the tptn_settings array.
				$args['name'],     // Label of the setting.
				function_exists( 'tptn_' . $args['type'] . '_callback' ) ? 'tptn_' . $args['type'] . '_callback' : 'tptn_missing_callback', // Function to handle the setting.
				'tptn_settings_' . $section,    // Page to display the setting. In our case it is the section as defined above.
				'tptn_settings_' . $section,    // Name of the section.
				$args
			);
		}
	}

	// Register the settings into the options table.
	register_setting( 'tptn_settings', 'tptn_settings', 'tptn_settings_sanitize' );
}
add_action( 'admin_init', 'tptn_register_settings' );


/**
 * Retrieve the array of plugin settings
 *
 * @since 2.5.0
 *
 * @return array Settings array
 */
function tptn_get_registered_settings() {

	$tptn_settings = array(
		/*** General settings ***/
		'general'             => apply_filters(
			'tptn_settings_general',
			array(
				'slug_header'  => array(
					'id'               => 'slug_header',
					'name'             => '<h3>' . esc_html__( 'Slug options', 'top-10' ) . '</h3>',
					'desc'             => '',
					'type'             => 'header',
				),
				'kb_slug'           => array(
					'id'               => 'kb_slug',
					'name'             => esc_html__( 'Knowledgebase slug', 'top-10' ),
					'desc'             => esc_html__( 'This will set the opening path of the URL of the knowledgebase and is set when registering the custom post type', 'top-10' ),
					'type'             => 'text',
					'options'          => 'top-10',
				),
				'category_slug'     => array(
					'id'               => 'category_slug',
					'name'             => esc_html__( 'Category slug', 'top-10' ),
					'desc'             => esc_html__( 'Each category is a section of the knowledgebase. This setting is used when registering the custom category and forms a part of the URL when browsing category archives', 'top-10' ),
					'type'             => 'text',
					'options'          => 'section',
				),
				'tag_slug'          => array(
					'id'               => 'tag_slug',
					'name'             => esc_html__( 'Tag slug', 'top-10' ),
					'desc'             => esc_html__( 'Each article can have multiple tags. This setting is used when registering the custom tag and forms a part of the URL when browsing tag archives', 'top-10' ),
					'type'             => 'text',
					'options'          => 'kb-tags',
				),
				'uninstall_header'  => array(
					'id'               => 'uninstall_header',
					'name'             => '<h3>' . esc_html__( 'Uninstall options', 'top-10' ) . '</h3>',
					'desc'             => '',
					'type'             => 'header',
					'options'          => '',
				),
				'uninstall_options' => array(
					'id'               => 'uninstall_options',
					'name'             => esc_html__( 'Delete options on uninstall', 'top-10' ),
					'desc'             => esc_html__( 'Check this box to delete the settings on this page when the plugin is deleted via the Plugins page in your WordPress Admin', 'top-10' ),
					'type'             => 'checkbox',
					'options'          => true,
				),
				'uninstall_data'    => array(
					'id'               => 'uninstall_data',
					'name'             => esc_html__( 'Delete all knowledgebase posts on uninstall', 'top-10' ),
					'desc'             => esc_html__( 'Check this box to delete all the posts, categories and tags created by the plugin. There is no way to restore the data if you choose this option', 'top-10' ),
					'type'             => 'checkbox',
					'options'          => false,
				),
				'feed_header'       => array(
					'id'               => 'feed_header',
					'name'             => '<h3>' . esc_html__( 'Feed options', 'top-10' ) . '</h3>',
					'desc'             => '',
					'type'             => 'header',
					'options'          => '',
				),
				'include_in_feed'   => array(
					'id'               => 'include_in_feed',
					'name'             => esc_html__( 'Include in feed', 'top-10' ),
					'desc'             => esc_html__( 'Adds the knowledgebase articles to the main RSS feed for your site', 'top-10' ),
					'type'             => 'checkbox',
					'options'          => true,
				),
				'disable_kb_feed'   => array(
					'id'               => 'disable_kb_feed',
					'name'             => esc_html__( 'Disable KB feed', 'top-10' ),
					/* translators: 1: Opening link tag, 2: Closing link tag. */
					'desc'             => sprintf( esc_html__( 'The knowledgebase articles have a default feed. This option will disable the feed. You might need to %1$srefresh your permalinks%2$s when changing this option.', 'top-10' ), '<a href="' . admin_url( 'options-permalink.php' ) . '" target="_blank">', '</a>' ),
					'type'             => 'checkbox',
					'options'          => false,
				),
			)
		),
		/*** Output settings ***/
		'output'             => apply_filters(
			'tptn_settings_output',
			array(
				'category_level'     => array(
					'id'                => 'category_level',
					'name'              => esc_html__( 'First section level', 'top-10' ),
					'desc'              => esc_html__( 'This option allows you to create multi-level knowledgebases. This works in conjunction with the inbuilt styles. Set to 1 to lay out the top level sections in a grid. Set to 2 to lay out the second level categories in the grid. This is great if you have multiple products and want to create separate knowledgebases for each of them. The default option is 2 and was the behaviour of this plugin before v1.5.0.', 'top-10' ),
					'type'                   => 'number',
					'options'                => '2',
					'size'                   => 'small',
					'min'                    => '1',
					'max'                    => '5',
				),
				'show_article_count' => array(
					'id'                => 'show_article_count',
					'name'              => esc_html__( 'Show article count', 'top-10' ),
					'desc'              => esc_html__( 'If selected, the number of articles will be displayed in an orange circle next to the header. You can override the color by styling tptn_section_count', 'top-10' ),
					'type'              => 'checkbox',
					'options'           => false,
				),
				'show_excerpt'       => array(
					'id'                => 'show_excerpt',
					'name'              => esc_html__( 'Show excerpt', 'top-10' ),
					'desc'              => esc_html__( 'Select to include the post excerpt after the article link', 'top-10' ),
					'type'              => 'checkbox',
					'options'           => false,
				),
				'clickable_section'  => array(
					'id'                => 'clickable_section',
					'name'              => esc_html__( 'Link section title', 'top-10' ),
					'desc'              => esc_html__( 'If selected, the title of each section of the knowledgebase will be linked to its own page', 'top-10' ),
					'type'              => 'checkbox',
					'options'           => true,
				),
			)
		),
		/*** Style settings ***/
		'styles'             => apply_filters(
			'tptn_settings_styles',
			array(
				'include_styles'    => array(
					'id'               => 'include_styles',
					'name'             => esc_html__( 'Include inbuilt styles', 'top-10' ),
					'desc'             => esc_html__( 'Uncheck this to disable this plugin from adding the inbuilt styles. You will need to add your own CSS styles if you disable this option', 'top-10' ),
					'type'             => 'checkbox',
					'options'          => true,
				),
				'columns'            => array(
					'id'                => 'columns',
					'name'              => esc_html__( 'Number of columns', 'top-10' ),
					'desc'              => esc_html__( 'Set number of columns to display the knowledgebase archives. This is only works if the above option is selected.', 'top-10' ),
					'type'                   => 'number',
					'options'                => '2',
					'size'                   => 'small',
					'min'                    => '1',
					'max'                    => '5',
				),
				'custom_css'        => array(
					'id'               => 'custom_css',
					'name'             => esc_html__( 'Custom CSS', 'top-10' ),
					'desc'             => esc_html__( 'Enter any custom valid CSS without any wrapping &lt;style&gt; tags', 'top-10' ),
					'type'             => 'textarea',
					'options'          => '',
				),
			)
		),
	);

	/**
	 * Filters the settings array
	 *
	 * @since 2.5.0
	 *
	 * @param array $tptn_setings Settings array
	 */
	return apply_filters( 'tptn_registered_settings', $tptn_settings );

}



/**
 * Flattens tptn_get_registered_settings() into $setting[id] => $setting[type] format.
 *
 * @since 2.5.0
 *
 * @return array Default settings
 */
function tptn_get_registered_settings_types() {

	$options = array();

	// Populate some default values.
	foreach ( tptn_get_registered_settings() as $tab => $settings ) {
		foreach ( $settings as $option ) {
			$options[ $option['id'] ] = $option['type'];
		}
	}

	/**
	 * Filters the settings array.
	 *
	 * @since 2.5.0
	 *
	 * @param array $options Default settings.
	 */
	return apply_filters( 'tptn_get_settings_types', $options );
}


/**
 * Default settings.
 *
 * @since 2.5.0
 *
 * @return array Default settings
 */
function tptn_settings_defaults() {

	$options = array();

	// Populate some default values.
	foreach ( tptn_get_registered_settings() as $tab => $settings ) {
		foreach ( $settings as $option ) {
			// When checkbox is set to true, set this to 1.
			if ( 'checkbox' === $option['type'] && ! empty( $option['options'] ) ) {
				$options[ $option['id'] ] = '1';
			}
			// If an option is set.
			if ( in_array( $option['type'], array( 'textarea', 'text', 'csv', 'numbercsv', 'posttypes', 'number' ), true ) && isset( $option['options'] ) ) {
				$options[ $option['id'] ] = $option['options'];
			}
			if ( in_array( $option['type'], array( 'multicheck', 'radio', 'select' ), true ) && isset( $option['default'] ) ) {
				$options[ $option['id'] ] = $option['default'];
			}
		}
	}

	/**
	 * Filters the default settings array.
	 *
	 * @since 2.5.0
	 *
	 * @param array $options Default settings.
	 */
	return apply_filters( 'tptn_settings_defaults', $options );
}


/**
 * Get the default option for a specific key
 *
 * @since 2.5.0
 *
 * @param string $key Key of the option to fetch.
 * @return mixed
 */
function tptn_get_default_option( $key = '' ) {

	$default_settings = tptn_settings_defaults();

	if ( array_key_exists( $key, $default_settings ) ) {
		return $default_settings[ $key ];
	} else {
		return false;
	}

}


/**
 * Reset settings.
 *
 * @since 2.5.0
 *
 * @return void
 */
function tptn_settings_reset() {
	delete_option( 'tptn_settings' );
}
