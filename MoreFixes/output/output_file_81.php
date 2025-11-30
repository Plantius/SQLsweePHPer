	function add_rapp_function($label,$link='',$access='SA_OPEN',$category='') {
		$appfunction = new app_function($label,$link,$access,$category);
		$this->rappfunctions[] = $appfunction;
		return $appfunction;
	}
