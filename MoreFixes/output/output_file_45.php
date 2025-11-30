		$sql_where .= " AND snl.severity='" . get_request_var('severity') . "'";
	}

	/* filter by search string */
	if (get_request_var('filter') != '') {
		$sql_where .= " AND (`varbinds` LIKE '%" . get_request_var('filter') . "%')";
	}
	$sql_where .= ' ORDER by `time` DESC';
	$sql_query  = "SELECT snl.*, sm.hostname, sc.description
		FROM snmpagent_notifications_log AS snl
		INNER JOIN snmpagent_managers AS sm
		ON sm.id = snl.manager_id
		LEFT JOIN snmpagent_cache AS sc
		ON sc.name = snl.notification
		WHERE $sql_where
		LIMIT " . ($rows*(get_request_var('page')-1)) . ',' . $rows;
