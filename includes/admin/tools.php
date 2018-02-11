<?php
/**
 * The admin-specific functionality of the plugin.
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
 * Render the tools settings page.
 *
 * @since 2.5.0
 *
 * @return void
 */
function tptn_tools_page() {

	/* Truncate overall posts table */
	if ( ( isset( $_POST['tptn_recreate_primary_key'] ) ) && ( check_admin_referer( 'tptn-tools-settings' ) ) ) {
		tptn_recreate_primary_key();
		add_settings_error( 'tptn-notices', '', esc_html__( 'Primary Key has been recreated', 'top-10' ), 'error' );
	}

	/* Truncate overall posts table */
	if ( ( isset( $_POST['tptn_trunc_all'] ) ) && ( check_admin_referer( 'tptn-tools-settings' ) ) ) {
		tptn_trunc_count( false );
		add_settings_error( 'tptn-notices', '', esc_html__( 'Top 10 popular posts reset', 'top-10' ), 'error' );
	}

	/* Truncate daily posts table */
	if ( ( isset( $_POST['tptn_trunc_daily'] ) ) && ( check_admin_referer( 'tptn-tools-settings' ) ) ) {
		tptn_trunc_count( true );
		add_settings_error( 'tptn-notices', '', esc_html__( 'Top 10 daily popular posts reset', 'top-10' ), 'error' );
	}

	/* Delete old settings */
	if ( ( isset( $_POST['tptn_delete_old_settings'] ) ) && ( check_admin_referer( 'tptn-tools-settings' ) ) ) {
		delete_option( 'ald_tptn_settings' );
		add_settings_error( 'tptn-notices', '', esc_html__( 'Old settings key has been deleted', 'top-10' ), 'error' );
	}

	/* Clean duplicates */
	if ( ( isset( $_POST['tptn_clean_duplicates'] ) ) && ( check_admin_referer( 'tptn-tools-settings' ) ) ) {
		tptn_clean_duplicates( true );
		tptn_clean_duplicates( false );
		add_settings_error( 'tptn-notices', '', esc_html__( 'Duplicate rows cleaned from the tables', 'top-10' ), 'error' );
	}

	/* Merge blog IDs */
	if ( ( isset( $_POST['tptn_merge_blogids'] ) ) && ( check_admin_referer( 'tptn-tools-settings' ) ) ) {
		tptn_merge_blogids( true );
		tptn_merge_blogids( false );
		add_settings_error( 'tptn-notices', '', esc_html__( 'Post counts across blog IDs 0 and 1 have been merged', 'top-10' ), 'error' );
	}

	ob_start();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Top 10 Tools', 'top-10' ); ?></h1>

		<?php settings_errors(); ?>

		<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
		<div id="post-body-content">

			<form method="post" >

				<h2 style="padding-left:0px"><?php esc_html_e( 'Clear cache', 'top-10' ); ?></h2>
				<p>
					<input type="button" name="cache_clear" id="cache_clear"  value="<?php esc_attr_e( 'Clear cache', 'top-10' ); ?>" class="button button-secondary" onclick="return clearCache();" />
				</p>
				<p class="description">
					<?php esc_html_e( 'Clear the Top 10 cache. This will also be cleared automatically when you save the settings page.', 'top-10' ); ?>
				</p>

				<h2 style="padding-left:0px"><?php esc_html_e( 'Recreate Primary Key', 'top-10' ); ?></h2>
				<p>
					<input name="tptn_recreate_primary_key" type="submit" id="tptn_recreate_primary_key" value="<?php esc_attr_e( 'Recreate Primary Key', 'top-10' ); ?>" class="button button-secondary" />
				</p>
				<p class="description">
					<?php esc_html_e( 'Deletes and reinitializes the primary key in the database tables.', 'top-10' ); ?>
				</p>

				<h2 style="padding-left:0px"><?php esc_html_e( 'Reset database', 'top-10' ); ?></h2>
				<p>
					<input name="tptn_trunc_all" type="submit" id="tptn_trunc_all" value="<?php esc_attr_e( 'Reset Popular Posts Network-wide', 'top-10' ); ?>" class="button button-secondary" style="color:#f00" onclick="if (!confirm('<?php esc_attr_e( 'Are you sure you want to reset the popular posts?', 'top-10' ); ?>')) return false;" />
					<input name="tptn_trunc_daily" type="submit" id="tptn_trunc_daily" value="<?php esc_attr_e( 'Reset Daily Popular Posts Network-wide', 'top-10' ); ?>" class="button button-secondary" style="color:#f00" onclick="if (!confirm('<?php esc_attr_e( 'Are you sure you want to reset the daily popular posts?', 'top-10' ); ?>')) return false;" />
				</p>
				<p class="description">
					<?php esc_html_e( 'This will reset the Top 10 tables. If you are running Top 10 on multisite then it will delete the popular posts across the entire network. This cannot be reversed. Make sure that your database has been backed up before proceeding', 'top-10' ); ?>
				</p>

				<h2 style="padding-left:0px"><?php esc_html_e( 'Other tools', 'top-10' ); ?></h2>
				<p>
					<input name="tptn_delete_old_settings" type="submit" id="tptn_delete_old_settings" value="<?php esc_attr_e( 'Delete old settings', 'top-10' ); ?>" class="button button-secondary" onclick="if (!confirm('<?php esc_attr_e( 'This will delete the settings before v2.5.x. Proceed?', 'top-10' ); ?>')) return false;" />
				</p>
				<p class="description">
					<?php esc_html_e( 'From v2.5.x, Top 10 stores the settings in a new key in the database. This will delete the old settings for the current blog. It is recommended that you do this at the earliest after upgrade. However, you should do this only if you are comfortable with the new settings.', 'top-10' ); ?>
				</p>

				<p>
					<input name="tptn_merge_blogids" type="submit" id="tptn_merge_blogids" value="<?php esc_attr_e( 'Merge blog ID 0 and 1 post counts', 'top-10' ); ?>" class="button button-secondary" onclick="if (!confirm('<?php esc_attr_e( 'This will merge post counts for blog IDs 0 and 1. Proceed?', 'top-10' ); ?>')) return false;" />
				</p>
				<p class="description">
					<?php esc_html_e( 'This will merge post counts for posts with table entries of 0 and 1', 'top-10' ); ?>
				</p>

				<p>
					<input name="tptn_clean_duplicates" type="submit" id="tptn_clean_duplicates" value="<?php esc_attr_e( 'Merge duplicates across blog IDs', 'top-10' ); ?>" class="button button-secondary" onclick="if (!confirm('<?php esc_attr_e( 'This will delete the duplicate entries in the tables. Proceed?', 'top-10' ); ?>')) return false;" />
				</p>
				<p class="description">
					<?php esc_html_e( 'In older versions, the plugin created entries with duplicate post IDs. Clicking the button below will merge these duplicate IDs', 'top-10' ); ?>
				</p>
				<?php wp_nonce_field( 'tptn-tools-settings' ); ?>
			</form>

		</div><!-- /#post-body-content -->

		<div id="postbox-container-1" class="postbox-container">

			<div id="side-sortables" class="meta-box-sortables ui-sortable">
				<?php include_once 'sidebar.php'; ?>
			</div><!-- /#side-sortables -->

		</div><!-- /#postbox-container-1 -->
		</div><!-- /#post-body -->
		<br class="clear" />
		</div><!-- /#poststuff -->

	</div><!-- /.wrap -->

	<?php
	echo ob_get_clean(); // WPCS: XSS OK.
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

	$wpdb->query( 'CREATE TEMPORARY TABLE ' . $table_name . '_temp AS SELECT * FROM ' . $table_name . ' GROUP BY postnumber' ); // WPCS: unprepared SQL OK.
	$wpdb->query( "TRUNCATE TABLE $table_name" ); // WPCS: unprepared SQL OK.
	$wpdb->query( 'INSERT INTO ' . $table_name . ' SELECT * FROM ' . $table_name . '_temp' ); // WPCS: unprepared SQL OK.
}


