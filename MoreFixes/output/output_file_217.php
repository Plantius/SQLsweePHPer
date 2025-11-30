function read_config_option($config_name, $force = false) {
	global $config;

	if (isset($_SESSION['sess_config_array'])) {
		$config_array = $_SESSION['sess_config_array'];
	} elseif (isset($config['config_options_array'])) {
		$config_array = $config['config_options_array'];
	}

	if ((!isset($config_array[$config_name])) || ($force)) {
		$db_setting = db_fetch_row_prepared('SELECT value FROM settings WHERE name = ?', array($config_name), false);

		$value = null;
		if (isset($db_setting['value'])) {
			$value = $db_setting['value'];
		}

		if ($value === null) {
			$value = read_default_config_option($config_name);
		}

		$config_array[$config_name] = $value;

		if (isset($_SESSION)) {
			$_SESSION['sess_config_array']  = $config_array;
		} else {
			$config['config_options_array'] = $config_array;
		}
	}

	return $config_array[$config_name];
}
