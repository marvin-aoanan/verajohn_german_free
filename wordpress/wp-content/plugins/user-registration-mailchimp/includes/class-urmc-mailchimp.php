<?php
/**
 * UserRegistrationMailChimp MailChimpClass.
 *
 * @class    URMC_Frontend
 * @version  1.0.0
 * @package  UserRegistrationMailChimp
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class URMC_MailChimp
 */
class URMC_MailChimp {

	/**
	 * Holds valid mailchimp Lists.
	 *
	 * @var array
	 */
	private static $valid_mailchimp_lists = array();

	/**
	 * Initialization of Mailchimp.
	 */
	private static function init() {
		if ( ! class_exists( '\DrewM\MailChimp\MailChimp' ) ) {
			require_once URMC_ABSPATH . 'mailchimp-api/src/Batch.php';
			require_once URMC_ABSPATH . 'mailchimp-api/src/Webhook.php';
			require_once URMC_ABSPATH . 'mailchimp-api/src/MailChimp.php';
		}
	}

	/**
	 * Get Mailchimp List.
	 */
	public static function get_mailchimp_list() {

		global $wpdb;

		$result = $wpdb->get_results( 'SELECT list_id, list_title from ' . $wpdb->prefix . 'urmc_lists' );

		if ( count( $result ) > 0 ) {

			$list_result = array();

			foreach ( $result as $list_obj ) {

				$list_id                 = isset( $list_obj->list_id ) ? $list_obj->list_id : '';
				$list_title              = isset( $list_obj->list_title ) ? $list_obj->list_title : '';
				$list_result[ $list_id ] = $list_title;
			}

			return $list_result;

		} else {

			return array( 'List not found' );
		}
	}

	/**
	 * Check whether mailchimp load from cache.
	 *
	 * @return bool
	 */
	public static function is_load_from_cache() {

		$urmc_mailchimp_api_key_validated = get_option( 'urmc_mailchimp_api_key_validated', false );

		if ( false === $urmc_mailchimp_api_key_validated ) {

			return true;
		}
		if ( ! urmc_is_auto_sync_enable() ) {

			return true;
		}

		$last_sync_date_time = get_option( 'urmc_mailchimp_last_sync_datetime', 0 );

		if ( '0' === $last_sync_date_time ) {

			return false;

		}
		$current_time = gmdate( 'Y-m-d H:i:s' );

		$last_sync_time = gmdate( 'Y-m-d H:i:s', strtotime( $last_sync_date_time ) );

		$expire_hour = urmc_get_auto_sync_hour();

		$hour_diff = round( ( strtotime( $current_time ) - strtotime( $last_sync_time ) ) / 3600, 1 );

		return ( $hour_diff > $expire_hour ) ? false : true;

	}

	/**
	 * Mailchimp validate key and update.
	 *
	 * @param array $urmc_mailchimp_settings Settings.
	 * @param bool  $is_force_sync Is force sync.
	 *
	 * @throws Exception Could not connect to mailchimp.
	 */
	public static function validate_key_and_update( $urmc_mailchimp_settings, $is_force_sync = false ) {

		$api_key = isset( $urmc_mailchimp_settings['api_key'] ) ? $urmc_mailchimp_settings['api_key'] : '';

		$urmc_mailchimp_settings_option = get_option( 'urmc_mailchimp_settings' );

		$urmc_mailchimp_settings_option = $urmc_mailchimp_settings;

		if ( ! empty( $api_key ) ) {

			$urmc_mailchimp_settings_option['api_key'] = $api_key;
		}
		$is_load_from_cache = self::is_load_from_cache();

		$status = true;

		$check_if_first_time = get_option( 'urmc_mailchimp_last_sync_datetime', '-1' );

		if ( ! $is_load_from_cache || $is_force_sync || '-1' == $check_if_first_time ) {

			try {
				self::init();

				$mailchimp = new \DrewM\MailChimp\MailChimp( $api_key );

				$response = $mailchimp->get( '/' );

				$account_id = isset( $response['account_id'] ) ? $response['account_id'] : '';

				if ( empty( $account_id ) ) {

					throw  new Exception( 'May be api key invalid or could not connect to mailchimp.' );
				}
			} catch ( Exception $e ) {

				$status = false;

				$logger = ur_get_logger();

				$logger->error( $e->getMessage(), array( 'source' => 'ur-mailchimp' ) );

			}

			if ( ! empty( $account_id ) ) {

				self::update_mailchimp_data( $api_key );

			} else {

				$status = false;
			}
			$key_status = ! empty( $api_key ) ? $status : false;

			update_option( 'urmc_mailchimp_api_key_validated', $key_status );

		}

		update_option( 'urmc_mailchimp_settings', $urmc_mailchimp_settings_option );

		return $status;

	}

