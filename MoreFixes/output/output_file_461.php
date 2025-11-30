	public function isConfigurationWritable() {
		global $config;
		return (is_writable($config['base_path'] . '/include/config.php') ? true : false);
	}
