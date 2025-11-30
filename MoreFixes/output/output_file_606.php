    public static function setTransactionPart(Db $zdb, $trans_id, $contrib_id)
    {
        try {
            $update = $zdb->update(self::TABLE);
            $update->set(
                array(Transaction::PK => $trans_id)
            )->where(self::PK . ' = ' . $contrib_id);

            $zdb->execute($update);
            return true;
        } catch (Throwable $e) {
            Analog::log(
                'Unable to attach contribution #' . $contrib_id .
                ' to transaction #' . $trans_id . ' | ' . $e->getMessage(),
                Analog::ERROR
            );
            throw $e;
        }
    }
