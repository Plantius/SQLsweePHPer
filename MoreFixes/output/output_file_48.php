		$sql_where = "WHERE ul.username='" . get_request_var('username') . "'";
	}

	/* filter by result */
	if (get_request_var('result') != '-1') {
		$sql_where .= ($sql_where != '' ? ' AND ':'WHERE ') . ' ul.result=' . get_request_var('result');
	}

	/* filter by search string */
	if (get_request_var('filter') != '') {
		if ($sql_where != '') {
			$sql_where .= " AND (ul.username LIKE '%" . get_request_var('filter') . "%'
				OR ul.time LIKE '%" . get_request_var('filter') . "%'
				OR ua.full_name LIKE '%" . get_request_var('filter') . "%'
				OR ul.ip LIKE '%" . get_request_var('filter') . "%')";
		} else {
			$sql_where = "WHERE (ul.username LIKE '%" . get_request_var('filter') . "%'
				OR ul.time LIKE '%" . get_request_var('filter') . "%'
				OR ua.full_name LIKE '%" . get_request_var('filter') . "%'
				OR ul.ip LIKE '%" . get_request_var('filter') . "%')";
		}
	}

	$total_rows = db_fetch_cell("SELECT
		COUNT(*)
		FROM user_auth AS ua
		RIGHT JOIN user_log AS ul
		ON ua.username=ul.username
		$sql_where");

	$user_log_sql = "SELECT ul.username, ua.full_name, ua.realm,
		ul.time, ul.result, ul.ip
		FROM user_auth AS ua
		RIGHT JOIN user_log AS ul
		ON ua.username=ul.username
		$sql_where
		ORDER BY " . get_request_var('sort_column') . ' ' . get_request_var('sort_direction') . '
		LIMIT ' . ($rows*(get_request_var('page')-1)) . ',' . $rows;
