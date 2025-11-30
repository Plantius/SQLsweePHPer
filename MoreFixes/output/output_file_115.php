function db_install_add_column ($table, $column, $ignore = true) {
	// Example: db_install_add_column ('plugin_config', array('name' => 'test' . rand(1, 200), 'type' => 'varchar (255)', 'NULL' => false));
	$status = 1;

	$sql = 'ALTER TABLE `' . $table . '` ADD `' . $column['name'] . '`';

	if (!db_column_exists($table, $column['name'], false)) {
		$status = db_add_column($table, $column, false);
	} elseif (!$ignore) {
		$status = 2;
	} else {
		$status = 1;
	}

	db_install_add_cache ($status, $sql);
}
