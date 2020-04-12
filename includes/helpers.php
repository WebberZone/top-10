<?php
/**
 * Helper functions
 *
 * @package Top_Ten
 */

/**
 * Function to delete all rows in the posts table.
 *
 * @since   1.3
 * @param   bool $daily  Daily flag.
 */
function tptn_trunc_count( $daily = true ) {
	global $wpdb;

	$table_name = $wpdb->base_prefix . 'top_ten';
	if ( $daily ) {
		$table_name .= '_daily';
	}

	$sql = "TRUNCATE TABLE $table_name";
	$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
}


/**
 * Retrieve the from date for the query
 *
 * @since 2.6.0
 *
 * @param string $time        A date/time string.
 * @param int    $daily_range Daily range.
 * @param int    $hour_range  Hour range.
 * @return string From date
 */
function tptn_get_from_date( $time = null, $daily_range = null, $hour_range = null ) {

	$current_time = isset( $time ) ? strtotime( $time ) : strtotime( current_time( 'mysql' ) );
	$daily_range  = isset( $daily_range ) ? $daily_range : tptn_get_option( 'daily_range' );
	$hour_range   = isset( $hour_range ) ? $hour_range : tptn_get_option( 'hour_range' );

	if ( tptn_get_option( 'daily_midnight' ) ) {
		$from_date = $current_time - ( max( 0, ( $daily_range - 1 ) ) * DAY_IN_SECONDS );
		$from_date = gmdate( 'Y-m-d 0', $from_date );
	} else {
		$from_date = $current_time - ( $daily_range * DAY_IN_SECONDS + $hour_range * HOUR_IN_SECONDS );
		$from_date = gmdate( 'Y-m-d H', $from_date );
	}

	/**
	 * Retrieve the from date for the query
	 *
	 * @since 2.6.0
	 *
	 * @param string $from_date   From date.
	 * @param string $time        A date/time string.
	 * @param int    $daily_range Daily range.
	 * @param int    $hour_range  Hour range.
	 */
	return apply_filters( 'tptn_get_from_date', $from_date, $time, $daily_range, $hour_range );
}


/**
 * Convert float number to format based on the locale if number_format_count is true.
 *
 * @since 2.6.0
 *
 * @param float $number   The number to convert based on locale.
 * @param int   $decimals Optional. Precision of the number of decimal places. Default 0.
 * @return string Converted number in string format.
 */
function tptn_number_format_i18n( $number, $decimals = 0 ) {

	$formatted = $number;

	if ( tptn_get_option( 'number_format_count' ) ) {
		$formatted = number_format_i18n( $number );
	}

	/**
	 * Filters the number formatted based on the locale.
	 *
	 * @since 2.6.0
	 *
	 * @param string $formatted Converted number in string format.
	 * @param float  $number    The number to convert based on locale.
	 * @param int    $decimals  Precision of the number of decimal places.
	 */
	return apply_filters( 'number_format_i18n', $formatted, $number, $decimals );
}

/**
 * Convert a string to CSV.
 *
 * @since 2.9.0
 *
 * @param string $array Input string.
 * @param string $delimiter Delimiter.
 * @param string $enclosure Enclosure.
 * @param string $terminator Terminating string.
 * @return string CSV string.
 */
function tptn_str_putcsv( $array, $delimiter = ',', $enclosure = '"', $terminator = "\n" ) {
	// First convert associative array to numeric indexed array.
	$work_array = array();
	foreach ( $array as $key => $value ) {
		$work_array[] = $value;
	}

	$string     = '';
	$array_size = count( $work_array );

	for ( $i = 0; $i < $array_size; $i++ ) {
		// Nested array, process nest item.
		if ( is_array( $work_array[ $i ] ) ) {
			$string .= str_putcsv( $work_array[ $i ], $delimiter, $enclosure, $terminator );
		} else {
			switch ( gettype( $work_array[ $i ] ) ) {
				// Manually set some strings.
				case 'NULL':
					$sp_format = '';
					break;
				case 'boolean':
					$sp_format = ( true === $work_array[ $i ] ) ? 'true' : 'false';
					break;
				// Make sure sprintf has a good datatype to work with.
				case 'integer':
					$sp_format = '%i';
					break;
				case 'double':
					$sp_format = '%0.2f';
					break;
				case 'string':
					$sp_format        = '%s';
					$work_array[ $i ] = str_replace( "$enclosure", "$enclosure$enclosure", $work_array[ $i ] );
					break;
				// Unknown or invalid items for a csv - note: the datatype of array is already handled above, assuming the data is nested.
				case 'object':
				case 'resource':
				default:
					$sp_format = '';
					break;
			}
			$string .= sprintf( '%2$s' . $sp_format . '%2$s', $work_array[ $i ], $enclosure );
			$string .= ( $i < ( $array_size - 1 ) ) ? $delimiter : $terminator;
		}
	}

	return $string;
}
