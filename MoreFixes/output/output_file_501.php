	public function result($column="")
	{
		if(!empty($column))
		{
			$result = array();
			$total = count($this->lastResult);
			for($i=0; $i < $total; $i++)
			{
				if(is_array($this->lastResult[$i]))
                {
                    array_push($result, $this->lastResult[$i][$column]);
                }
				else if(is_object($this->lastResult[$i]))
                {
                    array_push($result, $this->lastResult[$i]->$column);
                }
			}
			return $result;			
		}
		else
        {
            return $this->lastResult;
        }
	}
