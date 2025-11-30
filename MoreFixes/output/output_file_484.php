	public function quicksearch($text)
	{
		$like = ' LIKE '.protect('%'.$text.'%');
		
		$cols[] = 'node_id' . $like;
		$cols[] = 'lang' . $like;
		$cols[] = 'subtype' . $like;
		$cols[] = 'text' . $like;
	
		$where = ' AND ( ';	
		$where.= implode( ' OR ', $cols); 
		$where .= ')';
		
		return $where;
	}
