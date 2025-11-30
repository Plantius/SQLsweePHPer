    public function quicksearch($text)
    {
        $like = ' LIKE '.protect('%'.$text.'%');

        $cols[] = 'code '.$like;

        $where = ' AND ( ';
        $where.= implode( ' OR ', $cols);
        $where .= ')';

        return $where;
    }
