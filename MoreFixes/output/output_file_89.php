function asoc_row($tablica,$field,$id) {
	foreach($tablica as $row) {
		if ($row[$field]==$id) return $row;
	}
	return array();
}
