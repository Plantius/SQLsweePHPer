function display_sites() {
	if (get_request_var('filter') != '') {
		$sql_where = 'WHERE name LIKE ' . db_qstr('%' . get_request_var('filter') . '%') . '
			OR city LIKE ' . db_qstr('%' . get_request_var('filter') . '%') . '
			OR state LIKE ' . db_qstr('%' . get_request_var('filter') . '%') . '
			OR country LIKE ' . db_qstr('%' . get_request_var('filter') . '%');
	} else {
		$sql_where = '';
	}

	$sites = db_fetch_assoc("SELECT * FROM sites $sql_where");

	if (cacti_sizeof($sites)) {
		foreach($sites as $s) {
			print "<ul><li id='tsite:" . $s['id'] . "' data-jstree='{ \"type\" : \"site\"}'>" . $s['name'] . "</li></ul>\n";
		}
	}
}
