<?php
/**
 * Abstract UR Setting MailChimp Class
 *
 * @version  1.0.0
 * @package  UserRegistrationMailChimp/Form/Settings
 * @category Abstract Class
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Setting_MailChimp class.
 */
class UR_Setting_MailChimp extends UR_Field_Settings {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->field_id = 'mailchimp_advance_setting';

	}

	/**
	 * Output.
	 *
	 * @param array $field_data Field Data.
	 */
	public function output( $field_data = array() ) {

		// TODO: Implement output() method.
		$this->field_data = $field_data;

		$this->register_fields();

		$field_html = $this->fields_html;

		return $field_html;
	}

	/**
	 * Register Fields.
	 */
	public function register_fields() {

		$list = URMC_MailChimp::get_mailchimp_list();

		$array_key = array_keys( $list );

		$default = isset( $array_key[0] ) ? $array_key[0] : 0;

		// TODO: Implement register_fields() method.
		$fields = array(

			'mailchimp_list'                => array(

				'label'       => __( 'MailChimp List', 'user-registration-mailchimp' ),

				'id'          => $this->field_id . '_mailchimp_list',

				'name'        => $this->field_id . '[mailchimp_list]',

				'class'       => $this->default_class . ' ur-settings-mailchimp-list',

				'type'        => 'select',

				'required'    => true,

				'default'     => $default,

				'options'     => $list,

				'placeholder' => '',

				'tip'         => __( 'Which list do you want to use?', 'user-registration-mailchimp' ),
			),
			'auto_check_list'               => array(

				'label'       => __( 'Auto check list', 'user-registration-mailchimp' ),

				'id'          => $this->field_id . '_auto_check_list',

				'name'        => $this->field_id . '[auto_check_list]',

				'class'       => $this->default_class . ' ur-settings-auto-check-list',

				'type'        => 'select',

				'required'    => true,

				'default'     => 'yes',

				'options'     => array(

					'yes' => 'Yes',
					'no'  => 'No',
				),
				'placeholder' => '',

				'tip'         => __( 'Enabling this option will check the MailChimp field by default in the frontend form.', 'user-registration-mailchimp' ),
			),
			'sync_mailchimp_on_user_update' => array(

				'label'       => __( 'Auto sync on user update', 'user-registration-mailchimp' ),

				'id'          => $this->field_id . '_sync_mailchimp_on_user_update',

				'name'        => $this->field_id . '[sync_mailchimp_on_user_update]',

				'class'       => $this->default_class . ' ur-settings-sync-mailchimp-on-user-update',

				'type'        => 'select',

				'required'    => true,

				'default'     => true,

				'options'     => array(

					'yes' => 'Yes',
					'no'  => 'No',
				),
				'placeholder' => '',

				'tip'         => __( 'Whether to automatically synchronize the user profile details updated in the Mailchimp account', 'user-registration-mailchimp' ),
			),

		);

		$this->render_html( $fields );
	}
}

return new UR_Setting_MailChimp();
