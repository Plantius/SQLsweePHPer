    private function load($id)
    {
        try {
            $select = $this->zdb->select(self::TABLE);
            $select->limit(1)->where(self::PK . ' = ' . $id);
            if ($this->login->isSuperAdmin()) {
                $select->where(Adherent::PK . ' IS NULL');
            } else {
                $select->where(Adherent::PK . ' = ' . (int)$this->login->id);
            }

            $results = $this->zdb->execute($select);
            $res = $results->current();

            $this->loadFromRs($res);
        } catch (Throwable $e) {
