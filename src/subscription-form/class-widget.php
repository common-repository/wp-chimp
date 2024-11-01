<?php
/**
 * Subscription Form: Widget class
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
use function WP_Chimp\Core\get_the_mailchimp_api_key_status;
use function WP_Chimp\Core\rest_get_request;
use WP_Widget;

/**
 * Class to define the Subscribe Form widget.
 *
 * Register a new widget and define functionality to render the widget, both
 * of the front-end and the back-end.
 *
 * @since 0.1.0
 * @since 0.6.0 Added $title and $description props.
 *
 * @property array $title
 * @property array $description
 * @property array $defaults
 */
class Widget extends WP_Widget {

	/**
	 * The Widget title.
	 *
	 * @since 0.6.0
	 * @var string
	 */
	private $title;

	/**
	 * The Widget description.
	 *
	 * @since 0.6.0
	 * @var string
	 */
	private $description;

	/**
	 * Subscription Form defaults.
	 *
	 * @since 0.6.0
	 * @var array
	 */
	private $defaults;

	/**
	 * Specifies the classname and description, instantiates the widget,
	 *
	 * @since 0.1.0
	 */
	public function __construct() {

		$this->title = __( 'Subscription Form', 'wp-chimp' );
		$this->defaults = [
			'list_id' => get_the_default_list(),
			'title' => $this->title,
		];

		parent::__construct(
			'wp-chimp-subscription-form',
			$this->title,
			[
				'classname' => 'wp-chimp-subscription-form-widget',
				'description' => __( 'Display a MailChimp subscription form.', 'wp-chimp' ),
			]
		);
	}

	/**
	 * Echoes the widget content.
	 *
	 * @since 0.1.0
	 *
	 * @param array $args Display arguments including 'before_title', 'after_title', 'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	public function widget( $args, $instance ) {

		self::enqueue_scripts();

		$options = wp_parse_args( $instance, $this->defaults );
		$title = apply_filters( 'widget_title', $options['title'] );

		echo $args['before_widget']; // phpcs:ignore WordPress.XSS.EscapeOutput
		echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.XSS.EscapeOutput

		echo render( $options );

		echo $args['after_widget']; // phpcs:ignore WordPress.XSS.EscapeOutput
	}

	/**
	 * Outputs the settings update form.
	 *
	 * @since 0.1.0
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {

		$options = wp_parse_args( $instance, $this->defaults );
		if ( ! get_the_mailchimp_api_key_status() || ! isset( $options['list_id'] ) || empty( $options['list_id'] ) ) {
			the_notice_inactive();
			return;
		}

		$subscription_forms = rest_get_request( 'subscription-forms' );
		if ( ! is_array( $subscription_forms ) || 0 >= count( $subscription_forms ) ) {
			the_notice_inactive();
			return;
		}
		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'wp-chimp' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( isset( $options['title'] ) ? $options['title'] : $this->title ); ?>">
		</p>
		<p class="wp-chimp-list-select">
			<label for="<?php echo esc_attr( $this->get_field_id( 'list_id' ) ); ?>">
				<svg width="24" height="24" viewBox="0 0 24 24"><g><path fill="none" d="M0 0h24v24H0V0z"/><path d="M22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6zm-2 0l-8 4.99L4 6h16zm0 12H4V8l8 5 8-5v10z"/></g></svg>
				<span class="screen-reader-text"><?php esc_html_e( 'List ID', 'wp-chimp' ); ?></span>
			</label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'list_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'list_id' ) ); ?>">
			<?php
			foreach ( $subscription_forms as $subscription_form ) :
				$selected = $options['list_id'];
				$current = $subscription_form['list_id'];
				?>
				<option value="<?php echo esc_attr( $subscription_form['list_id'] ); ?>" <?php selected( $selected, $current, true ); ?>><?php echo esc_html( $subscription_form['name'] ); ?></option>
			<?php endforeach; ?>
			</select>
		</p>
		<?php
	}

	/**
	 * Updates a particular instance of a widget.
	 *
	 * @since 0.1.0
	 *
	 * @param array $new_instance New settings for this instance as input by the user via `WP_Widget::form()`.
	 * @param array $old_instance Old settings for this instance.
	 *
	 * @return array Settings to save or bool false to cancel saving.
	 */
	public function update( $new_instance, $old_instance ) {
		return wp_parse_args( $new_instance, $this->defaults );
	}

	/**
	 * Function to load the styles and scripts for the widget.
	 *
	 * @since 0.1.0
	 */
	private static function enqueue_scripts() {

		if ( ! wp_style_is( 'wp-chimp-subscription-form', 'enqueued' ) ) {
			wp_enqueue_style( 'wp-chimp-subscription-form' );
		}

		if ( ! wp_script_is( 'wp-chimp-subscription-form', 'enqueued' ) ) {
			wp_enqueue_script( 'wp-chimp-subscription-form' );
		}
	}
}

