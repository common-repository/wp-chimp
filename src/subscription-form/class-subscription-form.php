<?php
/**
 * Subscription Form: Main class
 *
 * @package WP_Chimp/Subscription_Form
 * @since 0.1.0
 */

namespace WP_Chimp\Subscription_Form;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

use function WP_Chimp\Core\get_the_default_list;
use function WP_Chimp\Core\get_the_script_suffix;
use WP_Chimp\Core\Hooks_Trait;
use WP_Chimp\Core\Lists\Query_Trait;

/**
 * Class to register the Subscription Form.
 *
 * Register components of the Subscription Form such as scripts, styles,
 * widget, shortcode, locale strings, etc.
 *
 * @since 0.1.0
 *
 * @property string $dir_path
 */
class Subscription_Form {

	/**
	 * Use traits.
	 *
	 * @since 0.7.0
	 */
	use Hooks_Trait, Query_Trait;

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 0.3.0
	 */
	public function run() {

		$this->get_hooks()->add_action( 'init', $this, 'register_scripts' );
		$this->get_hooks()->add_action( 'init', $this, 'register_block' );
		$this->get_hooks()->add_action( 'init', $this, 'register_shortcode' );
		$this->get_hooks()->add_action( 'widgets_init', $this, 'register_widget' );
		$this->get_hooks()->add_action( 'wp_enqueue_scripts', $this, 'enqueue_scripts', 30 );
		$this->get_hooks()->add_action( 'enqueue_block_editor_assets', $this, 'enqueue_block_editor_scripts', PHP_INT_MAX );
		$this->get_hooks()->add_action( 'enqueue_block_editor_assets', $this, 'enqueue_block_editor_styles', PHP_INT_MAX );

		$list_ids = $this->get_lists_query()->get_the_ids();
		if ( ! is_array( $list_ids ) ) {
			return;
		}

		foreach ( $list_ids as $list_id ) {
			$this->get_hooks()->add_filter( "default_option_wp_chimp_subscription_form_{$list_id}", Attributes::class, 'get_defaults', PHP_INT_MAX );
			$this->get_hooks()->add_filter( "option_wp_chimp_subscription_form_{$list_id}", Attributes::class, 'preserve_defaults', PHP_INT_MAX );
		}
	}

	/**
	 * Register the stylesheet and JavaScript loaded on the Subscrition Form.
	 *
	 * @since 0.1.0
	 */
	public function register_scripts() {

		$suffix = get_the_script_suffix();

		$handle = 'wp-chimp-subscription-form';
		$file = "assets/js/subscription-form{$suffix}.js";
		$deps = [ 'jquery' ];
		$mod = filemtime( WP_CHIMP_PLUGIN_DIR . $file );

		wp_register_script( $handle, plugins_url( $file, WP_CHIMP_PLUGIN_FILE ), $deps, $mod, true );

		$handle = 'wp-chimp-subscription-form';
		$file = "assets/css/subscription-form{$suffix}.css";
		$deps = [];
		$mod = filemtime( WP_CHIMP_PLUGIN_DIR . $file );

		wp_register_style( $handle, plugins_url( $file, WP_CHIMP_PLUGIN_FILE ), $deps, $mod );
	}

	/**
	 * Load the JavaScript in the Gutenberg editor area.
	 *
	 * @since 0.6.0
	 */
	public function enqueue_block_editor_scripts() {

		if ( ( 'enqueue_block_editor_assets' === current_action() ) ) {
			$suffix = get_the_script_suffix();

			$handle = 'wp-chimp-subscription-form-block';
			$file = "assets/js/subscription-form-block{$suffix}.js";
			$deps = [ 'wp-element', 'wp-autop', 'lodash' ];
			$mod = filemtime( WP_CHIMP_PLUGIN_DIR . $file );

			wp_enqueue_script( $handle, plugins_url( $file, WP_CHIMP_PLUGIN_FILE ), $deps, $mod, true );
		}
	}

	/**
	 * Load the Stylesheets in the Gutenberg editor area.
	 *
	 * @since 0.6.0
	 */
	public function enqueue_block_editor_styles() {

		if ( ( 'enqueue_block_editor_assets' === current_action() ) ) {
			$suffix = get_the_script_suffix();

			$handle = 'wp-chimp-subscription-form-block';
			$file = "assets/css/subscription-form-block{$suffix}.css";
			$deps = [];
			$mod = filemtime( WP_CHIMP_PLUGIN_DIR . $file );

			wp_enqueue_style( $handle, plugins_url( $file, WP_CHIMP_PLUGIN_FILE ), $deps, $mod, 'all' );
		}
	}

	/**
	 * Register a custom Gutenberg block of the Subscription Form.
	 *
	 * @since 0.1.0
	 */
	public function register_block() {

		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			'wp-chimp/subscription-form',
			[
				'title' => __( 'Subscription Form', 'wp-chimp' ),
				'description' => __( 'Display a MailChimp subscription form.', 'wp-chimp' ),
				'category' => 'widgets',
				'keywords' => [ 'form', 'subscription', 'mailchimp' ],
				'supports' => [ 'html' => false ],
				'style' => 'wp-chimp-subscription-form',
				'attributes' => [
					'list_id' => [
						'type' => 'string',
						'default' => get_the_default_list(),
					],
				],
				'render_callback' => __NAMESPACE__ . '\\render',
			]
		);
	}

	/**
	 * Register the Subscription Form widget.
	 *
	 * @since 0.1.0
	 */
	public function register_widget() {
		register_widget( __NAMESPACE__ . '\\Widget' );
	}

	/**
	 * Register the Subscription Form shortcode.
	 *
	 * @since 0.1.0
	 */
	public function register_shortcode() {
		add_shortcode( 'wp-chimp', [ __NAMESPACE__ . '\\Shortcode', 'render' ] );
	}

	/**
	 * Load scripts and styles.
	 *
	 * @since 0.1.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'wp-chimp-subscription-form' );
	}
}
