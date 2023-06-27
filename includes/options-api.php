<?php
/**
 * Top 10 Options API.
 *
 * @link  https://webberzone.com
 * @since 3.3.0
 *
 * @package WebberZone\Top_Ten
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Get Settings.
 *
 * Retrieves all plugin settings
 *
 * @since  2.5.0
 * @return array Top 10 settings
 */
function tptn_get_settings() {

	$settings = get_option( 'tptn_settings' );

	/**
	 * Settings array
	 *
	 * Retrieves all plugin settings
	 *
	 * @since 2.5.0
	 * @param array $settings Settings array
	 */
	return apply_filters( 'tptn_get_settings', $settings );
}

/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since  2.5.0
 *
 * @param string $key Option to fetch.
 * @param mixed  $default_value Default option.
 * @return mixed
 */
function tptn_get_option( $key = '', $default_value = null ) {
	global $tptn_settings;

	if ( empty( $tptn_settings ) ) {
		$tptn_settings = tptn_get_settings();
	}

	if ( is_null( $default_value ) ) {
		$default_value = tptn_get_default_option( $key );
	}

	$value = isset( $tptn_settings[ $key ] ) ? $tptn_settings[ $key ] : $default_value;

	/**
	 * Filter the value for the option being fetched.
	 *
	 * @since 2.5.0
	 *
	 * @param mixed $value  Value of the option
	 * @param mixed $key  Name of the option
	 * @param mixed $default_value Default value
	 */
	$value = apply_filters( 'tptn_get_option', $value, $key, $default_value );

	/**
	 * Key specific filter for the value of the option being fetched.
	 *
	 * @since 2.5.0
	 *
	 * @param mixed $value  Value of the option
	 * @param mixed $key  Name of the option
	 * @param mixed $default_value Default value
	 */
	return apply_filters( 'tptn_get_option_' . $key, $value, $key, $default_value );
}


/**
 * Update an option
 *
 * Updates an ata setting value in both the db and the global variable.
 * Warning: Passing in an empty, false or null string value will remove
 *        the key from the tptn_options array.
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

	// Let's let devs alter that value coming in.
	$value = apply_filters( 'tptn_update_option', $value, $key );

	// Next let's try to update the value.
	$options[ $key ] = $value;
	$did_update      = update_option( 'tptn_settings', $options );

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
 * Removes an ata setting value in both the db and the global variable.
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
 * Default settings.
 *
 * @since 2.5.0
 *
 * @return array Default settings
 */
function tptn_settings_defaults() {

	$options = array();

	// Populate some default values.
	foreach ( \WebberZone\Top_Ten\Admin\Settings\Settings::get_registered_settings() as $tab => $settings ) {
		foreach ( $settings as $option ) {
			// When checkbox is set to true, set this to 1.
			if ( 'checkbox' === $option['type'] && ! empty( $option['options'] ) ) {
				$options[ $option['id'] ] = 1;
			} else {
				$options[ $option['id'] ] = 0;
			}
			// If an option is set.
			if ( in_array( $option['type'], array( 'textarea', 'css', 'html', 'text', 'url', 'csv', 'color', 'numbercsv', 'postids', 'posttypes', 'number', 'wysiwyg', 'file', 'password' ), true ) && isset( $option['options'] ) ) {
				$options[ $option['id'] ] = $option['options'];
			}
			if ( in_array( $option['type'], array( 'multicheck', 'radio', 'select', 'radiodesc', 'thumbsizes' ), true ) && isset( $option['default'] ) ) {
				$options[ $option['id'] ] = $option['default'];
			}
		}
	}

	/**
	 * Filters the default settings array.
	 *
	 * @since 2.5.0
	 *
	 * @param array   $options Default settings.
	 */
	return apply_filters( 'tptn_settings_defaults', $options );
}


/**
 * Get the default option for a specific key
 *
 * @since 1.3.0
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


/**
 * Function to add an action to search for tags using Ajax.
 *
 * @since 1.7.0
 *
 * @return void
 */
function tptn_tag_search() {

	if ( ! isset( $_REQUEST['tax'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		wp_die();
	}

	$taxonomy = sanitize_key( $_REQUEST['tax'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$tax      = get_taxonomy( $taxonomy );
	if ( ! $tax ) {
		wp_die();
	}

	if ( ! current_user_can( $tax->cap->assign_terms ) ) {
		wp_die();
	}

	$s = isset( $_REQUEST['q'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$comma = _x( ',', 'tag delimiter' );
	if ( ',' !== $comma ) {
		$s = str_replace( $comma, ',', $s );
	}
	if ( false !== strpos( $s, ',' ) ) {
		$s = explode( ',', $s );
		$s = $s[ count( $s ) - 1 ];
	}
	$s = trim( $s );

	/** This filter has been defined in /wp-admin/includes/ajax-actions.php */
	$term_search_min_chars = (int) apply_filters( 'term_search_min_chars', 2, $tax, $s );

	/*
	 * Require $term_search_min_chars chars for matching (default: 2)
	 * ensure it's a non-negative, non-zero integer.
	 */
	if ( ( 0 === $term_search_min_chars ) || ( strlen( $s ) < $term_search_min_chars ) ) {
		wp_die();
	}

	$results = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'name__like' => $s,
			'fields'     => 'names',
			'hide_empty' => false,
		)
	);

	echo wp_json_encode( $results );
	wp_die();
}
add_action( 'wp_ajax_tptn_tag_search', 'tptn_tag_search' );
