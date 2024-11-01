<?php
/**
 * File to host the Subscription Form functions.
 *
 * @package WP_Chimp/Subscription_Form
 * @since 0.7.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

use function WP_Chimp\Subscription_Form\render;

if ( ! function_exists( 'get_the_subscription_form' ) ) :

	/**
	 * Retrieve the subscription form of the given list ID.
	 *
	 * @since 0.7.0
	 *
	 * @param string $list_id The MailChimp list ID.
	 * @return string
	 */
	function get_the_subscription_form( $list_id = '' ) {

		if ( ! is_string( $list_id ) || empty( $list_id ) ) {
			return '';
		}

		return render( [ 'list_id' => $list_id ] );
	}

endif;

if ( ! function_exists( 'the_subscription_form' ) ) :

	/**
	 * Render the subscription form of the given list ID.
	 *
	 * @since 0.7.0
	 *
	 * @param string $list_id The MailChimp list ID.
	 */
	function the_subscription_form( $list_id = '' ) {
		echo get_the_subscription_form( $list_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

endif;
