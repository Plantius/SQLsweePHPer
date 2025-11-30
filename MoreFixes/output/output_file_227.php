	function select($table,$cols,$where,$line,$file,$offset=False,$append='',$app=False,$num_rows=0,$join='',$table_def=False,$fetchmode=self::FETCH_ASSOC)
	{
		if ($this->Debug) echo "<p>db::select('$table',".print_r($cols,True).",".print_r($where,True).",$line,$file,$offset,'$app',$num_rows,'$join')</p>\n";

		if (!$table_def) $table_def = $this->get_table_definitions($app,$table);
		if (is_array($cols))
		{
			$cols = implode(',',$cols);
		}
		if (is_array($where))
		{
			$where = $this->column_data_implode(' AND ',$where,True,False, $table_def ? $table_def['fd'] : null);
		}
		if (self::$tablealiases && isset(self::$tablealiases[$table]))
		{
			$table = self::$tablealiases[$table];
		}
		$sql = "SELECT $cols FROM $table $join";

		// if we have a where clause, we need to add it together with the WHERE statement, if thats not in the join
		if ($where) $sql .= (strpos($join,"WHERE")!==false) ? ' AND ('.$where.')' : ' WHERE '.$where;

		if ($append) $sql .= ' '.$append;

		if ($this->Debug) echo "<p>sql='$sql'</p>";

		if ($line === false && $file === false)	// call by union, to return the sql rather than run the query
		{
			return $sql;
		}
		return $this->query($sql,$line,$file,$offset,$offset===False ? -1 : (int)$num_rows,false,$fetchmode);
	}
