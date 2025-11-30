function get_discovery_results(&$total_rows = 0, $rows = 0, $export = false) {
	global $os_arr, $status_arr, $networks, $device_actions;

	$sql_where  = '';
	$status     = get_request_var('status');
	$network    = get_request_var('network');
	$snmp       = get_request_var('snmp');
	$os         = get_request_var('os');
	$filter     = get_request_var('filter');

	if ($status == __('Down')) {
		$sql_where .= 'WHERE up=0';
	} else if ($status == __('Up')) {
		$sql_where .= 'WHERE up=1';
	}

	if ($network > 0) {
		$sql_where .= ($sql_where != '' ? ' AND ':'WHERE ') . 'network_id=' . $network;
	}

	if ($snmp == __('Down')) {
		$sql_where .= ($sql_where != '' ? ' AND ':'WHERE ') . 'snmp=0';
	} else if ($snmp == __('Up')) {
		$sql_where .= ($sql_where != '' ? ' AND ':'WHERE ') . 'snmp=1';
	}

	if ($os != '-1' && in_array($os, $os_arr)) {
		$sql_where .= ($sql_where != '' ? ' AND ':'WHERE ') . "os='$os'";
	}

	if ($filter != '') {
		$sql_where .= ($sql_where != '' ? ' AND ':'WHERE ') . "(hostname LIKE '%$filter%' OR ip LIKE '%$filter%')";
	}

	if ($export) {
		return db_fetch_assoc("SELECT * FROM automation_devices $sql_where ORDER BY INET_ATON(ip)");
	} else {
		$total_rows = db_fetch_cell("SELECT
			COUNT(*)
			FROM automation_devices
			$sql_where");

		$page = get_request_var('page');

		$sql_order = get_order_string();
		$sql_limit = ' LIMIT ' . ($rows*($page-1)) . ',' . $rows;

		$sql_query = "SELECT *,sysUptime snmp_sysUpTimeInstance, FROM_UNIXTIME(time) AS mytime
			FROM automation_devices
			$sql_where
			$sql_order
			$sql_limit";

		return db_fetch_assoc($sql_query);
	}
