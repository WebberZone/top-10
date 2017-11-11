<?php
/**
 * Top 10 Meta box functions.
 *
 * Accessible on Edit Posts, Pages and other custom post type screens
 *
 * @package   Top_Ten
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2008-2016 Ajay D'Souza
 */

/**** If this file is called directly, abort. ****/
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Function to add meta box in Write screens.
 *
 * @since 1.9.10
 *
 * @param text   $post_type  Post type.
 * @param object $post Post object.
 */
function tptn_add_meta_box( $post_type, $post ) {

	// If metaboxes are disabled, then exit.
	if ( ! tptn_get_option( 'show_metabox' ) ) {
		return;
	}

	// If current user isn't an admin and we're restricting metaboxes to admins only, then exit.
	if ( ! current_user_can( 'manage_options' ) && tptn_get_option( 'show_metabox_admins' ) ) {
		return;
	}

	$args       = array(
		'public' => true,
	);
	$post_types = get_post_types( $args );

	/**
	 * Filter post types on which the meta box is displayed
	 *
	 * @since 2.2.0
	 *
	 * @param array $post_types Array of post types
	 */
	$post_types = apply_filters( 'tptn_meta_box_post_types', $post_types );

	if ( in_array( $post_type, $post_types, true ) ) {

		add_meta_box(
			'tptn_metabox',
			'Top 10',
			'tptn_call_meta_box',
			$post_type,
			'advanced',
			'default'
		);
	}
}
add_action( 'add_meta_boxes', 'tptn_add_meta_box', 10, 2 );


/**
 * Function to call the meta box.
 *
 * @since 1.9.10
 */
function tptn_call_meta_box() {
	global $wpdb, $post;

	$table_name = $wpdb->base_prefix . 'top_ten';

	// Add an nonce field so we can check for it later.
	wp_nonce_field( 'tptn_meta_box', 'tptn_meta_box_nonce' );

	// Get the number of visits for the post being editted.
	$resultscount = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT postnumber, cntaccess FROM {$table_name} WHERE postnumber = %d AND blog_id = %d ",
			$post->ID,
			get_current_blog_id()
		)
	);  // DB call ok; no-cache ok; WPCS: unprepared SQL OK.
	$total_count  = $resultscount ? $resultscount->cntaccess : 0;

	// Get the post meta.
	$tptn_post_meta = get_post_meta( $post->ID, 'tptn_post_meta', true );

	// Disable display option.
	if ( isset( $tptn_post_meta['disable_here'] ) ) {
		$disable_here = $tptn_post_meta['disable_here'];
	} else {
		$disable_here = 0;
	}

	if ( isset( $tptn_post_meta['exclude_this_post'] ) ) {
		$exclude_this_post = $tptn_post_meta['exclude_this_post'];
	} else {
		$exclude_this_post = 0;
	}

?>
	<p>
		<label for="total_count"><strong><?php esc_html_e( 'Visit count:', 'top-10' ); ?></strong></label>
		<input type="text" id="total_count" name="total_count" value="<?php echo esc_attr( $total_count ); ?>" style="width:100%" />
		<em><?php esc_html_e( 'Enter a number above to update the visit count. Leaving the above box blank will set the count to zero', 'top-10' ); ?></em>
	</p>

<?php

	$results = get_post_meta( $post->ID, tptn_get_option( 'thumb_meta' ), true );
	$value   = ( $results ) ? $results : '';
?>
	<p>
		<label for="disable_here"><strong><?php esc_html_e( 'Disable Popular Posts display:', 'top-10' ); ?></strong></label>
		<input type="checkbox" id="disable_here" name="disable_here" <?php checked( 1, $disable_here, true ); ?> />
		<br />
		<em><?php esc_html_e( 'If this is checked, then Top 10 will not display the popular posts widgets when viewing this post.', 'top-10' ); ?></em>
	</p>

	<p>
		<label for="exclude_this_post"><strong><?php esc_html_e( 'Exclude this post from the popular posts list:', 'top-10' ); ?></strong></label>
		<input type="checkbox" id="exclude_this_post" name="exclude_this_post" <?php checked( 1, $exclude_this_post, true ); ?> />
		<br />
		<em><?php esc_html_e( 'If this is checked, then this post will be excluded from the popular posts list.', 'top-10' ); ?></em>
	</p>

	<p>
		<label for="thumb_meta"><strong><?php esc_html_e( 'Location of thumbnail:', 'top-10' ); ?></strong></label>
		<input type="text" id="thumb_meta" name="thumb_meta" value="<?php echo esc_url( $value ); ?>" style="width:100%" />
		<em><?php esc_html_e( "Enter the full URL to the image (JPG, PNG or GIF) you'd like to use. This image will be used for the post. It will be resized to the thumbnail size set under Top 10 Settings &raquo; Thumbnail options.", 'top-10' ); ?></em>
		<em><?php esc_html_e( 'The URL above is saved in the meta field:', 'top-10' ); ?></em><strong><?php echo esc_attr( tptn_get_option( 'thumb_meta' ) ); ?></strong>
	</p>

	<p>
		<?php if ( function_exists( 'crp_read_options' ) ) { ?>
			<em style="color:red">
				<?php
					/* translators: 1: Plugin name */
					printf( __( 'You have %1$s installed. If you are trying to modify the thumbnail, then you will need to make the same change in the %1$s meta box on this page.', 'top-10' ), 'Contextual Related Posts' ); // WPCS: XSS OK.
				?>
			</em>
		<?php } ?>
	</p>

	<?php
	if ( $results ) {
		echo '<img src="' . esc_url( $value ) . '" style="max-width:100%" />';
	}

}


