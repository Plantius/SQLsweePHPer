    private function load($id)
    {
        global $zdb;
        try {
            $select = $zdb->select(self::TABLE);
            $select->limit(1)
                ->where(self::PK . ' = ' . $id);

            $results = $zdb->execute($select);
            $this->loadFromRs($results->current());
        } catch (Throwable $e) {
            Analog::log(
                'An error occurred loading reminder #' . $id . "Message:\n" .
                $e->getMessage(),
                Analog::ERROR
            );
            throw $e;
        }
    }
