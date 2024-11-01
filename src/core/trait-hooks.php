<?php
/**
 * Core: Hooks trait
 *
 * @package WP_Chimp\Core
 * @since 0.7.0
 */

namespace WP_Chimp\Core;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

/**
 * Trait that provides interface to inject Hooks instance.
 *
 * @since 0.7.0
 *
 * @property WP_Chimp\Core\Hooks $loader
 */
trait Hooks_Trait {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since 0.7.0
	 * @var WP_Chimp\Core\Hooks $hooks Maintains and registers all hooks for the plugin.
	 */
	private $hooks;

	/**
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since 0.7.0
	 *
	 * @param WP_Chimp\Core\Hooks $hooks The Hooks instance.
	 */
	public function set_hooks( Hooks $hooks ) {
		$this->hooks = $hooks;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since 0.7.0
	 *
	 * @return WP_Chimp\Core\Hooks The Hooks instance.
	 */
	public function get_hooks() {
		return $this->hooks;
	}
}
