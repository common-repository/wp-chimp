<?php
/**
 * Endpoints: REST_Sync_Lists_Controller class
 *
 * @package WP_Chimp\Core\Endpoints
 * @since 0.1.0
 */

namespace WP_Chimp\Core\Endpoints;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

use function WP_Chimp\Core\get_the_lists_per_page;
use function WP_Chimp\Core\get_the_lists_total_items;
use function WP_Chimp\Core\request_lists;
use function WP_Chimp\Core\set_the_default_list;
use function WP_Chimp\Core\sort_lists;
use WP_Chimp\Core\Endpoints\REST_Controller;
use WP_REST_Server;

/**
 * Class to register the custom '/sync' endpoint to WP-API.
 *
 * @since 0.1.0
 * @since 0.3.0 Extends the REST_Controller class.
 */
class REST_Sync_Lists_Controller extends REST_Controller {

	/**
	 * The Constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		parent::__construct();
		$this->rest_base = self::get_rest_base();
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 0.3.0
	 */
	public function run() {
		$this->get_hooks()->add_action( 'rest_api_init', $this, 'register_routes' ); // Register `/lists/sync` endpoint.
	}

	/**
	 * Define and retrieve the base of this controller's route.
	 *
	 * @inheritDoc
	 * @return string
	 */
	public static function get_rest_base() {
		return 'sync';
	}

	/**
	 * Registers a custom REST API route.
	 *
	 * @since 0.1.0
	 */
	public function register_routes() {

		/**
		 * Register the '/sync/lists' route to retrieve a collection of MailChimp list.
		 *
		 * @uses WP_REST_Server
		 */
		register_rest_route(
			$this->namespace,
			$this->rest_base . '/lists',
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
					'args' => $this->get_collection_params(),
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);
	}

	/**
	 * Get the query params for collections.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public function get_collection_params() {

		return [
			'page' => [
				'description' => __( 'Current page of the collection.', 'wp-chimp' ),
				'type' => 'integer',
				'sanitize_callback' => 'absint',
				'default' => 1,
			],
			'per_page' => [
				'description' => __( 'Maximum number of items to be returned in result set.', 'wp-chimp' ),
				'type' => 'integer',
				'sanitize_callback' => 'absint',
				'default' => get_the_lists_per_page(),
			],
		];
	}

	/**
	 * Retrieves the list's schema, conforming to JSON Schema.
	 *
	 * @since 0.1.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {

		return [
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'title' => __( 'MailChimp List', 'wp-chimp' ),
			'type' => 'object',
			'properties' => [
				'web_id' => [
					'description' => __( 'The ID used in the MailChimp web application.', 'wp-chimp' ),
					'type' => 'integer',
					'readonly' => true,
				],
				'list_id' => [
					'description' => __( 'A string that uniquely identifies this list.', 'wp-chimp' ),
					'type' => 'string',
					'readonly' => true,
				],
				'name' => [
					'description' => __( 'The name of the list.', 'wp-chimp' ),
					'type' => 'string',
					'readonly' => true,
				],
				'stats' => [
					'description' => __( 'Stats for the list.', 'wp-chimp' ),
					'type' => 'object',
					'readonly' => true,
				],
				'double_optin' => [
					'description' => __( 'Whether or not to require the subscriber to confirm subscription.', 'wp-chimp' ),
					'type' => 'integer',
					'default' => false,
					'enum' => [ true, false ],
					'readonly' => true,
				],
				'marketing_permissions' => [
					'description' => __( 'Whether or not the list has marketing permissions (eg. GDPR) enabled.', 'wp-chimp' ),
					'type' => 'integer',
					'default' => false,
					'enum' => [ true, false ],
					'readonly' => true,
				],
			],
		];
	}

	/**
	 * Check if a given request has access to get a specific item.
	 *
	 * @since  0.1.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool
	 */
	public function get_items_permissions_check( $request ) {

		$nonce = $request->get_header( 'X-WP-Nonce' );
		return wp_verify_nonce( $nonce, 'wp_rest' ) && current_user_can( 'manage_options' );
	}

