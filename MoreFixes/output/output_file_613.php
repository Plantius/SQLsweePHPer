    public static function unsetTransactionPart(Db $zdb, Login $login, $trans_id, $contrib_id)
    {
        try {
            //first, we check if contribution is part of transaction
            $c = new Contribution($zdb, $login, (int)$contrib_id);
            if ($c->isTransactionPartOf($trans_id)) {
                $update = $zdb->update(self::TABLE);
                $update->set(
                    array(Transaction::PK => null)
                )->where(
                    self::PK . ' = ' . $contrib_id
                );
                $zdb->execute($update);
                return true;
            } else {
                Analog::log(
                    'Contribution #' . $contrib_id .
                    ' is not actually part of transaction #' . $trans_id,
                    Analog::WARNING
                );
                return false;
            }
        } catch (Throwable $e) {
            Analog::log(
                'Unable to detach contribution #' . $contrib_id .
                ' to transaction #' . $trans_id . ' | ' . $e->getMessage(),
                Analog::ERROR
            );
            throw $e;
        }
    }
