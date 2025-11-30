	public function quicksearch($text)
	{
		$like = ' LIKE '.protect('%'.$text.'%');
		
		$cols[] = 'id' . $like;
		$cols[] = 'LOWER(username)' . mb_strtolower($like);
		$cols[] = 'email' . $like;
		$cols[] = 'fullname' . $like;		
	
		$where = ' AND ( ';	
		$where.= implode( ' OR ', $cols); 
		$where .= ')';
		
		return $where;
	}	
