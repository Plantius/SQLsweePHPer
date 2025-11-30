function get_vdef_records(&$total_rows, &$rows) {
	/* form the 'where' clause for our main sql query */
	if (get_request_var('filter') != '') {
		$sql_where = "WHERE (rs.name LIKE '%" . get_request_var('filter') . "%')";
	} else {
		$sql_where = '';
	}

	if (get_request_var('has_graphs') == 'true') {
		$sql_having = 'HAVING graphs>0';
	} else {
		$sql_having = '';
	}

	$total_rows = db_fetch_cell("SELECT
		COUNT(`rows`)
        FROM (
            SELECT vd.id AS `rows`, vd.name,
            SUM(CASE WHEN local_graph_id>0 THEN 1 ELSE 0 END) AS graphs
            FROM vdef AS vd
            LEFT JOIN graph_templates_item AS gti
            ON gti.vdef_id=vd.id
            GROUP BY vd.id
        ) AS rs
        $sql_where
		$sql_having
	");

	$sql_order = get_order_string();
	$sql_limit = ' LIMIT ' . ($rows*(get_request_var('page')-1)) . ',' . $rows;
