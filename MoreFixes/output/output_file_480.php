    public function quicksearch($text)
    {
        $like = ' LIKE '.protect('%'.$text.'%');

        $cols[] = 'id' . $like;
        $cols[] = 'name' . $like;
        $cols[] = 'domain' . $like;
        $cols[] = 'subdomain' . $like;

        $where = ' AND ( ';
        $where.= implode( ' OR ', $cols);
        $where .= ')';

        return $where;
    }
