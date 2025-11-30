	public static function reorder($type, $order, $fixed)
	{
		global $DB;
		global $website;

		$item = explode("#", $order);
							
		for($i=0; $i < count($item); $i++)
		{		
			if(empty($item[$i]))
            {
                continue;
            }

            $block_is_fixed = ($fixed[$item[$i]]=='1'? '1' : '0');

			$ok = $DB->execute('
                UPDATE nv_blocks
				SET position = '.($i+1).',
				    fixed = '.$block_is_fixed.'
                WHERE id = '.$item[$i].'
				  AND website = '.$website->id
            );
			
			if(!$ok)
            {
                return array("error" => $DB->get_last_error());
            }
		}
