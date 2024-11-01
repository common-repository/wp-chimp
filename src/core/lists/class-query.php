<?php
/**
 * Lists: Query class
 *
 * The file that defines the class and the methods to query
 * *_chimp_lists table.
 *
 * @package WP_Chimp\Core\Lists
 * @since 0.1.0
 */

namespace WP_Chimp\Core\Lists;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

use function WP_Chimp\Core\get_the_lists_total_items;
use WP_Error;

/**
 * The class to query the *_chimp_lists table
 *
 * @since 0.1.0
 *
 * @property array $default_attrs
 */
class Query {

	/**
	 * The cache key to save the lists in the Object Caching.
	 *
	 * @since 0.3.0
	 */
	const CACHE_KEY = 'lists';

	/**
	 * The cache group.
	 *
	 * @since 0.3.0
	 */
	const CACHE_GROUP = 'wp_chimp_lists';

	/**
	 * The columns and its value.
	 *
	 * @since 0.6.0
	 * @var array
	 */
	const DEFAULT_DATA = [
		'web_id' => 0,
		'list_id' => '',
		'name' => '',
		'stats' => [],
		'double_optin' => 0,
		'marketing_permissions' => 0,
		'merge_fields' => [],
		'interest_categories' => [],
		'date_created' => '0000-00-00 00:00:00',
		'date_synced' => '0000-00-00 00:00:00',
	];

	/**
	 * Function to get all MailChimp list from the table.
	 *
	 * @since 0.1.0
	 *
	 * @param array $args {
	 *      The query arguments.
	 *
	 *      @type integer $per_page  The number of lists to retrieve.
	 *      @type integer $offset The number of lists to displace or pass over.
	 * }
	 * @return array An associative array of the MailChimp list ID.
	 *               Or, an empty array if the table is empty.
	 */
	public function query( array $args = [] ) {
		global $wpdb;

		$query = [];
		$args = wp_parse_args(
			$args,
			[
				'per_page' => get_the_lists_total_items(),
				'offset' => 0,
			]
		);

		// Check cache first.
		$lists = self::get_cache();

		if ( empty( $lists ) ) {

			$lists = $wpdb->get_results(
				"
				SELECT web_id, list_id, name, stats, double_optin, marketing_permissions, merge_fields, interest_categories, date_created
				FROM $wpdb->chimp_lists
			",
				ARRAY_A
			);

			if ( ! empty( $lists ) && ! is_wp_error( $lists ) ) {
				wp_cache_set( self::CACHE_KEY, $lists, 'wp_chimp_lists' );
			}
		}

		foreach ( $lists as $key => $list ) {
			$lists[ $key ] = self::sanitize_data_output( $list );
		}

		$query = array_slice( $lists, $args['offset'], $args['per_page'] );

		return $query;
	}

	/**
	 * Function to return only the IDs of the list.
	 *
	 * @since  0.1.0
	 * @access public
	 *
	 * @return array An associative array of the MailChimp list ID.
	 *               Or, an empty array if the table is empty.
	 */
	public function get_the_ids() {
		global $wpdb;

		// Check cache first.
		$lists = self::get_cache();
		$list_ids = [];

		if ( empty( $lists ) ) {

			$lists = $wpdb->get_results(
				"
				SELECT list_id
				FROM $wpdb->chimp_lists
			",
				ARRAY_A
			);
		}

		foreach ( $lists as $list ) {
			$list_ids[] = $list['list_id'];
		}

		return $list_ids;
	}

	/**
	 * Function to get the MailChimp list by the List ID.
	 *
	 * @since 0.1.0
	 *
	 * @param string $list_id The MailChimp list ID {@link https://kb.mailchimp.com/lists/manage-contacts/find-your-list-id}.
	 * @return array An associative array of the List from the database.
	 *               Or, an empty array if the list is not present,
	 *               with the $id is not present.
	 */
	public function get_by_the_id( $list_id = '' ) {
		global $wpdb;

		if ( empty( $list_id ) ) { // Return early if the $list_id is not supplied.
			return [];
		}

		// Check cache first.
		$lists = self::get_cache();

		if ( ! empty( $lists ) ) {

			foreach ( $lists as $data ) {
				if ( $data['list_id'] === $list_id ) {
					$list = $data;
				}
			}
		} else { // If the cache is empty, retrieve from the database.

			$list = $wpdb->get_row(
				$wpdb->prepare(
					"
				SELECT web_id, list_id, name, stats, double_optin, marketing_permissions, merge_fields, interest_categories, date_created
				FROM $wpdb->chimp_lists
				WHERE list_id = %s
			",
					[ $list_id ]
				),
				ARRAY_A
			);
		}

		return is_array( $list ) ? self::sanitize_data_output( $list ) : [];
	}

