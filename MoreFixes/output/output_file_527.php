    public function store(History $hist)
    {
        global $emitter;

        $event = null;

        try {
            $this->zdb->connection->beginTransaction();
            $values = array();
            $fields = $this->getDbFields($this->zdb);
            /** FIXME: quote? */
            foreach ($fields as $field) {
                $prop = '_' . $this->_fields[$field]['propname'];
                $values[$field] = $this->$prop;
            }

            $success = false;
            if (!isset($this->_id) || $this->_id == '') {
                //we're inserting a new transaction
                unset($values[self::PK]);
                $insert = $this->zdb->insert(self::TABLE);
                $insert->values($values);
                $add = $this->zdb->execute($insert);
                if ($add->count() > 0) {
                    $this->_id = $this->zdb->getLastGeneratedValue($this);

                    // logging
                    $hist->add(
                        _T("Transaction added"),
                        Adherent::getSName($this->zdb, $this->_member)
                    );
                    $success = true;
                    $event = 'transaction.add';
                } else {
                    $hist->add(_T("Fail to add new transaction."));
                    throw new \RuntimeException(
                        'An error occurred inserting new transaction!'
                    );
                }
            } else {
                //we're editing an existing transaction
                $update = $this->zdb->update(self::TABLE);
                $update->set($values)->where(
                    self::PK . '=' . $this->_id
                );
                $edit = $this->zdb->execute($update);
                //edit == 0 does not mean there were an error, but that there
                //were nothing to change
                if ($edit->count() > 0) {
                    $hist->add(
                        _T("Transaction updated"),
                        Adherent::getSName($this->zdb, $this->_member)
                    );
                }
                $success = true;
                $event = 'transaction.edit';
            }

            //dynamic fields
            if ($success) {
                $success = $this->dynamicsStore(true);
            }

            $this->zdb->connection->commit();

            //send event at the end of process, once all has been stored
            if ($event !== null) {
                $emitter->emit($event, $this);
            }

            return true;
        } catch (Throwable $e) {
            $this->zdb->connection->rollBack();
            Analog::log(
                'Something went wrong :\'( | ' . $e->getMessage() . "\n" .
                $e->getTraceAsString(),
                Analog::ERROR
            );
            throw $e;
        }
    }
