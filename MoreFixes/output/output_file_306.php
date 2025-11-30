	private function sendVisibleFieldsFormEmail($startLine, $email, $mergeFields = [],$responseId, $attachments = [], 		$replyToEmail = false, $replyToName = false, $adminDownloadLinks = false) {
		$formName = $this->form['name'] ? trim($this->form['name']) : '[blank name]';
		$formId = $this->form['id'];
		//var_dump($this->form['id']);
		$subject = 'New form submission for: ' . $formName;
		$addressFrom = ze::setting('email_address_from');
		$nameFrom = ze::setting('email_name_from');
		$url = ze\link::toItem(ze::$cID, ze::$cType, true, '', false, false, true);
		if (!$url) {
			$url = ze\link::absolute();
		}
		
		
			$body = $startLine;
		
			$body .=$this->getFormSummaryHTML($responseId);
		
		$body .= '<p>This is an auto-generated email from ' . htmlspecialchars($url) . '</p>';
		zenario_email_template_manager::putBodyInTemplate($body);
		zenario_email_template_manager::sendEmails($email, $subject, $addressFrom, $nameFrom, $body, [], $attachments, [], 0, false, $replyToEmail, $replyToName);
	}
