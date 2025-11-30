	public function goingForthAndBackStoresFormValuesOfSecondPageAndTriggersValidationOnlyWhenGoingForward() {
		$this->browser->request('http://localhost/test/form/simpleform/ThreePageFormWithValidation');

		$this->gotoNextFormPage($this->browser->getForm());

		$form = $this->browser->getForm();
		$form['--three-page-form-with-validation']['text2-1']->setValue('My Text on the second page');
		$this->gotoPreviousFormPage($form);
		$this->gotoNextFormPage($this->browser->getForm());
		$r = $this->gotoNextFormPage($this->browser->getForm());

		$this->assertSame(' error', $this->browser->getCrawler()->filterXPath('//*[contains(@class,"error")]//input[@id="three-page-form-with-validation-text2-1"]')->attr('class'));
		$form = $this->browser->getForm();
		$form['--three-page-form-with-validation']['text2-1']->setValue('42');
		$this->gotoNextFormPage($form);

		$form = $this->browser->getForm();
		$this->assertSame('', $form['--three-page-form-with-validation']['text3-1']->getValue());
	}
