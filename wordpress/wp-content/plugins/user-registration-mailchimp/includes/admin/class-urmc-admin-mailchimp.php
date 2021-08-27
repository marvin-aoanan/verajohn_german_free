<?php
/**
 * MailChimp Page
 *
 * @author   WPEverest
 * @category Admin
 * @package  UserRegistrationMailChimp/Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URMC_Admin_MailChimp Class.
 */
class URMC_Admin_MailChimp {

	/**
	 * Handles output of the addons page in admin.
	 */
	public static function output() {

		self::urmc_save_settings();

		wp_enqueue_script( 'user-registration-mailchimp-admin' );

			wp_localize_script(
				'user-registration-mailchimp-admin',
				'urmc_script_data',
				array(
					'ajax_url'             => admin_url( 'admin-ajax.php' ),
					'i18n_admin'           => array(
						'ignore_this_field'        => __( 'Ignore this field', 'user-registration-mailchimp' ),
						'synchronizing'            => __( 'Synchronizing with mailchimp....', 'user-registration-mailchimp' ),
						'synchronization_complete' => __( 'Synchronization completed.', 'user-registration-mailchimp' ),
						'could_not_sychronize'     => __( 'Could not synchronize with mailchimp, please check log.', 'user-registration-mailchimp' ),
						'something_wrong'          => __( 'Unknown error, please try again.', 'user-registration-mailchimp' ),
						'empty_api'                => __( 'Please enter mailchimp API.', 'user-registration-mailchimp' ),
						'enable_double_optin'      => __( 'Enable Double Opt-In', 'user-registration-mailchimp' ),
					),
					'mapping_fields'       => get_option( 'urmc_mailchimp_settings' ),
					'mailchimp_sync_nonce' => wp_create_nonce( 'mailchimp-synchronization' ),
				)
			);
		?>
		<div class="wrap urmc-wrap urmc-wrap-mailchimp">
			<form method="post">
				<?php
				self::urmc_page_content();
				$urmc_mailchimp_settings          = get_option( 'urmc_mailchimp_settings' );
				$ur_mc_api_key                    = isset( $urmc_mailchimp_settings['api_key'] ) ? $urmc_mailchimp_settings['api_key'] : '';
				$urmc_mailchimp_api_key_validated = get_option( 'urmc_mailchimp_api_key_validated', false );
				$urmc_mailchimp_api_key_validated = (bool) $urmc_mailchimp_api_key_validated;
				self::urmc_page_table_list();
				?>
				<input style="margin-top:20px;" type="submit" class="button button-primary"
					value="<?php echo esc_attr__( 'Save', 'user-registration-mailchimp' ); ?>"/>
			</form>
		</div>
		<?php

	}

	/**
	 * Save Mailchimp Settings
	 */
	private static function urmc_save_settings() {

		$_wpnonce = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';

		$flag = wp_verify_nonce( $_wpnonce, 'urmc_setting_save_nonce' );

		if ( true != $flag || is_wp_error( $flag ) ) {
			UR_Admin_Notices::remove_notice( 'urmc-mailchimp-sync' );
			return;
		}

		$urmc_mailchimp_settings = isset( $_POST['urmc_mailchimp_settings'] ) ? $_POST['urmc_mailchimp_settings'] : array();

		if ( count( $urmc_mailchimp_settings ) < 1 ) {
			return;
		}
		$urmc_mailchimp_settings = self::urmc_sanitize_array( $urmc_mailchimp_settings );

		$flag = URMC_MailChimp::validate_key_and_update( $urmc_mailchimp_settings );

		if ( $flag ) {
			self::urmc_notices( __( 'Setting successfully saved.', 'user-registration-mailchimp' ) );
		} else {
			self::urmc_notices( __( 'Could not save setting, may be api key missing or could not connect to mailchimp.', 'user-registration-mailchimp' ), 'error' );
		}
	}

