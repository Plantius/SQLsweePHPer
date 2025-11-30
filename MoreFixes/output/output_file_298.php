    private function load($id)
    {
        try {
            $select = $this->zdb->select(self::TABLE);
            $select->limit(1)->where(self::PK . ' = ' . $id);

            $results = $this->zdb->execute($select);
            $res = $results->current();

            $this->id = $id;
            $this->name = $res->type_name;
        } catch (Throwable $e) {
            Analog::log(
                'An error occurred loading payment type #' . $id . "Message:\n" .
                $e->getMessage(),
                Analog::ERROR
            );
            throw $e;
        }
    }
