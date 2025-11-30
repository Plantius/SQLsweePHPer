			$where .= implode( ' OR ', $cols);
			$where .= ')';
		}
		
		return $where;
	}
