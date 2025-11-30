    public function store()
    {
        global $hist, $emitter;

        $event = null;

        if (count($this->errors) > 0) {
            throw new \RuntimeException(
                'Existing errors prevents storing contribution: ' .
                print_r($this->errors, true)
            );
        }

        try {
            $this->zdb->connection->beginTransaction();
            $values = array();
            $fields = self::getDbFields($this->zdb);
            foreach ($fields as $field) {
                $prop = '_' . $this->_fields[$field]['propname'];
                switch ($field) {
                    case ContributionsTypes::PK:
                    case Transaction::PK:
                        if (isset($this->$prop)) {
                            $values[$field] = $this->$prop->id;
                        }
                        break;
                    default:
                        $values[$field] = $this->$prop;
                        break;
                }
            }

            //no end date, let's take database defaults
            if (!$this->isFee() && !$this->_end_date) {
                unset($values['date_fin_cotis']);
            }

            $success = false;
            if (!isset($this->_id) || $this->_id == '') {
                //we're inserting a new contribution
                unset($values[self::PK]);

                $insert = $this->zdb->insert(self::TABLE);
                $insert->values($values);
                $add = $this->zdb->execute($insert);

                if ($add->count() > 0) {
                    $this->_id = $this->zdb->getLastGeneratedValue($this);

                    // logging
                    $hist->add(
                        _T("Contribution added"),
                        Adherent::getSName($this->zdb, $this->_member)
                    );
                    $success = true;
                    $event = 'contribution.add';
                } else {
                    $hist->add(_T("Fail to add new contribution."));
                    throw new \Exception(
                        'An error occurred inserting new contribution!'
                    );
                }
            } else {
                //we're editing an existing contribution
                $update = $this->zdb->update(self::TABLE);
                $update->set($values)->where(
                    self::PK . '=' . $this->_id
                );
                $edit = $this->zdb->execute($update);

                //edit == 0 does not mean there were an error, but that there
                //were nothing to change
                if ($edit->count() > 0) {
                    $hist->add(
                        _T("Contribution updated"),
                        Adherent::getSName($this->zdb, $this->_member)
                    );
                }

                if ($edit === false) {
                    throw new \Exception(
                        'An error occurred updating contribution # ' . $this->_id . '!'
                    );
                }
                $success = true;
                $event = 'contribution.edit';
            }
            //update deadline
            if ($this->isFee()) {
                $this->updateDeadline();
            }

            //dynamic fields
            if ($success) {
                $success = $this->dynamicsStore(true);
            }

            $this->zdb->connection->commit();
            $this->_orig_amount = $this->_amount;

            //send event at the end of process, once all has been stored
            if ($event !== null) {
                $emitter->emit($event, $this);
            }

            return true;
        } catch (Throwable $e) {
            if ($this->zdb->connection->inTransaction()) {
                $this->zdb->connection->rollBack();
            }
            throw $e;
        }
    }
