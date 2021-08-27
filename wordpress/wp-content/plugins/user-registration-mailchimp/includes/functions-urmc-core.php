<?php
/**
 * UserRegistrationMailChimp Functions.
 *
 * General core functions available on both the front-end and admin.
 *
 * @author   WPEverest
 * @category Core
 * @package  UserRegistrationMailChimp/Functions
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Checks Mailchimp Compatible.
 *
 * @return string
 */
function urmc_is_compatible() {

	$plugins_path = WP_PLUGIN_DIR . URMC_DS . 'user-registration' . URMC_DS . 'user-registration.php';

	if ( ! file_exists( $plugins_path ) ) {

		return __( 'Please install <code>user-registration</code> plugin to use <code>user-registration-mailchimp</code> addon.', 'user-registration-mailchimp' );
	}
	$plugin_file_path = 'user-registration/user-registration.php';

	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	if ( ! is_plugin_active( $plugin_file_path ) ) {

		return __( 'Please activate <code>user-registration</code> plugin to use <code>user-registration-mailchimp</code> addon.', 'user-registration-mailchimp' );

	}

	if ( function_exists( 'UR' ) ) {

		$user_registration_version = UR()->version;

	} else {

		$user_registration_version = get_option( 'user_registration_version' );

	}

	if ( version_compare( $user_registration_version, '1.1.0', '<' ) ) {

		return __( 'Please update your <code>user registration</code> plugin(to at least 1.1.0 version) to use <code>user-registration-mailchimp</code> addon.', 'user-registration-mailchimp' );

	}

	return 'YES';

}

/**
 * Checks Plugin compatibility
 */
function urmc_check_plugin_compatibility() {

	add_action( 'admin_notices', 'urmc_admin_notices', 10 );

}

/**
 * Admin Notices.
 */
function urmc_admin_notices() {

	$class = 'notice notice-error';

	$message = urmc_is_compatible();

	if ( 'YES' !== $message ) {

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), ( $message ) );
	}
}

/**
 * Mailchimp  api settings.
 *
 * @return mixed
 */
