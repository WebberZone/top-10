<?php
/**
 * Controls admin notices.
 *
 * @package Top_Ten
 */

namespace WebberZone\Top_Ten\Admin;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Notices Class.
 *
 * @since 4.1.0
 */
class Admin_Notices {

	/**
	 * Constructor class.
	 *
	 * @since 4.1.0
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'missing_table_notice' ) );
	}

	/**
	 * Display admin notice if the tables are not created.
	 *
	 * @since 4.1.0
	 */
	public function missing_table_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $wpdb;

		$table_name       = $wpdb->base_prefix . 'top_ten';
		$table_name_daily = $wpdb->base_prefix . 'top_ten_daily';

		if ( ! Activator::is_table_installed( $table_name ) || ! Activator::is_table_installed( $table_name_daily ) ) {
			?>
			<div class="notice notice-warning">
				<p>
					<?php esc_html_e( 'Top 10: Some tables are missing, which will affect search results.', 'top-10' ); ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=tptn_tools_page' ) ); ?>">
						<?php esc_html_e( 'Click here to recreate tables.', 'top-10' ); ?>
					</a>
				</p>
			</div>
			<?php
		}
	}
}