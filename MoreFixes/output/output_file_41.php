            $sql .= 'SELECT ' . $db->quoteIdentifier($selectField);
        } else {
            $sql .= 'SELECT *';
        }
        if (!empty($config['from'])) {
            if (strpos(strtoupper(trim($config['from'])), 'FROM') !== 0) {
                $sql .= ' FROM ';
            }
            $sql .= ' ' . str_replace("\n", ' ', $config['from']);
        }

        if (!empty($config['where'])) {
            if (str_starts_with(strtoupper(trim($config['where'])), 'WHERE')) {
                $config['where'] = preg_replace('/^\s*WHERE\s*/', '', $config['where']);
            }
            $sql .= ' WHERE (' . str_replace("\n", ' ', $config['where']) . ')';
        }

        if (!empty($config['groupby']) && !$ignoreSelectAndGroupBy) {
            if (strpos(strtoupper(trim($config['groupby'])), 'GROUP BY') !== 0) {
                $sql .= ' GROUP BY ';
            }
            $sql .= ' ' . str_replace("\n", ' ', $config['groupby']);
        }

        if ($drillDownFilters) {
            $havingParts = [];
            $db = Db::get();
            foreach ($drillDownFilters as $field => $value) {
                if ($value !== '' && $value !== null) {
                    $havingParts[] = "$field = " . $db->quote($value);
                }
            }

            if ($havingParts) {
                $sql .= ' HAVING ' . implode(' AND ', $havingParts);
            }
        }

        return $sql;
    }
