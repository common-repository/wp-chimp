<?php
/**
 * Endpoints: REST_Controller class
 *
 * @package WP_Chimp\Core\Endpoints
 * @since 0.3.0
 */

namespace WP_Chimp\Core\Endpoints;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

use WP_Chimp\Core\Hooks_Trait;
use WP_Chimp\Core\Lists\Query_Trait;
use WP_Chimp\Deps\DrewM\MailChimp\MailChimp;
use WP_Error;
use WP_REST_Controller;

/**
 * Custom base controller for managing and interacting with REST API items in the plugin.
 *
 * @since 0.3.0
 * @since 0.4.0 Implements REST_Endpoint_Interface interface.
 * @since 0.6.0 Removed unused methods and properties (e.g. `$version`, `$plugin_name`, and `$lists_process`).
 *
 * @property string $namespace
 * @property string $rest_base
 * @property WP_Chimp\Deps\DrewM\MailChimp\MailChimp $mailchimp
 */
abstract class REST_Controller extends WP_REST_Controller implements REST_Endpoint_Interface {

	/**
	 * Use traits.
	 *
	 * @since 0.7.0
	 */
	use Hooks_Trait, Query_Trait;

	/**
	 * API Endpoint namespace.
	 *
	 * @since 0.3.0
	 * @var string
	 */
	protected $namespace;

	/**
	 * The base of this controller's route.
	 *
	 * @since 0.3.0
	 * @var string
	 */
	protected $rest_base;

	/**
	 * The MailChimp API key added in the option.
	 *
	 * @since 0.3.0
	 * @var DrewM\MailChimp\MailChimp
	 */
	protected $mailchimp;

	/**
	 * The Constructor.
	 *
	 * @since 0.3.0
	 */
	public function __construct() {
		$this->namespace = self::get_namespace();
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 0.3.0
	 */
	abstract public function run();

	/**
	 * Function to register the MailChimp instance.
	 *
	 * @since 0.3.0
	 *
	 * @param MailChimp $mailchimp The MailChimp instance.
	 */
	public function set_mailchimp( MailChimp $mailchimp ) {
		$this->mailchimp = $mailchimp;
	}

	/**
	 * Define and retrieve the base of this controller's route.
	 *
	 * @since 0.3.0
	 * @since 0.4.0 Turn to static to comply with the interface, and enforce overridden by returning a WP_Error instance
	 *
	 * @return string
	 */
	public static function get_rest_base() {
		/* translators: %s: method name */
		return new WP_Error( 'wp_chimp_invalid_method', sprintf( __( "Method '%s' not implemented. Must be overridden in subclass.", 'wp-chimp' ), __METHOD__ ), [ 'status' => 405 ] );
	}

	/**
	 * Define and retrieve the route namespace.
	 *
	 * @since 0.3.0
	 *
	 * @return string The route namespace.
	 */
	final public static function get_namespace() {
		return 'wp-chimp/' . self::get_rest_version();
	}

	/**
	 * Define and retrieve the REST API version.
	 *
	 * @since 0.3.0
	 *
	 * @return string The version.
	 */
	final public static function get_rest_version() {
		return 'v1';
	}
}
