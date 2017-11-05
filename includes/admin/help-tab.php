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
			'id'      => 'tptn-settings-general',
			'title'   => __( 'General', 'top-10' ),
			'content' =>
			'<p>' . __( 'This screen provides the basic settings for configuring Top 10.', 'top-10' ) . '</p>' .
				'<p>' . __( 'Enable the trackers and cache, configure basic tracker settings and uninstall settings.', 'top-10' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'tptn-settings-counter',
			'title'   => __( 'Counter/Tracker', 'top-10' ),
			'content' =>
			'<p>' . __( 'This screen provides settings to tweak the display counter and the tracker.', 'top-10' ) . '</p>' .
				'<p>' . __( 'Choose where to display the counter and customize the text. Select the type of tracker and which user groups to track.', 'top-10' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'tptn-settings-list',
			'title'   => __( 'Posts list', 'top-10' ),
			'content' =>
			'<p>' . __( 'This screen provides settings to tweak the output of the list of popular posts.', 'top-10' ) . '</p>' .
				'<p>' . __( 'Set the number of posts, which categories or posts to exclude, customize what to display and specific basic HTML markup used to create the posts.', 'top-10' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'tptn-settings-thumbnail',
			'title'   => __( 'Thumbnail', 'top-10' ),
			'content' =>
			'<p>' . __( 'This screen provides settings to tweak the thumbnail that can be displayed for each post in the list.', 'top-10' ) . '</p>' .
				'<p>' . __( 'Set the location and size of the thumbnail. Additionally, you can choose additional sources for the thumbnail i.e. a meta field, first image or a default thumbnail when nothing is available.', 'top-10' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'tptn-settings-styles',
			'title'   => __( 'Styles', 'top-10' ),
			'content' =>
			'<p>' . __( 'This screen provides options to control the look and feel of the popular posts list.', 'top-10' ) . '</p>' .
				'<p>' . __( 'Choose for default set of styles or add your own custom CSS to tweak the display of the posts.', 'top-10' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'tptn-settings-maintenance',
			'title'   => __( 'Maintenance', 'top-10' ),
			'content' =>
			'<p>' . __( 'This screen provides options to control the maintenance cron.', 'top-10' ) . '</p>' .
				'<p>' . __( 'Choose how often to run maintenance and at what time of the day.', 'top-10' ) . '</p>',
		)
	);

	do_action( 'tptn_settings_help', $screen );

}

/**
 * Generates the Tools help page.
 *
 * @since 2.5.0
 */
function tptn_settings_tools_help() {
	global $tptn_settings_tools_help;

	$screen = get_current_screen();

	if ( $screen->id !== $tptn_settings_tools_help ) {
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
			'id'      => 'tptn-settings-general',
			'title'   => __( 'General', 'top-10' ),
			'content' =>
			'<p>' . __( 'This screen provides some tools that help maintain certain features of Top 10.', 'top-10' ) . '</p>' .
				'<p>' . __( 'Clear the cache, reset the popular posts tables plus some miscellaneous fixes for older versions of Top 10.', 'top-10' ) . '</p>',
		)
	);

	do_action( 'tptn_settings_tools_help', $screen );
}
