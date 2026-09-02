<?php
/**
 * PHPStan bootstrap file for Top 10.
 *
 * @package WebberZone\Top_Ten
 */

namespace {
	if ( ! defined( 'TOP_TEN_VERSION' ) ) {
		define( 'TOP_TEN_VERSION', '0.0.0' );
	}

	if ( ! defined( 'TOP_TEN_PLUGIN_FILE' ) ) {
		define( 'TOP_TEN_PLUGIN_FILE', '' );
	}

	if ( ! defined( 'TOP_TEN_PLUGIN_DIR' ) ) {
		define( 'TOP_TEN_PLUGIN_DIR', '' );
	}

	if ( ! defined( 'TOP_TEN_PLUGIN_URL' ) ) {
		define( 'TOP_TEN_PLUGIN_URL', '' );
	}

	if ( ! defined( 'TOP_TEN_STORE_DATA' ) ) {
		define( 'TOP_TEN_STORE_DATA', 180 );
	}

	if ( ! defined( 'TOP_TEN_LOG_STORE_DATA' ) ) {
		define( 'TOP_TEN_LOG_STORE_DATA', 30 );
	}

	if ( ! defined( 'TOP_TEN_DB_VERSION' ) ) {
		define( 'TOP_TEN_DB_VERSION', '0.0.0' );
	}

	if ( ! function_exists( 'fs_dynamic_init' ) ) {
		/**
		 * Freemius fs_dynamic_init() stub for static analysis.
		 *
		 * This is loaded only by PHPStan and never in normal plugin runtime.
		 *
		 * @param array $args Freemius initialisation arguments.
		 * @return object Object with minimal Freemius-like API.
		 */
		function fs_dynamic_init( array $args ) {
			unset( $args );
			return new class() {
				/**
				 * Stub add_filter method.
				 *
				 * @param string   $hook_name Hook name.
				 * @param callable $callback  Callback.
				 * @return void
				 */
				public function add_filter( $hook_name, $callback ) {
				}
			};
		}
	}

	if ( ! function_exists( 'vc_map' ) ) {
		/**
		 * WPBakery Page Builder element-registration stub for static analysis.
		 *
		 * @param array<string, mixed> $args Element definition.
		 * @return void
		 */
		function vc_map( array $args ) {
			do_action( 'tptn_wpbakery_vc_map_stub', $args );
		}
	}
}

// Elementor has no official PHPStan stub package, so declare the minimal surface Top 10's
// Elementor builder module and widget touch.
namespace Elementor {
	if ( ! class_exists( __NAMESPACE__ . '\\Controls_Manager' ) ) {
		class Controls_Manager {
			const TEXT     = 'text';
			const NUMBER   = 'number';
			const SELECT   = 'select';
			const SWITCHER = 'switcher';
			const TEXTAREA = 'textarea';

			const TAB_CONTENT = 'content';
		}
	}

	if ( ! class_exists( __NAMESPACE__ . '\\Widget_Base' ) ) {
		abstract class Widget_Base {
			/** @return string */
			abstract public function get_name();

			/** @return string */
			abstract public function get_title();

			/** @return string */
			abstract public function get_icon();

			/** @return string[] */
			abstract public function get_categories();

			/** @param array<string, mixed> $args */
			protected function start_controls_section( string $section_id, array $args = array() ): void {}

			protected function end_controls_section(): void {}

			/** @param array<string, mixed> $args */
			protected function add_control( string $control_id, array $args = array() ): void {}

			abstract protected function register_controls(): void;

			abstract protected function render(): void;

			/** @return array<string, mixed> */
			protected function get_settings_for_display( ?string $setting_key = null ) {
				return array();
			}
		}
	}

	if ( ! class_exists( __NAMESPACE__ . '\\Elements_Manager' ) ) {
		class Elements_Manager {
			/** @param array<string, mixed> $args */
			public function add_category( string $category_id, array $args = array() ): void {}
		}
	}

	if ( ! class_exists( __NAMESPACE__ . '\\Widgets_Manager' ) ) {
		class Widgets_Manager {
			public function register( Widget_Base $widget ): bool {
				return true;
			}
		}
	}

