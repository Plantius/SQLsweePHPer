    public static function loadFieldType(Db $zdb, $id)
    {
        try {
            $select = $zdb->select(self::TABLE);
            $select->where('field_id = ' . $id);

            $results = $zdb->execute($select);
            $result = $results->current();
            if ($result) {
                $field_type = $result->field_type;
                $field_type = self::getFieldType($zdb, $field_type);
                $field_type->loadFromRs($result);
                return $field_type;
            }
        } catch (Throwable $e) {
            Analog::log(
                __METHOD__ . ' | Unable to retrieve field `' . $id .
                '` information | ' . $e->getMessage(),
                Analog::ERROR
            );
            return false;
        }
        return false;
    }
