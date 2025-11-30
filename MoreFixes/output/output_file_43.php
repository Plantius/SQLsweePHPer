		$sql_where .= ' AND hsc.snmp_query_id=' . get_request_var('snmp_query_id');
	}

	/* filter by search string */
	if (get_request_var('filter') != '') {
		$sql_where .= " AND (h.description LIKE '%" . get_request_var('filter') . "%'
			OR sq.name LIKE '%" . get_request_var('filter') . "%'
			OR hsc.field_name LIKE '%" . get_request_var('filter') . "%'
			OR hsc.field_value LIKE '%" . get_request_var('filter') . "%'
			OR hsc.oid LIKE '%" . get_request_var('filter') . "%'";
		if (get_request_var('with_index') == 1) {
			$sql_where .= " OR hsc.snmp_index LIKE '%" . get_request_var('filter') . "%'";
		}
		$sql_where .= ")";
	}

	$total_rows = db_fetch_cell("SELECT COUNT(*)
		FROM host_snmp_cache AS hsc
		INNER JOIN snmp_query AS sq
		ON hsc.snmp_query_id = sq.id
		INNER JOIN host AS h
		ON hsc.host_id = h.id
		WHERE hsc.host_id = h.id
		AND hsc.snmp_query_id = sq.id
		$sql_where");

	$snmp_cache_sql = "SELECT hsc.*, h.description, sq.name
		FROM host_snmp_cache AS hsc
		INNER JOIN snmp_query AS sq
		ON hsc.snmp_query_id = sq.id
		INNER JOIN host AS h
		ON hsc.host_id = h.id
		WHERE hsc.host_id = h.id
		AND hsc.snmp_query_id = sq.id
		$sql_where
		LIMIT " . ($rows*(get_request_var('page')-1)) . ',' . $rows;
