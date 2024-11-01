<?php
/**
 * Admin: Tab interface
 *
 * @package WP_Chimp\Admin
 * @since 0.6.0
 */

namespace WP_Chimp\Admin\Partials;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

/**
 * Tab interface.
 *
 * The interface to define the plugin Setting tab.
 *
 * @since 0.6.0
 */
interface Tab_Interface {

	/**
	 * Render the tab content.
	 *
	 * @since 0.6.0
	 *
	 * @return string
	 */
	public function content();

	/**
	 * Retrieve the Tab title.
	 *
	 * @since 0.6.0
	 *
	 * @return string The Tab title.
	 */
	public function get_title();

	/**
	 * Retrieve the Tab slug.
	 *
	 * @since 0.6.0
	 *
	 * @return string The Tab slug.
	 */
	public function get_slug();
}
