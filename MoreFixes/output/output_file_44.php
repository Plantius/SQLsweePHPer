		$sql_where .= " AND snl.severity='" . get_request_var('severity') . "'";
	}

	/* filter by search string */
	if (get_request_var('filter') != '') {
		$sql_where .= " AND (`varbinds` LIKE '%" . get_request_var('filter') . "%')";
	}

	$sql_where .= ' ORDER by `id` DESC';
	$sql_query = "SELECT snl.*, sc.description
		FROM snmpagent_notifications_log AS snl
		LEFT JOIN snmpagent_cache AS sc
		ON sc.name = snl.notification
		WHERE $sql_where
		LIMIT " . ($rows*(get_request_var('page')-1)) . ',' . $rows;
