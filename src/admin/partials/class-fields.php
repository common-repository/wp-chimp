<?php
/**
 * Admin: Fields class
 *
 * File to house the class that's used to render fields input, textarea, tinymce, etc.
 * for the Setting page in the admin area..
 *
 * @package WP_Chimp\Admin
 * @since 0.1.0
 */

namespace WP_Chimp\Admin\Partials;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

use function WP_Chimp\Core\get_the_mailchimp_api_key_status;

/**
 * Class to render render Setting fields such as input, textarea, tinymce, etc.
 *
 * @since 0.6.0
 */
class Fields {

	/**
	 * List of elements and their attributes allowed in the description fields.
	 *
	 * @since 0.7.0
	 */
	const KSES_FIELD_DESCRIPTION = [
		'em' => true,
		'strong' => true,
		'a' => [
			'href' => true,
			'target' => true,
			'title' => true,
		],
	];

	/**
	 * Parse the field arguments with the default.
	 *
	 * @since 0.7.0
	 *
	 * @param array $args The field arguments.
	 * @return array
	 */
	public static function parse_field_args( array $args = [] ) {

		$args = wp_parse_args(
			$args,
			[
				'name' => '',
				'value' => '',
				'label' => '',
				'label_for' => '',
				'placeholder' => '',
				'description' => '',
			]
		);

		return $args;
	}

	/**
	 * Render the input text field element.
	 *
	 * @since 0.6.0
	 *
	 * @param array $args The input text field arguments.
	 */
	public static function text( array $args = [] ) {

		$args = self::parse_field_args( $args );
		if ( empty( $args['name'] ) || empty( $args['label_for'] ) ) {
			return;
		} ?>
		<fieldset>
			<?php if ( ! empty( $args['label'] ) ) : ?>
			<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><?php echo esc_html( $args['label'] ); ?>:</label>
			<?php endif; ?>

			<input type="text" class="large-text" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="<?php echo esc_attr( $args['name'] ); ?>" placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>" value="<?php echo esc_attr( $args['value'] ); ?>" />

			<?php if ( ! empty( $args['description'] ) ) : ?>
			<p class="description"><?php echo wp_kses( $args['description'], self::KSES_FIELD_DESCRIPTION ); ?></p>
			<?php endif; ?>
		</fieldset>
		<?php
	}

	/**
	 * Render the "MailChimp API Key" input field.
	 *
	 * @since 0.6.0
	 *
	 * @param array $args The input field arguments.
	 */
	public static function mailchimp_api_key( array $args ) {

		$args = self::parse_field_args( $args );
		if ( empty( $args['name'] ) || empty( $args['label_for'] ) ) {
			return;
		}
		?>
		<fieldset>
			<input type="text" name="<?php echo esc_attr( $args['name'] ); ?>" id="<?php echo esc_attr( $args['label_for'] ); ?>" class="regular-text" value="<?php echo esc_attr( $args['value'] ); ?>" />

			<?php if ( get_the_mailchimp_api_key_status() ) : ?>
			<p class="description" id="wp-chimp-mailchimp-api-status"></p>
			<?php else : ?>
			<p class="description"><?php esc_html_e( 'Add your MailChimp API key', 'wp-chimp' ); ?>. <a href="https://kb.mailchimp.com/integrations/api-integrations/about-api-keys" target="_blank"><?php esc_html_e( 'How to get the API key?', 'wp-chimp' ); ?></a></p>
			<?php endif; ?>
		</fieldset>
		<?php
	}

	/**
	 * Generate the input checkbox.
	 *
	 * @param array $args The checkbox input arguments.
	 * @since 0.6.0
	 */
	public static function checkbox( array $args ) {

		$args = self::parse_field_args( $args );
		if ( empty( $args['name'] ) || empty( $args['label_for'] ) ) {
			return;
		}
		?>
		<fieldset>
			<label for="<?php echo esc_attr( $args['label_for'] ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( $args['name'] ); ?>" id="<?php echo esc_attr( $args['label_for'] ); ?>" <?php checked( $args['value'], 'on' ); ?>> <?php echo esc_html( $args['label'] ); ?>
			</label>

			<?php if ( ! empty( $args['description'] ) ) : ?>
			<p class="description"><?php echo wp_kses( $args['description'], self::KSES_FIELD_DESCRIPTION ); ?></p>
			<?php endif; ?>
		</fieldset>
		<?php
	}
}
