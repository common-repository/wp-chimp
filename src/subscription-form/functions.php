<?php
/**
 * Subscription Form functions
 *
 * @package WP_Chimp/Subscription_Form
 * @since 0.1.0
 */

namespace WP_Chimp\Subscription_Form;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

use function WP_Chimp\Core\get_the_mailchimp_api_key_status;
use function WP_Chimp\Core\get_the_rest_api_url;
use WP_Chimp\Core\Lists\Query;

/**
 * Retrieve the notice when Lists are not present or the MailChimp API
 * is not activated.
 *
 * @since 0.1.0
 * @since 0.6.0 Wrap the output in a `div`.
 *
 * @return string
 */
function get_the_notice_inactive() {

	$notice = '';
	if ( current_user_can( 'administrator' ) ) :

		// Translators: %1$s, and %2$s are a piece of translatable string wrapped within an HTML.
		$content = sprintf( __( 'Subscription Form is currently inactive. You might haven\'t yet input the MailChimp API key to %1$s or your MailChimp account might not contain a %2$s.', 'wp-chimp' ), '<a href="' . admin_url( 'options-general.php?page=wp-chimp' ) . '" target="_blank" class="wp-chimp-notice__url">' . __( 'the Settings page', 'wp-chimp' ) . '</a>', '<a href="https://kb.mailchimp.com/lists" target="_blank" class="wp-chimp-notice__url">' . __( 'List', 'wp-chimp' ) . '</a>' );

		$notice = "<div class=\"wp-chimp-notice wp-chimp-notice--warning\"><p class=\"wp-chimp-notice__content\">{$content}</p></div>";
	endif;

	return $notice;
}

/**
 * Retrieve the notice when the attributes or properties within the attributes,
 * are not defined.
 *
 * @since 0.6.0
 *
 * @param string $list_id The current list ID selected to render.
 * @return string
 */
function get_the_notice_undefined( $list_id ) {

	$notice = '';
	if ( current_user_can( 'administrator' ) ) :

		// Translators: %1$s, %2$s, %3$s, etc. are a piece of translatable string wrapped within an HTML.
		$content = sprintf( __( 'One or some of the required data property, such as the %1$s, %2$s and %3$s text, could not be retrieved from the selected MailChimp List. Please head to %4$s, and verify that the selected MailChimp List, %5$s, is actually present on this site.', 'wp-chimp' ), '<strong>' . __( 'Heading', 'wp-chimp' ) . '</strong>', '<strong>' . __( 'Email Placeholder', 'wp-chimp' ) . '</strong>', '<strong>' . __( 'Button', 'wp-chimp' ) . '</strong>', '<a href="' . admin_url( 'options-general.php?page=wp-chimp' ) . '" target="_blank" class="wp-chimp-notice__url">' . __( 'the Settings page', 'wp-chimp' ) . '</a>', "<code>{$list_id}</code>" );

		$notice = "<div class=\"wp-chimp-notice wp-chimp-notice--error\"><p class=\"wp-chimp-notice__content\">{$content}</p></div>";
	endif;

	return $notice;
}

/**
 * Echo the Subscription Form inactive notice.
 *
 * @since 0.6.0
 */
function the_notice_inactive() {

	$notice = get_the_notice_inactive();

	echo wp_kses(
		$notice,
		[
			'div' => [ 'class' => true ],
			'p' => [ 'class' => true ],
			'a' => [
				'href' => true,
				'target' => true,
				'class' => true,
			],
		]
	);
}

/**
 * Echo the Subscription Form inactive notice.
 *
 * @param string $list_id The current list ID selected to render.
 * @since 0.6.0
 */
function the_notice_undefined( $list_id ) {

	$notice = get_the_notice_undefined( $list_id );

	echo wp_kses(
		$notice,
		[
			'div' => [ 'class' => true ],
			'p' => [ 'class' => true ],
			'a' => [
				'href' => true,
				'target' => true,
				'class' => true,
			],
		]
	);
}

/**
 * Validate the selected MailChimp List ID.
 *
 * @since 0.6.0
 *
 * @param string $list_id The selected MailChimp List ID.
 * @return bool
 */
function validate_list_id( $list_id ) {

	/**
	 * When one of these filters are not defined, we can presume that
	 * the selected MailChimp List ID is not actually present.
	 *
	 * @see ./admin/partials/class-tab-mailchimp-manage.php
	 * @see ./assets/src/js/subscription-form/view.js
	 */

	if ( ! has_filter( "option_wp_chimp_subscription_form_{$list_id}" ) ) {
		return false;
	}

	if ( ! has_filter( "default_option_wp_chimp_subscription_form_{$list_id}" ) ) {
		return false;
	}

	return true;
}

/**
 * Retrieve Subscription Form attributes of a List ID.
 *
 * @since 0.6.0
 *
 * @param string $list_id The List ID.
 * @param string $name Optional. The attribute name.
 * @return mixed The value of the attributes.
 */
function get_attributes( $list_id, $name = '' ) {

	$options = get_option( "wp_chimp_subscription_form_{$list_id}" );

	if ( ! is_array( $options ) || empty( $options ) ) {
		return [];
	}

	$attributes = array_merge( [ 'list_id' => $list_id ], $options );
	$attributes = compose_attributes( $attributes );

	if ( is_string( $name ) && ! empty( $name ) ) {
		return isset( $attributes[ $name ] ) ? $attributes[ $name ] : null;
	} else {
		return $attributes;
	}
}

