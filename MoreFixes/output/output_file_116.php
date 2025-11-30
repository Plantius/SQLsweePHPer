function db_install_add_key ($table, $type, $key, $columns) {
	if (!is_array($columns)) {
		$columns = array($columns);
	}

	$sql = 'ALTER TABLE `' . $table . '` ADD ' . $type . ' ' . $key . '(' . implode(',', $columns) . ')';

	if (db_index_exists($table, $key, false)) {
		$type = str_ireplace('UNIQUE ', '', $type);
		db_install_execute("ALTER TABLE $table DROP $type $key");
	}

	db_install_execute($sql);
}
