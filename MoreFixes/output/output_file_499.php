    public function rep()
    {
        if($this->request->isPost()){
            $param = input();
            $table = $param['table'];
            $field = $param['field'];
            $findstr = $param['findstr'];
            $tostr = $param['tostr'];
            $where = $param['where'];

            $validate = \think\Loader::validate('Token');
            if(!$validate->check($param)){
                return $this->error($validate->getError());
            }

            if(!empty($table) && !empty($field) && !empty($findstr) && !empty($tostr)){
                $sql = "UPDATE ".$table." set ".$field."=Replace(".$field.",'".$findstr."','".$tostr."') where 1=1 ". $where;
                Db::execute($sql);
                return $this->success(lang('run_ok'));
            }

            return $this->error(lang('param_err'));
        }
        $list = Db::query("SHOW TABLE STATUS");
        $this->assign('list',$list);
        return $this->fetch('admin@database/rep');
    }
