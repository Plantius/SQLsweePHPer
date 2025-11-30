function manager_edit() {
	global $config, $snmp_auth_protocols, $snmp_priv_protocols, $snmp_versions,
		$tabs_manager_edit, $fields_manager_edit, $manager_notification_actions;

	/* ================= input validation ================= */
	get_filter_request_var('id');
	/* ==================================================== */

	if (!isset_request_var('tab')) {
		set_request_var('tab', 'general');
	}
	$id	= (isset_request_var('id') ? get_request_var('id') : '0');

	if ($id) {
		$manager = db_fetch_row_prepared('SELECT * FROM snmpagent_managers WHERE id = ?', array(get_request_var('id')));
		$header_label = __esc('SNMP Notification Receiver [edit: %s]', $manager['description']);
	} else {
		$header_label = __('SNMP Notification Receiver [new]');
	}

	if (cacti_sizeof($tabs_manager_edit) && isset_request_var('id')) {
		$i = 0;

		/* draw the tabs */
		print "<div class='tabs'><nav><ul role='tablist'>";

		foreach (array_keys($tabs_manager_edit) as $tab_short_name) {
			if (($id == 0 && $tab_short_name != 'general')){
				print "<li class='subTab'><a href='#' " . (($tab_short_name == get_request_var('tab')) ? "class='selected'" : '') . "'>" . $tabs_manager_edit[$tab_short_name] . '</a></li>';
			}else {
				print "<li class='subTab'><a " . (($tab_short_name == get_request_var('tab')) ? "class='selected'" : '') .
					" href='" . html_escape($config['url_path'] .
					'managers.php?action=edit&id=' . get_request_var('id') .
					'&tab=' . $tab_short_name) .
					"'>" . $tabs_manager_edit[$tab_short_name] . '</a></li>';
			}

			$i++;
		}
