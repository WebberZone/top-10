<?php
/**
 * Top 10 Display Network statistics page.
 *
 * @package   Top_Ten
 * @subpackage  Top_Ten_Network_Statistics
 */

namespace WebberZone\Top_Ten\Admin\Network;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Top_Ten_Network_Statistics class.
 */
class Statistics {

	/**
	 * WP_List_Table object.
	 *
	 * @var \WebberZone\Top_Ten\Admin\Statistics_Table
	 */
	public $pop_posts_table;

	/**
	 * Parent Menu ID.
	 *
	 * @since 3.3.0
	 *
	 * @var string Parent Menu ID.
	 */
	public $parent_id;

	/**
	 * Class constructor.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_filter( 'set-screen-option', array( __CLASS__, 'set_screen' ), 10, 3 );
		add_action( 'network_admin_menu', array( $this, 'network_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts in admin area.
	 *
	 * @since 3.0.0
	 *
	 * @param string $hook The current admin page.
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( $hook === $this->parent_id ) {
			wp_enqueue_script( 'top-ten-admin-js' );
			wp_enqueue_style( 'tptn-admin-ui-css', );
		}
	}

	/**
	 * Admin Menu.
	 *
	 * @since 3.0.0
	 */
	public function network_admin_menu() {
		$this->parent_id = add_menu_page(
			esc_html__( 'Top 10 - Network Popular Posts', 'top-10' ),
			esc_html__( 'Top 10', 'top-10' ),
			'manage_network_options',
			'tptn_network_pop_posts_page',
			array( $this, 'render_page' ),
			'dashicons-editor-ol'
		);

		add_submenu_page(
			'tptn_network_pop_posts_page',
			esc_html__( 'Top 10 - Network Popular Posts', 'top-10' ),
			esc_html__( 'Popular Posts', 'top-10' ),
			'manage_network_options',
			'tptn_network_pop_posts_page',
			array( $this, 'render_page' )
		);
		add_action( "load-{$this->parent_id}", array( $this, 'screen_option' ) );
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
	public function render_page() {

		$page = '';
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
								$this->pop_posts_table->prepare_items();
								$this->hidden_inputs();
								$this->pop_posts_table->display();
								?>
							</form>
						</div>
					</div>
					<div id="postbox-container-1" class="postbox-container">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">
							<?php \WebberZone\Top_Ten\Admin\Admin::display_admin_sidebar(); ?>
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
		$this->pop_posts_table = new \WebberZone\Top_Ten\Admin\Statistics_Table( true );
	}

	/**
	 * Displays the hidden inputs.
	 */
	public function hidden_inputs() {
		if ( ! empty( $_REQUEST['orderby'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			echo '<input type="hidden" name="orderby" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) ) . '" />'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		if ( ! empty( $_REQUEST['order'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			echo '<input type="hidden" name="order" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) . '" />'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
	}
}
