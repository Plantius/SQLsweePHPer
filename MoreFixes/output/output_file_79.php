	function add_module($name, $icon = null) {
		$module = new module($name,$icon);
		$this->modules[] = $module;
		return $module;
	}
