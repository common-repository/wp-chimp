<?php
/**
 * Core: Options class
 *
 * @package WP_Chimp\Core
 * @since 0.2.0
 */

namespace WP_Chimp\Core;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

/**
 * Define the site options functionality.
 *
 * The class poses a wrapper for the native WordPress `*_option` functions.
 *
 * @since 0.2.0
 */
class Options {

	/**
	 * Use traits.
	 *
	 * @since 0.7.0
	 */
	use Hooks_Trait;

	/**
	 * Lists of the registered options in the plugin; the names and their default value.
	 *
	 * @since 0.2.0
	 * @since 0.6.0 Changed to a constant instead of a property (requires PHP5.6+).
	 *
	 * @var array
	 */
	const OPTIONS = [
		'wp_chimp_api_key_status' => [
			'default' => 'invalid',
			'sanitize_callback' => [ __CLASS__, 'sanitize_api_key_status' ],
		],
		'wp_chimp_lists_total_items' => [
			'default' => 0,
			'sanitize_callback' => 'absint',
		],
		'wp_chimp_lists_db_version' => [
			'default' => 0,
			'sanitize_callback' => 'absint',
		],
		'wp_chimp_lists_db_upgraded' => [
			'default' => [],
			'autoload' => false,
			'sanitize_callback' => [ __CLASS__, 'sanitize_lists_db_upgraded' ],
		],
		'wp_chimp_lists_init' => [
			'default' => 0,
			'autoload' => false,
			'sanitize_callback' => 'absint',
		],
		'wp_chimp_default_list' => [
			'default' => '',
			'sanitize_callback' => 'sanitize_key',
		],
	];

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 0.6.0
	 */
	public function run() {

		foreach ( self::OPTIONS as $option_name => $data ) {
			$this->get_hooks()->add_filter( "default_option_{$option_name}", $this, 'option_defaults', PHP_INT_MAX, 2 );
			$this->get_hooks()->add_filter( "option_{$option_name}", $this, 'sanitize_options_output', PHP_INT_MAX, 2 );
		}
	}

	/**
	 * Filters the default value for the options.
	 *
	 * @since 0.6.0
	 *
	 * @param mixed  $default The default value to return if the option does not exist in the database.
	 * @param string $option_name Option name.
	 * @return mixed
	 */
	public function option_defaults( $default, $option_name ) {

		$default = self::OPTIONS[ $option_name ]['default'];
		if ( ! is_null( $default ) ) {
			$default = self::OPTIONS[ $option_name ]['default'];
		}

		return $default;
	}

	/**
	 * Filters the value of an existing option.
	 *
	 * @since 0.6.0
	 *
	 * @param mixed  $value Value of the option. If stored serialized, it will be unserialized prior to being returned.
	 * @param string $option_name Option name.
	 * @return mixed
	 */
	public function sanitize_options_output( $value, $option_name ) {

		$callback = self::OPTIONS[ $option_name ]['sanitize_callback'];
		if ( ! is_null( $callback ) ) {
			$callback = self::OPTIONS[ $option_name ]['sanitize_callback'];
		}

		if ( is_callable( $callback ) ) {
			return call_user_func( self::OPTIONS[ $option_name ]['sanitize_callback'], $value );
		} else {
			return $value;
		}
	}

	/**
	 * Sanitize the value to add in the `wp_chimp_lists_db_upgraded` option.
	 *
	 * @since 0.5.0
	 *
	 * @param array $value The value to sanitize.
	 * @return array The value sanitized.
	 */
	public static function sanitize_lists_db_upgraded( array $value ) {

		if ( ! isset( $value['upgraded'] ) || ! isset( $value['version'] ) ) {
			return [];
		}

		return [
			'upgraded' => (bool) $value['upgraded'],
			'version' => absint( $value['version'] ),
		];
	}

	/**
	 * Sanitize API key status output.
	 *
	 * @since 0.5.0
	 *
	 * @param string $value The API key status. It should be 'valid' or 'invalid'.
	 * @return string The API key status 'valid' or 'invalid'
	 */
	public static function sanitize_api_key_status( $value ) {

		if ( in_array( $value, [ 'valid', 'invalid' ], true ) ) {
			return $value;
		}

		return 'invalid';
	}
}
