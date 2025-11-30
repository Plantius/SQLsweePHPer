	public function quicksearch($text)
	{
		$like = ' LIKE '.protect('%'.$text.'%');

		// all columns to look for	
		$cols[] = 'b.id' . $like;
		$cols[] = 'b.title' . $like;
		$cols[] = 'b.notes' . $like;		

		$where = ' AND ( ';	
		$where.= implode( ' OR ', $cols); 
		$where .= ')';
		
		return $where;
	}
