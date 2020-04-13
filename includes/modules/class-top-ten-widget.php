<?php
/**
 * Widget class.
 *
 * @package   Top_Ten
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2008-2020 Ajay D'Souza
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
	public function __construct() {
		parent::__construct(
			'widget_tptn_pop', // Base ID.
			__( 'Popular Posts [Top 10]', 'top-10' ), // Name.
			array(
				'description'                 => __( 'Display popular posts', 'where-did-they-go-from-here' ),
				'customize_selective_refresh' => true,
				'classname'                   => 'tptn_posts_list_widget',
			)
		);

		add_action( 'wp_enqueue_scripts', array( $this, 'front_end_styles' ) );

	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title              = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$limit              = isset( $instance['limit'] ) ? esc_attr( $instance['limit'] ) : '';
		$offset             = isset( $instance['offset'] ) ? esc_attr( $instance['offset'] ) : '';
		$disp_list_count    = isset( $instance['disp_list_count'] ) ? esc_attr( $instance['disp_list_count'] ) : '';
		$show_excerpt       = isset( $instance['show_excerpt'] ) ? esc_attr( $instance['show_excerpt'] ) : '';
		$show_author        = isset( $instance['show_author'] ) ? esc_attr( $instance['show_author'] ) : '';
		$show_date          = isset( $instance['show_date'] ) ? esc_attr( $instance['show_date'] ) : '';
		$post_thumb_op      = isset( $instance['post_thumb_op'] ) ? esc_attr( $instance['post_thumb_op'] ) : 'text_only';
		$thumb_height       = isset( $instance['thumb_height'] ) ? esc_attr( $instance['thumb_height'] ) : '';
		$thumb_width        = isset( $instance['thumb_width'] ) ? esc_attr( $instance['thumb_width'] ) : '';
		$daily              = isset( $instance['daily'] ) ? esc_attr( $instance['daily'] ) : 'overall';
		$daily_range        = isset( $instance['daily_range'] ) ? esc_attr( $instance['daily_range'] ) : '';
		$hour_range         = isset( $instance['hour_range'] ) ? esc_attr( $instance['hour_range'] ) : '';
		$include_categories = isset( $instance['include_categories'] ) ? esc_attr( $instance['include_categories'] ) : '';
		$include_cat_ids    = isset( $instance['include_cat_ids'] ) ? esc_attr( $instance['include_cat_ids'] ) : '';

		// Parse the Post types.
		$post_types = array();
		if ( isset( $instance['post_types'] ) ) {
			$post_types = $instance['post_types'];
			parse_str( $post_types, $post_types );  // Save post types in $post_types variable.
		}
		$wp_post_types   = get_post_types(
			array(
				'public' => true,
			)
		);
		$posts_types_inc = array_intersect( $wp_post_types, $post_types );

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
			<?php esc_html_e( 'Title', 'top-10' ); ?>: <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>">
			<?php esc_html_e( 'No. of posts', 'top-10' ); ?>: <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" type="text" value="<?php echo esc_attr( $limit ); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'offset' ) ); ?>">
			<?php esc_html_e( 'Offset', 'top-10' ); ?>: <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'offset' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'offset' ) ); ?>" type="text" value="<?php echo esc_attr( $offset ); ?>" />
			</label>
		</p>
		<p>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'daily' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'daily' ) ); ?>">
				<option value="overall" <?php selected( 'overall', $daily, true ); ?>><?php esc_html_e( 'Overall', 'top-10' ); ?></option>
				<option value="daily" <?php selected( 'daily', $daily, true ); ?>><?php esc_html_e( 'Custom time period (Enter below)', 'top-10' ); ?></option>
			</select>
		</p>
		<p>
			<?php esc_html_e( 'In days and hours (applies only to custom option above)', 'top-10' ); ?>:
			<label for="<?php echo esc_attr( $this->get_field_id( 'daily_range' ) ); ?>">
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'daily_range' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'daily_range' ) ); ?>" type="text" value="<?php echo esc_attr( $daily_range ); ?>" /> <?php esc_html_e( 'days', 'top-10' ); ?>
			</label>
			<label for="<?php echo esc_attr( $this->get_field_id( 'hour_range' ) ); ?>">
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'hour_range' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hour_range' ) ); ?>" type="text" value="<?php echo esc_attr( $hour_range ); ?>" /> <?php esc_html_e( 'hours', 'top-10' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'disp_list_count' ) ); ?>">
				<input id="<?php echo esc_attr( $this->get_field_id( 'disp_list_count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'disp_list_count' ) ); ?>" type="checkbox" <?php checked( true, $disp_list_count, true ); ?> /> <?php esc_html_e( 'Show count?', 'top-10' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_excerpt' ) ); ?>">
				<input id="<?php echo esc_attr( $this->get_field_id( 'show_excerpt' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_excerpt' ) ); ?>" type="checkbox" <?php checked( true, $show_excerpt, true ); ?> /> <?php esc_html_e( 'Show excerpt?', 'top-10' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_author' ) ); ?>">
				<input id="<?php echo esc_attr( $this->get_field_id( 'show_author' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_author' ) ); ?>" type="checkbox" <?php checked( true, $show_author, true ); ?> /> <?php esc_html_e( 'Show author?', 'top-10' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_date' ) ); ?>">
				<input id="<?php echo esc_attr( $this->get_field_id( 'show_date' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_date' ) ); ?>" type="checkbox" <?php checked( true, $show_date, true ); ?> /> <?php esc_html_e( 'Show date?', 'top-10' ); ?>
			</label>
		</p>
		<p>
			<?php esc_html_e( 'Thumbnail options', 'top-10' ); ?>: <br />
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'post_thumb_op' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_thumb_op' ) ); ?>">
				<option value="inline" <?php selected( 'inline', $post_thumb_op, true ); ?>><?php esc_html_e( 'Thumbnails inline, before title', 'top-10' ); ?></option>
				<option value="after" <?php selected( 'after', $post_thumb_op, true ); ?>><?php esc_html_e( 'Thumbnails inline, after title', 'top-10' ); ?></option>
				<option value="thumbs_only" <?php selected( 'thumbs_only', $post_thumb_op, true ); ?>><?php esc_html_e( 'Only thumbnails, no text', 'top-10' ); ?></option>
				<option value="text_only" <?php selected( 'text_only', $post_thumb_op, true ); ?>><?php esc_html_e( 'No thumbnails, only text.', 'top-10' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'thumb_height' ) ); ?>">
				<?php esc_html_e( 'Thumbnail height', 'top-10' ); ?>:
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'thumb_height' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'thumb_height' ) ); ?>" type="text" value="<?php echo esc_attr( $thumb_height ); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'thumb_width' ) ); ?>">
				<?php esc_html_e( 'Thumbnail width', 'top-10' ); ?>:
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'thumb_width' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'thumb_width' ) ); ?>" type="text" value="<?php echo esc_attr( $thumb_width ); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'include_categories' ) ); ?>">
				<?php esc_html_e( 'Only from categories', 'top-10' ); ?>:
				<input class="widefat category_autocomplete" id="<?php echo esc_attr( $this->get_field_id( 'include_categories' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'include_categories' ) ); ?>" type="text" value="<?php echo esc_attr( $include_categories ); ?>" />
			</label>
			<input type="hidden" id="<?php echo esc_attr( $this->get_field_id( 'include_cat_ids' ) ); ?>" name="<?php echo esc_attr( $this->get_field_id( 'include_cat_ids' ) ); ?>" value="<?php echo esc_attr( $include_cat_ids ); ?>" />
		</p>
		<p><?php esc_html_e( 'Post types to include:', 'top-10' ); ?><br />

			<?php foreach ( $wp_post_types as $wp_post_type ) { ?>

				<label>
					<input id="<?php echo esc_attr( $this->get_field_id( 'post_types' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_types' ) ); ?>[]" type="checkbox" value="<?php echo esc_attr( $wp_post_type ); ?>" <?php checked( true, in_array( $wp_post_type, $posts_types_inc, true ), true ); ?> />
					<?php echo esc_attr( $wp_post_type ); ?>
				</label>
				<br />

			<?php } ?>
		</p>

		<?php
			/**
			 * Fires after Top 10 widget options.
			 *
			 * @since 2.0.0
			 *
			 * @param   array   $instance   Widget options array
			 */
			do_action( 'tptn_widget_options_after', $instance );
		?>

		<?php
	}

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
	public function update( $new_instance, $old_instance ) {
		$instance                    = $old_instance;
		$instance['title']           = wp_strip_all_tags( $new_instance['title'] );
		$instance['limit']           = $new_instance['limit'];
		$instance['offset']          = $new_instance['offset'];
		$instance['daily']           = $new_instance['daily'];
		$instance['daily_range']     = wp_strip_all_tags( $new_instance['daily_range'] );
		$instance['hour_range']      = wp_strip_all_tags( $new_instance['hour_range'] );
		$instance['disp_list_count'] = isset( $new_instance['disp_list_count'] ) ? true : false;
		$instance['show_excerpt']    = isset( $new_instance['show_excerpt'] ) ? true : false;
		$instance['show_author']     = isset( $new_instance['show_author'] ) ? true : false;
		$instance['show_date']       = isset( $new_instance['show_date'] ) ? true : false;
		$instance['post_thumb_op']   = $new_instance['post_thumb_op'];
		$instance['thumb_height']    = $new_instance['thumb_height'];
		$instance['thumb_width']     = $new_instance['thumb_width'];

		// Process post types to be selected.
		$wp_post_types          = get_post_types(
			array(
				'public' => true,
			)
		);
		$post_types             = ( isset( $new_instance['post_types'] ) ) ? $new_instance['post_types'] : array();
		$post_types             = array_intersect( $wp_post_types, $post_types );
		$instance['post_types'] = http_build_query( $post_types, '', '&' );

		// Save include_categories.
		$include_categories = array_unique( str_getcsv( $new_instance['include_categories'] ) );

		foreach ( $include_categories as $cat_name ) {
			$cat = get_term_by( 'name', $cat_name, 'category' );

			if ( isset( $cat->term_taxonomy_id ) ) {
				$include_cat_ids[]   = $cat->term_taxonomy_id;
				$include_cat_names[] = $cat->name;
			}
		}
		$instance['include_cat_ids']    = isset( $include_cat_ids ) ? join( ',', $include_cat_ids ) : '';
		$instance['include_categories'] = isset( $include_cat_names ) ? tptn_str_putcsv( $include_cat_names ) : '';

		/**
		 * Filters Update widget options array.
		 *
		 * @since 2.0.0
		 *
		 * @param   array   $instance   Widget options array
		 */
		return apply_filters( 'tptn_widget_options_update', $instance );
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		global $post;

		// Get the post meta.
		if ( isset( $post ) ) {
			$tptn_post_meta = get_post_meta( $post->ID, 'tptn_post_meta', true );

			if ( isset( $tptn_post_meta['disable_here'] ) && ( $tptn_post_meta['disable_here'] ) ) {
				return;
			}
		}

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? wp_strip_all_tags( tptn_get_option( 'title' ) ) : $instance['title'] );

		$limit = isset( $instance['limit'] ) ? $instance['limit'] : tptn_get_option( 'limit' );
		if ( empty( $limit ) ) {
			$limit = tptn_get_option( 'limit' );
		}

		$offset      = isset( $instance['offset'] ) ? $instance['offset'] : 0;
		$daily_range = ( empty( $instance['daily_range'] ) ) ? tptn_get_option( 'daily_range' ) : $instance['daily_range'];
		$hour_range  = ( empty( $instance['hour_range'] ) ) ? tptn_get_option( 'hour_range' ) : $instance['hour_range'];

		$daily = ( isset( $instance['daily'] ) && ( 'daily' === $instance['daily'] ) ) ? true : false;

		$output  = $args['before_widget'];
		$output .= $args['before_title'] . $title . $args['after_title'];

		$post_thumb_op = isset( $instance['post_thumb_op'] ) ? esc_attr( $instance['post_thumb_op'] ) : 'text_only';

		$thumb_height = ( isset( $instance['thumb_height'] ) && '' !== $instance['thumb_height'] ) ? absint( $instance['thumb_height'] ) : tptn_get_option( 'thumb_height' );
		$thumb_width  = ( isset( $instance['thumb_width'] ) && '' !== $instance['thumb_width'] ) ? absint( $instance['thumb_width'] ) : tptn_get_option( 'thumb_width' );

		$disp_list_count = isset( $instance['disp_list_count'] ) ? esc_attr( $instance['disp_list_count'] ) : '';
		$show_excerpt    = isset( $instance['show_excerpt'] ) ? esc_attr( $instance['show_excerpt'] ) : '';
		$show_author     = isset( $instance['show_author'] ) ? esc_attr( $instance['show_author'] ) : '';
		$show_date       = isset( $instance['show_date'] ) ? esc_attr( $instance['show_date'] ) : '';
		$post_types      = isset( $instance['post_types'] ) ? $instance['post_types'] : tptn_get_option( 'post_types' );
		$include_cat_ids = isset( $instance['include_cat_ids'] ) ? esc_attr( $instance['include_cat_ids'] ) : '';

		$arguments = array(
			'is_widget'       => 1,
			'instance_id'     => $this->number,
			'heading'         => 0,
			'limit'           => $limit,
			'offset'          => $offset,
			'daily'           => $daily,
			'daily_range'     => $daily_range,
			'hour_range'      => $hour_range,
			'show_excerpt'    => $show_excerpt,
			'show_author'     => $show_author,
			'show_date'       => $show_date,
			'post_thumb_op'   => $post_thumb_op,
			'thumb_height'    => $thumb_height,
			'thumb_width'     => $thumb_width,
			'disp_list_count' => $disp_list_count,
			'post_types'      => $post_types,
			'include_cat_ids' => $include_cat_ids,
		);

		/**
		 * Filters arguments passed to tptn_pop_posts for the widget.
		 *
		 * @since 2.0.0
		 *
		 * @param   array   $arguments  Widget options array
		 */
		$arguments = apply_filters( 'tptn_widget_options', $arguments );

		$output .= tptn_pop_posts( $arguments );

		$output .= $args['after_widget'];

		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	}


	/**
	 * Add styles to the front end if the widget is active.
	 *
	 * @since   2.3.0
	 */
	public function front_end_styles() {

		if ( ! 'left_thumbs' === tptn_get_option( 'tptn_styles' ) ) {
			return;
		}

		// We need to process all instances because this function gets to run only once.
		$widget_settings = get_option( $this->option_name );

		foreach ( (array) $widget_settings as $instance => $options ) {

			// Identify instance.
			$widget_id = "{$this->id_base}-{$instance}";

			// Check if it's our instance.
			if ( ! is_active_widget( false, $widget_id, $this->id_base, true ) ) {
				continue;   // Not active.
			}

			$thumb_height = ( isset( $options['thumb_height'] ) && '' !== $options['thumb_height'] ) ? absint( $options['thumb_height'] ) : tptn_get_option( 'thumb_height' );
			$thumb_width  = ( isset( $options['thumb_width'] ) && '' !== $options['thumb_width'] ) ? absint( $options['thumb_width'] ) : tptn_get_option( 'thumb_width' );

			// Enqueue the custom css for the thumb width and height for this specific widget.
			$custom_css = "
			.tptn_posts_widget{$instance} img.tptn_thumb {
				width: {$thumb_width}px !important;
				height: {$thumb_height}px !important;
			}
			";

			wp_add_inline_style( 'tptn-style-left-thumbs', $custom_css );

		}

	}
}


/**
 * Initialise the widget.
 */
function tptn_register_widget() {
	register_widget( 'Top_Ten_Widget' );
}
add_action( 'widgets_init', 'tptn_register_widget', 1 );

