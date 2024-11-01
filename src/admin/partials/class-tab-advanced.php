<?php
/**
 * Admin: Tab_Advanced class
 *
 * @package WP_Chimp\Admin
 * @since 0.6.0
 */

namespace WP_Chimp\Admin\Partials;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

/**
 * Class to register the Advanced tab in the Settings page.
 *
 * @since 0.6.0
 */
class Tab_Advanced extends Tab_Base {

	/**
	 * The tab unique name.
	 *
	 * @since 0.6.0
	 * @var string
	 */
	protected $slug = 'advanced';

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
	 * The unique name to save the Setting value in the database.
	 *
	 * @since 0.6.0
	 * @var string
	 */
	protected $setting_name;

	/**
	 * The Constructor
	 *
	 * @since 0.6.0
	 */
	public function __construct() {

		$this->title = __( 'Advanced', 'wp-chimp' );
		$this->setting_page = 'wp-chimp-tab-advanced';
		$this->setting_group = 'wp-chimp-advanced';
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 0.6.0
	 */
	public function run() {

		$this->get_hooks()->add_action( 'admin_init', $this, 'register_setting' );
		$this->get_hooks()->add_action( 'added_option', $this, 'added_option', 30, 2 );
		$this->get_hooks()->add_action( 'updated_option', $this, 'updated_option', 30, 3 );

		$this->get_hooks()->add_filter( 'default_option_wp_chimp_settings_advanced_lists', __CLASS__, 'lists_option_defaults', PHP_INT_MAX );
		$this->get_hooks()->add_filter( 'option_wp_chimp_settings_advanced_lists', __CLASS__, 'lists_force_options_default', PHP_INT_MAX );
	}

	/**
	 * Enforce the default value to some of the options in `wp_chimp_settings_advanced_lists`.
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
	public static function lists_force_options_default( $values ) {

		if ( ! is_array( $values ) ) {
			$values = [];
		}

		$defaults = self::lists_option_defaults();
		$force_default = [];

		foreach ( $values as $key => $value ) {
			if ( in_array( $key, $force_default, true ) && empty( $value ) ) {
				$values[ $key ] = $defaults[ $key ];
			}
		}

		return $values;
	}

	/**
	 * Retrieve the options default value `wp_chimp_settings_advanced_lists`.
	 *
	 * @since 0.6.0
	 *
	 * @return array
	 */
	public static function lists_option_defaults() {

		return [
			'detailed_stats' => 'off',
		];
	}

	/**
	 * Register the page settings.
	 *
	 * @since 0.6.0
	 */
	public function register_setting() {

		// Advanced: Setting.
		$setting_name = 'wp_chimp_settings_advanced_lists';
		register_setting(
			$this->setting_group,
			$setting_name,
			[
				'type' => 'string',
				'sanitize_callback' => [ __CLASS__, 'sanitize_setting' ],
			]
		);

		// Advanced: Section.
		$advanced_section = 'wp-chimp-section-advanced';
		add_settings_section( $advanced_section, '', '', $this->setting_page );

		// Advanced: Fields.
		$values = get_option( $setting_name );

		$field_name = 'detailed_stats';
		$field_args = [
			'name' => "{$setting_name}[{$field_name}]",
			'value' => isset( $values[ $field_name ] ) ? $values[ $field_name ] : 'off',
			'label_for' => 'field-lists-detailed-stats',
			'label' => __( 'Display the Lists detailed stats', 'wp-chimp' ),

			// Translators: %s additional text to notice user to resync lists ("After selecting this option...").
			'description' => sprintf( __( 'Display additional statistic information of the list, such as the Avg. Subscribe Rate, Avg. Open Rate, etc. %s', 'wp-chimp' ), '<strong>' . __( 'Don\'t forget to resynchronize the MailChimp Lists to pull the detailed stats from MailChimp.', 'wp-chimp' ) . '</strong>' ),
		];
		add_settings_field( $field_name, __( 'Detailed Stats', 'wp-chimp' ), [ __NAMESPACE__ . '\\Fields', 'checkbox' ], $this->setting_page, $advanced_section, $field_args );
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

		if ( 'wp_chimp_settings_advanced_lists' === $option && $value ) {
			update_option( 'wp_chimp_lists_init', 0, false );
		}
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

		if ( 'wp_chimp_settings_advanced_lists' === $option && $value !== $old_value ) {
			update_option( 'wp_chimp_lists_init', 0, false );
		}
	}

	/**
	 * Sanitize the setting input before saving it to the database.
	 *
	 * @since 0.6.0
	 *
	 * @param array $values Unsanitized setting values.
	 * @return array Sanitized seting values.
	 */
	public static function sanitize_setting( $values ) {
		return $values;
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
}
