    public function zoomSubmitAction($dataLabel, $goto)
    {
        //Query generation part
        $sql_query = $this->_buildSqlQuery();
        $sql_query .= ' LIMIT ' . $_POST['maxPlotLimit'];

        //Query execution part
        $result = $this->dbi->query(
            $sql_query . ";",
            DatabaseInterface::CONNECT_USER,
            DatabaseInterface::QUERY_STORE
        );
        $fields_meta = $this->dbi->getFieldsMeta($result);
        $data = array();
        while ($row = $this->dbi->fetchAssoc($result)) {
            //Need a row with indexes as 0,1,2 for the getUniqueCondition
            // hence using a temporary array
            $tmpRow = array();
            foreach ($row as $val) {
                $tmpRow[] = $val;
            }
            //Get unique condition on each row (will be needed for row update)
            $uniqueCondition = Util::getUniqueCondition(
                $result, // handle
                count($this->_columnNames), // fields_cnt
                $fields_meta, // fields_meta
                $tmpRow, // row
                true, // force_unique
                false, // restrict_to_table
                null // analyzed_sql_results
            );
            //Append it to row array as where_clause
            $row['where_clause'] = $uniqueCondition[0];

            $tmpData = array(
                $_POST['criteriaColumnNames'][0] =>
                    $row[$_POST['criteriaColumnNames'][0]],
                $_POST['criteriaColumnNames'][1] =>
                    $row[$_POST['criteriaColumnNames'][1]],
                'where_clause' => $uniqueCondition[0]
            );
            $tmpData[$dataLabel] = ($dataLabel) ? $row[$dataLabel] : '';
            $data[] = $tmpData;
        }
        unset($tmpData);

        //Displays form for point data and scatter plot
        $titles = array(
            'Browse' => Util::getIcon(
                'b_browse',
                __('Browse foreign values')
            )
        );
        $this->response->addHTML(
            Template::get('table/search/zoom_result_form')->render([
                'db' => $this->db,
                'table' => $this->table,
                'column_names' => $this->_columnNames,
                'foreigners' => $this->_foreigners,
                'column_null_flags' => $this->_columnNullFlags,
                'column_types' => $this->_columnTypes,
                'titles' => $titles,
                'goto' => $goto,
                'data' => $data,
                'data_json' => json_encode($data),
                'zoom_submit' => isset($_POST['zoom_submit']),
                'foreign_max_limit' => $GLOBALS['cfg']['ForeignKeyMaxLimit'],
            ])
        );
    }
