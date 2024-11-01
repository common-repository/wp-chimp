<?php
/**
 * Core: Plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @package WP_Chimp\Core
 * @since 0.1.0
 */

namespace WP_Chimp\Core;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

use Exception;
use WP_Chimp\Admin;
use WP_Chimp\Core\Lists\Query_Trait;
use WP_Chimp\Deps\DrewM\MailChimp\MailChimp;
use WP_Chimp\Subscription_Form;

/**
 * Loaded dependencies with Mozart.
 *
 * The prefix looks terrible at best, but no other choice at least
 * for the moment.
 *
 * @since 0.2.0
 * @see https://github.com/coenjacobs/mozart
 */
use WP_Chimp_Packages_underDEV_Requirements as WP_Chimp_Requirements;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since 0.1.0
 */
class Plugin {

	/**
	 * Use traits.
	 *
	 * @since 0.7.0
	 */
	use Hooks_Trait, Query_Trait;

	/**
	 * Holds the WP_Chimp_Requirements instance.
	 *
	 * @since 0.7.0
	 * @var WP_Chimp_Requirements
	 */
	private $requirements;

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since 0.1.0
	 */
	public function load_dependencies() {

		require_once WP_CHIMP_PLUGIN_DIR . 'src/packages/Classes/underdev/requirements/underDEV_Requirements.php';
		require_once WP_CHIMP_PLUGIN_DIR . 'src/core/functions.php';

		require_once WP_CHIMP_PLUGIN_DIR . 'src/subscription-form/functions.php';
		require_once WP_CHIMP_PLUGIN_DIR . 'src/subscription-form/template-tags.php';
	}

	/**
	 * Check the plugin requirements.
	 *
	 * @since 0.1.0
	 */
	public function check_requirements() {

		$this->requirements = new WP_Chimp_Requirements(
			'WP Chimp',
			[
				'php' => '5.6',
				'wp' => '5.0',
			]
		);
	}

	/**
	 * Define the hooks related to the plugin requirements.
	 *
	 * @since 0.1.0
	 */
	public function define_requirement_hooks() {
		$this->get_hooks()->add_action( 'admin_notices', $this->requirements, 'notice' );
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the WP_Chimp/Languages class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since 0.1.0
	 */
	public function define_languages_hooks() {

		$languages = new Languages();
		$languages->set_hooks( $this->get_hooks() );
		$languages->run();
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since 0.1.0
	 */
	public function define_admin_hooks() {

		$admin = new Admin\Admin();
		$admin->set_hooks( $this->get_hooks() );
		$admin->run();

		$tab_mailchimp = new Admin\Partials\Tab_MailChimp();
		$tab_mailchimp->set_hooks( $this->get_hooks() );
		$tab_mailchimp->set_lists_query( $this->get_lists_query() );
		$tab_mailchimp->run();

		$tab_advanced = new Admin\Partials\Tab_Advanced();
		$tab_advanced->set_hooks( $this->get_hooks() );
		$tab_advanced->run();

		$admin_page = new Admin\Partials\Page();
		$admin_page->set_hooks( $this->get_hooks() );
		$admin_page->add_tab( $tab_mailchimp );
		$admin_page->add_tab( $tab_advanced );
		$admin_page->run();

		$admin_menu = new Admin\Partials\Menu();
		$admin_menu->set_hooks( $this->get_hooks() );
		$admin_menu->set_page( $admin_page );
		$admin_menu->run();
	}

	/**
	 * Register all of the hooks related to the database functionality
	 * of the plugin.
	 *
	 * @since 0.1.0
	 */
	public function define_database_hooks() {

		$lists_db = new Lists\Database_Table();
		$lists_db->set_hooks( $this->get_hooks() );
		$lists_db->run();
	}

	/**
	 * Register all the hooks related to the plugin options.
	 *
	 * @since 0.6.0
	 */
	public function define_options_hooks() {

		$options = new Options();
		$options->set_hooks( $this->get_hooks() );
		$options->run();
	}

	/**
	 * Register custom REST API routes of the plugin using WP-API.
	 *
	 * @since 0.1.0
	 */
	public function define_endpoints_hooks() {

		/**
		 * The MailChimp API key.
		 *
		 * @var string
		 */
		$api_key = get_the_mailchimp_api_key();

		if ( ! empty( $api_key ) ) {

			try {
				$mailchimp = new MailChimp( $api_key );

				$lists_rest = new Endpoints\REST_Lists_Controller();
				$lists_rest->set_hooks( $this->get_hooks() );
				$lists_rest->set_mailchimp( $mailchimp );
				$lists_rest->set_lists_query( $this->get_lists_query() );
				$lists_rest->run();

				$sync_rest = new Endpoints\REST_Sync_Lists_Controller();
				$sync_rest->set_hooks( $this->get_hooks() );
				$sync_rest->set_mailchimp( $mailchimp );
				$sync_rest->set_lists_query( $this->get_lists_query() );
				$sync_rest->run();

				$ping_rest = new Endpoints\REST_Ping_Controller();
				$ping_rest->set_hooks( $this->get_hooks() );
				$ping_rest->set_mailchimp( $mailchimp );
				$ping_rest->run();

				$subsform_rest = new Endpoints\REST_Subscription_Forms_Controller();
				$subsform_rest->set_hooks( $this->get_hooks() );
				$subsform_rest->set_mailchimp( $mailchimp );
				$subsform_rest->set_lists_query( $this->get_lists_query() );
				$subsform_rest->run();
			} catch ( Exception $e ) {

				// TODO: Should actually do something here.
				unset( $e );
			}
		}
	}

	/**
	 * Register all of the hooks to register the Subscribe Form.
	 *
	 * @since 0.1.0
	 */
	public function define_subscription_form_hooks() {

		$subscription_form = new Subscription_Form\Subscription_Form();

		$subscription_form->set_hooks( $this->get_hooks() );
		$subscription_form->set_lists_query( $this->get_lists_query() );
		$subscription_form->run();
	}

	/**
	 * Register the settings state to be used in the JavaScript side of the plugin.
	 *
	 * @since 0.1.0
	 * @since 0.3.0 The Core\Settings class is introduced.
	 * @since 0.4.0 Renamed to Core\Inline_State class.
	 */
	public function define_inline_state() {

		$settings = new Inline_State();
		$settings->set_hooks( $this->get_hooks() );
		$settings->run();
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 0.1.0
	 */
	public function run() {

		$this->load_dependencies();
		$this->check_requirements();

		if ( ! $this->requirements->satisfied() ) {
			$this->define_requirement_hooks();
		} else {
			$this->define_languages_hooks();
			$this->define_options_hooks();
			$this->define_database_hooks();
			$this->define_inline_state();
			$this->define_admin_hooks();
			$this->define_endpoints_hooks();
			$this->define_subscription_form_hooks();
		}

		$this->get_hooks()->run();
	}
}
