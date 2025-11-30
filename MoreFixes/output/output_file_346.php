	public function checkAccessToken(){
		$model  = $this->loadModel('Plugin');
		$config = $model->getConfig('fileView');
		if(!$config['apiKey']){
			return;
		}
		$timeTo = isset($this->in['timeTo'])?intval($this->in['timeTo']):'';
		$token = md5($config['apiKey'].$this->in['path'].$timeTo);
		
		//show_tips(array($config['apiKey'],$token,$this->in));
		if($token != $this->in['token']){
			show_tips('token 错误!');
		}
		if($timeTo != '' && $timeTo <= time()){
			show_tips('token已失效!');
		}
	}
