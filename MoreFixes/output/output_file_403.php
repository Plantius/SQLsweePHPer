    public function getMissingAmount()
    {
        if (empty($this->_id)) {
            return (double)$this->amount;
        }

        try {
            $select = $this->zdb->select(Contribution::TABLE);
            $select->columns(
                array(
                    'sum' => new Expression('SUM(montant_cotis)')
                )
            )->where(self::PK . ' = ' . $this->_id);

            $results = $this->zdb->execute($select);
            $result = $results->current();
            $dispatched_amount = $result->sum;
            return (double)$this->_amount - (double)$dispatched_amount;
        } catch (Throwable $e) {
            Analog::log(
                'An error occurred retrieving missing amounts | ' .
                $e->getMessage(),
                Analog::ERROR
            );
            throw $e;
        }
    }
