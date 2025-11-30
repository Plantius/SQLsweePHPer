    public function renderView()
    {
        /** @var RequestSql $obj */
        if (!($obj = $this->loadObject(true))) {
            return '';
        }

        try {
            if ($results = Db::readOnly()->getArray($obj->sql)) {
                foreach (array_keys($results[0]) as $key) {
                    $tabKey[] = $key;
                }

                $view['name'] = $obj->name;
                $view['key'] = $tabKey;
                $view['results'] = $results;

                $this->toolbar_title = $obj->name;

                $requestSql = new RequestSql();
                $view['attributes'] = $requestSql->attributes;
            } else {
                $view['error'] = true;
            }
        } catch (PrestaShopException $e) {
            $this->errors[] = $e->getMessage();
            $view = [
                'name'    => '',
                'key'     => '',
                'results' => [],
            ];
        }

        $this->tpl_view_vars = [
            'view' => $view,
        ];

        return parent::renderView();
    }
