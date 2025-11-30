function display_hosts() {
	$sql_where = '';

	if (get_request_var('filter') != '') {
		$sql_where .= 'h.hostname LIKE ' . db_qstr('%' . get_request_var('filter') . '%') . ' OR h.description LIKE ' . db_qstr('%' . get_request_var('filter') . '%');
	}

	if (get_filter_request_var('site_id') > 0) {
		$sql_where .= ($sql_where != '' ? ' AND ':'') . 'h.site_id = ' . get_filter_request_var('site_id');
	}

	$hosts = get_allowed_devices($sql_where, 'description', '20');

	if (cacti_sizeof($hosts)) {
		foreach($hosts as $h) {
			print "<ul><li id='thost:" . $h['id'] . "' data-jstree='{ \"type\" : \"device\"}'>" . $h['description'] . ' (' . $h['hostname'] . ')' . "</li></ul>\n";
		}
	}
}
