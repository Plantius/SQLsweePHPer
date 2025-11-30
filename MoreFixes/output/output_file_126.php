function db_update_table($table, $data, $removecolumns = false, $log = true, $db_conn = false) {
	global $database_sessions, $database_default, $database_hostname, $database_port;

	/* check for a connection being passed, if not use legacy behavior */
	if (!is_object($db_conn)) {
		$db_conn = $database_sessions["$database_hostname:$database_port:$database_default"];

		if (!is_object($db_conn)) {
			return false;
		}
	}

	if (!db_table_exists($table, $log, $db_conn)) {
		return db_table_create ($table, $data, $log, $db_conn);
	}

	$allcolumns = array();
	foreach ($data['columns'] as $column) {
		$allcolumns[] = $column['name'];
		if (!db_column_exists($table, $column['name'], $log, $db_conn)) {
			db_add_column ($table, $column, $log, $db_conn);
		} else {
			// Check that column is correct and fix it
			// FIXME: Need to still check default value
			$arr = db_fetch_row("SHOW columns FROM `$table` LIKE '" . $column['name'] . "'", $log, $db_conn);
			if ($column['type'] != $arr['Type'] || (isset($column['NULL']) && ($column['NULL'] ? 'YES' : 'NO') != $arr['Null'])
							    || (isset($column['auto_increment']) && ($column['auto_increment'] ? 'auto_increment' : '') != $arr['Extra'])) {
				$sql = 'ALTER TABLE `' . $table . '` CHANGE `' . $column['name'] . '` `' . $column['name'] . '`';
				if (isset($column['type']))
					$sql .= ' ' . $column['type'];
				if (isset($column['unsigned']))
					$sql .= ' unsigned';
				if (isset($column['NULL']) && $column['NULL'] == false)
					$sql .= ' NOT NULL';
				if (isset($column['NULL']) && $column['NULL'] == true && !isset($column['default']))
					$sql .= ' default NULL';
				if (isset($column['default']))
					$sql .= ' default ' . (is_numeric($column['default']) ? $column['default'] : "'" . $column['default'] . "'");
				if (isset($column['on_update']))
					$sql .= ' ON UPDATE ' . $column['on_update'];
				if (isset($column['auto_increment']))
					$sql .= ' auto_increment';
				if (isset($column['comment']))
					$sql .= " COMMENT '" . $column['comment'] . "'";
				db_execute($sql, $log, $db_conn);
			}
		}
	}
