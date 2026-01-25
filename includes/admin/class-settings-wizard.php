<?php
/**
 * Settings Wizard class.
 *
 * @package WebberZone\Top_Ten\Admin
 */

namespace WebberZone\Top_Ten\Admin;

use WebberZone\Top_Ten\Admin\Settings\Settings_Wizard_API;
use WebberZone\Top_Ten\Admin\Settings;
use function WebberZone\Top_Ten\wz_top_ten;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Settings Wizard class for Top 10.
 *
 * @since 4.2.0
 */
class Settings_Wizard extends Settings_Wizard_API {

	/**
	 * Main constructor class.
	 *
	 * @since 4.2.0
	 */
	public function __construct() {
		$settings_key = 'tptn_settings';
		$prefix       = 'tptn';

		$args = array(
			'steps'               => $this->get_wizard_steps(),
			'translation_strings' => $this->get_translation_strings(),
			'page_slug'           => 'tptn_wizard',
			'menu_args'           => array(
				'parent'     => 'tptn_options_page',
				'capability' => 'manage_options',
			),
		);

		parent::__construct( $settings_key, $prefix, $args );
		$this->additional_hooks();
	}

	/**
	 * Additional hooks specific to Top 10.
	 *
	 * @since 4.2.0
	 */
	protected function additional_hooks() {
		// Trigger wizard setup logic on plugin activation.
		add_action( 'tptn_activate', array( $this, 'trigger_wizard_on_activation' ) );
		add_action( 'admin_init', array( $this, 'register_wizard_notice' ) );

		// Register Tom Select AJAX handlers for wizard taxonomy fields.
		add_action( 'wp_ajax_nopriv_' . $this->prefix . '_taxonomy_search_tom_select', array( Settings::class, 'taxonomy_search_tom_select' ) );
		add_action( 'wp_ajax_' . $this->prefix . '_taxonomy_search_tom_select', array( Settings::class, 'taxonomy_search_tom_select' ) );
	}

	/**
	 * Get wizard steps configuration.
	 *
	 * @since 4.2.0
	 *
	 * @return array Wizard steps.
	 */
	public function get_wizard_steps() {
		$all_settings_grouped = Settings::get_registered_settings();
		$all_settings         = array();
		foreach ( $all_settings_grouped as $section_settings ) {
			$all_settings = array_merge( $all_settings, $section_settings );
		}

		$basic_settings_keys = array(
			'add_to',
			'limit',
			'post_types',
			'range_desc',
			'daily_range',
			'hour_range',
		);

		$display_settings_keys = array(
			'title',
			'title_daily',
			'show_excerpt',
			'show_author',
			'show_date',
			'post_thumb_op',
			'thumb_size',
		);

		$content_tuning_keys = array(
			'exclude_front',
			'exclude_post_ids',
			'exclude_cat_slugs',
			'exclude_on_cat_slugs',
		);

		$admin_settings_keys = array(
			'show_metabox',
			'show_metabox_admins',
			'pv_in_admin',
			'show_count_non_admins',
		);

		$pro_settings_keys = array(
			'use_global_settings',
			'admin_column_post_types',
			'show_dashboard_to_roles',
			'max_execution_time',
		);

		$steps = array(
			'welcome'         => array(
				'title'       => __( 'Welcome to Top 10', 'top-10' ),
				'description' => __( 'Thank you for installing Top 10! This wizard will help you configure the essential settings to get your popular posts list working perfectly.', 'top-10' ),
				'settings'    => array(),
			),
			'basic_settings'  => array(
				'title'       => __( 'Basic Settings', 'top-10' ),
				'description' => __( 'Configure how Top 10 tracks and counts your popular posts.', 'top-10' ),
				'settings'    => $this->build_step_settings( $basic_settings_keys, $all_settings ),
			),
			'display_options' => array(
				'title'       => __( 'Display Options', 'top-10' ),
				'description' => __( 'Customize how your popular posts list will look and what information to display.', 'top-10' ),
				'settings'    => $this->build_step_settings( $display_settings_keys, $all_settings ),
			),
			'content_tuning'  => array(
				'title'       => __( 'Content Tuning', 'top-10' ),
				'description' => __( 'Fine-tune which content is included and how posts are excluded from the list.', 'top-10' ),
				'settings'    => $this->build_step_settings( $content_tuning_keys, $all_settings ),
			),
			'admin_settings'  => array(
				'title'       => __( 'Admin Settings', 'top-10' ),
				'description' => __( 'Configure how Top 10 integrates with your admin area, dashboards, and user roles.', 'top-10' ),
				'settings'    => $this->build_step_settings( $admin_settings_keys, $all_settings ),
			),
			'pro_settings'    => array(
				'title'       => __( 'Pro Settings', 'top-10' ),
				'description' => __( 'Configure Pro-only options such as global block settings, category exclusions, and query optimisation. These features require Top 10 Pro.', 'top-10' ),
				'settings'    => $this->build_step_settings( $pro_settings_keys, $all_settings ),
			),
		);

		/**
		 * Filter wizard steps.
		 *
		 * @param array $steps Wizard steps.
		 */
		return apply_filters( 'tptn_wizard_steps', $steps );
	}

