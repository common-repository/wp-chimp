<?php
/**
 * Core: Languages class
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @package WP_Chimp\Core
 * @since 0.1.0
 */

namespace WP_Chimp\Core;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since 0.1.0
 */
class Languages {

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
		$this->get_hooks()->add_action( 'plugins_loaded', $this, 'load_plugin_textdomain' );
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 0.1.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'wp-chimp', false, dirname( plugin_basename( WP_CHIMP_PLUGIN_FILE ) ) . '/languages/' );
	}
}
