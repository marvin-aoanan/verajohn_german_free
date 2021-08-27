<?php
/**
 * URMC_Ajax
 *
 * AJAX Event Handler
 *
 * @class    URMC_Ajax
 * @version  1.0.0
 * @package  UserRegistrationFileUpload/Classes
 * @category Class
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URMC_Ajax Class
 */
class URMC_Ajax {

	/**
	 * Hooks in ajax handlers
	 */
	public static function init() {

		self::add_ajax_events();
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax)
	 */
	public static function add_ajax_events() {

		$ajax_events = array(
			'sync_with_mailchimp' => false,
		);
		foreach ( $ajax_events as $ajax_event => $nopriv ) {

			add_action( 'wp_ajax_user_registration_mailchimp_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {

				add_action(
					'wp_ajax_nopriv_user_registration_mailchimp_' . $ajax_event,
					array(
						__CLASS__,
						$ajax_event,
					)
				);
			}
		}
	}

	/**
	 * Sync with Mailchimp.
	 */
	public static function sync_with_mailchimp() {

		check_ajax_referer( 'mailchimp-synchronization', 'security' );

		$nonce = isset( $_REQUEST['security'] ) ? $_REQUEST['security'] : '';

		$api = isset( $_REQUEST['api'] ) ? $_REQUEST['api'] : '';

		$flag = wp_verify_nonce( $nonce, 'mailchimp-synchronization' );

		if ( true != $flag || is_wp_error( $flag ) ) {

			wp_send_json_error(
				array(
					'message' => __( 'Could not validate nonce, please reload and try again.', 'user-registration-mailchimp' ),
				)
			);
		}

		$urmc_mailchimp_settings_option = get_option( 'urmc_mailchimp_settings', array() );

		$urmc_mailchimp_settings_option['api_key'] = $api;

		$status = URMC_MailChimp::validate_key_and_update( $urmc_mailchimp_settings_option, true );

		if ( $status ) {
			wp_send_json_success(
				array(
					'message' => __( 'Successfully fetch data from mailchimp server.', 'user-registration-mailchimp' ),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => sprintf( __( 'Could not fetch data from mailcimp server. May be wrong API key. Please check %1$slog%2$s for more detail.', 'user-registration-mailchimp' ), '<a target="_blank" href="' . admin_url( 'admin.php?page=user-registration-status' ) . '">', '</a>' ),
				)
			);
		}

	}


}

URMC_Ajax::init();
