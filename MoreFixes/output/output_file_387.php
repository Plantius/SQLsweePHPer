    public function getAdvancedResultSet($q, $values, $per_page = 100)
    {
        $page = $page = $_GET['page'] ?? 1;
        $per_page = $_GET['per_page'] ?? $per_page;

        $offset = ($page - 1) * $per_page;
        $q = str_replace('SELECT ', 'SELECT SQL_CALC_FOUND_ROWS ', $q);
        $q .= sprintf(' LIMIT %s,%s', $offset, $per_page);
        $result = $this->di['db']->getAll($q, $values);
        $total = $this->di['db']->getCell('SELECT FOUND_ROWS();');

        $pages = ($per_page > 1) ? (int)ceil($total / $per_page) : 1;
        return array(
            "pages"             => $pages,
            "page"              => $page,
            "per_page"          => $per_page,
            "total"             => $total,
            "list"              => $result,
        );
    }
