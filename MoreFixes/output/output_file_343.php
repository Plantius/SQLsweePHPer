    public function authenticate(){
		if (!$this->_hasDB) return ($this->user == 'magmi' && $this->pass == 'magmi');
		$tn=$this->tablename('admin_user');
        $result = $this->select("SELECT * FROM $tn WHERE username = ?",array($this->user))->fetch(PDO::FETCH_ASSOC);
        return $this->validatePass($result['password'],$this->pass);
    }
