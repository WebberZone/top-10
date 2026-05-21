<?php
/**
 * CSV helper for import and export of count data.
 *
 * @package WebberZone\Top_Ten\Util
 * @since 4.3.0
 */

namespace WebberZone\Top_Ten\Util;

use WebberZone\Top_Ten\Database;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Shared CSV read/write logic used by both the admin Import/Export page
 * and the WP-CLI `wp top10 counts export|import` commands.
 *
 * @since 4.3.0
 */
class Csv_Helper {

	/**
	 * Write count rows to an open file handle in the Top 10 CSV format.
	 *
	 * Writes UTF-8 BOM (for Excel compatibility), a header row, then one
	 * data row per result.  The positional column layout is:
	 *
	 *   Overall:  Post ID, Visits, Blog ID[, URL]
	 *   Daily:    Post ID, Visits, Date, Blog ID[, URL]
	 *
	 * @since 4.3.0
	 *
	 * @param resource $fh            Open file handle (writable).
	 * @param array    $rows          Result rows from the database. Each row
	 *                                must contain at least postnumber,
	 *                                cntaccess, and blog_id. Daily rows must
	 *                                also contain dp_date.
	 * @param bool     $daily         Whether rows are from the daily table.
	 * @param bool     $include_urls  Whether to append a "URL" column.
	 * @param bool     $network_wide  Whether this is a network-wide export
	 *                                (used for correct permalink resolution).
	 */
	public static function write_export_csv( $fh, array $rows, bool $daily, bool $include_urls = false, bool $network_wide = false ): void {
		// UTF-8 BOM for Excel / spreadsheet compatibility.
		fprintf( $fh, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

		$header = array( 'Post ID', 'Visits' );
		if ( $daily ) {
			$header[] = 'Date';
		}
		$header[] = 'Blog ID';
		if ( $include_urls ) {
			$header[] = 'URL';
		}

		fputcsv( $fh, $header, ',', '"', '\\' );

		$url_cache = array();
		foreach ( $rows as $row ) {
			$line = array( $row['postnumber'], $row['cntaccess'] );
			if ( $daily ) {
				$line[] = $row['dp_date'];
			}
			$line[] = $row['blog_id'];

			if ( $include_urls ) {
				$pid = (int) $row['postnumber'];
				$bid = (int) $row['blog_id'];
				if ( ! isset( $url_cache[ $bid ][ $pid ] ) ) {
					$url_cache[ $bid ][ $pid ] = is_multisite() && $network_wide
						? get_blog_permalink( $bid, $pid )
						: get_permalink( $pid );
				}
				$line[] = $url_cache[ $bid ][ $pid ];
			}

			fputcsv( $fh, $line, ',', '"', '\\' );
		}
	}

	/**
	 * Parse an import CSV file and return structured rows.
	 *
	 * Strips any UTF-8 BOM from the beginning of the file, reads the header
	 * to auto-detect whether the file targets the overall or daily table
	 * (by the presence of a "Date" column), then returns every data row in
	 * a canonical associative format.
	 *
	 * The caller is responsible for URL resolution, date normalisation,
	 * blog filtering, and database writes.
	 *
	 * @since 4.3.0
	 *
	 * @param string $file_path Path to the CSV file.
	 * @return array An associative array with the keys:
	 *               - daily: bool           Whether the file targets the daily table.
	 *               - rows:  array[]        Parsed data rows.  Each element is an
	 *                                       associative array with the keys
	 *                                       postnumber (int), cntaccess (int),
	 *                                       blog_id (int), plus dp_date (string)
	 *                                       when daily is true, and url (string)
	 *                                       when an optional URL column is present.
	 *               - total: int            Total number of data rows.
	 */
	public static function parse_import_file( string $file_path ): array {
		$handle = fopen( $file_path, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		if ( false === $handle ) {
			return array(
				'daily' => false,
				'rows'  => array(),
				'total' => 0,
			);
		}

		// Strip BOM if present.
		$bom = fread( $handle, 3 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread
		if ( ( chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) ) !== $bom ) {
			rewind( $handle );
		}

		$headers = fgetcsv( $handle, 0, ',', '"', '\\' );
		if ( false === $headers || empty( $headers ) ) {
			fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			return array(
				'daily' => false,
				'rows'  => array(),
				'total' => 0,
			);
		}

		$headers = array_map( 'trim', $headers );

		// Auto-detect daily vs overall from presence of a "Date" column.
		$daily       = in_array( 'Date', $headers, true );
		$col_post_id = 0;
		$col_count   = 1;
		$col_date    = $daily ? 2 : -1;
		$col_blog_id = $daily ? 3 : 2;
		$col_url     = $daily ? 4 : 3;
		$has_url     = isset( $headers[ $col_url ] ) && 'URL' === $headers[ $col_url ];

		$rows  = array();
		$total = 0;

		while ( false !== ( $line = fgetcsv( $handle, 0, ',', '"', '\\' ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			if ( empty( $line ) || ( 1 === count( $line ) && '' === $line[0] ) ) {
				continue;
			}

			$row = array(
				'postnumber' => isset( $line[ $col_post_id ] ) ? absint( $line[ $col_post_id ] ) : 0,
				'cntaccess'  => isset( $line[ $col_count ] ) ? absint( $line[ $col_count ] ) : 0,
				'blog_id'    => isset( $line[ $col_blog_id ] ) ? absint( $line[ $col_blog_id ] ) : get_current_blog_id(),
			);

			if ( $daily ) {
				$row['dp_date'] = isset( $line[ $col_date ] ) ? trim( $line[ $col_date ] ) : '';
			}
			if ( $has_url && isset( $line[ $col_url ] ) ) {
				$row['url'] = trim( $line[ $col_url ] );
			}

			$rows[] = $row;
			++$total;
		}

		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		return array(
			'daily' => $daily,
			'rows'  => $rows,
			'total' => $total,
		);
	}

	/**
	 * Fetch raw count rows from the database for CSV export.
	 *
	 * Called by both the admin Import/Export page and the WP-CLI
	 * `wp top10 counts export` command.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $daily        Whether to query the daily table.
	 * @param bool $network_wide Whether to collect rows from all sites (multisite only).
	 * @param int  $blog_id      Specific blog ID (0 = current site). Only used when
	 *                           $network_wide is false.
	 * @param int  $limit        Max rows to return (0 = all).
	 * @return array Raw result rows (ARRAY_A).
	 */
	public static function fetch_export_data( bool $daily, bool $network_wide = false, int $blog_id = 0, int $limit = 0 ): array {
		global $wpdb;

		$table = Database::get_table( $daily );

		if ( $network_wide && is_multisite() ) {
			$results = array();
			$sites   = get_sites( array( 'number' => 1000 ) );
			foreach ( $sites as $site ) {
				switch_to_blog( (int) $site->blog_id );
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$sql  = $wpdb->prepare( "SELECT * FROM `{$table}` WHERE blog_id = %d", (int) $site->blog_id );
				$rows = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
				restore_current_blog();
				$results = array_merge( $results, is_array( $rows ) ? $rows : array() );
				if ( $limit > 0 && count( $results ) >= $limit ) {
					break;
				}
			}
			return $limit > 0 ? array_slice( $results, 0, $limit ) : $results;
		}

		$bid = $blog_id > 0 ? $blog_id : get_current_blog_id();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$sql = $wpdb->prepare( "SELECT * FROM `{$table}` WHERE blog_id = %d", $bid );
		if ( $limit > 0 ) {
			$sql .= $wpdb->prepare( ' LIMIT %d', $limit );
		}

		$rows = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return is_array( $rows ) ? $rows : array();
	}
}
