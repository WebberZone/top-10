<?php
/**
 * Save settings.
 *
 * Functions to register, read, write and update settings.
 * Portions of this code have been inspired by Easy Digital Downloads, WordPress Settings Sandbox, etc.
 *
 * @link  https://webberzone.com
 * @since 2.5.0
 *
 * @package    Top 10
 * @subpackage Admin/Save_Settings
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Sanitize the form data being submitted.
 *
 * @since 2.5.0
 * @param  array $input Input unclean array.
 * @return array Sanitized array
 */
function tptn_settings_sanitize( $input = array() ) {

	// First, we read the options collection.
	global $tptn_settings;

	// This should be set if a form is submitted, so let's save it in the $referrer variable.
	if ( empty( $_POST['_wp_http_referer'] ) ) {
		return $input;
	}

	parse_str( sanitize_text_field( wp_unslash( $_POST['_wp_http_referer'] ) ), $referrer ); // Input var okay.

	// Get the various settings we've registered.
	$settings       = tptn_get_registered_settings();
	$settings_types = tptn_get_registered_settings_types();

	// Check if we need to set to defaults.
	$reset = isset( $_POST['settings_reset'] );

	if ( $reset ) {
		tptn_settings_reset();
		$tptn_settings = tptn_get_settings();

		add_settings_error( 'tptn-notices', '', __( 'Settings have been reset to their default values. Reload this page to view the updated settings', 'top-10' ), 'error' );

		return $tptn_settings;
	}

	// Get the tab. This is also our settings' section.
	$tab = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';

	$input = $input ? $input : array();

	/**
	 * Filter the settings for the tab. e.g. tptn_settings_general_sanitize.
	 *
	 * @since 2.5.0
	 * @param  array $input Input unclean array
	 */
	$input = apply_filters( 'tptn_settings_' . $tab . '_sanitize', $input );

	// Create out output array by merging the existing settings with the ones submitted.
	$output = array_merge( $tptn_settings, $input );

	// Loop through each setting being saved and pass it through a sanitization filter.
	foreach ( $settings_types as $key => $type ) {

		/**
		 * Skip settings that are not really settings.
		 *
		 * @since 2.5.0
		 * @param  array $non_setting_types Array of types which are not settings.
		 */
		$non_setting_types = apply_filters( 'tptn_non_setting_types', array( 'header', 'descriptive_text' ) );

		if ( in_array( $type, $non_setting_types, true ) ) {
			continue;
		}

		if ( array_key_exists( $key, $output ) ) {

			/**
			 * Field type filter.
			 *
			 * @since 2.5.0
			 * @param array $output[$key] Setting value.
			 * @param array $key Setting key.
			 */
			$output[ $key ] = apply_filters( 'tptn_settings_sanitize_' . $type, $output[ $key ], $key );
		}

		/**
		 * Field type filter for a specific key.
		 *
		 * @since 2.5.0
		 * @param array $output[$key] Setting value.
		 * @param array $key Setting key.
		 */
		$output[ $key ] = apply_filters( 'tptn_settings_sanitize' . $key, $output[ $key ], $key );

		// Delete any key that is not present when we submit the input array.
		if ( ! isset( $input[ $key ] ) ) {
			unset( $output[ $key ] );
		}
	}

	// Delete any settings that are no longer part of our registered settings.
	if ( array_key_exists( $key, $output ) && ! array_key_exists( $key, $settings_types ) ) {
		unset( $output[ $key ] );
	}

	add_settings_error( 'tptn-notices', '', __( 'Settings updated.', 'top-10' ), 'updated' );

	/**
	 * Filter the settings array before it is returned.
	 *
	 * @since 2.5.0
	 * @param array $output Settings array.
	 * @param array $input Input settings array.
	 */
	return apply_filters( 'tptn_settings_sanitize', $output, $input );

}


/**
 * Sanitize text fields
 *
 * @since 2.5.0
 *
 * @param  array $value The field value.
 * @return string  $value  Sanitized value
 */
function tptn_sanitize_text_field( $value ) {
	return tptn_sanitize_textarea_field( $value );
}
add_filter( 'tptn_settings_sanitize_text', 'tptn_sanitize_text_field' );


/**
 * Sanitize number fields
 *
 * @since 2.5.0
 *
 * @param  array $value The field value.
 * @return string  $value  Sanitized value
 */
function tptn_sanitize_number_field( $value ) {
	return filter_var( $value, FILTER_SANITIZE_NUMBER_INT );
}
add_filter( 'tptn_settings_sanitize_number', 'tptn_sanitize_number_field' );


/**
 * Sanitize CSV fields
 *
 * @since 2.5.0
 *
 * @param  array $value The field value.
 * @return string  $value  Sanitized value
 */
function tptn_sanitize_csv_field( $value ) {

	return implode( ',', array_map( 'trim', explode( ',', sanitize_text_field( wp_unslash( $value ) ) ) ) );
}
add_filter( 'tptn_settings_sanitize_csv', 'tptn_sanitize_csv_field' );


/**
 * Sanitize CSV fields which hold numbers e.g. IDs
 *
 * @since 2.5.0
 *
 * @param  array $value The field value.
 * @return string  $value  Sanitized value
 */
