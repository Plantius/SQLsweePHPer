    public function load($id)
    {
        try {
            $select = $this->zdb->select($this->table);
            $select->where($this->fpk . ' = ' . $id);

            $results = $this->zdb->execute($select);
            if ($results->count() > 0) {
                $result = $results->current();
                $this->loadFromRS($result);

                return true;
            } else {
                Analog::log(
                    'Unknown ID ' . $id,
                    Analog::ERROR
                );
                return false;
            }
        } catch (Throwable $e) {
            Analog::log(
                'Cannot load ' . $this->getType() . ' from id `' . $id . '` | ' .
                $e->getMessage(),
                Analog::WARNING
            );
            throw $e;
        }
    }
