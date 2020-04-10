<?php
/**
 * Top 10 Display Network statistics page.
 *
 * @package   Top_Ten
 * @subpackage  Top_Ten_Network_Statistics
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2008-2020 Ajay D'Souza
 */

/**** If this file is called directly, abort. ****/
if ( ! defined( 'WPINC' ) ) {
	die;
}


if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Top_Ten_Network_Statistics class.
 */
class Top_Ten_Network_Statistics {

	/**
	 * Class instance.
	 *
	 * @var class Class instance.
	 */
	public static $instance;

	/**
	 * WP_List_Table object.
	 *
	 * @var object WP_List_Table object.
	 */
	public $pop_posts_obj;

	/**
	 * Class constructor.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_filter( 'set-screen-option', array( __CLASS__, 'set_screen' ), 10, 3 );
	}

	/**
	 * Set screen.
	 *
	 * @param  string $status Status of screen.
	 * @param  string $option Option name.
	 * @param  string $value  Option value.
	 * @return string Value.
	 */
	public static function set_screen( $status, $option, $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundBeforeLastUsed
		return $value;
	}

	/**
	 * Plugin settings page
	 */
	public function plugin_settings_page() {
		$args = null;
		if ( isset( $_REQUEST['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$page = sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Top 10 - Network Wide Popular Posts', 'top-10' ); ?></h1>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="get">
								<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>" />
								<?php

								// If this is a post date filter?
								if ( isset( $_REQUEST['post-date-filter-to'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
									$args['post-date-filter-to'] = sanitize_text_field( wp_unslash( $_REQUEST['post-date-filter-to'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
								}

								if ( isset( $_REQUEST['post-date-filter-from'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
									$args['post-date-filter-from'] = sanitize_text_field( wp_unslash( $_REQUEST['post-date-filter-from'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
								}

								$this->pop_posts_obj->prepare_items( $args );

								$this->pop_posts_obj->display();
								?>
							</form>
						</div>
					</div>
					<div id="postbox-container-1" class="postbox-container">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">
							<?php include_once 'sidebar.php'; ?>
						</div><!-- /side-sortables -->
					</div><!-- /postbox-container-1 -->
				</div><!-- /post-body -->
				<br class="clear" />
			</div><!-- /poststuff -->
		</div>
		<?php
	}

	/**
	 * Screen options
	 */
	public function screen_option() {
		$option = 'per_page';
		$args   = array(
			'label'   => __( 'Popular Posts', 'top-10' ),
			'default' => 20,
			'option'  => 'pop_posts_per_page',
		);
		add_screen_option( $option, $args );
		$this->pop_posts_obj = new Top_Ten_Network_Statistics_Table();
	}

	/** Singleton instance */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}

/**
 * Function to initialise stats page.
 *
 * @since 2.4.2
 */
function tptn_network_stats_page() {
	Top_Ten_Statistics::get_instance();
}
add_action( 'plugins_loaded', 'tptn_network_stats_page' );
