	function add_lapp_function($level, $label,$link='',$access='SA_OPEN',$category='') {
		$this->modules[$level]->lappfunctions[] = new app_function($label, $link, $access, $category);
	}
