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

namespace WebberZone\Top_Ten\Admin;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Import Export functions.
 *
 * @since 3.3.0
 */
class Import_Export {

	/**
	 * Parent Menu ID.
	 *
	 * @since 3.3.0
	 *
	 * @var string Parent Menu ID.
	 */
	public $parent_id;

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		add_filter( 'admin_init', array( __CLASS__, 'process_settings_import' ), 9 );
		add_filter( 'admin_init', array( __CLASS__, 'process_settings_export' ) );
		add_filter( 'admin_init', array( __CLASS__, 'export_tables' ) );
		add_filter( 'admin_init', array( __CLASS__, 'import_tables' ), 9 );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'network_admin_menu', array( $this, 'network_admin_menu' ), 11 );
	}

	/**
	 * Admin Menu.
	 *
	 * @since 3.3.0
	 */
	public function admin_menu() {

		$is_network_admin = ( 'network_admin_menu' === current_filter() );

		$this->parent_id = add_submenu_page(
			'tptn_dashboard',
			esc_html__( 'Top 10 Import Export Tables', 'top-10' ),
			esc_html__( 'Import/Export', 'top-10' ),
			'manage_options',
			'tptn_exim_page',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Admin Menu.
	 *
	 * @since 3.3.0
	 */
	public function network_admin_menu() {

		$this->parent_id = add_submenu_page(
			'tptn_network_pop_posts_page',
			esc_html__( 'Top 10 Import Export Tables', 'top-10' ),
			esc_html__( 'Import/Export', 'top-10' ),
			'manage_network_options',
			'tptn_exim_page',
			array( $this, 'render_page' )
		);
	}
	/**
	 * Render the tools settings page.
	 *
	 * @since 2.7.0
	 *
	 * @return void
	 */
	public function render_page() {

		/* Message for successful file import */
		if ( isset( $_GET['file_import'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( 'success' === $_GET['file_import'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				add_settings_error( 'tptn-notices', '', esc_html__( 'Data has been imported into the table', 'top-10' ), 'success' );
			} elseif ( 'fail' === $_GET['file_import'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				add_settings_error( 'tptn-notices', '', esc_html__( 'Data import failure. Check the number of columns for the file being imported, if you are uploading it in the right section below and that the data is correct', 'top-10' ), 'error' );
			}
		}

		/* Message for successful file import */
		if ( isset( $_GET['settings_import'] ) && 'success' === $_GET['settings_import'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			add_settings_error( 'tptn-notices', '', esc_html__( 'Settings have been imported successfully', 'top-10' ), 'success' );
		}

		ob_start();
		?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Top 10 - Import/Export tables', 'top-10' ); ?></h1>

		<?php settings_errors(); ?>

		<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
		<div id="post-body-content">

			<?php if ( ! is_network_admin() ) : ?>
			<form method="post">

				<h2 style="padding-left:0px"><?php esc_html_e( 'Export/Import settings', 'top-10' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Export the plugin settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'top-10' ); ?>
				</p>
				<p><input type="hidden" name="tptn_action" value="export_settings" /></p>
				<p>
					<?php submit_button( esc_html__( 'Export Settings', 'top-10' ), 'primary', 'tptn_export_settings', false ); ?>
				</p>

				<?php wp_nonce_field( 'tptn_export_settings_nonce', 'tptn_export_settings_nonce' ); ?>
			</form>

			<form method="post" enctype="multipart/form-data">

				<p class="description">
					<?php esc_html_e( 'Import the plugin settings from a .json file. This file can be obtained by exporting the settings on this/another site using the form above.', 'top-10' ); ?>
				</p>
				<p>
					<input type="file" name="import_settings_file" />
				</p>
				<p>
					<?php submit_button( esc_html__( 'Import Settings', 'top-10' ), 'primary', 'tptn_import_settings', false ); ?>
				</p>

				<input type="hidden" name="tptn_action" value="import_settings" />
				<?php wp_nonce_field( 'tptn_import_settings_nonce', 'tptn_import_settings_nonce' ); ?>
			</form>
			<?php endif; ?>

			<form method="post">

				<h2 style="padding-left:0px"><?php esc_html_e( 'Export tables', 'top-10' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Click the buttons below to export the overall and the daily tables. The file is downloaded as an CSV file which you should be able to edit in Excel or any other compatible software.', 'top-10' ); ?>
					<?php esc_html_e( 'If you are using WordPress Multisite then this will include the counts across all sites as the plugin uses a single table to store counts.', 'top-10' ); ?>
				</p>
				<p>
					<input type="hidden" name="tptn_action" value="export_tables" />
					<input type="hidden" name="network_wide" value="<?php echo ( is_network_admin() ? 1 : 0 ); ?>" />
				</p>
				<p>
					<?php submit_button( esc_html__( 'Export overall tables', 'top-10' ), 'primary', 'tptn_export_total', false ); ?>
					<?php submit_button( esc_html__( 'Export daily tables', 'top-10' ), 'primary', 'tptn_export_daily', false ); ?>
				</p>

				<?php wp_nonce_field( 'tptn_export_nonce', 'tptn_export_nonce' ); ?>
			</form>

			<form method="post" enctype="multipart/form-data">

				<h2 style="padding-left:0px"><?php esc_html_e( 'Import tables', 'top-10' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'This action will replace the data in the table being import. Best practice would be to first export the data using the buttons above. Following this, update the file with the new data and then import it. It is important to maintain the export format of the data to avoid corruption.', 'top-10' ); ?>
					<br />
					<?php esc_html_e( 'Be careful when opening the file in Excel as it tends to change the date format. Recommended date-time format is YYYY-MM-DD H.', 'top-10' ); ?>
				</p>
				<p class="description">
					<strong><?php esc_html_e( 'Backup your database before proceeding so you will be able to restore it in case anything goes wrong.', 'top-10' ); ?></strong>
				</p>
				<h4 style="padding-left:0px"><?php esc_html_e( 'Import Overall Table', 'top-10' ); ?></h4>
				<p>
					<input type="file" name="import_file" />
				</p>
				<p>
					<?php submit_button( esc_html__( 'Import Overall CSV', 'top-10' ), 'primary', 'tptn_import_total', false ); ?>
				</p>

				<h4 style="padding-left:0px"><?php esc_html_e( 'Import Daily Table', 'top-10' ); ?></h4>
				<p>
					<input type="file" name="import_file_daily" />
				</p>
				<p>
					<?php submit_button( esc_html__( 'Import Daily CSV', 'top-10' ), 'primary', 'tptn_import_daily', false ); ?>
				</p>

				<input type="hidden" name="tptn_action" value="import_tables" />
				<input type="hidden" name="network_wide" value="<?php echo ( is_network_admin() ? 1 : 0 ); ?>" />

				<?php wp_nonce_field( 'tptn_import_nonce', 'tptn_import_nonce' ); ?>
			</form>

		</div><!-- /#post-body-content -->

		<div id="postbox-container-1" class="postbox-container">

			<div id="side-sortables" class="meta-box-sortables ui-sortable">
				<?php include_once 'settings/sidebar.php'; ?>
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
	 * Process a settings export that generates a .csv file of the Top 10 table.
	 *
	 * @since 2.7.0
	 */
	public static function export_tables() {
		global $wpdb;

		if ( empty( $_POST['tptn_action'] ) || 'export_tables' !== $_POST['tptn_action'] ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_POST['tptn_export_nonce'] ), 'tptn_export_nonce' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			return;
		}

		if ( is_network_admin() && ! current_user_can( 'manage_network_options' ) ) {
			return;
		}

		if ( is_admin() && ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_POST['tptn_export_total'] ) ) {
			$daily = false;
		} elseif ( isset( $_POST['tptn_export_daily'] ) ) {
			$daily = true;
		} else {
			return;
		}

		if ( isset( $_POST['network_wide'] ) ) {
			$network_wide = intval( $_POST['network_wide'] );
		} else {
			return;
		}

		$table_name = \WebberZone\Top_Ten\Util\Helpers::get_tptn_table( $daily );

		$filename = array(
			'top-ten',
			( $daily ? 'daily' : '' ),
			'table',
			( is_network_admin() ? '' : 'blog' . get_current_blog_id() ),
			current_time( 'Y_m_d_Hi' ),
		);
		$filename = array_filter( $filename );
		$filename = implode( '-', $filename );
		$filename = $filename . '.csv';

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
		if ( ! $network_wide ) {
			$sql .= $wpdb->prepare( ' WHERE blog_id=%d ', get_current_blog_id() );
		}

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
		fclose( $fh ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		exit;
	}

	/**
	 * Process a .csv file to import the table into the database.
	 *
	 * @since 2.7.0
	 */
	public static function import_tables() {
		global $wpdb;

		if ( empty( $_POST['tptn_action'] ) || 'import_tables' !== $_POST['tptn_action'] ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_POST['tptn_import_nonce'] ), 'tptn_import_nonce' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			return;
		}

		if ( is_network_admin() && ! current_user_can( 'manage_network_options' ) ) {
			return;
		}

		if ( is_admin() && ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_POST['tptn_import_total'] ) ) {
			$daily        = false;
			$column_count = 3;
		} elseif ( isset( $_POST['tptn_import_daily'] ) ) {
			$daily        = true;
			$column_count = 4;
		} else {
			return;
		}

		if ( isset( $_POST['network_wide'] ) ) {
			$network_wide = intval( $_POST['network_wide'] );
		} else {
			return;
		}

		$table_name = \WebberZone\Top_Ten\Util\Helpers::get_tptn_table( $daily );
		$filename   = 'import_file';
		if ( $daily ) {
			$filename .= '_daily';
		}

		$tmp       = isset( $_FILES[ $filename ]['name'] ) ? explode( '.', sanitize_file_name( wp_unslash( $_FILES[ $filename ]['name'] ) ) ) : array();
		$extension = end( $tmp );

		if ( 'csv' !== $extension ) {
			wp_die( esc_html__( 'Please upload a valid .csv file', 'top-10' ) );
		}

		$import_file = isset( $_FILES[ $filename ]['tmp_name'] ) ? ( wp_unslash( $_FILES[ $filename ]['tmp_name'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( empty( $import_file ) ) {
			wp_die( esc_html__( 'Please upload a file to import', 'top-10' ) );
		}

		$data        = array();
		$file_import = '';

		// Open uploaded CSV file with read-only mode.
		$csv_file = fopen( $import_file, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen

		// Skip first line.
		fgetcsv( $csv_file );

		while ( ( $line = fgetcsv( $csv_file, 100, ',' ) ) !== false ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition

			if ( count( $line ) !== $column_count ) {
				$file_import = 'fail';
				break;
			}

			if ( $daily ) {

				if ( ! is_network_admin() && ( (int) get_current_blog_id() !== (int) $line[3] ) ) {
					continue;
				}

				$dp_date = new \DateTime( $line[2] );
				$dp_date = $dp_date->format( 'Y-m-d H' );

				$data[] = $wpdb->prepare( '( %d, %d, %s, %d )', $line[0], $line[1], $dp_date, $line[3] );
			} else {
				if ( ! is_network_admin() && ( (int) get_current_blog_id() !== (int) $line[2] ) ) {
					continue;
				}

				$data[] = $wpdb->prepare( '( %d, %d, %d )', $line[0], $line[1], $line[2] );
			}
		}

		// Close file.
		fclose( $csv_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		$result = false;
		if ( ! empty( $data ) ) {
			// Truncate the table before import.
			\WebberZone\Top_Ten\Util\Helpers::trunc_count( $daily, (bool) $network_wide );

			if ( $daily ) {
				$result = $wpdb->query( "INSERT INTO {$table_name} (postnumber, cntaccess, dp_date, blog_id) VALUES " . implode( ',', $data ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			} else {
				$result = $wpdb->query( "INSERT INTO {$table_name} (postnumber, cntaccess, blog_id) VALUES " . implode( ',', $data ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			}
		}

		$file_import = $result ? 'success' : 'fail';

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'        => 'tptn_exim_page',
					'file_import' => $file_import,
				),
				is_network_admin() ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Process a settings export that generates a .json file of the Top 10 settings
	 *
	 * @since 2.7.0
	 */
	public static function process_settings_export() {

		if ( empty( $_POST['tptn_action'] ) || 'export_settings' !== $_POST['tptn_action'] ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_POST['tptn_export_settings_nonce'] ), 'tptn_export_settings_nonce' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = get_option( 'tptn_settings' );

		ignore_user_abort( true );

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=tptn-settings-export-' . gmdate( 'm-d-Y' ) . '.json' );
		header( 'Expires: 0' );

		echo wp_json_encode( $settings );
		exit;
	}

	/**
	 * Process a settings import from a json file
	 *
	 * @since 2.7.0
	 */
	public static function process_settings_import() {

		if ( empty( $_POST['tptn_action'] ) || 'import_settings' !== $_POST['tptn_action'] ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_POST['tptn_import_settings_nonce'] ), 'tptn_import_settings_nonce' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$filename  = 'import_settings_file';
		$extension = isset( $_FILES[ $filename ]['name'] ) ? pathinfo( sanitize_file_name( wp_unslash( $_FILES[ $filename ]['name'] ) ), PATHINFO_EXTENSION ) : '';

		if ( 'json' !== $extension ) {
			wp_die( esc_html__( 'Please upload a valid .json file', 'top-10' ) );
		}

		$import_file = isset( $_FILES[ $filename ]['tmp_name'] ) ? ( wp_unslash( $_FILES[ $filename ]['tmp_name'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( empty( $import_file ) ) {
			wp_die( esc_html__( 'Please upload a file to import', 'top-10' ) );
		}

		// Retrieve the settings from the file and convert the json object to an array.
		$settings = (array) json_decode( file_get_contents( $import_file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		update_option( 'tptn_settings', $settings );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'            => 'tptn_exim_page',
					'settings_import' => 'success',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

}
