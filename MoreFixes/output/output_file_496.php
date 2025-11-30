    public function remove(History $hist, $transaction = true)
    {
        global $emitter;

        try {
            if ($transaction) {
                $this->zdb->connection->beginTransaction();
            }

            //remove associated contributions if needeed
            if ($this->getDispatchedAmount() > 0) {
                $c = new Contributions($this->zdb, $this->login);
                $clist = $c->getListFromTransaction($this->_id);
                $cids = array();
                foreach ($clist as $cid) {
                    $cids[] = $cid->id;
                }
                $rem = $c->remove($cids, $hist, false);
            }

            //remove transaction itself
            $delete = $this->zdb->delete(self::TABLE);
            $delete->where(
                self::PK . ' = ' . $this->_id
            );
            $del = $this->zdb->execute($delete);
            if ($del->count() > 0) {
                $this->dynamicsRemove(true);
            } else {
                Analog::log(
                    'Transaction has not been removed!',
                    Analog::WARNING
                );
                return false;
            }

            if ($transaction) {
                $this->zdb->connection->commit();
            }

            $emitter->emit('transaction.remove', $this);
            return true;
        } catch (Throwable $e) {
            if ($transaction) {
                $this->zdb->connection->rollBack();
            }
            Analog::log(
                'An error occurred trying to remove transaction #' .
                $this->_id . ' | ' . $e->getMessage(),
                Analog::ERROR
            );
            throw $e;
        }
    }
