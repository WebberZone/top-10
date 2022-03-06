<?php
/**
 * Widget class.
 *
 * @package   Top_Ten
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
				'classname'                   => 'tptn_posts_list_widget',
				'customize_selective_refresh' => true,
				'show_instance_in_rest'       => true,
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
		$instance['title']              = isset( $instance['title'] ) ? $instance['title'] : '';
		$instance['limit']              = isset( $instance['limit'] ) ? $instance['limit'] : '';
		$instance['offset']             = isset( $instance['offset'] ) ? $instance['offset'] : '';
		$instance['disp_list_count']    = isset( $instance['disp_list_count'] ) ? $instance['disp_list_count'] : '';
		$instance['show_excerpt']       = isset( $instance['show_excerpt'] ) ? $instance['show_excerpt'] : '';
		$instance['show_author']        = isset( $instance['show_author'] ) ? $instance['show_author'] : '';
		$instance['show_date']          = isset( $instance['show_date'] ) ? $instance['show_date'] : '';
		$instance['post_thumb_op']      = isset( $instance['post_thumb_op'] ) ? $instance['post_thumb_op'] : 'text_only';
		$instance['thumb_height']       = isset( $instance['thumb_height'] ) ? $instance['thumb_height'] : '';
		$instance['thumb_width']        = isset( $instance['thumb_width'] ) ? $instance['thumb_width'] : '';
		$instance['daily']              = isset( $instance['daily'] ) ? $instance['daily'] : 'overall';
		$instance['daily_range']        = isset( $instance['daily_range'] ) ? $instance['daily_range'] : '';
		$instance['hour_range']         = isset( $instance['hour_range'] ) ? $instance['hour_range'] : '';
		$instance['include_categories'] = isset( $instance['include_categories'] ) ? $instance['include_categories'] : '';
		$instance['include_cat_ids']    = isset( $instance['include_cat_ids'] ) ? $instance['include_cat_ids'] : '';
		$instance['include_post_ids']   = isset( $instance['include_post_ids'] ) ? $instance['include_post_ids'] : '';

		// Parse the Post types.
		$post_types = array();
		if ( ! empty( $instance['post_types'] ) ) {
			$post_types = $instance['post_types'];
			parse_str( $post_types, $post_types ); // Save post types in $post_types variable.
		}
		$wp_post_types   = get_post_types(
			array(
				'public' => true,
			)
		);
		$posts_types_inc = array_intersect( $wp_post_types, $post_types );

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'top-10' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>"><?php esc_html_e( 'No. of posts', 'top-10' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['limit'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'offset' ) ); ?>"><?php esc_html_e( 'Offset', 'top-10' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'offset' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'offset' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['offset'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'daily' ) ); ?>"><?php esc_html_e( 'Select period', 'top-10' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'daily' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'daily' ) ); ?>">
				<option value="overall" <?php selected( $instance['daily'], 'overall' ); ?>><?php esc_html_e( 'Overall', 'top-10' ); ?></option>
				<option value="daily" <?php selected( $instance['daily'], 'daily' ); ?>><?php esc_html_e( 'Custom time period (Enter below)', 'top-10' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'daily_range' ) ); ?>"><?php esc_html_e( 'Days', 'top-10' ); ?></label>
			<input id="<?php echo esc_attr( $this->get_field_id( 'daily_range' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'daily_range' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['daily_range'] ); ?>" />
			<br />
			<label for="<?php echo esc_attr( $this->get_field_id( 'hour_range' ) ); ?>"><?php esc_html_e( 'Hours', 'top-10' ); ?></label>
			<input id="<?php echo esc_attr( $this->get_field_id( 'hour_range' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hour_range' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['hour_range'] ); ?>" />
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['disp_list_count'], true ); ?> id="<?php echo esc_attr( $this->get_field_id( 'disp_list_count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'disp_list_count' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'disp_list_count' ) ); ?>"><?php esc_html_e( 'Show count', 'top-10' ); ?></label>
			<br/>

			<input class="checkbox" type="checkbox" <?php checked( $instance['show_excerpt'], true ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_excerpt' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_excerpt' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_excerpt' ) ); ?>"><?php esc_html_e( 'Show excerpt', 'top-10' ); ?></label>
			<br/>

			<input class="checkbox" type="checkbox" <?php checked( $instance['show_author'], true ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_author' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_author' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_author' ) ); ?>"><?php esc_html_e( 'Show author', 'top-10' ); ?></label>
			<br/>

			<input class="checkbox" type="checkbox" <?php checked( $instance['show_date'], true ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_date' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_date' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_date' ) ); ?>"><?php esc_html_e( 'Show date', 'top-10' ); ?></label>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'post_thumb_op' ) ); ?>"><?php esc_html_e( 'Thumbnail options', 'top-10' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'post_thumb_op' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_thumb_op' ) ); ?>">
				<option value="inline" <?php selected( $instance['post_thumb_op'], 'inline' ); ?>><?php esc_html_e( 'Thumbnails inline, before title', 'top-10' ); ?></option>
				<option value="after" <?php selected( $instance['post_thumb_op'], 'after' ); ?>><?php esc_html_e( 'Thumbnails inline, after title', 'top-10' ); ?></option>
				<option value="thumbs_only" <?php selected( $instance['post_thumb_op'], 'thumbs_only' ); ?>><?php esc_html_e( 'Only thumbnails, no text', 'top-10' ); ?></option>
				<option value="text_only" <?php selected( $instance['post_thumb_op'], 'text_only' ); ?>><?php esc_html_e( 'No thumbnails, only text.', 'top-10' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'thumb_height' ) ); ?>"><?php esc_html_e( 'Thumbnail height', 'top-10' ); ?></label>
			<input id="<?php echo esc_attr( $this->get_field_id( 'thumb_height' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'thumb_height' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['thumb_height'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'thumb_width' ) ); ?>"><?php esc_html_e( 'Thumbnail width', 'top-10' ); ?></label>
			<input id="<?php echo esc_attr( $this->get_field_id( 'thumb_width' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'thumb_width' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['thumb_width'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'include_categories' ) ); ?>"><?php esc_html_e( 'Only from categories', 'top-10' ); ?>:</label>
			<input class="widefat category_autocomplete" id="<?php echo esc_attr( $this->get_field_id( 'include_categories' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'include_categories' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['include_categories'] ); ?>" />
			<input type="hidden" id="<?php echo esc_attr( $this->get_field_id( 'include_cat_ids' ) ); ?>" name="<?php echo esc_attr( $this->get_field_id( 'include_cat_ids' ) ); ?>" value="<?php echo esc_attr( $instance['include_cat_ids'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'include_post_ids' ) ); ?>"><?php esc_html_e( 'Include IDs', 'top-10' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'include_post_ids' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'include_post_ids' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['include_post_ids'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'post_types' ) ); ?>"><?php esc_html_e( 'Post types to include', 'top-10' ); ?></label>
			<br />
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
		$instance['disp_list_count'] = isset( $new_instance['disp_list_count'] ) ? (bool) $new_instance['disp_list_count'] : false;
		$instance['show_excerpt']    = isset( $new_instance['show_excerpt'] ) ? (bool) $new_instance['show_excerpt'] : false;
		$instance['show_author']     = isset( $new_instance['show_author'] ) ? (bool) $new_instance['show_author'] : false;
		$instance['show_date']       = isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;
		$instance['daily']           = $new_instance['daily'];
		$instance['daily_range']     = $new_instance['daily_range'];
		$instance['hour_range']      = $new_instance['hour_range'];
		$instance['thumb_height']    = $new_instance['thumb_height'];
		$instance['thumb_width']     = $new_instance['thumb_width'];

		$instance['post_thumb_op'] = 'text_only';
		if ( in_array( $new_instance['post_thumb_op'], array( 'inline', 'after', 'thumbs_only', 'text_only' ), true ) ) {
			$instance['post_thumb_op'] = $new_instance['post_thumb_op'];
		}

		$instance['include_post_ids'] = implode( ',', array_filter( array_map( 'absint', explode( ',', $new_instance['include_post_ids'] ) ) ) );

		// Process post types to be selected.
		$wp_post_types          = get_post_types(
			array(
				'public' => true,
			)
		);
		$post_types             = isset( $new_instance['post_types'] ) ? (array) $new_instance['post_types'] : array();
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

		$default_title = wp_strip_all_tags( tptn_get_option( 'title' ) );
		$title         = ! empty( $instance['title'] ) ? $instance['title'] : $default_title;

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$limit = ! empty( $instance['limit'] ) ? $instance['limit'] : tptn_get_option( 'limit' );

		$offset           = isset( $instance['offset'] ) ? $instance['offset'] : 0;
		$daily_range      = ( empty( $instance['daily_range'] ) ) ? tptn_get_option( 'daily_range' ) : $instance['daily_range'];
		$hour_range       = ( empty( $instance['hour_range'] ) ) ? tptn_get_option( 'hour_range' ) : $instance['hour_range'];
		$daily            = ( isset( $instance['daily'] ) && ( 'daily' === $instance['daily'] ) ) ? true : false;
		$post_thumb_op    = isset( $instance['post_thumb_op'] ) ? $instance['post_thumb_op'] : 'text_only';
		$thumb_height     = ! empty( $instance['thumb_height'] ) ? absint( $instance['thumb_height'] ) : tptn_get_option( 'thumb_height' );
		$thumb_width      = ! empty( $instance['thumb_width'] ) ? absint( $instance['thumb_width'] ) : tptn_get_option( 'thumb_width' );
		$disp_list_count  = isset( $instance['disp_list_count'] ) ? $instance['disp_list_count'] : '';
		$show_excerpt     = isset( $instance['show_excerpt'] ) ? $instance['show_excerpt'] : '';
		$show_author      = isset( $instance['show_author'] ) ? $instance['show_author'] : '';
		$show_date        = isset( $instance['show_date'] ) ? $instance['show_date'] : '';
		$post_types       = ! empty( $instance['post_types'] ) ? $instance['post_types'] : tptn_get_option( 'post_types' );
		$include_cat_ids  = isset( $instance['include_cat_ids'] ) ? $instance['include_cat_ids'] : '';
		$include_post_ids = isset( $instance['include_post_ids'] ) ? $instance['include_post_ids'] : '';

		$output  = $args['before_widget'];
		$output .= $args['before_title'] . $title . $args['after_title'];

		$arguments = array(
			'is_widget'        => 1,
			'instance_id'      => $this->number,
			'heading'          => 0,
			'limit'            => $limit,
			'offset'           => $offset,
			'daily'            => $daily,
			'daily_range'      => $daily_range,
			'hour_range'       => $hour_range,
			'show_excerpt'     => $show_excerpt,
			'show_author'      => $show_author,
			'show_date'        => $show_date,
			'post_thumb_op'    => $post_thumb_op,
			'thumb_height'     => $thumb_height,
			'thumb_width'      => $thumb_width,
			'disp_list_count'  => $disp_list_count,
			'post_types'       => $post_types,
			'include_cat_ids'  => $include_cat_ids,
			'include_post_ids' => $include_post_ids,
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