	/**
	 * Build settings array for a wizard step from keys.
	 *
	 * @since 4.2.0
	 *
	 * @param array $keys Setting keys for this step.
	 * @param array $all_settings All settings array.
	 * @return array
	 */
	protected function build_step_settings( $keys, $all_settings ) {
		$step_settings = array();

		foreach ( $keys as $key ) {
			if ( isset( $all_settings[ $key ] ) ) {
				$step_settings[ $key ] = $all_settings[ $key ];
			}
		}

		return $step_settings;
	}

	/**
	 * Get translation strings for the wizard.
	 *
	 * @since 4.2.0
	 *
	 * @return array Translation strings.
	 */
	public function get_translation_strings() {
		return array(
			'page_title'            => __( 'Top 10 Setup Wizard', 'top-10' ),
			'menu_title'            => __( 'Setup Wizard', 'top-10' ),
			'next_step'             => __( 'Next Step', 'top-10' ),
			'previous_step'         => __( 'Previous Step', 'top-10' ),
			'finish_setup'          => __( 'Finish Setup', 'top-10' ),
			'skip_wizard'           => __( 'Skip Wizard', 'top-10' ),
			/* translators: %s: Search query. */
			'tom_select_no_results' => __( 'No results found for "%s"', 'top-10' ),
			'steps_nav_aria_label'  => __( 'Setup Wizard Steps', 'top-10' ),
			/* translators: %1$d: Current step number, %2$d: Total number of steps */
			'step_of'               => __( 'Step %1$d of %2$d', 'top-10' ),
			'wizard_complete'       => __( 'Setup Complete!', 'top-10' ),
			'setup_complete'        => __( 'Your Top 10 plugin has been configured successfully. You can now start displaying popular posts on your site!', 'top-10' ),
			'go_to_settings'        => __( 'Go to Settings', 'top-10' ),
			'checkbox_modified'     => __( 'Modified from default setting', 'top-10' ),
		);
	}

	/**
	 * Trigger wizard on plugin activation.
	 *
	 * @since 4.2.0
	 */
	public function trigger_wizard_on_activation() {
		// Set a transient that will trigger the wizard on first admin page visit.
		// This works better than an option because it's temporary and won't persist
		// if the wizard is never accessed.
		set_transient( 'tptn_show_wizard_activation_redirect', true, HOUR_IN_SECONDS );

		// Also set an option for more persistent storage in multisite environments.
		update_option( 'tptn_show_wizard', true );
	}

	/**
	 * Register the wizard notice with the Admin_Notices_API.
	 *
	 * @since 4.2.0
	 */
	public function register_wizard_notice() {
		$admin_notices_api = wz_top_ten()->admin->admin_notices_api;
		if ( ! $admin_notices_api ) {
			return;
		}

		$admin_notices_api->register_notice(
			array(
				'id'          => 'tptn_wizard_notice',
				'message'     => sprintf(
					'<p>%s</p><p><a href="%s" class="button button-primary">%s</a></p>',
					esc_html__( 'Welcome to Top 10! Would you like to run the setup wizard to configure the plugin?', 'top-10' ),
					esc_url( admin_url( 'admin.php?page=tptn_wizard' ) ),
					esc_html__( 'Run Setup Wizard', 'top-10' )
				),
				'type'        => 'info',
				'dismissible' => true,
				'capability'  => 'manage_options',
				'conditions'  => array(
					function () {
						$page = sanitize_key( (string) filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );

						return ! $this->is_wizard_completed() &&
							! get_option( 'tptn_wizard_notice_dismissed', false ) &&
							( get_transient( 'tptn_show_wizard_activation_redirect' ) || get_option( 'tptn_show_wizard', false ) ) &&
							'tptn_wizard' !== $page;
					},
				),
			)
		);
	}