	/**
	 * Mailchimp status Notice.
	 *
	 * @param mixed  $notice Notice.
	 * @param string $type Type.
	 */
	public static function urmc_notices( $notice, $type = '' ) {

		$type = empty( $type ) ? 'updated' : $type;
		?>
		<div id="message" class="<?php echo esc_attr__( $type, 'user-registration-mailchimp' ); ?>">
			<?php echo wp_kses_post( wpautop( $notice ) ); ?>
		</div>
		<?php
	}

	/**
	 * Sanitize Array Field.
	 *
	 * @param array $data Data.
	 *
	 * @return array
	 */
	public static function urmc_sanitize_array( $data = array() ) {
		if ( ! is_array( $data ) || ! count( $data ) ) {
			return array();
		}
		foreach ( $data as $k => $v ) {
			if ( ! is_array( $v ) && ! is_object( $v ) ) {
				$data[ $k ] = sanitize_text_field( trim( $v ) );
			}
			if ( is_array( $v ) ) {
				$data[ $k ] = self::urmc_sanitize_array( $v );
			}
		}

		return $data;
	}

	/**
	 * Mailchimp Page Content.
	 */
	public static function urmc_page_content() {

		$urmc_mailchimp_settings = get_option( 'urmc_mailchimp_settings' );

		$urmc_mailchimp_api_key_validated = get_option( 'urmc_mailchimp_api_key_validated', false );

		?>


		<?php echo wp_nonce_field( 'urmc_setting_save_nonce' ); ?>

		<h2><?php esc_html__( 'MailChimp Integration', 'user-registration-mailchimp' ); ?></h2>

		<?php echo urmc_get_mailchimp_api_key_content( $urmc_mailchimp_settings ); ?>

		<?php

		// Check to see if a API key has been set.
		if ( ! isset( $urmc_mailchimp_settings['api_key'] ) || empty( $urmc_mailchimp_settings['api_key'] ) ) {

			echo '<p>' . esc_html__( 'Before you can make any changes you will need to add a MailChimp API key.', 'user-registration-mailchimp' ) . '</p>';

			// If everything goes well let's show some options.
		}

	}

	/**
	 * Mailchimp List table.
	 */
	private static function urmc_page_table_list() {
		?>
		<table id="urmc-list-table" class="wp-list-table widefat fixed striped posts">
			<thead>
			<tr>
				<?php echo self::list_header(); ?>

			</tr>
			</thead>
			<tbody id="the-list">
			<?php echo self::list_body(); ?>
			</tbody>
			<tfoot>
			<tr>

				<?php echo self::list_header(); ?>
			</tr>
			</tfoot>
		</table>
		<?php
	}

	/**
	 * Form Fields except mailchimp.
	 *
	 * @param array $post_content Post Content.
	 *
	 * @return array
	 */
	private static function form_field_array( $post_content ) {

		$form_field_array = array();

		$post_content_array = json_decode( $post_content );

		if ( ! empty( $post_content_array ) ) {

			foreach ( $post_content_array as $key => $content ) {

				foreach ( $content as $content_key => $field_value ) {

					foreach ( $field_value as $single_key => $single_field ) {

						$general_setting = isset( $single_field->general_setting ) ? $single_field->general_setting : new stdClass();

						$field_key = isset( $single_field->field_key ) ? $single_field->field_key : '';

						if ( 'mailchimp' !== $field_key ) {

							$label = isset( $general_setting->label ) ? $general_setting->label : '';

							$field_name = isset( $general_setting->field_name ) ? $general_setting->field_name : '';

							if ( ! empty( $field_name ) ) {

								$form_field_array[ $field_name ] = ! empty( $label ) ? $label : $field_name;

							}
						}
					}
				}
			}
		}

		return $form_field_array;
	}

