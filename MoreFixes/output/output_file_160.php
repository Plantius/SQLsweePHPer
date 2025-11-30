		function get_selected_application() {
			if (isset($this->selected_application))
				return $this->applications[$this->selected_application];
			foreach ($this->applications as $application)
				return $application;
			return null;
		}
