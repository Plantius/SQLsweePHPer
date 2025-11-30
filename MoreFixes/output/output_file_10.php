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
                        $conditionFilters[] = $condition;
                    }
                }
            }
        }

        if ($request->get('searchString')) {
            $filterTerm = $db->quote('%' . mb_strtolower($request->get('searchString')) . '%');
            $conditionFilters[] = '(lower(' . $tableName . '.key) LIKE ' . $filterTerm . ' OR lower(' . $tableName . '.text) LIKE ' . $filterTerm . ')';
        }

        if ($languageMode) {
            $result = [
                'joins' => $joins,
                'conditions' => $conditions,
            ];

            return $result;
        } else {
            if (!empty($conditionFilters)) {
                return implode(' AND ', $conditionFilters);
            }

            return null;
        }
    }