	/**
	 * Get Mailchimp List Body.
	 *
	 * @return string
	 */
	private static function list_body() {

		global $wpdb;

		$args = array(
			'post_type' => 'user_registration',
			'status'    => 'publish',

		);
		$posts_array = get_posts( $args );

		$post_node = '';

		$post_node .= '<select class="urmc-form-field-node" name="URMC_FORM_ID"><option value="-1">' . __( 'Select form', 'user-registration-mailchimp' ) . '</option>';

		foreach ( $posts_array as $post ) {

			$post_content = self::form_field_array( ( $post->post_content ) );

			$encoded_content = json_encode( $post_content );

			$post_node .= '<option value="' . $post->ID . '" data-fields="' . base64_encode( $encoded_content ) . '">' . $post->post_title . '</option>';
		}

		$post_node .= '</select>';

		$list_array = self::list_array();

		$list_array_keys = array_keys( $list_array );

		$select_fields = join( ',', $list_array_keys );

		if ( empty( $select_fields ) ) {
			$select_fields = '*';
		}

		$table = $wpdb->prefix . 'urmc_lists';

		$result_body = $wpdb->get_results( "SELECT {$select_fields} FROM {$table}" );

		$body_node = '';

		$list_fields = array();

		foreach ( $result_body as $result ) {

			$body_node .= '<tr class="urmc-list-settings" style="display: table-row;">';

			$list_header = self::list_array();

			$list_node = '';

			foreach ( $list_header as $list_key => $list_value ) {

				$label = isset( $result->$list_key ) ? $result->$list_key : $list_key;

				$is_hidden = isset( $list_value['is_hidden'] ) ? $list_value['is_hidden'] : false;

				if ( ! $is_hidden ) {

					$list_node .= '<td   class="manage-column' . $list_key . '"><span>' . $label . '</span></td>';

				}
			}
			$list_field_string = isset( $result->list_fields ) ? $result->list_fields : '{}';

			$list_fields = json_decode( $list_field_string );

			$body_node .= $list_node;

			$body_node .= '</tr>';

			$body_node .= '<tr class="urmc-list-settings" style="display: table-row;">';

			$body_node .= self::get_mapping_data( $list_array, $result, $post_node, $list_fields );

			$body_node .= '</tr>';

		}

		if ( count( $result_body ) < 1 ) {

			$total_visible_list = count( self::get_visible_list( $list_array ) );

			$body_node .= '<tr>';

			$body_node .= '<td colspan="' . $total_visible_list . '">' . __( 'No List found.', 'user-registration-mailchimp' ) . '</td>';

			$body_node .= '</tr>';
		}

		return $body_node;

	}

	/**
	 * Get Visibile Mailchimp List.
	 *
	 * @param array $list_array List.
	 *
	 * @return array
	 */
	private static function get_visible_list( $list_array ) {

		$visible_lists = array();
		foreach ( $list_array as $list_key => $list ) {

			$is_hidden = isset( $list['is_hidden'] ) && $list['is_hidden'] ? true : false;

			if ( ! $is_hidden ) {

				$visible_lists[ $list_key ] = $list;
			}
		}

		return $visible_lists;

	}

