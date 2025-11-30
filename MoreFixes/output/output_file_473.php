	public function processBackgroundInstall() {
		global $config;
		switch ($this->mode) {
			case Installer::MODE_UPGRADE:
				$which = 'UPGRADE';
				break;
			case Installer::MODE_DOWNGRADE:
				$which = 'DOWNGRADE';
				break;
			default:
				$which = 'INSTALL';
				break;
		}

		log_install_and_cacti(sprintf('Starting %s Process for v%s', $which, CACTI_VERSION));

		$this->setProgress(Installer::PROGRESS_START);

		$this->convertDatabase();

		if (!$this->hasRemoteDatabaseInfo()) {
			$this->installTemplate();
		}

		$this->setProgress(Installer::PROGRESS_TEMPLATES_END);
		$failure = '';

		if ($this->mode == Installer::MODE_POLLER) {
			$failure = $this->installPoller();
		} else {
			if ($this->mode == Installer::MODE_INSTALL) {
				$failure = $this->installServer();
			} else if ($this->mode == Installer::MODE_UPGRADE) {
				$failure = $this->upgradeDatabase();
			}
			$this->disablePluginsNowIntegrated();
		}

		log_install_and_cacti(sprintf('Setting Cacti Version to %s', CACTI_VERSION));
		log_install_and_cacti(sprintf('Finished %s Process for v%s', $which, CACTI_VERSION));

		set_config_option('install_error',$failure);
		$this->setProgress(Installer::PROGRESS_VERSION_BEGIN);
		db_execute_prepared('UPDATE version SET cacti = ?', array(CACTI_VERSION));
		set_config_option('install_version', CACTI_VERSION);
		$this->setProgress(Installer::PROGRESS_VERSION_END);

		if (empty($failure)) {
			// No failures so lets update the version
			$this->setProgress(Installer::PROGRESS_COMPLETE);
			$this->setStep(Installer::STEP_COMPLETE);
		} else {
			log_install_and_cacti($failure);
			$this->setProgress(Installer::PROGRESS_COMPLETE);
			$this->setStep(Installer::STEP_ERROR);
		}
	}
