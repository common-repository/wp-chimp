<?php
/**
 * Subscription Form: Attribute class
 *
 * @package WP_Chimp/Subscription_Form
 * @since 0.7.0
 */

namespace WP_Chimp\Subscription_Form;

use WP_Chimp\Core\Hooks_Trait;
use WP_Chimp\Core\Lists\Query_Trait;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

/**
 * Class to handle the Subscription Form Attirbutes input and output .
 *
 * @since 0.7.0
 */
class Attributes {

	/**
	 * Use traits.
	 *
	 * @since 0.7.0
	 */
	use Hooks_Trait, Query_Trait;

	/**
	 * The Constructor.
	 *
	 * @since 0.7.0
	 *
	 * @return void
	 */
	public function __construct() {

		$list_ids = $this->get_lists_query()->get_the_ids();
		if ( ! is_array( $list_ids ) ) {
			return;
		}

		foreach ( $list_ids as $list_id ) {
			$this->get_hooks()->add_filter( "default_option_wp_chimp_subscription_form_{$list_id}", __CLASS__, 'get_defaults', PHP_INT_MAX );
			$this->get_hooks()->add_filter( "option_wp_chimp_subscription_form_{$list_id}", __CLASS__, 'preserve_defaults', PHP_INT_MAX );
		}
	}

	/**
	 * Retrieve an attribute information.
	 *
	 * @since 0.7.0
	 *
	 * @param string $list_id The MailChimp List ID.
	 * @param string $attr_name An attribute name.
	 * @return array Detailed information regarding the attribute e.g. value, label, default, etc.
	 */
	public static function get( $list_id, $attr_name = '' ) {

		$composed_attributes = [];
		if ( empty( $list_id ) || ! is_string( $list_id ) ) {
			return $composed_attributes;
		}

		$values = (array) get_option( "wp_chimp_subscription_form_{$list_id}" );

		$composed_attributes = self::compose_attributes( $values );
		$composed_attributes = array_merge( [ 'list_id' => $list_id ], $composed_attributes );

		if ( is_string( $attr_name ) && isset( $composed_attributes[ $attr_name ] ) ) {
			return $composed_attributes[ $attr_name ];
		}

		return $composed_attributes;
	}

	/**
	 * Retrieve only the value of an attribute.
	 *
	 * @since 0.7.3
	 *
	 * @param string $list_id The MailChimp List ID.
	 * @param string $attr_name An attribute name.
	 * @return mixed|null
	 */
	public static function get_value( $list_id, $attr_name = '' ) {
		$attribute = self::get( $list_id, $attr_name );
		return isset( $attribute['value'] ) ? $attribute['value'] : null;
	}

	/**
	 * Update the value of an attribute.
	 *
	 * @since 0.7.0
	 *
	 * @param string $list_id The MailChimp List ID.
	 * @param array  $values The value of the attribute or a list of attribute.
	 * @return void
	 */
	public static function update( $list_id, array $values ) {
		update_option( "wp_chimp_subscription_form_{$list_id}", $values, false );
	}

	/**
	 * Sanitize and validate value before updating to the database.
	 *
	 * @since 0.7.0
	 *
	 * @param array $values The attribute value that's about to be saved to the database.
	 * @return array
	 */
	public static function sanitize( array $values = [] ) {

		$attributes = self::attributes();
		foreach ( $values as $key => $value ) {
			$editor_type = isset( $attributes[ $key ]['editor_type'] ) && is_string( $attributes[ $key ]['editor_type'] ) ? $attributes[ $key ]['editor_type'] : null;

			switch ( $editor_type ) {
				case 'text':
					$values[ $key ] = sanitize_text_field( $value );
					break;
				case 'textarea':
					$values[ $key ] = sanitize_textarea_field( $value );
					break;
				case 'richtext':
					$values[ $key ] = wp_kses_post(
						$value,
						[
							'strong' => true,
							'em' => true,
							'a' => [
								'href' => true,
								'target' => true,
								'rel' => true,
							],
						]
					);
					break;
			}
		}

		return $values;
	}

	/**
	 * Compose attributes value to the formatted data containing 'label', 'default',
	 * 'type', and 'editor_type'.
	 *
	 * @since 0.7.0
	 *
	 * @param array $attributes The value-key pair of the attribute name and the value.
	 * @return array
	 */
	public static function compose_attributes( array $attributes = [] ) {

		$attributes = self::preserve_defaults( $attributes );
		$composed_attributes = self::attributes();

		foreach ( $attributes as $key => $value ) {
			if ( 'list_id' === $key ) {
				continue;
			}
			$composed_attributes[ $key ]['value'] = $value;
		}

		return $composed_attributes;
	}

	/**
	 * Enforce the default value to some of the options.
	 *
	 * This function will ensure that option that's not yet set or the value is empty
	 * will fallback to the default value.
	 *
	 * @since 0.7.0
	 *
	 * @param mixed $values Value of the option. If stored serialized, it will be
	 *                      unserialized prior to being returned.
	 * @return array The options and their value.
	 */
	public static function preserve_defaults( $values = null ) {

		if ( ! is_array( $values ) ) {
			$values = [];
		}

		$defaults = self::get_defaults();
		$force_default = [
			'text_heading',
			'text_email_placeholder',
			'text_button',
			'text_notice_generic_error',
			'text_notice_subscribed',
			'text_notice_member_exists',
			'text_notice_pending',
			'text_notice_invalid_email',
			'text_notice_invalid_list_id',
			'text_notice_forgotten_email',
		];

		foreach ( $defaults as $key => $default ) {
			$value = isset( $values[ $key ] ) && is_string( $values[ $key ] ) ? $values[ $key ] : '';
			$values[ $key ] = in_array( $key, $force_default, true ) && empty( $value ) ? $default : $value;
		}

		return $values;
	}