	/**
	 * Insert a new entry of MailChimp List to the table.
	 *
	 * @since  0.1.0
	 *
	 * @param  array $data The data to add into the table.
	 * @return int|bool|WP_Error Should return 1 the data has been success fully added.
	 *                           Otherwise, it should return false if an error
	 *                           occured, or WP_Error if it is failed to
	 *                           insert the data.
	 */
	public function insert( array $data ) {
		global $wpdb;

		if ( is_wp_error( self::is_columns_data_valid( $data ) ) ) {
			return self::is_columns_data_valid( $data );
		}

		$data = self::sanitize_columns( $data );
		$data = wp_parse_args( $data, self::DEFAULT_DATA );

		/**
		 * First let's check the 'list_id' existance. We'll need to be sure that
		 * the ID is a string, it is not an empty, and the row with the ID
		 * does not exist.
		 */
		$current_id = self::get_by_the_id( $data['list_id'] );

		if ( ! empty( $current_id ) ) {
			return new WP_Error( 'wp_chimp_list_id_exists', esc_html__( 'That MailChimp list ID already exists. Consider using the the update method to update the existing list.', 'wp-chimp' ), $current_id );
		}

		$inserted = $wpdb->insert(
			$wpdb->chimp_lists,
			self::sanitize_data_input( $data ),
			[
				'%d', // web_id.
				'%s', // list_id.
				'%s', // name.
				'%s', // stats.
				'%d', // double_optin.
				'%d', // marketing_permissions.
				'%s', // merge_fields.
				'%s', // interest_categories.
				'%s', // date_created.
				'%s', // date_synced.
			]
		);
		self::delete_cache();

		if ( false === $inserted ) {
			/* Translators: %s is the MailChimp list ID. */
			return new WP_Error( 'wp_chimp_insert_list_error', sprintf( esc_html__( 'Inserting the MailChimp list ID %s failed.', 'wp-chimp' ), $data['list_id'] ), $data );
		}

		return $inserted;
	}

	/**
	 * Update the existing entry in the MailChimp List table.
	 *
	 * @since 0.1.0
	 *
	 * @param string $list_id   The MailChimp list ID {@link https://kb.mailchimp.com/lists/manage-contacts/find-your-list-id}
	 *                     to be updated.
	 * @param array  $data An array of data to be updated to the $list_id.
	 * @return int|false Number of rows affected/selected or false on error
	 */
	public function update( $list_id = '', array $data ) {
		global $wpdb;

		if ( is_wp_error( self::is_columns_data_valid( $data ) ) ) {
			return self::is_columns_data_valid( $data );
		}

		$data = self::sanitize_columns( $data );
		$data = wp_parse_args( $data, self::DEFAULT_DATA );

		unset( $data['list_id'] ); // Remove the `list_id` from the updated column.

		$updated = $wpdb->update(
			$wpdb->chimp_lists,
			self::sanitize_data_input( $data ),
			[ 'list_id' => $list_id ],
			[
				'%d', // web_id.
				'%s', // name.
				'%s', // stats.
				'%d', // double_optin.
				'%d', // marketing_permissions.
				'%s', // merge_fields.
				'%s', // interest_categories.
				'%s', // date_created.
				'%s', // date_synced.
			],
			[
				'%s', // list_id.
			]
		);
		self::delete_cache();

		return $updated;
	}

	/**
	 * Function to delete a MailChimp list by the ID.
	 *
	 * @since  0.1.0
	 * @access public
	 *
	 * @param  string $id The MailChimp list ID {@link https://kb.mailchimp.com/lists/manage-contacts/find-your-list-id}.
	 * @return int|false The number of rows updated, or false on error.
	 */
	public function delete( $id = '' ) {
		global $wpdb;

		$deleted = $wpdb->delete(
			$wpdb->chimp_lists,
			[ 'list_id' => $id ],
			[ '%s' ]
		);
		self::delete_cache();

		return $deleted;
	}

	/**
	 * Function to empty the records in the `*_chimp_lists` table
	 *
	 * @since  0.1.0
	 * @access public
	 *
	 * @return int|false Number of rows affected/selected or false on error
	 */
	public function truncate() {
		global $wpdb;

		$emptied = $wpdb->query( "TRUNCATE TABLE $wpdb->chimp_lists" );
		self::delete_cache();

		/**
		 * ...For CREATE, ALTER, TRUNCATE and DROP SQL statements, (which affect
		 * whole tables instead of specific rows) this function returns
		 * TRUE on success...
		 *
		 * @link https://codex.wordpress.org/Class_Reference/wpdb#Running_General_Queries
		 */
		return $emptied;
	}

