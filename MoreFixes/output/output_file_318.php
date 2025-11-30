    protected function getCheckFileQuery()
    {
        global $zdb;

        $select = $zdb->select(self::TABLE);
        $select->columns(
            array(
                'picture',
                'format'
            )
        );
        $select->where(self::PK . ' = ' . $this->db_id);
        return $select;
    }
