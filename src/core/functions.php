<?php
/**
 * Plugin core functions
 *
 * @package WP_Chimp/Core
 * @since 0.1.0
 */

namespace WP_Chimp\Core;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

use WP_Chimp\Deps\DrewM\MailChimp\MailChimp;
use WP_Error;
use WP_REST_Request;

/**
 * Retrieve the MailChimp API key.
 *
 * @since 0.1.0
 *
 * @return string The MailChimp API key or an empty string.
 */
function get_the_mailchimp_api_key() {

	$options = get_option( 'wp_chimp_settings_mailchimp' );
	$api_key = isset( $options['api_key'] ) && is_string( $options['api_key'] ) ? $options['api_key'] : '';

	return $api_key;
}

/**
 * Function to retrieve the API key status.
 *
 * If MailChimp returns an error when fetching the data from the API,
 * this option is set to `invalid`.
 *
 * @since 0.1.0
 *
 * @return bool Returns `true` if the MailChimp API key is a valid key
 *              else `false`.
 */
function get_the_mailchimp_api_key_status() {

	$api_key = get_the_mailchimp_api_key();
	if ( empty( $api_key ) ) {
		return false;
	}

	$status = get_option( 'wp_chimp_api_key_status' );
	if ( 'invalid' === $status ) {
		return false;
	}

	return true;
}

/**
 * Retrieve the MailChimp Datacenter ID.
 *
 * @since 0.5.0
 *
 * @return string
 */
function get_the_mailchimp_dc() {

	$api_key = get_the_mailchimp_api_key();
	$dash_pos = strrpos( $api_key, '-' );

	return false === $dash_pos ? '' : substr( $api_key, $dash_pos + 1 );
}

/**
 * Retrieve the number of mail lists on the MailChimp account.
 *
 * The total items are retrieved from the MailChimp API response when
 * the API key is added or when the data is resynced.
 *
 * @since 0.1.0
 *
 * @return int The Lists total items.
 */
function get_the_lists_total_items() {

	$total_items = get_option( 'wp_chimp_lists_total_items' );
	return absint( $total_items );
}

/**
 * Set the default of the MailChimp lists.
 *
 * @since 0.2.0
 *
 * @param array $lists The MailChimp lists data.
 * @param array $index The index on the array in the MailChimp lists to set as the default.
 */
function set_the_default_list( array $lists, $index = 0 ) {

	if ( isset( $lists[ $index ] ) && isset( $lists[ $index ]['list_id'] ) ) {
		$default = sanitize_key( $lists[ $index ]['list_id'] );
		update_option( 'wp_chimp_default_list', $default, true );
	}
}

/**
 * Retrieve the default list from database.
 *
 * @since 0.2.0
 *
 * @return string The ID of the default list.
 */
function get_the_default_list() {
	return get_option( 'wp_chimp_default_list' );
}

/**
 * Check whether the lists are already installed to the database.
 *
 * @since 0.1.0
 *
 * @return bool
 */
function is_lists_init() {

	$lists_init = get_option( 'wp_chimp_lists_init' );
	$lists_init = is_int( $lists_init ) && 0 < $lists_init ? $lists_init : false;

	$db_version = get_option( 'wp_chimp_lists_db_version' );
	$db_version = is_int( $db_version ) && 0 < $lists_init ? $db_version : false;

	return (bool) $lists_init && $lists_init === $db_version;
}

/**
 * Check if it's the plugin setting page.
 *
 * @since 0.7.0
 *
 * @return bool
 */
function is_setting_page() {
	$screen = get_current_screen();
	return 'settings_page_wp-chimp' === $screen->id;
}

/**
 * Check if it's the admin page where the plugin will be loaded.
 *
 * @since 0.7.0
 *
 * @return bool
 */
function is_admin_page() {
	$screen = get_current_screen();
	return 'settings_page_wp-chimp' === $screen->id || 'widgets' === $screen->id || 'customize' === $screen->id;
}

