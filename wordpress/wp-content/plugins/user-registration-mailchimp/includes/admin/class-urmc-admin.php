<?php
/**
 * UserRegistrationMailChimp Admin.
 *
 * @class    URMC_Admin
 * @version  1.0.0
 * @package  UserRegistrationMailChimp/Admin
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URMC_Admin Class
 */
class URMC_Admin {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		urmc_check_plugin_compatibility();
		$message = urmc_is_compatible();
		if ( 'YES' !== $message ) {
			return;
		}
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'admin_init', array( $this, 'admin_redirects' ) );
		add_action( 'admin_init', array( $this, 'auto_sync_with_mailchimp' ) );
		add_action( 'admin_menu', array( $this, 'add_mailchimp_menu' ), 65 );
		add_filter( 'user_registration_form_field_mailchimp_path', 'urmc_form_field_mailchimp', 10, 1 );

		add_filter(
			'user_registration_mailchimp_admin_template',
			array(
				$this,
				'user_registration_mailchimp_admin_template',
			),
			10,
			1
		);
		add_filter( 'user_registration_registered_form_fields', 'urmc_registered_form_fields', 10, 1 );
		add_filter(
			'user_registration_mailchimp_advance_class',
			array(
				$this,
				'urmc_mailchimp_advance_class',
			),
			10,
			1
		);
		add_action(
			'user_registration_after_admin_save_profile_validation',
			array(
				$this,
				'user_registration_after_admin_save_profile_validation',
			),
			10,
			2
		);

	}

	/**
	 * Sync User with mailchimp update user from admin side.
	 *
	 * @param int   $user_id User ID.
	 * @param array $profile Form Details.
	 */
	public function user_registration_after_admin_save_profile_validation( $user_id, $profile ) {

		$user_subscribe_list = get_user_meta( $user_id, 'urmc_subscribe_mailchimp_list', true );

		$user_subscribe_list_array = json_decode( $user_subscribe_list, true );

		$form_id = get_user_meta( $user_id, 'ur_form_id', true );

		$valid_form_data     = array();
		$mailchimp_field_key = '';
		foreach ( $profile as $field_key => $fields ) {
			if ( 'mailchimp' === $fields['field_key'] ) {
				$mailchimp_field_key = $field_key;
			}
		}

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
			} else {
				if ( '' !== $mailchimp_field_key && ! isset( $_POST[ $mailchimp_field_key ] ) ) {
					$new_string_key                                   = substr_replace( $mailchimp_field_key, '', $pos, strlen( 'user_registration_' ) );
					$valid_form_data[ $new_string_key ]               = new stdClass();
					$valid_form_data[ $new_string_key ]->value        = '';
					$valid_form_data[ $new_string_key ]->field_type   = $profile[ $mailchimp_field_key ]['type'];
					$valid_form_data[ $new_string_key ]->label        = $profile[ $mailchimp_field_key ]['label'];
					$valid_form_data[ $new_string_key ]->field_name   = $new_string_key;
					$valid_form_data[ $new_string_key ]->extra_params = array(
						'field_key' => $profile[ $mailchimp_field_key ]['field_key'],
						'label'     => $profile[ $mailchimp_field_key ]['label'],
					);

				}

				$key        = 'email' === $post_key ? 'user_email' : $post_key;
				$field_data = 'user_registration_' . $key;
				$data       = isset( $profile[ $field_data ] ) ? $profile[ $field_data ] : array();

				if ( ! empty( $data ) ) {
					$valid_form_data[ $key ]               = new stdClass();
					$valid_form_data[ $key ]->value        = $post_data;
					$valid_form_data[ $key ]->field_type   = $profile[ $field_data ]['type'];
					$valid_form_data[ $key ]->label        = $profile[ $field_data ]['label'];
					$valid_form_data[ $key ]->field_name   = $key;
					$valid_form_data[ $key ]->extra_params = array(
						'field_key' => $profile[ $field_data ]['field_key'],
						'label'     => $profile[ $field_data ]['label'],
					);
				}
			}
		}

		if ( count( $valid_form_data ) < 1 ) {
			return;
		}

		$valid_mailchimp_lists_array = urmc_get_valid_mailchimp_list( $form_id, $valid_form_data );

		$selected_mailchimp            = $valid_mailchimp_lists_array['selected_list'];
		$unselected_mailchimp          = $valid_mailchimp_lists_array['not_selected'];
		$sync_mailchimp_on_user_update = $valid_mailchimp_lists_array['sync_mailchimp_on_user_update'];

		$valid_mailchimp_lists = array_merge( $selected_mailchimp, $unselected_mailchimp );

		if ( ! empty( $valid_mailchimp_lists ) ) {

			foreach ( $valid_mailchimp_lists as $list_key => $list ) {

				if ( isset( $valid_form_data[ $list_key ] ) && 'yes' === $sync_mailchimp_on_user_update[ $list_key ] ) {

					$is_mailchimp_subscribed_from_form = '1' == $valid_form_data[ $list_key ]->value ? true : false;

					$is_mailchimp_already_subscribed = isset( $user_subscribe_list_array[ $list_key ] ) ? true : false;

					if ( $is_mailchimp_already_subscribed && ! $is_mailchimp_subscribed_from_form ) {

						URMC_MailChimp::unsubscribe( $user_id, $user_subscribe_list_array[ $list_key ], $form_id, $valid_form_data, $list_key );

					} elseif ( $is_mailchimp_subscribed_from_form && count( $selected_mailchimp ) > 0 ) {

						URMC_MailChimp::send_data( $valid_mailchimp_lists, $valid_form_data, $form_id, $user_id );
					}
				}
			}
		}
	}

	/**
	 * Auto Sync With Mailchimp
	 */
	public function auto_sync_with_mailchimp() {

		$load_from_cache = URMC_MailChimp::is_load_from_cache();

		if ( $load_from_cache ) {

			return;
		}
		if ( false === $load_from_cache ) {

			$urmc_mailchimp_settings_option = get_option( 'urmc_mailchimp_settings', array() );
			$status                         = URMC_MailChimp::validate_key_and_update( $urmc_mailchimp_settings_option, true );

			$logger = ur_get_logger();

			if ( $status ) {

				$logger->notice( 'Successfully synchronize with mailchimp via MAILCHIMP AUTOSYNC of ur mailcimp.', array( 'source' => 'ur-mailchimp' ) );

			} else {
				$logger->warning( 'Could not synchronize with mailchimp via MAILCHIMP AUTOSYNC of ur mailcimp.', array( 'source' => 'ur-mailchimp' ) );
			}
		}
	}

	/**
	 * Mailchimp Advance class.
	 *
	 * @param array $file_data File Data.
	 */
	public function urmc_mailchimp_advance_class( $file_data ) {

		$path = URMC_ABSPATH . 'includes/form/settings/class-ur-setting-mailchimp.php';

		$file_data['file_path'] = $path;

		return $file_data;

	}

	/**
	 * Mailchimp Admin Template.
	 *
	 * @param string $path Path.
	 *
	 * @return string
	 */
	public function user_registration_mailchimp_admin_template( $path ) {

		$path = URMC_ABSPATH . 'includes/form/views/admin/admin-mailchimp.php';

		return $path;

	}

	/**
	 * Add Mailchimp Menu.
	 */
	public function add_mailchimp_menu() {

		add_submenu_page(
			'user-registration',
			__( 'User Registration MailChimp', 'user-registration-mailchimp' ),
			__( 'MailChimp', 'user-registration-mailchimp' ),
			'manage_user_registration',
			'user-registration-mailchimp',
			array(
				$this,
				'mailchimp_page',
			)
		);
	}

	/**
	 *  Init the mailchimp page.
	 */
	public function mailchimp_page() {
		URMC_Admin_MailChimp::output();
		$this->admin_scripts();

	}

	/**
	 * Handle redirects to setup/welcome page after install and updates.
	 *
	 * For setup wizard, transient must be present, the user must have access rights, and we must ignore the network/bulk plugin updaters.
	 */
	public function admin_redirects() {
		// Setup wizard redirect.
		if ( get_transient( '_urmc_activation_redirect' ) ) {
			delete_transient( '_urmc_activation_redirect' );

			if ( ( is_network_admin() || isset( $_GET['activate-multi'] ) ) || ! current_user_can( 'manage_options' ) || apply_filters( 'urmc_prevent_activation_redirect', false ) ) {
				return;
			}

			$message = urmc_is_compatible();
			if ( 'YES' === $message ) {
				// If the user needs to install, send them to the settings page.
				wp_safe_redirect( admin_url( 'admin.php?page=user-registration-mailchimp' ) );
				exit;
			}
		}
	}

	/**
	 * Admin Scripts.
	 */
	public function admin_scripts() {

		$screen = get_current_screen();

		$screen_id = $screen ? $screen->id : '';

		$valid_screens = array( 'user-registration_page_user-registration-mailchimp' );

		if ( in_array( $screen_id, $valid_screens ) ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_register_script(
				'user-registration-mailchimp-admin',
				URMC()->plugin_url() . '/assets/js/admin/user-registration-mailchimp-admin' . $suffix . '.js',
				array(
					'jquery',
				),
				URMC_VERSION
			);
		}
	}

	/**
	 * Admin Styles.
	 */
	public function admin_styles() {

		wp_register_style( 'user-registration-mailchimp-admin-style', URMC()->plugin_url() . '/assets/css/user-registration-mailchimp-admin-style.css', array(), URMC_VERSION );

		wp_enqueue_style( 'user-registration-mailchimp-admin-style' );

	}

}

return new URMC_Admin();
