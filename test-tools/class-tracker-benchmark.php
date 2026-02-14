<?php
/**
 * Benchmark trackers helper.
 *
 * @package WebberZone\Top_Ten\Tools
 */

namespace WebberZone\Top_Ten\Tools;

use WebberZone\Top_Ten\Database;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Measures tracker response times and restores counts.
 *
 * @since 4.2.0
 */
class Tracker_Benchmark {

	/**
	 * Post ID to benchmark.
	 *
	 * @var int
	 */
	private int $post_id = 0;

	/**
	 * Blog ID for the post.
	 *
	 * @var int
	 */
	private int $blog_id = 0;

	/**
	 * Number of iterations per tracker.
	 *
	 * @var int
	 */
	private int $iterations = 3;

	/**
	 * Whether to skip SSL verification (dev only).
	 *
	 * @var bool
	 */
	private bool $insecure = false;

	/**
	 * Whether to restore counts after benchmarking.
	 *
	 * @var bool
	 */
	private bool $should_restore = true;

	/**
	 * Tracker keys to benchmark.
	 *
	 * @var array
	 */
	private array $trackers_to_run = array();

	/**
	 * Snapshot of counts to restore later.
	 *
	 * @var array
	 */
	private array $snapshot = array(
		'overall' => array(),
		'daily'   => array(),
	);


	/**
	 * Initialize CLI arguments.
	 */
	public function __construct() {
		$options = getopt(
			'',
			array(
				'post:',
				'blog::',
				'iterations::',
				'trackers::',
				'insecure::',
				'no-restore',
			)
		);

		$this->post_id    = isset( $options['post'] ) ? absint( $options['post'] ) : 0;
		$this->blog_id    = isset( $options['blog'] ) ? absint( $options['blog'] ) : get_current_blog_id();
		$this->iterations = isset( $options['iterations'] ) ? max( 1, absint( $options['iterations'] ) ) : 3;

		$this->insecure       = array_key_exists( 'insecure', $options );
		$this->should_restore = ! array_key_exists( 'no-restore', $options );

		$tracker_arg = isset( $options['trackers'] ) ? sanitize_text_field( (string) $options['trackers'] ) : '';

		if ( '' !== $tracker_arg ) {
			$this->trackers_to_run = array_filter(
				array_map(
					static function ( $tracker ) {
						return strtolower( trim( $tracker ) );
					},
					explode( ',', $tracker_arg )
				)
			);
		}

		if ( empty( $this->trackers_to_run ) ) {
			$this->trackers_to_run = array( 'query', 'ajax', 'rest', 'fast', 'high' );
		}

		if ( $this->post_id <= 0 ) {
			$this->print_usage();
			exit( 1 );
		}
	}

	/**
	 * Execute the benchmark.
	 *
	 * @return void
	 */
	public function run() {
		if ( $this->should_restore ) {
			$this->snapshot_counts();
		}

		try {
			$results = $this->benchmark_trackers();
			$this->print_results( $results );
		} catch ( \Throwable $exception ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI context.
			echo 'Benchmark failed: ' . $exception->getMessage() . PHP_EOL;
		} finally {
			if ( $this->should_restore ) {
				$this->restore_counts();
			}
		}
	}

	/**
	 * Print usage instructions.
	 *
	 * @return void
	 */
	private function print_usage() {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI context.
		echo "Usage: php test-tools/benchmark-trackers.php --post=<POST_ID> [--blog=<BLOG_ID>] [--iterations=3] [--trackers=query,ajax,rest,fast,high] [--insecure] [--no-restore]\n";
		echo "  --insecure    Disable SSL verification (use only on local/dev setups)\n";
		echo "  --no-restore  Leave counts as-is after benchmarks (no snapshot restore)\n";
	}

