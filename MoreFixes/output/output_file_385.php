    public function findByToken($token)
    {
        $query = (new DbQuery())
            ->select('*')
            ->from(self::$definition['table'])
            ->where("content = '$token'")
        ;

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);
    }
