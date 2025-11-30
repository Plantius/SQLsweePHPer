    public static function loadFrom(Db $zdb, $id, $mailing, $new = true)
    {
        try {
            $select = $zdb->select(self::TABLE);
            $select->where('mailing_id = ' . $id);

            $results = $zdb->execute($select);
            $result = $results->current();

            return $mailing->loadFromHistory($result, $new);
        } catch (Throwable $e) {
            Analog::log(
                'Unable to load mailing model #' . $id . ' | ' .
                $e->getMessage(),
                Analog::WARNING
            );
            throw $e;
        }
    }