	/**
	 * Update Mailchimp Data.
	 *
	 * @param string $api_key API KEY.
	 */
	private static function update_mailchimp_data( $api_key ) {

		self::init();

		$mailchimp = new \DrewM\MailChimp\MailChimp( $api_key );

		$count = apply_filters( 'user_registration_mailchimp_list_count', 20 );

		$result_array = $mailchimp->get( 'lists?count=' . $count . '' );

		$parsed_lists = array();

		$lists = isset( $result_array['lists'] ) ? $result_array['lists'] : array();

		foreach ( $lists as $key => $list ) {

			$parsed_array = array();

			$list_id                       = isset( $list['id'] ) ? $list['id'] : '';
			$total_member                  = isset( $list['stats']['member_count'] ) ? $list['stats']['member_count'] : 0;
			$parsed_array['list_id']       = $list_id;
			$parsed_array['web_id']        = $list['web_id'];
			$parsed_array['list_title']    = $list['name'];
			$parsed_array['updated_date']  = gmdate( 'Y-m-d H:i:s' );
			$parsed_array['created_date']  = gmdate( 'Y-m-d H:i:s' );
			$parsed_array['total_members'] = $total_member;

			$merge_fields_response = $mailchimp->get(
				'lists/' . $list_id . '/merge-fields',
				array(
					'count' => 500,
				)
			);

			$merge_fields = isset( $merge_fields_response['merge_fields'] ) ? $merge_fields_response['merge_fields'] : array();

			$parsed_merge_fields = array();

			$has_email = false;

			$parsed_merge_fields[] = array(

				'merge_id' => 0,
				'tag'      => 'email_address',
				'name'     => 'Email Address',
				'type'     => 'email',
				'required' => true,

			);

			foreach ( $merge_fields as $field_key => $field_value ) {
				$single_merge_field = array();

				$single_merge_field['merge_id'] = $field_value['merge_id'];
				$single_merge_field['tag']      = $field_value['tag'];
				$single_merge_field['name']     = $field_value['name'];
				$single_merge_field['type']     = $field_value['type'];
				$single_merge_field['required'] = $field_value['required'];
				if ( false === $has_email && 'EMAIL' === $field_value['tag'] ) {

					$has_email = true;

				}
				array_push( $parsed_merge_fields, $single_merge_field );

			}
			if ( $has_email ) {

				unset( $parsed_merge_fields[0] );
			}

			$parsed_array['field_count'] = count( $parsed_merge_fields );

			$interest_list = $mailchimp->get(
				'lists/' . $list_id . '/interest-categories',
				array(
					'count'  => 500,
					'fields' => 'categories.id,categories.title,categories.type',
				)
			);

			if ( isset( $interest_list['categories'] ) && ! empty( $interest_list['categories'] ) ) {

				$group_details = array();

				foreach ( $interest_list['categories'] as $key => $value ) {
					$groups          = $mailchimp->get(
						'lists/' . $list_id . '/interest-categories/' . $value['id'] . '/interests',
						array(
							'count'  => 500,
							'fields' => 'interests.id,interests.name',
						)
					);
					$value['groups'] = $groups['interests'];
					array_push( $group_details, $value );
				}
				$parsed_merge_fields['interests'] = $group_details;
			}

			$parsed_array['list_fields'] = wp_json_encode( $parsed_merge_fields );

			array_push( $parsed_lists, $parsed_array );
		}

		if ( count( $parsed_lists ) > 0 ) {

			global $wpdb;

			$table = $wpdb->prefix . 'urmc_lists';

			$delete = $wpdb->query( "TRUNCATE TABLE $table" );

			if ( $delete ) {

				foreach ( $parsed_lists as $parsed_key => $single_parsed ) {
					$wpdb->insert(
						$table,
						array(
							'list_title'    => $single_parsed['list_title'],
							'list_id'       => $single_parsed['list_id'],
							'updated_date'  => $single_parsed['updated_date'],
							'created_date'  => $single_parsed['created_date'],
							'list_fields'   => $single_parsed['list_fields'],
							'total_members' => $single_parsed['total_members'],
							'field_count'   => $single_parsed['field_count'],
							'web_id'        => $single_parsed['web_id'],
						)
					);
				}
			}
		}

		update_option( 'urmc_mailchimp_last_sync_datetime', gmdate( 'Y-m-d H:i:s' ) );
	}

