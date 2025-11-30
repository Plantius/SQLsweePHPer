	public function quicksearch($text)
	{
		$like = ' LIKE '.protect('%'.$text.'%');
				
		// all columns to look for	
		$cols[] = 'i.id' . $like;
		$cols[] = 'i.title' . $like;
		$cols[] = 'i.notes' . $like;
			
		$where = ' AND ( ';	
		$where.= implode( ' OR ', $cols); 
		$where .= ')';
		
		return $where;
	}	
