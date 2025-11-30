    public function getSimpleResultSet($q, $values, $per_page = 100, $page = null)
    {
        if (is_null($page)){
            $page = $_GET['page'] ?? 1;
        }
        $per_page = $_GET['per_page'] ?? $per_page;

        $offset = ($page - 1) * $per_page;

        $sql = $q;
        $sql .= sprintf(' LIMIT %s,%s', $offset, $per_page);
        $result = $this->di['db']->getAll($sql, $values);

        $exploded = explode('FROM', $q);
        $sql = 'SELECT count(1) FROM ' . $exploded[1];
        $total = $this->di['db']->getCell($sql , $values);

        $pages = ($per_page > 1) ? (int)ceil($total / $per_page) : 1;
        return array(
            "pages"             => $pages,
            "page"              => $page,
            "per_page"          => $per_page,
            "total"             => $total,
            "list"              => $result,
        );
    }
