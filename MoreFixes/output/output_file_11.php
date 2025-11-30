                        $filter['value'] = strtotime($filter['value']);
                        $field = $fieldname;
                        $value = $filter['value'];
                    }
                }

                if ($field && $value) {
                    $condition = $field . ' ' . $operator . ' ' . $db->quote($value);

                    if ($languageMode) {
                        $conditions[$fieldname] = $condition;
                        $joins[] = [
                            'language' => $fieldname,
                        ];
                    } else {
                        $placeHolderName = self::PLACEHOLDER_NAME . $placeHolderCount;
                        $placeHolderCount++;
                        $conditionFilters[] = [
                            'condition' => $field . ' ' . $operator . ' :' . $placeHolderName,
                            'field' => $placeHolderName,
                            'value' => $value,
                        ];
                    }
                }
            }
        }

        if ($request->get('searchString')) {
            $conditionFilters[] = [
                'condition' => '(lower(' . $tableName . '.key) LIKE :filterTerm OR lower(' . $tableName . '.text) LIKE :filterTerm)',
                'field' => 'filterTerm',
                'value' => '%' . mb_strtolower($request->get('searchString')) . '%',
            ];
        }

        if ($languageMode) {
            return [
                'joins' => $joins,
                'conditions' => $conditions,
            ];
        }

        if(!empty($conditionFilters)) {
            $conditions = [];
            $params = [];
            foreach($conditionFilters as $conditionFilter) {
                $conditions[] = $conditionFilter['condition'];
                $params[$conditionFilter['field']] = $conditionFilter['value'];
            }

            $conditionFilters = [
                'condition' => implode(' AND ', $conditions),
                'params' => $params,
            ];
        }

        return $conditionFilters;
    }
