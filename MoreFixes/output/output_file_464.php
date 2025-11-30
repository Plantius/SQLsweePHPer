	public function list_items($limit = NULL, $offset = 0, $col = 'id', $order = 'asc', $just_count = FALSE)
	{
		if (!empty($this->list_items))
		{
			$filter_params = array('limit', 'offset', 'col', 'order');
			foreach ($filter_params as $param)
			{
				$this->list_items->$param = $$param;
			}
			$this->list_items->run();

			 // in case it changed with run method
			$col = $this->list_items->col;
			$order = $this->list_items->order;
		}

		$this->_list_items_query();

		$this->_limit_to_user();

		if ($just_count)
		{
			$has_have = FALSE;
			foreach($this->filters as $k => $v)
			{
				if (preg_match('#.+_having$#', $k))
				{
					$has_have = TRUE;
					break;
				}
			}

			if (!$has_have)
			{
				return $this->db->count_all_results();
			}
		}
		
		if (!$this->db->has_select())
		{
			$this->db->select($this->table_name.'.*'); // make select table specific
		}

		if (!empty($col)) $this->db->order_by(str_replace(' ', '', $col), str_replace(' ', '', $order), FALSE);
		if (!empty($limit)) $this->db->limit((int) $limit);
		$this->db->offset((int)$offset);

		$query = $this->db->get();
		$data = $query->result_array();

		if (!empty($this->list_items) AND $just_count == FALSE)
		{
			$data = $this->list_items->process($data);
		}

		// has have statement
		if ($just_count)
		{
			return count($data);
		}
		
		return $data;
	}
