    public function store($values)
    {
        if (!$this->check($values)) {
            return false;
        }

        $isnew = ($this->id === null);
        if ($this->old_name !== null) {
            $this->deleteTranslation($this->old_name);
            $this->addTranslation($this->name);
        }

        try {
            $values = array(
                'field_name'        => strip_tags($this->name),
                'field_perm'        => $this->perm,
                'field_required'    => $this->required,
                'field_width'       => ($this->width === null ? new Expression('NULL') : $this->width),
                'field_height'      => ($this->height === null ? new Expression('NULL') : $this->height),
                'field_size'        => ($this->size === null ? new Expression('NULL') : $this->size),
                'field_repeat'      => ($this->repeat === null ? new Expression('NULL') : $this->repeat),
                'field_form'        => $this->form,
                'field_index'       => $this->index
            );

            if ($this->required === false) {
                //Handle booleans for postgres ; bugs #18899 and #19354
                $values['field_required'] = $this->zdb->isPostgres() ? 'false' : 0;
            }

            if (!$isnew) {
                $update = $this->zdb->update(self::TABLE);
                $update->set($values)->where(
                    self::PK . ' = ' . $this->id
                );
                $this->zdb->execute($update);
            } else {
                $values['field_type'] = $this->getType();
                $insert = $this->zdb->insert(self::TABLE);
                $insert->values($values);
                $this->zdb->execute($insert);

                $this->id = $this->zdb->getLastGeneratedValue($this);

                if ($this->name != '') {
                    $this->addTranslation($this->name);
                }
            }
        } catch (Throwable $e) {
            Analog::log(
                'An error occurred storing field | ' . $e->getMessage(),
                Analog::ERROR
            );
            $this->errors[] = _T("An error occurred storing the field.");
        }

        if (count($this->errors) === 0 && $this->hasFixedValues()) {
            $contents_table = self::getFixedValuesTableName($this->id, true);

            try {
                $this->zdb->drop(str_replace(PREFIX_DB, '', $contents_table), true);
                $field_size = ((int)$this->size > 0) ? $this->size : 1;
                $this->zdb->db->query(
                    'CREATE TABLE ' . $contents_table .
                    ' (id INTEGER NOT NULL,val varchar(' . $field_size .
                    ') NOT NULL)',
                    \Laminas\Db\Adapter\Adapter::QUERY_MODE_EXECUTE
                );
            } catch (Throwable $e) {
                Analog::log(
                    'Unable to manage fields values table ' .
                    $contents_table . ' | ' . $e->getMessage(),
                    Analog::ERROR
                );
                $this->errors[] = _T("An error occurred creating field values table");
            }

            if (count($this->errors) == 0 && is_array($this->values)) {
                $contents_table = self::getFixedValuesTableName($this->id);
                try {
                    $this->zdb->connection->beginTransaction();

                    $insert = $this->zdb->insert($contents_table);
                    $insert->values(
                        array(
                            'id'    => ':id',
                            'val'   => ':val'
                        )
                    );
                    $stmt = $this->zdb->sql->prepareStatementForSqlObject($insert);

                    $cnt_values = count($this->values);
                    for ($i = 0; $i < $cnt_values; $i++) {
                        $stmt->execute(
                            array(
                                'id'    => $i,
                                'val'   => $this->values[$i]
                            )
                        );
                    }
                    $this->zdb->connection->commit();
                } catch (Throwable $e) {
                    $this->zdb->connection->rollBack();
                    Analog::log(
                        'Unable to store field ' . $this->id . ' values (' .
                        $e->getMessage() . ')',
                        Analog::ERROR
                    );
                    $this->warnings[] = _T('An error occurred storing dynamic field values :(');
                }
            }
        }

        if (count($this->errors) === 0) {
            return true;
        } else {
            return false;
        }
    }
