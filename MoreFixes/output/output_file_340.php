    public function applyGridFilter(cmsModel $model, $filter) {

        // применяем сортировку
        if (!empty($filter['order_by']) && !empty($filter['order_to'])) {

            // Есть ли вообще такой столбец
            if(empty($this->grid['columns'][$filter['order_by']])){
                $filter['order_by'] = $this->grid['options']['order_by'];
            }

            $order_by = $filter['order_by'];

            // Есть отдельный столбец для сортировки
            if (!empty($this->grid['columns'][$order_by]['order_by'])) {
                $order_by = $this->grid['columns'][$order_by]['order_by'];
            }

            $model->orderBy($order_by, $filter['order_to']);
        }

        // устанавливаем страницу
        if (!empty($filter['page'])) {

            $filter['perpage'] = !empty($filter['perpage']) ? (int) $filter['perpage'] : 30;
            $filter['page']    = (int) ($filter['page'] <= 0 ? 1 : $filter['page']);

            $model->limitPage($filter['page'], $filter['perpage']);
        }

        // Пагинация отключена
        if(!$this->grid['options']['is_pagination']){
            $model->limit(false);
        }

        //
        // проходим по каждой колонке таблицы
        // и проверяем не передан ли фильтр для нее
        //
        foreach ($this->getVisibleColumns() as $field => $column) {

            if (empty($column['filter']) ||
                    $column['filter'] === 'none' ||
                    !array_key_exists($field, $filter) ||
                    is_empty_value($filter[$field])) {
                continue;
            }

            if (!empty($column['filter_by'])) {
                $filter_field = $column['filter_by'];
            } else {
                $filter_field = $field;
            }

            switch ($column['filter']) {
                case 'range_date':
                    if (isset($filter[$field]['from']) && !is_empty_value($filter[$field]['from'])) {
                        $date_from = date('Y-m-d', strtotime($filter[$field]['from']));
                        $model->filterGtEqual($filter_field, $date_from);
                    }
                    if (isset($filter[$field]['to']) && !is_empty_value($filter[$field]['to'])) {
                        $date_to = date('Y-m-d', strtotime($filter[$field]['to']));
                        $model->filterLtEqual($filter_field, $date_to);
                    }
                case 'range':
                    if (isset($filter[$field]['from']) && !is_empty_value($filter[$field]['from'])) {
                        $model->filterGtEqual($filter_field, $filter[$field]['from']);
                    }
                    if (isset($filter[$field]['to']) && !is_empty_value($filter[$field]['to'])) {
                        $model->filterLtEqual($filter_field, $filter[$field]['to']);
                    }
                    break;
                case 'zero':
                    if($filter[$field]) {
                        $model->filterEqual($filter_field, 0);
                    }
                case 'nn':
                    if($filter[$field]) {
                        $model->filterNotNull($filter_field);
                    }
                    break;
                case 'ni':
                    if($filter[$field]) {
                        $model->filterIsNull($filter_field);
                    }
                    break;
                case 'in': $model->filterIn($filter_field, !is_array($filter[$field]) ? explode(',', $filter[$field]) : $filter[$field]);
                    break;
                case 'filled': ($filter[$field] ? $model->filterNotNull($filter_field) : $model->filterIsNull($filter_field));
                    break;
                case 'exact': $model->filterEqual($filter_field, $filter[$field]);
                    break;
                case 'ip': $model->filterEqual($filter_field, string_iptobin($filter[$field]), true);
                    break;
                case 'like': $model->filterLike($filter_field, "%{$filter[$field]}%");
                    break;
                case 'date':
                    $date = date('Y-m-d', strtotime($filter[$field]));
                    $model->filterLike($filter_field, "%{$date}%");
                    break;
            }
        }

        // Запоминаем
        $this->grid['filter'] = array_merge($this->grid['filter'], $filter);

        // Дополнительный фильтр
        if (!empty($filter['advanced_filter']) && is_string($filter['advanced_filter'])) {

            parse_str($filter['advanced_filter'], $dataset_filters);

            $model->applyDatasetFilters($dataset_filters);
        }

        return $model;
    }
