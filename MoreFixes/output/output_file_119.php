function db_install_errors($cacti_version) {
	global $session;

	if (sizeof($session)) {	
		foreach ($session as $sc) {
			if (isset($sc[$cacti_version])) {
				foreach ($sc[$cacti_version] as $value => $sql) {
					if ($value == 0) {
						echo "    DB Error: " . $sql . PHP_EOL;
					}
				}
			}
		}
	}
}
