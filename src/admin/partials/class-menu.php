<?php
/**
 * Admin: Menu class
 *
 * @package WP_Chimp\Admin
 * @since 0.1.0
 */

namespace WP_Chimp\Admin\Partials;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

use function WP_Chimp\Core\is_setting_page;
use WP_Chimp\Admin\Partials\Page;
use WP_Chimp\Core\Hooks_Trait;

/**
 * Class to register a new menu in the admin area.
 *
 * @since 0.1.0
 */
class Menu {

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

		$this->get_hooks()->add_action( 'admin_menu', $this, 'register_menu' );
		$this->get_hooks()->add_action( 'current_screen', $this, 'register_help_tabs' );

		$this->get_hooks()->add_filter( 'admin_footer_text', __CLASS__, 'customize_footer_text', PHP_INT_MAX );
	}

	/**
	 * Register the Admin\Partials\Page instance.
	 *
	 * @since 0.4.0
	 *
	 * @param Page $admin_page The Admin\Partials\Page instance.
	 */
	public function set_page( Page $admin_page ) {
		$this->admin_page = $admin_page;
	}

	/**
	 * Register a new menu page in the admin.
	 *
	 * @since 0.1.0
	 */
	public function register_menu() {

		$menu_title = 'MailChimp';
		$page_title = 'MailChimp ' . __( 'Settings', 'wp-chimp' ); // Mind the space.

		add_options_page( $page_title, $menu_title, 'manage_options', 'wp-chimp', [ $this->admin_page, 'render' ] );
	}

	/**
	 * Add a tab to the contextual help menu in the admin page.
	 *
	 * @since 0.1.0
	 */
	public function register_help_tabs() {

		$screen = get_current_screen();

		if ( 'settings_page_wp-chimp' === $screen->id ) {

			$screen->add_help_tab(
				[
					'id' => 'overview',
					'title' => __( 'Overview', 'wp-chimp' ),
					'callback' => [ __CLASS__, 'html_help_tab_overview' ],
				]
			);

			$screen->add_help_tab(
				[
					'id' => 'mailchimp-api',
					'title' => __( 'MailChimp API', 'wp-chimp' ),
					'callback' => [ __CLASS__, 'html_help_tab_mailchimp_api' ],
				]
			);

			$screen->add_help_tab(
				[
					'id' => 'synchronize-lists',
					'title' => __( 'Synchronize Lists', 'wp-chimp' ),
					'callback' => [ __CLASS__, 'html_help_tab_synchronize_lists' ],
				]
			);
			$screen->set_help_sidebar( self::html_help_tab_sidebar() );
		}
	}

	/**
	 * Render the content of the "Overview" section in the Help tab in the plugin Admin Page.
	 *
	 * @since 0.1.0
	 */
	public static function html_help_tab_overview() {
		?>
		<p><?php esc_html_e( 'This is the setting page where you can connect your site to your MailChimp account. Once connected the plugin will provide you the ability to display a MailChimp subscription form in the posts, pages, and the Widget area.', 'wp-chimp' ); ?></p>

		<p><strong><?php esc_html_e( 'Non-affiliation Disclaimer', 'wp-chimp' ); ?></strong></p>
		<p><?php esc_attr_e( 'MailChimp is a registered trademark of The Rocket Science Group. The name "MailChimp" used in this plugin is for identification and reference purposes only and does not imply any association with the trademark holder of their product brand, or any of its subsidiaries or its affiliates.', 'wp-chimp' ); ?></p>
		<?php
	}

	/**
	 * Render the content of the "MailChimp API" section in the Help tab in the plugin Admin Page.
	 *
	 * @since 0.2.0
	 */
	public static function html_help_tab_mailchimp_api() {

		// Translators: %s link to MailChimp help article.
		$about_mailchimp_api_key = sprintf( __( "To connect to your MailChimp account, you'll need to generate a MailChimp API key. You may refer to this guide from MailChimp, %s, to generate the API key.", 'wp-chimp' ), '<a href="https://mailchimp.com/help/about-api-keys/" target="_blank">About API Keys</a>' );
		?>

		<p>
		<?php
			echo wp_kses(
				$about_mailchimp_api_key,
				[
					'a' => [
						'href' => true,
						'target' => true,
					],
				]
			);
		?>
		</p>

		<p><?php esc_html_e( "Once you've obtained the API key, add it to the input field on this Settings page. The site will start retrieving the MailChimp Lists from your MailChimp account, and display them on a table on this Settings page with a few of the details such as the ID, the name, and the number of subscribers.", 'wp-chimp' ); ?></p>
		<?php
	}

	/**
	 * Render the content of the "Synchronize Lists" section in the Help tab in the plugin Admin Page.
	 *
	 * @since 0.4.0
	 */
	public static function html_help_tab_synchronize_lists() {

		$synchronize_lists_btn = '<kbd>' . _x( 'Sync', 'button text', 'wp-chimp' ) . '</kbd>';

		// Translators: %s the Synchronize Lists button wrapped in `kbd` HTML element.
		$synchronize_lists = sprintf( __( 'You can click the %s button whenever you need to update the MailChimp Lists data from MailChimp onto your site.', 'wp-chimp' ), $synchronize_lists_btn );
		?>
		<p><?php echo wp_kses( $synchronize_lists, [ 'kbd' => true ] ); ?></p>
		<p><?php esc_html_e( "This button could be disabled that will prevent you to update the MailChimp Lists. If this happens, it is because the site is not able not connect to the MailChimp API. It could be the API key has become invalid, deactivated, there's an ongoing issue with the MailChimp data center, or there's something between the site and MailChimp that prevents the outgoing connection to pass through such as a Firewall.", 'wp-chimp' ); ?></p>
		<?php
	}

	/**
	 * Render the content of the sidebar in the Help tab in the plugin Admin Page.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public static function html_help_tab_sidebar() {

		$content = '<p><strong>' . esc_html__( 'For more information:', 'wp-chimp' ) . '</strong></p>';
		$content .= '<p><a href="https://kb.mailchimp.com/integrations/api-integrations/about-api-keys" target="_blank">' . esc_html__( 'About MailChimp API keys', 'wp-chimp' ) . '</a></p>';
		$content .= '<p><a href="https://codex.wordpress.org/Shortcode" target="_blank">' . esc_html__( 'About WordPress Shortcode', 'wp-chimp' ) . '</a></p>';

		return $content;
	}

	/**
	 * Customize the footer text in the plugin setting page.
	 *
	 * @since 0.7.0
	 *
	 * @param string $text The admin footer text.
	 * @return string Customized admin footer text.
	 */
	public static function customize_footer_text( $text ) {

		if ( is_setting_page() ) {

			// Translators: 1. WP Chimp.
			$thank_you = '<em id="wp-chimp-thanks">' . sprintf( __( 'Thank you for using %1$s', 'wp-chimp' ), '<a href="https://wordpress.org/plugins/wp-chimp/" target="_blank">WP Chimp</a>' ) . '</em>';
			$disclaimer = '<em><a href="https://github.com/wp-chimp/wp-chimp#non-affiliation-disclaimer" target="_blank">Non-affiliation Disclaimer</a></em>';

			return $thank_you . ' &#8226; ' . $disclaimer;
		}

		return $text;
	}
}
