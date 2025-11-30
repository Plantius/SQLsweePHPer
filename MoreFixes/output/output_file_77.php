	function add_lapp_function($label,$link='',$access='SA_OPEN',$category='') {
		$appfunction = new app_function($label,$link,$access,$category);
		$this->lappfunctions[] = $appfunction;
		return $appfunction;
	}
