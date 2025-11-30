	public function jsonSerialize() {
		$output = $this->processCurrentStep();

		if (isset($this->stepData)) {
			if (!isset($this->stepData['Theme'])) {
				$this->stepData['Theme'] = $this->theme;
			}
		}

		return array(
			'Mode'     => $this->mode,
			'Step'     => $this->stepCurrent,
			'Eula'     => $this->eula,
			'Prev'     => $this->buttonPrevious,
			'Next'     => $this->buttonNext,
			'Test'     => $this->buttonTest,
			'Html'     => $output,
			'StepData' => $this->stepData,
			'RRDVer'   => $this->rrdVersion,
			'Theme'    => $this->theme,
			'Language' => $this->language
		);
	}
