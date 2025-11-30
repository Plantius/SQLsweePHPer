function db_install_rename_table ($table, $newname) {
	$sql = 'RENAME TABLE `' . $table . '` TO `' . $newname . '`';
	if (db_table_exists($table, false) && !db_table_exists($newname, false)) {
		db_install_execute ($sql);
	} else {
		db_install_add_cache (2, $sql);
	}
}
