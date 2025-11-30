    public function exists($uuid)
    {
        return (bool) $this->db->fetchOne('SELECT uuid FROM ' . self::TABLE_NAME . ' where uuid = ?', [$uuid]);
    }
