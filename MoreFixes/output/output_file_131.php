function display_graphs() {
	$sql_where = '';

	if (get_request_var('filter') != '') {
		$sql_where .= 'WHERE (title_cache LIKE ' . db_qstr('%' . get_request_var('filter') . '%') . ' OR gt.name LIKE ' . db_qstr('%' . get_request_var('filter') . '%') . ') AND local_graph_id > 0';
	} else {
		$sql_where .= 'WHERE local_graph_id > 0';
	}

	if (get_filter_request_var('site_id') != '') {
		$sql_where .= ($sql_where != '' ? ' AND ': 'WHERE ') . 'h.site_id = ' . get_request_var('site_id');
	}

	if (get_request_var('host_id') != '') {
		$sql_where .= ($sql_where != '' ? ' AND ':'WHERE ') . 'gl.host_id = ' . get_request_var('host_id');
	}

	$graphs = db_fetch_assoc("SELECT
		gtg.local_graph_id AS id,
		gtg.title_cache AS title,
		gt.name AS template_name
		FROM graph_templates_graph AS gtg
		LEFT JOIN graph_templates AS gt
		ON gt.id=gtg.graph_template_id
		LEFT JOIN graph_local AS gl
		ON gtg.local_graph_id = gl.id
		LEFT JOIN host as h
		ON gl.host_id = h.id
		$sql_where
		ORDER BY title_cache
		LIMIT 20");

	if (cacti_sizeof($graphs)) {
		foreach($graphs as $g) {
			if (is_graph_allowed($g['id'])) {
				print "<ul><li id='tgraph:" . $g['id'] . "' data-jstree='{ \"type\": \"graph\" }'>" . html_escape($g['title']) . '</li></ul>';
			}
		}
	}
}
