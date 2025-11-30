function user_group_members_edit($header_label) {
	global $config, $auth_realms;

	process_member_request_vars();

	member_filter($header_label);

	/* if the number of rows is -1, set it to the default */
	if (get_request_var('rows') == -1) {
		$rows = read_config_option('num_rows_table');
	} else {
		$rows = get_request_var('rows');
	}

	/* form the 'where' clause for our main sql query */
	if (get_request_var('filter') != '') {
		$sql_where = "WHERE (username LIKE '%" . get_request_var('filter') . "%' OR full_name LIKE '%" . get_request_var('filter') . "%')";
	} else {
		$sql_where = '';
	}

	if (get_request_var('associated') == 'false') {
		/* Show all items */
	} else {
		$sql_where .= ($sql_where != '' ? ' AND ':'WHERE ') . ' (user_auth_group_members.group_id=' . get_request_var('id', 0) . ')';
	}

	$total_rows = db_fetch_cell("SELECT
		COUNT(ua.id)
		FROM user_auth AS ua
		LEFT JOIN user_auth_group_members
		ON (ua.id = user_auth_group_members.user_id)
		$sql_where");

	$sql_query = "SELECT DISTINCT ua.id, ua.username, ua.full_name, ua.enabled, ua.realm
		FROM user_auth AS ua
		LEFT JOIN user_auth_group_members
		ON (ua.id = user_auth_group_members.user_id)
		$sql_where
		ORDER BY username, full_name
		LIMIT " . ($rows*(get_request_var('page')-1)) . ',' . $rows;
