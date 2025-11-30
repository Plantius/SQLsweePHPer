    public function remove($transaction = true)
    {
        global $emitter;

        try {
            if ($transaction) {
                $this->zdb->connection->beginTransaction();
            }

            $delete = $this->zdb->delete(self::TABLE);
            $delete->where(self::PK . ' = ' . $this->_id);
            $del = $this->zdb->execute($delete);
            if ($del->count() > 0) {
                $this->updateDeadline();
                $this->dynamicsRemove(true);
            } else {
                Analog::log(
                    'Contribution has not been removed!',
                    Analog::WARNING
                );
                return false;
            }
            if ($transaction) {
                $this->zdb->connection->commit();
            }
            $emitter->emit('contribution.remove', $this);
            return true;
        } catch (Throwable $e) {
            if ($transaction) {
                $this->zdb->connection->rollBack();
            }
            Analog::log(
                'An error occurred trying to remove contribution #' .
                $this->_id . ' | ' . $e->getMessage(),
                Analog::ERROR
            );
            throw $e;
        }
    }
