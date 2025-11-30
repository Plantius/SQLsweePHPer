    public function grantCheck($mode = 'i')
    {
        Analog::log(
            'Check for database rights (mode ' . $mode . ')',
            Analog::DEBUG
        );
        $stop = false;
        $results = array(
            'create' => false,
            'insert' => false,
            'select' => false,
            'update' => false,
            'delete' => false,
            'drop'   => false
        );
        if ($mode === 'u') {
            $results['alter'] = false;
        }

        //can Galette CREATE tables?
        try {
            $sql = 'CREATE TABLE galette_test (
                test_id INTEGER NOT NULL,
                test_text VARCHAR(20)
            )';
            $this->db->query($sql, Adapter::QUERY_MODE_EXECUTE);
            $results['create'] = true;
        } catch (Throwable $e) {
            Analog::log('Cannot CREATE TABLE', Analog::WARNING);
            //if we cannot create tables, we cannot check other permissions
            $stop = true;
            $results['create'] = $e;
        }

        //all those tests need the table to exists
        if (!$stop) {
            if ($mode == 'u') {
                //can Galette ALTER tables? (only for update mode)
                try {
                    $sql = 'ALTER TABLE galette_test ALTER test_text SET DEFAULT \'nothing\'';
                    $this->db->query($sql, Adapter::QUERY_MODE_EXECUTE);
                    $results['alter'] = true;
                } catch (Throwable $e) {
                    Analog::log(
                        'Cannot ALTER TABLE | ' . $e->getMessage(),
                        Analog::WARNING
                    );
                    $results['alter'] = $e;
                }
            }

            //can Galette INSERT records ?
            $values = array(
                'test_id'      => 1,
                'test_text'    => 'a simple text'
            );
            try {
                $insert = $this->sql->insert('galette_test');
                $insert->values($values);

                $res = $this->execute($insert);

                if ($res->count() === 1) {
                    $results['insert'] = true;
                } else {
                    throw new \Exception('No row inserted!');
                }
            } catch (Throwable $e) {
                Analog::log(
                    'Cannot INSERT records | ' . $e->getMessage(),
                    Analog::WARNING
                );
                //if we cannot insert records, some others tests cannot be done
                $stop = true;
                $results['insert'] = $e;
            }

            //all those tests need that the first record exists
            if (!$stop) {
                //can Galette UPDATE records ?
                $values = array(
                    'test_text' => 'another simple text'
                );
                try {
                    $update = $this->sql->update('galette_test');
                    $update->set($values)->where(
                        array('test_id' => 1)
                    );
                    $res = $this->execute($update);
                    if ($res->count() === 1) {
                        $results['update'] = true;
                    } else {
                        throw new \Exception('No row updated!');
                    }
                } catch (Throwable $e) {
                    Analog::log(
                        'Cannot UPDATE records | ' . $e->getMessage(),
                        Analog::WARNING
                    );
                    $results['update'] = $e;
                }

                //can Galette SELECT records ?
                try {
                    $select = $this->sql->select('galette_test');
                    $select->where('test_id = 1');
                    $res = $this->execute($select);
                    $pass = $res->count() === 1;

                    if ($pass) {
                        $results['select'] = true;
                    } else {
                        throw new \Exception('Select is empty!');
                    }
                } catch (Throwable $e) {
                    Analog::log(
                        'Cannot SELECT records | ' . $e->getMessage(),
                        Analog::WARNING
                    );
                    $results['select'] = $e;
                }

                //can Galette DELETE records ?
                try {
                    $delete = $this->sql->delete('galette_test');
                    $delete->where(array('test_id' => 1));
                    $this->execute($delete);
                    $results['delete'] = true;
                } catch (Throwable $e) {
                    Analog::log(
                        'Cannot DELETE records | ' . $e->getMessage(),
                        Analog::WARNING
                    );
                    $results['delete'] = $e;
                }
            }

            //can Galette DROP tables ?
            try {
                $sql = 'DROP TABLE galette_test';
                $this->db->query($sql, Adapter::QUERY_MODE_EXECUTE);
                $results['drop'] = true;
            } catch (Throwable $e) {
                Analog::log(
                    'Cannot DROP TABLE | ' . $e->getMessage(),
                    Analog::WARNING
                );
                $results['drop'] = $e;
            }
        }

        return $results;
    }
