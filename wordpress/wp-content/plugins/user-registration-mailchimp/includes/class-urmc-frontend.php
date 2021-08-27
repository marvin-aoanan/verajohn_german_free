<?php
/**
 * UserRegistrationMailChimp Frontend.
 *
 * @class    URMC_Frontend
 * @version  1.0.0
 * @package  UserRegistrationMailChimp/Admin
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URMC_Frontend Class
 */
class URMC_Frontend {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {

		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );

		add_action(
			'user_registration_after_register_user_action',
			array(
				$this,
				'user_registration_after_register_user_action',
			),
			10,
			3
		);

		add_action( 'woocommerce_checkout_update_user_meta', array( $this, 'urmc_checkout_process' ), 10, 2 );

		add_filter( 'user_registration_registered_form_fields', 'urmc_registered_form_fields', 11, 1 );

		add_filter( 'user_registration_form_field_mailchimp_path', 'urmc_form_field_mailchimp', 10, 1 );

		add_filter(
			'user_registration_mailchimp_frontend_form_data',
			array(
				$this,
				'user_registration_mailchimp_frontend_form_data',
			),
			10,
			1
		);
		add_action(
			'user_registration_after_save_profile_validation',
			array(
				$this,
				'user_registration_after_save_profile_validation',
			),
			10,
			2
		);

	}

	/**
	 * Sync Mailchimp after user update profile.
	 *
	 * @param int   $user_id Userid.
	 * @param array $profile Form Data.
	 */
	public function user_registration_after_save_profile_validation( $user_id, $profile ) {

		$user_subscribe_list = get_user_meta( $user_id, 'urmc_subscribe_mailchimp_list', true );

		$user_subscribe_list_array = json_decode( $user_subscribe_list, true );

		$form_id = get_user_meta( $user_id, 'ur_form_id', true );

		$valid_form_data = array();

		foreach ( $_POST as $post_key => $post_data ) {
			$pos = strpos( $post_key, 'user_registration_' );

			if ( false !== $pos ) {
				$new_string = substr_replace( $post_key, '', $pos, strlen( 'user_registration_' ) );

				if ( ! empty( $new_string ) ) {
					$valid_form_data[ $new_string ]               = new stdClass();
					$valid_form_data[ $new_string ]->value        = $post_data;
					$valid_form_data[ $new_string ]->field_type   = $profile[ $post_key ]['type'];
					$valid_form_data[ $new_string ]->label        = $profile[ $post_key ]['label'];
					$valid_form_data[ $new_string ]->field_name   = $new_string;
					$valid_form_data[ $new_string ]->extra_params = array(
						'field_key' => $profile[ $post_key ]['field_key'],
						'label'     => $profile[ $post_key ]['label'],
					);
				}
			}
		}
		if ( count( $valid_form_data ) < 1 ) {
			return;
		}

		$valid_mailchimp_lists_array   = urmc_get_valid_mailchimp_list( $form_id, $valid_form_data );
		$selected_mailchimp            = $valid_mailchimp_lists_array['selected_list'];
		$unselected_mailchimp          = $valid_mailchimp_lists_array['not_selected'];
		$sync_mailchimp_on_user_update = $valid_mailchimp_lists_array['sync_mailchimp_on_user_update'];

		$valid_mailchimp_lists = array_merge( $selected_mailchimp, $unselected_mailchimp );

		foreach ( $valid_mailchimp_lists as $list_key => $list ) {

			if ( isset( $valid_form_data[ $list_key ] ) && 'yes' === $sync_mailchimp_on_user_update[ $list_key ] ) {
				$is_mailchimp_subscribed_from_form = '1' == $valid_form_data[ $list_key ]->value ? true : false;
				$is_mailchimp_already_subscribed   = isset( $user_subscribe_list_array[ $list_key ] ) ? true : false;
				if ( $is_mailchimp_already_subscribed && ! $is_mailchimp_subscribed_from_form ) {
					URMC_MailChimp::unsubscribe( $user_id, $user_subscribe_list_array[ $list_key ], $form_id, $valid_form_data, $list_key );

				} elseif ( $is_mailchimp_subscribed_from_form && count( $selected_mailchimp ) > 0 ) {
					URMC_MailChimp::send_data( $valid_mailchimp_lists, $valid_form_data, $form_id, $user_id );
				}
			}
		}

	}

	/**
	 * Sync to Mailchimp After Woocommerce Checkout.
	 *
	 * @param int   $customer_id User ID.
	 * @param array $data Form Data.
	 */
	public function urmc_checkout_process( $customer_id, $data ) {
		$checkout = WC()->checkout();
		if ( ! $checkout->is_registration_required() && empty( $_POST['createaccount'] ) ) {
			return;
		}

		$form_id       = get_option( 'user_registration_woocommerce_settings_form', 0 );
		$checkout_sync = get_option( 'user_registration_woocommrece_settings_sync_checkout', 'no' );

		if ( 0 < $form_id && 'yes' === $checkout_sync ) {

			$profile = user_registration_form_data( $customer_id, $form_id );
			$valid_form_data = array();

			$check_mailchimp_field = false;
			foreach ( $_POST as $post_key => $post_data ) {
				if( 'billing_email' === $post_key ) {
					$post_key = 'user_registration_user_email';
				}else if(  'billing_first_name' === $post_key ||  'billing_last_name' === $post_key ){
					$post_key =  'billing_first_name' === $post_key ? 'user_registration_first_name' : 'user_registration_last_name';
				}

				$pos = strpos( $post_key, 'user_registration_' );

				if ( false !== $pos && isset( $profile[ $post_key ]['field_key'] ) ) {
					$new_string = substr_replace( $post_key, '', $pos, strlen( 'user_registration_' ) );

					if ( ! empty( $new_string ) ) {

						if( 'mailchimp' === $profile[ $post_key ]['field_key'] ) {
							$check_mailchimp_field = true;
						}
						$valid_form_data[ $new_string ]               = new stdClass();
						$valid_form_data[ $new_string ]->value        = $post_data;
						$valid_form_data[ $new_string ]->field_type   = $profile[ $post_key ]['type'];
						$valid_form_data[ $new_string ]->label        = $profile[ $post_key ]['label'];
						$valid_form_data[ $new_string ]->field_name   = $new_string;
						$valid_form_data[ $new_string ]->extra_params = array(
							'field_key' => $profile[ $post_key ]['field_key'],
							'label'     => $profile[ $post_key ]['label'],
						);
					}
				}
			}
			if ( ! $check_mailchimp_field || count( $valid_form_data ) < 1 ) {
				return;
			}

			$valid_mailchimp_lists_array = urmc_get_valid_mailchimp_list( $form_id, $valid_form_data );

			$valid_mailchimp_lists = $valid_mailchimp_lists_array['selected_list'];
			if ( count( $valid_mailchimp_lists ) > 0 ) {
				URMC_MailChimp::send_data( $valid_mailchimp_lists, $valid_form_data, $form_id, $customer_id );
			} else {
				$logger = ur_get_logger();
				$logger->error( 'Could not found any valid mailchimp list.', array( 'source' => 'ur-mailchimp' ) );
			}
		}
	}

	/**
	 * Sync to Mailchimp After Registeration.
	 *
	 * @param array $valid_form_data form data.
	 * @param int   $form_id form id.
	 * @param int   $user_id user id.
	 */
	public function user_registration_after_register_user_action( $valid_form_data, $form_id, $user_id ) {

		if ( $user_id < 1 ) {
			return;
		}

		$valid_mailchimp_lists_array = urmc_get_valid_mailchimp_list( $form_id, $valid_form_data );

		$valid_mailchimp_lists = $valid_mailchimp_lists_array['selected_list'];

		if ( count( $valid_mailchimp_lists ) > 0 ) {

			URMC_MailChimp::send_data( $valid_mailchimp_lists, $valid_form_data, $form_id, $user_id );

		} else {

			$logger = ur_get_logger();

			$logger->error( 'Could not found any valid mailchimp list.', array( 'source' => 'ur-mailchimp' ) );

		}

	}

	/**
	 * Mailchimp frontend form data.
	 *
	 * @param  array $filter_data  Filter Data.
	 */
	public function user_registration_mailchimp_frontend_form_data( $filter_data ) {

		$data = $filter_data['data'];

		$advance_settings = isset( $data['advance_setting'] ) ? $data['advance_setting'] : new stdClass();

		$auto_check = isset( $advance_settings->auto_check_list ) && 'yes' === $advance_settings->auto_check_list ? true : false;

		if ( $auto_check ) {
			$filter_data['form_data']['default'] = 1;
		}

		return $filter_data;
	}

	/**
	 * Load Script.
	 */
	public function load_scripts() {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'user-registration-mailchimp-frontend-script', URMC()->plugin_url() . '/assets/js/frontend/user-registration-mailchimp-script' . $suffix . '.js', array( 'jquery' ), URMC_VERSION );

		wp_register_style( 'user-registration-mailchimp-frontend-style', URMC()->plugin_url() . '/assets/css/user-registration-mailchimp-frontend-style.css', array(), URMC_VERSION );

		$condition = false;

		if ( $condition ) {
			wp_enqueue_script( 'user-registration-mailchimp-frontend-script' );
			wp_enqueue_style( 'user-registration-mailchimp-frontend-style' );
			wp_localize_script(
				'user-registration-mailchimp-script',
				'urmc_frontend_data',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
				)
			);
		}

	}


}

return new URMC_Frontend();
