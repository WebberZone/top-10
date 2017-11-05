<?php
/**
 * Widget to display the overall count.
 *
 * @package   Top_Ten
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2008-2016 Ajay D'Souza
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Widget to display the overall count.
 *
 * @extends WP_Widget
 */
class Top_Ten_Count_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'widget_tptn_count', // Base ID.
			__( 'Overall count [Top 10]', 'top-10' ), // Name.
			array(
				'description'                 => __( 'Display overall count', 'where-did-they-go-from-here' ),
				'customize_selective_refresh' => true,
				'classname'                   => 'tptn_posts_count_widget',
			)
		);

	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
			<?php esc_html_e( 'Title', 'top-10' ); ?>: <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</label>
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
	public function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );

		/**
		 * Filters Update widget options array.
		 *
		 * @since 2.0.0
		 *
		 * @param   array   $instance   Widget options array
		 */
		return apply_filters( 'tptn_widget_options_update', $instance );
	} //ending update

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		global $wpdb;

		$table_name = $wpdb->base_prefix . 'top_ten';

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'] );

		$resultscount = $wpdb->get_row( 'SELECT SUM(cntaccess) as sum_count FROM ' . $table_name ); // WPCS: unprepared SQL OK.
		$cntaccess    = number_format_i18n( ( ( $resultscount ) ? $resultscount->sum_count : 0 ) );

		$output  = $args['before_widget'];
		$output .= $args['before_title'] . $title . $args['after_title'];

		$output .= $cntaccess;

		$output .= $args['after_widget'];

		echo $output; // WPCS: XSS OK.

	} //ending function widget

}


/**
 * Initialise the widget.
 */
function tptn_register_count_widget() {
	register_widget( 'Top_Ten_Count_Widget' );
}
add_action( 'widgets_init', 'tptn_register_count_widget', 1 );

