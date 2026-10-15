<?php

use WebberZone\Top_Ten\Frontend\Display;

class Test_Top_Ten_Rendering_Security extends WP_UnitTestCase {

	private $post_id;

	public function set_up() {
		parent::set_up();
		\WebberZone\Top_Ten\Admin\Activator::create_tables();
		$this->post_id = self::factory()->post->create(
			array(
				'post_status' => 'publish',
				'post_title'  => 'Security fixture',
			)
		);
		add_filter( 'get_tptn_posts', array( $this, 'fixture_posts' ) );
	}

	public function fixture_posts() {
		return array( get_post( $this->post_id ) );
	}

	private function render_popular_block( array $args ) {
		return do_blocks(
			'<!-- wp:top-10/popular-posts ' . wp_json_encode(
				array(
					'other_attributes' => http_build_query(
						array_merge(
							array(
								'cache'   => 0,
								'heading' => 0,
							),
							$args
						)
					),
				)
			) . ' /-->'
		);
	}

	private function assert_safe_html( $html ) {
		$this->assertStringContainsString( 'Security fixture', $html );
		$document = new DOMDocument();
		$previous = libxml_use_internal_errors( true );
		try {
			$document->loadHTML( $html );
		} finally {
			libxml_clear_errors();
			libxml_use_internal_errors( $previous );
		}
		$xpath = new DOMXPath( $document );
		$this->assertSame( 0, $xpath->query( '//script | //*[@onmouseover or @onerror or @onfocus]' )->length, $html );
		return $xpath;
	}

	public function test_block_wrapper_classes_cannot_create_attributes() {
		$html = $this->render_popular_block(
			array(
				'is_widget'   => true,
				'instance_id' => '1" onmouseover="alert(1)',
				'extra_class' => 'safe" onfocus="alert(1)',
			)
		);
		$this->assert_safe_html( $html );
		$this->assertStringContainsString( 'tptn_posts_widget-', $html );
	}

	public function test_shortcode_classes_cannot_create_attributes() {
		$html = do_shortcode( '[tptn_list cache="0" extra_class=\'safe" onmouseover="alert(1)\']' );
		$this->assert_safe_html( $html );
	}

	public function test_wrapper_is_escaped_after_class_filters() {
		$filter = static function ( $classes ) {
			return $classes . '" onmouseover="alert(1)';
		};
		add_filter( 'tptn_post_class', $filter );
		try {
			$this->assert_safe_html( Display::pop_posts( array( 'cache' => false ) ) );
		} finally {
			remove_filter( 'tptn_post_class', $filter );
		}
	}

	public function test_block_array_dimensions_cannot_create_attributes() {
		foreach ( array( 'html', 'css' ) as $mode ) {
			$html   = $this->render_popular_block(
				array(
					'thumb_size'         => array( '150" onerror="alert(1)', '90" onmouseover="alert(1)' ),
					'thumb_html'         => $mode,
					'post_thumb_op'      => 'inline',
					'tptn_styles'        => 'no_style',
					'thumb_default'      => 'https://example.org/security-fixture.jpg',
					'thumb_default_show' => true,
					'scan_images'        => false,
				)
			);
			$xpath  = $this->assert_safe_html( $html );
			$images = $xpath->query( '//img' );
			$this->assertGreaterThan( 0, $images->length );
			$this->assertStringNotContainsString( 'alert(1)', $html );
			if ( 'html' === $mode ) {
				$this->assertSame( '150', $images->item( 0 )->getAttribute( 'width' ) );
				$this->assertSame( '90', $images->item( 0 )->getAttribute( 'height' ) );
			} else {
				$this->assertStringContainsString( 'max-width:150px', $images->item( 0 )->getAttribute( 'style' ) );
				$this->assertStringContainsString( 'max-height:90px', $images->item( 0 )->getAttribute( 'style' ) );
			}
		}
	}
	public function test_locked_posts_do_not_expose_body_images() {
		wp_update_post(
			array(
				'ID'            => $this->post_id,
				'post_password' => 'secret',
				'post_content'  => '<img src="https://example.org/secret-body-image.jpg" />',
			)
		);
		wp_set_current_user( 0 );
		$html = $this->render_popular_block(
			array(
				'post_thumb_op' => 'inline',
				'tptn_styles'   => 'no_style',
				'scan_images'   => true,
			)
		);
		$this->assertStringNotContainsString( 'secret-body-image', $html );
	}

	public function test_unlocked_excerpt_is_not_cached_for_other_visitors() {
		wp_update_post(
			array(
				'ID'            => $this->post_id,
				'post_password' => 'secret',
				'post_excerpt'  => 'secretcachedexcerpt',
			)
		);
		wp_set_current_user( 0 );
		require_once ABSPATH . WPINC . '/class-phpass.php';
		$hasher   = new PasswordHash( 8, true );
		$cookie   = 'wp-postpass_' . COOKIEHASH;
		$previous = $_COOKIE[ $cookie ] ?? null;
		$args     = array(
			'cache'          => true,
			'show_excerpt'   => true,
			'excerpt_length' => 20,
		);
		try {
			unset( $_COOKIE[ $cookie ] );
			$this->assertStringNotContainsString( 'secretcachedexcerpt', Display::pop_posts( $args ) );
			$_COOKIE[ $cookie ] = $hasher->HashPassword( 'secret' );
			$this->assertStringContainsString( 'secretcachedexcerpt', Display::pop_posts( $args ) );
			unset( $_COOKIE[ $cookie ] );
			$this->assertStringNotContainsString( 'secretcachedexcerpt', Display::pop_posts( $args ) );
		} finally {
			if ( null === $previous ) {
				unset( $_COOKIE[ $cookie ] );
			} else {
				$_COOKIE[ $cookie ] = $previous;
			}
		}
	}

	public function test_adding_password_invalidates_cached_excerpt() {
		wp_update_post(
			array(
				'ID'           => $this->post_id,
				'post_excerpt' => 'newlyprotectedexcerpt',
			)
		);
		wp_set_current_user( 0 );
		$args = array(
			'cache'          => true,
			'show_excerpt'   => true,
			'excerpt_length' => 20,
		);
		$this->assertStringContainsString( 'newlyprotectedexcerpt', Display::pop_posts( $args ) );
		wp_update_post(
			array(
				'ID'            => $this->post_id,
				'post_password' => 'secret',
			)
		);
		$this->assertStringNotContainsString( 'newlyprotectedexcerpt', Display::pop_posts( $args ) );
	}

	public function test_block_markup_and_thumbnail_urls_are_safe() {
		$html = $this->render_popular_block(
			array(
				'heading'            => true,
				'title'              => '<h3 onmouseover="alert(1)">Heading</h3>',
				'before_list'        => '<ul onfocus="alert(1)">',
				'post_thumb_op'      => 'inline',
				'tptn_styles'        => 'no_style',
				'thumb_default'      => 'javascript:alert(1)',
				'thumb_default_show' => true,
				'scan_images'        => false,
			)
		);
		$this->assert_safe_html( $html );
		$this->assertStringNotContainsString( 'javascript:', $html );
	}
}
