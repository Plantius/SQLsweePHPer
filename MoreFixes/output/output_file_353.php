    public function columns()
    {
        $param = input();
        $table = $param['table'];
        if(!empty($table)){
            $list = Db::query('SHOW COLUMNS FROM '.$table);
            $this->success(lang('obtain_ok'),null, $list);
        }
        $this->error(lang('param_err'));
    }
