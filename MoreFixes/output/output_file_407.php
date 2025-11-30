    public function getPairs($data)
    {
        $limit = $data['per_page'] ?? 30;
        [$sql, $params] = $this->getSearchQuery($data, "SELECT c.id, CONCAT_WS('', c.first_name,  ' ', c.last_name) as full_name");
        $sql = $sql . ' LIMIT ' . $limit;

        return $this->di['db']->getAssoc($sql, $params);
    }
