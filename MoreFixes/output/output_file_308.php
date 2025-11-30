    private function updateDeadline()
    {
        try {
            $due_date = self::getDueDate($this->zdb, $this->_member);

            if ($due_date != '') {
                $date_fin_update = $due_date;
            } else {
                $date_fin_update = new Expression('NULL');
            }

            $update = $this->zdb->update(Adherent::TABLE);
            $update->set(
                array('date_echeance' => $date_fin_update)
            )->where(
                Adherent::PK . '=' . $this->_member
            );
            $this->zdb->execute($update);
            return true;
        } catch (Throwable $e) {
            Analog::log(
                'An error occurred updating member ' . $this->_member .
                '\'s deadline |' .
                $e->getMessage(),
                Analog::ERROR
            );
            throw $e;
        }
    }
