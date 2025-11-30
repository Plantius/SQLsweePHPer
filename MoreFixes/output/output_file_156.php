function get_networks(&$sql_where, $rows, $apply_limits = true) {
	if (get_request_var('filter') != '') {
		$sql_where = " WHERE (automation_networks.name LIKE '%" . get_request_var('filter') . "%')";
	}

	$sql_order = get_order_string();

	if ($apply_limits) {
		$sql_limit = ' LIMIT ' . ($rows*(get_request_var('page')-1)) . ',' . $rows;
	} else {
