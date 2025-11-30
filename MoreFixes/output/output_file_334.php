    public function addRow($row)
    {
        $this->getLog()->debug('Adding row ' . var_export($row, true));

        // Update the last edit date on this dataSet
        $this->lastDataEdit = time();

        // Build a query to insert
        $keys = array_keys($row);
        $keys[] = 'id';

        $values = array_values($row);
        $values[] = NULL;

        $sql = 'INSERT INTO `dataset_' . $this->dataSetId . '` (`' . implode('`, `', $keys) . '`) VALUES (' . implode(',', array_fill(0, count($values), '?')) . ')';

        return $this->getStore()->insert($sql, $values);
    }
