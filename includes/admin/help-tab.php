<?php
/**
 * Help tab.
 *
 * Functions to generated the help tab on the Settings page.
 *
 * @link  https://webberzone.com
 * @since 2.5.0
 *
 * @package Top 10
 * @subpackage Admin/Help
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Generates the settings help page.
 *
 * @since 2.5.0
 */
function tptn_settings_help() {
	global $tptn_settings_page;

	$screen = get_current_screen();

	if ( $screen->id !== $tptn_settings_page ) {
		return;
	}

	$screen->set_help_sidebar(
		/* translators: 1: Support link. */
		'<p>' . sprintf( __( 'For more information or how to get support visit the <a href="%1$s">WebberZone support site</a>.', 'top-10' ), esc_url( 'https://webberzone.com/support/' ) ) . '</p>' .
		/* translators: 1: Forum link. */
		'<p>' . sprintf( __( 'Support queries should be posted in the <a href="%1$s">WordPress.org support forums</a>.', 'top-10' ), esc_url( 'https://wordpress.org/support/plugin/top-10' ) ) . '</p>' .
		'<p>' . sprintf(
			/* translators: 1: Github Issues link, 2: Github page. */
			__( '<a href="%1$s">Post an issue</a> on <a href="%2$s">GitHub</a> (bug reports only).', 'top-10' ),
			esc_url( 'https://github.com/WebberZone/top-10/issues' ),
			esc_url( 'https://github.com/WebberZone/top-10' )
		) . '</p>'
	);

	$screen->add_help_tab(
		array(
			'id'        => 'tptn-settings-general',
			'title'     => __( 'General', 'top-10' ),
			'content'   =>
			'<p>' . __( 'This screen provides the basic settings for configuring your knowledgebase.', 'top-10' ) . '</p>' .
				'<p>' . __( 'Set the knowledgebase slugs which drive what the urls are for the knowledgebase homepage, articles, categories and tags.', 'top-10' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'        => 'tptn-settings-styles',
			'title'     => __( 'Styles', 'top-10' ),
			'content'   =>
			'<p>' . __( 'This screen provides options to control the look and feel of the knowledgebase.', 'top-10' ) . '</p>' .
				'<p>' . __( 'Disable the styles included within the plugin and/or add your own CSS styles to customize this.', 'top-10' ) . '</p>',
		)
	);

	do_action( 'tptn_settings_help', $screen );

}
