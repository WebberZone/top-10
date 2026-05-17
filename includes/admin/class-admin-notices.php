<?php
/**
 * Admin Notices class.
 *
 * @package WebberZone\Top_Ten\Admin
 */

namespace WebberZone\Top_Ten\Admin;

use WebberZone\Top_Ten\Database;
use WebberZone\Top_Ten\Util\Hook_Registry;
use function WebberZone\Top_Ten\wz_top_ten;

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
		Hook_Registry::add_action( 'admin_init', array( $this, 'register_notices' ) );
	}

	/**
	 * Register notices with the Admin_Notices_API.
	 *
	 * @since 4.3.0
	 */
	public function register_notices() {
		$admin_notices_api = wz_top_ten()->admin->admin_notices_api ?? null;
		if ( ! $admin_notices_api ) {
			return;
		}

		// Result notice after running the create-missing-tables action.
		if ( isset( $_GET['tptn_tables_created'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$status = sanitize_key( wp_unslash( $_GET['tptn_tables_created'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( 'created' === $status ) {
				$admin_notices_api->register_notice(
					array(
						'id'          => 'tptn_tables_created',
						'message'     => '<p>' . esc_html__( 'Top 10: Missing database tables were created successfully.', 'top-10' ) . '</p>',
						'type'        => 'success',
						'dismissible' => true,
						'capability'  => 'manage_options',
					)
				);
			} elseif ( 'failed' === $status ) {
				$admin_notices_api->register_notice(
					array(
						'id'          => 'tptn_tables_create_failed',
						'message'     => '<p>' . esc_html__( 'Top 10: One or more database tables could not be created. Please check your database permissions or try the Recreate tables option on the Tools page.', 'top-10' ) . '</p>',
						'type'        => 'error',
						'dismissible' => true,
						'capability'  => 'manage_options',
					)
				);
			}
		}

		$missing = $this->get_missing_tables();
		if ( empty( $missing ) ) {
			return;
		}

		$admin_notices_api->register_notice(
			array(
				'id'          => 'tptn_missing_tables',
				'message'     => $this->build_missing_table_message( $missing ),
				'type'        => 'warning',
				'dismissible' => false,
				'capability'  => 'manage_options',
			)
		);
	}

	/**
	 * Get the list of missing Top 10 tables.
	 *
	 * @since 4.3.0
	 *
	 * @return array Array of HTML-formatted missing table descriptions.
	 */
	private function get_missing_tables(): array {
		$all_tables = array(
			Database::get_table( false ) => __( 'Top 10 (total counts)', 'top-10' ),
			Database::get_table( true )  => __( 'Top 10 Daily (daily counts)', 'top-10' ),
			Database::get_log_table()    => __( 'Visits Log', 'top-10' ),
			Database::get_funnel_table() => __( 'Visits Funnel', 'top-10' ),
		);

		$missing = array();
		foreach ( $all_tables as $table => $label ) {
			if ( ! Database::is_table_installed( $table ) ) {
				$missing[] = sprintf( '<code>%s</code> (%s)', esc_html( $table ), esc_html( $label ) );
			}
		}

		return $missing;
	}

	/**
	 * Build the missing-tables notice message HTML.
	 *
	 * @since 4.3.0
	 *
	 * @param array $missing Array of HTML-formatted missing table descriptions.
	 * @return string Notice HTML.
	 */
	private function build_missing_table_message( array $missing ): string {
		$create_url = wp_nonce_url(
			admin_url( 'admin.php?action=tptn_create_missing_tables' ),
			'tptn-create-missing-tables'
		);

		$items = '';
		foreach ( $missing as $item ) {
			$items .= '<li>' . wp_kses( $item, array( 'code' => array() ) ) . '</li>';
		}

		return sprintf(
			'<p><strong>%1$s</strong></p><ul style="list-style:disc;padding-left:2em;margin:0 0 .5em;">%2$s</ul><p><a href="%3$s" class="button button-primary">%4$s</a></p>',
			esc_html__( 'Top 10: The following database tables are missing:', 'top-10' ),
			$items,
			esc_url( $create_url ),
			esc_html__( 'Create missing tables', 'top-10' )
		);
	}
}