/**
 * Retrieve the
 *
 * @since 0.7.0
 *
 * @return string
 */
function get_the_script_suffix() {
	return defined( 'SCRIPT_DEBUG' ) && false === SCRIPT_DEBUG ? '' : '.min';
}

/**
 * Define the number of Lists to show on the table in the plugin Settings page.
 *
 * TODO: Make this adjustable via a Filter Hooks.
 *
 * @since  0.6.0
 *
 * @return integer
 */
function get_the_lists_per_page() {
	return 10;
}

/**
 * Retrieve the WP-Chimp REST API base/namespace.
 *
 * @since 0.1.0
 *
 * @return string The WP-Chimp REST API endpont base URL.
 */
function get_the_rest_api_namespace() {
	return Endpoints\REST_Controller::get_namespace();
}

/**
 * Retrieve the WP-Chimp REST API base URL.
 *
 * @since 0.1.0
 *
 * @return string The full URL of the WP-Chimp REST API endpoint.
 */
function get_the_rest_api_url() {
	return rest_url( get_the_rest_api_namespace() );
}

/**
 * Convert string from snake_case to camelCase
 *
 * @since 0.1.0
 *
 * @param string $string The string to convert in camelCase format.
 * @return string The converted string in snake_case
 */
function from_snake_to_camel( $string ) {
	return lcfirst( implode( '', array_map( 'ucfirst', explode( '_', $string ) ) ) );
}

/**
 * Convert string from camelCase to snake_case
 *
 * @since 0.1.0
 *
 * @param string $string The string to convert in camelCase format.
 * @return string The converted string in snake_case
 */
function from_camel_to_snake( $string ) {
	return strtolower( preg_replace( [ '/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/' ], '$1_$2', $string ) );
}

/**
 * Function to transform the array keys to camelCase.
 *
 * This function will be used to convert associative array that
 * will be used in JavaScript.
 *
 * @since 0.1.0
 *
 * @param  array $inputs Associative array.
 * @return array Associative array with the key converted to camelcase
 */
function convert_keys_to_camel_case( array $inputs ) {

	$inputs_converted = [];
	foreach ( $inputs as $key => $input ) {
		$key = from_snake_to_camel( $key );
		if ( is_array( $input ) ) {
			$input = convert_keys_to_camel_case( $input );
		}
		$inputs_converted[ $key ] = $input;
	}

	return $inputs_converted;
}

/**
 * Function to transform the array keys to snake_case.
 *
 * This function will be used to convert associative array that
 * will be used in PHP.
 *
 * @since 0.1.0
 *
 * @param  array $inputs Associative array.
 * @return array Associative array with the key converted to camelcase
 */
function convert_keys_to_snake_case( array $inputs ) {

	$inputs_converted = [];
	foreach ( $inputs as $key => $input ) {
		$key = from_camel_to_snake( $key );
		if ( is_array( $input ) ) {
			$input = convert_keys_to_snake_case( $input );
		}
		$inputs_converted[ $key ] = $input;
	}

	return $inputs_converted;
}

/**
 * Obfuscate half of a string.
 *
 * @since 0.1.0
 *
 * @param string $string The API key string.
 * @return string The obfuscated API key
 */
function obfuscate_string( $string = '' ) {

	$obfuscated_api_key = '';

	if ( is_string( $string ) && ! empty( $string ) ) {

		$api_key_length = strlen( $string );
		$obfuscated_length = ceil( $api_key_length / 2 );
		$obfuscated_api_key = str_repeat( '*', $obfuscated_length ) . substr( $string, $obfuscated_length );
	}

	return $obfuscated_api_key;
}

/**
 * Request "Lists" from MailChimp API.
 *
 * @since 0.5.0
 *
 * @param MailChimp $mailchimp The MailChimp class instance.
 * @return array
 */
