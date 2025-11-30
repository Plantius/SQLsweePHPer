function install_test_temporary_table() {
	$table = 'test_temp_' . rand();

	if (!db_execute('CREATE TEMPORARY TABLE ' . $table . ' (`cacti` char(20) NOT NULL DEFAULT "", PRIMARY KEY (`cacti`)) ENGINE=InnoDB', false)) {
		return false;
	} else {
		db_execute('DROP TABLE ' . $table);
	}

	return true;
}
