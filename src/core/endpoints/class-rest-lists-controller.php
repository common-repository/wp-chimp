<?php
/**
 * Endpoints: Lists_Controller class
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
use WP_Chimp\Core\Endpoints\REST_Controller;
use WP_Chimp\Deps\DrewM\MailChimp\MailChimp;
use WP_Chimp\Subscription_Form\Attributes;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Class to register the custom '/lists' endpoint to WP-API.
 *
 * @since 0.1.0
 * @since 0.3.0 Extends the REST_Controller class.
 */
class REST_Lists_Controller extends REST_Controller {

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
		$this->get_hooks()->add_action( 'rest_api_init', $this, 'register_routes' ); // Register `/lists` endpoint.
	}

	/**
	 * Define and retrieve the base of this controller's route.
	 *
	 * @inheritDoc
	 * @return string
	 */
	public static function get_rest_base() {
		return 'lists';
	}

	/**
	 * Registers a custom REST API route.
	 *
	 * @since 0.1.0
	 */
	public function register_routes() {

		/**
		 * Register the '/lists' route to retrieve a collection of MailChimp list.
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
					'args' => $this->get_collection_params(),
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);

		/**
		 * Register the '/lists' route to retrieve a single MailChimp list with their ID.
		 *
		 * @uses WP_REST_Server
		 */
		register_rest_route(
			$this->namespace,
			$this->rest_base . '/(?P<id>[\w-]+)',
			[
				'args' => [
					'id' => [
						'description' => __( 'Unique identifier of the MailChimp List.', 'wp-chimp' ),
						'type' => 'string',
					],
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
					'args' => [
						'email' => [
							'description' => __( 'Email address to add to the MailChimp List.', 'wp-chimp' ),
							'required' => true,
							'type' => 'string',
						],
					],
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
	 * Retrieves the Lists schema, conforming to JSON Schema.
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
		return current_user_can( 'manage_options' );
	}

	/**
	 * Check if a given request has access to get a specific item.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_item_permissions_check( $request ) {
		return $this->get_items_permissions_check( $request );
	}

	/**
	 * Checks if a request has access when updating a Subscription Form.
	 *
	 * @since 0.7.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool
	 */
	public function update_item_permissions_check( $request ) {
		return true;
	}

	/**
	 * Function to return the response from '/lists' endpoints.
	 *
	 * @since  0.1.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response Response object.
	 */
	public function get_items( $request ) {

		$lists = [];
		$items = [];

		$page_num = absint( $request->get_param( 'page' ) );
		$per_page = absint( $request->get_param( 'per_page' ) );
		$per_page = 0 < $per_page ? $per_page : self::get_lists_per_page(); // Ensure fallback to the default per-page.

		$lists = $this->get_lists(
			[
				'page' => $page_num,
				'per_page' => $per_page,
				'offset' => self::get_lists_offset( $page_num, $per_page ),
			]
		);

		$total_items = self::get_lists_total_items();
		$total_pages = self::get_lists_total_pages( $total_items, $per_page );

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
	 * Function to return the response from '/list' API endpoint.
	 *
	 * @since  0.1.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response Response object.
	 */
	public function get_item( $request ) {

		$id = $request->get_param( 'id' );
		$list = $this->validate_list_by_id( $id );

		if ( is_wp_error( $list ) ) {
			return $list;
		}

		$item = $this->prepare_item_for_response( $list, $request );

		return rest_ensure_response( $item );
	}

	/**
	 * Function to retrieve a response after updating '/subscription-forms/<id>' API endpoint.
	 *
	 * @since 0.6.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response Response object.
	 */
	public function update_item( $request ) {

		$list_id = $request->get_param( 'id' );
		$email = $request->get_param( 'email' );

		$response = [
			'status' => 'unknown',
			'notice' => [
				'type' => 'error',
				'message' => Attributes::get_value( $list_id, 'text_notice_generic_error' ),
			],
		];

		$email = $this->validate_email( $email, $request );
		if ( is_wp_error( $email ) ) {
			return $email;
		}

		$list = $this->validate_list_id( $list_id, $request );
		if ( is_wp_error( $list ) ) {
			return $list;
		}

		if ( $this->mailchimp instanceof MailChimp ) {

			$optin_status = $this->get_optin_status( $list_id );
			$subscription = $this->mailchimp->post(
				"lists/{$list_id}/members",
				[
					'email_address' => $email,
					'status' => $optin_status,
				]
			);

			if ( isset( $subscription['status'] ) ) {
				$response['status'] = $subscription['status'];
			}

			/**
			 * MailChimp returns an error response for email that's already been subscribed to
			 * the list. Let's it as a success.
			 */
			if ( isset( $subscription['title'] ) && 'Member Exists' === $subscription['title'] ) {
				$response['status'] = 'member-exists';
			}

			// MailChimp returns an error response for email that's already been deleted.
			if ( isset( $subscription['title'] ) && 'Forgotten Email Not Subscribed' === $subscription['title'] ) {
				$response['status'] = 'forgotten-email';
			}

			// Determin the message notice following the code status.
			if ( isset( $response['status'] ) ) {
				switch ( $response['status'] ) {
					case 'subscribed':
						$response['notice'] = [
							'type' => 'success',
							'message' => Attributes::get_value( $list_id, 'text_notice_subscribed' ),
						];
						break;

					case 'member-exists':
						$response['notice'] = [
							'type' => 'success',
							'message' => Attributes::get_value( $list_id, 'text_notice_member_exists' ),
						];
						break;

					case 'pending':
						$response['notice'] = [
							'type' => 'info',
							'message' => Attributes::get_value( $list_id, 'text_notice_pending' ),
						];
						break;

					case 'forgotten-email':
						$response['notice'] = [
							'type' => 'warning',
							'message' => Attributes::get_value( $list_id, 'text_notice_pending' ),
						];
						break;
				}
			}
		}

		return rest_ensure_response( $response );
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
			$data['double_optin'] = (bool) $optin;
		}

		if ( isset( $props['marketing_permissions'] ) && isset( $item['marketing_permissions'] ) ) {

			$optin = 1 === absint( $item['marketing_permissions'] );
			$data['marketing_permissions'] = (bool) $optin;
		}

		return rest_ensure_response( $data ); // Wrap the data in a response object.
	}

	/**
	 * Retieve MailChimp Lists.
	 *
	 * @since 0.1.0
	 *
	 * @param array $args The arguments passed in the Endpoint query strings.
	 * @return mixed Return an object if it is successfully retrieved the MailChimp list,
	 *               or an empty array if not. It may also return an Exception if
	 *               the key, added is invalid.
	 */
	public function get_lists( array $args ) {
		return $this->get_lists_query()->query( $args );
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
	public static function get_lists_offset( $page_num, $per_page ) {

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
	 * @param int $total_items The total items are there to display.
	 * @param int $per_page Maximum number of items to be displayed.
	 * @return int The number of pages.
	 */
	public static function get_lists_total_pages( $total_items = 0, $per_page = 10 ) {

		$total_pages = 0 >= $total_items ? 1 : ceil( $total_items / absint( $per_page ) );
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
	public static function get_lists_total_items() {

		$total_items = get_the_lists_total_items();
		return absint( $total_items );
	}

	/**
	 * Retrieve the subscription status for the subscriber.
	 *
	 * @since 0.6.0
	 * @link https://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/
	 *
	 * @param string $list_id A MailChimp List ID.
	 * @return string Returns `pending` if the Double Optin option is enabled on the list,
	 *                otherwise returns `subscribed`.
	 */
	public function get_optin_status( $list_id ) {

		$list = $this->get_lists_query()->get_by_the_id( $list_id );
		return isset( $list['double_optin'] ) && 1 === absint( $list['double_optin'] ) ? 'pending' : 'subscribed';
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
				isset( $message ) ? $message : __( 'Unknown error messsage.', 'wp-chimp' ),
				[
					'status' => 404,
				]
			);
		}

		return $list;
	}

	/**
	 * Validate email format.
	 *
	 * @since 0.6.0
	 *
	 * @param string          $email The email address to validate.
	 * @param WP_REST_Request $request Full details about the request.
	 * @return string|WP_Error The email address or WP_Error if the email address format is invalid.
	 */
	public function validate_email( $email, WP_REST_Request $request = null ) {

		if ( ! is_email( $email ) ) {
			$list_id = $request->get_param( 'id' );
			$message = Attributes::get_value( $list_id, 'text_notice_invalid_email' );

			return new WP_Error(
				'wp_chimp_rest_list_invalid_email',
				isset( $message ) ? $message : __( 'Unknown error messsage.', 'wp-chimp' ),
				[
					'status' => 400,
					'email' => $email,
				]
			);
		}

		return $email;
	}
}
