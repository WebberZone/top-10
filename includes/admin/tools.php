<?php
/**
 * The admin-specific tools.
 *
 * @link  https://webberzone.com
 * @since 2.5.0
 *
 * @package    Top 10
 * @subpackage Admin/Tools
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Function to delete all duplicate rows in the posts table.
 *
 * @since   1.6.2
 *
 * @param   bool $daily  Daily flag.
 */
function tptn_clean_duplicates( $daily = false ) {
	global $wpdb;

	$table_name = $wpdb->base_prefix . 'top_ten';
	if ( $daily ) {
		$table_name .= '_daily';
	}

	$wpdb->query( 'CREATE TEMPORARY TABLE ' . $table_name . '_temp AS SELECT * FROM ' . $table_name . ' GROUP BY postnumber' );
	$wpdb->query( "TRUNCATE TABLE $table_name" );
	$wpdb->query( 'INSERT INTO ' . $table_name . ' SELECT * FROM ' . $table_name . '_temp' );
}


/**
 * Function to merge counts with post numbers of blog ID 0 and 1 respectively.
 *
 * @since   2.0.4
 *
 * @param   bool $daily  Daily flag
 */
function tptn_merge_blogids( $daily = false ) {
	global $wpdb;

	$table_name = $wpdb->base_prefix . 'top_ten';
	if ( $daily ) {
		$table_name .= '_daily';
	}

	if ( $daily ) {
		$wpdb->query(
			"
            INSERT INTO `$table_name` (postnumber, cntaccess, dp_date, blog_id) (
                SELECT
                    postnumber,
                    SUM(cntaccess) as sumCount,
                    dp_date,
                    1
                FROM `$table_name`
                WHERE blog_ID IN (0,1)
                GROUP BY postnumber, dp_date
            ) ON DUPLICATE KEY UPDATE cntaccess = VALUES(cntaccess);
        "
		);
	} else {
		$wpdb->query(
			"
            INSERT INTO `$table_name` (postnumber, cntaccess, blog_id) (
                SELECT
                    postnumber,
                    SUM(cntaccess) as sumCount,
                    1
                FROM `$table_name`
                WHERE blog_ID IN (0,1)
                GROUP BY postnumber
            ) ON DUPLICATE KEY UPDATE cntaccess = VALUES(cntaccess);
        "
		);
	}

	$wpdb->query( "DELETE FROM $table_name WHERE blog_id = 0" );
}