	/**
	 * Mailchimp parse with mapping data.
	 *
	 * @param array $valid_form_data Form Data.
	 * @param  bool  $is_mapped Check is Mapped.
	 * @param array $fields Field.
	 * @param int   $enable_double_optin Enable Double Option.
	 * @param int   $user_id UserId.
	 */
	private static function parse_with_mapping_data( $valid_form_data, $is_mapped, $fields = array(), $enable_double_optin = 0, $user_id ) {

		$email_obj = isset( $valid_form_data['user_email'] ) ? $valid_form_data['user_email'] : new stdClass();

		$user_email = isset( $email_obj->value ) ? $email_obj->value : '';
		if ( empty( $user_email ) ) {

			$logger = ur_get_logger();

			$logger->notice( 'Email not found on parse_with_mapping_data ', array( 'source' => 'ur-mailchimp' ) );

			return;
		}
		$enable_double_optin = ( absint( $enable_double_optin ) );

		$status      = $enable_double_optin ? 'pending' : 'subscribed';
		$parsed_data = array(
			'email_address' => $user_email,
			'status'        => $status,
			'merge_fields'  => array(),
		);

		if ( $is_mapped ) {

			foreach ( $fields as $mailchimp_key => $form_field ) {

				if ( 'email_address' !== $mailchimp_key ) {
					$value_object = isset( $valid_form_data[ $form_field ] ) ? $valid_form_data[ $form_field ] : new stdClass();

					$value = isset( $value_object->value ) ? $value_object->value : '';

					if ( ! empty( $value ) && '-1' !== $value ) {
						$field_object = (object) $value_object->extra_params;

						if ( 'date' === $field_object->field_key && '' !== $value ) {
							$date_format = get_option( 'user_registration_' . $form_field . '_date_format' );
							$date        = '' !== $date_format ? DateTime::createFromFormat( $date_format, $value ) : DateTime::createFromFormat( 'Y-m-d', $value );
							$value       = $date->format( 'm/d' );
						}
						$parsed_data['merge_fields'][ $mailchimp_key ] = $value;
					}
				}
			}
		} else {

			$first_name = get_user_meta( $user_id, 'first_name', true );

			$last_name = get_user_meta( $user_id, 'last_name', true );

			if ( ! empty( $first_name ) ) {
				$parsed_data['merge_fields']['FNAME'] = $first_name;
			}
			if ( ! empty( $last_name ) ) {
				$parsed_data['merge_fields']['LNAME'] = $last_name;
			}
		}

		return $parsed_data;

	}

