function form_save() {
	if (!isset_request_var('tab')) set_request_var('tab', 'general');

	/* ================= input validation ================= */
	get_filter_request_var('id');
	get_filter_request_var('max_log_size');

	if (!in_array(get_nfilter_request_var('max_log_size'), range(1,31))) {
		//	die_html_input_error();
	}
	/* ================= input validation ================= */

	switch(get_nfilter_request_var('tab')){
		case 'notifications':
			header('Location: managers.php?action=edit&tab=notifications&id=' . get_request_var('id'));
			break;
		default:
			$save['id']                       = get_request_var('id');
			$save['description']              = form_input_validate(trim(get_nfilter_request_var('description')), 'description', '', false, 3);
			$save['hostname']                 = form_input_validate(trim(get_nfilter_request_var('hostname')), 'hostname', '', false, 3);
			$save['disabled']                 = form_input_validate(get_nfilter_request_var('disabled'), 'disabled', '^on$', true, 3);
			$save['max_log_size']             = get_nfilter_request_var('max_log_size');

			$save['snmp_version']             = form_input_validate(get_nfilter_request_var('snmp_version'), 'snmp_version', '^[1-3]$', false, 3);
			$save['snmp_community']           = form_input_validate(get_nfilter_request_var('snmp_community'), 'snmp_community', '', true, 3);

			if ($save['snmp_version'] == 3) {
				$save['snmp_username']        = form_input_validate(get_nfilter_request_var('snmp_username'), 'snmp_username', '', true, 3);
				$save['snmp_password']        = form_input_validate(get_nfilter_request_var('snmp_password'), 'snmp_password', '', true, 3);
				$save['snmp_auth_protocol']   = form_input_validate(get_nfilter_request_var('snmp_auth_protocol'), 'snmp_auth_protocol', "^\[None\]|MD5|SHA$", true, 3);
				$save['snmp_priv_passphrase'] = form_input_validate(get_nfilter_request_var('snmp_priv_passphrase'), 'snmp_priv_passphrase', '', true, 3);
				$save['snmp_priv_protocol']   = form_input_validate(get_nfilter_request_var('snmp_priv_protocol'), 'snmp_priv_protocol', "^\[None\]|DES|AES128$", true, 3);
				$save['snmp_engine_id']       = form_input_validate(get_request_var_post('snmp_engine_id'), 'snmp_engine_id', '', false, 3);
			} else {
				$save['snmp_username']        = '';
				$save['snmp_password']        = '';
				$save['snmp_auth_protocol']   = '';
				$save['snmp_priv_passphrase'] = '';
				$save['snmp_priv_protocol']   = '';
				$save['snmp_engine_id']       = '';
			}

			$save['snmp_port']                = form_input_validate(get_nfilter_request_var('snmp_port'), 'snmp_port', '^[0-9]+$', false, 3);
			$save['snmp_message_type']        = form_input_validate(get_nfilter_request_var('snmp_message_type'), 'snmp_message_type', '^[1-2]$', false, 3);
			$save['notes']                    = form_input_validate(get_nfilter_request_var('notes'), 'notes', '', true, 3);


			if ($save['snmp_version'] == 3 && ($save['snmp_password'] != get_nfilter_request_var('snmp_password_confirm'))) {
				raise_message(4);
			}

			if ($save['snmp_version'] == 3 && ($save['snmp_priv_passphrase'] != get_nfilter_request_var('snmp_priv_passphrase_confirm'))) {
				raise_message(4);
			}

			$manager_id = 0;
			if (!is_error_message()) {
				$manager_id = sql_save($save, 'snmpagent_managers');
				raise_message( ($manager_id)? 1 : 2 );
			}
			break;
	}

	header('Location: managers.php?action=edit&header=false&id=' . (empty($manager_id) ? get_nfilter_request_var('id') : $manager_id) );
