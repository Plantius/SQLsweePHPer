		$sql_where .= " AND snmpagent_cache.mib='" . get_request_var('mib') . "'";
	}
	/* filter by search string */
	if (get_request_var('filter') != '') {
		$sql_where .= " AND (`oid` LIKE '%" . get_request_var('filter') . "%'
			OR `name` LIKE '%" . get_request_var('filter') . "%'
			OR `mib` LIKE '%" . get_request_var('filter') . "%')";
	}
	$sql_where .= ' ORDER by `oid`';

	form_start('managers.php', 'chk');

	/* FIXME: Change SQL Queries to not use WHERE 1 */
	$total_rows = db_fetch_cell("SELECT COUNT(*) FROM snmpagent_cache WHERE 1 $sql_where");

	$snmp_cache_sql = "SELECT * FROM snmpagent_cache WHERE 1 $sql_where LIMIT " . ($rows*(get_request_var('page')-1)) . ',' . $rows;
