<?php
/**
 * Import Export class.
 *
 * @package WebberZone\Top_Ten\Admin
 */

namespace WebberZone\Top_Ten\Admin;

use WebberZone\Top_Ten\Database;
use WebberZone\Top_Ten\Util\Helpers;
use WebberZone\Top_Ten\Util\Hook_Registry;
use WebberZone\Top_Ten\Util\Csv_Helper;

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
		Hook_Registry::add_filter( 'admin_init', array( $this, 'process_settings_import' ), 9 );
		Hook_Registry::add_filter( 'admin_init', array( $this, 'process_settings_export' ) );
		Hook_Registry::add_filter( 'admin_init', array( $this, 'export_tables' ) );
		Hook_Registry::add_filter( 'admin_init', array( $this, 'import_tables' ), 9 );
		Hook_Registry::add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		Hook_Registry::add_action( 'network_admin_menu', array( $this, 'network_admin_menu' ), 11 );
		Hook_Registry::add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

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
							<?php esc_html_e( 'Importing a file will add or increment counts — it does not replace existing data. To start fresh, use the Maintenance tab to truncate the tables first. Best practice is to export before importing. It is important to maintain the export format of the data to avoid corruption.', 'top-10' ); ?>
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

		$results = Csv_Helper::fetch_export_data( $daily, (bool) $network_wide, get_current_blog_id() );

		ignore_user_abort( true );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( "Content-Disposition: attachment; filename={$filename}" );
		header( 'Expires: 0' );

		$fh = fopen( 'php://output', 'w' );
		Csv_Helper::write_export_csv( $fh, $results, $daily, (bool) $export_urls, (bool) $network_wide );
		fclose( $fh ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		exit;
	}

	/**
	 * Process a .csv file to import the table into the database.
	 *
	 * @since 2.7.0
	 */
	public static function import_tables() {
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

		$overall_upload = ! empty( $_FILES['overall_table_file']['tmp_name'] );
		$daily_upload   = ! empty( $_FILES['daily_table_file']['tmp_name'] );

		if ( ! $overall_upload && ! $daily_upload ) {
			return;
		}

		if ( isset( $_POST['network_wide'] ) ) {
			$network_wide = (bool) intval( $_POST['network_wide'] );
		} else {
			return;
		}

		if ( isset( $_POST['import_urls'] ) ) {
			$use_urls = (bool) intval( $_POST['import_urls'] );
		} else {
			$use_urls = false;
		}

		$results = array();

		if ( $overall_upload ) {
			$import_file = wp_unslash( $_FILES['overall_table_file']['tmp_name'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$parsed      = Csv_Helper::parse_import_file( $import_file );

			if ( 0 === $parsed['total'] ) {
				wp_safe_redirect(
					add_query_arg(
						array(
							'page'        => 'tptn_exim_page',
							'file_import' => 'fail',
						),
						is_network_admin() ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' )
					)
				);
				exit;
			}

			$prepared = self::build_import_data( $parsed['rows'], false, $use_urls, $network_wide );
			if ( ! empty( $prepared ) ) {
				$results[] = Database::bulk_upsert( $prepared, false );
			}
		}

		if ( $daily_upload ) {
			$import_file = wp_unslash( $_FILES['daily_table_file']['tmp_name'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$parsed      = Csv_Helper::parse_import_file( $import_file );

			if ( 0 === $parsed['total'] ) {
				wp_safe_redirect(
					add_query_arg(
						array(
							'page'        => 'tptn_exim_page',
							'file_import' => 'fail',
						),
						is_network_admin() ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' )
					)
				);
				exit;
			}

			$prepared = self::build_import_data( $parsed['rows'], true, $use_urls, $network_wide );
			if ( ! empty( $prepared ) ) {
				$results[] = Database::bulk_upsert( $prepared, true );
			}
		}

		$all_success = ! empty( $results ) && ! in_array( false, $results, true );
		$file_import = $all_success ? 'success' : 'fail';

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
	 * Build structured import data from parsed CSV rows.
	 *
	 * @since 4.3.0
	 *
	 * @param array $rows         Parsed rows from Csv_Helper::parse_import_file().
	 * @param bool  $daily        Whether these are daily-table rows.
	 * @param bool  $use_urls     Whether to resolve URL column to post IDs.
	 * @param bool  $network_wide Whether this is a network-wide import.
	 * @return array Prepared data rows for Database::bulk_upsert().
	 */
	private static function build_import_data( array $rows, bool $daily, bool $use_urls, bool $network_wide ): array {
		$prepared = array();
		$url_list = array();

		foreach ( $rows as $row ) {
			$post_id = absint( $row['postnumber'] );
			$blog_id = isset( $row['blog_id'] ) ? absint( $row['blog_id'] ) : get_current_blog_id();

			if ( $use_urls && ! empty( $row['url'] ) ) {
				$url = esc_url_raw( trim( $row['url'] ) );
				if ( ! isset( $url_list[ $url ] ) ) {
					$url_list[ $url ] = url_to_postid( $url );
				}
				$post_id = absint( $url_list[ $url ] );
			}

			if ( 0 === $post_id ) {
				continue;
			}

			if ( ! $network_wide && ! is_network_admin() && (int) get_current_blog_id() !== $blog_id ) {
				continue;
			}

			if ( $daily ) {
				$raw_date = isset( $row['dp_date'] ) ? trim( $row['dp_date'] ) : '';
				try {
					$dp_date = ( new \DateTime( $raw_date ) )->format( 'Y-m-d H' );
				} catch ( \Exception $e ) {
					$dp_date = $raw_date;
				}
				$prepared[] = array(
					'postnumber' => $post_id,
					'cntaccess'  => absint( $row['cntaccess'] ),
					'dp_date'    => $dp_date,
					'blog_id'    => $blog_id,
				);
			} else {
				$prepared[] = array(
					'postnumber' => $post_id,
					'cntaccess'  => absint( $row['cntaccess'] ),
					'blog_id'    => $blog_id,
				);
			}
		}

		return $prepared;
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
