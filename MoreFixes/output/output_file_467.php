    public function load($id)
    {
        try {
            $select = $this->zdb->select(self::TABLE, 't');
            $select->where(self::PK . ' = ' . $id);
            $select->join(
                array('a' => PREFIX_DB . Adherent::TABLE),
                't.' . Adherent::PK . '=a.' . Adherent::PK,
                array()
            );

            //restrict query on current member id if he's not admin nor staff member
            if (!$this->login->isAdmin() && !$this->login->isStaff() && !$this->login->isGroupManager()) {
                if (!$this->login->isLogged()) {
                    Analog::log(
                        'Non-logged-in users cannot load transaction id `' . $id,
                        Analog::ERROR
                    );
                    return false;
                }
                $select->where
                    ->nest()
                        ->equalTo('a.' . Adherent::PK, $this->login->id)
                        ->or
                        ->equalTo('a.parent_id', $this->login->id)
                    ->unnest()
                    ->and
                    ->equalTo('t.' . self::PK, $id)
                ;
            } else {
                $select->where->equalTo(self::PK, $id);
            }

            $results = $this->zdb->execute($select);
            $result = $results->current();
            if ($result) {
                $this->loadFromRS($result);
                return true;
            } else {
                Analog::log(
                    'Transaction id `' . $id . '` does not exists',
                    Analog::WARNING
                );
                return false;
            }
        } catch (Throwable $e) {
            Analog::log(
                'Cannot load transaction form id `' . $id . '` | ' .
                $e->getMessage(),
                Analog::WARNING
            );
            throw $e;
        }
    }
