<?php
/**
 * Widget class.
 *
 * @package   Top_Ten
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2008-2015 Ajay D'Souza
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Top 10 Widget.
 *
 * @extends WP_Widget
 */
class Top_Ten_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'widget_tptn_pop', // Base ID
			__( 'Popular Posts [Top 10]', 'top-10' ), // Name
			array( 'description' => __( 'Display popular posts', 'top-10' ) ) // Args
		);
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	function form( $instance ) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$limit = isset( $instance['limit'] ) ? esc_attr( $instance['limit'] ) : '';
		$disp_list_count = isset( $instance['disp_list_count'] ) ? esc_attr( $instance['disp_list_count'] ) : '';
		$show_excerpt = isset( $instance['show_excerpt'] ) ? esc_attr( $instance['show_excerpt'] ) : '';
		$show_author = isset( $instance['show_author'] ) ? esc_attr( $instance['show_author'] ) : '';
		$show_date = isset( $instance['show_date'] ) ? esc_attr( $instance['show_date'] ) : '';
		$post_thumb_op = isset( $instance['post_thumb_op'] ) ? esc_attr( $instance['post_thumb_op'] ) : 'text_only';
		$thumb_height = isset( $instance['thumb_height'] ) ? esc_attr( $instance['thumb_height'] ) : '';
		$thumb_width = isset( $instance['thumb_width'] ) ? esc_attr( $instance['thumb_width'] ) : '';
		$daily = isset( $instance['daily'] ) ? esc_attr( $instance['daily'] ) : 'overall';
		$daily_range = isset( $instance['daily_range'] ) ? esc_attr( $instance['daily_range'] ) : '';
		$hour_range = isset( $instance['hour_range'] ) ? esc_attr( $instance['hour_range'] ) : '';

		// Parse the Post types
		$post_types = array();
		if ( isset( $instance['post_types'] ) ) {
			$post_types = $instance['post_types'];
			parse_str( $post_types, $post_types );	// Save post types in $post_types variable
		}
		$wp_post_types	= get_post_types( array(
			'public'	=> true,
		) );
		$posts_types_inc = array_intersect( $wp_post_types, $post_types );

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">
			<?php _e( 'Title', 'top-10' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>">
			<?php _e( 'No. of posts', 'top-10' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php echo esc_attr( $limit ); ?>" />
			</label>
		</p>
		<p>
			<select class="widefat" id="<?php echo $this->get_field_id( 'daily' ); ?>" name="<?php echo $this->get_field_name( 'daily' ); ?>">
			  <option value="overall" <?php if ( 'overall' == $daily ) { echo 'selected="selected"'; } ?>><?php _e( 'Overall', 'top-10' ); ?></option>
			  <option value="daily" <?php if ( 'daily' == $daily ) { echo 'selected="selected"'; } ?>><?php _e( 'Custom time period (Enter below)', 'top-10' ); ?></option>
			</select>
		</p>
		<p>
			<?php _e( 'In days and hours (applies only to custom option above)', 'top-10' ); ?>:
			<label for="<?php echo $this->get_field_id( 'daily_range' ); ?>">
				<input class="widefat" id="<?php echo $this->get_field_id( 'daily_range' ); ?>" name="<?php echo $this->get_field_name( 'daily_range' ); ?>" type="text" value="<?php echo esc_attr( $daily_range ); ?>" /> <?php _e( 'days', 'top-10' ); ?>
			</label>
			<label for="<?php echo $this->get_field_id( 'hour_range' ); ?>">
				<input class="widefat" id="<?php echo $this->get_field_id( 'hour_range' ); ?>" name="<?php echo $this->get_field_name( 'hour_range' ); ?>" type="text" value="<?php echo esc_attr( $hour_range ); ?>" /> <?php _e( 'hours', 'top-10' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'disp_list_count' ); ?>">
			<input id="<?php echo $this->get_field_id( 'disp_list_count' ); ?>" name="<?php echo $this->get_field_name( 'disp_list_count' ); ?>" type="checkbox" <?php if ( $disp_list_count ) { echo 'checked="checked"'; } ?> /> <?php _e( 'Show count?', 'top-10' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_excerpt' ); ?>">
			<input id="<?php echo $this->get_field_id( 'show_excerpt' ); ?>" name="<?php echo $this->get_field_name( 'show_excerpt' ); ?>" type="checkbox" <?php if ( $show_excerpt ) { echo 'checked="checked"'; } ?> /> <?php _e( 'Show excerpt?', 'top-10' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_author' ); ?>">
			<input id="<?php echo $this->get_field_id( 'show_author' ); ?>" name="<?php echo $this->get_field_name( 'show_author' ); ?>" type="checkbox" <?php if ( $show_author ) { echo 'checked="checked"'; } ?> /> <?php _e( 'Show author?', 'top-10' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_date' ); ?>">
				<input id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" type="checkbox" <?php if ( $show_date ) { echo 'checked="checked"'; } ?> /> <?php _e( 'Show date?', 'top-10' ); ?>
			</label>
		</p>
		<p>
			<?php _e( 'Thumbnail options', 'top-10' ); ?>: <br />
			<select class="widefat" id="<?php echo $this->get_field_id( 'post_thumb_op' ); ?>" name="<?php echo $this->get_field_name( 'post_thumb_op' ); ?>">
			  <option value="inline" <?php if ( 'inline' == $post_thumb_op ) { echo 'selected="selected"'; } ?>><?php _e( 'Thumbnails inline, before title','top-10' ); ?></option>
			  <option value="after" <?php if ( 'after' == $post_thumb_op ) { echo 'selected="selected"'; } ?>><?php _e( 'Thumbnails inline, after title','top-10' ); ?></option>
			  <option value="thumbs_only" <?php if ( 'thumbs_only' == $post_thumb_op ) { echo 'selected="selected"'; } ?>><?php _e( 'Only thumbnails, no text','top-10' ); ?></option>
			  <option value="text_only" <?php if ( 'text_only' == $post_thumb_op ) { echo 'selected="selected"'; } ?>><?php _e( 'No thumbnails, only text.','top-10' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'thumb_height' ); ?>">
				<?php _e( 'Thumbnail height', 'top-10' ); ?>:
				<input class="widefat" id="<?php echo $this->get_field_id( 'thumb_height' ); ?>" name="<?php echo $this->get_field_name( 'thumb_height' ); ?>" type="text" value="<?php echo esc_attr( $thumb_height ); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'thumb_width' ); ?>">
				<?php _e( 'Thumbnail width', 'top-10' ); ?>:
				<input class="widefat" id="<?php echo $this->get_field_id( 'thumb_width' ); ?>" name="<?php echo $this->get_field_name( 'thumb_width' ); ?>" type="text" value="<?php echo esc_attr( $thumb_width ); ?>" />
			</label>
		</p>
		<p><?php _e( 'Post types to include:', 'top-10' ); ?><br />

			<?php foreach ( $wp_post_types as $wp_post_type ) { ?>

				<label>
					<input id="<?php echo $this->get_field_id( 'post_types' ); ?>" name="<?php echo $this->get_field_name( 'post_types' ); ?>[]" type="checkbox" value="<?php echo $wp_post_type; ?>" <?php if ( in_array( $wp_post_type, $posts_types_inc ) ) { echo 'checked="checked"'; } ?> />
					<?php echo $wp_post_type; ?>
				</label>
				<br />

			<?php }	?>
		</p>

		<?php
			/**
			 * Fires after Top 10 widget options.
			 *
			 * @since 2.0.0
			 *
			 * @param	array	$instance	Widget options array
			 */
			do_action( 'tptn_widget_options_after', $instance );
		?>

		<?php
	} //ending form creation

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['limit'] = $new_instance['limit'];
		$instance['daily'] = $new_instance['daily'];
		$instance['daily_range'] = strip_tags( $new_instance['daily_range'] );
		$instance['hour_range'] = strip_tags( $new_instance['hour_range'] );
		$instance['disp_list_count'] = isset( $new_instance['disp_list_count'] ) ? true : false;
		$instance['show_excerpt'] = isset( $new_instance['show_excerpt'] ) ? true : false;
		$instance['show_author'] = isset( $new_instance['show_author'] ) ? true : false;
		$instance['show_date'] = isset( $new_instance['show_date'] ) ? true : false;
		$instance['post_thumb_op'] = $new_instance['post_thumb_op'];
		$instance['thumb_height'] = $new_instance['thumb_height'];
		$instance['thumb_width'] = $new_instance['thumb_width'];

		// Process post types to be selected
		$wp_post_types	= get_post_types( array(
			'public'	=> true,
		) );
		$post_types = ( isset( $new_instance['post_types'] ) ) ? $new_instance['post_types'] : array();
		$post_types = array_intersect( $wp_post_types, $post_types );
		$instance['post_types'] = http_build_query( $post_types, '', '&' );

		/**
		 * Filters Update widget options array.
		 *
		 * @since 2.0.0
		 *
		 * @param	array	$instance	Widget options array
		 */
		return apply_filters( 'tptn_widget_options_update' , $instance );
	} //ending update

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	function widget( $args, $instance ) {
		global $wpdb, $tptn_url, $tptn_settings, $post;

		// Get the post meta
		$tptn_post_meta = get_post_meta( $post->ID, 'tptn_post_meta', true );

		if ( isset( $tptn_post_meta['disable_here'] ) && ( 1 == $tptn_post_meta['disable_here'] ) ) { return; }

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? strip_tags( $tptn_settings['title'] ) : $instance['title'] );

		$limit = isset( $instance['limit'] ) ? $instance['limit'] : $tptn_settings['limit'];
		if ( empty( $limit ) ) {
			$limit = $tptn_settings['limit'];
		}

		$daily_range = ( empty( $instance['daily_range'] ) ) ? $tptn_settings['daily_range'] : $instance['daily_range'];
		$hour_range = ( empty( $instance['hour_range'] ) ) ? $tptn_settings['hour_range'] : $instance['hour_range'];

		$daily = ( isset( $instance['daily'] ) && ( 'daily' == $instance['daily'] ) ) ? true : false;

		$output = $args['before_widget'];
		$output .= $args['before_title'] . $title . $args['after_title'];

		$post_thumb_op = isset( $instance['post_thumb_op'] ) ? esc_attr( $instance['post_thumb_op'] ) : 'text_only';

		$thumb_height = ( isset( $instance['thumb_height'] ) && '' != $instance['thumb_height'] ) ? intval( $instance['thumb_height'] ) : $tptn_settings['thumb_height'];
		$thumb_width = ( isset( $instance['thumb_width'] ) && '' != $instance['thumb_width'] ) ? intval( $instance['thumb_width'] ) : $tptn_settings['thumb_width'];

		$disp_list_count = isset( $instance['disp_list_count'] ) ? esc_attr( $instance['disp_list_count'] ) : '';
		$show_excerpt = isset( $instance['show_excerpt'] ) ? esc_attr( $instance['show_excerpt'] ) : '';
		$show_author = isset( $instance['show_author'] ) ? esc_attr( $instance['show_author'] ) : '';
		$show_date = isset( $instance['show_date'] ) ? esc_attr( $instance['show_date'] ) : '';
		$post_types = isset( $instance['post_types'] ) ? $instance['post_types'] : $tptn_settings['post_types'];

		$arguments = array(
			'is_widget' => 1,
			'heading' => 0,
			'limit' => $limit,
			'daily' => $daily,
			'daily_range' => $daily_range,
			'hour_range' => $hour_range,
			'show_excerpt' => $show_excerpt,
			'show_author' => $show_author,
			'show_date' => $show_date,
			'post_thumb_op' => $post_thumb_op,
			'thumb_height' => $thumb_height,
			'thumb_width' => $thumb_width,
			'disp_list_count' => $disp_list_count,
			'post_types' => $post_types,
		);

		/**
		 * Filters arguments passed to tptn_pop_posts for the widget.
		 *
		 * @since 2.0.0
		 *
		 * @param	array	$arguments	Widget options array
		 */
		$arguments = apply_filters( 'tptn_widget_options' , $arguments );

		$output .= tptn_pop_posts( $arguments );

		$output .= $args['after_widget'];

		echo $output;

	} //ending function widget
}


/**
 * Initialise the widget.
 */
function tptn_register_widget() {
	register_widget( 'Top_Ten_Widget' );
}
add_action( 'widgets_init', 'tptn_register_widget', 1 );

