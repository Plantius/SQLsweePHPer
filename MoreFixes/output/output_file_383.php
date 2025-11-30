	public function findByMultiple($table, $where, $nr=NULL, $rev=NULL, $distinct=NULL)
	{
		$value = '';
		$match = NULL;
		$query = 'SELECT';

		if ($distinct != NULL)
			$query.= " DISTINCT " . $distinct;
		else
			$query.= " *";

		$query.= " FROM " . $table;

		if ($where != NULL)
		{
			foreach ($where as $key => $value)
			{
				if ($key != NULL)
				{
					if ($value != NULL)
						$match .= " ". $key . " = '" . $value . "' and";
					else
						$match .= " ". $key . " is NULL and";
				}
			}

			if ($match != NULL)
				$query .= " WHERE" . $match;

			$query = rtrim($query, "and");
			$query = rtrim($query);
		}

		if ($rev == 1)
			$query.= " ORDER BY id DESC";

		if ($nr != NULL)
			$query.= " LIMIT " . $nr;

		$result = $this->query($query, true);

		if (!$result)
			return false;

		if ($nr == 1)
		{
			$row = $this->fetchArray($result);
			$this->closeCursor($result);
			return $row;
		}

		$collection = array();

		while($row = $this->fetchArray($result))
			$collection[] = $row;

		$this->closeCursor($result);

		return $collection;
	}
