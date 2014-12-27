<?php
/**
 * Top 10 Meta box functions.
 *
 * Accessible on Edit Posts, Pages and other custom post type screens
 *
 * @package   Top_Ten
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      http://ajaydsouza.com
 * @copyright 2008-2014 Ajay D'Souza
 */

/**** If this file is called directly, abort. ****/
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Function to add meta box in Write screens.
 *
 * @since	1.9.10
 *
 * @param	text	$post_type	Post type
 * @param	object	$post		Post object
 */
function tptn_add_meta_box( $post_type, $post ) {

	$args = array(
	   'public'   => true,
	);
	$post_types = get_post_types( $args );

	if ( in_array( $post_type, $post_types ) ) {

		add_meta_box(
			'tptn_metabox',
			__( 'Top 10', TPTN_LOCAL_NAME ),
			'tptn_call_meta_box',
			$post_type,
			'advanced',
			'default'
		);
	}
}
add_action( 'add_meta_boxes', 'tptn_add_meta_box' , 10, 2 );


/**
 * Function to call the meta box.
 *
 * @since	1.9.10
 */
function tptn_call_meta_box() {
	global $wpdb, $post, $tptn_settings;

	$table_name = $wpdb->base_prefix . "top_ten";

	// Add an nonce field so we can check for it later.
	wp_nonce_field( 'tptn_meta_box', 'tptn_meta_box_nonce' );

	$resultscount = $wpdb->get_row( $wpdb->prepare(
		"SELECT postnumber, cntaccess FROM {$table_name} WHERE postnumber = %d" ,
		$post->ID
	) );
	$total_count = $resultscount ? $resultscount->cntaccess : 0;

	if ( current_user_can( 'manage_options' ) ) {
?>
	<p>
		<label for="total_count"><?php _e( "Visit count:", TPTN_LOCAL_NAME ); ?></label>
		<input type="text" id="total_count" name="total_count" value="<?php echo $total_count ?>" style="width:100%" />
		<em><?php _e( "Enter a number above to update the visit count. Leaving the above box blank will set the count to zero", TPTN_LOCAL_NAME ); ?></em>
	</p>

<?php
	}

	$results = get_post_meta( $post->ID, $tptn_settings['thumb_meta'], true );
	$value = ( $results ) ? $results : '';
?>
	<p>
		<label for="thumb_meta"><?php _e( "Location of thumbnail:", TPTN_LOCAL_NAME ); ?></label>
		<input type="text" id="thumb_meta" name="thumb_meta" value="<?php echo esc_url( $value ) ?>" style="width:100%" />
		<em><?php _e( "Enter the full URL to the image (JPG, PNG or GIF) you'd like to use. This image will be used for the post. It will be resized to the thumbnail size set under Settings &raquo; Related Posts &raquo; Output Options", TPTN_LOCAL_NAME ); ?></em>
		<em><?php _e( "The URL above is saved in the meta field: ", TPTN_LOCAL_NAME ); ?></em><strong><?php echo $tptn_settings['thumb_meta']; ?></strong>
	</p>

	<?php
	if ( $results ) {
		echo '<img src="' . esc_url( $value ) . '" style="max-width:100%" />';
	}

}


/**
 * Function to save the meta box.
 *
 * @since	1.9.10
 *
 * @param	int	$post_id
 */
function tptn_save_meta_box( $post_id ) {
	global $tptn_settings, $wpdb;

	$table_name = $wpdb->base_prefix . "top_ten";

    // Bail if we're doing an auto save
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

    // if our nonce isn't there, or we can't verify it, bail
    if ( ! isset( $_POST['tptn_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['tptn_meta_box_nonce'], 'tptn_meta_box' ) ) return;

    // if our current user can't edit this post, bail
    if ( ! current_user_can( 'edit_posts' ) ) return;

	// Update the posts view count
	if ( ( isset( $_POST['total_count'] ) ) && ( current_user_can( 'manage_options' ) ) ) {
    	$total_count = intval( $_POST['total_count'] );
    	if ( 0 <> $total_count ) {
			$tt = $wpdb->query( $wpdb->prepare(
					"INSERT INTO {$table_name} (postnumber, cntaccess) VALUES('%d', '%d') ON DUPLICATE KEY UPDATE cntaccess= %d ",
					$post_id,
					$total_count,
					$total_count
				) );
		} else {
			$resultscount = $wpdb->query( $wpdb->prepare(
				"DELETE FROM {$table_name} WHERE postnumber = %d ",
				$post_id
			) );
		}
	}

    // Update the thumbnail URL
    if ( isset( $_POST['thumb_meta'] ) ) {
    	$thumb_meta = $_POST['thumb_meta'] == '' ? '' : sanitize_text_field( $_POST['thumb_meta'] );
    }

	$tptn_post_meta = get_post_meta( $post_id, $tptn_settings['thumb_meta'], true );
	if ( $tptn_post_meta && '' != $tptn_post_meta ) {
		$gotmeta = true;
	} else {
		$gotmeta = false;
	}

	if ( $gotmeta && '' != $thumb_meta ) {
		update_post_meta( $post_id, $tptn_settings['thumb_meta'], $thumb_meta );
	} elseif ( ! $gotmeta && '' != $thumb_meta ) {
		add_post_meta( $post_id, $tptn_settings['thumb_meta'], $thumb_meta );
	} else {
		delete_post_meta( $post_id, $tptn_settings['thumb_meta'] );
	}

}
add_action( 'save_post', 'tptn_save_meta_box' );

?>