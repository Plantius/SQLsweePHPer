	public static function reorder($parent, $children)
	{
		global $DB;
		global $website;
		
		$children = explode("#", $children);
				
		for($i=0; $i < count($children); $i++)
		{		
			if(empty($children[$i]))
            {
                continue;
            }

			$ok =	$DB->execute('UPDATE nv_structure 
									 SET position = '.($i+1).'
								   WHERE id = '.$children[$i].' 
									 AND parent = '.intval($parent).'
									 AND website = '.$website->id);
							 
			if(!$ok)
            {
                return array("error" => $DB->get_last_error());
            }
		}
