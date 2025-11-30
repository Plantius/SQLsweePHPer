    public function getDataRowAction()
    {
        $extra_data = array();
        $row_info_query = 'SELECT * FROM `' . $_POST['db'] . '`.`'
            . $_POST['table'] . '` WHERE ' .  $_POST['where_clause'];
        $result = $this->dbi->query(
            $row_info_query . ";",
            DatabaseInterface::CONNECT_USER,
            DatabaseInterface::QUERY_STORE
        );
        $fields_meta = $this->dbi->getFieldsMeta($result);
        while ($row = $this->dbi->fetchAssoc($result)) {
            // for bit fields we need to convert them to printable form
            $i = 0;
            foreach ($row as $col => $val) {
                if ($fields_meta[$i]->type == 'bit') {
                    $row[$col] = Util::printableBitValue(
                        $val, $fields_meta[$i]->length
                    );
                }
                $i++;
            }
            $extra_data['row_info'] = $row;
        }
        $this->response->addJSON($extra_data);
    }
