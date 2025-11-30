    public function applyDatasetFilters($dataset, $ignore_sorting = false, $allowed_fields = []) {

        if (!empty($dataset['filters'])) {

            foreach ($dataset['filters'] as $filter) {

                // Если заданы разрешенные поля, проверяем
                // валидация
                if ($allowed_fields && !in_array($filter['field'], $allowed_fields, true)) {
                    continue;
                }

                if (isset($filter['callback']) && is_callable($filter['callback'])) {
                    $filter['callback']($this, $dataset);
                    continue;
                }

                if (!isset($filter['value'])) {
                    continue;
                }
                if (($filter['value'] === '') && !in_array($filter['condition'], ['nn', 'ni'])) {
                    continue;
                }
                if (empty($filter['condition'])) {
                    continue;
                }

                if ($filter['value'] !== '' && !is_array($filter['value'])) {
                    $filter['value'] = string_replace_user_properties($filter['value']);
                }

                switch ($filter['condition']) {

                    // общие условия
                    case 'eq': $this->filterEqual($filter['field'], $filter['value']);
                        break;
                    case 'gt': $this->filterGt($filter['field'], $filter['value']);
                        break;
                    case 'lt': $this->filterLt($filter['field'], $filter['value']);
                        break;
                    case 'ge': $this->filterGtEqual($filter['field'], $filter['value']);
                        break;
                    case 'le': $this->filterLtEqual($filter['field'], $filter['value']);
                        break;
                    case 'nn': $this->filterNotNull($filter['field']);
                        break;
                    case 'ni': $this->filterIsNull($filter['field']);
                        break;

                    // строки
                    case 'lk': $this->filterLike($filter['field'], '%' . $filter['value'] . '%');
                        break;
                    case 'ln': $this->filterNotLike($filter['field'], '%' . $filter['value'] . '%');
                        break;
                    case 'lb': $this->filterLike($filter['field'], $filter['value'] . '%');
                        break;
                    case 'lf': $this->filterLike($filter['field'], '%' . $filter['value']);
                        break;

                    // даты
                    case 'dy': $this->filterDateYounger($filter['field'], $filter['value']);
                        break;
                    case 'do': $this->filterDateOlder($filter['field'], $filter['value']);
                        break;

                    // массив
                    case 'in':
                        if (!is_array($filter['value'])) {
                            $filter['value'] = explode(',', $filter['value']);
                        }
                        $this->filterIn($filter['field'], $filter['value']);
                        break;
                }
            }
        }

        if (!empty($dataset['sorting']) && !$ignore_sorting) {
            $this->orderByList($dataset['sorting']);
        }

        if (!empty($dataset['index'])) {
            $this->forceIndex($dataset['index'], 2);
        }

        return true;
    }
