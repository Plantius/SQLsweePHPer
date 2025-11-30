    public function quicksearch($text)
    {
        $like = ' LIKE '.protect('%'.$text.'%');

        $cols[] = 'name '.$like;

        $where = ' AND ( ';
        $where.= implode( ' OR ', $cols);
        $where .= ')';

        return $where;
    }