	/**
	 * Send Data to Mailchimp.
	 *
	 * @param array $valid_mailchimp_lists_array Mailchimp List Array.
	 * @param array $valid_form_data Form Data.
	 * @param int   $form_id Form Id.
	 * @param int   $user_id User Id.
	 *
	 * @throws Exception Throws exception according to conditions.
	 */
	public static function send_data( $valid_mailchimp_lists_array, $valid_form_data, $form_id, $user_id ) {

		$mailchimp_settings = get_option( 'urmc_mailchimp_settings', array() );

		$mailchimp_mapping_data = isset( $mailchimp_settings['data'] ) ? $mailchimp_settings['data'] : array();

		foreach ( $valid_mailchimp_lists_array as $mailchimp_list_key => $list ) {

			try {
				$mapped_form_data = isset( $mailchimp_mapping_data[ $list ] ) ? $mailchimp_mapping_data[ $list ] : array();

				$option_form_id = isset( $mapped_form_data['form_id'] ) ? $mapped_form_data['form_id'] : 0;

				$is_mapped = absint( $option_form_id ) === absint( $form_id ) ? true : false;

				$fields = isset( $mapped_form_data['fields'] ) ? $mapped_form_data['fields'] : array();

				$enable_double_optin = isset( $mapped_form_data['enable_double_optin'] ) ? $mapped_form_data['enable_double_optin'] : 0;

				$valid_data = self::parse_with_mapping_data( $valid_form_data, $is_mapped, $fields, $enable_double_optin, $user_id );

				$api_key = isset( $mailchimp_settings['api_key'] ) ? $mailchimp_settings['api_key'] : '';

				$is_valid_api_key = get_option( 'urmc_mailchimp_api_key_validated', false );

				if ( ! $is_valid_api_key ) {

					throw  new Exception( __( 'Invalid API key.', 'user-registration-mailchimp' ) );
				}
				if ( empty( $api_key ) ) {

					throw  new Exception( __( 'API key not found.', 'user-registration-mailchimp' ) );
				}
				if ( ! isset( $valid_data['email_address'] ) ) {

					throw  new Exception( __( 'Email address not found.', 'user-registration-mailchimp' ) );

				}
				if ( empty( $valid_data['email_address'] ) ) {

					throw  new Exception( __( 'Empty email address found.', 'user-registration-mailchimp' ) );

				}
				$member_id = md5( strtolower( $valid_data['email_address'] ) );

				self::init();

				if ( count( $valid_data['merge_fields'] ) < 1 ) {
					unset( $valid_data['merge_fields'] );
				}

				if ( ! empty( $mapped_form_data['group'] ) ) {

					$groups = array();

					foreach ( $mapped_form_data['group'] as $id => $segments ) {
						if ( is_array( $segments ) ) {
							foreach ( $segments as $id => $group ) {
								$groups[ $group ] = true;
							}
						} else {
							$groups[ $segments ] = true;
						}
					}

					if ( ! empty( $groups ) ) {
						$valid_data['interests'] = $groups;
					}
				}

				$mailchimp = new \DrewM\MailChimp\MailChimp( $api_key );

				$result_array = $mailchimp->put( 'lists/' . $list . '/members/' . $member_id, $valid_data );

				$result = (object) $result_array;

				$id = isset( $result->id ) ? $result->id : '';

				if ( empty( $id ) ) {
					self::mailchimp_error_handler( $result, $list );
				} else {
					self::$valid_mailchimp_lists [ $mailchimp_list_key ] = $list;
				}
			} catch ( Exception $e ) {

				$logger = ur_get_logger();

				$logger->error( $e->getMessage(), array( 'source' => 'ur-mailchimp' ) );

				return false;

			}
		}
		if ( count( self::$valid_mailchimp_lists ) > 0 ) {

			update_user_meta( $user_id, 'urmc_subscribe_mailchimp_list', wp_json_encode( self::$valid_mailchimp_lists ) );
		}

	}

