	public static function export()
	{
		$settings_definitions = [];
		foreach (PhpHelper::loadConfigArrayDir('./actions/admin/settings/')['groups'] as $group) {
			foreach ($group['fields'] as $field) {
				$settings_definitions[$field['settinggroup']][$field['varname']] = $field;
			}
		}

		$result_stmt = Database::query("
			SELECT * FROM `" . TABLE_PANEL_SETTINGS . "` ORDER BY `settingid` ASC
		");
		$_data = [];
		while ($row = $result_stmt->fetch(PDO::FETCH_ASSOC)) {
			$index = $row['settinggroup'] . "." . $row['varname'];
			if (!in_array($index, self::$no_export)) {
				$_data[$index] = $row['value'];
			}

			if (array_key_exists($row['settinggroup'], $settings_definitions) && array_key_exists($row['varname'], $settings_definitions[$row['settinggroup']])) {
				// Export image file
				if ($settings_definitions[$row['settinggroup']][$row['varname']]['type'] === "image") {
					if ($row['value'] === "") {
						continue;
					}

					$_data[$index . '.image_data'] = base64_encode(file_get_contents(explode('?', $row['value'], 2)[0]));
				}
			}
		}

		// add checksum for validation
		$_data['_sha'] = sha1(var_export($_data, true));
		$_export = json_encode($_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		if (!$_export) {
			throw new Exception("Error exporting settings: " . json_last_error_msg());
		}

		return $_export;
	}
