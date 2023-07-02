<?php

class Sample_Test extends WP_UnitTestCase {
	public function test_post_type_exists() {
		// The full application and it's functions are loaded.
		$this->assertTrue( post_type_exists( 'post' ) );
	}
}