	/**
	 * Mailchimp Error Handler.
	 *
	 * @param mixed  $result Result.
	 * @param string $list List.
	 *
	 * @throws Exception Throws exception according to conditions.
	 */
	private static function mailchimp_error_handler( $result, $list ) {

		$error_status = isset( $result->status ) ? $result->status : '';

		$detail = isset( $result->detail ) ? $result->detail : '';

		$errors = isset( $result->errors ) ? $result->errors : array();

		if ( empty( $error_status ) && empty( $detail ) ) {

			throw  new Exception( 'Something wrong while connecting mailchimp list(' . $list . ').' );

		}

		if ( count( $errors ) < 1 || ! is_array( $errors ) ) {

			throw  new Exception( $detail . ' - list(' . $list . ').' );

		}
		$is_unknown_error = true;

		$logger = ur_get_logger();

		foreach ( $errors as $error ) {

			$field = isset( $error['field'] ) ? $error['field'] : '';

			$message = isset( $error['message'] ) ? $error['message'] : '';

			if ( ! empty( $field ) || ! empty( $message ) ) {

				$is_unknown_error = false;

				$logger->error( $field . ' ' . $message . ' - list(' . $list . ').', array( 'source' => 'ur-mailchimp' ) );
			}
		}
		if ( $is_unknown_error ) {

			throw  new Exception( __( 'Unknown error on mailchimp - list(' . $list . ').', 'user-registration-mailchimp' ) );

		}
	}

	/**
	 * Subscribe to Mailchimp.
	 *
	 * @param int    $user_id UserId.
	 * @param string $list_key List Key.
	 * @param int    $form_id Form Id.
	 * @param array  $valid_form_data Form Data.
	 * @param mixed  $list_index List index.
	 */
	public static function subscribe( $user_id, $list_key, $form_id, $valid_form_data, $list_index ) {

		try {
			$mailchimp_settings               = get_option( 'urmc_mailchimp_settings', array() );
			$api_key                          = isset( $mailchimp_settings['api_key'] ) ? $mailchimp_settings['api_key'] : 0;
			$urmc_mailchimp_api_key_validated = get_option( 'urmc_mailchimp_api_key_validated', false );
			if ( ! $urmc_mailchimp_api_key_validated || empty( $api_key ) ) {

				$logger = ur_get_logger();
				$logger->error( 'Invalid api key or key not validated yet.', array( 'source' => 'ur-mailchimp' ) );

				return;
			}
			$mailchimp_data         = isset( $mailchimp_settings['data'] ) ? $mailchimp_settings['data'] : array();
			$mapping_data_by_list   = isset( $mailchimp_data[ $list_key ] ) ? $mailchimp_data[ $list_key ] : array();
			$mapping_form_id        = isset( $mapping_data_by_list['form_id'] ) ? $mapping_data_by_list['form_id'] : 0;
			$mapping_fields         = isset( $mapping_data_by_list['fields'] ) ? $mapping_data_by_list['fields'] : array();
			$is_double_opt_in       = isset( $mapping_data_by_list['enable_double_optin'] ) && '1' === $mapping_data_by_list['enable_double_optin'] ? true : false;
			$user                   = get_userdata( $user_id );
			$user_data              = isset( $user->data ) ? $user->data : new stdClass();
			$form_fields_from_table = array(
				'user_login'   => 'user_login',
				'user_email'   => 'user_email',
				'user_url'     => 'user_url',
				'display_name' => 'display_name',
			);
			$form_fields_from_meta  = array(
				'nickname'    => 'nickname',
				'description' => 'description',
				'first_name'  => 'first_name',
				'last_name'   => 'last_name',
			);
			$all_fields_data        = array();

			$parsed_data = array(
				'email_address' => $user_data->user_email,
				'status'        => $is_double_opt_in ? 'pending' : 'subscribed',
				'merge_fields'  => array(),
			);
			if ( $mapping_form_id === $form_id && count( $mapping_fields ) > 0 ) {

				$mapping_form_fields = array_values( $mapping_fields );

				foreach ( $mapping_form_fields as $field ) {
					if ( isset( $form_fields_from_table [ $field ] ) ) {

						$field_index = $form_fields_from_table[ $field ];

						$all_fields_data[ $field ] = $user_data->$field_index;

					} elseif ( isset( $form_fields_from_meta [ $field ] ) ) {

						$all_fields_data[ $field ] = get_user_meta( $user_id, $form_fields_from_meta[ $field ], true );
					} else {

						$all_fields_data[ $field ] = get_user_meta( $user_id, 'user_registration_' . $field, true );

					}
				}

				if ( isset( $mapping_fields['email_address'] ) ) {
					unset( $mapping_fields['email_address'] );
				}
				foreach ( $mapping_fields as $field_key => $field_value ) {

					if ( isset( $all_fields_data[ $field_value ] ) ) {
						$parsed_data['merge_fields'][ $field_key ] = $all_fields_data[ $field_value ];
					}
				}
			}

			if ( count( $parsed_data['merge_fields'] ) < 1 ) {

				unset( $parsed_data['merge_fields'] );

			}

			self::init();

			$member_id = md5( strtolower( $parsed_data['email_address'] ) );

			$mailchimp = new \DrewM\MailChimp\MailChimp( $api_key );

			$result_array = $mailchimp->put( 'lists/' . $list_key . '/members/' . $member_id, $parsed_data );

			$result = (object) $result_array;

			$id = isset( $result->id ) ? $result->id : '';

			$user_subscriber_lists = json_decode( get_user_meta( $user_id, 'urmc_subscribe_mailchimp_list', true ), true );

			if ( empty( $id ) ) {
				self::mailchimp_error_handler( $result, $list_key );
			} else {
				if ( is_array( $user_subscriber_lists ) ) {

					$user_subscriber_lists[ $list_index ] = $list_key;

					update_user_meta( $user_id, 'urmc_subscribe_mailchimp_list', wp_json_encode( $user_subscriber_lists ) );

				}
			}
		} catch ( Exception $exception ) {

			$logger = ur_get_logger();

			$logger->error( $exception->getMessage(), array( 'source' => 'ur-mailchimp' ) );

			return false;

		}
	}

