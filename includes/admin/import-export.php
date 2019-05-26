<?php
/**
 * Functions to import and export the Top 10 data.
 *
 * @link  https://webberzone.com
 * @since 2.7.0
 *
 * @package    Top 10
 * @subpackage Admin/Tools/Import_Export
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Render the tools settings page.
 *
 * @since 2.7.0
 *
 * @return void
 */
function tptn_exim_page() {

	/* Truncate overall posts table */
	if ( isset( $_GET['file_import'] ) && 'success' === $_GET['file_import'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		add_settings_error( 'tptn-notices', '', esc_html__( 'Data has been imported into the table', 'top-10' ), 'updated' );
	}

	ob_start();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Top 10 - Import/Export tables', 'top-10' ); ?></h1>

		<?php settings_errors(); ?>

		<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
		<div id="post-body-content">

			<form method="post" >

				<h2 style="padding-left:0px"><?php esc_html_e( 'Export tables', 'top-10' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Click the buttons below to export the overall and the daily tables. The file is downloaded as an CSV file which you should be able to edit in Excel or any other compatible software.', 'top-10' ); ?>
					<?php esc_html_e( 'If you are using WordPress Multisite then this will include the counts across all sites as the plugin uses a single table to store counts.', 'top-10' ); ?>
				</p>
				<p><input type="hidden" name="tptn_action" value="export_settings" /></p>
				<p>
					<?php submit_button( esc_html__( 'Export overall tables', 'top-10' ), 'primary', 'tptn_export_total', false ); ?>
					<?php submit_button( esc_html__( 'Export daily tables', 'top-10' ), 'primary', 'tptn_export_daily', false ); ?>
				</p>

				<?php wp_nonce_field( 'tptn_export_nonce', 'tptn_export_nonce' ); ?>
			</form>

			<form method="post" enctype="multipart/form-data">

				<h2 style="padding-left:0px"><?php esc_html_e( 'Import tables', 'top-10' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'This action will replace the data in your tables, so I suggest that you export the existing data using the buttons above, amend it and then import it. It is important to maintain the export format of the data to avoid corruption.', 'top-10' ); ?>
				</p>
				<p class="description">
					<strong><?php esc_html_e( 'Backup your database before proceeding so you will be able to restore it in case anything goes wrong.', 'top-10' ); ?></strong>
				</p>
				<h4 style="padding-left:0px"><?php esc_html_e( 'Import Overall Table', 'top-10' ); ?></h4>
				<p>
					<input type="file" name="import_file" />
				</p>
				<p>
					<input type="hidden" name="tptn_action" value="import_settings" />
					<?php submit_button( esc_html__( 'Import Overall CSV', 'top-10' ), 'primary', 'tptn_import_total', false ); ?>
				</p>

				<h4 style="padding-left:0px"><?php esc_html_e( 'Import Daily Table', 'top-10' ); ?></h4>
				<p>
					<input type="file" name="import_file_daily" />
				</p>
				<p>
					<input type="hidden" name="tptn_action" value="import_settings" />
					<?php submit_button( esc_html__( 'Import Daily CSV', 'top-10' ), 'primary', 'tptn_import_daily', false ); ?>
				</p>

				<?php wp_nonce_field( 'tptn_import_nonce', 'tptn_import_nonce' ); ?>
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
	echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}


/**
 * Process a settings export that generates a .json file of the shop settings
 *
 * @since 2.7.0
 */
function tptn_export_tables() {
	global $wpdb;

	if ( empty( $_POST['tptn_action'] ) || 'export_settings' !== $_POST['tptn_action'] ) {
		return;
	}

	if ( isset( $_POST['tptn_export_nonce'] ) && ! wp_verify_nonce( sanitize_key( $_POST['tptn_export_nonce'] ), 'tptn_export_nonce' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['tptn_export_total'] ) ) {
		$daily = false;
	} elseif ( isset( $_POST['tptn_export_daily'] ) ) {
		$daily = true;
	} else {
		return;
	}

	$table_name = $wpdb->base_prefix . 'top_ten';
	if ( $daily ) {
		$table_name .= '_daily';
	}

	$filename = 'top-ten' . ( $daily ? '-daily' : '' ) . '-table-' . current_time( 'Y_m_d_Hi' ) . '.csv';

	$header_row = array(
		esc_html__( 'Post ID', 'top-10' ),
		esc_html__( 'Visits', 'top-10' ),
	);
	if ( $daily ) {
		$header_row[] = esc_html__( 'Date', 'top-10' );
	}
	$header_row[] = esc_html__( 'Blog ID', 'top-10' );

	$data_rows = array();

	$sql = 'SELECT * FROM ' . $table_name;

	$results = $wpdb->get_results( $sql, 'ARRAY_A' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

	foreach ( $results as $result ) {
		$row = array(
			$result['postnumber'],
			$result['cntaccess'],
		);
		if ( $daily ) {
			$row[] = $result['dp_date'];
		}
		$row[] = $result['blog_id'];

		$data_rows[] = $row;
	}

	ignore_user_abort( true );

	$fh = fopen( 'php://output', 'w' );
	fprintf( $fh, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( "Content-Disposition: attachment; filename={$filename}" );
	header( 'Expires: 0' );

	fputcsv( $fh, $header_row );
	foreach ( $data_rows as $data_row ) {
		fputcsv( $fh, $data_row );
	}
	fclose( $fh ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose

	exit;
}
add_action( 'admin_init', 'tptn_export_tables' );

/**
 * Process a settings export that generates a .json file of the shop settings
 *
 * @since 2.7.0
 */
function tptn_import_tables() {
	global $wpdb;

	if ( empty( $_POST['tptn_action'] ) || 'import_settings' !== $_POST['tptn_action'] ) {
		return;
	}

	if ( isset( $_POST['tptn_import_nonce'] ) && ! wp_verify_nonce( sanitize_key( $_POST['tptn_import_nonce'] ), 'tptn_import_nonce' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['tptn_import_total'] ) ) {
		$daily = false;
	} elseif ( isset( $_POST['tptn_import_daily'] ) ) {
		$daily = true;
	} else {
		return;
	}

	$table_name = $wpdb->base_prefix . 'top_ten';
	$filename   = 'import_file';
	if ( $daily ) {
		$table_name .= '_daily';
		$filename   .= '_daily';
	}

	$extension = isset( $_FILES[ $filename ]['name'] ) ? end( explode( '.', sanitize_file_name( wp_unslash( $_FILES[ $filename ]['name'] ) ) ) ) : '';

	if ( 'csv' !== $extension ) {
		wp_die( esc_html__( 'Please upload a valid .csv file', 'top-10' ) );
	}

	$import_file = isset( $_FILES[ $filename ]['tmp_name'] ) ? ( wp_unslash( $_FILES[ $filename ]['tmp_name'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	if ( empty( $import_file ) ) {
		wp_die( esc_html__( 'Please upload a file with a valid name (no special characters) to import', 'top-10' ) );
	}

	// Truncate the table before import.
	tptn_trunc_count( $daily );

	// Open uploaded CSV file with read-only mode.
	$csv_file = fopen( $import_file, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen

	// Skip first line.
	fgetcsv( $csv_file );

	while ( ( $line = fgetcsv( $csv_file, 100, ',' ) ) !== false ) { // phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition

		if ( $daily ) {
			$dp_date = str_replace( '/', '-', $line[2] );
			$dp_date = date( 'Y-m-d H', strtotime( $dp_date ) );

			$wpdb->query( $wpdb->prepare( "INSERT INTO {$table_name} (postnumber, cntaccess, dp_date, blog_id) VALUES( %d, %d, %s, %d ) ", $line[0], $line[1], $dp_date, $line[3] ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		} else {
			$wpdb->query( $wpdb->prepare( "INSERT INTO {$table_name} (postnumber, cntaccess, blog_id) VALUES( %d, %d, %d ) ", $line[0], $line[1], $line[2] ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}
	}

	// Close file.
	fclose( $csv_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'        => 'tptn_exim_page',
				'file_import' => 'success',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;

}
add_action( 'admin_init', 'tptn_import_tables' );
