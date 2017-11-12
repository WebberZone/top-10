<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link  https://webberzone.com
 * @since 2.5.0
 *
 * @package    Top 10
 * @subpackage Admin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Creates the admin submenu pages under the Downloads menu and assigns their
 * links to global variables
 *
 * @since 2.5.0
 *
 * @global $tptn_settings_page
 * @return void
 */
function tptn_add_admin_pages_links() {
	global $tptn_settings_page, $tptn_settings_tools_help;

	$tptn_settings_page = add_menu_page( esc_html__( 'Top 10 Settings', 'top-10' ), esc_html__( 'Top 10', 'top-10' ), 'manage_options', 'tptn_options_page', 'tptn_options_page', 'dashicons-editor-ol' );
	add_action( "load-$tptn_settings_page", 'tptn_settings_help' ); // Load the settings contextual help.
	add_action( "admin_head-$tptn_settings_page", 'tptn_adminhead' ); // Load the admin head.

	$plugin_page = add_submenu_page( 'tptn_options_page', esc_html__( 'Top 10 Settings', 'top-10' ), esc_html__( 'Settings', 'top-10' ), 'manage_options', 'tptn_options_page', 'tptn_options_page' );
	add_action( 'admin_head-' . $plugin_page, 'tptn_adminhead' );

	$tptn_settings_tools_help = add_submenu_page( 'tptn_options_page', esc_html__( 'Top 10 Tools', 'top-10' ), esc_html__( 'Tools', 'top-10' ), 'manage_options', 'tptn_tools_page', 'tptn_tools_page' );
	add_action( "load-$tptn_settings_tools_help", 'tptn_settings_tools_help' );
	add_action( 'admin_head-' . $tptn_settings_tools_help, 'tptn_adminhead' );

	// Initialise Top 10 Statistics pages.
	$tptn_stats_screen = new Top_Ten_Statistics();

	$plugin_page = add_submenu_page( 'tptn_options_page', __( 'Top 10 Popular Posts', 'top-10' ), __( 'Popular Posts', 'top-10' ), 'manage_options', 'tptn_popular_posts', array( $tptn_stats_screen, 'plugin_settings_page' ) );
	add_action( "load-$plugin_page", array( $tptn_stats_screen, 'screen_option' ) );
	add_action( 'admin_head-' . $plugin_page, 'tptn_adminhead' );

	$plugin_page = add_submenu_page( 'tptn_options_page', __( 'Top 10 Daily Popular Posts', 'top-10' ), __( 'Daily Popular Posts', 'top-10' ), 'manage_options', 'tptn_popular_posts&orderby=daily_count&order=desc', array( $tptn_stats_screen, 'plugin_settings_page' ) );
	add_action( "load-$plugin_page", array( $tptn_stats_screen, 'screen_option' ) );
	add_action( 'admin_head-' . $plugin_page, 'tptn_adminhead' );

}
add_action( 'admin_menu', 'tptn_add_admin_pages_links' );


/**
 * Function to add CSS and JS to the Admin header.
 *
 * @since 2.5.0
 * @return void
 */
function tptn_adminhead() {

	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-autocomplete' );
	wp_enqueue_script( 'jquery-ui-tabs' );
	wp_enqueue_script( 'plugin-install' );
	add_thickbox();
?>
	<script type="text/javascript">
	//<![CDATA[
		// Function to clear the cache.
		function clearCache() {
			/**** since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php ****/
			jQuery.post(ajaxurl, {
				action: 'tptn_clear_cache'
			}, function (response, textStatus, jqXHR) {
				alert(response.message);
			}, 'json');
		}

		// Function to add auto suggest.
		jQuery(document).ready(function($) {
			$.fn.tptnTagsSuggest = function( options ) {

				var cache;
				var last;
				var $element = $( this );

				var taxonomy = $element.attr( 'data-wp-taxonomy' ) || 'category';

				function split( val ) {
					return val.split( /,\s*/ );
				}

				function extractLast( term ) {
					return split( term ).pop();
				}

				$element.on( "keydown", function( event ) {
						// Don't navigate away from the field on tab when selecting an item.
						if ( event.keyCode === $.ui.keyCode.TAB &&
						$( this ).autocomplete( 'instance' ).menu.active ) {
							event.preventDefault();
						}
					})
					.autocomplete({
						minLength: 2,
						source: function( request, response ) {
							var term;

							if ( last === request.term ) {
								response( cache );
								return;
							}

							term = extractLast( request.term );

							if ( last === request.term ) {
								response( cache );
								return;
							}

							$.ajax({
								type: 'POST',
								dataType: 'json',
								url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
								data: {
									action: 'tptn_tag_search',
									tax: taxonomy,
									q: term
								},
								success: function( data ) {
									cache = data;

									response( data );
								}
							});

							last = request.term;

						},
						search: function() {
							// Custom minLength.
							var term = extractLast( this.value );

							if ( term.length < 2 ) {
								return false;
							}
						},
						focus: function( event, ui ) {
							// Prevent value inserted on focus.
							event.preventDefault();
						},
						select: function( event, ui ) {
							var terms = split( this.value );

							// Remove the last user input.
							terms.pop();

							// Add the selected item.
							terms.push( ui.item.value );

							// Add placeholder to get the comma-and-space at the end.
							terms.push( "" );
							this.value = terms.join( ", " );
							return false;
						}
					});

			};

			$( '.category_autocomplete' ).each( function ( i, element ) {
				$( element ).tptnTagsSuggest();
			});

			// Prompt the user when they leave the page without saving the form.
			formmodified=0;

			$('form *').change(function(){
				formmodified=1;
			});

			window.onbeforeunload = confirmExit;

			function confirmExit() {
				if (formmodified == 1) {
					return "<?php esc_html__( 'New information not saved. Do you wish to leave the page?', 'where-did-they-go-from-here' ); ?>";
				}
			}

			$( "input[name='submit']" ).click( function() {
				formmodified = 0;
			});

			$( function() {
				$( "#post-body-content" ).tabs({
					create: function( event, ui ) {
						$( ui.tab.find("a") ).addClass( "nav-tab-active" );
					},
					activate: function( event, ui ) {
						$( ui.oldTab.find("a") ).removeClass( "nav-tab-active" );
						$( ui.newTab.find("a") ).addClass( "nav-tab-active" );
					}
				});
			});

		});

	//]]>
	</script>
<?php
}


