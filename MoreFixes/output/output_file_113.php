function db_index_exists($table, $index, $log = true, $db_conn = false) {
	$_keys = array_rekey(db_fetch_assoc("SHOW KEYS FROM `$table`", $log, $db_conn), "Key_name", "Key_name");
	return in_array($index, $_keys);
}