	/**
	 * Get the default values of the attributes.
	 *
	 * @return array
	 */
	public static function get_defaults() {

		$properties = self::attributes();
		return array_map(
			function( $prop ) {
				if ( isset( $prop['default'] ) ) {
					return $prop['default'];
				}
			},
			$properties
		);
	}

	/**
	 * Retrieve the lists of attributes.
	 *
	 * @since 0.7.0
	 *
	 * @return array
	 */
	public static function attributes() {

		return [
			'text_heading' => [
				'label' => __( 'Heading Text', 'wp-chimp' ),
				'default' => __( 'Subscribe', 'wp-chimp' ),
				'type' => 'readonly',
				'editor_type' => 'text',
			],
			'text_sub_heading' => [
				'label' => __( 'Sub-heading Text', 'wp-chimp' ),
				'default' => __( 'Get notified of our next update right to your inbox', 'wp-chimp' ),
				'type' => 'readonly',
				'editor_type' => 'richtext',
			],
			'text_email_placeholder' => [
				'label' => __( 'Email Input Placeholder Text', 'wp-chimp' ),
				'description' => __( 'Displayed as placeholder text within the email input field.', 'wp-chimp' ),
				'default' => __( 'Enter your email address', 'wp-chimp' ),
				'type' => 'email',
				'editor_type' => 'text',
			],
			'text_marketing_permissions' => [
				'label' => __( 'Marketing Permission', 'wp-chimp' ),
				'default' => __( 'I agree to the Terms and Conditions and Privacy Policy', 'wp-chimp' ),
				'type' => 'checkbox',
				'editor_type' => 'richtext',
			],
			'text_button' => [
				'label' => __( 'Button Text', 'wp-chimp' ),
				'default' => __( 'Submit', 'wp-chimp' ),
				'type' => 'button',
				'editor_type' => 'text',
			],
			'text_footer' => [
				'label' => __( 'Footer Text', 'wp-chimp' ),
				'default' => __( 'We hate spam too, unsubscribe at any time', 'wp-chimp' ),
				'type' => 'readonly',
				'editor_type' => 'richtext',
			],
			'text_notice_generic_error' => [
				'label' => __( 'Generic Error Notice', 'wp-chimp' ),
				'description' => __( 'Displayed when the a non-specific error occured.', 'wp-chimp' ),
				'default' => __( 'Oops! Something went wrong. Please try again later', 'wp-chimp' ),
				'type' => 'readonly',
				'editor_type' => 'textarea',
			],
			'text_notice_subscribed' => [
				'label' => __( 'Subscribed Notice', 'wp-chimp' ),
				'description' => __( 'Displayed when the email is successfully subscribed.', 'wp-chimp' ),
				'default' => __( 'Thank you, your subscription request was successful!', 'wp-chimp' ),
				'type' => 'readonly',
				'editor_type' => 'textarea',
			],
			'text_notice_member_exists' => [
				'label' => __( 'Member Exists Notice', 'wp-chimp' ),
				'description' => __( 'Displayed when the email subscribed is already on the list.', 'wp-chimp' ),
				'default' => __( 'Your email address is already subscribed, thank you!', 'wp-chimp' ),
				'type' => 'readonly',
				'editor_type' => 'textarea',
			],
			'text_notice_pending' => [
				'label' => __( 'Pending Notice', 'wp-chimp' ),
				'description' => __( 'Displayed when the subscription requires confirmation.', 'wp-chimp' ),
				'default' => __( 'You\'re almost there. Please check your email to confirm your subscription', 'wp-chimp' ),
				'type' => 'readonly',
				'editor_type' => 'textarea',
			],
			'text_notice_invalid_email' => [
				'label' => __( 'Invalid Email Notice', 'wp-chimp' ),
				'description' => __( 'Displayed when the email format is invalid.', 'wp-chimp' ),
				'default' => __( 'Please provide a valid email address', 'wp-chimp' ),
				'type' => 'readonly',
				'editor_type' => 'textarea',
			],
			'text_notice_invalid_list_id' => [
				'label' => __( 'Invalid List ID Notice', 'wp-chimp' ),
				'description' => __( 'Displayed when the list ID designated is invalid or not present.', 'wp-chimp' ),
				'default' => __( 'Oops! Something went wrong. We could not add your email address to our mailing list', 'wp-chimp' ),
				'type' => 'readonly',
				'editor_type' => 'textarea',
			],
			'text_notice_forgotten_email' => [
				'label' => __( 'Forgotten Email Notice', 'wp-chimp' ),
				'description' => __( 'Displayed when the subscribed email was recently deleted from the List.', 'wp-chimp' ),
				'default' => __( 'This email address was permanently deleted and cannot be re-imported. The contact must re-subscribe to get back on the list', 'wp-chimp' ),
				'type' => 'readonly',
				'editor_type' => 'textarea',
			],
		];
	}
}
