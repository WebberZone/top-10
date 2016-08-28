<?php
/**
 * Deprecated functions from Top 10 - Admin. You shouldn't
 * use these functions and look for the alternatives instead. The functions will be
 * removed in a later version.
 *
 * @package Top_Ten
 */

/**
 * Function to generate the top 10 daily popular posts page.
 *
 * @since	1.9.2
 * @deprecated	2.2.0
 */
function tptn_manage_daily() {
	tptn_manage( 1 );
}


/**
 * Function to generate the top 10 popular posts page.
 *
 * @since	1.3
 * @deprecated	2.2.0
 *
 * @param	int	$daily	Overall popular.
 */
function tptn_manage( $daily = 0 ) {

	$paged = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 0;
	$limit = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : 0;
	$daily = isset( $_GET['daily'] ) ? intval( $_GET['daily'] ) : $daily;

?>

<div class="wrap">
	<h2>
	<?php if ( ! $daily ) {
		esc_html_e( 'Popular Posts', 'top-10' );
} else {
	esc_html_e( 'Daily Popular Posts', 'top-10' );
} ?>
	</h2>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<?php echo tptn_pop_display( $daily, $paged, $limit, false ); ?>
			</div>
			<!-- /post-body-content -->
			<div id="postbox-container-1" class="postbox-container">
				<div id="side-sortables" class="meta-box-sortables ui-sortable">
					<?php tptn_admin_side(); ?>
				</div>
				<!-- /side-sortables -->
			</div>
			<!-- /postbox-container-1 -->
		</div>
		<!-- /post-body -->
		<br class="clear" />
	</div>
	<!-- /poststuff -->
</div>
<!-- /wrap -->

<?php
}


