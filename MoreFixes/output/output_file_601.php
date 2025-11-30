    public static function reorder($order)
    {
        global $DB;
		global $website;

		$items = explode("#", $order);

		for($i=0; $i < count($items); $i++)
		{
			if(empty($items[$i])) continue;

			$ok =	$DB->execute('UPDATE nv_items
									 SET position = '.($i+1).'
								   WHERE id = '.$items[$i].'
						 		     AND website = '.$website->id);

			if(!$ok)
            {
                return array("error" => $DB->get_last_error());
            }
		}