	/**
	 * Get the URL to redirect to after wizard completion.
	 *
	 * @since 4.2.0
	 *
	 * @return string Redirect URL.
	 */
	protected function get_completion_redirect_url() {
		return admin_url( 'admin.php?page=tptn_options_page' );
	}

	/**
	 * Override render_wizard_page to handle custom steps.
	 *
	 * @since 4.2.0
	 */
	public function render_wizard_page() {
		$this->current_step = $this->get_current_step();
		$step_config        = $this->get_current_step_config();

		if ( empty( $step_config ) ) {
			$this->render_completion_page();
			return;
		}

		// Check if this is a custom step.
		if ( ! empty( $step_config['custom_step'] ) ) {
			$this->render_custom_tables_step( $step_config );
			return;
		}

		// Use parent method for regular steps.
		parent::render_wizard_page();
	}

	/**
	 * Render the custom tables indexing step.
	 *
	 * @since 4.2.0
	 *
	 * @param array $step_config Step configuration.
	 */
	protected function render_custom_tables_step( $step_config ) {
		?>
		<div class="wrap wizard-wrap">
			<h1><?php echo esc_html( $this->translation_strings['wizard_title'] ); ?></h1>

			<?php $this->render_wizard_steps_navigation(); ?>

			<div class="wizard-progress">
				<div class="wizard-progress-bar">
					<div class="wizard-progress-fill" style="width: <?php echo esc_attr( (string) ( ( $this->current_step / $this->total_steps ) * 100 ) ); ?>%;"></div>
				</div>
				<p class="wizard-step-counter">
					<?php
					printf(
						esc_html( $this->translation_strings['step_of'] ),
						esc_html( (string) $this->current_step ),
						esc_html( (string) $this->total_steps )
					);
					?>
				</p>
			</div>

			<div class="wizard-content">
				<div class="wizard-step">
					<h2><?php echo esc_html( $step_config['title'] ?? '' ); ?></h2>
					
					<?php if ( ! empty( $step_config['description'] ) ) : ?>
						<p class="wizard-step-description"><?php echo wp_kses_post( $step_config['description'] ); ?></p>
					<?php endif; ?>

					<form method="post" action="">
						<?php wp_nonce_field( "{$this->prefix}_wizard_nonce", "{$this->prefix}_wizard_nonce" ); ?>
						
						<div class="wizard-fields">
							<?php $this->render_custom_tables_interface(); ?>
						</div>

						<div class="wizard-actions">
							<?php $this->render_wizard_buttons(); ?>
						</div>
					</form>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the custom tables indexing interface.
	 *
	 * @since 4.2.0
	 */
	protected function render_custom_tables_interface() {
		// Top 10 does not implement the CRP custom tables UI in its wizard.
		// Intentionally left blank.
	}

	/**
	 * Override the render completion page to show Top 10 specific content.
	 *
	 * @since 4.2.0
	 */
	protected function render_completion_page() {
		?>
		<div class="wrap wizard-wrap wizard-complete">
			<div class="wizard-completion-header">
				<h1><?php echo esc_html( $this->translation_strings['wizard_complete'] ); ?></h1>
				<p class="wizard-completion-message">
					<?php echo esc_html( $this->translation_strings['setup_complete'] ); ?>
				</p>
			</div>

			<div class="wizard-completion-content">
				<div class="wizard-completion-actions">
					<a href="<?php echo esc_url( $this->get_completion_redirect_url() ); ?>" class="button button-primary button-large">
						<?php esc_html_e( 'Go to Settings', 'top-10' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=tptn_tools_page' ) ); ?>" class="button button-secondary button-large">
						<?php esc_html_e( 'Go to Tools', 'top-10' ); ?>
					</a>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="button button-secondary button-large" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'View Site', 'top-10' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php
	}
}
