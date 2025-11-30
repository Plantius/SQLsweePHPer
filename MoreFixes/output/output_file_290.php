	private function disablePluginsNowIntegrated() {
		global $plugins_integrated;
		foreach ($plugins_integrated as $plugin) {
			if (api_plugin_is_enabled ($plugin)) {
				api_plugin_remove_hooks ($plugin);
				api_plugin_remove_realms ($plugin);
				db_execute_prepared('DELETE FROM plugin_config
					WHERE directory = ?',
					array($plugin));
			}
		}
	}
