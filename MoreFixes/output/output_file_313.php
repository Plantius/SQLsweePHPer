    protected function emailExistsInDB($email)
    {
        /* Build sql query*/
        $sql  = 'SELECT * FROM '.$this->usersTable;
        $sql .= ' WHERE email = "'.$email.'";';
        /* Execute query */
        $results = $this->wiki->loadAll($sql);
        return $results; // If the password does not already exist in DB, $result is an empty table => false
    }
