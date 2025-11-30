function db_install_drop_column ($table, $column) {
	$sql = 'ALTER TABLE `' . $table . '` DROP `' . $column . '`';
	if (db_column_exists($table, $column, false)) {
		$status = (db_remove_column ($table, $column) ? 1 : 0);
	} else {
		$status = 2;
	}
	db_install_add_cache ($status, $sql);
}