	/**
	 * Get Mailchimp Mapping Data.
	 *
	 * @param array  $list_array Lists.
	 * @param mixed  $result Result.
	 * @param string $post_node Post Node.
	 * @param array  $list_fields List Fields.
	 *
	 * @return string
	 */
	private static function get_mapping_data( $list_array, $result, $post_node, $list_fields ) {

		$interests_list = array();

		if ( isset( $list_fields->interests ) ) {
			$interests_list = $list_fields->interests;
			unset( $list_fields->interests );
		}

		$list_id = isset( $result->list_id ) ? $result->list_id : 0;

		$total_keys = count( array_keys( $list_array ) );

		$mapping_node = '<td colspan="' . ( $total_keys - 1 ) . '">';

		if ( ! empty( $interests_list ) ) {
			$mailchimp_group_list_node = '<label class="urmc-mapping-label">Select Groups</label>';
			foreach ( $interests_list as $key => $interests ) {
				$mailchimp_group_list_node .= '<div class="ur-mailchimp-group-type urmc-mailchimp-' . $interests->type . '"><label class="ur-mailchimp-group-title">' . $interests->title . '</label>';
				if ( 'checkboxes' === $interests->type || 'radio' === $interests->type ) {
					foreach ( $interests->groups as $group ) {
						$input_type                 = 'checkboxes' !== $interests->type ? $interests->type : 'checkbox';
						$mailchimp_group_list_node .= '<div class="ur-check"><input type="' . $input_type . '" name="urmc_mailchimp_settings[data][' . $list_id . '][group][' . $interests->type . '][]" id="urmc_mailchimp_settings_' . $group->id . '" value="' . $group->id . '" /> <label for="urmc_mailchimp_settings_' . $group->id . '">' . $group->name . '</label></div>';
					}
					$mailchimp_group_list_node .= '</div>';
				} elseif ( 'dropdown' === $interests->type ) {
					$mailchimp_group_list_node .= '<select name="urmc_mailchimp_settings[data][' . $list_id . '][group][' . $interests->type . ']" id="urmc_mailchimp_settings_dropdown">';
					foreach ( $interests->groups as $group ) {
						$selected                   = ! empty( $group->id ) ? selected( $group->id, false ) : '';
						$mailchimp_group_list_node .= sprintf(
							'<option value="%s" %s>%s</option>',
							esc_attr( $group->id ),
							$selected,
							esc_attr( $group->name )
						);
					}
					$mailchimp_group_list_node .= '</select> </div>';
				}
			}
			$mapping_node .= '<div class="urmc-mailchimp-group-wrap"><div class="urmc-mailchimp-group-list">' . $mailchimp_group_list_node . '</div>';
		}

		$mapping_node .= '<div class="urmc-mailchimp-form-map"><a class="urmc-edit-mapping-data button">' . __( 'Edit Mapping', 'user-registration-mailchimp' ) . '</a>';

		$post_node = str_replace( 'URMC_FORM_ID', 'urmc_mailchimp_settings[data][' . $list_id . '][form_id]', $post_node );

		$mapping_node .= '<div class="urmc-mapping-data">' . $post_node . '</div>';

		$mailchimp_list_field_node = '<select style="display:none" name="' . $list_id . '">';

		foreach ( $list_fields as $field_key => $field_value ) {

			$is_required                = (bool) $field_value->required ? true : false;
			$mailchimp_list_field_node .= '<option data-required="' . $is_required . '" value="' . $field_value->tag . '">' . $field_value->name . '</option>';

		}

		$mailchimp_list_field_node .= '</select>';

		$mapping_node .= '<div class="urmc-mailchimp-list-fields">' . $mailchimp_list_field_node . '</div></div>';

		$mapping_node .= '</div></td>';

		return $mapping_node;
	}


	/**
	 * Get Mailchimp List Header.
	 *
	 * @return string
	 */
	private static function list_header() {

		$list_header = self::list_array();

		$list_node = '';

		foreach ( $list_header as $list_key => $list_value ) {

			$label = isset( $list_value['label'] ) ? $list_value['label'] : $list_key;

			$is_hidden = isset( $list_value['is_hidden'] ) ? $list_value['is_hidden'] : false;

			if ( ! $is_hidden ) {

				$list_node .= '<th scope="col" class="manage-column' . $list_key . '"><span>' . $label . '</span></th>';

			}
		}

		return $list_node;

	}

	/**
	 * Get Mailchimp List Array.
	 *
	 * @return array
	 */
	private static function list_array() {

		$list_array = array(

			'ID'            => array( 'label' => __( 'ID', 'user-registration-mailchimp' ) ),
			'list_title'    => array( 'label' => __( 'List', 'user-registration-mailchimp' ) ),
			'list_id'       => array( 'label' => __( 'List ID', 'user-registration-mailchimp' ) ),
			'web_id'        => array( 'label' => __( 'Web ID', 'user-registration-mailchimp' ) ),
			'updated_date'  => array( 'label' => __( 'Updated At', 'user-registration-mailchimp' ) ),
			'total_members' => array( 'label' => __( 'Total Members', 'user-registration-mailchimp' ) ),
			'field_count'   => array( 'label' => __( 'Field Count', 'user-registration-mailchimp' ) ),
			'list_fields'   => array(
				'label'     => __( 'List Fields', 'user-registration-mailchimp' ),
				'is_hidden' => true,
			),

		);

		return $list_array;
	}
}