function request_lists( MailChimp $mailchimp ) {

	$total_items = get_the_lists_total_items();
	$response = $mailchimp->get(
		'lists',
		[
			'count' => absint( $total_items ),
		]
	);

	if ( ! $mailchimp->success() ) {
		return new WP_Error(
			'wp_chimp_request_list_failed',
			__( 'Request to MailChimp API has failed.', 'wp-chimp' ),
			[
				'response' => $mailchimp->getLastResponse(),
			]
		);
	}

	if ( ! isset( $response['lists'] ) ) {
		return new WP_Error(
			'wp_chimp_request_list_invalid_response',
			__( 'MailChimp API returned an invalid response.', 'wp-chimp' ),
			[
				'response' => $response,
			]
		);
	}

	return $response['lists'];
}

/**
 * Function perform a GET request to the plugin custom API endpoint.
 *
 * @since 0.6.0
 *
 * @param string $endpoint The plugin API endpoint.
 * @return array
 */
function rest_get_request( $endpoint ) {

	static $data;

	if ( ! is_string( $endpoint ) || empty( $endpoint ) ) {
		return new WP_Error( 'wp_chimp_endpoint_invalid', __( 'The endpoint argument must be a string and not empty.', 'wp-chimp' ), $endpoint );
	}

	if ( is_null( $data ) ) {
		$request = new WP_REST_Request( 'GET', "/wp-chimp/v1/{$endpoint}" );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );

		$response = rest_do_request( $request );
		$data = 200 === $response->get_status() ? $response->get_data() : [];
	}

	return $data;
}

/**
 * Function to sort out MailChimp API.
 *
 * The function will select select few data out of the
 * MailChimp API response.
 *
 * @since  0.5.0
 *
 * @param  array $raw_lists The MailChimp API response.
 * @return array
 */
function sort_lists( array $raw_lists = [] ) {

	$sorted_lists = [];
	$manage_advanced = get_option( 'wp_chimp_settings_advanced_lists' );
	$detailed_stats = isset( $manage_advanced['detailed_stats'] ) ? $manage_advanced['detailed_stats'] : false;

	foreach ( $raw_lists as $key => $list ) {

		$web_id  = isset( $list['web_id'] ) ? $list['web_id'] : null;
		$list_id = isset( $list['id'] ) ? $list['id'] : null;

		if ( $list_id && $web_id ) {

			$list_name = isset( $list['name'] ) ? $list['name'] : '';

			if ( 'on' === $detailed_stats ) {
				$list_stats = isset( $list['stats'] ) ? $list['stats'] : [];
			} else {
				$list_stats = isset( $list['stats'] ) ? [
					'member_count' => isset( $list['stats']['member_count'] ) ? $list['stats']['member_count'] : 0,
				] : [];
			}

			$list_optin = isset( $list['double_optin'] ) ? $list['double_optin'] : 0;
			$marketing_permissions = isset( $list['marketing_permissions'] ) ? $list['marketing_permissions'] : 0;
			$merge_fields = isset( $list['merge_fields'] ) && is_array( $list['merge_fields'] ) ? $list['merge_fields'] : [];
			$interest_categories = isset( $list['interest_categories'] ) && is_array( $list['interest_categories'] ) ? $list['interest_categories'] : [];
			$date_created = isset( $list['date_created'] ) ? date( 'Y-m-d H:i:s', strtotime( $list['date_created'] ) ) : '';

			/**
			 * Arrange the entry to add in the `_chimp_lists` table.
			 * The `interest_categories` and the `merge_fields` categories value
			 * will be added using a separate API endpoint.
			 *
			 * @var array
			 */
			$sorted_lists[ $key ] = [
				'web_id' => $web_id,
				'list_id' => $list_id,
				'name' => $list_name,
				'stats' => $list_stats,
				'double_optin' => true === (bool) $list_optin ? 1 : 0,
				'marketing_permissions' => true === (bool) $marketing_permissions ? 1 : 0,
				'merge_fields' => $merge_fields,
				'interest_categories' => $interest_categories,
				'date_created' => $date_created,
			];
		}
	}

	return $sorted_lists;
}
