function db_install_execute($sql) {
	$status = (db_execute($sql, false) ? 1 : 0);
	db_install_add_cache ($status, $sql);
}
