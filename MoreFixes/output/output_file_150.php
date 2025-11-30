		function get_application($id) {
			if (isset($this->applications[$id]))
				return $this->applications[$id];
			return null;
		}
