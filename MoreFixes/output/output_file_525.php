    public function store()
    {
        global $hist, $emitter, $login;
        $event = null;

        if (!$login->isAdmin() && !$login->isStaff() && !$login->isGroupManager() && $this->id == '') {
            if ($this->preferences->pref_bool_create_member) {
                $this->_parent = $login->id;
            }
        }

        try {
            $values = array();
            $fields = self::getDbFields($this->zdb);

            foreach ($fields as $field) {
                if (
                    $field !== 'date_modif_adh'
                    || empty($this->_id)
                ) {
                    $prop = '_' . $this->fields[$field]['propname'];
                    if (
                        ($field === 'bool_admin_adh'
                        || $field === 'bool_exempt_adh'
                        || $field === 'bool_display_info'
                        || $field === 'activite_adh')
                        && $this->$prop === false
                    ) {
                        //Handle booleans for postgres ; bugs #18899 and #19354
                        $values[$field] = $this->zdb->isPostgres() ? 'false' : 0;
                    } elseif ($field === 'parent_id') {
                        //handle parents
                        if ($this->_parent === null) {
                            $values['parent_id'] = new Expression('NULL');
                        } elseif ($this->parent instanceof Adherent) {
                            $values['parent_id'] = $this->_parent->id;
                        } else {
                            $values['parent_id'] = $this->_parent;
                        }
                    } else {
                        $values[$field] = $this->$prop;
                    }
                }
            }

            //an empty value will cause date to be set to 1901-01-01, a null
            //will result in 0000-00-00. We want a database NULL value here.
            if (!$this->_birthdate) {
                $values['ddn_adh'] = new Expression('NULL');
            }
            if (!$this->_due_date) {
                $values['date_echeance'] = new Expression('NULL');
            }

            if ($this->_title instanceof Title) {
                $values['titre_adh'] = $this->_title->id;
            } else {
                $values['titre_adh'] = new Expression('NULL');
            }

            if (!$this->_parent) {
                $values['parent_id'] = new Expression('NULL');
            }

            if (!$this->_number) {
                $values['num_adh'] = new Expression('NULL');
            }

            //fields that cannot be null
            $notnull = [
                '_surname'  => 'prenom_adh',
                '_nickname' => 'pseudo_adh',
                '_address'  => 'adresse_adh',
                '_zipcode'  => 'cp_adh',
                '_town'     => 'ville_adh'
            ];
            foreach ($notnull as $prop => $field) {
                if ($this->$prop === null) {
                    $values[$field] = '';
                }
            }

            $success = false;
            if (empty($this->_id)) {
                //we're inserting a new member
                unset($values[self::PK]);
                //set modification date
                $this->_modification_date = date('Y-m-d');
                $values['date_modif_adh'] = $this->_modification_date;

                $insert = $this->zdb->insert(self::TABLE);
                $insert->values($values);
                $add = $this->zdb->execute($insert);
                if ($add->count() > 0) {
                    $this->_id = $this->zdb->getLastGeneratedValue($this);
                    $this->_picture = new Picture($this->_id);
                    // logging
                    if ($this->_self_adh) {
                        $hist->add(
                            _T("Self_subscription as a member: ") .
                            $this->getNameWithCase($this->_name, $this->_surname),
                            $this->sname
                        );
                    } else {
                        $hist->add(
                            _T("Member card added"),
                            $this->sname
                        );
                    }
                    $success = true;

                    $event = 'member.add';
                } else {
                    $hist->add(_T("Fail to add new member."));
                    throw new \Exception(
                        'An error occurred inserting new member!'
                    );
                }
            } else {
                //we're editing an existing member
                if (!$this->isDueFree()) {
                    // deadline
                    $due_date = Contribution::getDueDate($this->zdb, $this->_id);
                    if ($due_date) {
                        $values['date_echeance'] = $due_date;
                    }
                }

                if (!$this->_password) {
                    unset($values['mdp_adh']);
                }

                $update = $this->zdb->update(self::TABLE);
                $update->set($values);
                $update->where(
                    self::PK . '=' . $this->_id
                );

                $edit = $this->zdb->execute($update);

                //edit == 0 does not mean there were an error, but that there
                //were nothing to change
                if ($edit->count() > 0) {
                    $this->updateModificationDate();
                    $hist->add(
                        _T("Member card updated"),
                        $this->sname
                    );
                }
                $success = true;
                $event = 'member.edit';
            }

            //dynamic fields
            if ($success) {
                $success = $this->dynamicsStore();
                $this->storeSocials($this->id);
            }

            //send event at the end of process, once all has been stored
            if ($event !== null) {
                $emitter->emit($event, $this);
            }
            return $success;
        } catch (Throwable $e) {
            Analog::log(
                'Something went wrong :\'( | ' . $e->getMessage() . "\n" .
                $e->getTraceAsString(),
                Analog::ERROR
            );
            throw $e;
        }
    }
