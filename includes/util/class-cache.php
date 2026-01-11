<?php
/**
 * Cache class.
 *
 * @package WebberZone\Top_Ten\Util
 */

namespace WebberZone\Top_Ten\Util;

use WebberZone\Top_Ten\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Cache class.
 *
 * @since 3.3.0
 */
class Cache {

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		Hook_Registry::add_action( 'wp_ajax_tptn_clear_cache', array( $this, 'ajax_clearcache' ) );
	}

	/**
	 * Function to clear the Top 10 Cache with Ajax.
	 *
	 * @since   2.2.0
	 */
	public function ajax_clearcache() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}
		check_ajax_referer( 'tptn-admin', 'security' );

		$count = $this->delete();

		wp_send_json_success(
			array(
				/* translators: 1: Number of entries cleared. */
				'message' => sprintf( _n( '%s entry cleared', '%s entries cleared', $count, 'top-10' ), number_format_i18n( $count ) ),
			)
		);
	}

	/**
	 * Delete the Top 10 cache.
	 *
	 * @since 2.3.0
	 *
	 * @param array $transients Array of transients to delete.
	 * @return int Number of transients deleted.
	 */
	public static function delete( $transients = array() ) {
		$loop = 0;

		$default_transients = self::get_keys();

		if ( ! empty( $transients ) ) {
			$transients = array_intersect( $default_transients, (array) $transients );
		} else {
			$transients = $default_transients;
		}

		foreach ( $transients as $transient ) {
			$del = delete_transient( $transient );
			if ( $del ) {
				++$loop;
			}
		}
		return $loop;
	}

	/**
	 * Get the default meta keys used for the cache
	 *
	 * @return  array   Transient meta keys
	 */
	public static function get_keys() {

		$meta_keys = array(
			'tptn_total',
			'tptn_daily',
			'tptn_total_shortcode',
			'tptn_daily_shortcode',
			'tptn_total_widget',
			'tptn_daily_widget',
			'tptn_total_manual',
			'tptn_daily_manual',
		);

		$meta_keys = array_merge( $meta_keys, self::get_widget_keys() );

		/**
		 * Filters the array containing the various cache keys.
		 *
		 * @since   1.9
		 *
		 * @param   array   $default_meta_keys  Array of meta keys
		 */
		return apply_filters( 'tptn_cache_keys', $meta_keys );
	}

	/**
	 * Get the cache key based on a list of parameters.
	 *
	 * @since 4.2.0
	 *
	 * @param mixed $attr Array of attributes typically.
	 * @return string Cache meta key
	 */
	public static function get_key( $attr ): string {
		$args = (array) $attr;

		static $setting_types = null;
		if ( null === $setting_types ) {
			$setting_types = function_exists( 'tptn_get_registered_settings_types' ) ? tptn_get_registered_settings_types() : array();
		}

		// Remove args that don't affect query results.
		$exclude_keys = array(
			'after_list',
			'after_list_item',
			'before_list',
			'before_list_item',
			'blank_output',
			'blank_output_text',
			'cache',
			'className',
			'echo',
			'excerpt_length',
			'extra_class',
			'heading',
			'is_block',
			'is_manual',
			'is_shortcode',
			'is_widget',
			'link_new_window',
			'link_nofollow',
			'more_link_text',
			'no_found_rows',
			'other_attributes',
			'post_types',
			'post_id',
			'postid',
			'same_post_type',
			'show_author',
			'show_credit',
			'show_date',
			'show_excerpt',
			'show_metabox',
			'show_metabox_admins',
			'suppress_filters',
			'title',
			'title_length',
		);

		foreach ( $exclude_keys as $key ) {
			unset( $args[ $key ] );
		}

		// Remove any keys ending in _header or _desc, or with type 'header'.
		foreach ( $args as $key => $value ) {
			if ( '_header' === substr( $key, -7 ) || '_desc' === substr( $key, -5 ) ) {
				unset( $args[ $key ] );
				continue;
			}

			if ( isset( $setting_types[ $key ] ) && 'header' === $setting_types[ $key ] ) {
				unset( $args[ $key ] );
			}
		}

		// Define categories of types for normalization.
		$id_array_types     = array( 'postids', 'numbercsv', 'taxonomies' );
		$string_array_types = array( 'posttypes', 'csv', 'multicheck' );
		$numeric_types      = array( 'number', 'checkbox', 'select', 'radio', 'radiodesc' );

		// Process arguments based on their registered types.
		foreach ( $args as $key => $value ) {
			$type = $setting_types[ $key ] ?? '';

			if ( in_array( $type, $numeric_types, true ) && is_numeric( $value ) ) {
				$args[ $key ] = (int) $value;
			} elseif ( in_array( $type, $id_array_types, true ) ) {
				$args[ $key ] = is_array( $value ) ? $value : wp_parse_id_list( $value );
				$args[ $key ] = array_unique( array_map( 'absint', $args[ $key ] ) );
				$args[ $key ] = array_filter( $args[ $key ] );
				sort( $args[ $key ] );
				if ( empty( $args[ $key ] ) ) {
					unset( $args[ $key ] );
				}
			} elseif ( in_array( $type, $string_array_types, true ) ) {
				if ( is_string( $value ) && strpos( $value, '=' ) !== false ) {
					parse_str( $value, $parsed );
					$value = array_keys( $parsed );
				} elseif ( is_string( $value ) ) {
					$value = explode( ',', $value );
				}
				$args[ $key ] = is_array( $value ) ? $value : array( $value );
				$args[ $key ] = array_unique( array_map( 'strval', $args[ $key ] ) );
				$args[ $key ] = array_filter( $args[ $key ] );
				sort( $args[ $key ] );
				if ( empty( $args[ $key ] ) ) {
					unset( $args[ $key ] );
				}
			}
		}

		// Fallback for known keys that might not be in $setting_types or need specific handling.
		$id_arrays = array(
			'author__in',
			'author__not_in',
			'category__and',
			'category__in',
			'category__not_in',
			'cornerstone_post_ids',
			'exclude_categories',
			'exclude_on_categories',
			'exclude_on_post_ids',
			'exclude_post_ids',
			'include_cat_ids',
			'include_post_ids',
			'manual_related',
			'post__in',
			'post__not_in',
			'post_parent__in',
			'post_parent__not_in',
			'tag__and',
			'tag__in',
			'tag__not_in',
			'tag_slug__and',
			'tag_slug__in',
		);

		foreach ( $id_arrays as $key ) {
			if ( array_key_exists( $key, $args ) && ! isset( $setting_types[ $key ] ) ) {
				if ( null !== $args[ $key ] ) {
					$args[ $key ] = is_array( $args[ $key ] ) ? $args[ $key ] : wp_parse_id_list( $args[ $key ] );
					$args[ $key ] = array_unique( array_map( 'absint', $args[ $key ] ) );
					$args[ $key ] = array_filter( $args[ $key ] );
					sort( $args[ $key ] );

					if ( empty( $args[ $key ] ) ) {
						unset( $args[ $key ] );
					}
				} else {
					unset( $args[ $key ] );
				}
			}
		}

		$string_arrays = array(
			'exclude_cat_slugs',
			'exclude_on_cat_slugs',
			'exclude_on_post_types',
			'post_name__in',
			'post_status',
			'post_type',
			'same_taxes',
		);

		foreach ( $string_arrays as $key ) {
			if ( array_key_exists( $key, $args ) && ! isset( $setting_types[ $key ] ) ) {
				if ( null !== $args[ $key ] ) {
					if ( is_string( $args[ $key ] ) && strpos( $args[ $key ], '=' ) !== false ) {
						parse_str( $args[ $key ], $parsed );
						$parsed_value = array_keys( $parsed );
					} elseif ( is_string( $args[ $key ] ) ) {
						$parsed_value = explode( ',', $args[ $key ] );
					} else {
						$parsed_value = $args[ $key ];
					}
					$args[ $key ] = is_array( $parsed_value ) ? $parsed_value : array( $parsed_value );
					$args[ $key ] = array_unique( array_map( 'strval', $args[ $key ] ) );
					$args[ $key ] = array_filter( $args[ $key ] );
					sort( $args[ $key ] );

					if ( empty( $args[ $key ] ) ) {
						unset( $args[ $key ] );
					}
				} else {
					unset( $args[ $key ] );
				}
			}
		}

		// Sort top-level arguments.
		ksort( $args );

		// Remove any remaining empty strings or null values.
		foreach ( $args as $key => $value ) {
			if ( '' === $value || null === $value ) {
				unset( $args[ $key ] );
			}
		}

		// Generate cache key.
		return md5( wp_json_encode( $args ) );
	}

	/**
	 * Get the transient names for the Top 10 widgets.
	 *
	 * @since 2.3.0
	 *
	 * @return array Top 10 Cache widget keys.
	 */
	public static function get_widget_keys() {
		global $wpdb;

		$keys = array();

		$sql = "
		SELECT option_name
		FROM {$wpdb->options}
		WHERE `option_name` LIKE '_transient_tptn_%'
		";

		$results = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		if ( is_array( $results ) ) {
			foreach ( $results as $result ) {
				$keys[] = str_replace( '_transient_', '', $result->option_name );
			}
		}

		return apply_filters( 'tptn_cache_get_widget_keys', $keys );
	}
}
