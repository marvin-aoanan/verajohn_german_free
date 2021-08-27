/**
 * UserRegistration Admin JS
 * global i18n_admin
 */
(function ( $, urmc_script_data ) {
	var i18n_admin = urmc_script_data.i18n_admin;
	$(function () {
		var urmc_mapping = {
			init: function ( $selector, value ) {
				var selected_value = typeof value !== 'undefined' ? value : $selector.find('option:selected').val();
				var selected_option_node = $selector.find('option[value="' + selected_value + '"]');
				this.render_field(selected_option_node);
			},
			render_field: function ( option_node ) {
				var option_data = option_node.attr('data-fields');
				var data_obj;
				var form_field_node = $('<select class="urmc-form-field"/>');
				form_field_node.append('<option value="-1">' + i18n_admin.ignore_this_field + '</option>');
				if ( 'undefined' !== typeof option_data ) {
					try {
						var encodedString = atob(option_data);
						data_obj = $.parseJSON(encodedString);
						$.each(data_obj, function ( obj_key, obj_value ) {
							var field_label = obj_value === '' || obj_value === null ? obj_key : obj_value;
							form_field_node.append('<option value="' + obj_key + '">' + field_label + '</option>');
						});
					} catch ( e ) {
					}
				}
				if ( typeof data_obj === 'object' ) {
					var mailchimp_list_node = option_node.closest('td').find('.urmc-mailchimp-list-fields').find('select');
					this.populate_mapping_node(mailchimp_list_node, form_field_node);
				}
			},
			populate_mapping_node: function ( mailchimp_list_node, form_field_node ) {
				var wrapper = $('<div class="urmc-mapping-node"/>');
				$.each(mailchimp_list_node.find('option'), function () {
					var mailchimp_key = $(this).val();
					var is_required = parseInt($(this).attr('data-required'), 2);
					var mailchimp_value = $(this).text();
					var urmc_grid = $('<div class="urmc-mapping-grid"/>');
					var form_fields_label = $('<label class="urmc-mapping-label" for="' + mailchimp_key + '"/>');
					form_fields_label.text(mailchimp_value);
					if ( is_required === 1 ) {
						form_fields_label.find('span').remove();
						form_fields_label.append(' <span style="color:red">*</span>');
					}
					urmc_grid.append(form_fields_label);
					var form_field_node_clone = form_field_node.clone();
					var form_field_node_name = 'urmc_mailchimp_settings[data][' + mailchimp_list_node.attr('name') + '][fields]';
					form_field_node_clone.attr('name', form_field_node_name + '[' + mailchimp_key + ']');
					if ( mailchimp_key === 'email_address' ) {
						form_field_node_clone.find('option[value="user_email"]').prop('selected', true);
						form_field_node_clone.attr('readonly', 'readonly');
					}
					urmc_grid.append(form_field_node_clone);
					wrapper.append(urmc_grid);
				});
				var double_opt_in_wrap = $('<div class="urmc-mapping-grid urmc-double-option"/>');
				var double_opt_in_id = 'urmc_mailchimp_settings[data][' + mailchimp_list_node.attr('name') + '][enable_double_optin]';
				double_opt_in_wrap.append($('<label class="urmc-mapping-label" for="' + double_opt_in_id + '"/>').text(i18n_admin.enable_double_optin));
				double_opt_in_wrap.append('<input value="1" type="checkbox" name="' + double_opt_in_id + '" id="' + double_opt_in_id + '"/> <label for="' + double_opt_in_id + '">Enable</label>');
				wrapper.append(double_opt_in_wrap);
				mailchimp_list_node.closest('td').find('.urmc-mapping-node').remove();
				mailchimp_list_node.closest('td').append(wrapper);
			},
			populate_option_data: function ( data ) {
				var $this = this;
				for ( var key in data ) {
					var select_tag = $('select[name="urmc_mailchimp_settings[data][' + key + '][form_id]"]');
					var form_id = data[ key ].form_id;
					select_tag.find('option[value="' + form_id + '"]').prop('selected', true);
					select_tag.change();
					var fields = data[ key ].fields;
					if ( typeof fields !== 'undefined' ) {
						$this.change_fields(select_tag, fields, key);
					}

					var group = data[ key ].group;
					if ( typeof group !== 'undefined' ) {
						if ( typeof group.checkboxes !== 'undefined' ) {
							$.each(group.checkboxes, function(index, group_id){
								$('input[type="checkbox"][value="' + group_id + '"]').prop('checked', true);
							});
						}
						if ( typeof group.radio !== 'undefined' ) {
							$.each(group.radio, function(index, group_id){
								$('input[type="radio"][value="' + group_id + '"]').prop('checked', true);
							});
						}
						if ( typeof group.dropdown !== 'undefined' ) {
							$('#urmc_mailchimp_settings_dropdown option[value='+ group.dropdown +']').attr('selected', 'selected');
						}
					}

					var has_double_opt_in = false;
					if ( typeof data[ key ].enable_double_optin !== 'undefined' && data[ key ].enable_double_optin === '1' ) {
						has_double_opt_in = true;
					}
					if ( has_double_opt_in ) {
						$('input[type="checkbox"][name="urmc_mailchimp_settings[data][' + key + '][enable_double_optin]"]').prop('checked', true);
					}
				}
			},
			change_fields: function ( select_tag, fields, list_id ) {
				for ( var key in fields ) {
					var select_name = 'urmc_mailchimp_settings[data][' + list_id + '][fields][' + key + ']';
					var mailchimp_node = select_tag.closest('td').find('.urmc-mapping-node').find('.urmc-mapping-grid').find('select.urmc-form-field[name="' + select_name + '"]');
					mailchimp_node.find('option[value="' + fields[ key ] + '"]').prop('selected', true);
				}
			}
		};
		var urmc_mapping_event = {
			toggle_mapping: function ( node ) {
				node.closest('td').find('.urmc-mapping-node').slideToggle();
			}
		};
		var form_select_node = $('select.urmc-form-field-node');
		form_select_node.on('change', function () {
			urmc_mapping.init($(this));
			if( $(this).val() > 0 ) {
				$(this).closest('td').find('.urmc-edit-mapping-data').click();
			} else {
				if( $(this).closest('td').find('.urmc-mapping-node').is(':visible') ){
					urmc_mapping_event.toggle_mapping($('.urmc-edit-mapping-data'));
				}
			}
		});
		if ( typeof urmc_script_data.mapping_fields.data !== 'undefined' ) {
			urmc_mapping.populate_option_data(urmc_script_data.mapping_fields.data);
		}
		$('.urmc-edit-mapping-data').on('click', function () {
			if( form_select_node.val() > 0 ) {
				urmc_mapping_event.toggle_mapping($(this));
			}
		});
		function synchronization_with_mailchimp ( $node ) {
			var $node_val = $node.text();
			var mailchimp_api = $('.ur-mailchimp-api-key').val();
			if ( mailchimp_api === '' ) {
				alert(i18n_admin.empty_api);
				return;
			}
			var data = {
				security: urmc_script_data.mailchimp_sync_nonce,
				action: 'user_registration_mailchimp_sync_with_mailchimp',
				api: mailchimp_api
			};
			$.ajax({
				url: urmc_script_data.ajax_url,
				data: data,
				type: 'POST',
				async: true,
				beforeSend: function () {
					$node.html('<small class="spinner is-active" style="float:left"></small><span>' + i18n_admin.synchronizing + '</span>');
					$node.addClass('synchronizing');
				},
				complete: function ( ajax_response ) {
					$node.removeClass('synchronizing');
					var type = '';
					var message = '';
					try {
						var response = $.parseJSON(ajax_response.responseText);
						if ( typeof response !== 'object' ) {
							throw i18n_admin.something_wrong;
						}
						if ( typeof response.data === 'undefined' ) {
							throw i18n_admin.something_wrong;
						}
						if ( typeof response.success === 'undefined' ) {
							throw i18n_admin.something_wrong;
						}
						if ( typeof response.data.message === 'undefined' ) {
							throw i18n_admin.something_wrong;
						}
						type = response.success === true ? 'success' : 'error';
						message = response.data.message;
						if ( type === 'success' ) {
							setTimeout(function () {
								location.reload();
							}, 2000);
						}
					} catch ( exception ) {
						type = 'error';
						message = exception;
					}
					urmc_show_message(type, message);
					$node.html($node_val);
				}
			});
		}

		$('#mailchimp-sync').on('click', function () {
			if ( $(this).hasClass('synchronizing') ) {
				return;
			}
			synchronization_with_mailchimp($(this));
		});
		function urmc_show_message ( type, message ) {
			var message_node = '<div class="urmc-notice notice notice-' + type + '">';
			message_node += '<p>' + message + '</p>';
			message_node += '</div>';
			$('#ur-mailchimp-api-key').find('.urmc-notice').remove();
			$('#ur-mailchimp-api-key').prepend(message_node);
		}
	});
}(jQuery, window.urmc_script_data));