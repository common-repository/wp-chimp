<?php
/**
 * Admin: Tab MailChimp class
 *
 * File to house the MailChimp tab regsitration in the plugin Settings page.
 *
 * @package WP_Chimp\Admin
 * @since 0.6.0
 */

namespace WP_Chimp\Admin\Partials;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

use Exception;
use function WP_Chimp\Core\get_the_mailchimp_api_key;
use function WP_Chimp\Core\get_the_mailchimp_api_key_status;
use function WP_Chimp\Core\get_the_script_suffix;
use function WP_Chimp\Core\is_setting_page;
use function WP_Chimp\Core\obfuscate_string;
use WP_Chimp\Deps\DrewM\MailChimp\MailChimp;

/**
 * Class to register the MailChimp tab in the Settings page.
 *
 * @since 0.6.0
 */
class Tab_MailChimp extends Tab_Base {

	/**
	 * The tab unique name.
	 *
	 * @since 0.6.0
	 * @var string
	 */
	protected $slug = 'mailchimp';

	/**
	 * The page ID to render the section and the fields.
	 *
	 * @since 0.6.0
	 * @var string
	 */
	protected $setting_page;

	/**
	 * The unique ID to register the Setting.
	 *
	 * @since 0.6.0
	 * @var string
	 */
	protected $setting_group;