	/**
	 * Snapshot current counts for the test post.
	 *
	 * @return void
	 */
	private function snapshot_counts() {
		global $wpdb;

		$overall_table = Database::get_table( false );
		$daily_table   = Database::get_table( true );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$this->snapshot['overall'] = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT postnumber, cntaccess, blog_id FROM {$overall_table} WHERE postnumber = %d AND blog_id = %d",
				$this->post_id,
				$this->blog_id
			),
			ARRAY_A
		);

		$this->snapshot['daily'] = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT postnumber, cntaccess, dp_date, blog_id FROM {$daily_table} WHERE postnumber = %d AND blog_id = %d",
				$this->post_id,
				$this->blog_id
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Restore counts to their original values.
	 *
	 * @return void
	 */
	private function restore_counts() {
		global $wpdb;

		$overall_table = Database::get_table( false );
		$daily_table   = Database::get_table( true );

		$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$overall_table,
			array(
				'postnumber' => $this->post_id,
				'blog_id'    => $this->blog_id,
			),
			array( '%d', '%d' )
		);

		$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$daily_table,
			array(
				'postnumber' => $this->post_id,
				'blog_id'    => $this->blog_id,
			),
			array( '%d', '%d' )
		);

		if ( ! empty( $this->snapshot['overall'] ) && is_array( $this->snapshot['overall'] ) ) {
			$row = $this->snapshot['overall'];

			$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$overall_table,
				array(
					'postnumber' => $row['postnumber'],
					'cntaccess'  => $row['cntaccess'],
					'blog_id'    => $row['blog_id'],
				),
				array( '%d', '%d', '%d' )
			);
		}

		if ( ! empty( $this->snapshot['daily'] ) && is_array( $this->snapshot['daily'] ) ) {
			foreach ( $this->snapshot['daily'] as $row ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$daily_table,
					array(
						'postnumber' => $row['postnumber'],
						'cntaccess'  => $row['cntaccess'],
						'dp_date'    => $row['dp_date'],
						'blog_id'    => $row['blog_id'],
					),
					array( '%d', '%d', '%s', '%d' )
				);
			}
		}
	}

	/**
	 * Run tracker benchmarks.
	 *
	 * @return array
	 */
	private function benchmark_trackers(): array {
		$results    = array();
		$trackermap = $this->get_tracker_definitions();
		$payload    = $this->get_base_payload();

		foreach ( $this->trackers_to_run as $tracker_key ) {
			if ( ! isset( $trackermap[ $tracker_key ] ) ) {
				$message = sprintf(
					'Tracker "%s" not recognised. Skipping.' . PHP_EOL,
					$tracker_key
				);
				echo $message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$results[ $tracker_key ] = array(
					'label'      => ucfirst( $tracker_key ),
					'iterations' => array(),
					'error'      => __( 'Tracker not recognised.', 'top-10' ),
				);
				continue;
			}

			$definition = $trackermap[ $tracker_key ];

			if ( ! empty( $definition['skip_reason'] ) ) {
				$message = sprintf(
					'%s skipped: %s' . PHP_EOL,
					$definition['label'],
					$definition['skip_reason']
				);
				echo $message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$results[ $tracker_key ] = array(
					'label'      => $definition['label'],
					'iterations' => array(),
					'error'      => $definition['skip_reason'],
				);
				continue;
			}

			$results[ $tracker_key ] = array(
				'label'      => $definition['label'],
				'iterations' => array(),
			);

			printf( 'Running %s (%d iterations)... ', $definition['label'], $this->iterations ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			for ( $index = 1; $index <= $this->iterations; $index++ ) {
				$request                                 = call_user_func( $definition['builder'], $payload );
				$results[ $tracker_key ]['iterations'][] = $this->measure_request( $request );

				echo '.'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				flush();
			}

			echo ' done.' . PHP_EOL . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $results;
	}

	/**
	 * Default payload shared by trackers.
	 *
	 * @return array
	 */
	private function get_base_payload(): array {
		return array(
			'top_ten_id'       => $this->post_id,
			'top_ten_blog_id'  => $this->blog_id,
			'activate_counter' => 11,
			'top_ten_debug'    => 0,
		);
	}

	/**
	 * Tracker definitions and builders.
	 *
	 * @return array
	 */
	private function get_tracker_definitions(): array {
		$high_config = $this->load_high_traffic_config();

		return array(
			'query' => array(
				'label'   => __( 'Query-based tracker', 'top-10' ),
				'builder' => function ( array $payload ) {
					$url = add_query_arg( $payload, home_url( '/' ) );

					return array(
						'url'    => $url,
						'method' => 'GET',
					);
				},
			),
			'ajax'  => array(
				'label'   => __( 'AJAX tracker', 'top-10' ),
				'builder' => function ( array $payload ) {
					$body           = $payload;
					$body['action'] = 'tptn_tracker';

					return array(
						'url'    => admin_url( 'admin-ajax.php' ),
						'method' => 'POST',
						'body'   => $body,
					);
				},
			),
			'rest'  => array(
				'label'   => __( 'REST tracker', 'top-10' ),
				'builder' => function ( array $payload ) {
					return array(
						'url'    => rest_url( 'top-10/v1/tracker' ),
						'method' => 'POST',
						'body'   => $payload,
					);
				},
			),
			'fast'  => array(
				'label'   => __( 'Fast tracker', 'top-10' ),
				'builder' => function ( array $payload ) {
					return array(
						'url'     => plugins_url( 'includes/pro/fast-tracker-js.php', TOP_TEN_PLUGIN_FILE ),
						'method'  => 'POST',
						'body'    => $payload,
						'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
					);
				},
			),
			'high'  => array(
				'label'       => __( 'High-traffic tracker', 'top-10' ),
				'builder'     => function ( array $payload ) {
					return array(
						'url'     => plugins_url( 'includes/pro/high-traffic-tracker-js.php', TOP_TEN_PLUGIN_FILE ),
						'method'  => 'POST',
						'body'    => $payload,
						'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
					);
				},
				'skip_reason' => $high_config ? '' : __( 'High-traffic config not found. Skipping.', 'top-10' ),
			),
		);
	}

	/**
	 * Execute a request and capture timing.
	 *
	 * @param array $request Request definition.
	 * @return array
	 */
	private function measure_request( array $request ): array {
		$method = isset( $request['method'] ) ? strtoupper( $request['method'] ) : 'GET';
		$args   = array(
			'method'    => $method,
			'timeout'   => 10,
			'sslverify' => ! $this->insecure,
		);

		if ( ! empty( $request['headers'] ) ) {
			$args['headers'] = $request['headers'];
		}

		if ( 'POST' === $method && ! empty( $request['body'] ) ) {
			$args['body'] = $request['body'];
		}

		$start_time = microtime( true );
		$response   = wp_remote_request( $request['url'], $args );
		$end_time   = microtime( true );

		$duration = ( $end_time - $start_time ) * 1000;

		if ( is_wp_error( $response ) ) {
			return array(
				'duration' => $duration,
				'status'   => 0,
				'message'  => $response->get_error_message(),
			);
		}

		$status  = wp_remote_retrieve_response_code( $response );
		$message = wp_remote_retrieve_response_message( $response );
		$success = ( 200 <= $status && 300 > $status );

		$fallback_reason = wp_remote_retrieve_header( $response, 'x-tptn-fallback-reason' );
		if ( ! empty( $fallback_reason ) ) {
			return array(
				'duration' => $duration,
				'status'   => 0,
				'message'  => sprintf(
					/* translators: %s: fallback reason header. */
					__( 'High-traffic fallback: %s', 'top-10' ),
					$fallback_reason
				),
			);
		}

		return array(
			'duration' => $duration,
			'status'   => $status,
			'message'  => $success ? '' : ( $message ? $message : sprintf(
				/* translators: %d: HTTP status code. */
				__( 'HTTP status %d', 'top-10' ),
				$status
			) ),
		);
	}

	/**
	 * Print formatted benchmark results.
	 *
	 * @param array $results Benchmark data.
	 *
	 * @return void
	 */
	private function print_results( array $results ) {
		$summary_rows = array();

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI context.
		printf( "Tracker Benchmark Results (Post ID %d, Blog ID %d)\n", $this->post_id, $this->blog_id );
		printf( "Iterations: %d\n\n", $this->iterations );

		foreach ( $results as $tracker_key => $data ) {
			$label = isset( $data['label'] ) ? $data['label'] : ucfirst( $tracker_key );
			echo "=== {$label} ===\n";

			if ( ! empty( $data['error'] ) ) {
				echo $data['error'] . "\n\n";
				continue;
			}

			if ( empty( $data['iterations'] ) ) {
				echo "No data recorded.\n\n";
				continue;
			}

			$success_durations = array();
			foreach ( $data['iterations'] as $row ) {
				if ( 200 <= $row['status'] && 300 > $row['status'] ) {
					$success_durations[] = $row['duration'];
				}
			}

			foreach ( $data['iterations'] as $index => $row ) {
				$iteration = $index + 1;
				$duration  = number_format( $row['duration'], 2 );
				$status    = $row['status'];
				$message   = $row['message'] ? ' - ' . $row['message'] : '';

				printf( "Run %d: %sms (status %s)%s\n", $iteration, $duration, $status, $message );
			}

			if ( empty( $success_durations ) ) {
				echo "All runs failed. Fix the error above and re-run.\n\n";
				$summary_rows[] = array(
					'label'           => $label,
					'average'         => null,
					'successful_runs' => 0,
					'failed_runs'     => count( $data['iterations'] ),
				);
				continue;
			}

			$average = array_sum( $success_durations ) / count( $success_durations );
			printf( "Average (successful runs): %sms\n\n", number_format( $average, 2 ) );

			$summary_rows[] = array(
				'label'           => $label,
				'average'         => $average,
				'successful_runs' => count( $success_durations ),
				'failed_runs'     => count( $data['iterations'] ) - count( $success_durations ),
			);
		}

		if ( ! empty( $summary_rows ) ) {
			echo "Summary\n";
			echo str_repeat( '-', 60 ) . "\n";
			printf( "%-25s %-15s %-10s %-10s\n", 'Tracker', 'Avg (ms)', 'Success', 'Failed' );
			echo str_repeat( '-', 60 ) . "\n";

			foreach ( $summary_rows as $row ) {
				$avg_display = null === $row['average'] ? '—' : number_format( $row['average'], 2 );
				printf(
					"%-25s %-15s %-10d %-10d\n",
					$row['label'],
					$avg_display,
					$row['successful_runs'],
					$row['failed_runs']
				);
			}

			echo str_repeat( '-', 60 ) . "\n\n";
		}
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Load the High-traffic tracker configuration.
	 *
	 * @return array|null
	 */
	private function load_high_traffic_config() {
		$config_path = $this->find_high_traffic_config_path();

		if ( '' === $config_path ) {
			return null;
		}

		if ( ! defined( 'TPTN_FASTCFG_LOADER' ) ) {
			define( 'TPTN_FASTCFG_LOADER', true );
		}

		$config = include $config_path;

		$required_keys = array( 'host', 'user', 'pass', 'name', 'prefix' );
		foreach ( $required_keys as $key ) {
			if ( empty( $config[ $key ] ) ) {
				return null;
			}
		}

		return $config;
	}


	/**
	 * Find the High-traffic config path.
	 *
	 * @return string
	 */
	private function find_high_traffic_config_path(): string {
		$candidates = array();

		$wp_config_dir = $this->get_wp_config_dir();
		if ( '' !== $wp_config_dir ) {
			$candidates[] = trailingslashit( $wp_config_dir ) . 'top-10-fast-config.php';
		}

		$docroot_parent = $this->get_document_root_parent();
		if ( '' !== $docroot_parent ) {
			$candidates[] = trailingslashit( $docroot_parent ) . 'top-10-fast-config.php';
		}

		foreach ( $candidates as $candidate ) {
			if ( $this->is_path_in_docroot( $candidate ) ) {
				continue;
			}

			if ( file_exists( $candidate ) && is_readable( $candidate ) && ! is_link( $candidate ) ) {
				return $candidate;
			}
		}

		return '';
	}

	/**
	 * Determine WordPress config directory.
	 *
	 * @return string
	 */
	private function get_wp_config_dir(): string {
		if ( defined( 'ABSPATH' ) ) {
			if ( file_exists( ABSPATH . 'wp-config.php' ) ) {
				return untrailingslashit( ABSPATH );
			}

			$up = dirname( untrailingslashit( ABSPATH ) );
			if ( file_exists( $up . '/wp-config.php' ) ) {
				return $up;
			}
		}

		return '';
	}

	/**
	 * Get the parent directory of the document root.
	 *
	 * @return string
	 */
	private function get_document_root_parent(): string {
		$docroot = isset( $_SERVER['DOCUMENT_ROOT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['DOCUMENT_ROOT'] ) ) : '';

		if ( '' === $docroot ) {
			return '';
		}

		$real_path = realpath( $docroot );
		if ( false === $real_path ) {
			return '';
		}

		$parent = dirname( $real_path );

		return is_dir( $parent ) ? $parent : '';
	}

	/**
	 * Check whether a path is inside the document root.
	 *
	 * @param string $path Absolute path.
	 *
	 * @return bool
	 */
	private function is_path_in_docroot( string $path ): bool {
		$docroot = isset( $_SERVER['DOCUMENT_ROOT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['DOCUMENT_ROOT'] ) ) : '';

		if ( '' === $docroot ) {
			return false;
		}

		$docroot_real = realpath( $docroot );
		$path_real    = realpath( $path );

		if ( false === $docroot_real || false === $path_real ) {
			return false;
		}

		return 0 === strpos( $path_real, trailingslashit( $docroot_real ) );
	}
}
