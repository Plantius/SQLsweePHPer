	private function setStep($param_step = -1) {
		$step = Installer::STEP_WELCOME;
		if (empty($param_step)) {
			$param_step = 1;
		}

		if (intval($param_step) > Installer::STEP_NONE && intval($param_step) <= Installer::STEP_ERROR) {
			$step = $param_step;
		}

		if ($step == Installer::STEP_NONE) {
			$step == Installer::STEP_WELCOME;
		}

		log_install('step', 'setStep: ' . var_export($step, true));
		log_install('step', "setStep:" . PHP_EOL . var_export(debug_backtrace(0), true));

//		$install_version = read_config_option('install_version', true);
//		if ($install_version !== false) {
//			if ($install_version == CACTI_VERSION && $step == Installer::STEP_INSTALL) {
//				$step = Installer::STEP_COMPLETE;
//			}
//		}

		// Make current step the first if it is unknown
		$this->stepCurrent  = ($step == Installer::STEP_NONE ? Installer::STEP_WELCOME : $step);
		$this->stepPrevious = Installer::STEP_NONE;
		$this->steNext      = Installer::STEP_NONE;
		if ($step <= Installer::STEP_COMPLETE) {
			$this->stepNext     = ($step >= Installer::STEP_COMPLETE ? Installer::STEP_NONE : $step + 1);
			if ($step >= Installer::STEP_WELCOME) {
				$this->stepPrevious = ($step <= Installer::STEP_WELCOME ? Installer::STEP_NONE : $step - 1);
			}
		}

		set_config_option('install_step', $this->stepCurrent);
		$this->updateButtons();
		set_config_option('install_prev', $this->stepPrevious);
		set_config_option('install_next', $this->stepNext);
	}