/**
 * Compose attributes value with the attribute properties e.g. label, default, and type.
 *
 * @since 0.7.0
 *
 * @see WP_Chimp/Subscription_Form/Subscription_Form::properties() Retrieve the Subscription Form attributes
 *
 * @param array $attrs The attributes value.
 * @return array
 */
function compose_attributes( array $attrs ) {

	$attributes = Attributes::attributes();
	foreach ( $attrs as $key => $value ) {
		if ( isset( $attributes[ $key ] ) ) {
			$attrs[ $key ] = array_merge( $attributes[ $key ], [ 'value' => $value ] );
		}
	}

	return $attrs;
}

/**
 * Render the "Subscription Form" HTML output.
 *
 * Handles "Subscription Form" output from the Widget, the Gutenberg Block,
 * and the Shortcode.
 *
 * @since 0.1.0
 *
 * @param  array $attrs The attributes to assign to the .
 * @return string The "Subscription Form" HTML markup or a notice if it's inactive.
 */
function render( array $attrs ) {

	if ( ! get_the_mailchimp_api_key_status() ) {
		return get_the_notice_inactive();
	}

	if ( ! validate_list_id( $attrs['list_id'] ) ) {
		return get_the_notice_undefined( $attrs['list_id'] );
	}

	$attributes = get_attributes( $attrs['list_id'] );
	$rest_url = get_the_rest_api_url();
	$action = "{$rest_url}/lists/{$attrs['list_id']}";

	// TODO: Need refactor.
	$query = new Query();
	$list = $query->get_by_the_id( $attrs['list_id'] );

	$rand = wp_rand( 1, 100 );
	ob_start();
	?>

	<div class="wp-chimp-subscription-form">
		<div class="wp-chimp-subscription-form__notice"></div>

		<header class="wp-chimp-subscription-form__header">
			<h3 class="wp-chimp-subscription-form__heading"><?php echo esc_html( $attributes['text_heading']['value'] ); ?></h3>
			<?php if ( isset( $attributes['text_sub_heading'] ) && ! empty( $attributes['text_sub_heading']['value'] ) ) : ?>
			<div class="wp-chimp-subscription-form__sub-heading">
				<?php
					$text_sub_heading = wpautop( trim( $attributes['text_sub_heading']['value'] ), false );
					echo wp_kses(
						$text_sub_heading,
						[
							'em' => true,
							'strong' => true,
							'a' => [
								'href' > true,
								'target' => true,
								'title' => true,
							],
						]
					);
				?>
			</div>
			<?php endif; ?>
		</header>

		<form class="wp-chimp-form" method="POST" action="<?php echo esc_attr( $action ); ?>">
			<fieldset class="wp-chimp-form-fields">
				<?php
				$text_email_placeholder = '';
				if ( isset( $attributes['text_email_placeholder'], $attributes['text_email_placeholder']['value'] ) ) {
					$text_email_placeholder = wp_kses( trim( $attributes['text_email_placeholder']['value'] ), [] );
				}
				?>
				<input class="wp-chimp-form-fields__item wp-chimp-form-fields-email" name="email" type="email" placeholder="<?php echo esc_attr( $text_email_placeholder ); ?>" required />

				<?php
				$marketing_permissions = '';
				if ( isset( $list['marketing_permissions'] ) && 1 === absint( $list['marketing_permissions'] ) ) {
					if ( isset( $attributes['text_marketing_permissions'], $attributes['text_marketing_permissions']['value'] ) ) {
						$marketing_permissions = wp_kses( trim( $attributes['text_marketing_permissions']['value'] ), [] );
					}
				}
				if ( ! empty( $marketing_permissions ) ) :
					?>
				<div class="wp-chimp-form-fields__item wp-chimp-form-fields-checkbox">
					<input type="checkbox" id="<?php echo esc_attr( "marketing_permissions_{$rand}" ); ?>" required />
					<label for="<?php echo esc_attr( "marketing_permissions_{$rand}" ); ?>">
						<?php echo esc_html( $marketing_permissions ); ?>
					</label>
				</div>
				<?php endif; ?>
			</fieldset>
			<?php
			$text_button = __( 'Subscribe', 'wp-chimp' );
			if ( isset( $attributes['text_button'], $attributes['text_button']['value'] ) ) {
				$text_button = wp_kses( trim( $attributes['text_button']['value'] ), [] );
			}
			?>
			<button class="wp-chimp-form-button" type="submit"><?php echo esc_html( $text_button ); ?></button>
		</form>

		<?php
		if ( isset( $attributes['text_footer'], $attributes['text_footer']['value'] ) &&
		! empty( $attributes['text_footer']['value'] ) ) :
			?>
		<div class="wp-chimp-subscription-form__footer">
			<?php
				$text_footer = wpautop( trim( $attributes['text_footer']['value'] ), false );
				echo wp_kses(
					$text_footer,
					[
						'em' => true,
						'strong' => true,
						'a' => [
							'href' > true,
							'target' => true,
							'title' => true,
						],
					]
				);
			?>
		</div>
		<?php endif; ?>
	</div>

	<?php
	$subscription_form = ob_get_contents();
	ob_end_clean();

	return $subscription_form;
}