	/**
	 * The Constructor
	 *
	 * @since 0.6.0
	 */
	public function __construct() {

		$this->title = __( 'MailChimp', 'wp-chimp' );
		$this->setting_page = 'wp-chimp-tab-mailchimp';
		$this->setting_group = 'wp-chimp-mailchimp';
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 0.6.0
	 */
	public function run() {

		$this->get_hooks()->add_action( 'admin_enqueue_scripts', $this, 'enqueue_styles' );
		$this->get_hooks()->add_action( 'admin_enqueue_scripts', $this, 'enqueue_scripts' );

		$this->get_hooks()->add_action( 'admin_init', $this, 'register_setting' );
		$this->get_hooks()->add_action( 'added_option', $this, 'added_option', 30, 2 );
		$this->get_hooks()->add_action( 'updated_option', $this, 'updated_option', 30, 3 );

		$this->get_hooks()->add_filter( 'default_option_wp_chimp_settings_mailchimp', __CLASS__, 'option_defaults', PHP_INT_MAX );
		$this->get_hooks()->add_filter( 'option_wp_chimp_settings_mailchimp', __CLASS__, 'force_options_default', PHP_INT_MAX );
	}

	/**
	 * Load the JavaScript for the Tab.
	 *
	 * @since 0.6.0
	 */
	public function enqueue_styles() {

		if ( is_setting_page() ) {
			$suffix = get_the_script_suffix();

			$handle = 'wp-chimp-tab-mailchimp';
			$file = "assets/css/tab-mailchimp{$suffix}.css";
			$deps = [ 'wp-components' ];
			$mod = filemtime( WP_CHIMP_PLUGIN_DIR . $file );

			wp_enqueue_style( $handle, plugins_url( $file, WP_CHIMP_PLUGIN_FILE ), $deps, $mod );
			wp_enqueue_style( 'wp-chimp-subscription-form' );
		}
	}

	/**
	 * Load the JavaScript for the Tab.
	 *
	 * @since 0.6.0
	 */
	public function enqueue_scripts() {

		if ( is_setting_page() ) {
			$suffix = get_the_script_suffix();

			$handle = 'react-content-loader';
			$file = "assets/js/react-content-loader{$suffix}.js";
			$deps = [ 'react', 'react-dom' ];
			$mod = filemtime( WP_CHIMP_PLUGIN_DIR . $file );

			wp_enqueue_script( $handle, plugins_url( $file, WP_CHIMP_PLUGIN_FILE ), $deps, $mod, false );

			if ( ! wp_script_is( 'clipboard', 'enqueued' ) ) {
				$file = "assets/js/clipboard{$suffix}.js";
				$deps = [];
				$mod = filemtime( WP_CHIMP_PLUGIN_DIR . $file );

				wp_enqueue_script( 'clipboard', plugins_url( $file, WP_CHIMP_PLUGIN_FILE ), $deps, $mod, false );
			}

			$handle = 'wp-chimp-tab-mailchimp';
			$file = "assets/js/tab-mailchimp{$suffix}.js";
			$deps = [ 'react', 'react-dom', 'lodash', 'clipboard', 'wp-compose', 'wp-api-fetch', 'wp-i18n', 'wp-autop', 'wp-element', 'wp-components' ];
			$mod = filemtime( WP_CHIMP_PLUGIN_DIR . $file );

			wp_enqueue_editor();
			wp_enqueue_script( $handle, plugins_url( $file, WP_CHIMP_PLUGIN_FILE ), $deps, $mod, true );
			wp_set_script_translations( $handle, 'wp-chimp' );
		}
	}

	/**
	 * Enforce the default value to some of the options.
	 *
	 * This function will ensure that option that's not yet set or the value is empty
	 * will fallback to the default value.
	 *
	 * @since 0.6.0
	 *
	 * @param mixed $values Value of the option. If stored serialized, it will be
	 *                      unserialized prior to being returned.
	 * @return array The options and their value.
	 */
	public static function force_options_default( $values ) {

		if ( ! is_array( $values ) ) {
			$values = [];
		}

		$defaults = self::option_defaults();
		$force_default = [];

		foreach ( $values as $key => $value ) {
			if ( in_array( $key, $force_default, true ) && empty( $value ) ) {
				$values[ $key ] = $defaults[ $key ];
			}
		}

		return $values;
	}

	/**
	 * Retrieve the options default value.
	 *
	 * @since 0.6.0
	 *
	 * @return array
	 */
	public static function option_defaults() {

		return [
			'api_key' => '',
		];
	}

	/**
	 * Sanitize the setting input before saving it to the database.
	 *
	 * @since 0.6.0
	 *
	 * @param array $values Unsanitized setting values.
	 * @return array Sanitized seting values.
	 */
	public static function sanitize_setting( array $values ) {

		$sanitized_values = [];
		foreach ( $values as $key => $value ) {
			switch ( $key ) {
				case 'api_key':
					$value = trim( $value );

					if ( ! empty( $value ) ) {
						$sanitized_values[ $key ] = sanitize_text_field( $value );
					}
					break;
			}
		}

		return $sanitized_values;
	}

	/**
	 * Register the page settings
	 *
	 * @since 0.6.0
	 */
	public function register_setting() {

		// MailChimp: Setting.
		$setting_name = 'wp_chimp_settings_mailchimp'; // The setting database option field.
		register_setting(
			$this->setting_group,
			$setting_name,
			[
				'type' => 'string',
				'sanitize_callback' => [ $this, 'sanitize_setting' ],
			]
		);

		// MailChimp: Section.
		$mailchimp_section = 'wp-chimp-mailchimp-section';
		add_settings_section( $mailchimp_section, '', [ $this, 'content_section_mailchimp' ], $this->setting_page );

		// MailChimp: Fields.
		$api_key = get_the_mailchimp_api_key();
		$field_name = 'api_key';
		$field_args = [
			'name' => "{$setting_name}[{$field_name}]",
			'value' => obfuscate_string( $api_key ),
			'label_for' => 'field-mailchimp-api-key',
			'description' => sprintf( '%s <a href="https://kb.mailchimp.com/integrations/api-integrations/about-api-keys" target="_blank">%s</a>', __( 'Add your MailChimp API key', 'wp-chimp' ), __( 'How to get the API key?', 'wp-chimp' ) ),
		];
		add_settings_field( $field_name, __( 'API Key', 'wp-chimp' ), [ __NAMESPACE__ . '\\Fields', 'mailchimp_api_key' ], $this->setting_page, $mailchimp_section, $field_args );
	}

	/**
	 * Render the HTML content of the tab.
	 *
	 * @since 0.6.0
	 */
	public function content() {
		?>
		<form action="options.php" method="post">
		<?php
			settings_fields( $this->setting_group );
			do_settings_sections( $this->setting_page );

			submit_button( __( 'Save Settings', 'wp-chimp' ) );
		?>
		</form>
		<?php
	}

	/**
	 * Function that fills the section with the desired content.
	 *
	 * @since 0.6.0
	 */
	public function content_section_mailchimp() {
		if ( get_the_mailchimp_api_key_status() ) :
			?>
			<header class="wp-chimp-section__header">
				<h2 class="wp-chimp-section__title wp-heading-inline"><?php esc_html_e( 'Lists', 'wp-chimp' ); ?></h2><button class="page-title-action" id="wp-chimp-sync-lists-button" type="button" disabled><?php esc_html_e( 'Sync', 'wp-chimp' ); ?></button>
				<p class="wp-chimp-section__description"><?php esc_html_e( 'The Lists in your MailChimp account is displayed in the following table. If you\'ve just made a change in MailChimp, click the Synchronize button above to update the List on this table.', 'wp-chimp' ); ?></p>
			</header>
			<div id="wp-chimp-lists"></div>
			<?php
		endif;
	}

	/**
	 * Handle the option update.
	 *
	 * @since 0.6.0
	 *
	 * @param string $option    Option name.
	 * @param mixed  $old_value The old option value.
	 * @param mixed  $value     The new option value.
	 */
	public function updated_option( $option, $old_value, $value ) {

		/**
		 * Check the MailChimp API key update; if the new API key is
		 * different we need to reset the initilization.
		 */
		if ( 'wp_chimp_settings_mailchimp' === $option && $value !== $old_value ) {
			$old_api_key = isset( $old_value['api_key'] ) ? $old_value['api_key'] : '';
			$new_api_key = isset( $value['api_key'] ) ? $value['api_key'] : '';

			if ( $new_api_key !== $old_api_key ) {
				$this->reset_data( $new_api_key );
			}
		}
	}

	/**
	 * Handle the option addition.
	 *
	 * Run when the option is first added.
	 *
	 * @since 0.6.0
	 *
	 * @param string $option Option name.
	 * @param mixed  $value  The new option value.
	 */
	public function added_option( $option, $value ) {

		if ( 'wp_chimp_settings_mailchimp' === $option && $value ) {
			$api_key = isset( $value['api_key'] ) ? $value['api_key'] : '';
			$this->reset_data( $api_key );
		}
	}

	/**
	 * Reset lists and some option whent API is added or updated.
	 *
	 * @since 0.6.0
	 * @since 0.2.1 Reset the default list.
	 *
	 * @param mixed $api_key The new MailChimp API Key.
	 */
	protected function reset_data( $api_key ) {

		$this->get_lists_query()->truncate(); // Remove all entries from the `_chimp_lists` table.
		$this->get_lists_query()->delete_cache(); // Remove from the Object Caching.

		$total_items = self::get_lists_total_items( $api_key );

		/**
		 * NOTE: We do not reset the `wp_chimp_default_list` option.
		 * This will ensure that the (previous) default List ID is
		 * supplied to avoid fatal error in the "Gutenberg" editor
		 * when the MailChimp Lists actually are not present.
		 *
		 * Example case where the block editor may throw an error.
		 * <!-- wp:wp-chimp/subscription-form -->
		 * [wp-chimp list_id="ab269ejkd"]
		 * <!-- /wp:wp-chimp/subscription-form -->
		 */
		update_option( 'wp_chimp_lists_init', 0, false );
		update_option( 'wp_chimp_lists_db_upgraded', [], false );
		update_option( 'wp_chimp_lists_total_items', $total_items ? $total_items : 0 );
		update_option( 'wp_chimp_api_key_status', null === $total_items ? 'invalid' : 'valid' );
	}

	/**
	 * Retrieve the total the lists total item registered in the MailChimp account.
	 *
	 * This function also acts as a "ping" to check whether the MailChimp API key
	 * is valid and that we are able to communitcate with MailChimp.
	 *
	 * @since 0.6.0
	 *
	 * @param string $api_key The MailChimp API key value.
	 * @return integer|null The number of total items, or null if it is not set.
	 */
	protected static function get_lists_total_items( $api_key ) {

		if ( empty( $api_key ) ) { // If empty, abort early.
			return;
		}

		try {
			$mailchimp = new MailChimp( $api_key );
			$response = $mailchimp->get(
				'lists',
				[
					'fields' => 'total_items',
				]
			);

			if ( $mailchimp->success() && isset( $response['total_items'] ) ) {
				return absint( $response['total_items'] );
			} else {

				if ( isset( $response['status'] ) ) {
					add_settings_error( 'wp-chimp-api-status', 'mailchimp-api-error', $mailchimp->getLastError(), 'error' );
				} else {

					$message = __( 'Oops, something unexpected happened. Please try again.', 'wp-chimp' );
					add_settings_error( 'wp-chimp-api-status', 'unknown-error', $message, 'error' );
				}
			}
		} catch ( Exception $e ) {

			$message = __( 'Invalid MailChimp API key supplied.', 'wp-chimp' );
			add_settings_error( 'wp-chimp-invalid-api-key', '401', $message );
		}
	}
}
