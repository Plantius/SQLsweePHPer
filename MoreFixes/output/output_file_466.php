    public function load($id)
    {
        try {
            $select = $this->zdb->select(self::TABLE);
            $select->where(self::PK . ' = ' . $id);

            $results = $this->zdb->execute($select);
            $result = $results->current();

            if ($result) {
                $this->loadFromRs($result);
            }
        } catch (Throwable $e) {
            Analog::log(
                'Unable to retrieve field type for field ' . $id . ' | ' .
                $e->getMessage(),
                Analog::ERROR
            );
        }
    }
