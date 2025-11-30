    public function dbExists($dbName = '')
    {
        $sql = "SHOW DATABASES like '{$dbName}'";
        return $this->dbh->query($sql)->fetch();
    }
