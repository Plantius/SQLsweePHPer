    public function isUsed($id)
    {
        try {
            $select = $this->zdb->select($this->used);
            $select->where($this->fpk . ' = ' . $id);

            $results = $this->zdb->execute($select);
            $result = $results->current();

            if ($result !== null) {
                return true;
            } else {
                return false;
            }
        } catch (Throwable $e) {
            Analog::log(
                'Unable to check if ' . $this->getType . ' `' . $id .
                '` is used. | ' . $e->getMessage(),
                Analog::ERROR
            );
            //in case of error, we consider that it is used, to avoid errors
            return true;
        }
    }
