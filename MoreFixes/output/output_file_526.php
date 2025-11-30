    public function store()
    {
        global $zdb, $hist;

        try {
            $values = array(
                self::PK     => $this->id,
                'group_name' => $this->group_name
            );

            if ($this->parent_group) {
                $values['parent_group'] = $this->parent_group->getId();
            }

            if (!isset($this->id) || $this->id == '') {
                //we're inserting a new group
                unset($values[self::PK]);
                $this->creation_date = date("Y-m-d H:i:s");
                $values['creation_date'] = $this->creation_date;

                $insert = $zdb->insert(self::TABLE);
                $insert->values($values);
                $add = $zdb->execute($insert);
                if ($add->count() > 0) {
                    $this->id = $zdb->getLastGeneratedValue($this);

                    // logging
                    $hist->add(
                        _T("Group added"),
                        $this->group_name
                    );
                    return true;
                } else {
                    $hist->add(_T("Fail to add new group."));
                    throw new \Exception(
                        'An error occurred inserting new group!'
                    );
                }
            } else {
                //we're editing an existing group
                $update = $zdb->update(self::TABLE);
                $update
                    ->set($values)
                    ->where(self::PK . '=' . $this->id);

                $edit = $zdb->execute($update);

                //edit == 0 does not mean there were an error, but that there
                //were nothing to change
                if ($edit->count() > 0) {
                    $hist->add(
                        _T("Group updated"),
                        $this->group_name
                    );
                }
                return true;
            }
            /** FIXME: also store members and managers? */
        } catch (Throwable $e) {
            Analog::log(
                'Something went wrong :\'( | ' . $e->getMessage() . "\n" .
                $e->getTraceAsString(),
                Analog::ERROR
            );
            throw $e;
        }
    }