	if ( ! class_exists( __NAMESPACE__ . '\\Plugin' ) ) {
		class Plugin {}
	}
}

// Bricks Builder has no official PHPStan stub package, so declare the minimal surface Top 10's
// Bricks builder module, element and style handler touch.
namespace {
	if ( ! defined( 'BRICKS_DB_PAGE_CONTENT' ) ) {
		define( 'BRICKS_DB_PAGE_CONTENT', '_bricks_page_content_2' );
	}

	if ( ! defined( 'BRICKS_DB_PAGE_HEADER' ) ) {
		define( 'BRICKS_DB_PAGE_HEADER', '_bricks_page_header_2' );
	}

	if ( ! defined( 'BRICKS_DB_PAGE_FOOTER' ) ) {
		define( 'BRICKS_DB_PAGE_FOOTER', '_bricks_page_footer_2' );
	}

	if ( ! function_exists( 'bricks_is_builder' ) ) {
		/**
		 * Bricks builder-context stub for static analysis.
		 *
		 * @return bool
		 */
		function bricks_is_builder() {
			return false;
		}
	}
}

namespace Bricks {
	if ( ! class_exists( __NAMESPACE__ . '\\Element' ) ) {
		abstract class Element {
			/** @var string */
			public $category = '';

			/** @var string */
			public $name = '';

			/** @var string */
			public $icon = '';

			/** @var array<string, mixed> */
			public $controls = array();

			/** @var array<string, mixed> */
			public $control_groups = array();

			/** @var array<string, mixed> */
			public $control_options = array();

			/** @var mixed Raw element settings; Bricks does not guarantee an array. */
			public $settings = array();

			/** @return string */
			public function get_label() {
				return '';
			}

			/** @return string */
			public function get_description() {
				return '';
			}

			/** @return string[] */
			public function get_keywords() {
				return array();
			}

			/** @return void */
			public function set_control_groups() {}

			/** @return void */
			public function set_controls() {}

			/** @return void */
			public function render() {}

			/**
			 * @param string $key Attribute group key.
			 * @return string
			 */
			public function render_attributes( $key = '_root' ) {
				unset( $key );
				return '';
			}

			/**
			 * @param array<string, mixed> $args Placeholder arguments.
			 * @return void
			 */
			public function render_element_placeholder( array $args = array() ) {
				unset( $args );
			}

			/**
			 * @param string $content Content containing dynamic data tags.
			 * @return string
			 */
			public function render_dynamic_data( $content = '' ) {
				return (string) $content;
			}
		}
	}

	if ( ! class_exists( __NAMESPACE__ . '\\Elements' ) ) {
		class Elements {
			/**
			 * @param string $file       Path to the element class file.
			 * @param string $name       Element name.
			 * @param string $class_name Element class name.
			 * @return bool
			 */
			public static function register_element( $file, $name = '', $class_name = '' ) {
				unset( $file, $name, $class_name );
				return true;
			}
		}
	}

	if ( ! class_exists( __NAMESPACE__ . '\\Helpers' ) ) {
		class Helpers {
			/** @return array<string, string> */
			public static function get_registered_post_types() {
				return array();
			}
		}
	}
}

// When running on the free plugin (includes/pro/ removed by sync), define Pro class stubs
// so PHPStan can resolve the ?Pro\Pro $pro property and the site-wide database stub used
// by shared admin classes.
namespace WebberZone\Top_Ten\Pro {
	if ( ! is_dir( dirname( __FILE__ ) . '/includes/pro' ) ) {
		class Pro {} // phpcs:ignore

		class Sitewide_Database {
			public const CONTEXT_IDS = array( 'front_page' => PHP_INT_MAX );

			public static function is_available(): bool {
				return false;
			}

			public static function get_context_key( $post_id, $blog_id = null ): string {
				unset( $post_id, $blog_id );
				return '';
			}

			public static function get_context_label( $context_key ): string {
				unset( $context_key );
				return '';
			}

			public static function get_context_ids(): array {
				return array();
			}
		}
	}
}
