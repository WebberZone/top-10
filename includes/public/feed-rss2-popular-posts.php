<?php
/**
 * RSS2 Feed Template for displaying RSS2 Posts feed for the Top 10 Popular posts.
 *
 * @package TOP_TEN
 */

$settings = array(
	'daily'       => $daily,
	'daily_range' => tptn_get_option( 'feed_daily_range' ),
	'limit'       => tptn_get_option( 'feed_limit' ),
);
$topposts = get_tptn_pop_posts( $settings );

$topposts = wp_list_pluck( (array) $topposts, 'postnumber' );

$args = array(
	'post__in'       => $topposts,
	'orderby'        => 'post__in',
	'posts_per_page' => count( $topposts ),
);

query_posts( $args ); // phpcs:ignore WordPress.WP.DiscouragedFunctions.query_posts_query_posts

header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );
$more = 1; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

echo '<?xml version="1.0" encoding="' . esc_attr( get_option( 'blog_charset' ) ) . '"?' . '>';

/**
 * Fires between the xml and rss tags in a feed.
 *
 * @since 4.0.0
 *
 * @param string $context Type of feed. Possible values include 'rss2', 'rss2-comments',
 *                        'rdf', 'atom', and 'atom-comments'.
 */
do_action( 'rss_tag_pre', 'rss2' );
?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	<?php
	/**
	 * Fires at the end of the RSS root to add namespaces.
	 *
	 * @since 2.0.0
	 */
	do_action( 'rss2_ns' );
	?>
>

<channel>
	<title><?php wp_title_rss(); ?></title>
	<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
	<link><?php bloginfo_rss( 'url' ); ?></link>
	<description><?php bloginfo_rss( 'description' ); ?></description>
	<lastBuildDate><?php echo get_feed_build_date( 'r' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></lastBuildDate>
	<language><?php bloginfo_rss( 'language' ); ?></language>
	<sy:updatePeriod>
	<?php
		$duration = 'hourly';

		/**
		 * Filters how often to update the RSS feed.
		 *
		 * @since 2.1.0
		 *
		 * @param string $duration The update period. Accepts 'hourly', 'daily', 'weekly', 'monthly',
		 *                         'yearly'. Default 'hourly'.
		 */
		echo apply_filters( 'rss_update_period', $duration ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	?>
	</sy:updatePeriod>
	<sy:updateFrequency>
	<?php
		$frequency = '1';

		/**
		 * Filters the RSS update frequency.
		 *
		 * @since 2.1.0
		 *
		 * @param string $frequency An integer passed as a string representing the frequency
		 *                          of RSS updates within the update period. Default '1'.
		 */
		echo apply_filters( 'rss_update_frequency', $frequency ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	?>
	</sy:updateFrequency>
	<?php
	/**
	 * Fires at the end of the RSS2 Feed Header.
	 *
	 * @since 2.0.0
	 */
	do_action( 'rss2_head' );

	while ( have_posts() ) :
		the_post();
		?>
	<item>
		<title><?php the_title_rss(); ?></title>
		<link><?php the_permalink_rss(); ?></link>
		<?php if ( get_comments_number() || comments_open() ) : ?>
		<comments><?php comments_link_feed(); ?></comments>
		<?php endif; ?>
		<pubDate><?php echo mysql2date( 'D, d M Y H:i:s +0000', get_post_time( 'Y-m-d H:i:s', true ), false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></pubDate>
		<dc:creator><![CDATA[<?php the_author(); ?>]]></dc:creator>
		<?php the_category_rss( 'rss2' ); ?>

		<guid isPermaLink="false"><?php the_guid(); ?></guid>
		<?php if ( get_option( 'rss_use_excerpt' ) ) : ?>
		<description><![CDATA[<?php the_excerpt_rss(); ?>]]></description>
		<?php else : ?>
		<description><![CDATA[<?php the_excerpt_rss(); ?>]]></description>
			<?php $content = get_the_content_feed( 'rss2' ); ?>
			<?php if ( strlen( $content ) > 0 ) : ?>
		<content:encoded><![CDATA[<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>]]></content:encoded>
	<?php else : ?>
		<content:encoded><![CDATA[<?php the_excerpt_rss(); ?>]]></content:encoded>
	<?php endif; ?>
		<?php endif; ?>
		<?php if ( get_comments_number() || comments_open() ) : ?>
		<wfw:commentRss><?php echo esc_url( get_post_comments_feed_link( null, 'rss2' ) ); ?></wfw:commentRss>
		<slash:comments><?php echo get_comments_number(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></slash:comments>
		<?php endif; ?>
		<?php rss_enclosure(); ?>
		<?php
		/**
		 * Fires at the end of each RSS2 feed item.
		 *
		 * @since 2.0.0
		 */
		do_action( 'rss2_item' );
		?>
	</item>
	<?php endwhile; ?>
</channel>
</rss>
