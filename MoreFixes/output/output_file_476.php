	public function quicksearch($text)
	{
		$like = ' LIKE '.protect('%'.$text.'%');
		
		$cols[] = 'id' . $like;
		$cols[] = 'category' . $like;
		$cols[] = 'codename' . $like;
		$cols[] = 'icon' . $like;		
		$cols[] = 'lid' . $like;		
	
		$where = ' AND ( ';	
		$where.= implode( ' OR ', $cols); 
		$where .= ')';
		
		return $where;
	}		  
