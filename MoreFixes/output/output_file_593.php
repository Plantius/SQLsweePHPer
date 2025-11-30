    public static function getSName($zdb, $id, $wid = false, $wnick = false)
    {
        try {
            $select = $zdb->select(self::TABLE);
            $select->where(self::PK . ' = ' . $id);

            $results = $zdb->execute($select);
            $row = $results->current();
            return self::getNameWithCase(
                $row->nom_adh,
                $row->prenom_adh,
                false,
                ($wid === true ? $row->id_adh : false),
                ($wnick === true ? $row->pseudo_adh : false)
            );
        } catch (Throwable $e) {
            Analog::log(
                'Cannot get formatted name for member form id `' . $id . '` | ' .
                $e->getMessage(),
                Analog::WARNING
            );
            throw $e;
        }
    }
