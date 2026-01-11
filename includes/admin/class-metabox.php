<?php
/**
 * Metabox class.
 *
 * @package WebberZone\Top_Ten\Admin
 */

namespace WebberZone\Top_Ten\Admin;

use WebberZone\Top_Ten\Counter;
use WebberZone\Top_Ten\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Metabox Class.
 *
 * @since 3.3.0
 */
class Metabox {

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		Hook_Registry::add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		Hook_Registry::add_action( 'save_post', array( $this, 'save_meta_box' ) );
		Hook_Registry::add_action( 'edit_attachment', array( $this, 'save_meta_box' ) );
		Hook_Registry::add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 11 );
	}

	/**
	 * Function to add meta box in Write screens.
	 *
	 * @since 1.9.10
	 *
	 * @param string $post_type  Post type.
	 */
	public static function add_meta_box( $post_type ) {

		// If metaboxes are disabled, then exit.
		if ( ! \tptn_get_option( 'show_metabox' ) ) {
			return;
		}

		// If current user isn't an admin and we're restricting metaboxes to admins only, then exit.
		if ( ! current_user_can( 'manage_options' ) && \tptn_get_option( 'show_metabox_admins' ) ) {
			return;
		}

		/**
		 * Filters whether to show the Top 10 meta box.
		 *
		 * @since 3.1.0
		 *
		 * @param bool $show_meta_box Whether the Top 10 meta box should be shown. Default true.
		 */
		$show_meta_box = apply_filters( 'tptn_show_meta_box', true );

		if ( ! $show_meta_box ) {
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
				array( __CLASS__, 'call_meta_box' ),
				$post_type,
				'advanced',
				'default'
			);
		}
	}

	/**
	 * Function to call the meta box.
	 *
	 * @since 1.9.10
	 */
	public static function call_meta_box() {
		global $post;

		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'tptn_meta_box', 'tptn_meta_box_nonce' );

		// Get the number of visits for the post being editted.
		$total_count = get_tptn_total_count( $post->ID, get_current_blog_id() );

		// Get the post meta.
		$post_meta = get_post_meta( $post->ID, 'tptn_post_meta', true );

		$disable_here      = isset( $post_meta['disable_here'] ) ? $post_meta['disable_here'] : 0;
		$exclude_this_post = isset( $post_meta['exclude_this_post'] ) ? $post_meta['exclude_this_post'] : 0;

		$thumb_meta = get_post_meta( $post->ID, \tptn_get_option( 'thumb_meta' ), true );
		$thumb_meta = ( $thumb_meta ) ? $thumb_meta : '';

		?>
	<p>
		<label for="total_count"><strong><?php esc_html_e( 'Visit count:', 'top-10' ); ?></strong></label>
		<input type="text" id="total_count" name="total_count" value="<?php echo esc_attr( (string) $total_count ); ?>" style="width:100%" />
		<em><?php esc_html_e( 'Enter a number above to update the visit count. Leaving the above box blank will set the count to zero', 'top-10' ); ?></em>
		<input type="hidden" id="total_count_original" name="total_count_original" value="<?php echo esc_attr( (string) $total_count ); ?>">
	</p>

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
		<input type="text" id="thumb_meta" name="thumb_meta" value="<?php echo esc_url( $thumb_meta ); ?>" style="width:100%" />
		<em><?php esc_html_e( "Enter the full URL to the image (JPG, PNG or GIF) you'd like to use. This image will be used for the post. It will be resized to the thumbnail size set under Top 10 Settings &raquo; Thumbnail options.", 'top-10' ); ?></em>
		<em><?php esc_html_e( 'The URL above is saved in the meta field:', 'top-10' ); ?></em><strong><?php echo esc_attr( \tptn_get_option( 'thumb_meta' ) ); ?></strong>
	</p>

	<p>
		<?php if ( function_exists( 'crp_get_settings' ) ) { ?>
			<em style="color:red">
				<?php
					/* translators: 1: Plugin name */
					printf( __( 'You have %1$s installed. If you are trying to modify the thumbnail, then you will need to make the same change in the %1$s meta box on this page.', 'top-10' ), 'Contextual Related Posts' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</em>
		<?php } ?>
	</p>

		<?php
		if ( $thumb_meta ) {
			echo '<img src="' . esc_url( $thumb_meta ) . '" style="max-width:100%" />';
		}
		?>

		<?php
		/**
		 * Action triggered when displaying Top 10 meta box
		 *
		 * @since 4.0.0
		 *
		 * @param object  $post   Post object
		 */
		do_action( 'tptn_call_meta_box', $post );
	}


	/**
	 * Function to save the meta box.
	 *
	 * @since 1.9.10
	 *
	 * @param int $post_id Post ID.
	 */
	public static function save_meta_box( $post_id ) {
		global $wpdb;

		$post_meta = array();

		// Bail if we're doing an auto save.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// If our nonce isn't there, or we can't verify it, bail.
		if ( ! isset( $_POST['tptn_meta_box_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['tptn_meta_box_nonce'] ), 'tptn_meta_box' ) ) {
			return;
		}

		// If our current user can't edit this post, bail.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Update the posts view count.
		if ( isset( $_POST['total_count'] ) && isset( $_POST['total_count_original'] ) ) {
			$total_count          = intval( $_POST['total_count'] );
			$total_count_original = intval( $_POST['total_count_original'] );
			$blog_id              = get_current_blog_id();

			if ( 0 === $total_count ) {
				Counter::delete_count( $post_id, $blog_id );
			} elseif ( $total_count_original !== $total_count ) {
				Counter::edit_count( $post_id, $blog_id, $total_count );
			}
		}

		// Update the thumbnail URL.
		if ( isset( $_POST['thumb_meta'] ) ) {
			$thumb_meta = empty( $_POST['thumb_meta'] ) ? '' : sanitize_text_field( wp_unslash( $_POST['thumb_meta'] ) );
		}

		if ( ! empty( $thumb_meta ) ) {
			update_post_meta( $post_id, \tptn_get_option( 'thumb_meta' ), $thumb_meta );
		} else {
			delete_post_meta( $post_id, \tptn_get_option( 'thumb_meta' ) );
		}

		$post_meta['disable_here']      = isset( $_POST['disable_here'] ) ? 1 : 0;
		$post_meta['exclude_this_post'] = isset( $_POST['exclude_this_post'] ) ? 1 : 0;

		/**
		 * Filter the Top 10 Post meta variable which contains post-specific settings
		 *
		 * @since 2.2.0
		 *
		 * @param array $post_meta Top 10 post-specific settings
		 * @param int $post_id Post ID
		 */
		$post_meta = apply_filters( 'tptn_post_meta', $post_meta, $post_id );

		$post_meta_filtered = array_filter( $post_meta );

		/**** Now we can start saving */
		if ( empty( $post_meta_filtered ) ) { // Checks if all the array items are 0 or empty.
			delete_post_meta( $post_id, 'tptn_post_meta' ); // Delete the post meta if no options are set.
		} else {
			update_post_meta( $post_id, 'tptn_post_meta', $post_meta );
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

	/**
	 * Enqueue scripts and styles for the meta box.
	 *
	 * @since 4.0.0
	 */
	public function admin_enqueue_scripts() {

		// If metaboxes are disabled, then exit.
		if ( ! \tptn_get_option( 'show_metabox' ) ) {
			return;
		}

		// If current user isn't an admin and we're restricting metaboxes to admins only, then exit.
		if ( ! current_user_can( 'manage_options' ) && \tptn_get_option( 'show_metabox_admins' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( 'post' === $screen->base || 'page' === $screen->base ) {
			wp_enqueue_script( 'wz-taxonomy-suggest-js' );
		}
	}
}
