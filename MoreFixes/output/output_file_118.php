function db_install_drop_table ($table) {
	$sql = 'DROP TABLE `' . $table . '`';
	if (db_table_exists($table, false)) {
		db_install_execute ($sql);
	} else {
		db_install_add_cache (2, $sql);
	}
}