	/**
	 * Unsubcribe the user from Mailchimp.
	 *
	 * @param int    $user_id UserId.
	 * @param string $list_key List Key.
	 * @param int    $form_id Form Id.
	 * @param array  $valid_form_data Form Data.
	 * @param mixed  $list_index List index.
	 */
	public static function unsubscribe( $user_id, $list_key, $form_id, $valid_form_data, $list_index ) {
		try {
			$mailchimp_settings               = get_option( 'urmc_mailchimp_settings', array() );
			$api_key                          = isset( $mailchimp_settings['api_key'] ) ? $mailchimp_settings['api_key'] : 0;
			$urmc_mailchimp_api_key_validated = get_option( 'urmc_mailchimp_api_key_validated', false );
			if ( ! $urmc_mailchimp_api_key_validated || empty( $api_key ) ) {

				$logger = ur_get_logger();
				$logger->error( 'Invalid api key or key not validated yet.', array( 'source' => 'ur-mailchimp' ) );

				return;
			}
			$user      = get_userdata( $user_id );
			$user_data = isset( $user->data ) ? $user->data : new stdClass();

			self::init();

			$member_id = md5( strtolower( $user_data->user_email ) );

			$mailchimp = new \DrewM\MailChimp\MailChimp( $api_key );

			$result_array = $mailchimp->delete( 'lists/' . $list_key . '/members/' . $member_id );

			$result = (object) $result_array;

			$error_status = isset( $result->status ) ? $result->status : '';

			$user_subscriber_lists = json_decode( get_user_meta( $user_id, 'urmc_subscribe_mailchimp_list', true ), true );

			if ( ! empty( $error_status ) ) {
				self::mailchimp_error_handler( $result, $list_key );
			} else {
				if ( isset( $user_subscriber_lists[ $list_index ] ) ) {

					unset( $user_subscriber_lists[ $list_index ] );

					update_user_meta( $user_id, 'urmc_subscribe_mailchimp_list', wp_json_encode( $user_subscriber_lists ) );

				}
			}
		} catch ( Exception $exception ) {

			$logger = ur_get_logger();

			$logger->error( $exception->getMessage(), array( 'source' => 'ur-mailchimp' ) );

			return false;

		}
	}


}
