			$upgrade_function = 'upgrade_to_' . str_replace('.', '_', $cacti_upgrade_version);

			// check for upgrade version file, then include, check for function and execute
			if (file_exists($upgrade_file)) {
				include_once($upgrade_file);
				if (function_exists($upgrade_function)) {
					log_install_and_cacti('Applying v' . $cacti_upgrade_version . ' upgrade');
					call_user_func($upgrade_function);
				} else {
					log_install_and_cacti('WARNING: Failed to find upgrade function for v' . $cacti_upgrade_version);
				}
			} else {
				log_install('','INFO: Failed to find ' . $upgrade_file . ' upgrade file for v' . $cacti_upgrade_version);
			}
		}

		if (empty($failure)) {
			$failure = $this->checkDatabaseUpgrade();
		}

		return $failure;;
	}