/**
 * Function to merge counts with post numbers of blog ID 0 and 1 respectively.
 *
 * @since   2.0.4
 *
 * @param   bool $daily  Daily flag.
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

	$wpdb->query( "DELETE FROM $table_name WHERE blog_id = 0" ); // WPCS: unprepared SQL OK.
}

/**
 * Function to delete and create the primary keys in the database table.
 *
 * @since   2.5.6
 */
function tptn_recreate_primary_key() {
	global $wpdb;

	$table_name       = $wpdb->base_prefix . 'top_ten';
	$table_name_daily = $wpdb->base_prefix . 'top_ten_daily';

	$wpdb->hide_errors();

	if ( $wpdb->query( $wpdb->prepare( "SHOW INDEXES FROM {$table_name} WHERE Key_name = %s", 'PRIMARY' ) ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' DROP PRIMARY KEY ' ); // WPCS: unprepared SQL OK.
	}
	if ( $wpdb->query( $wpdb->prepare( "SHOW INDEXES FROM {$table_name_daily} WHERE Key_name = %s", 'PRIMARY' ) ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name_daily . ' DROP PRIMARY KEY ' ); // WPCS: unprepared SQL OK.
	}

	$wpdb->query( 'ALTER TABLE ' . $table_name . ' ADD PRIMARY KEY(postnumber, blog_id) ' ); // WPCS: unprepared SQL OK.
	$wpdb->query( 'ALTER TABLE ' . $table_name_daily . ' ADD PRIMARY KEY(postnumber, dp_date, blog_id) ' ); // WPCS: unprepared SQL OK.

	$wpdb->show_errors();
}

