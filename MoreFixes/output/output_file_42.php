		$sql_where .= ' AND h.host_template_id=' . get_request_var('host_template_id');
	}

	/* get the WHERE clause for matching hosts */
	$sql_filter = build_matching_objects_filter($rule_id, AUTOMATION_RULE_TYPE_TREE_MATCH);

	$templates = array();
	$sql_field = $item['field'] . ' AS source ';

	/* now we build up a new query for counting the rows */
	$rows_query = "SELECT h.id AS host_id, h.hostname, h.description,
		h.disabled, h.status, ht.name AS host_template_name, $sql_field
		$sql_tables
		$sql_where AND ($sql_filter)";

	$total_rows = cacti_sizeof(db_fetch_assoc($rows_query, false));

	$sortby = get_request_var('sort_column');
	if ($sortby=='h.hostname') {
		$sortby = 'INET_ATON(h.hostname)';
	}

	$sql_query = "$rows_query ORDER BY $sortby " .
		get_request_var('sort_direction') . ' LIMIT ' .
		($rows*(get_request_var('page')-1)) . ',' . $rows;
