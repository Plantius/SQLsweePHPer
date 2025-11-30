    public function getListItems($ignore_field = false){

        if (!$ignore_field) {

            $this->model->setPerPage($this->default_perpage);

            $visible_columns = cmsUser::getUPSActual($this->ups_key.'.visible_columns', $this->request->get('visible_columns', []));

            if ($visible_columns) {

                $switchable_columns = $this->grid->getSwitchableColumns();

                if ($switchable_columns) {
                    foreach ($switchable_columns as $name => $column) {
                        if (!in_array($name, $visible_columns)) {
                            $this->grid->disableColumn($name);
                        } else {
                            $this->grid->enableColumn($name);
                        }
                    }
                }
            }

            $filter     = $this->grid->filter;
            $pre_filter = cmsUser::getUPSActual($this->ups_key, $this->request->get('filter', ''));

            if ($pre_filter) {
                parse_str($pre_filter, $filter);
            }

            if ($filter) {

                if ($this->filter_callback) {
                    $filter = call_user_func_array($this->filter_callback, [$filter]);
                }

                $this->grid->applyGridFilter($this->model, $filter);
            }
        }

        if($this->list_callback){
            $this->model = call_user_func_array($this->list_callback, [$this->model]);
        }

        $total = $this->model->getCount($this->table_name);

        $data = $this->model->get($this->table_name, $this->item_callback) ?: [];

        if($this->items_callback){
            $data = call_user_func_array($this->items_callback, [$data]);
        }

        return $this->grid->makeGridRows($data, $total);
    }
