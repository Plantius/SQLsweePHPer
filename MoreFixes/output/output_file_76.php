	function add_item($label, $link) {
		$item = new menu_item($label,$link);
		array_push($this->items,$item);
		return $item;
	}
