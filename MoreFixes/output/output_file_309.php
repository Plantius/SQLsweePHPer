    private function updateModificationDate()
    {
        try {
            $modif_date = date('Y-m-d');
            $update = $this->zdb->update(self::TABLE);
            $update->set(
                array('date_modif_adh' => $modif_date)
            )->where(self::PK . '=' . $this->_id);

            $edit = $this->zdb->execute($update);
            $this->_modification_date = $modif_date;
        } catch (Throwable $e) {
            Analog::log(
                'Something went wrong updating modif date :\'( | ' .
                $e->getMessage() . "\n" . $e->getTraceAsString(),
                Analog::ERROR
            );
            throw $e;
        }
    }
