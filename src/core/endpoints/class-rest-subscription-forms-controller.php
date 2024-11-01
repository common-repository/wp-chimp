<?php
/**
 * Endpoints: REST_Subscrption_Form_Controller class
 *
 * @package WP_Chimp\Core\Endpoints
 * @since 0.1.0
 */

namespace WP_Chimp\Core\Endpoints;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

use WP_Chimp\Core\Endpoints\REST_Controller;
use WP_Chimp\Subscription_Form\Attributes;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Class to register the custom '/subscription-forms' endpoint to WP-API.
 *
 * @since 0.6.0
 */
class REST_Subscription_Forms_Controller extends REST_Controller {

	/**
	 * The Constructor.
	 *
	 * @since 0.6.0
	 */
	public function __construct() {
		parent::__construct();
		$this->rest_base = self::get_rest_base();
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 0.6.0
	 */
	public function run() {
		$this->get_hooks()->add_action( 'rest_api_init', $this, 'register_routes' ); // Register `/subscription-forms` endpoint.
	}

	/**
	 * Define and retrieve the base of this controller's route.
	 *
	 * @inheritDoc
	 * @return string
	 */
	public static function get_rest_base() {
		return 'subscription-forms';
	}

	/**
	 * Registers a custom REST API route.
	 *
	 * @since 0.6.0
	 */
	public function register_routes() {

		/**
		 * Register the '/subscription-form' route to retrieve the subscription options and configs.
		 *
		 * @uses WP_REST_Server
		 */
		register_rest_route(
			$this->namespace,
			$this->rest_base,
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);

		/**
		 * Register the '/subscription-forms' route to retrieve the subscription options and configs.
		 *
		 * @uses WP_REST_Server
		 */
		register_rest_route(
			$this->namespace,
			$this->rest_base . '/(?P<id>[\w-]+)',
			[
				'schema' => [ $this, 'get_public_item_schema' ],
				'args' => [
					'id' => [
						'description' => __( 'Unique identifier of the MailChimp List.', 'wp-chimp' ),
						'type' => 'string',
					],
					'context' => $this->get_context_param( [ 'default' => 'view' ] ),
				],
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'get_item' ],
					'permission_callback' => [ $this, 'get_item_permissions_check' ],
				],
				[
					'methods' => WP_REST_Server::EDITABLE,
					'callback' => [ $this, 'update_item' ],
					'permission_callback' => [ $this, 'update_item_permissions_check' ],
				],
			]
		);
	}

	/**
	 * Retrieves the Lists schema, conforming to JSON Schema.
	 *
	 * @since 0.6.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {

		return [
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'title' => __( 'Subscription Form', 'wp-chimp' ),
			'type' => 'object',
			'properties' => [
				'list_id' => [
					'description' => __( 'A string that uniquely identifies the list associated with the subscription form.', 'wp-chimp' ),
					'type' => 'string',
					'readonly' => true,
				],
				'name' => [
					'description' => __( 'The name of the list associated with the subscription form.', 'wp-chimp' ),
					'type' => 'string',
					'readonly' => true,
				],
				'double_optin' => [
					'description' => __( 'Whether or not to require the subscriber to confirm subscription.', 'wp-chimp' ),
					'type' => 'boolean',
					'default' => false,
					'readonly' => true,
				],
				'marketing_permissions' => [
					'description' => __( 'Whether or not the list has marketing permissions (eg. GDPR) enabled.', 'wp-chimp' ),
					'type' => 'integer',
					'default' => false,
					'readonly' => true,
				],
				'attributes' => [
					'description' => __( 'The subscription form attributes.', 'wp-chimp' ),
					'type' => 'object',
				],
			],
		];
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

		if ( isset( $props['list_id'] ) && isset( $item['list_id'] ) ) {
			$data['list_id'] = sanitize_key( $item['list_id'] );
		}

		if ( isset( $props['name'] ) && isset( $item['name'] ) ) {
			$data['name'] = sanitize_text_field( $item['name'] );
		}

		if ( isset( $props['double_optin'] ) && isset( $item['double_optin'] ) ) {

			$optin = 1 === absint( $item['double_optin'] );
			$data['double_optin'] = (bool) $optin;
		}

		if ( isset( $props['marketing_permissions'] ) && isset( $item['marketing_permissions'] ) ) {

			$optin = 1 === absint( $item['marketing_permissions'] );
			$data['marketing_permissions'] = (bool) $optin;
		}

		if ( isset( $props['attributes'] ) && isset( $data['list_id'] ) ) {
			$data['attributes'] = Attributes::get( $data['list_id'] );
		}

		$response = rest_ensure_response( $data );

		/**
		 * Filter the `/subscription-forms` API response.
		 *
		 * @since 0.6.0
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param array            $item The subscription form data item.
		 * @param WP_REST_Request  $request Request object.
		 */
		return apply_filters( 'wp_chimp_rest_prepare_subscription_forms', $response, $item, $request );
	}

	/**
	 * Prepare the item for create or update operation
	 *
	 * @since 0.7.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return array
	 */
	protected function prepare_item_for_database( $request ) {

		$body = $request->get_body();
		$attributes = json_decode( $body, true );
		if ( empty( $attributes ) ) {
			return [];
		}

		return Attributes::sanitize( $attributes );
	}

	/**
	 * Function to retrieve the response from '/subscription-forms' API endpoint.
	 *
	 * @since 0.6.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response Response object.
	 */
	public function get_items( $request ) {

		$lists = $this->get_lists();
		$items = [];
		foreach ( $lists as $list ) {
			$data = $this->prepare_item_for_response( $list, $request );
			$items[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $items );

		return $response;
	}

	/**
	 * Function to retrieve the response from '/subscription-forms/<id>' API endpoint.
	 *
	 * @since 0.6.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response Response object.
	 */
	public function get_item( $request ) {

		$response = [];

		$list_id = $request->get_param( 'id' );

		$list = $this->validate_list_id( $list_id, $request );
		if ( is_wp_error( $list ) ) {
			return $list;
		}

		$item = $this->prepare_item_for_response( $list, $request );
		$response = rest_ensure_response( $item );

		return $response;
	}

	/**
	 * Update the Subscription Form attributes.
	 *
	 * @since 0.7.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response Response object.
	 */
	public function update_item( $request ) {

		$response = [];

		$attributes = $this->prepare_item_for_database( $request );
		$params = $request->get_params();

		$list = $this->validate_list_id( $params['id'] );
		if ( is_array( $list ) ) {
			Attributes::update( $params['id'], $attributes );

			$list = array_merge( $list, [ 'attributes' => $attributes ] );
			$item = $this->prepare_item_for_response( $list, $request );
		}

		$response = rest_ensure_response( $item );

		return $response;
	}

	/**
	 * Retieve MailChimp Lists.
	 *
	 * @since 0.6.0
	 *
	 * @return mixed Return an object if it is successfully retrieved the MailChimp list,
	 *               or an empty array if not. It may also return an Exception if
	 *               the key, added is invalid.
	 */
	public function get_lists() {
		return $this->get_lists_query()->query();
	}

	/**
	 * Check if a given request has access to get a Subscription Form.
	 *
	 * @since 0.6.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool
	 */
	public function get_items_permissions_check( $request ) {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Check if a given request has access to get a Subscription Form item.
	 *
	 * @since 0.6.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool
	 */
	public function get_item_permissions_check( $request ) {
		return $this->get_items_permissions_check( $request );
	}

	/**
	 * Checks if a request has access when updating a Subscription Form.
	 *
	 * @since 0.6.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool
	 */
	public function update_item_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Retrieve the list and verify if it is present by the ID.
	 *
	 * @since 0.6.0
	 *
	 * @param string          $list_id The MailChimp List ID to validate.
	 * @param WP_REST_Request $request Full details about the request.
	 * @return array|WP_Error The List data in array or WP_Error if the list is empty or data is not an array.
	 */
	public function validate_list_id( $list_id, WP_REST_Request $request = null ) {

		$list = $this->get_lists_query()->get_by_the_id( $list_id );

		if ( ! is_array( $list ) || empty( $list ) ) {
			$message = Attributes::get_value( $list_id, 'text_notice_invalid_list_id' );

			return new WP_Error(
				'wp_chimp_rest_invalid_list_id',
				$message,
				[
					'status' => 404,
				]
			);
		}

		return $list;
	}
}
