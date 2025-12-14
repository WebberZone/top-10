<?php
/**
 * Import Export class.
 *
 * @package WebberZone\Top_Ten\Admin
 */

namespace WebberZone\Top_Ten\Admin;

use WebberZone\Top_Ten\Database;
use WebberZone\Top_Ten\Util\Helpers;

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
		add_filter( 'admin_init', array( $this, 'process_settings_import' ), 9 );
		add_filter( 'admin_init', array( $this, 'process_settings_export' ) );
		add_filter( 'admin_init', array( $this, 'export_tables' ) );
		add_filter( 'admin_init', array( $this, 'import_tables' ), 9 );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'network_admin_menu', array( $this, 'network_admin_menu' ), 11 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		new WPP_Importer();
	}

	/**
	 * Enqueue scripts in admin area.
	 *
	 * @since 4.0.0
	 *
	 * @param string $hook The current admin page.
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( $hook === $this->parent_id ) {
			wp_enqueue_script( 'top-ten-admin-js' );
			wp_enqueue_style( 'top-ten-admin-css' );

		}
	}

	/**
	 * Admin Menu.
	 *
	 * @since 3.3.0
	 */
	public function admin_menu() {

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
			'tptn_dashboard',
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
		<?php do_action( 'tptn_settings_page_header' ); ?>

		<?php settings_errors(); ?>

		<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
		<div id="post-body-content">

			<?php if ( ! is_network_admin() ) : ?>
			<div class="postbox">
				<h2><span><?php esc_html_e( 'Export/Import settings', 'top-10' ); ?></span></h2>
				<div class="inside">
					<form method="post">
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
				</div>
			</div>
			<?php endif; ?>

			<div class="postbox">
				<h2><span><?php esc_html_e( 'Export tables', 'top-10' ); ?></span></h2>
				<div class="inside">
					<form method="post">
						<p class="description">
							<?php esc_html_e( 'Click the buttons below to export the overall and the daily tables. The file is downloaded as an CSV file which you should be able to edit in Excel or any other compatible software.', 'top-10' ); ?>
							<?php esc_html_e( 'If you are using WordPress Multisite then this will include the counts across all sites as the plugin uses a single table to store counts.', 'top-10' ); ?>
						</p>
						<p>
							<label><input type="checkbox" name="export_urls" id="export_urls" value="1" /> <?php esc_html_e( 'Include URLs in export', 'top-10' ); ?></label>
							<input type="hidden" name="tptn_action" value="export_tables" />
							<input type="hidden" name="network_wide" value="<?php echo ( is_network_admin() ? 1 : 0 ); ?>" />
						</p>
						<p>
							<?php submit_button( esc_html__( 'Export overall tables', 'top-10' ), 'primary', 'tptn_export_total', false ); ?>
							<?php submit_button( esc_html__( 'Export daily tables', 'top-10' ), 'primary', 'tptn_export_daily', false ); ?>
						</p>

						<?php wp_nonce_field( 'tptn_export_nonce', 'tptn_export_nonce' ); ?>
					</form>
				</div>
			</div>

			<div class="postbox">
				<h2><span><?php esc_html_e( 'Import tables', 'top-10' ); ?></span></h2>
				<div class="inside">
					<form method="post" enctype="multipart/form-data">
						<p class="description">
							<?php esc_html_e( 'This action will replace the data in the table being import. Best practice would be to first export the data using the buttons above. Following this, update the file with the new data and then import it. It is important to maintain the export format of the data to avoid corruption.', 'top-10' ); ?>
							<br />
							<?php esc_html_e( 'Be careful when opening the file in Excel as it tends to change the date format. Recommended date-time format is YYYY-MM-DD H.', 'top-10' ); ?>
						</p>
						<p class="notice notice-warning">
							<strong><?php esc_html_e( 'Backup your database before proceeding so you will be able to restore it in case anything goes wrong.', 'top-10' ); ?></strong>
						</p>
						<p>
							<label><input type="checkbox" name="import_urls" id="import_urls" value="1" /> <?php esc_html_e( 'Use URLs instead of Post IDs in import', 'top-10' ); ?></label>
							<input type="hidden" name="tptn_action" value="import_tables" />
							<input type="hidden" name="network_wide" value="<?php echo ( is_network_admin() ? 1 : 0 ); ?>" />
						</p>

						<table class="form-table">
							<tr>
								<th scope="row"><?php esc_html_e( 'Import overall tables', 'top-10' ); ?></th>
								<td><input type="file" name="overall_table_file" /><br />
								<span class="description"><?php esc_html_e( 'CSV file', 'top-10' ); ?></span></td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Import daily tables', 'top-10' ); ?></th>
								<td><input type="file" name="daily_table_file" /><br />
								<span class="description"><?php esc_html_e( 'CSV file', 'top-10' ); ?></span></td>
							</tr>
						</table>
						<p>
							<?php submit_button( esc_html__( 'Import Tables', 'top-10' ), 'primary', 'tptn_import', false ); ?>
						</p>

						<?php wp_nonce_field( 'tptn_import_nonce', 'tptn_import_nonce' ); ?>
					</form>
				</div>
			</div>

			<?php
			/**
			 * Action hook to add additional import/export options.
			 *
			 * @since 3.3.0
			 */
			do_action( 'tptn_admin_import_export_tab_content' );
			?>

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

		if ( isset( $_POST['export_urls'] ) ) {
			$export_urls = intval( $_POST['export_urls'] );
		} else {
			$export_urls = 0;
		}

		$table_name = Database::get_table( $daily );

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

		if ( $export_urls ) {
			$header_row[] = esc_html__( 'URL', 'top-10' );
		}

		$data_rows = array();
		$url_list  = array();

		global $wpdb;

		// Use the Database class to fetch data.
		$results = array();
		if ( $network_wide ) {
			// For network-wide export, we need to get all blogs.
			if ( is_multisite() ) {
				$sites = get_sites( array( 'number' => 1000 ) );
				foreach ( $sites as $site ) {
					switch_to_blog( (int) $site->blog_id );
					$blog_results = self::get_blog_results( $daily, (int) $site->blog_id );
					$results      = array_merge( $results, $blog_results );
					restore_current_blog();
				}
			}
		} else {
			$results = self::get_blog_results( $daily, get_current_blog_id() );
		}

		foreach ( $results as $result ) {
			$row = array(
				$result['postnumber'],
				$result['cntaccess'],
			);
			if ( $daily ) {
				$row[] = $result['dp_date'];
			}
			$row[] = $result['blog_id'];

			if ( $export_urls ) {
				// Check if $result['postnumber'] is found in $url_list array. $url_list is an associative array with post ID as key and URL as value.
				if ( isset( $url_list[ $result['postnumber'] ] ) ) {
					$row[] = $url_list[ $result['postnumber'] ];
				} else {
					// If $result['postnumber'] is not found in $url_list array, get URL from $result['postnumber'] and add it to $url_list array.
					$row[] = ( is_multisite() && $network_wide ) ? get_blog_permalink( $result['blog_id'], $result['postnumber'] ) : get_permalink( $result['postnumber'] );

					$url_list[ $result['postnumber'] ] = $row[4];
				}
			}

			$data_rows[] = $row;
		}

		ignore_user_abort( true );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( "Content-Disposition: attachment; filename={$filename}" );
		header( 'Expires: 0' );

		$fh = fopen( 'php://output', 'w' );
		fprintf( $fh, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

		fputcsv( $fh, $header_row, ',', '"', '\\' );
		foreach ( $data_rows as $data_row ) {
			fputcsv( $fh, $data_row, ',', '"', '\\' );
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

		if ( isset( $_POST['import_urls'] ) ) {
			$import_urls = intval( $_POST['import_urls'] );
			++$column_count;
		} else {
			$import_urls = 0;
		}

		if ( isset( $_POST['reset_tables'] ) ) {
			$reset_tables = intval( $_POST['reset_tables'] );
		} else {
			$reset_tables = 0;
		}

		$table_name = Database::get_table( $daily );
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
		$url_list    = array();
		$file_import = '';

		// Open uploaded CSV file with read-only mode.
		$csv_file = fopen( $import_file, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen

		// Skip first line.
		fgetcsv( $csv_file, 1000, ',', '"', '\\' );

		while ( ( $line = fgetcsv( $csv_file, 1000, ',', '"', '\\' ) ) !== false ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition

			if ( count( $line ) !== $column_count ) {
				$file_import = 'fail';
				break;
			}

			if ( $import_urls ) {
				$url = $daily ? $line[4] : $line[3];
				$url = trim( $url );
				$url = esc_url_raw( $url );

				// Check if $url is found in $url_list array. $url_list is an associative array with $url as key and post ID as value.
				if ( isset( $url_list[ $url ] ) ) {
					$line[0] = $url_list[ $url ];
				} else {
					// If $url is not found in $url_list array, get post ID from $url and add it to $url_list array.
					$line[0] = url_to_postid( $url );
					if ( 0 === $line[0] ) {
						continue;
					}
					$url_list[ $url ] = $line[0];
				}
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
			if ( $reset_tables ) {
				// Truncate the table before import.
				if ( ! is_multisite() || (bool) $network_wide ) {
					Database::truncate_table( Database::get_table( $daily ) );
				} else {
					\WebberZone\Top_Ten\Counter::delete_counts( array( 'daily' => $daily ) );
				}
			}

			// Prepare data for bulk upsert.
			$prepared_data = array();
			foreach ( $data as $row ) {
				$line = explode( ',', trim( $row, '()' ) );
				$line = array_map( 'trim', $line );
				$line = array_map( 'str_replace', array_fill( 0, count( $line ), "'" ), array_fill( 0, count( $line ), '' ), $line );

				if ( $daily ) {
					$prepared_data[] = array(
						'postnumber' => (int) $line[0],
						'cntaccess'  => (int) $line[1],
						'dp_date'    => $line[2],
						'blog_id'    => (int) $line[3],
					);
				} else {
					$prepared_data[] = array(
						'postnumber' => (int) $line[0],
						'cntaccess'  => (int) $line[1],
						'blog_id'    => (int) $line[2],
					);
				}
			}

			$result = Database::bulk_upsert( $prepared_data, $daily );
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

	/**
	 * Get results for a specific blog.
	 *
	 * @param bool $daily   Whether to get daily results.
	 * @param int  $blog_id Blog ID.
	 * @return array Results.
	 */
	private static function get_blog_results( $daily, $blog_id ) {
		global $wpdb;

		$table = Database::get_table( $daily );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$sql = $wpdb->prepare( "SELECT * FROM {$table} WHERE blog_id = %d", $blog_id );

		return $wpdb->get_results( $sql, 'ARRAY_A' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
	}
}
