	public function initializeObject() {
		$this->initializeFormStateFromRequest();
		$this->initializeCurrentPageFromRequest();

		if (!$this->isFirstRequest()) {
			$this->processSubmittedFormValues();
		}
	}
