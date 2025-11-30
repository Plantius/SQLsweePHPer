	function add_rapp_function($level, $label,$link='',$access='SA_OPEN',$category='') {
		$this->modules[$level]->rappfunctions[] = new app_function($label, $link, $access, $category);
	}
