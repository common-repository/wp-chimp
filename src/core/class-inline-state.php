<?php
/**
 * Core: Inline_State class
 *
 * @package WP_Chimp\Core
 * @since 0.3.0
 * @since 0.4.0 Renamed to class-inline-state.php.
 */

namespace WP_Chimp\Core;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

/**
 * Class to handle the plugin State inline.
 *
 * @since 0.3.0
 */
class Inline_State {

	/**
	 * Use traits.
	 *
	 * @since 0.7.0
	 */
	use Hooks_Trait;

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 0.3.0
	 */
	public function run() {
		$this->get_hooks()->add_action( 'admin_enqueue_scripts', $this, 'enqueue_state', 30 );
	}

	/**
	 * Function to add the settings state.
	 *
	 * The settings state will be used in the JavaScript side of the plugin
	 * i.e. whether we should display the 'Subscription Form', request data
	 * to MailChimp API, etc.
	 *
	 * @since 0.3.0
	 */
	public function enqueue_state() {

		$state = $this->get_state();
		$data = 'var wpChimpInlineState = ' . wp_json_encode( $state );

		wp_add_inline_script( 'wp-chimp-tab-mailchimp', $data, 'before' );
		wp_add_inline_script( 'wp-chimp-subscription-form-block', $data, 'before' );
	}

	/**
	 * Retrieve options and nonces.
	 *
	 * This data will be primarily consumed in the JavaScript.
	 *
	 * @since 0.3.0
	 * @see enqueue_state
	 *
	 * @return array
	 */
	protected function get_state() {

		$tinymce_style_file = 'assets/css/admin.css';
		$tinymce_style_url = plugins_url( $tinymce_style_file, WP_CHIMP_PLUGIN_FILE );

		$args = [
			'wp_rest_nonce' => wp_create_nonce( 'wp_rest' ),
			'settings_url' => admin_url( 'options-general.php?page=wp-chimp' ),
			'rest_api_url' => get_the_rest_api_url(),
			'tinymce_style_url' => $tinymce_style_url . '?ver=' . filemtime( WP_CHIMP_PLUGIN_DIR . $tinymce_style_file ),
			'mailchimp_api_dc' => get_the_mailchimp_dc(),
			'mailchimp_api_status' => get_the_mailchimp_api_key_status(),
			'lists_total_items' => get_the_lists_total_items(),
			'lists_per_page' => get_the_lists_per_page(),
			'lists_init' => is_lists_init(),
			'default_list' => get_the_default_list(),
		];

		return convert_keys_to_camel_case( $args );
	}
}
