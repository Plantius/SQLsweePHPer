	private function checkDatabaseUpgrade() {
		$failure = '';

		if (isset($_SESSION['cacti_db_install_cache']) && is_array($_SESSION['cacti_db_install_cache'])) {
			foreach ($_SESSION['cacti_db_install_cache'] as $cacti_upgrade_version => $actions) {
				foreach ($actions as $action) {
					// set sql failure if status set to zero on any action
					if ($action['status'] == 0) {
						$failure = 'WARNING: One or more database actions failed';
					}
				}
			}
		}

		return $failure;
	}