	/**
	 * Check if a given request has access to get a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_item_permissions_check( $request ) {
		return $this->get_items_permissions_check( $request );
	}

	/**
	 * Function to return the response from '/lists' endpoints.
	 *
	 * @since  0.1.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response Response object.
	 */
	public function get_items( $request ) {

		$lists = [];
		$items = [];

		$page_num = absint( $request->get_param( 'page' ) );
		$per_page = absint( $request->get_param( 'per_page' ) );

		$lists = $this->get_lists(
			[
				'page' => $page_num,
				'per_page' => $per_page,
				'offset' => self::get_lists_offset( $page_num, $per_page ),
			]
		);

		$total_items = self::get_lists_total_items();
		$total_pages = self::get_lists_total_pages( $per_page );

		foreach ( $lists as $key => $list ) {
			$data = $this->prepare_item_for_response( $list, $request );
			$items[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $items );

		if ( $page_num ) {
			$response->header( 'X-WP-Chimp-Lists-Page', $page_num );
		}

		if ( $total_items ) {
			$response->header( 'X-WP-Chimp-Lists-Total', $total_items );
		}

		if ( $total_pages ) {
			$response->header( 'X-WP-Chimp-Lists-TotalPages', $total_pages );
		}

		return $response;
	}

	/**
	 * Prepares a single MailChimp list output for response.
	 *
	 * @since 0.1.0
	 *
	 * @param array           $item The detail of a MailChimp list (e.g. list_id, name, etc.).
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $item, $request ) {

		$data = [];
		$schema = $this->get_item_schema();
		$props = $schema['properties'];

		if ( isset( $props['web_id'] ) && isset( $item['web_id'] ) ) {
			$data['web_id'] = absint( $item['web_id'] );
		}

		if ( isset( $props['list_id'] ) && isset( $item['list_id'] ) ) {
			$data['list_id'] = sanitize_key( $item['list_id'] );
		}

		if ( isset( $props['name'] ) && isset( $item['name'] ) ) {
			$data['name'] = sanitize_text_field( $item['name'] );
		}

		if ( isset( $props['stats'] ) && isset( $item['stats'] ) ) {
			$data['stats'] = maybe_unserialize( $item['stats'] );
		}

		if ( isset( $props['double_optin'] ) && isset( $item['double_optin'] ) ) {

			$optin = 1 === absint( $item['double_optin'] );
			$enum = $props['double_optin']['enum'];
			$default = $props['double_optin']['default'];

			$data['double_optin'] = in_array( $optin, $enum, true ) ? $optin : $default;
		}

		if ( isset( $props['marketing_permissions'] ) && isset( $item['marketing_permissions'] ) ) {

			$optin = 1 === absint( $item['marketing_permissions'] );
			$enum = $props['double_optin']['enum'];
			$default = $props['marketing_permissions']['default'];

			$data['marketing_permissions'] = in_array( $optin, $enum, true ) ? $optin : $default;
		}

		return rest_ensure_response( $data ); // Wrap the data in a response object.
	}

	/**
	 * Function to get call the API to get list from MailChimp.
	 *
	 * @since 0.1.0
	 *
	 * @param array $args The arguments passed in the Endpoint query strings.
	 * @return mixed Return an object if it is successfully retrieved the MailChimp list,
	 *               or an empty array if not. It may also return an Exception if
	 *               the key, added is invalid.
	 */
	protected function get_lists( array $args ) {

		$lists = $this->get_remote_lists( $args );

		if ( is_wp_error( $lists ) || ! is_array( $lists ) ) {
			$lists = $this->get_local_lists( $args );
		}

		return $lists;
	}

	/**
	 * Function to get the list from MailChimp API.
	 *
	 * @since 0.1.0
	 *
	 * @param array $args The arguments passed in the Endpoint query strings.
	 * @return mixed Returns an object of the lists, or an Exception if the API key added
	 *               is invalid.
	 */
	protected function get_remote_lists( array $args = [] ) {

		$lists = [];
		$response = request_lists( $this->mailchimp );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$lists = sort_lists( $response );
		set_the_default_list( $lists );

		$this->get_lists_query()->truncate();     // Remove all entries from the `_chimp_lists` table.
		$this->get_lists_query()->delete_cache(); // Remove from the Object Caching.
		$this->process_lists( $lists );     // Add lists to the "Background Process".

		return self::slice_lists( (array) $lists, $args );
	}

	/**
	 * Retieve MailChimp Lists from the database.
	 *
	 * @since 0.4.0
	 *
	 * @param array $args The arguments passed in the Endpoint query strings.
	 * @return mixed Return an object if it is successfully retrieved the MailChimp list,
	 *               or an empty array if not. It may also return an Exception if
	 *               the key, added is invalid.
	 */
	protected function get_local_lists( array $args ) {
		return $this->get_lists_query()->query( $args );
	}

	/**
	 * Add Lists to the Background Process to add each of the Lists to the database.
	 *
	 * @since 0.1.0
	 *
	 * @param array $lists The Lists data retrieved from the MailChimp API response.
	 */
	protected function process_lists( array $lists ) {

		if ( 0 > count( $lists ) ) {
			return;
		}

		$last = end( $lists );
		foreach ( $lists as $key => $list ) {

			$list['date_synced'] = date( 'Y-m-d H:i:s' );
			$current = $this->get_lists_query()->get_by_the_id( $list['list_id'] );

			if ( isset( $current['list_id'] ) && $current['list_id'] === $list['list_id'] ) {
				$this->get_lists_query()->update( $list['list_id'], $list );
			} else {
				$this->get_lists_query()->insert( $list );
			}

			if ( $list['list_id'] === $last['list_id'] ) {

				$db_version = get_option( 'wp_chimp_lists_db_version' );

				update_option( 'wp_chimp_lists_init', $db_version, false );
				update_option( 'wp_chimp_lists_db_upgraded', [], false ); // We're done re-sync the Lists, remove DB upgrade information.
			}
		}

		return $lists;
	}

	/**
	 * Retrieve the Lists offset.
	 *
	 * Offset the result set by a specific number of items. Primarily used for determinating
	 * pagination on the Lists table in the Settings page.
	 *
	 * @since 0.1.0
	 *
	 * @param int $page_num The page number requested.
	 * @param int $per_page The page number requested.
	 * @return int The offset number of the given page requested.
	 */
	protected static function get_lists_offset( $page_num, $per_page ) {

		$offset = ( absint( $page_num ) - 1 ) * absint( $per_page );
		return absint( $offset );
	}

	/**
	 * Retrieve the total pages.
	 *
	 * The total number could be used for determinating the pagination of the Lists table
	 * in the Settings page.
	 *
	 * @since 0.1.0
	 *
	 * @param int $per_page Maximum number of items to be returned in result.
	 * @return int The number of pages.
	 */
	protected static function get_lists_total_pages( $per_page ) {

		$total_items = self::get_lists_total_items();
		$total_pages = ceil( $total_items / absint( $per_page ) );

		return absint( $total_pages );
	}

	/**
	 * Retrieve the number of items.
	 *
	 * The number is obtained from the MailChimp API response during the initialization,
	 * when the API key is first added.
	 *
	 * @since 0.1.0
	 * @see Admin\Page->updated_option();
	 *
	 * @return int The total items of the lists.
	 */
	protected static function get_lists_total_items() {

		$total_items = get_the_lists_total_items();
		return absint( $total_items );
	}

	/**
	 * Function to filter the lists output for WP-API response.
	 *
	 * Ensure that the output follows the parameter passsed in the endpoint
	 * query strings.
	 *
	 * @since 0.1.0
	 * @since 0.4.0 Renamed to slice_lists.
	 *
	 * @param array $lists The remote lists retrieved from MailChimp API.
	 * @param array $args The arguments passed in the endpoint query strings.
	 * @return array The filtered MailChimp lists.
	 */
	protected static function slice_lists( array $lists, array $args = [] ) {

		$args = wp_parse_args(
			$args,
			[
				'offset' => 0,
				'per_page' => self::get_lists_total_items(),
			]
		);

		return array_slice( $lists, $args['offset'], $args['per_page'] );
	}
}