/**
 * Function to save the meta box.
 *
 * @since 1.9.10
 *
 * @param int $post_id Post ID.
 */
function tptn_save_meta_box( $post_id ) {
	global $wpdb;

	$tptn_post_meta = array();

	$table_name = $wpdb->base_prefix . 'top_ten';

	// Bail if we're doing an auto save.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// If our nonce isn't there, or we can't verify it, bail.
	if ( ! isset( $_POST['tptn_meta_box_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['tptn_meta_box_nonce'] ), 'tptn_meta_box' ) ) { // Input var okay.
		return;
	}

	// If our current user can't edit this post, bail.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Update the posts view count.
	if ( isset( $_POST['total_count'] ) ) { // Input var okay.
		$total_count = intval( $_POST['total_count'] ); // Input var okay.
		$blog_id     = get_current_blog_id();

		if ( 0 === $total_count ) {
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$table_name} WHERE postnumber = %d AND blog_id = %d",
					$post_id,
					$blog_id
				)
			);  // DB call ok; no-cache ok; WPCS: unprepared SQL OK.
		} else {
			$wpdb->query(
				$wpdb->prepare(
					"INSERT INTO {$table_name} (postnumber, cntaccess, blog_id) VALUES( %d, %d, %d ) ON DUPLICATE KEY UPDATE cntaccess= %d ",
					$post_id,
					$total_count,
					$blog_id,
					$total_count
				)
			);  // DB call ok; no-cache ok; WPCS: unprepared SQL OK.
		}
	}

	// Update the thumbnail URL.
	if ( isset( $_POST['thumb_meta'] ) ) { // Input var okay.
		$thumb_meta = empty( $_POST['thumb_meta'] ) ? '' : sanitize_text_field( wp_unslash( $_POST['thumb_meta'] ) ); // Input var okay.
	}

	if ( ! empty( $thumb_meta ) ) {
		update_post_meta( $post_id, tptn_get_option( 'thumb_meta' ), $thumb_meta );
	} else {
		delete_post_meta( $post_id, tptn_get_option( 'thumb_meta' ) );
	}

	// Disable posts.
	if ( isset( $_POST['disable_here'] ) ) { // Input var okay.
		$tptn_post_meta['disable_here'] = 1;
	} else {
		$tptn_post_meta['disable_here'] = 0;
	}

	if ( isset( $_POST['exclude_this_post'] ) ) { // Input var okay.
		$tptn_post_meta['exclude_this_post'] = 1;
	} else {
		$tptn_post_meta['exclude_this_post'] = 0;
	}

	/**
	 * Filter the Top 10 Post meta variable which contains post-specific settings
	 *
	 * @since 2.2.0
	 *
	 * @param array $tptn_post_meta Top 10 post-specific settings
	 * @param int $post_id Post ID
	 */
	$tptn_post_meta = apply_filters( 'tptn_post_meta', $tptn_post_meta, $post_id );

	$tptn_post_meta_filtered = array_filter( $tptn_post_meta );

	/**** Now we can start saving */
	if ( empty( $tptn_post_meta_filtered ) ) { // Checks if all the array items are 0 or empty.
		delete_post_meta( $post_id, 'tptn_post_meta' ); // Delete the post meta if no options are set.
	} else {
		update_post_meta( $post_id, 'tptn_post_meta', $tptn_post_meta );
	}

	/**
	 * Action triggered when saving Contextual Related Posts meta box settings
	 *
	 * @since 2.2
	 *
	 * @param int $post_id Post ID
	 */
	do_action( 'tptn_save_meta_box', $post_id );

}
add_action( 'save_post', 'tptn_save_meta_box' );
add_action( 'edit_attachment', 'tptn_save_meta_box' );

