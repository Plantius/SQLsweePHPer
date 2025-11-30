    public function wipecache()
    {
        $type = $this->request->request("type");
        switch ($type) {
            case 'all':
            case 'content':
                rmdirs(CACHE_PATH, false);
                Cache::clear();
                if ($type == 'content')
                    break;
            case 'template':
                rmdirs(TEMP_PATH, false);
                if ($type == 'template')
                    break;
            case 'addons':
                Service::refresh();
                if ($type == 'addons')
                    break;
        }

        \think\Hook::listen("wipecache_after");
        $this->success();
    }
