function db_table_create($table, $data, $log = true, $db_conn = false) {
	global $database_sessions, $database_default, $database_hostname, $database_port;

	/* check for a connection being passed, if not use legacy behavior */
	if (!is_object($db_conn)) {
		$db_conn = $database_sessions["$database_hostname:$database_port:$database_default"];

		if (!is_object($db_conn)) {
			return false;
		}
	}

	if (!db_table_exists($table, $log, $db_conn)) {
		$c = 0;
		$sql = 'CREATE TABLE `' . $table . "` (\n";
		foreach ($data['columns'] as $column) {
			if (isset($column['name'])) {
				if ($c > 0)
					$sql .= ",\n";
				$sql .= '`' . $column['name'] . '`';
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
				if (isset($column['comment']))
					$sql .= " COMMENT '" . $column['comment'] . "'";
				if (isset($column['auto_increment']))
					$sql .= ' auto_increment';
				$c++;
			}
		}

		if (isset($data['primary'])) {
			if (is_array($data['primary'])) {
				$sql .= ",\n PRIMARY KEY (`" . implode('`,`'. $data['primary']) . '`)';
			} else {
				$sql .= ",\n PRIMARY KEY (`" . $data['primary'] . '`)';
			}
		}

		if (isset($data['keys']) && sizeof($data['keys'])) {
			foreach ($data['keys'] as $key) {
				if (isset($key['name'])) {
					if (is_array($key['columns'])) {
						$sql .= ",\n KEY `" . $key['name'] . '` (`' . implode('`,`', $key['columns']) . '`)';
					} else {
						$sql .= ",\n KEY `" . $key['name'] . '` (`' . $key['columns'] . '`)';
					}
				}
			}
		}
		$sql .= ') ENGINE = ' . $data['type'];

		if (isset($data['comment'])) {
			$sql .= " COMMENT = '" . $data['comment'] . "'";
		}

		if (db_execute($sql, $log, $db_conn)) {
			return true;
		}

		return false;
	}