	/**
	 * Function to count the number of rows stored in the tables
	 *
	 * @since 0.1.0
	 *
	 * @return int The number of rows in the table
	 */
	public function count_rows() {
		global $wpdb;
		return absint( $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->chimp_lists" ) );
	}

	/**
	 * Function to check if the required column contains valid data
	 *
	 * @since  0.1.0
	 * @since  0.5.0 Renamed to `is_columns_data_valid` and add `web_id` check.
	 * @access public
	 *
	 * @param  array $data The data containing the columns to insert to the database.
	 * @return boolean
	 */
	public function is_columns_data_valid( array $data = [] ) {

		if ( empty( $data ) ) {
			return new WP_Error( 'wp_chimp_list_column_data_invalid', __( 'The column data could not be empty.', 'wp-chimp' ) );
		}

		if ( ! isset( $data['web_id'] ) || ! is_int( $data['web_id'] ) ) {
			// Translators: %s is the column name.
			return new WP_Error( 'wp_chimp_list_column_data_invalid', sprintf( __( 'The "%s" column data is invalid.', 'wp-chimp' ), 'web_id' ), $data );
		}

		/**
		 * Let's check the list_id existance. We'll need to be sure that
		 * the ID is a string, it is not an empty, and the row with the given list_id
		 * does not exist.
		 */
		if ( ! is_string( $data['list_id'] ) || empty( $data['list_id'] ) ) {
			// Translators: %s is the column name.
			return new WP_Error( 'wp_chimp_list_column_data_invalid', sprintf( __( 'The "%s" column is invalid.', 'wp-chimp' ), 'list_id' ), $data );
		}

		/**
		 * Do not insert the entry to the database if the MailChimp name is empty,
		 * or, if it is not the expected data type.
		 */
		if ( ! is_string( $data['name'] ) || empty( $data['name'] ) ) {
			// Translators: %s is the column name.
			return new WP_Error( 'wp_chimp_list_column_data_invalid', sprintf( __( 'The "%s" column is invalid.', 'wp-chimp' ), 'name' ), $data );
		}

		return true;
	}

	/**
	 * Function to filter-out array that should not to include be in the table
	 *
	 * @since 0.1.0
	 * @since 0.5.0 Turned to public method.
	 *
	 * @param  array $data List of columns and the values to add to the table.
	 * @return array       List of columns with the invalid columnes filtered-out
	 */
	public function sanitize_columns( array $data ) {

		$diffs = array_diff_key( $data, self::DEFAULT_DATA );
		foreach ( $diffs as $key => $diff ) {
			unset( $data[ $key ] );
		}

		return $data;
	}

	/**
	 * Function to sanitize values to input in the database.
	 *
	 * @since 0.5.0
	 *
	 * @param  array $data The unsanitize data.
	 * @return array Sanitized values.
	 */
	public static function sanitize_data_input( array $data ) {

		$sanitized_data = [];
		foreach ( $data as $key => $value ) {
			switch ( $key ) {
				case 'web_id':
				case 'double_optin':
				case 'marketing_permissions':
					$sanitized_data[ $key ] = absint( $value );
					break;
				case 'list_id':
					$sanitized_data[ $key ] = sanitize_key( $value );
					break;
				case 'name':
				case 'date_created':
				case 'date_synced':
					$sanitized_data[ $key ] = sanitize_text_field( $value );
					break;
				case 'stats':
				case 'merge_fields':
				case 'interest_categories':
					$value = is_array( $value ) ? $value : [];
					$sanitized_data[ $key ] = maybe_serialize( $value );
					break;
			}
		}

		return $sanitized_data;
	}

	/**
	 * Function to sanitize values to ouput from the database.
	 *
	 * @since 0.5.0
	 *
	 * @param  array $data The unsanitize data.
	 * @return array Sanitized values.
	 */
	public static function sanitize_data_output( array $data ) {

		$sanitized_data = [];
		foreach ( $data as $key => $value ) {
			switch ( $key ) {
				case 'web_id':
				case 'double_optin':
				case 'marketing_permissions':
					$sanitized_data[ $key ] = absint( $value );
					break;
				case 'list_id':
					$sanitized_data[ $key ] = sanitize_key( $value );
					break;
				case 'name':
				case 'date_created':
				case 'date_synced':
					$sanitized_data[ $key ] = sanitize_text_field( $value );
					break;
				case 'stats':
				case 'merge_fields':
				case 'interest_categories':
					$serialized = maybe_unserialize( is_string( $value ) ? $value : [] );
					$sanitized_data[ $key ] = is_array( $serialized ) ? $serialized : [];
					break;
			}
		}

		return $sanitized_data;
	}

	/**
	 * Retrieve the lists from the Object Caching.
	 *
	 * @since 0.3.0
	 *
	 * @return array The lists from the cache.
	 */
	public static function get_cache() {

		$cache = wp_cache_get( self::CACHE_KEY, self::CACHE_GROUP );
		return ! is_array( $cache ) ? [] : $cache;
	}

	/**
	 * Delete the lists from the Object Caching.
	 *
	 * @since 0.3.0
	 *
	 * @return bool true on successful removal, false on failure.
	 */
	public static function delete_cache() {
		return wp_cache_delete( self::CACHE_KEY, self::CACHE_GROUP );
	}

	/**
	 * Add the lists from the Object Caching.
	 *
	 * @since 0.3.0
	 *
	 * @param mixed $value The value to cache.
	 * @return bool true on successful removal, false on failure.
	 */
	public static function add_cache( $value = [] ) {
		return wp_cache_add( self::CACHE_KEY, $value, self::CACHE_GROUP );
	}

	/**
	 * Set the lists from the Object Caching.
	 *
	 * @since 0.3.0
	 *
	 * @param mixed $value The value to cache.
	 * @return bool true on successful removal, false on failure.
	 */
	public static function set_cache( $value = [] ) {
		return wp_cache_set( self::CACHE_KEY, $value, self::CACHE_GROUP );
	}
}
