<?php
/**
 * Helper functions
 *
 * @package Top_Ten
 */

namespace WebberZone\Top_Ten\Util;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Columns Class.
 *
 * @since 3.3.0
 */
class Helpers {

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
	}

	/**
	 * Function to delete all rows in the posts table.
	 *
	 * @since 3.2.0
	 *
	 * @param bool $daily  Daily flag.
	 */
	public static function get_tptn_table( $daily = false ) {
		global $wpdb;

		$table_name = $wpdb->base_prefix . 'top_ten';
		if ( $daily ) {
			$table_name .= '_daily';
		}
		return $table_name;
	}


	/**
	 * Function to delete all rows in the posts table.
	 *
	 * @since 1.3
	 *
	 * @param bool $daily        Daily flag.
	 * @param bool $network_wide Network wide flag.
	 */
	public static function trunc_count( $daily = true, $network_wide = false ) {
		global $wpdb;

		if ( ! is_multisite() || $network_wide ) {
			$table_name = self::get_tptn_table( $daily );

			$sql = "TRUNCATE TABLE $table_name";
			$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		} else {
			\WebberZone\Top_Ten\Counter::delete_counts(
				array(
					'daily' => $daily,
				)
			);
		}
	}

	/**
	 * Retrieve the from date for the query
	 *
	 * @since 2.6.0
	 *
	 * @param string $time        A date/time string.
	 * @param int    $daily_range Daily range.
	 * @param int    $hour_range  Hour range.
	 * @return string From date
	 */
	public static function get_from_date( $time = null, $daily_range = null, $hour_range = null ) {

		$current_time = isset( $time ) ? strtotime( $time ) : strtotime( current_time( 'mysql' ) );
		$daily_range  = isset( $daily_range ) ? $daily_range : \tptn_get_option( 'daily_range' );
		$hour_range   = isset( $hour_range ) ? $hour_range : \tptn_get_option( 'hour_range' );

		if ( \tptn_get_option( 'daily_midnight' ) ) {
			$from_date = $current_time - ( max( 0, ( $daily_range - 1 ) ) * DAY_IN_SECONDS );
			$from_date = gmdate( 'Y-m-d 0:0:0', $from_date );
		} else {
			$from_date = $current_time - ( $daily_range * DAY_IN_SECONDS + $hour_range * HOUR_IN_SECONDS );
			$from_date = gmdate( 'Y-m-d H:0:0', $from_date );
		}

		/**
		 * Retrieve the from date for the query
		 *
		 * @since 2.6.0
		 *
		 * @param string $from_date   From date.
		 * @param string $time        A date/time string.
		 * @param int    $daily_range Daily range.
		 * @param int    $hour_range  Hour range.
		 */
		return apply_filters( 'tptn_get_from_date', $from_date, $time, $daily_range, $hour_range );
	}


	/**
	 * Convert float number to format based on the locale if number_format_count is true.
	 *
	 * @since 2.6.0
	 *
	 * @param float $number   The number to convert based on locale.
	 * @param int   $decimals Optional. Precision of the number of decimal places. Default 0.
	 * @return string Converted number in string format.
	 */
	public static function number_format_i18n( $number, $decimals = 0 ) {

		$formatted = (float) $number;

		if ( \tptn_get_option( 'number_format_count' ) ) {
			$formatted = number_format_i18n( (float) $formatted );
		}

		/**
		 * Filters the number formatted based on the locale.
		 *
		 * @since 2.6.0
		 *
		 * @param string $formatted Converted number in string format.
		 * @param float  $number    The number to convert based on locale.
		 * @param int    $decimals  Precision of the number of decimal places.
		 */
		return apply_filters( 'number_format_i18n', $formatted, $number, $decimals );
	}

	/**
	 * Convert a string to CSV.
	 *
	 * @since 2.9.0
	 *
	 * @param array  $input Input string.
	 * @param string $delimiter Delimiter.
	 * @param string $enclosure Enclosure.
	 * @param string $terminator Terminating string.
	 * @return string CSV string.
	 */
	public static function str_putcsv( $input, $delimiter = ',', $enclosure = '"', $terminator = "\n" ) {
		// First convert associative array to numeric indexed array.
		$work_array = array();
		foreach ( $input as $key => $value ) {
			$work_array[] = $value;
		}

		$string     = '';
		$input_size = count( $work_array );

		for ( $i = 0; $i < $input_size; $i++ ) {
			// Nested array, process nest item.
			if ( is_array( $work_array[ $i ] ) ) {
				$string .= self::str_putcsv( $work_array[ $i ], $delimiter, $enclosure, $terminator );
			} else {
				switch ( gettype( $work_array[ $i ] ) ) {
					// Manually set some strings.
					case 'NULL':
						$sp_format = '';
						break;
					case 'boolean':
						$sp_format = ( true === $work_array[ $i ] ) ? 'true' : 'false';
						break;
					// Make sure sprintf has a good datatype to work with.
					case 'integer':
						$sp_format = '%i';
						break;
					case 'double':
						$sp_format = '%0.2f';
						break;
					case 'string':
						$sp_format        = '%s';
						$work_array[ $i ] = str_replace( "$enclosure", "$enclosure$enclosure", $work_array[ $i ] );
						break;
					// Unknown or invalid items for a csv - note: the datatype of array is already handled above, assuming the data is nested.
					case 'object':
					case 'resource':
					default:
						$sp_format = '';
						break;
				}
				$string .= sprintf( '%2$s' . $sp_format . '%2$s', $work_array[ $i ], $enclosure );
				$string .= ( $i < ( $input_size - 1 ) ) ? $delimiter : $terminator;
			}
		}

		return $string;
	}

	/**
	 * Processes exclusion settings to return if the related posts should not be displayed on the current post.
	 *
	 * @since 3.3.0
	 *
	 * @param int|\WP_Post|null $post Post ID or post object. Defaults to global $post. Default null.
	 * @param array             $args Parameters in a query string format.
	 * @return bool True if any exclusion setting is matched.
	 */
	public static function exclude_on( $post = null, $args = array() ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return false;
		}

		// If this post ID is in the DO NOT DISPLAY list.
		$exclude_on_post_ids_list = isset( $args['exclude_on_post_ids_list'] ) ? $args['exclude_on_post_ids_list'] : \tptn_get_option( 'exclude_on_post_ids_list' );
		$exclude_on_post_ids_list = explode( ',', $exclude_on_post_ids_list );
		if ( in_array( $post->ID, $exclude_on_post_ids_list ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			return true;
		}

		// If this post type is in the DO NOT DISPLAY list.
		// If post_types is empty or contains a query string then use parse_str else consider it comma-separated.
		$exclude_on_post_types = isset( $args['exclude_on_post_types'] ) ? $args['exclude_on_post_types'] : \tptn_get_option( 'exclude_on_post_types' );
		$exclude_on_post_types = $exclude_on_post_types ? explode( ',', $exclude_on_post_types ) : array();

		if ( in_array( $post->post_type, $exclude_on_post_types, true ) ) {
			return true;
		}

		// If this post's category is in the DO NOT DISPLAY list.
		$exclude_on_categories = isset( $args['exclude_on_categories'] ) ? $args['exclude_on_categories'] : \tptn_get_option( 'exclude_on_categories' );
		$exclude_on_categories = explode( ',', $exclude_on_categories );
		$post_categories       = get_the_terms( $post->ID, 'category' );
		$categories            = array();
		if ( ! empty( $post_categories ) && ! is_wp_error( $post_categories ) ) {
			$categories = wp_list_pluck( $post_categories, 'term_taxonomy_id' );
		}
		if ( ! empty( array_intersect( $exclude_on_categories, $categories ) ) ) {
			return true;
		}

		// If the DO NOT DISPLAY meta field is set.
		if ( ( isset( $args['is_shortcode'] ) && ! $args['is_shortcode'] ) &&
		( isset( $args['is_manual'] ) && ! $args['is_manual'] ) &&
		( isset( $args['is_block'] ) && ! $args['is_block'] ) ) {
			$tptn_post_meta = get_post_meta( $post->ID, 'tptn_post_meta', true );

			if ( isset( $tptn_post_meta['disable_here'] ) ) {
				$disable_here = $tptn_post_meta['disable_here'];
			} else {
				$disable_here = 0;
			}

			if ( $disable_here ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Truncate a string to a certain length.
	 *
	 * @since 2.5.4
	 *
	 * @param  string $input String to truncate.
	 * @param  int    $count Maximum number of characters to take.
	 * @param  string $more What to append if $input needs to be trimmed.
	 * @param  bool   $break_words Optionally choose to break words.
	 * @return string Truncated string.
	 */
	public static function trim_char( $input, $count = 60, $more = '&hellip;', $break_words = false ) {
		$input = wp_strip_all_tags( $input, true );
		if ( 0 === $count ) {
			return '';
		}
		if ( mb_strlen( $input ) > $count && $count > 0 ) {
			$count -= min( $count, mb_strlen( $more ) );
			if ( ! $break_words ) {
				$input = preg_replace( '/\s+?(\S+)?$/u', '', mb_substr( $input, 0, $count + 1 ) );
			}
			$input = mb_substr( $input, 0, $count ) . $more;
		}
		/**
		 * Filters truncated string.
		 *
		 * @since 2.4.0
		 *
		 * @param string $input String to truncate.
		 * @param int $count Maximum number of characters to take.
		 * @param string $more What to append if $input needs to be trimmed.
		 * @param bool $break_words Optionally choose to break words.
		 */
		return apply_filters( 'tptn_trim_char', $input, $count, $more, $break_words );
	}

	/**
	 * Get the WP_Query arguments.
	 *
	 * @return array WP_Query arguments.
	 */
	public static function get_wp_query_arguments() {
		$arguments = array(
			// Author Parameters.
			'author'              => '',
			'author_name'         => '',
			'author__in'          => array(),
			'author__not_in'      => array(),

			// Category Parameters.
			'cat'                 => '',
			'category_name'       => '',
			'category__and'       => array(),
			'category__in'        => array(),
			'category__not_in'    => array(),

			// Tag Parameters.
			'tag'                 => '',
			'tag_id'              => '',
			'tag__and'            => array(),
			'tag__in'             => array(),
			'tag__not_in'         => array(),
			'tag_slug__and'       => array(),
			'tag_slug__in'        => array(),

			// Search Parameters.
			's'                   => '',
			'search_columns'      => array(),
			'exact'               => false,
			'sentence'            => false,

			// Post & Page Parameters.
			'p'                   => '',
			'name'                => '',
			'page_id'             => '',
			'pagename'            => '',
			'post__in'            => array(),
			'post__not_in'        => array(),
			'post_parent'         => '',
			'post_parent__in'     => array(),
			'post_parent__not_in' => array(),
			'post_name__in'       => array(),

			// Password Parameters.
			'has_password'        => false,
			'post_password'       => null,

			// Post Type Parameters.
			'post_type'           => '',

			// Status Parameters.
			'post_status'         => '',

			// Comment Parameters.
			'comment_count'       => '',

			// Pagination Parameters.
			'posts_per_page'      => '',

			// Order & Orderby Parameters.
			'orderby'             => '',
			'order'               => '',

			// Date Parameters.
			'year'                => '',
			'monthnum'            => '',
			'day'                 => '',
			'hour'                => '',
			'minute'              => '',
			'second'              => '',

			// Custom Field (post meta) Parameters.
			'meta_key'            => '', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'          => '', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'meta_value_num'      => '',
			'meta_compare'        => '',
		);

		return $arguments;
	}

	/**
	 * Parse WP_Query variables to parse comma separated list of IDs and convert them to arrays as needed by WP_Query.
	 *
	 * @param array $query_vars Defined query variables.
	 * @return array Complete query variables with undefined ones filled in empty.
	 */
	public static function parse_wp_query_arguments( $query_vars ) {

		$array_keys = array(
			'category__in',
			'category__not_in',
			'category__and',
			'post__in',
			'post__not_in',
			'post_name__in',
			'tag__in',
			'tag__not_in',
			'tag__and',
			'tag_slug__in',
			'tag_slug__and',
			'post_parent__in',
			'post_parent__not_in',
			'author__in',
			'author__not_in',
		);

		foreach ( $array_keys as $key ) {
			if ( isset( $query_vars[ $key ] ) ) {
				$query_vars[ $key ] = wp_parse_list( $query_vars[ $key ] );
			}
		}

		return $query_vars;
	}

	/**
	 * Check if the user agent matches known bots.
	 *
	 * @since 3.3.0
	 * @link https://github.com/janusman/robot-user-agents
	 *
	 * @return bool True if the user agent matches a known bot pattern, false otherwise.
	 */
	public static function is_bot() {
		if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return false;
		}

		$user_agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );

		$bot_user_agents = array(
			'11A465',
			'AddThis.com',
			'AdsBot-Google',
			'Ahrefs',
			'alexa site audit',
			'AlipesNewsBot',
			'Amazonbot',
			'Amazon-Route53-Health-Check-Service',
			'ApacheBench',
			'AppDynamics',
			'Applebot',
			'ArchiveBot',
			'Archive-It',
			'AspiegelBot',
			'Assetnote',
			'axios',
			'azure-logic-apps',
			'Baiduspider',
			'Barkrowler',
			'bingbot',
			'BLEXBot',
			'BLP_bbot',
			'BluechipBacklinks',
			'Buck',
			'Bytespider',
			'CCBot',
			'check_http',
			'CloudFlare-Prefetch',
			'cludo.com bot',
			'colly',
			'contentkingapp',
			'Cookiebot',
			'CopperEgg',
			'crawler4j',
			'Csnibot',
			'Curebot',
			'curl',
			'CyotekWebCopy',
			'Daum',
			'Datadog Agent',
			'DataForSeoBot',
			'Detectify',
			'DotBot',
			'Dow Jones Searchbot',
			'DuckDuckBot',
			'facebookexternalhit',
			'Faraday',
			'FeedBurner',
			'FeedFetcher-Google',
			'feedonomics',
			'Fess',
			'Funnelback',
			'Fuzz Faster U Fool',
			'GAChecker',
			'Ghost Inspector',
			'Grapeshot',
			'gobuster',
			'gocolly',
			'Googlebot',
			'GoogleStackdriverMonitoring',
			'Go-http-client',
			'go-resty',
			'GuzzleHttp',
			'HeadlessChrome',
			'heritrix',
			'hokifyBot',
			'Honolulu-bot',
			'HTTrack',
			'HubSpot Crawler',
			'ICC-Crawler',
			'Imperva',
			'IonCrawl',
			'jooble',
			'KauaiBot',
			'Kinza',
			'LieBaoFast',
			'linabot',
			'Linespider',
			'Linguee',
			'LinkChecker',
			'LinkedInBot',
			'LinkUpBot',
			'LinuxGetUrl',
			'LMY47V',
			'MacOutlook',
			'Magnet.me',
			'Magus Bot',
			'Mail.RU_Bot',
			'MauiBot',
			'Mb2345Browser',
			'MegaIndex',
			'Microsoft Office',
			'Microsoft Outlook',
			'Microsoft Word',
			'MicroMessenger',
			'mindbreeze-crawler',
			'mirrorweb.com',
			'MJ12bot',
			'monitoring-plugins',
			'Monsidobot',
			'MQQBrowser',
			'msnbot',
			'MSOffice',
			'MTRobot',
			'nagios-plugins',
			'nettle',
			'Neevabot',
			'NewsCred',
			'newspaper',
			'Nuclei',
			'NukeScan',
			'OnCrawl',
			'Orbbot',
			'PageFreezer',
			'panscient.com',
			'PetalBot',
			'Pingdom.com',
			'Pinterestbot',
			'PiplBot',
			'python-requests',
			'Qwantify',
			'Re-re Studio',
			'Riddler',
			'RocketValidator',
			'rogerbot',
			'RustBot',
			'Safeassign',
			'Scrapy',
			'Screaming Frog',
			'SeobilityBot',
			'Search365bot',
			'SearchBlox',
			'searchunify',
			'Seekport',
			'SemanticScholarBot',
			'SemrushBot',
			'SEOkicks',
			'seoscanners',
			'serpstatbot',
			'SessionCam',
			'SeznamBot',
			'Site24x7',
			'SiteAuditBot',
			'siteimprove',
			'SiteLockSpider',
			'SiteSucker',
			'SkypeRoom',
			'Slackbot',
			'Slurp',
			'Sogou web spider',
			'special_archiver',
			'SpiderLing',
			'StatusCake',
			'Swiftbot',
			'Synack',
			'Turnitin',
			'trendictionbot',
			'trendkite-akashic-crawler',
			'UCBrowser',
			'Uptime',
			'UptimeRobot',
			'UT-Dorkbot',
			'weborama-fetcher',
			'WhiteHat Security',
			'Wget',
			'WTWBot',
			'www.loc.gov',
			'Xenu Link Sleuth',
			'Vagabondo',
			'VelenPublicWebCrawler',
			'Yeti',
			'Veracode Security Scan',
			'YandexBot',
			'YandexImages',
			'YisouSpider',
			'Zabbix',
			'ZoominfoBot',
			'ZoomSpider',
		);

		/**
		 * Filter the list of known bots.
		 *
		 * @since 3.3.0
		 *
		 * @param array $bot_user_agents List of known bots.
		 */
		$bot_user_agent = apply_filters( 'tptn_bots', $bot_user_agents );

		foreach ( $bot_user_agents as $bot_user_agent ) {
			if ( preg_match( '/' . preg_quote( strtolower( $bot_user_agent ), '/' ) . '/i', strtolower( $user_agent ) ) ) {
				return true;
			}
		}

		return false;
	}

}