/**
 * Customise the taxonomy columns.
 *
 * @since 2.5.0
 * @param  array $columns Columns in the admin view.
 * @return array Updated columns.
 */
function tptn_tax_columns( $columns ) {

	// Remove the description column.
	unset( $columns['description'] );

	$new_columns = array(
		'tax_id' => 'ID',
	);

	return array_merge( $columns, $new_columns );
}
add_filter( 'manage_edit-tptn_category_columns', 'tptn_tax_columns' );
add_filter( 'manage_edit-tptn_category_sortable_columns', 'tptn_tax_columns' );
add_filter( 'manage_edit-tptn_tag_columns', 'tptn_tax_columns' );
add_filter( 'manage_edit-tptn_tag_sortable_columns', 'tptn_tax_columns' );


/**
 * Add taxonomy ID to the admin column.
 *
 * @since 2.5.0
 *
 * @param  string     $value Deprecated.
 * @param  string     $name  Name of the column.
 * @param  int|string $id    Category ID.
 * @return int|string
 */
function tptn_tax_id( $value, $name, $id ) {
	return 'tax_id' === $name ? $id : $value;
}
add_filter( 'manage_tptn_category_custom_column', 'tptn_tax_id', 10, 3 );
add_filter( 'manage_tptn_tag_custom_column', 'tptn_tax_id', 10, 3 );


/**
 * Add rating links to the admin dashboard
 *
 * @since 2.5.0
 *
 * @param string $footer_text The existing footer text.
 * @return string Updated Footer text
 */
function tptn_admin_footer( $footer_text ) {

	if ( get_current_screen()->parent_base === 'tptn_options_page' ) {

		$text = sprintf(
			/* translators: 1: Top 10 website, 2: Plugin reviews link. */
			__( 'Thank you for using <a href="%1$s" target="_blank">Top 10</a>! Please <a href="%2$s" target="_blank">rate us</a> on <a href="%2$s" target="_blank">WordPress.org</a>', 'top-10' ),
			'https://webberzone.com/top-10',
			'https://wordpress.org/support/plugin/top-10/reviews/#new-post'
		);

		return str_replace( '</span>', '', $footer_text ) . ' | ' . $text . '</span>';

	} else {

		return $footer_text;

	}
}
add_filter( 'admin_footer_text', 'tptn_admin_footer' );


/**
 * Add CSS to Admin head
 *
 * @since 2.5.0
 *
 * return void
 */
function tptn_admin_head() {
?>
	<style type="text/css" media="screen">
		#dashboard_right_now .tptn-article-count:before {
			content: "\f331";
		}
	</style>
<?php
}
add_filter( 'admin_head', 'tptn_admin_head' );

/**
 * Adding WordPress plugin action links.
 *
 * @version 1.9.2
 *
 * @param   array $links Action links.
 * @return  array   Links array with our settings link added.
 */
function tptn_plugin_actions_links( $links ) {

	return array_merge(
		array(
			'settings' => '<a href="' . admin_url( 'options-general.php?page=tptn_options_page' ) . '">' . __( 'Settings', 'top-10' ) . '</a>',
		),
		$links
	);

}
add_filter( 'plugin_action_links_' . plugin_basename( TOP_TEN_PLUGIN_FILE ), 'tptn_plugin_actions_links' );


/**
 * Add links to the plugin action row.
 *
 * @since   1.5
 *
 * @param   array $links Action links.
 * @param   array $file Plugin file name.
 * @return  array   Links array with our links added
 */
function tptn_plugin_actions( $links, $file ) {
	$plugin = plugin_basename( TOP_TEN_PLUGIN_FILE );

	if ( $file === $plugin ) {
		$links[] = '<a href="https://wordpress.org/support/plugin/top-10/">' . __( 'Support', 'top-10' ) . '</a>';
		$links[] = '<a href="https://ajaydsouza.com/donate/">' . __( 'Donate', 'top-10' ) . '</a>';
		$links[] = '<a href="https://github.com/WebberZone/top-10">' . __( 'Contribute', 'top-10' ) . '</a>';
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'tptn_plugin_actions', 10, 2 );


