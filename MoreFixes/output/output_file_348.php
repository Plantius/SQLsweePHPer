    public function checkOverlap()
    {
        try {
            $select = $this->zdb->select(self::TABLE, 'c');
            $select->columns(
                array('date_debut_cotis', 'date_fin_cotis')
            )->join(
                array('ct' => PREFIX_DB . ContributionsTypes::TABLE),
                'c.' . ContributionsTypes::PK . '=ct.' . ContributionsTypes::PK,
                array()
            )->where(Adherent::PK . ' = ' . $this->_member)
                ->where(array('cotis_extension' => new Expression('true')))
                ->where->nest->nest
                ->greaterThanOrEqualTo('date_debut_cotis', $this->_begin_date)
                ->lessThan('date_debut_cotis', $this->_end_date)
                ->unnest
                ->or->nest
                ->greaterThan('date_fin_cotis', $this->_begin_date)
                ->lessThanOrEqualTo('date_fin_cotis', $this->_end_date);

            if ($this->id != '') {
                $select->where(self::PK . ' != ' . $this->id);
            }

            $results = $this->zdb->execute($select);
            if ($results->count() > 0) {
                $result = $results->current();
                $d = new \DateTime($result->date_debut_cotis);

                return _T("- Membership period overlaps period starting at ") .
                    $d->format(__("Y-m-d"));
            }
            return true;
        } catch (Throwable $e) {
            Analog::log(
                'An error occurred checking overlapping fee. ' . $e->getMessage(),
                Analog::ERROR
            );
            throw $e;
        }
    }
