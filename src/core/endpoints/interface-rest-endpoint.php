<?php
/**
 * Endpoints: REST_Endpoint interface
 *
 * @package WP_Chimp\Core\Endpoints
 * @since 0.4.0
 */

namespace WP_Chimp\Core\Endpoints;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

/**
 * REST endpoint interface.
 *
 * Implement this interface to create a new custom endpoint.
 *
 * @since 0.4.0
 */
interface REST_Endpoint_Interface {

	/**
	 * Retrieve the base of this controller's route.
	 *
	 * @since 0.4.0
	 *
	 * @return string
	 */
	public static function get_rest_base();

	/**
	 * Define and retrieve the route namespace.
	 *
	 * @since 0.4.0
	 *
	 * @return string The route namespace.
	 */
	public static function get_namespace();

	/**
	 * Define and retrieve the REST API version.
	 *
	 * @since 0.4.0
	 *
	 * @return string The version.
	 */
	public static function get_rest_version();
}