function tptn_sanitize_numbercsv_field( $value ) {

	return implode( ',', array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $value ) ) ) ) ) );
}
add_filter( 'tptn_settings_sanitize_numbercsv', 'tptn_sanitize_numbercsv_field' );


/**
 * Sanitize textarea fields
 *
 * @since 2.5.0
 *
 * @param  array $value The field value.
 * @return string  $value  Sanitized value
 */
function tptn_sanitize_textarea_field( $value ) {

	global $allowedposttags;

	// We need more tags to allow for script and style.
	$moretags = array(
		'script' => array(
			'type'    => true,
			'src'     => true,
			'async'   => true,
			'defer'   => true,
			'charset' => true,
			'lang'    => true,
		),
		'style'  => array(
			'type'   => true,
			'media'  => true,
			'scoped' => true,
			'lang'   => true,
		),
		'link'   => array(
			'rel'      => true,
			'type'     => true,
			'href'     => true,
			'media'    => true,
			'sizes'    => true,
			'hreflang' => true,
		),
	);

	$allowedtags = array_merge( $allowedposttags, $moretags );

	/**
	 * Filter allowed tags allowed when sanitizing text and textarea fields.
	 *
	 * @since 2.5.0
	 *
	 * @param array $allowedtags Allowed tags array.
	 * @param array $value The field value.
	 */
	$allowedtags = apply_filters( 'tptn_sanitize_allowed_tags', $allowedtags, $value );

	return wp_kses( wp_unslash( $value ), $allowedtags );

}
add_filter( 'tptn_settings_sanitize_textarea', 'tptn_sanitize_textarea_field' );


/**
 * Sanitize checkbox fields
 *
 * @since 2.5.0
 *
 * @param  array $value The field value.
 * @return string|int  $value  Sanitized value
 */
function tptn_sanitize_checkbox_field( $value ) {

	$value = ( -1 === (int) $value ) ? 0 : 1;

	return $value;
}
add_filter( 'tptn_settings_sanitize_checkbox', 'tptn_sanitize_checkbox_field' );


/**
 * Sanitize post_types fields
 *
 * @since 2.5.0
 *
 * @param  array $value The field value.
 * @return string  $value  Sanitized value
 */
function tptn_sanitize_posttypes_field( $value ) {

	$post_types = is_array( $value ) ? array_map( 'sanitize_text_field', wp_unslash( $value ) ) : array( 'post', 'page' );

	return implode( ',', $post_types );
}
add_filter( 'tptn_settings_sanitize_posttypes', 'tptn_sanitize_posttypes_field' );


/**
 * Sanitize exclude_cat_slugs to save a new entry of exclude_categories
 *
 * @since 2.5.0
 *
 * @param  array $settings Settings array.
 * @return string  $settings  Sanitizied settings array.
 */
function tptn_sanitize_exclude_cat( $settings ) {

	if ( ! empty( $settings['exclude_cat_slugs'] ) ) {

		$exclude_cat_slugs = explode( ',', $settings['exclude_cat_slugs'] );

		foreach ( $exclude_cat_slugs as $cat_name ) {
			$cat = get_term_by( 'name', $cat_name, 'category' );

			// Fall back to slugs since that was the default format before v2.4.0.
			if ( false === $cat ) {
				$cat = get_term_by( 'slug', $cat_name, 'category' );
			}
			if ( isset( $cat->term_taxonomy_id ) ) {
				$exclude_categories[]       = $cat->term_taxonomy_id;
				$exclude_categories_slugs[] = $cat->name;
			}
		}
		$settings['exclude_categories'] = isset( $exclude_categories ) ? join( ',', $exclude_categories ) : '';
		$settings['exclude_cat_slugs']  = isset( $exclude_categories_slugs ) ? join( ',', $exclude_categories_slugs ) : '';

	}

	return $settings;
}
add_filter( 'tptn_settings_sanitize', 'tptn_sanitize_exclude_cat' );


/**
 * Enable/disable Top 10 cron on save.
 *
 * @since 2.5.0
 *
 * @param  array $settings Settings array.
 * @return string  $settings  Sanitizied settings array.
 */
function tptn_sanitize_cron( $settings ) {

	$settings['cron_hour'] = min( 23, absint( $settings['cron_hour'] ) );
	$settings['cron_min']  = min( 59, absint( $settings['cron_min'] ) );

	if ( ! empty( $settings['cron_on'] ) ) {
		tptn_enable_run( $settings['cron_hour'], $settings['cron_min'], $settings['cron_recurrence'] );
	} else {
		tptn_disable_run();
	}

	return $settings;
}
add_filter( 'tptn_settings_sanitize', 'tptn_sanitize_cron' );


/**
 * Delete cache when saving settings.
 *
 * @since 2.5.0
 *
 * @param  array $settings Settings array.
 * @return string  $settings  Sanitizied settings array.
 */
function tptn_sanitize_cache( $settings ) {

	// Delete the cache.
	tptn_cache_delete();

	return $settings;
}
add_filter( 'tptn_settings_sanitize', 'tptn_sanitize_cache' );
