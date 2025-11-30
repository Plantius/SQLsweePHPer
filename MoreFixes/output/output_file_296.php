    private function load($id)
    {
        global $zdb;
        try {
            $select = $zdb->select(self::TABLE);
            $select->limit(1)->where(self::PK . ' = ' . $id);

            $results = $zdb->execute($select);
            $res = $results->current();

            $this->id = $id;
            $this->short = $res->short_label;
            $this->long = $res->long_label;
        } catch (Throwable $e) {
            Analog::log(
                'An error occurred loading title #' . $id . "Message:\n" .
                $e->getMessage(),
                Analog::ERROR
            );
            throw $e;
        }
    }
