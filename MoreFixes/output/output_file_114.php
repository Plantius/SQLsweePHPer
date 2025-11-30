function db_install_add_cache ($status, $sql) {
	global $cacti_upgrade_version;

	// add query to upgrade results array by version to the cli global session
	$cache_file = read_config_option('install_cache_db');
	if (!empty($cache_file)) {
		file_put_contents($cache_file, '<[version]> ' . $cacti_upgrade_version . ' <[status]> ' . $status . ' <[sql]> ' . clean_up_lines($sql) . PHP_EOL, FILE_APPEND);
	}
}
