<?php
/**
 * Admin: Main class
 *
 * @package WP_Chimp\Admin
 * @since 0.1.0
 */

namespace WP_Chimp\Admin;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

use function WP_Chimp\Core\get_the_script_suffix;
use function WP_Chimp\Core\is_admin_page;
use WP_Chimp\Core\Hooks_Trait;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @since 0.1.0
 */
class Admin {

	/**
	 * Use traits.
	 *
	 * @since 0.7.0
	 */
	use Hooks_Trait;

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 0.3.0
	 */
	public function run() {
		$this->get_hooks()->add_action( 'admin_enqueue_scripts', $this, 'enqueue_admin_styles' );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 0.1.0
	 * @since 0.6.0 Renamed to enqueue_admin_styles
	 */
	public function enqueue_admin_styles() {

		if ( is_admin_page() ) {
			$suffix = get_the_script_suffix();

			$handle = 'wp-chimp-settings';
			$file = "assets/css/admin{$suffix}.css";
			$deps = [ 'wp-components' ];
			$mod = filemtime( WP_CHIMP_PLUGIN_DIR . $file );

			wp_enqueue_style( $handle, plugins_url( $file, WP_CHIMP_PLUGIN_FILE ), $deps, $mod, 'all' );
		}
	}
}
