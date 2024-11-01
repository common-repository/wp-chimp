<?php
/**
 * Core: Query trait
 *
 * @package WP_Chimp\Core
 * @since 0.7.0
 */

namespace WP_Chimp\Core\Lists;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

/**
 * Trait that provides interface to inject WP_Chimp\Core\Lists\Query instance.
 *
 * @since 0.7.0
 *
 * @property WP_Chimp\Core\Lists\Query $lists_query
 */
trait Query_Trait {

	/**
	 * The Query instance
	 *
	 * Used to interact with the {$prefix}chimp_lists table,
	 * such as inserting a new row or updating the existing rows.
	 *
	 * @since 0.7.0
	 * @var WP_Chimp\Core\Lists\Query;
	 */
	private $lists_query;

	/**
	 * Register the Query instance
	 *
	 * @since 0.7.0
	 *
	 * @param Lists\Query $query The List\Query instance to retrieve the lists from the database.
	 */
	public function set_lists_query( Query $query ) {
		$this->lists_query = $query;
	}

	/**
	 * Retrieve the Query instance
	 *
	 * @since 0.7.0
	 */
	public function get_lists_query() {
		return $this->lists_query;
	}
}
