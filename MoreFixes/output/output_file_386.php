    public function get($id)
    {
        if (!is_numeric($id)) {
            $this->errors[] = _T("ID must be an integer!");
            return false;
        }

        try {
            $select = $this->zdb->select($this->table);
            $select->where($this->fpk . '=' . $id);

            $results = $this->zdb->execute($select);
            $result = $results->current();

            if (!$result) {
                $this->errors[] = _T("Label does not exist");
                return false;
            }

            return $result;
        } catch (Throwable $e) {
            Analog::log(
                __METHOD__ . ' | ' . $e->getMessage(),
                Analog::WARNING
            );
            throw $e;
        }
    }
