	public function quicksearch($text)
	{
		global $DB;
		global $website;
		
		$like = ' LIKE '.protect('%'.$text.'%');
		
		// we search for the IDs at the dictionary NOW (to avoid inefficient requests)
		$DB->query('
            SELECT DISTINCT (nvw.node_id)
              FROM nv_webdictionary nvw
             WHERE nvw.node_type = "block" AND
                   nvw.text '.$like.' AND
                   nvw.website = '.$website->id,
            'array'
        );
						   
		$dict_ids = $DB->result("node_id");
		
		// all columns to look for	
		$cols[] = 'b.id' . $like;
		$cols[] = 'b.type' . $like;
		$cols[] = 'b.notes' . $like;		

		if(!empty($dict_ids))
        {
            $cols[] = 'b.id IN ('.implode(',', $dict_ids).')';
        }
			
		$where = ' AND ( ';	
		$where.= implode( ' OR ', $cols); 
		$where .= ')';
		
		return $where;
	}
