	public static function reorder($element, $template, $order, $enableds=NULL)
	{
		global $DB;
		global $website;
		
		$item = explode("#", $order);
							
		for($i=0; $i < count($item); $i++)
		{		
			if(empty($item[$i])) continue;

			$enabled = '';			
			if(is_array($enableds))
			{
				$enabled = ', enabled = 0 ';
				for($e=0; $e < count($enableds); $e++)
				{
					if($enableds[$e]==$item[$i]) $enabled = ', enabled = 1 ';
				}
			}
			
			$ok =	$DB->execute('
                UPDATE nv_properties
				   SET position = '.($i+1).' '.$enabled.' 
				 WHERE id = '.$item[$i].'
				   AND website = '.$website->id
            );
			
			if(!$ok)
            {
                return array("error" => $DB->get_last_error());
            }
		}
