<?php
/**
 * Endpoints: REST_Ping_Controller class
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
use WP_Chimp\Deps\DrewM\MailChimp\MailChimp;
use WP_REST_Server;

/**
 * Class to register the custom '/ping' endpoint to WP-API.
 *
 * @since 0.4.0
 */
class REST_Ping_Controller extends REST_Controller {

	/**
	 * Undocumented variable
	 *
	 * @var array
	 */
	protected $status = [
		'received',
		'loss',
	];

	/**
	 * The Constructor.
	 *
	 * @since 0.4.0
	 */
	public function __construct() {
		parent::__construct();
		$this->rest_base = self::get_rest_base();
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 0.4.0
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
		return 'ping';
	}

	/**
	 * Registers a custom REST API route.
	 *
	 * @since 0.4.0
	 */
	public function register_routes() {

		/**
		 * Register the '/ping' route to check API health status.
		 *
		 * @uses WP_REST_Server
		 */
		register_rest_route(
			$this->namespace,
			$this->rest_base,
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'get_item' ],
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);
	}

	/**
	 * Get an item for /ping route.
	 *
	 * @since  0.4.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response Response object.
	 */
	public function get_item( $request ) {

		$ping = $this->ping( $request );
		$data = $this->prepare_item_for_response( $ping, $request );

		return rest_ensure_response( $data );
	}

	/**
	 * Check MailChimp APi health status.
	 *
	 * @since 0.4.0
	 *
	 * @return array The ping response.
	 */
	protected function ping() {

		$status = [
			'connected' => false,
			'title' => __( 'Unable to connect to MailChimp. Your API key may be missing, invalid, or formatted improperly.', 'wp-chimp' ),
		];

		if ( $this->mailchimp instanceof MailChimp ) {

			/**
			 * Call the MailChimp health check API.
			 *
			 * @see https://developer.mailchimp.com/documentation/mailchimp/reference/ping/
			 */
			$response = $this->mailchimp->get( 'ping' );

			if ( $this->mailchimp->success() && isset( $response['health_status'] ) ) {

				$status = [
					'connected' => true,
					'title' => __( 'Connected to MailChimp.', 'wp-chimp' ),
				];
			} else {

				$status = [
					'connected' => false,
					'title' => __( 'Unable to connect to MailChimp.', 'wp-chimp' ),
				];
			}
		}

		return $status;
	}

	/**
	 * Check if a given request has access to get a specific item.
	 *
	 * @since 0.4.0
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
	 * @since 0.4.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_item_permissions_check( $request ) {
		return $this->get_items_permissions_check( $request );
	}

	/**
	 * Retrieves the route schema.
	 *
	 * @since 0.4.0
	 *
	 * @return array Schema data.
	 */
	public function get_item_schema() {

		return [
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'title' => __( 'Check MailChimp API Status', 'wp-chimp' ),
			'type' => 'object',
			'properties' => [
				'connected' => [
					'description' => __( 'The API connection status.', 'wp-chimp' ),
					'type' => 'boolean',
					'readonly' => true,
				],
				'title' => [
					'description' => __( 'The API connection title.', 'wp-chimp' ),
					'type' => 'string',
					'readonly' => true,
				],
				'response' => [
					'description' => __( 'Response detail.', 'wp-chimp' ),
					'type' => 'object',
					'readonly' => true,
				],
			],
		];
	}

	/**
	 * Prepares a the route response.
	 *
	 * @since 0.4.0
	 *
	 * @param array           $item The detail of a MailChimp list (e.g. list_id, name, etc.).
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $item, $request ) {

		$data = [];
		$fields = $this->get_fields_for_response( $request );

		if ( in_array( 'connected', $fields, true ) ) {
			$data['connected'] = (bool) $item['connected'];
		}

		if ( in_array( 'title', $fields, true ) ) {
			$data['title'] = wp_strip_all_tags( $item['title'] );
		}

		return rest_ensure_response( $data ); // Wrap the data in a response object.
	}
}
