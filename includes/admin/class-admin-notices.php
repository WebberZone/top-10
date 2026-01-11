<?php
/**
 * Admin Notices class.
 *
 * @package WebberZone\Top_Ten\Admin
 */

namespace WebberZone\Top_Ten\Admin;

use WebberZone\Top_Ten\Database;
use WebberZone\Top_Ten\Util\Hook_Registry;

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
		Hook_Registry::add_action( 'admin_notices', array( $this, 'missing_table_notice' ) );
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

		// Use Database class directly.
		if ( ! Database::are_tables_installed() ) {
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