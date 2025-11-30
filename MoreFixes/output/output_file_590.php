    public static function getDueDate(Db $zdb, $member_id)
    {
        if (!$member_id) {
            return '';
        }
        try {
            $select = $zdb->select(self::TABLE, 'c');
            $select->columns(
                array(
                    'max_date' => new Expression('MAX(date_fin_cotis)')
                )
            )->join(
                array('ct' => PREFIX_DB . ContributionsTypes::TABLE),
                'c.' . ContributionsTypes::PK . '=ct.' . ContributionsTypes::PK,
                array()
            )->where(
                Adherent::PK . ' = ' . $member_id
            )->where(
                array('cotis_extension' => new Expression('true'))
            );

            $results = $zdb->execute($select);
            $result = $results->current();
            $due_date = $result->max_date;

            //avoid bad dates in postgres and bad mysql return from zenddb
            if ($due_date == '0001-01-01 BC' || $due_date == '1901-01-01') {
                $due_date = '';
            }
            return $due_date;
        } catch (Throwable $e) {
            Analog::log(
                'An error occurred trying to retrieve member\'s due date',
                Analog::ERROR
            );
            throw $e;
        }
    }
