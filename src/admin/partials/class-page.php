<?php
/**
 * Admin: Page class
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @package WP_Chimp\Admin
 * @since 0.1.0
 */

namespace WP_Chimp\Admin\Partials;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

use WP_Chimp\Core\Hooks_Trait;

/**
 * Class to render the admin page.
 *
 * @since 0.1.0
 */
class Page {

	/**
	 * Use traits.
	 *
	 * @since 0.7.0
	 */
	use Hooks_Trait;

	/**
	 * List of tabs to render in the page.
	 *
	 * @since 0.6.0
	 * @var array
	 */
	public $tabs = [];

	/**
	 * Add a tab to the Settings page.
	 *
	 * @since 0.6.0
	 *
	 * @param Tab_Base $tab An object.
	 */
	public function add_tab( Tab_Base $tab ) {
		$this->tabs[ $tab->get_slug() ] = $tab;
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 0.3.0
	 */
	public function run() {

		/**
		 * Add the Action link for the plugin in the Plugin list screen.
		 *
		 * !important that_path e plugin file name is always referring to the plugin main file
		 * in the plugin's root folder instead of the sub-folders in order for the function_path to work.
		 *
		 * @link https://developer.wordpress.org/reference/hooks/prefixplugin_action_links_plugin_file/
		 */
		$this->get_hooks()->add_filter( 'plugin_action_links_' . plugin_basename( WP_CHIMP_PLUGIN_FILE ), $this, 'add_action_links', 2 );
	}

	/**
	 * Render the tab content.
	 *
	 * @since 0.6.0
	 *
	 * @return void
	 */
	public function render() {
		$current_tab = $this->get_current_tab();
		?>
		<div class="wrap wp-chimp-settings" id="wp-chimp-settings-<?php echo esc_attr( $current_tab->get_slug() ); ?>">
		<?php
			$this->render_tabs( $current_tab );
			$this->render_content( $current_tab );
		?>
		</div>
		<?php
	}

	/**
	 * Render the Tabs navigation.
	 *
	 * @since 0.6.0
	 *
	 * @param Tab_Base $current_tab The current tab slug.
	 */
	protected function render_tabs( Tab_Base $current_tab ) {

		if ( 1 <= count( $this->tabs ) ) :
			?>
			<div class="nav-tab-wrapper">
			<?php

			$tab_keys = array_keys( $this->tabs );
			foreach ( $this->tabs as $tab ) :

				$tab_slug = method_exists( $tab, 'get_slug' ) ? $tab->get_slug() : null;
				$current_tab_slug = method_exists( $current_tab, 'get_slug' ) ? $current_tab->get_slug() : null;

				if ( null === $tab_slug ) {
					continue;
				}

				$admin_url = add_query_arg( 'page', 'wp-chimp', admin_url( 'options-general.php' ) );
				$tab_url = isset( $tab_keys[0] ) && $tab_keys[0] === $tab_slug ? $admin_url : add_query_arg( 'tab', $tab_slug, $admin_url );
				$tab_active = $tab_slug === $current_tab_slug ? ' nav-tab-active' : '';
				?>

				<a href="<?php echo esc_url( $tab_url ); ?>" class="nav-tab<?php echo esc_attr( $tab_active ); ?>"><?php echo esc_html( $tab->get_title() ); ?></a>

				<?php
			endforeach;
			?>
			</div>
			<h1 class="screen-reader-text"><?php echo esc_html( $current_tab->get_title() ); ?></h1>
			<?php
		endif;
	}

	/**
	 * Render the Setting tab content.
	 *
	 * @since 0.6.0
	 *
	 * @param Tab_Base $current_tab The current tab slug.
	 */
	protected function render_content( Tab_Base $current_tab ) {

		if ( method_exists( $current_tab, 'content' ) ) {
			$current_tab->content();
		}
	}

	/**
	 * Retrieve the current tab object.
	 *
	 * @since 0.6.0
	 *
	 * @return object The current tab Object.
	 */
	protected function get_current_tab() {

		$current_tab = null;
		$tab_keys = array_keys( $this->tabs );
		$tab_first = isset( $tab_keys[0] ) ? $this->tabs[ $tab_keys[0] ] : null;  // Get the first tab.
		$tab_slug = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : '';   // phpcs:ignore WordPress.Security.NonceVerification

		foreach ( $this->tabs as $tab ) {
			if ( $tab->get_slug() !== $tab_slug ) {
				continue;
			}
			$current_tab = $tab;
		}

		return $current_tab ? $current_tab : $tab_first;
	}

	/**
	 * Add the action link in Plugin list screen.
	 *
	 * @since 0.1.0
	 *
	 * @param  array $links WordPress built-in links (e.g. Activate, Deactivate, and Edit).
	 * @return array        Action links with the new one added.
	 */
	public function add_action_links( $links ) {

		$admin_url = add_query_arg( 'page', 'wp-chimp', get_admin_url( null, 'options-general.php' ) );
		$markup = '<a href="' . esc_url( $admin_url ) . '">%1$s</a>';
		$settings = [
			'settings' => sprintf( $markup, __( 'Settings', 'wp-chimp' ) ),
		];

		return array_merge( $settings, $links );
	}
}
