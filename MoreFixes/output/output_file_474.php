	public function processStepError() {
		$output = Installer::sectionTitleError(__('Failed'));
		$output .= Installer::sectionNormal(__('There was a problem during this process.  Please check the log below for more information'));
		$output .= $this->getInstallLog();
	}
