    public function addUser(){
        $login_user = $this->checkLogin();
        $this->checkAdmin();
        $username = I("post.username");
        $password = I("post.password");
        $uid = I("post.uid");
        $name = I("post.name");
        if(!$username){
            $this->sendError(10101,'用户名不允许为空');
            return ;
        }
        if($uid){
            if($password){
                D("User")->updatePwd($uid, $password);
            }
            if($name){
                D("User")->where(" uid = '$uid' ")->save(array("name"=>$name));
             }
             $this->sendResult(array());
        }else{
            if (D("User")->isExist($username)) {
                $this->sendError(10101,L('username_exists'));
                return ;
             }
             $new_uid = D("User")->register($username,$password);
             if (!$new_uid) {
                 $this->sendError(10101);
             }else{
                 if($name){
                    D("User")->where(" uid = '$new_uid' ")->save(array("name"=>$name));
                 }
                 $this->sendResult($return);
             }
        }

    }