function urmc_mailchimp_api_settings() {

	return apply_filters(
		'user_registration_mail_chimp_settings',
		array(

			array(
				'title' => __( 'API settings', 'user-registration-mailchimp' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'user_registration_mailchimp_options',
			),
			array(
				'title'    => __( 'Enable mailchimp integration ?', 'user-registration-mailchimp' ),
				'desc'     => __( 'Tick here if you want to enable mailchimp integration.', 'user-registration-mailchimp' ),
				'id'       => 'user_registration_mail_chimp_setting_enable',
				'default'  => 'no',
				'type'     => 'checkbox',
				'autoload' => false,
			),
			array(
				'title'    => __( 'Mailchimp API Key', 'user-registration-mailchimp' ),
				'desc'     => sprintf( __( 'Get api key from  %1$s mailchimp %2$s.', 'user-registration-mailchimp' ), '<a href="https://us16.admin.mailchimp.com/account/api/" target="_blank">', '</a>' ),
				'default'  => '',
				'id'       => 'user_registration_mail_chimp_setting_api_key',
				'type'     => 'text',
				'autoload' => false,
				'desc_tip' => true,
				'css'      => 'min-width: 350px;',

			),
			array(
				'type' => 'sectionend',
				'id'   => 'user_registration_mailchimp_options',
			),

		)
	);

}

/**
 * Mailchimp advance settings.
 *
 * @return mixed
 */
function urmc_mailchimp_advance_settings() {

	return apply_filters(
		'user_registration_other_mail_chimp_settings',
		array(

			array(
				'title' => __( 'Advance Settings', 'user-registration-mailchimp' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'user_registration_mailchimp_advance_options',
			),

			array(
				'type' => 'sectionend',
				'id'   => 'user_registration_mailchimp_advance_options',
			),

		)
	);

}

/**
 * Checks is mailchimp enabled.
 *
 * @param int $form_id Form Id.
 *
 * @return bool
 */
function urmc_is_mailchimp_enable( $form_id ) {
	$ur_mail_chimp_setting_api_key        = get_option( 'user_registration_mail_chimp_setting_api_key', 0 );
	$ur_mail_chimp_setting_setting_enable = get_option( 'user_registration_mail_chimp_setting_enable', 0 );
	$list_id                              = ur_get_single_post_meta( $form_id, 'user_registration_form_setting_mailchimp_list_id', '0' );

	if ( ! empty( $list_id ) && '0' !== $list_id && 0 !== $ur_mail_chimp_setting_api_key && ! empty( $ur_mail_chimp_setting_api_key ) && 'yes' === $ur_mail_chimp_setting_setting_enable ) {

		return true;
	}

	return false;

}

/**
 * Get mailchimp api key content.
 *
 * @param array $ur_mc_settings Mailchimp Settings.
 *
 * @return string
 */
function urmc_get_mailchimp_api_key_content( $ur_mc_settings ) {

	$ur_mc_api_key_validated = get_option( 'urmc_mailchimp_api_key_validated', false );

	isset( $ur_mc_settings['api_key'] ) ? $ur_mc_api_key = $ur_mc_settings['api_key'] : $ur_mc_api_key = '';

	$ur_mc_api_key_validated = (bool) $ur_mc_api_key_validated;
	$output                  = '';
	$output                 .= '<div id="ur-mailchimp-api-key">';
	$output                 .= '<label for="ur-mailchimp-api-key-input"><strong>' . __( 'MailChimp API Key:', 'user-registration-mailchimp' ) . '</strong></label>';
	$output                 .= '<input id="urmc_mailchimp_setting" class="ur-text ur-mailchimp-api-key regular-text" type="text" name="urmc_mailchimp_settings[api_key]" value="' . $ur_mc_api_key . '" />';
	$status                  = __( 'Not Connected', 'user-registration-mailchimp' );
	$add_class               = 'ur-mailchimp-offline';

	if ( ! empty( $ur_mc_api_key ) && true == $ur_mc_api_key_validated ) {
		$output   .= '<span class="validateStatus"><img src="' . URMC()->plugin_url() . '/assets/images/icon_okay.png" title="' . __( 'The API key was successfully validated!', 'user-registration-mailchimp' ) . '"/></span>';
		$status    = __( 'Connected', 'user-registration-mailchimp' );
		$add_class = 'ur-mailchimp-online';

	} elseif ( ! empty( $ur_mc_api_key ) && false == $ur_mc_api_key_validated ) {
		$output .= '<span class="validateStatus"><img src="' . URMC()->plugin_url() . '/assets/images/icon_error.png" title="' . __( 'Either the API key is not valid or we could not connect to MailChimp to validate it!', 'user-registration-mailchimp' ) . '"/></span>';
	} else {
		$output .= '<p class="description">' . __( 'Enter a MailChimp API key. You can create keys in your MailChimp account.', 'user-registration-mailchimp' ) . '</p>';
	}

	$output .= '</div>';
	$output .= '<br/>';

	$output .= '<div class="tablenav top"><div id="ur-mailchimp-api-status" class="alignleft"><label class="ur-mailchimp-status-label" for="ur-mailchimp-api-key-input"><strong>' . __( 'Status', 'user-registration-mailchimp' ) . '</strong></label><label class="ur-mailchimp-status ' . $add_class . ' " for="ur-mailchimp-api-key-input"><strong>' . $status . '</strong></label></div>';
	$output .= '<button title="' . __( 'Please remap fields after synchronization.', 'user-registration-mailchimp' ) . '"';
	$output .= 'style="float:right; margin-bottom:20px;" type="button" class="button" id="mailchimp-sync">' . __( 'Force synchronize with MailChimp', 'user-registration-mailchimp' );
	$output .= '</button>';
	$output .= '</div>';

	return $output;
}

/**
 * Form field mailchimp.
 *
 * @param string $path Path.
 *
 * @return string
 */
function urmc_form_field_mailchimp( $path ) {

	return URMC_ABSPATH . 'includes/form/class-ur-mailchimp.php';
}

/**
 * Get registered form fields.
 *
 * @param array $fields Fields.
 *
 * @return array
 */
function urmc_registered_form_fields( $fields ) {

	$field = 'mailchimp';

	if ( ! isset( $fields[ $field ] ) ) {

		array_push( $fields, $field );
	}

	return $fields;
}

/**
 * Checks is auto sync enable.
 *
 * @return boolean
 */
function urmc_is_auto_sync_enable() {

	return apply_filters( 'user_registration_mailchimp_is_auto_sync', true ); // auto sync enable feature.

}

/**
 * Get auto sync hour.
 *
 * @return mixed
 */
function urmc_get_auto_sync_hour() {

	return apply_filters( 'user_registration_mailchimp_auto_sync_hour', 24 ); // Mailchimp auto sync after each 24 hour.
}

/**
 * Get valid mailchimp list.
 *
 * @param int   $form_id Form Id.
 * @param array $valid_form_data Form Data.
 *
 * @return array
 */
function urmc_get_valid_mailchimp_list( $form_id, $valid_form_data ) {

	$post_content = ur_get_post_content( $form_id );

	$post_content_array = ur_get_form_data_by_key( $post_content, 'mailchimp' );

	$valid_mailchimp_lists = array(

		'selected_list'                 => array(),

		'not_selected'                  => array(),

		'sync_mailchimp_on_user_update' => array(),
	);

	foreach ( $post_content_array as $mailchimp_key => $mailchimp_data ) {

		if ( isset( $valid_form_data[ $mailchimp_key ] ) ) {

			$mailchimp_list                = isset( $mailchimp_data->advance_setting->mailchimp_list ) ? $mailchimp_data->advance_setting->mailchimp_list : '';
			$sync_mailchimp_on_user_update = isset( $mailchimp_data->advance_setting->sync_mailchimp_on_user_update ) ? $mailchimp_data->advance_setting->sync_mailchimp_on_user_update : '';

			$is_mailchimp_checked = isset( $valid_form_data[ $mailchimp_key ]->value ) ? $valid_form_data[ $mailchimp_key ]->value : '0';
			if ( ! empty( $mailchimp_list ) && $is_mailchimp_checked ) {

				$valid_mailchimp_lists['selected_list'][ $mailchimp_key ] = $mailchimp_list;

			} elseif ( ! empty( $mailchimp_list ) ) {

				$valid_mailchimp_lists['not_selected'][ $mailchimp_key ] = $mailchimp_list;
			}

			$valid_mailchimp_lists['sync_mailchimp_on_user_update'][ $mailchimp_key ] = $sync_mailchimp_on_user_update;
		}
	}
	return $valid_mailchimp_lists;
}
